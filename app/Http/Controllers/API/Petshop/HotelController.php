<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Hotel;
use App\Models\Petshop\Quarto;
use App\Models\Produto;
use App\Models\ProdutoOs;
use App\Models\Servico;
use App\Models\ServicoOs;
use App\Services\Notificacao\HotelNotificacaoService;
use App\Services\Petshop\HotelService;
use App\Services\QuartoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    protected $hotel_service;
    protected $quarto_service;

    public function __construct(HotelService $hotel_service, QuartoService $quarto_service)
    {
        $this->hotel_service = $hotel_service;
        $this->quarto_service = $quarto_service;
    }

    public function storeHotel (Request $request)
    {
        $this->_validate($request);

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

        try {
            $checkin = Carbon::parse($request->checkin . ' ' . $request->timecheckin);
            $checkout = Carbon::parse($request->checkout . ' ' . $request->timecheckout);

            foreach ($request->servico_datas ?? [] as $data_servico_tmp) {
                if ($data_servico_tmp && Carbon::parse($data_servico_tmp)->gt($checkout)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data do serviço não pode ser após o check out.'
                    ], 400);
                }
            }

            $diarias = $checkin->copy()->startOfDay()->diffInDays($checkout->copy()->startOfDay());
            $diarias = max($diarias, 1);

            $quarto = Quarto::findOrFail($request->quarto_id);
            if ($quarto->status !== Quarto::STATUS_DISPONIVEL) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quarto indisponível para o periodo selecionado.'
                ], 403);
            }

            $pet = Animal::findOrFail($request->animal_id);

            $servico_counts = [];
            $servicos_data = [];
            $valor_servicos = 0;
            
            foreach ($request->servico_ids ?? [] as $index => $servico_id) {
                if (!$servico_id) {
                    continue;
                }
                $servico = Servico::findOrFail($servico_id);
                $valor_servicos += __convert_value_bd($request->servico_valor[$index]) ?? 0;
                $servico_counts[$servico_id] = ($servico_counts[$servico_id] ?? 0) + 1;

                if ($index === 0) {
                    $data_servico = $request->checkin;
                    $hora_servico = $request->timecheckin;
                    $valor_servico = $request->servico_valor[0] ? __convert_value_bd($request->servico_valor[0]) : 0;
                } else {
                    $data_servico = isset($request->servico_datas[$index - 1]) ? $request->servico_datas[$index - 1] : $request->checkin;
                    $hora_servico = isset($request->servico_horas[$index - 1]) ? $request->servico_horas[$index - 1] : $request->timecheckin;
                    $valor_servico = $request->servico_valor[$index] ? __convert_value_bd($request->servico_valor[$index]) : 0;
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

            $quarto_is_busy = $this->quarto_service->checkIfQuartoIsBusy($quarto_data);

            if ($quarto_is_busy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há vagas disponíveis nesse quarto para as datas selecionadas.'
                ], 400);
            }

            $hotel = DB::transaction(function () use (
                $request,
                $empresa_id,
                $pet,
                $quarto,
                $checkin,
                $checkout,
                $diarias,
                $valor_servicos,
                $valor_produtos,
                $servicos_data,
                $servico_counts,
                $produtos,
                $produtos_data
            ) {
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

                return $hotel;
            });

            $hotelParaNotificacao = $hotel->fresh([
                'empresa',
                'cliente',
                'animal',
                'quarto',
                'servicos',
                'produtos',
            ]);

            (new HotelNotificacaoService())->nova($hotelParaNotificacao ?? $hotel);

            return response()->json([
                'success' => true,
                'message' => 'Reserva agendada com sucesso!'
            ], 200);

        } catch (\Exception $e) {
            Log::error('HotelController@store exception', ['message' => $e->getMessage()]);
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao agendar a reserva.',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    public function updateHotel (Request $request, $id)
    {
        $quarto_service = new QuartoService();
        $empresa_id = $request->empresa_id;
        Log::debug('HotelController@update payload', $request->only('checkin', 'timecheckin', 'checkout', 'timecheckout'));

        $request->merge([
            'servico_valor' => array_map(
                fn ($valor) => __convert_value_bd($valor),
                array_filter(
                    $request->input('servico_valor', []), fn ($v) => !empty($v)
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
            return response()->json([
                'success' => false,
                'message' => 'Selecione um serviço de hotel como serviço de reserva.'
            ], 401);
        }

        try {
            $hotel = Hotel::findOrFail($id);
            $checkin = Carbon::parse($request->checkin . ' ' . $request->timecheckin);
            $checkout = Carbon::parse($request->checkout . ' ' . $request->timecheckout);

            foreach ($request->servico_datas ?? [] as $data_servico_tmp) {
                if ($data_servico_tmp && Carbon::parse($data_servico_tmp)->gt($checkout)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data do serviço não pode ser após o check-out.'
                    ], 401);
                }
            }

            $diarias = $checkin->diffInDays($checkout);
            $diarias = $diarias === 0 ? 1 : $diarias;

            $servico_counts = [];
            $servicos_data = [];
            $valor_servicos = 0;
            foreach ($request->servico_ids ?? [] as $index => $servicoId) {
                if (!$servicoId) {
                    continue;
                }
                $servico = Servico::findOrFail($servicoId);
                $valor_servicos += $request->servico_valor[$index] ?? 0;
                $servico_counts[$servicoId] = ($servico_counts[$servicoId] ?? 0) + 1;

                if ($index === 0) {
                    $data_servico = $request->checkin;
                    $hora_servico = $request->timecheckin;
                    $valor_servico = $request->servico_valor[$index] ?? 0;
                } else {
                    $data_servico = $request->servico_datas[$index - 1] ?? $request->checkin;
                    $hora_servico = $request->servico_horas[$index - 1] ?? null;
                    $valor_servico = $request->servico_valor[$index] ?? 0;
                }

                $servicos_data[] = [
                    'servico_id'   => $servicoId,
                    'data_servico' => $data_servico,
                    'hora_servico' => $hora_servico,
                    'valor_servico' => $valor_servico
                ];
            }
            $servicos = Servico::whereIn('id', array_keys($servico_counts))->get();
            

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

            $is_busy = false;

            if (
                $checkin != $hotel->checkin ||
                $checkout != $hotel->checkout
            ) {
                $quarto_data = (object) [
                    'quarto_id' => $request->quarto_id,
                    'empresa_id' => $empresa_id,
                    'checkin' => $checkin,
                    'checkout' => $checkout,
                    'reserva_id' => $hotel->id
                ];
                $is_busy = $quarto_service->checkIfQuartoIsBusy($quarto_data);
            }

            if ($is_busy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há vagas disponíveis nesse quarto para as datas selecionadas.'
                ]);
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
                'valor' => $valor_servicos + $valor_produtos,
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
            $hotel->produtos()->sync($produtos_data);

            $ordem = $hotel->ordemServico()->first();

            if ($ordem) {
                $ordem->update([
                    'valor' => $valor_servicos + $valor_produtos,
                ]);

                ServicoOs::where('ordem_servico_id', $ordem->id)->delete();
                ProdutoOs::where('ordem_servico_id', $ordem->id)->delete();

                foreach ($servicos as $servico) {
                    $quantidade = $servico_counts[$servico->id];
                    ServicoOs::create([
                        'ordem_servico_id' => $ordem->id,
                        'servico_id'       => $servico->id,
                        'quantidade'       => $quantidade,
                        'valor'            => $servico->valor ?? 0,
                        'subtotal'         => ($servico->valor ?? 0) * $quantidade,
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
                        'data_inicio' => $ordem->getOriginal('data_inicio'),
                        'estado' => 'EA',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Reserva atualizada com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            Log::error('HotelController@update exception', ['message' => $e->getMessage()]);
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateReservaHotel (Request $request, $id)
    {
        $request->validate([
            'reserva_checkin' => 'required|date',
            'reserva_checkout' => 'required|date',
            'reserva_timecheckin' => 'required',
            'reserva_timecheckout' => 'required',
            'reserva_quarto_id' => 'required|integer',
        ]);

        try {
            $hotel = Hotel::findOrFail($id);

            $checkin = Carbon::parse($request->reserva_checkin . ' ' . $request->reserva_timecheckin);
            $checkout = Carbon::parse($request->reserva_checkout . ' ' . $request->reserva_timecheckout);

            $hotel->update([
                'checkin' => $checkin,
                'checkout' => $checkout,
                'quarto_id' => $request->reserva_quarto_id
            ]);

            $this->hotel_service->updateContaReceberDataVencimento($hotel->id);

            return response()->json([
                'success' => true,
                'message' => 'Reserva atualizada com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar a reserva...',
                'exception' => $e->getMessage() 
            ], 500);
        }
    }

    public function updateServicosExtras (Request $request, $id)
    {
        $request->merge([
            'extra_servico_valor' => array_values(
                array_map(
                    fn ($valor) => __convert_value_bd($valor),
                    array_filter(
                        $request->input('extra_servico_valor', []),
                        fn ($v) => $v !== null && $v !== '' 
                    )
                )
            ),
        ]);
        
        $request->validate([
            'extra_servico_ids' => 'nullable|array|exists:servicos,id',

            'extra_servico_datas' => 'required_with:extra_servico_ids|array',
            'extra_servico_datas.*' => 'required_with:extra_servico_ids|date',
            
            'extra_servico_horas' => 'required_with:extra_servico_ids|array',
            'extra_servico_horas.*' => 'regex:/^\d{2}:\d{2}(:\d{2})?$/',
            
            'extra_servico_valor' => 'required_with:extra_servico_ids|array',
            'extra_servico_valor.*' => 'numeric',
        ]);

        try {
            $hotel = Hotel::findOrFail($id);

            $servico_reserva = $hotel->servicos
            ->filter(fn ($servico) => $servico->categoria->nome === 'HOTEL')
            ->values();

            $servico_frete = $hotel->servicos
            ->filter(fn ($servico) => $servico->categoria->nome === 'FRETE')
            ->values();

            $servicos_fixos = $servico_reserva->pluck('id')
            ->merge($servico_frete->pluck('id'))
            ->toArray();

            $total_servicos = 0;

            $total_servicos += $servico_reserva->sum('valor');
            $total_servicos += $servico_frete->sum('valor');
            
            $servicos_extras = [];
            if (isset($request->extra_servico_ids)) {
                foreach ($request->extra_servico_ids as $id => $servico_id) {
                    $servicos_extras[$servico_id] = [
                        'data_servico' => $request->extra_servico_datas[$id],
                        'hora_servico' => $request->extra_servico_horas[$id],
                        'valor_servico' => $request->extra_servico_valor[$id],
                    ];

                    $total_servicos += $request->extra_servico_valor[$id];
                }
            }

            $hotel->servicos()->sync($servicos_extras + $servicos_fixos);

            $total_produtos = 0;

            if (isset($hotel->produtos)) {
                foreach ($hotel->produtos as $produto) {
                    $total_produtos += $produto->pivot->quantidade * $produto->valor_unitario;
                }
            }

            $valor_total = $total_servicos + $total_produtos;

            $ordem_servico = $hotel->ordemServico;
            if ($ordem_servico) {
                ServicoOs::where('ordem_servico_id', $ordem_servico->id)
                    ->whereHas('servico.categoria', fn ($query) =>
                        $query->whereNot('nome', 'HOTEL')->whereNot('nome', 'FRETE')
                    )
                    ->delete();

                if (isset($request->extra_servico_ids)) {
                    $updated_hotel_servicos = Servico::whereIn('id', $request->extra_servico_ids)->get();

                    foreach ($updated_hotel_servicos as $index => $servico) {
                        ServicoOs::create([
                            'ordem_servico_id' => $ordem_servico->id,
                            'servico_id'       => $servico->id,
                            'quantidade'       => 1,
                            'valor'            => $request->extra_servico_valor[$index] ?? 0,
                            'subtotal'         => $request->extra_servico_valor[$index] ?? 0,
                            'desconto'         => 0,
                        ]);
                    }
                }

                $hotel->ordemServico->update([
                    'valor' => $valor_total,
                    'total_sem_desconto' => $valor_total
                ]);
            }

            $this->hotel_service->updateValorTotal($hotel->id);

            return response()->json([
                'success' => true,
                'message' => 'Serviços atualizados com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar a reserva...',
                'exception' => $e->getMessage() 
            ], 500);
        }
    }

    public function updateServicoFrete (Request $request, $id)
    {
        $request->merge([
            'servico_frete_valor' => __convert_value_bd($request->servico_frete_valor),
        ]);
        
        $request->validate([
            'servico_frete' => 'nullable|numeric|exists:servicos,id',
            'servico_frete_valor' => 'required_with:servico_frete|numeric',
        ]);

        try {
            $hotel = Hotel::findOrFail($id);

            $servico_reserva = $hotel->servicos
            ->filter(fn ($servico) => $servico->categoria->nome === 'HOTEL')
            ->values();

            $servicos_extras = $hotel->servicos
            ->filter(fn ($servico) => $servico->categoria->nome != 'HOTEL' && $servico->categoria->nome != 'FRETE')
            ->values(); 


            $servicos_fixos = $servico_reserva->pluck('id')
            ->merge($servicos_extras->pluck('id'))
            ->toArray();

            $total_servicos = 0;

            $total_servicos += $servico_reserva->sum('valor');
            $total_servicos += $servicos_extras->sum('valor');
            
            $servico_frete = [];
            if (isset($request->servico_frete)) {
                $servico_frete[$request->servico_frete] = [
                    'data_servico' => $hotel->checkin->format('Y-m-d'),
                    'hora_servico' => $hotel->checkin->format('H:i'),
                    'valor_servico' => $request->servico_frete_valor,
                ];

                $total_servicos += $request->servico_frete_valor;
            } else {
                if (isset($hotel->hotelClienteEndereco)) {
                    $hotel->hotelClienteEndereco()->delete();
                }
            }

            $hotel->servicos()->sync($servico_frete + $servicos_fixos);

            $total_produtos = 0;

            if (isset($hotel->produtos)) {
                foreach ($hotel->produtos as $produto) {
                    $total_produtos += $produto->pivot->quantidade * $produto->valor_unitario;
                }
            }

            $valor_total = $total_servicos + $total_produtos;

            $this->hotel_service->updateValorTotal($hotel->id);

            $ordem_servico = $hotel->ordemServico;
            if ($ordem_servico) {
                ServicoOs::where('ordem_servico_id', $ordem_servico->id)
                    ->whereHas('servico.categoria', fn ($query) =>
                        $query->where('nome', 'FRETE')
                    )
                ->delete();

                if (isset($request->servico_frete)) {
                    ServicoOs::create([
                        'ordem_servico_id' => $ordem_servico->id,
                        'servico_id'       => $request->servico_frete,
                        'quantidade'       => 1,
                        'valor'            => $request->servico_frete_valor ?? 0,
                        'subtotal'         => $request->servico_frete_valor ?? 0,
                        'desconto'         => 0,
                    ]);
                }

                $hotel->ordemServico->update([
                    'valor' => $valor_total,
                    'total_sem_desconto' => $valor_total
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Serviços atualizados com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar a reserva...',
                'exception' => $e->getMessage() 
            ], 500);
        }
    }

    public function updateProdutos (Request $request, $id)
    {
        $request->merge([
            'agendamento_valor_unitario_produto' => array_values(
                array_map(
                    fn ($valor) => __convert_value_bd($valor),
                    array_filter(
                        $request->input('agendamento_valor_unitario_produto', []),
                        fn ($v) => $v !== null && $v !== '' 
                    )
                )
            ),
        ]);
        
        $request->validate([
            'agendamento_produto_id' => 'nullable|array|exists:produtos,id',

            'agendamento_qtd_produto' => 'required_with:agendamento_produto_id|array',
            'agendamento_qtd_produto.*' => 'numeric',
        ]);

        try {
            $hotel = Hotel::findOrFail($id);

            $produtos = [];
            if (isset($request->agendamento_produto_id)) {
                foreach ($request->agendamento_produto_id as $id => $produto_id) {
                    $produtos[$produto_id] = [
                        'quantidade' => $request->agendamento_qtd_produto[$id],
                    ];
                }
            }

            $hotel->produtos()->sync($produtos);

            $ordem_servico = $hotel->ordemServico;

            if ($ordem_servico) {
                ProdutoOs::where('ordem_servico_id', $ordem_servico->id)->delete();

                if (isset($request->agendamento_produto_id)) {
                    $updated_hotel_produtos = Produto::whereIn('id', $request->agendamento_produto_id)->get();

                    foreach ($updated_hotel_produtos as $index => $produto) {
                        ProdutoOs::create([
                            'ordem_servico_id' => $ordem_servico->id,
                            'produto_id'       => $produto->id,
                            'quantidade'       => $request->agendamento_qtd_produto[$index],
                            'valor'            => $produto->valor_unitario ?? 0,
                            'subtotal'         => (($produto->valor_unitario ?? 0) * ($request->agendamento_qtd_produto[$index] ?? 0)) ?? 0,
                            'desconto'         => 0,
                        ]);
                    }
                }
            }

            $this->hotel_service->updateValorTotal($hotel->id);

            return response()->json([
                'success' => true,
                'message' => 'Produtos atualizados com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar os produtos...',
                'exception' => $e->getMessage() 
            ], 500);
        }
    }
}
