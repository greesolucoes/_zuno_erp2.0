<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Creche;
use App\Models\Petshop\Turma;
use App\Models\Produto;
use App\Models\ProdutoOs;
use App\Models\Servico;
use App\Models\ServicoOs;
use App\Services\Notificacao\CrecheNotificacaoService;
use App\Services\Petshop\CrecheService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class CrecheController extends Controller
{
    protected $creche_service;

    public function __construct(CrecheService $creche_service)
    {
        $this->creche_service = $creche_service;
    }

    /**
     * Valida os campos de cadastro para se cadastrar uma nova creche ou 
     * atualiza-la por completo (usado no storeCreche)
     */
    private function __validate(Request $request) {
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

            'animal_id' => 'required|exists:animais,id',
            'turma_id' => 'required|exists:turmas,id',
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
        ]);
    }
    
    /**
     * Cria um novo agendamento de creche de forma dinâmica
    */
    public function storeCreche(Request $request)
    {
        $empresa_id = $request->empresa_id;
            
        $this->__validate($request);

        $servico_reserva = $request->servico_ids[0] ? Servico::with('categoria')->find($request->servico_ids[0]) : null;

        if (!$servico_reserva || strtoupper($servico_reserva->categoria->nome) !== 'CRECHE') {
            return response()->json([
                'success' => false,
                'message' => 'Selecione um serviço de reserva para dar continuidade ao cadastro.'
            ], 400);
        }

        try {
            $data_entrada = Carbon::parse($request->data_entrada.' '.$request->horario_entrada);
            $data_saida = Carbon::parse($request->data_saida.' '.$request->horario_saida);

            $pet = Animal::findOrFail($request->animal_id);

            $turma = Turma::findOrFail($request->turma_id);
            if ($turma->status !== Turma::STATUS_DISPONIVEL) {
                return response()->json([
                    'success' => false,
                    'message' => 'Turma selecionada não está disponível para reserva.'
                ], 403);
            }

            $servico_counts = [];
            $servicos_data = [];
            $valor_servicos = 0;
            foreach ($request->servico_ids ?? [] as $index => $servico_id) {
                if (!$servico_id) {
                    continue;
                }
                $servico = Servico::findOrFail($servico_id);
                $valor_servicos += $request->servico_valor[$index] ?? 0;
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

            if (!empty($produtos_data)) {
                $creche->produtos()->sync($produtos_data);
            }

            $codigo_sequencial = (OrdemServico::where('empresa_id', $empresa_id)->max('codigo_sequencial') ?? 0) + 1;

            $ordem = OrdemServico::create([
                'descricao' => 'Ordem de Serviço Creche',
                'cliente_id' => $pet->cliente_id,
                'empresa_id' => $empresa_id,
                'funcionario_id' => $request->colaborador_id,
                'animal_id' => $pet->id,
                'creche_id' => $creche->id,
                'usuario_id' => auth()->id(),
                'codigo_sequencial' => $codigo_sequencial,
                'valor' => $valor_servicos + $valor_produtos,
                'data_inicio' => $data_entrada,
                'data_entrega' => $data_saida,
                'estado' => 'AG',
            ]);

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

            $crecheParaNotificacao = $creche->fresh([
                'empresa',
                'cliente',
                'animal',
                'turma',
                'colaborador',
                'servicos',
                'produtos',
            ]);

            (new CrecheNotificacaoService())->nova($crecheParaNotificacao ?? $creche);

            return response()->json([
                'success' => true, 
                'message' => 'Reserva agendada com sucesso!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'exception' => $e,
                'message' => 'Ocorreu um erro desconhecido ao agendar a reserva.', 
            ]);
        }
    }

    public function updateReservaCreche (Request $request, $id)
    {
        $request->validate([
            'reserva_data_entrada' => 'required|date',
            'reserva_data_saida' => 'required|date',
            'reserva_horario_entrada' => 'required',
            'reserva_horario_saida' => 'required',
            'reserva_turma_id' => 'required|integer',
        ]);

        try {
            $creche = Creche::findOrFail($id);

            $data_entrada = Carbon::parse($request->reserva_data_entrada . ' ' . $request->reserva_horario_entrada);
            $data_saida = Carbon::parse($request->reserva_data_saida . ' ' . $request->reserva_horario_saida);

            $creche->update([
                'data_entrada' => $data_entrada,
                'data_saida' => $data_saida,
                'turma_id' => $request->reserva_turma_id
            ]);

            $this->creche_service->updateContaReceberDataVencimento($creche->id);  
            
            return response()->json([
                'success' => true,
                'message', 'Reserva atualizada com sucesso!'
            ], 200);
        } catch (\Exception $e) {
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
            $creche = Creche::findOrFail($id);

            $servico_reserva = $creche->servicos
            ->filter(fn ($servico) => $servico->categoria->nome === 'CRECHE')
            ->values();

            $servico_frete = $creche->servicos
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

            $creche->servicos()->sync($servicos_fixos + $servicos_extras);

            $total_produtos = 0;

            if (isset($creche->produtos)) {
                foreach ($creche->produtos as $produto) {
                    $total_produtos += $produto->pivot->quantidade * $produto->valor_unitario;
                }
            }

            $valor_total = $total_servicos + $total_produtos;

            $this->creche_service->updateValorTotal($creche->id);

            $ordem_servico = $creche->ordemServico;
            if ($ordem_servico) {
                $extras_servicos = ServicoOs::where('ordem_servico_id', $ordem_servico->id)
                ->whereHas('servico', function ($query) {
                    $query->whereHas('categoria', function ($query) {
                        $query->whereNot('nome', 'CRECHE')
                        ->whereNot('nome', 'FRETE');
                    });
                })
                ->get();

                $extras_servicos->each(fn ($servico) => $servico->delete());

                if (isset($request->extra_servico_ids)) {
                    $updated_creche_servicos = Servico::whereIn('id', $request->extra_servico_ids)->get();

                    foreach ($updated_creche_servicos as $index => $servico) {
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

                $creche->ordemServico->update([
                    'valor' => $valor_total,
                    'total_sem_desconto' => $valor_total
                ]);
            }

            return response()->json([
                'success' => true,
                'message', 'Serviços atualizados com sucesso!'
            ], 200);
        } catch (\Exception $e) {
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
            $creche = Creche::findOrFail($id);

            $servico_reserva = $creche->servicos
            ->filter(fn ($servico) => $servico->categoria->nome === 'CRECHE')
            ->values();

            $servicos_extras = $creche->servicos
            ->filter(fn ($servico) => $servico->categoria->nome != 'CRECHE' && $servico->categoria->nome != 'FRETE')
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
                    'data_servico' => Carbon::parse($creche->data_entrada)->format('Y-m-d'),
                    'hora_servico' => Carbon::parse($creche->data_entrada)->format('H:i'),
                    'valor_servico' => $request->servico_frete_valor,
                ];

                $total_servicos += $request->servico_frete_valor;
            } else {
                if (isset($creche->crecheClienteEndereco)) {
                    $creche->crecheClienteEndereco()->delete();
                }
            }

            $creche->servicos()->sync($servico_frete + $servicos_fixos);

            $total_produtos = 0;

            if (isset($creche->produtos)) {
                foreach ($creche->produtos as $produto) {
                    $total_produtos += $produto->pivot->quantidade * $produto->valor_unitario;
                }
            }

            $valor_total = $total_servicos + $total_produtos;

            $this->creche_service->updateValorTotal($creche->id);

            $ordem_servico = $creche->ordemServico;
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

                $creche->ordemServico->update([
                    'valor' => $valor_total,
                    'total_sem_desconto' => $valor_total
                ]);
            }

            return response()->json([
                'success' => true,
                'message', 'Serviços atualizados com sucesso!'
            ], 200);
        } catch (\Exception $e) {
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
            $creche = Creche::findOrFail($id);

            $produtos = [];
            if (isset($request->agendamento_produto_id)) {
                foreach ($request->agendamento_produto_id as $id => $produto_id) {
                    $produtos[$produto_id] = [
                        'quantidade' => $request->agendamento_qtd_produto[$id],
                    ];
                }
            }

            $creche->produtos()->sync($produtos);

            $ordem_servico = $creche->ordemServico;

            if ($ordem_servico) {
                ProdutoOs::where('ordem_servico_id', $ordem_servico->id)->delete();

                if (isset($request->agendamento_produto_id)) {
                    $updated_creche_produtos = Produto::whereIn('id', $request->agendamento_produto_id)->get();

                    foreach ($updated_creche_produtos as $index => $produto) {
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

            $this->creche_service->updateValorTotal($creche->id);

            return response()->json([
                'success' => true,
                'message', 'Produtos atualizados com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar os produtos...',
                'exception' => $e->getMessage() 
            ], 500);
        }
    }
}
