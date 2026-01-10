<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Configuracao;
use App\Models\Petshop\Estetica;
use App\Models\Petshop\EsteticaProduto;
use App\Models\Petshop\EsteticaServico;
use App\Models\Petshop\Hotel;
use App\Models\Petshop\Plano;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\ServicoOs;
use App\Services\Notificacao\EsteticaNotificacaoService;
use App\Services\Petshop\EsteticaService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EsteticaController extends Controller
{
    protected $estetica_service;

    public function __construct(EsteticaService $estetica_service)
    {
        $this->estetica_service = $estetica_service;
    }

    public function getJornada(Request $request)
    {
        $requested_date = Carbon::parse($request->dia_agendamento);
        $requested_day_week = $requested_date->dayOfWeek;

        $current_jornada_funcionario = null;
        $has_jornada_funcionario = false;

        if ($request->funcionario_id) {
            $funcionario = Funcionario::with('jornadaTrabalho.dias')->find($request->funcionario_id);
            
            if (isset($funcionario)) {
                if (isset($funcionario->jornadaTrabalho)) {
                    $current_jornada = $funcionario->jornadaTrabalho->dias()->where('dia_semana', $requested_day_week)->first();

                    if ($current_jornada) {
                        $current_jornada_funcionario = $current_jornada;
                    }

                    $has_jornada_funcionario = true;
                } 
            } else {
                return response()->json(['success' => false, 'message' => 'Colaborador não encontrado'], 404);
            }
        }

        $jornada_empresa_config = Configuracao::with('horarios')
            ->where('filial_id', $request->empresa_id)
            ->first();

        if (!$jornada_empresa_config) {
            $jornada_empresa_config = Configuracao::with('horarios')
                ->where('empresa_id', $request->empresa_id)
                ->whereNull('filial_id')
                ->first();
        }

        $current_empresa_jornada = $jornada_empresa_config->horarios()->where('dia_semana', $requested_day_week)->first();

        return response()->json(
            [
                'success' => true,
                'jornada_empresa' => $current_empresa_jornada,
                'jornada_funcionario' => $current_jornada_funcionario,
                'has_jornada_funcionario' => $has_jornada_funcionario
            ],
        200);

        return response()->json(['success' => true, 'message' => 'Nenhuma jornada de trabalho encontrada'], 200);
    }

    public function getCurrentAgendamentos(Request $request)
    {
        $requested_date = Carbon::parse($request->dia_agendamento);

        try {
            $agendamentos = Estetica::with(['colaborador', 'colaborador.jornadaTrabalho', 'colaborador.jornadaTrabalho.dias', 'cliente', 'animal', 'servicos', 'servicos.servico'])->where('empresa_id', $request->empresa_id)
                ->whereNotIn('estado', ['concluido', 'rejeitado', 'cancelado', 'pendente_aprovacao'])
                ->where('data_agendamento', $requested_date->format('Y-m-d'))
                ->orderBy('horario_agendamento', 'asc')
            ->get();

            $agendamentos = $agendamentos->map(function ($agendamento) {
                $agendamento = $agendamento->toArray();

                $inicio_reserva = Carbon::parse($agendamento['data_agendamento'])
                    ->setTimeFromTimeString($agendamento['horario_agendamento']);

                $fim_reserva = Carbon::parse($agendamento['data_agendamento'])
                    ->setTimeFromTimeString($agendamento['horario_saida']);

                $agendamento['data_agendamento'] = $inicio_reserva->format('Y-m-d');
                $agendamento['horario_agendamento'] = $inicio_reserva->format('H:i');
                $agendamento['horario_saida'] = $fim_reserva->format('H:i');

                return $agendamento;
            });

            return response()->json(['success' => true, 'data' => $agendamentos], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao buscar os agendamentos',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    public function storeEstetica(Request $request)
    {
        $empresa_id = $request->empresa_id;
        
        if ($request->filled('data_agendamento')) {
            try {
                $request->merge([
                    'data_agendamento' => Carbon::parse($request->data_agendamento)->format('Y-m-d')
                ]);
            } catch (\Exception $e) {
                Log::warning('Formato de data_agendamento inválido', ['value' => $request->data_agendamento]);
            }
        }

        if ($request->filled('horario_agendamento') && $request->filled('horario_saida')) {
            try {
                $request->merge([
                    'horario_agendamento' => Carbon::parse($request->horario_agendamento)->format('H:i'),
                    'horario_saida' => Carbon::parse($request->horario_saida)->format('H:i')
                ]);
            } catch (\Exception $e) {
                Log::warning('Formato de horario_agendamento inválido', ['value' => $request->horario_agendamento]);
            }
        }

        $request->validate([
            'animal_id'           => 'required|exists:petshop_animais,id',
            'colaborador_id'      => 'nullable|exists:funcionarios,id',
            'servico_id'          => 'required|array|min:1',
            'servico_id.*'        => 'exists:servicos,id',
            'produto_id'          => 'nullable|array',
            'produto_id.*'        => 'nullable|exists:produtos,id',
            'qtd_produto'         => 'nullable|array',
            'data_agendamento'    => 'required|date_format:Y-m-d',
            'horario_agendamento' => 'required|date_format:H:i',
            'horario_saida'       => 'required|date_format:H:i',
            'descricao'           => 'nullable|string|max:1000',
        ]);

        try {
            $pet = Animal::findOrFail($request->animal_id);

            $estetica = Estetica::create([
                'empresa_id'         => $empresa_id,
                'animal_id'          => $pet->id,
                'cliente_id'         => $pet->cliente_id,
                'colaborador_id'     => $request->colaborador_id ?: null,
                'plano_id'           => $request->plano_id,
                'descricao'          => $request->descricao,
                'data_agendamento'   => $request->data_agendamento,
                'horario_agendamento'=> $request->horario_agendamento,
                'horario_saida'      => $request->horario_saida,
                'estado'             => $request->estado ?? 'agendado',
            ]);

            foreach ($request->servico_id as $index => $servicoId) {
                $servico = Servico::findOrFail($servicoId);
                EsteticaServico::create([
                    'estetica_id' => $estetica->id,
                    'servico_id'  => $servico->id,
                    'subtotal'    => __convert_value_bd($request->subtotal_servico[$index]) ?? 0,
                ]);
            }

            $servico_frete = $estetica->servicos->filter(function ($item) {
                return $item->servico->categoria && $item->servico->categoria->nome === 'FRETE';
            });

            if ($servico_frete->first()) {
                $endereco_cliente_data = [
                    'cep' => $request->cep,
                    'rua' => $request->rua,
                    'bairro' => $request->bairro,
                    'numero' => $request->numero,
                    'complemento' => $request->complemento,

                    'cidade_id' => $request->modal_cidade_id,
                    'estetica_id' => $estetica->id,
                    'cliente_id' => $estetica->cliente_id,
                ];

                $this->estetica_service->updateOrCreateEsteticaClienteEndereco($estetica->id, $endereco_cliente_data);
            }

            if ($request->filled('produto_id')) {
                foreach ($request->produto_id as $index => $produtoId) {
                    if (!$produtoId) {
                        continue;
                    }
                    $produto = Produto::findOrFail($produtoId);
                    $qtd = intval($request->qtd_produto[$index] ?? 1);
                    $subtotal = ($produto->valor_unitario ?? 0) * $qtd;

                    EsteticaProduto::create([
                        'estetica_id' => $estetica->id,
                        'produto_id'  => $produto->id,
                        'quantidade'  => $qtd,
                        'valor'       => $produto->valor_unitario ?? 0,
                        'subtotal'    => $subtotal,
                    ]);
                }
            }

            if ($estetica->estado !== 'pendente_aprovacao') {
                if (!$this->estetica_service->criarOrdemServico($estetica)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Limite de uso do serviço atingido para este período.'
                    ]);
                }
            }

            $esteticaParaNotificacao = $estetica->fresh(['empresa', 'cliente', 'animal', 'servicos.servico']);
            (new EsteticaNotificacaoService())->nova($esteticaParaNotificacao ?? $estetica);

            return response()->json([
                'success' => true,
                'message' => 'Reserva agendada com sucesso!'
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao salvar agendamento de estética', ['exception' => $e]);

            return response()->json([    
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao realizar o agendamento.',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEstetica(Request $request, $id)
    {
        $request->validate([
            'animal_id'      => 'required|exists:petshop_animais,id',
            'servico_id'    => 'required|exists:servicos,id',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
            'entrada'        => 'required|date_format:Y-m-d',
            'saida'          => 'required|date_format:Y-m-d',
            'descricao'      => 'nullable|string|max:1000',
        ]);

        try {
            $estetica    = Estetica::findOrFail($id);
            $dataEntrada = Carbon::parse($request->entrada . ' ' . $request->hora_entrada);
            $dataSaida   = Carbon::parse($request->saida . ' ' . $request->hora_saida);
            $pet         = Animal::findOrFail($request->animal_id);
            $servico     = Servico::findOrFail($request->servico_id);
            $empresa_id  = $request->empresa_id;

            $dataAntiga = Carbon::parse($estetica->getOriginal('entrada'));
            $dataNova   = $dataEntrada;

            $mesAntigo = $dataAntiga->format('Ym');
            $mesNovo   = $dataNova->format('Ym');

            $estetica->update([
                'empresa_id'     => $empresa_id,
                'animal_id'      => $pet->id,
                'cliente_id'     => $pet->cliente_id,
                'colaborador_id' => $request->colaborador_id ?: null,
                'servico_id'     => $servico->id,
                'descricao'      => $request->descricao,
                'entrada'        => $dataEntrada,
                'saida'          => $dataSaida,
                'valor'          => $servico->valor,
                'estado'         => $request->estado,
            ]);

            if ($estetica->plano_id) {
                if ($mesAntigo !== $mesNovo) {
                    $ordemAntiga = OrdemServico::where('plano_id', $estetica->plano_id)
                        ->whereMonth('data_entrega', $dataAntiga->month)
                        ->whereYear('data_entrega', $dataAntiga->year)
                        ->first();

                    if ($ordemAntiga) {
                        $modulos = $ordemAntiga->modulo_ids ?? [];

                        if (isset($modulos['Estetica'])) {
                            $modulos['Estetica'] = array_values(array_filter(
                                $modulos['Estetica'],
                                fn($id) => $id != $estetica->id
                            ));

                            if (empty($modulos['Estetica'])) {
                                unset($modulos['Estetica']);
                            }

                            if (empty($modulos)) {
                                $ordemAntiga->delete();
                            } else {
                                $ordemAntiga->modulo_ids = $modulos;
                                $ordemAntiga->save();
                                $this->recalcularOrdem($ordemAntiga);
                            }
                        }
                    }

                    // ORDEM NOVA
                    $ordemNova = OrdemServico::where('plano_id', $estetica->plano_id)
                        ->whereMonth('data_entrega', $dataNova->month)
                        ->whereYear('data_entrega', $dataNova->year)
                        ->first();

                    $codigoSequencial = (OrdemServico::where('empresa_id', $estetica->empresa_id)->max('codigo_sequencial') ?? 0) + 1;

                    if (!$ordemNova) {
                        $ordemNova = OrdemServico::create([
                            'empresa_id'        => $empresa_id,
                            'plano_id'          => $estetica->plano_id,
                            'codigo_sequencial' => $codigoSequencial,
                            'data_entrega'      => $dataNova->copy()->endOfMonth(),
                            'modulo_ids'        => ['Estetica' => [$estetica->id]],
                            'modulos'           => ['Estetica'],
                            'valor'             => 0,
                            'cliente_id'        => $estetica->cliente_id,
                            'descricao'         => 'ORDEM DE SERVIÇO: ' . $estetica->plano_id . ' (MÊS: ' . $dataNova->format('Y-m') . ')',
                            'funcionario_id'    => $estetica->colaborador_id,
                            'estado'            => $estetica->estado === "AG" ? 'AF' : 'FZ',
                        ]);
                    } else {
                        $modulos = $ordemNova->modulo_ids ?? [];
                        if (!in_array($estetica->id, $modulos['Estetica'] ?? [])) {
                            $modulos['Estetica'][] = $estetica->id;
                            $ordemNova->modulo_ids = $modulos;
                            $ordemNova->modulos = ['Estetica'];
                            $ordemNova->save();
                        }
                    }

                    $this->recalcularOrdem($ordemNova);
                    $estetica->ordem_servico_id = $ordemNova->id;
                    $estetica->save();
                } else {
                    // MESMO MÊS
                    $ordem = $estetica->ordemServico;
                    if ($ordem) {
                        $dataEntrega = Carbon::parse($ordem->data_entrega);
                        if ($dataEntrega->month != $dataNova->month || $dataEntrega->year != $dataNova->year) {
                            $ordem = null;
                        }
                    }

                    if ($ordem) {
                        if (strtolower($request->estado) === 'cancelado') {
                            $modulos = $ordem->modulo_ids ?? [];

                            if (isset($modulos['Estetica'])) {
                                $modulos['Estetica'] = array_values(array_filter(
                                    $modulos['Estetica'],
                                    fn($id) => $id != $estetica->id
                                ));

                                if (empty($modulos['Estetica'])) {
                                    unset($modulos['Estetica']);
                                }

                                if (empty($modulos)) {
                                    $ordem->delete();
                                } else {
                                    $ordem->modulo_ids = $modulos;
                                    $ordem->save();
                                    $this->recalcularOrdem($ordem);
                                }
                            }
                        } else {
                            $this->recalcularOrdem($ordem);
                        }
                    }
                }

                // Atualiza valor total do plano
                $plano = Plano::find($estetica->plano_id);
                if ($plano) {
                    $totalPlano = Hotel::where('empresa_id', $empresa_id)->where('plano_id', $plano->id)->sum('valor') +
                        Estetica::where('empresa_id', $empresa_id)->where('plano_id', $plano->id)->sum('valor');

                    $plano->total = $totalPlano;
                    $plano->save();
                }
            } else {
                $ordem = $estetica->ordemServico;
                if ($ordem) {
                    $this->recalcularOrdem($ordem);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Agendamento atualizado com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'message' => 'Erro ao atualizar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateReservaEstetica(Request $request, $id)
    {
        $request->validate([
            'data_agendamento' => 'required|date',
            'horario_agendamento' => 'required|date_format:H:i',
            'horario_saida' => 'required|date_format:H:i|after:horario_agendamento',
            'colaborador_id' => 'nullable|exists:funcionarios,id',
            'servico_id' => 'required|array|min:1',
            'servico_id.*' => 'exists:servicos,id',
        ]);

        try {
            $estetica = Estetica::find($id);

            if (!$estetica) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estética não encontrada'
                ], 404);
            }

            $data_entrada = Carbon::parse($request->data_agendamento);
            $horario_entrada = $request->horario_agendamento;
            $horario_saida = $request->horario_saida;

            $date_time_entrada = Carbon::parse($data_entrada->format('Y-m-d') . ' ' . $horario_entrada);

            $estetica->update([
                'data_agendamento'   => $data_entrada,
                'horario_agendamento'=> $horario_entrada, 
                'horario_saida'      => $horario_saida,
                'colaborador_id'     => $request->colaborador_id ?? null,
            ]);

            $estetica->servicos()->delete();
            $valor_total = 0;

            foreach ($request->servico_id as $index => $servico_id) {
                $servico = Servico::findOrFail($servico_id);
                EsteticaServico::create([
                    'estetica_id' => $estetica->id,
                    'servico_id'  => $servico->id,
                    'subtotal'    => __convert_value_bd($request->subtotal_servico[$index]) ?? 0,
                ]);

                $valor_total += __convert_value_bd($request->subtotal_servico[$index]) ?? 0;
            }

            $data_final_agendamento = Carbon::parse($data_entrada->format('Y-m-d') . ' ' . $horario_saida);

            foreach ($estetica->produtos as $produto) {
                $valor_total += ($produto->valor_unitario ?? 0) * $produto->qtd;
            }

            $this->estetica_service->updateValorTotal($estetica->id);
            $this->estetica_service->updateContaReceberDataVencimento($estetica->id);

            $ordem = $estetica->ordemServico;

            if ($ordem) {
                $ordem->update([
                    'cliente_id'         => $estetica->animal->cliente_id,
                    'empresa_id'         => $estetica->empresa_id,
                    'funcionario_id'     => $request->colaborador_id,
                    'animal_id'          => $estetica->pet_id,
                    'valor'              => $valor_total,
                    'total_sem_desconto' => $valor_total,
                    'data_inicio'        => $date_time_entrada,
                    'data_entrega'       => $data_final_agendamento,
                ]);

                ServicoOs::where('ordem_servico_id', $ordem->id)->delete();
                foreach ($estetica->servicos as $servico) {
                    ServicoOs::create([
                        'ordem_servico_id' => $ordem->id,
                        'servico_id'       => $servico->servico_id,
                        'quantidade'       => 1,
                        'valor'            => $servico->subtotal,
                        'subtotal'         => $servico->subtotal,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Agendamento atualizado com sucesso!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage(),
                'message' => 'Ocorreu um erro desconhecido ao atualizar a reserva...'
            ], 500);
        }

    }

    private function recalcularOrdem(OrdemServico $ordem)
    {
        $moduloIds = $ordem->modulo_ids;
        $totalValor = 0;
        $servicosAgrupados = [];

        ServicoOs::where('ordem_servico_id', $ordem->id)->delete();

        foreach ($moduloIds as $moduloNome => $ids) {
            $modelClass = match ($moduloNome) {
                'Estetica' => \App\Models\Petshop\Estetica::class,
                'Hotel'    => \App\Models\Petshop\Hotel::class,
                default    => null,
            };

            if (!$modelClass || empty($ids)) continue;

            $mes = Carbon::parse($ordem->data_entrega)->month;
            $ano = Carbon::parse($ordem->data_entrega)->year;

            $servicos = $modelClass::whereIn('id', $ids)
                ->whereMonth('entrada', $mes)
                ->whereYear('entrada', $ano)
                ->get();

            foreach ($servicos as $servico) {
                $servicoId = $servico->servico_id ?? $servico->id;
                $valor     = $servico->valor ?? 0;

                if (!isset($servicosAgrupados[$servicoId])) {
                    $servicosAgrupados[$servicoId] = [
                        'quantidade' => 0,
                        'valor'      => $valor,
                    ];
                }

                $servicosAgrupados[$servicoId]['quantidade']++;
            }
        }

        foreach ($servicosAgrupados as $servicoId => $info) {
            $subtotal = $info['quantidade'] * $info['valor'];
            $totalValor += $subtotal;

            ServicoOs::create([
                'ordem_servico_id' => $ordem->id,
                'servico_id'       => $servicoId,
                'quantidade'       => $info['quantidade'],
                'valor'            => $info['valor'],
                'subtotal'         => $subtotal,
                'desconto'         => 0,
            ]);
        }

        $ordem->valor = $totalValor;
        $ordem->save();
    }

    public function checkIfServicoIsFree (Request $request) 
    {
        $servico = Estetica::whereNot('id', $request->id)
            ->where('empresa_id', $request->empresa_id)
            ->where('colaborador_id', $request->colaborador_id)
            ->where(function ($query) use ($request) {
                $query->where('entrada', '<', $request->saida)
                    ->where('saida', '>', $request->entrada);
            })
        ->first();

        return response()->json([
            'success' => !$servico,
            'servico' => $servico
        ], 200);
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
            $estetica = Estetica::findOrFail($id);

            $servicos_reserva = $estetica->servicos
            ->filter(fn ($item) => $item->servico->categoria->nome === 'ESTETICA')
            ->values();


            $total_servicos = 0;

            $total_servicos += $servicos_reserva->sum('subtotal');

            $servico_frete = $estetica->servicos->filter(fn ($item) => $item->servico->categoria->nome === 'FRETE')->values();
            
            if (isset($servico_frete) && $servico_frete->count() > 0) {
                EsteticaServico::where('estetica_id', $estetica->id)
                    ->whereHas('servico.categoria', fn ($query) =>
                        $query->where('nome', 'FRETE')
                    )
                ->delete();
            }
            
            if (isset($request->servico_frete)) {
                EsteticaServico::create([
                    'estetica_id' => $estetica->id,
                    'servico_id' => $request->servico_frete,
                    'subtotal' => $request->servico_frete_valor,
                ]);

                $total_servicos += $request->servico_frete_valor;
            } else {
                if (isset($estetica->esteticaClienteEndereco)) {
                    $estetica->esteticaClienteEndereco()->delete();
                }
            }

            $total_produtos = 0;

            if (isset($estetica->produtos)) {
                foreach ($estetica->produtos as $produto) {
                    $total_produtos += $produto->quantidade * $produto->valor;
                }
            }

            $valor_total = $total_servicos + $total_produtos;

            $this->estetica_service->updateValorTotal($estetica->id);

            $ordem_servico = $estetica->ordemServico;
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

                $estetica->ordemServico->update([
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
}
