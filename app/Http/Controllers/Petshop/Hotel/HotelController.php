<?php

namespace App\Http\Controllers\Petshop\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Hotel;
use App\Models\Petshop\Quarto;
use App\Models\Servico;
use App\Models\ServicoOs;
use App\Models\Produto;
use App\Models\ProdutoOs;
use App\Services\Petshop\HotelService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\QuartoService;
use Dompdf\Dompdf;

class HotelController extends Controller
{
    protected HotelService $hotel_service; 

    public function __construct(HotelService $hotel_service)
    {
        $this->hotel_service = $hotel_service;
    }


    public function index(Request $request)
    {

        $empresa_id = $request->empresa_id;

        $pesquisa = $request->input('pesquisa');
        $checkin = $request->input('checkin_start_date');
        $checkout = $request->input('checkout_end_date');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $quarto_id = $request->input('quarto_id');
        $estado = $request->input('estado');

        $query = Hotel::where('empresa_id', $empresa_id)
            ->when($pesquisa, function ($q) use ($pesquisa) {
                $q->where(function ($sub) use ($pesquisa) {
                    $sub->whereHas('animal', fn($q) => $q->where('petshop_animais.nome', 'like', "%{$pesquisa}%"))
                        ->orWhereHas('cliente', fn($q) => $q->where('clientes.razao_social', 'like', "%{$pesquisa}%"));
                });
            })
            ->when($quarto_id, function ($q) use ($quarto_id) {
                $q->where('quarto_id', $quarto_id);
            })
            ->when($estado, function ($q) use ($estado) {
                $q->where('estado', $estado);
            })
            ->when($checkin, function ($q) use ($checkin) {
                $q->whereDate('checkin', '>=', $checkin);
            })
            ->when($checkout, function ($q) use ($checkout) {
                $q->whereDate('checkout', '<=', $checkout);
            })
            ->when($start_date, function ($q) use ($start_date) {
                $q->whereDate('created_at', '>=', $start_date);
            })
            ->when($end_date, function ($q) use ($end_date) {
                $q->whereDate('created_at', '<=', $end_date);
            });

        $contagemPorQuarto = (clone $query)
            ->select('quarto_id', DB::raw('count(distinct animal_id) as total'))
            ->groupBy('quarto_id')
            ->pluck('total', 'quarto_id');

        $data = $query->orderBy('created_at', 'desc')->paginate(env("PAGINACAO"))->appends($request->all());

        $servicos = Servico::whereHas('categoria', function ($query) {
            $query->where('nome', 'HOTEL');
        })->get();

        $quartos = Quarto::where('empresa_id', $empresa_id)->get();

        return view('petshop.hotel.index', compact('data', 'contagemPorQuarto', 'servicos', 'quartos'));
    }

    public function create()
    {

        $empresa_id = request()->empresa_id;

        $servicos = Servico::whereHas('categoria', function ($query) {
            $query->where('nome', 'HOTEL');
        })->get();
        $quartos = Quarto::where('empresa_id', $empresa_id)->get();

        $servicosFormatados = $servicos->mapWithKeys(function ($servico) {
            return [
                $servico->id => $servico->nome . ' (R$ ' . number_format($servico->valor, 2, ',', '.') . ')'
            ];
        });

        $data = new Hotel();

        return view('petshop.hotel.create', compact('data', 'servicos', 'quartos', 'servicosFormatados'));
    }

    public function store(Request $request)
    {
        $quarto_service = new QuartoService();

        $empresa_id = $request->empresa_id;
        Log::debug('HotelController@store payload', $request->only('checkin', 'timecheckin', 'checkout', 'timecheckout'));

        $request->merge([
            'servico_ids' => array_values(
                array_map(
                    fn ($id) => $id,
                    array_filter(
                        $request->input('servico_ids', []),
                        fn ($v) => $v !== null && $v !== ''
                    )
                )
            ),

            'servico_valor' => array_values(
                array_map(
                    fn ($valor) => __convert_value_bd($valor),
                    array_filter(
                        $request->input('servico_valor', []),
                        fn ($v) => $v !== null && $v !== '' 
                    )
                )
            ),

            'servico_ids_valid' => collect($request->input('servico_ids', []))
                ->slice(1) 
                ->filter(fn ($val) => !empty($val))
                ->values() 
                ->toArray(),

            'servico_datas_valid' => collect($request->input('servico_datas', []))
                ->slice(1)
                ->filter(fn ($val) => !empty($val))
                ->values()
                ->toArray(),

            'servico_horas_valid' => collect($request->input('servico_horas', []))
                ->slice(1)
                ->filter(fn ($val) => !empty($val))
                ->values()
                ->toArray(),

            'servico_valor_valid' => collect($request->input('servico_valor', []))
                ->slice(1)
                ->filter(fn ($val) => $val !== null && $val !== '')
                ->map(fn ($valor) => __convert_value_bd($valor))
                ->values()
                ->toArray(),
        ]);

        $request->validate([
            'empresa_id' => 'required|integer',
            // Dados da reserva

            'animal_id' => 'required|exists:petshop_animais,id',
            'quarto_id' => 'required|exists:quartos,id',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
            'checkin' => 'required|date',
            'timecheckin' => ['required', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'checkout' => 'required|date|after_or_equal:checkin',
            'timecheckout' => ['required', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'descricao' => 'nullable|string|max:1000',

            // Serviço de reserva
            'servico_ids' => 'required|array',
            'servico_ids.0' => 'required|exists:servicos,id',

            // Serviços extras
            'servico_ids.*' => 'nullable|exists:servicos,id',
            'servico_datas_valid.*' => 'required_with:servico_ids_valid|date|after_or_equal:checkin',
            'servico_horas_valid.*' => [
                'required_with:servico_ids_valid',
                'regex:/^\d{2}:\d{2}(:\d{2})?$/'
            ],
            'servico_valor_valid.*' => 'required_with:servico_ids_valid|numeric|min:0',

            // Produtos
            'produto_id' => 'nullable|array',
            'produto_id.*' => 'nullable|exists:produtos,id',
            'qtd_produto' => 'nullable|array',
            'qtd_produto.*' => 'nullable|numeric|min:1',
        ]);

        $servico_reserva = $request->servico_ids[0] ? Servico::with('categoria')->find($request->servico_ids[0]) : null;

        if (!$servico_reserva || strtoupper($servico_reserva->categoria->nome) !== 'HOTEL') {
            return back()->withErrors(['servico_ids.0' => 'Selecione um serviço de hotel como primeiro serviço.'])->withInput();
        }

        try {
            $checkin = Carbon::parse($request->checkin . ' ' . $request->timecheckin);
            $checkout = Carbon::parse($request->checkout . ' ' . $request->timecheckout);

            foreach ($request->servico_datas ?? [] as $data_servico_tmp) {
                if ($data_servico_tmp && Carbon::parse($data_servico_tmp)->gt($checkout)) {
                    return back()->withErrors(['servico_datas.*' => 'Data do serviço não pode ser após o check-out.'])->withInput();
                }
            }

            $diarias = $checkin->copy()->startOfDay()->diffInDays($checkout->copy()->startOfDay());
            $diarias = max($diarias, 1);

            $quarto = Quarto::findOrFail($request->quarto_id);
            if ($quarto->status !== Quarto::STATUS_DISPONIVEL) {
                session()->flash('flash_erro', 'Quarto selecionado não está disponível para reserva.');
                return redirect()->back()->withInput();
            }

            $pet = Animal::findOrFail($request->animal_id);

            $servico_counts = [];
            $servicos_data = [];
            $valor_servicos = 0.0;
            
            foreach ($request->servico_ids ?? [] as $index => $servico_id) {
                if (!$servico_id) {
                    continue;
                }
                $servico = Servico::findOrFail($servico_id);
                $raw_valor_servico = $request->servico_valor[$index] ?? null;
                $raw_valor_servico = $raw_valor_servico !== null ? preg_replace('/[^0-9,\\.-]/', '', (string) $raw_valor_servico) : null;
                $valor_servicos += !empty($raw_valor_servico) ? (float) __convert_value_bd($raw_valor_servico) : 0.0;
                $servico_counts[$servico_id] = ($servico_counts[$servico_id] ?? 0) + 1;

                if ($index === 0) {
                    $data_servico = $request->checkin;
                    $hora_servico = $request->timecheckin;
                    $raw_valor_servico = $request->servico_valor[0] ?? null;
                    $raw_valor_servico = $raw_valor_servico !== null ? preg_replace('/[^0-9,\\.-]/', '', (string) $raw_valor_servico) : null;
                    $valor_servico = !empty($raw_valor_servico) ? (float) __convert_value_bd($raw_valor_servico) : 0.0;
                } else {
                    $data_servico = isset($request->servico_datas[$index - 1]) ? $request->servico_datas[$index - 1] : $request->checkin;
                    $hora_servico = isset($request->servico_horas[$index - 1]) ? $request->servico_horas[$index - 1] : $request->timecheckin;
                    $raw_valor_servico = $request->servico_valor[$index] ?? null;
                    $raw_valor_servico = $raw_valor_servico !== null ? preg_replace('/[^0-9,\\.-]/', '', (string) $raw_valor_servico) : null;
                    $valor_servico = !empty($raw_valor_servico) ? (float) __convert_value_bd($raw_valor_servico) : 0.0;
                }

                $servicos_data[] = [
                    'servico_id'   => $servico_id,
                    'data_servico' => $data_servico,
                    'hora_servico' => $hora_servico,
                    'valor_servico' => $valor_servico
                ];
            }

            $produtos_data = [];
            $valor_produtos = 0;
            $produtos = collect();

            foreach ($request->produto_id ?? [] as $index => $produto_id) {
                if (!$produto_id) {
                    continue;
                }

                $produto = Produto::findOrFail($produto_id);
                $quantidade = (float) str_replace(',', '.', $request->qtd_produto[$index] ?? 1);

                $valor_produtos += $produto->valor_unitario * $quantidade;

                if (isset($produtos_data[$produto_id])) {
                    $produtos_data[$produto_id]['quantidade'] += $quantidade;
                } else {
                    $produtos_data[$produto_id] = ['quantidade' => $quantidade];
                }

                $produtos[$produto_id] = $produto;
            }

            $quarto_data = (object) [
                'quarto_id' => $quarto->id,
                'empresa_id' => $empresa_id,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ];

            $quarto_is_busy = $quarto_service->checkIfQuartoIsBusy($quarto_data);

            if ($quarto_is_busy) {
                session()->flash('flash_erro', 'Não há vagas disponíveis nesse quarto para as datas selecionadas.');
                return redirect()->back()->withInput();
            }

            $hotel = Hotel::create([
                'empresa_id'      => $empresa_id,
                'animal_id'       => $pet->id,
                'cliente_id'      => $pet->cliente_id,
                'quarto_id'       => $quarto->id,
                'colaborador_id'  => $request->colaborador_id,
                'checkin'         => $checkin,
                'checkout'        => $checkout,
                'descricao'       => $request->descricao,
                'diarias'         => $diarias,
                'valor'           => ($valor_servicos) + $valor_produtos,
                'estado'          => 'Agendado',
                'situacao_checklist' => false,
            ]);
            Log::info('HotelController@store created', $hotel->only('id', 'checkin', 'checkout'));

            foreach ($servicos_data as $pivot) {
                $hotel->servicos()->attach($pivot['servico_id'], [
                    'data_servico' => $pivot['data_servico'],
                    'hora_servico' => $pivot['hora_servico'],
                    'valor_servico' => $pivot['valor_servico'],
                ]);
            }
            if (!empty($produtos_data)) {
                $hotel->produtos()->sync($produtos_data);
            }

            $codigoSequencial = OrdemServico::nextCodigoSequencial($empresa_id);

            $ordem = OrdemServico::create(OrdemServico::filterAttributesForTable([
                'descricao'         => 'Ordem de Serviço Avulso',
                'cliente_id'        => $pet->cliente_id,
                'empresa_id'        => $empresa_id,
                'funcionario_id'    => $request->colaborador_id,
                'animal_id'         => $pet->id,
                'plano_id'          => null,
                'hotel_id'          => $hotel->id,
                'usuario_id'        => get_id_user() ?? auth()->id(),
                'codigo_sequencial' => $codigoSequencial,
                'valor'             => $valor_servicos + $valor_produtos,
                'data_inicio'       => $checkin,
                'data_entrega'      => $checkout,
                'estado'            => $codigoSequencial !== null ? 'EA' : 'pendente',
            ]));

            $hotel->update(['ordem_servico_id' => $ordem->id]);

            foreach ($hotel->servicos as $servico) {
                $quantidade = $servico_counts[$servico->id];
                ServicoOs::create([
                    'ordem_servico_id' => $ordem->id,
                    'servico_id'       => $servico->id,
                    'quantidade'       => $quantidade,
                    'valor'            => $servico->pivot->valor_servico ?? 0,
                    'subtotal'         => ($servico->pivot->valor_servico ?? 0) * $quantidade,
                    'desconto'         => 0,
                ]);
            }

            $servico_frete = $hotel->servicos->filter(function ($servico) {
                return $servico->categoria && $servico->categoria->nome === 'FRETE';
            });

            if ($servico_frete->first()) {
                $endereco_cliente_data = [
                    'cep' => $request->cep,
                    'rua' => $request->rua,
                    'bairro' => $request->bairro,
                    'numero' => $request->numero,
                    'complemento' => $request->complemento,

                    'cidade_id' => $request->modal_cidade_id,
                    'hotel_id' => $hotel->id,
                    'cliente_id' => $hotel->cliente_id,
                ];

                $this->hotel_service->updateOrCreateHotelClienteEndereco($hotel->id, $endereco_cliente_data);
            }

            foreach ($produtos as $produtoId => $produto) {
                $quantidade = $produtos_data[$produtoId]['quantidade'];
                ProdutoOs::create([
                    'ordem_servico_id' => $ordem->id,
                    'produto_id'       => $produto->id,
                    'quantidade'       => $quantidade,
                    'valor'            => $produto->valor_unitario ?? 0,
                    'subtotal'         => ($produto->valor_unitario ?? 0) * $quantidade,
                    'desconto'         => 0,
                ]);
            }

            session()->flash('flash_sucesso', 'Reserva cadastrada com sucesso!');
        } catch (\Exception $e) {
            Log::error('HotelController@store exception', ['message' => $e->getMessage()]);
            session()->flash('flash_erro', 'Erro ao cadastrar reserva: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('hoteis.index');
    }

    public function show(string $id)
    {
        return redirect()->route('hoteis.edit', [$id]);
    }

    public function move(Request $request, Hotel $hotel)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $hotel->load('quarto');
        $quarto = $hotel->quarto;
        if ($quarto->status !== Quarto::STATUS_DISPONIVEL) {
            return response()->json([
                'success' => false,
                'message' => 'Quarto indisponível para as datas selecionadas.'
            ], 422);
        }

        $reservasAtivas = Hotel::where('quarto_id', $quarto->id)
            ->where('id', '!=', $hotel->id)
            ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('checkin', [$start, $end])
                    ->orWhereBetween('checkout', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('checkin', '<=', $start)
                            ->where('checkout', '>=', $end);
                    });
            })
            ->count();

        if ($reservasAtivas >= $quarto->capacidade) {
            return response()->json([
                'success' => false,
                'message' => 'Quarto indisponível para as datas selecionadas.'
            ], 422);
        }

        $hotel->update([
            'checkin'  => $start,
            'checkout' => $end,
        ]);

        return response()->json(['success' => true]);
    }


    public function edit(string $id)
    {
        $empresa_id = request()->empresa_id;

        $data = Hotel::with('servicos.categoria')->where('empresa_id', $empresa_id)->findOrFail($id);

        $servicos =  Servico::whereHas('categoria', function ($query) {
            $query->where('nome', 'HOTEL');
        })->get();
        $quartos = Quarto::where('empresa_id', $empresa_id)->get();

        $servicosFormatados = $servicos->mapWithKeys(function ($servico) {
            return [
                $servico->id => $servico->nome . ' (R$ ' . number_format($servico->valor, 2, ',', '.') . ')'
            ];
        });
        
        $reserva = $data->servicos->first(function ($servico) {
            return $servico->categoria && $servico->categoria->nome === 'HOTEL';
        });
        
        $frete = $data->servicos->first(function ($servico) {
            return $servico->categoria && $servico->categoria->nome === 'FRETE';
        });

        $extras_servicos =  $data->servicos->filter(function ($servico) {
            return !$servico->categoria || $servico->categoria->nome !== 'HOTEL' && $servico->categoria->nome !== 'FRETE';
        });

        $data->setRelation('servicos', $extras_servicos);

        $data->hotelClienteEndereco && $data->hotelClienteEndereco = $data->hotelClienteEndereco->load('cidade');

        return view('petshop.hotel.edit', compact('data', 'servicos', 'quartos', 'servicosFormatados', 'reserva', 'frete'));
    }

    public function update(Request $request, $id)
    {
        $quarto_servie = new QuartoService();
        $empresa_id = $request->empresa_id;
        Log::debug('HotelController@update payload', $request->only('checkin', 'timecheckin', 'checkout', 'timecheckout'));

        $request->merge([
            'servico_valor' => array_values(
                array_map(
                    fn ($valor) => __convert_value_bd($valor),
                    array_filter(
                        $request->input('servico_valor', []),
                        fn ($v) => $v !== null && $v !== '' 
                    )
                )
            ),

            'servico_ids_valid' => collect($request->input('servico_ids', []))
                ->slice(1) 
                ->values() 
                ->toArray(),

            'servico_datas_valid' => collect($request->input('servico_datas', []))
                ->slice(1)
                ->filter(fn ($val) => !empty($val))
                ->values()
                ->toArray(),

            'servico_horas_valid' => collect($request->input('servico_horas', []))
                ->slice(1)
                ->filter(fn ($val) => !empty($val))
                ->values()
                ->toArray(),

            'servico_valor_valid' => collect($request->input('servico_valor', []))
                ->slice(1)
                ->filter(fn ($val) => $val !== null && $val !== '')
                ->map(fn ($valor) => __convert_value_bd($valor))
                ->values()
                ->toArray(),
        ]);

        $request->validate([
            'empresa_id' => 'required|integer',
            // Dados da reserva

            'animal_id' => 'required|exists:petshop_animais,id',
            'quarto_id' => 'required|exists:quartos,id',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
            'checkin' => 'required|date',
            'timecheckin' => ['required', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'checkout' => 'required|date|after_or_equal:checkin',
            'timecheckout' => ['required', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'descricao' => 'nullable|string|max:1000',

            // Serviço de reserva
            'servico_ids' => 'required|array',
            'servico_ids.0' => 'required|exists:servicos,id',

            // Serviços extras
            'servico_ids.*' => 'nullable|exists:servicos,id',
            'servico_datas_valid.*' => 'required_with:servico_ids_valid|date|after_or_equal:checkin',
            'servico_horas_valid.*' => [
                'required_with:servico_ids_valid',
                'regex:/^\d{2}:\d{2}(:\d{2})?$/'
            ],
            'servico_valor_valid.*' => 'required_with:servico_ids_valid|numeric|min:0',

            // Produtos
            'produto_id' => 'nullable|array',
            'produto_id.*' => 'nullable|exists:produtos,id',
            'qtd_produto' => 'nullable|array',
            'qtd_produto.*' => 'nullable|numeric|min:1',
        ]);

        $servico_reserva = $request->servico_ids[0] ? Servico::with('categoria')->find($request->servico_ids[0]) : null;
        if (!$servico_reserva || strtoupper($servico_reserva->categoria->nome) !== 'HOTEL') {
            return back()->withErrors(['servico_ids.0' => 'Selecione um serviço de hotel como primeiro serviço.'])->withInput();
        }

        try {
            $hotel = Hotel::findOrFail($id);
            $checkin = Carbon::parse($request->checkin . ' ' . $request->timecheckin);
            $checkout = Carbon::parse($request->checkout . ' ' . $request->timecheckout);

            foreach ($request->servico_datas ?? [] as $data_servico_tmp) {
                if ($data_servico_tmp && Carbon::parse($data_servico_tmp)->gt($checkout)) {
                    return back()->withErrors(['servico_datas.*' => 'Data do serviço não pode ser após o check-out.'])->withInput();
                }
            }

            $diarias = $checkin->diffInDays($checkout);
            $diarias = $diarias === 0 ? 1 : $diarias;

            $quarto = Quarto::findOrFail($request->quarto_id);
            if ($quarto->status !== Quarto::STATUS_DISPONIVEL) {
                session()->flash('flash_erro', 'Quarto selecionado não está disponível para reserva.');
                return redirect()->back()->withInput();
            }

            $servico_counts = [];
            $servicos_data = [];
            $valor_servicos = 0;

            foreach ($request->servico_ids ?? [] as $index => $servicoId) {
                if (!$servicoId) {
                    continue;
                }
                $servico = Servico::findOrFail($servicoId);
                $valor_servicos += isset($request->servico_valor[$index]) ? __convert_value_bd($request->servico_valor[$index]) : 0;
                $servico_counts[$servicoId] = ($servico_counts[$servicoId] ?? 0) + 1;

                if ($index === 0) {
                    $data_servico = $request->checkin;
                    $hora_servico = $request->timecheckin;
                    $valor_servico = __convert_value_bd($request->servico_valor[0]) ?? $servico->valor ?? 0;
                } else {
                    $data_servico = isset($request->servico_datas[$index - 1]) ? $request->servico_datas[$index - 1] : $request->checkin;
                    $hora_servico = isset($request->servico_horas[$index - 1]) ? $request->servico_horas[$index - 1] : $request->timecheckin;
                    $valor_servico = isset($request->servico_valor[$index]) ? __convert_value_bd($request->servico_valor[$index]) : 0;
                }

                $servicos_data[] = [
                    'servico_id'   => $servicoId,
                    'data_servico' => $data_servico,
                    'hora_servico' => $hora_servico,
                    'valor_servico' => $valor_servico
                ];
            }

            $produtos_data = [];
            $valor_produtos = 0;
            $produtos = collect();

            foreach ($request->produto_id ?? [] as $index => $produtoId) {
                if (!$produtoId) {
                    continue;
                }

                $produto = Produto::findOrFail($produtoId);
                $quantidade = (float) str_replace(',', '.', $request->qtd_produto[$index] ?? 1);

                $valor_produtos += $produto->valor_unitario * $quantidade;

                if (isset($produtos_data[$produtoId])) {
                    $produtos_data[$produtoId]['quantidade'] += $quantidade;
                } else {
                    $produtos_data[$produtoId] = ['quantidade' => $quantidade];
                }

                $produtos[$produtoId] = $produto;
            }

            $quarto_data = (object) [
                'quarto_id' => $request->quarto_id,
                'empresa_id' => $empresa_id,
                'checkin' => $checkin,
                'checkout' => $checkout,
                'reserva_id' => $hotel->id
            ];
            $is_busy = $quarto_servie->checkIfQuartoIsBusy($quarto_data);

            if ($is_busy) {
                session()->flash('flash_erro', 'Não há vagas disponíveis nesse quarto para as datas selecionadas.');
                return redirect()->back()->withInput();
            }

            $hotel->update([
                'empresa_id' => $empresa_id,
                'animal_id' => $request->animal_id,
                'cliente_id' => Animal::findOrFail($request->animal_id)->cliente_id,
                'quarto_id' => $request->quarto_id,
                'colaborador_id' => $request->colaborador_id,
                'checkin' => $checkin,
                'checkout' => $checkout,
                'descricao' => $request->descricao,
                'diarias' => $diarias,
            ]);
            Log::info('HotelController@update updated', $hotel->only('id', 'checkin', 'checkout'));

            $hotel->servicos()->detach();
            foreach ($servicos_data as $pivot) {
                $hotel->servicos()->attach($pivot['servico_id'], [
                    'data_servico' => $pivot['data_servico'],
                    'hora_servico' => $pivot['hora_servico'],
                    'valor_servico' => $pivot['valor_servico'],
                ]);
            }

            
            $servico_frete = $hotel->servicos->filter(function ($servico) {
                return $servico->categoria && $servico->categoria->nome === 'FRETE';
            });

            if ($servico_frete->first()) {
                $endereco_cliente_data = [
                    'cep' => $request->cep,
                    'rua' => $request->rua,
                    'bairro' => $request->bairro,
                    'numero' => $request->numero,
                    'complemento' => $request->complemento,

                    'cidade_id' => $request->modal_cidade_id,
                    'hotel_id' => $hotel->id,
                    'cliente_id' => $hotel->cliente_id,
                ];

                $this->hotel_service->updateOrCreateHotelClienteEndereco($hotel->id, $endereco_cliente_data);
            } else {
                if (isset($hotel->hotelClienteEndereco)) {
                    $hotel->hotelClienteEndereco->delete();
                }
            }

            $hotel->produtos()->sync($produtos_data);

            $ordem = $hotel->ordemServico()->first();

            if ($ordem) {
                $ordem->update([
                    'valor' => $valor_servicos + $valor_produtos,
                ]);

                ServicoOs::where('ordem_servico_id', $ordem->id)->delete();
                ProdutoOs::where('ordem_servico_id', $ordem->id)->delete();

                foreach ($hotel->servicos as $servico) {
                    $quantidade = $servico_counts[$servico->id];
                    ServicoOs::create([
                        'ordem_servico_id' => $ordem->id,
                        'servico_id'       => $servico->id,
                        'quantidade'       => $quantidade,
                        'valor'            => $servico->pivot->valor_servico ?? 0,
                        'subtotal'         => ($servico->pivot->valor_servico ?? 0) * $quantidade,
                        'desconto'         => 0,
                    ]);
                }

                foreach ($produtos as $produtoId => $produto) {
                    $quantidade = $produtos_data[$produtoId]['quantidade'];
                    ProdutoOs::create([
                        'ordem_servico_id' => $ordem->id,
                        'produto_id'       => $produto->id,
                        'quantidade'       => $quantidade,
                        'valor'            => $produto->valor_unitario ?? 0,
                        'subtotal'         => ($produto->valor_unitario ?? 0) * $quantidade,
                        'desconto'         => 0,
                    ]);
                }
            }

            if ($hotel->estado != "Agendado") {

                $ordens = OrdemServico::where('plano_id', $hotel->plano_id)
                    ->where('empresa_id', $empresa_id)
                    ->whereMonth('data_inicio', date('m', strtotime($checkin)))
                    ->whereYear('data_inicio', date('Y', strtotime($checkin)))
                    ->get();

                foreach ($ordens as $ordem) {
                    $ordem->update([
                        'data_inicio' => $ordem->getOriginal('data_inicio'), // força gravar o mesmo valor original
                        'estado' => 'EA',
                    ]);
                }
            }

            $this->hotel_service->updateValorTotal($hotel->id);
            $this->hotel_service->updateContaReceberDataVencimento($hotel->id);

            session()->flash('flash_sucesso', 'Reserva atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('HotelController@update exception', ['message' => $e->getMessage()]);
            session()->flash('flash_erro', 'Erro ao atualizar reserva: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('hoteis.index');
    }

    public function printEnderecoEntrega($id){
        $hotel = Hotel::findOrFail($id);
        
        $item = $hotel->hotelClienteEndereco;

        $height = 350;
        
        $config = Empresa::where('id', $item->empresa_id)->first();

        $p = view('petshop.hotel.cupom_entrega', compact('config', 'item', 'hotel'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $domPdf->setPaper([0, 0, 220, $height]);
        $domPdf->render();  

        $domPdf->stream("Endereço de entrega.pdf", array("Attachment" => false));
    }

    public function destroy(string $id)
    {
        try {
            $empresa_id = request()->empresa_id;

            // Busca o hotel garantindo que pertence à empresa
            $hotel = Hotel::where('empresa_id', $empresa_id)->findOrFail($id);

            // Busca as ordens de serviço ligadas a este hotel
            $ordem = $hotel->ordemServico;

            if (isset($ordem)) {
                ServicoOs::where('ordem_servico_id', $ordem->id)->delete();

                $ordem->delete();
            }

            $hotel->delete();

            session()->flash('flash_sucesso', 'Reserva excluída com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao excluir hotel: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('hoteis.index');
    }
}
