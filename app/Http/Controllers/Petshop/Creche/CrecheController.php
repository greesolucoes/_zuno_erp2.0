<?php

namespace App\Http\Controllers\Petshop\Creche;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Creche;
use App\Models\Petshop\Turma;
use App\Models\Produto;
use App\Models\ProdutoOs;
use App\Models\Servico;
use App\Models\ServicoOs;
use App\Services\Petshop\CrecheService;
use App\Services\TurmaService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrecheController extends Controller
{
    private TurmaService $turma_service;
    protected CrecheService $creche_service;
     
    public function __construct(
        TurmaService $turma_service,
        CrecheService $creche_service
    ) {
        $this->turma_service = $turma_service;
        $this->creche_service = $creche_service;
    }

    public function index(Request $request)
    {
        $empresa_id = request()->empresa_id;
        $pesquisa = $request->input('pesquisa');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $created_at_start_date = $request->input('created_at_start_date');
        $created_at_end_date = $request->input('created_at_end_date');
        $turma_id = $request->input('turma_id');
        $estado = $request->input('estado');

        $data = Creche::where('empresa_id', $empresa_id)
            ->with(['animal', 'cliente', 'turma'])
            ->when($pesquisa, function ($q) use ($pesquisa) {
                $q->where(function ($sub) use ($pesquisa) {
                    $sub->whereHas('animal', fn($q) => $q->where('petshop_animais.nome', 'like', "%{$pesquisa}%"))
                        ->orWhereHas('cliente', fn($q) => $q->where('clientes.razao_social', 'like', "%{$pesquisa}%"));
                });
            })
            ->when($start_date, function ($q) use ($start_date) {
                $q->whereDate('data_entrada', '>=', $start_date);
            })
            ->when($end_date, function ($q) use ($end_date) {
                $q->whereDate('data_saida', '<=', $end_date);
            })
            ->when($created_at_start_date, function ($q) use ($created_at_start_date) {
                $q->whereDate('created_at', '>=', $created_at_start_date);
            })
            ->when($created_at_end_date, function ($q) use ($created_at_end_date) {
                $q->whereDate('data_saida', '<=', $created_at_end_date);
            })
            ->when($turma_id, function ($q) use ($turma_id) {
                $q->where('turma_id', $turma_id);
            })
            ->when($estado, function ($q) use ($estado) {
                $q->where('estado', $estado);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINACAO'))->appends($request->all());

        $contagemPorTurma = Creche::where('empresa_id', $empresa_id)
            ->select('turma_id', DB::raw('count(distinct animal_id) as total'))
            ->groupBy('turma_id')
            ->pluck('total', 'turma_id');

        $servicos = Servico::whereHas('categoria', function ($q) {
            $q->where('nome', 'CRECHE');
        })->get();

        $data->map(function ($creche) {
            $creche->data_entrada = Carbon::parse($creche->data_entrada);
            $creche->data_saida = Carbon::parse($creche->data_saida);
        });

        $turmas = Turma::where('empresa_id', $empresa_id)->get();

        return view('petshop.creche.index', compact('data', 'servicos', 'contagemPorTurma', 'turmas'));
    }

    public function create()
    {
        return view('petshop.creche.create');
    }

    public function store(Request $request)
    {
        $empresa_id = $request->empresa_id ?? (session('user_logged')['empresa'] ?? null);
        $request->merge(['empresa_id' => $empresa_id]);
	            
        $this->_validate($request);

        $servico_reserva = $request->servico_ids[0] ? Servico::with('categoria')->find($request->servico_ids[0]) : null;

        if (!$servico_reserva || strtoupper($servico_reserva->categoria->nome) !== 'CRECHE') {
            return back()->withErrors(['servico_ids.0' => 'Selecione um serviço de creche como primeiro serviço.'])->withInput();
        }

        try {
            $data_entrada = Carbon::parse($request->data_entrada.' '.$request->horario_entrada);
            $data_saida = Carbon::parse($request->data_saida.' '.$request->horario_saida);

            $pet = Animal::findOrFail($request->animal_id);

            $turma = Turma::findOrFail($request->turma_id);
            if ($turma->status !== Turma::STATUS_DISPONIVEL) {
                session()->flash('flash_erro', 'Turma selecionada não está disponível para reserva.');
                return redirect()->back()->withInput();
            }

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
                    $data_servico = $request->data_entrada;
                    $hora_servico = $request->horario_entrada;
                    $raw_valor_servico = $request->servico_valor[0] ?? null;
                    $raw_valor_servico = $raw_valor_servico !== null ? preg_replace('/[^0-9,\\.-]/', '', (string) $raw_valor_servico) : null;
                    $valor_servico = !empty($raw_valor_servico) ? (float) __convert_value_bd($raw_valor_servico) : 0.0;
                } else {
                    $data_servico = isset($request->servico_datas[$index - 1]) ? $request->servico_datas[$index - 1] : $request->data_entrada;
                    $hora_servico = isset($request->servico_horas[$index - 1]) ? $request->servico_horas[$index - 1] : $request->horario_entrada;
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
            $servicos = Servico::whereIn('id', array_keys($servico_counts))->get();

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

            $creche = Creche::create([
                'empresa_id' => $empresa_id,
                'animal_id' => $pet->id,
                'cliente_id' => $pet->cliente_id,
                'turma_id' => $request->turma_id,
                'colaborador_id' => $request->colaborador_id,
                'data_entrada' => $data_entrada,
                'data_saida' => $data_saida,
                'descricao' => $request->descricao,
                'valor' => $valor_servicos + $valor_produtos,
                'estado' => 'agendado',
            ]);

            foreach ($servicos_data as $pivot) {
                $creche->servicos()->attach($pivot['servico_id'], [
                    'data_servico' => $pivot['data_servico'],
                    'hora_servico' => $pivot['hora_servico'],
                    'valor_servico' => $pivot['valor_servico'],
                ]);
            }
            if (! empty($produtos_data)) {
                $creche->produtos()->sync($produtos_data);
            }

            $codigo_sequencial = OrdemServico::nextCodigoSequencial($empresa_id);

            $ordem = OrdemServico::create(OrdemServico::filterAttributesForTable([
                'descricao'         => 'Ordem de Serviço Creche',
                'cliente_id'        => $pet->cliente_id,
                'empresa_id'        => $empresa_id,
                'funcionario_id'    => $request->colaborador_id,
                'animal_id'         => $pet->id,
                'creche_id'         => $creche->id,
                'usuario_id'        => get_id_user() ?? auth()->id(),
                'codigo_sequencial' => $codigo_sequencial,
                'valor'             => $valor_servicos + $valor_produtos,
                'data_inicio'       => $data_entrada,
                'data_entrega'      => $data_saida,
                'estado'            => $codigo_sequencial !== null ? 'AG' : 'pendente',
            ]));

            $creche->update(['ordem_servico_id' => $ordem->id]);

            foreach ($creche->servicos as $servico) {
                $quantidade = $servico_counts[$servico->id];
                ServicoOs::create([
                    'ordem_servico_id' => $ordem->id,
                    'servico_id' => $servico->id,
                    'quantidade' => $quantidade,
                    'valor' => $servico->pivot->valor_servico ?? 0,
                    'subtotal' => ($servico->pivot->valor_servico ?? 0) * $quantidade,
                    'desconto' => 0,
                ]);
            }

            $servico_frete = $creche->servicos->filter(function ($servico) {
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
                    'creche_id' => $creche->id,
                    'cliente_id' => $creche->cliente_id,
                ];

                $this->creche_service->updateOrCreateCrecheClienteEndereco($creche->id, $endereco_cliente_data);
            }

            foreach ($produtos as $produto_id => $produto) {
                $quantidade = $produtos_data[$produto_id]['quantidade'];
                ProdutoOs::create([
                    'ordem_servico_id' => $ordem->id,
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'valor' => $produto->valor_unitario ?? 0,
                    'subtotal' => ($produto->valor_unitario ?? 0) * $quantidade,
                    'desconto' => 0,
                ]);
            }

            session()->flash('flash_sucesso', 'Reserva cadastrada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao salvar reserva: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('creches.index');
    }

    public function show(string $id)
    {
        return redirect()->route('creches.edit', [$id]);
    }

    public function move(Request $request, Creche $creche)
    {
        $request->validate([
            'start' => 'required|date',
        ]);

        $start = Carbon::parse($request->start);
        $creche->load('turma');
        $turma = $creche->turma;
        if ($turma->status !== Turma::STATUS_DISPONIVEL) {
            return response()->json([
                'success' => false,
                'message' => 'Turma indisponível para a data selecionada.'
            ], 422);
        }
        $reservasAtivas = Creche::where('turma_id', $turma->id)
            ->where('id', '!=', $creche->id)
            ->whereIn(DB::raw('LOWER(estado)'), ['agendado', 'em_andamento'])
            ->whereDate('data', $start->toDateString())
            ->count();
        if ($reservasAtivas >= $turma->capacidade) {
            return response()->json([
                'success' => false,
                'message' => 'Turma indisponível para a data selecionada.'
            ], 422);
        }
        $creche->update(['data' => $start]);

        return response()->json(['success' => true]);
    }

    public function edit(string $id)
    {
        $empresa_id = request()->empresa_id;
        $data = Creche::where('empresa_id', $empresa_id)->with(['servicos', 'produtos'])->findOrFail($id);
        
        $reserva = $data->servicos->first(function ($servico) {
            return $servico->categoria && $servico->categoria->nome === 'CRECHE';
        });
        
        $frete = $data->servicos->first(function ($servico) {
            return $servico->categoria && $servico->categoria->nome === 'FRETE';
        });

        $extras_servicos =  $data->servicos->filter(function ($servico) {
            return !$servico->categoria || $servico->categoria->nome !== 'CRECHE' && $servico->categoria->nome !== 'FRETE';
        });

        $data->data_entrada = Carbon::parse($data->data_entrada);
        $data->data_saida = Carbon::parse($data->data_saida); 
        $data->setRelation('servicos', $extras_servicos);

        $data->crecheClienteEndereco && $data->crecheClienteEndereco = $data->crecheClienteEndereco->load('cidade');

        return view('petshop.creche.edit', compact('data', 'reserva', 'frete', 'extras_servicos'));
    }

    public function update(Request $request, string $id)
    {
        $empresa_id = $request->empresa_id ?? (session('user_logged')['empresa'] ?? null);
        $request->merge(['empresa_id' => $empresa_id]);

        $this->_validate($request);

        try {
            $data_entrada = Carbon::parse($request->data_entrada.' '.$request->horario_entrada);
            $data_saida = Carbon::parse($request->data_saida.' '.$request->horario_saida);

            $creche = Creche::where('empresa_id', $empresa_id)->findOrFail($id);
            $pet = Animal::findOrFail($request->animal_id);
            $turma = Turma::findOrFail($request->turma_id);
            if ($turma->status !== Turma::STATUS_DISPONIVEL) {
                session()->flash('flash_erro', 'Turma selecionada não está disponível para reserva.');
                return redirect()->back()->withInput();
            }

            $servico_counts = [];
            $servicos_data = [];
            $valor_servicos = 0;

            foreach ($request->servico_ids ?? [] as $index => $servico_id) {
                if (!$servico_id) {
                    continue;
                }
                $servico = Servico::findOrFail($servico_id);
                $valor_servicos += $request->servico_valor[$index];
                $servico_counts[$servico_id] = ($servico_counts[$servico_id] ?? 0) + 1;

                if ($index === 0) {
                    $data_servico = $request->data_entrada;
                    $hora_servico = $request->horario_entrada;
                    $valor_servico = $request->servico_valor[0] ? __convert_value_bd($request->servico_valor[0]) : 0;
                } else {

                    $data_servico = isset($request->servico_datas[$index - 1]) ? $request->servico_datas[$index - 1] : $request->data_entrada;
                    $hora_servico = isset($request->servico_horas[$index - 1]) ? $request->servico_horas[$index - 1] : $request->horario_entrada;
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
                if (! $produto_id) {
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

            $turma_data = (object) [
                'turma_id' => $request->turma_id,
                'empresa_id' => $empresa_id,
                'data_entrada' => $data_entrada,
                'data_saida' => $data_saida,
                'reserva_id' => $creche->id
            ];

            $is_busy = $this->turma_service->checkIfTurmaIsBusy($turma_data);

            if ($is_busy) {
                session()->flash('flash_erro', 'Não há vagas disponíveis nessa turma para as datas selecionadas.');
                return redirect()->back()->withInput();
            }

            $creche->update([
                'animal_id' => $pet->id,
                'cliente_id' => $pet->cliente_id,
                'turma_id' => $request->turma_id,
                'colaborador_id' => $request->colaborador_id,
                'data' => $request->data,
                'descricao' => $request->descricao,
                'estado' => $request->estado,
            ]);

            $creche->servicos()->detach();
            foreach ($servicos_data as $pivot) {
                $creche->servicos()->attach($pivot['servico_id'], [
                    'data_servico' => $pivot['data_servico'],
                    'hora_servico' => $pivot['hora_servico'],
                    'valor_servico' => $pivot['valor_servico']
                ]);
            }

            $servico_frete = $creche->servicos->filter(function ($servico) {
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
                    'creche_id' => $creche->id,
                    'cliente_id' => $creche->cliente_id,
                ];

                $this->creche_service->updateOrCreateCrecheClienteEndereco($creche->id, $endereco_cliente_data);
            } else {
                if (isset($creche->crecheClienteEndereco)) {
                    $creche->crecheClienteEndereco->delete();
                }
            }


            $creche->produtos()->sync($produtos_data);

            $this->creche_service->updateValorTotal($creche->id);
            $this->creche_service->updateContaReceberDataVencimento($creche->id);

            $ordem = $creche->ordemServico()->first();

            if ($ordem) {
                $ordem->update([
                    'valor' => $valor_servicos + $valor_produtos,
                ]);

                ServicoOs::where('ordem_servico_id', $ordem->id)->delete();
                ProdutoOs::where('ordem_servico_id', $ordem->id)->delete();

                foreach ($creche->servicos as $servico) {
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

            if ($creche->estado != "agendado") {

                $ordens = OrdemServico::where('plano_id', $creche->plano_id)
                    ->where('empresa_id', $empresa_id)
                    ->whereMonth('data_inicio', date('m', strtotime($data_entrada)))
                    ->whereYear('data_inicio', date('Y', strtotime($data_entrada)))
                    ->get();

                foreach ($ordens as $ordem) {
                    $ordem->update([
                        'data_inicio' => $ordem->getOriginal('data_inicio'),
                        'estado' => 'EA',
                    ]);
                }
            }

            session()->flash('flash_sucesso', 'Reserva atualizada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao atualizar reserva: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('creches.index');
    }

	    private function _validate(Request $request) {
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
            
	        $rules = [
                'empresa_id' => 'required|integer',
	            // Dados da reserva

            'animal_id' => 'required|exists:petshop_animais,id',
            'turma_id' => 'required|exists:petshop_turmas,id',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
            'data_entrada' => 'required|date',
            'horario_entrada' => ['required', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'data_saida' => 'required|date|after_or_equal:data_entrada',
            'horario_saida' => ['required', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'descricao' => 'nullable|string|max:1000',

            // Serviço de reserva
            'servico_ids' => 'required|array',
            'servico_ids.0' => 'required|exists:servicos,id',

            // Serviços extras
            'servico_ids.*' => 'nullable|exists:servicos,id',
            'servico_datas_valid.*' => 'required_with:servico_ids_valid|date|after_or_equal:data_entrada',
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
        ];
        $messages = [];
        $this->validate($request, $rules, $messages);
    }

     public function printEnderecoEntrega($id){
        $creche = Creche::findOrFail($id);
        
        $item = $creche->crecheClienteEndereco;

        $height = 350;
        
        $config = Empresa::where('id', $item->empresa_id)->first();

        $p = view('petshop.creche.cupom_entrega', compact('config', 'item', 'creche'));

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
            $creche = Creche::where('empresa_id', $empresa_id)->findOrFail($id);
            $creche->delete();
            session()->flash('flash_sucesso', 'Reserva excluída com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao excluir reserva: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('creches.index');
    }

    public function attachServicos(Request $request, Creche $creche)
    {
        $rules = [
            'servico_ids' => 'array',
            'servico_ids.*' => 'exists:servicos,id',
        ];
        $messages = [];
        $this->validate($request, $rules, $messages);

        try {
            DB::transaction(function () use ($request, $creche) {
                foreach ($request->servico_ids ?? [] as $servico_id) {
                    $creche->servicos()->attach($servico_id);
                }
            });

            session()->flash('flash_sucesso', 'Serviços adicionados com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao adicionar serviços: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }
}
