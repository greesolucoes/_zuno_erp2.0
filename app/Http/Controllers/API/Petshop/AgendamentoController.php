<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\DiaSemana;
use App\Models\Funcionamento;
use App\Models\Funcionario;
use App\Models\Interrupcoes;
use App\Models\Agendamento;
use App\Models\OrdemServico;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Creche;
use App\Models\Petshop\Estetica;
use App\Models\Petshop\Hotel;
use App\Models\Petshop\Plano;
use App\Models\Servico;
use App\Services\Petshop\CrecheService;
use App\Services\Petshop\EsteticaService;
use App\Services\Petshop\HotelService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgendamentoController extends Controller
{
    protected EsteticaService $estetica_service;
    protected HotelService $hotel_service;
    protected CrecheService $creche_service;

    public function __construct(EsteticaService $estetica_service, HotelService $hotel_service, CrecheService $creche_service) {
        $this->estetica_service = $estetica_service;
        $this->hotel_service = $hotel_service;
        $this->creche_service = $creche_service;
    }

    /**
     * Controla para que tipo de agendamento será feito conforme os
     * parâmetros de busca e devolve a lista de agendamentos encontrados
     * 
     * @param Request $request parâmetros de busca
     * 
     * @return Response $agendamentos - resposta json com a lista de agendamentos encontrados ou algum erro que tenha acontecido
     */
    public function searchAgendamentos (Request $request) {
        $this->_validate($request);

        try {
            $agendamentos = [];

            switch ($request->categoria) {
                case 'HOTEL':
                    $agendamentos = $this->getHotels($request->all());
                    break;
                case 'CRECHE':
                    $agendamentos = $this->getCreches($request->all());
                    break;
                case 'ESTETICA':
                    $agendamentos = $this->getEsteticas($request->all());
                    break;
                case 'VETERINARIO':
                    $agendamentos = $this->getVetAtendimentos($request->all());
                    break;
                default:
                    $agendamentos[] = $this->getHotels($request->all());
                    $agendamentos[] = $this->getCreches($request->all());
                    $agendamentos[] = $this->getEsteticas($request->all());
                    $agendamentos[] = $this->getVetAtendimentos($request->all());
                    break;
            }

            return response()->json($agendamentos, 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Faz uma pesquisa nos agendamentos do hotel conforme os parâmetros passados
     * 
     * @param array $data parâmetros de busca
     * 
     * @return array $agendamentos lista de agendamentos de hotel encontrados
     */
    private function getHotels(array $data)
    {       
        try {
            $agendamentos = Hotel::where('empresa_id', $data['empresa_id'])
                ->when(isset($data['estados']) && $data['estados'] != '', function ($query) use ($data) {
                    return $query->whereIn('estado', $data['estados']);
                })
                ->when(!empty($data['funcionario_id']), function ($query) use ($data) {
                    return $query->where('colaborador_id', $data['funcionario_id']);
                })
                ->when(!empty($data['cliente_id']), function ($query) use ($data) {
                    return $query->where('cliente_id', $data['cliente_id']);
                })
                ->when(!empty($data['start_date']) && !empty($data['end_date']), function ($query) use ($data) {
                    $start = Carbon::parse($data['start_date']);
                    $end = Carbon::parse($data['end_date']);
                    
                    return $query->where(function ($q) use ($start, $end) {
                        $q->whereBetween('checkin', [$start, $end])
                        ->orWhereBetween('checkout', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->where('checkin', '<', $start)
                                ->where('checkout', '>', $end);
                        });
                    });
                })
                ->with(['cliente', 'colaborador', 'animal', 'servico'])
                ->get()
                ->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'modulo' => 'HOTEL',
                        'plano' => $h->plano_id,
                        'has_plano' => $h->plano_id ? true : false,
                        'estado' => mb_strtolower($h->estado ?? '', 'UTF-8'),
                        'extendedProps' => [
                            'pet' => [
                                'id' => $h->animal->id ?? null,
                                'nome' => $h->animal->nome ?? null,
                                'raca' => $h->animal->raca->nome ?? null,
                                'raca_id' => $h->animal->raca->id ?? null,
                                'especie' => $h->animal->especie->nome ?? null,
                                'especie_id' => $h->animal->especie->id ?? null,
                                'pelagem' => $h->animal->pelagem->nome ?? null,
                                'pelagem_id' => $h->animal->pelagem->id ?? null,
                                'cor' => $h->animal->cor ?? null,
                                'porte' => $h->animal->porte ?? null,
                                'peso' => $h->animal->peso ?? null,
                                'idade' => $h->animal->idade ?? null,
                                'sexo' => $h->animal->sexo ?? null,
                                'chip' => $h->animal->chip ?? null,
                                'tem_pedigree' => $h->animal->tem_pedigree ?? false,
                                'pedigree' => $h->animal->pedigree ?? null,
                                'origem' => $h->animal->origem ?? null,
                                'data_nascimento' => $h->animal->data_nascimento ?? null,
                                'observacao' => $h->animal->observacao ?? null,
                            ],
                            'cliente' => array_merge(
                                $h->cliente->toArray(),
                                ['nome_cidade' => $h->cliente->cidade->nome ?? null]
                            ),
                            'cliente_id' => $h->cliente->id,
                            'colaborador_id' => $h->colaborador_id ?? null,
                            'animal_id' => $h->animal->id ?? null,
                            'quarto' => $h->quarto->nome ?? '--',
                            'quarto_id' => $h->quarto_id ?? null,
                            'conta_receber_id' => $h->contaReceber->id ?? null,
                            'cliente_contato' => $h->cliente->telefone ?? $h->cliente->telefone_secundario ?? $h->cliente->telefone_terciario ?? '--',
                            'colaborador' => $h->colaborador->nome ?? ($h->servico->funcionario->nome ?? '--'),
                            'reserva' => $h->servicos->filter(fn($s) => $s->categoria->nome == 'HOTEL')->first() ?? null,
                            'frete' => $h->servicos->filter(fn($s) => $s->categoria->nome == 'FRETE')->first() ?? null,
                            'endereco_frete' => isset($h->hotelClienteEndereco) ? $h->hotelClienteEndereco->load('cidade') : null,
                            'servicos' => $h->servicos
                            ->filter(fn($s) => $s->categoria->nome != 'HOTEL' && $s->categoria->nome != 'FRETE')
                            ->sortBy(function($s) {
                                return Carbon::parse($s->pivot->data_servico . ' ' . $s->pivot->hora_servico);
                            })
                            ->map(function($s) {
                                return [
                                    'id' => $s->id,
                                    'nome' => $s->nome,
                                    'categoria' => $s->categoria->nome ?? null,
                                    'valor' => $s->valor ?? 0,
                                    'pivot' => $s->pivot,
                                    'tempo_execucao' => $s->tempo_execucao
                                ];
                            })
                            ->values(),
                            'produtos' => $h->produtos->map(function($p) {
                                return [
                                    'id' => $p->id,
                                    'nome' => $p->nome,
                                    'categoria' => $p->categoria->nome ?? null,
                                    'valor_unitario' => $p->valor_unitario ?? 0,
                                    'subtotal' => $p->valor_unitario * $p->pivot->quantidade,
                                    'quantidade' => $p->pivot->quantidade,
                                    'pivot' => $p->pivot
                                ];
                            }),
                            'has_checklist' => true,
                            'checklists' => $h->checklists->map(function($c) {
                                return [
                                    'id' => $c->id,
                                    'tipo' => $c->tipo,
                                    'checklist' => $c->checklist,
                                ];
                            })->values() ?? [],
                            'quarto_id' => $h->quarto_id,
                            'quarto' => $h->quarto->nome,
                            'horario' => $h->checkin->format('H:i') . ' - ' . $h->checkout->format('H:i'),
                            'data_entrada' => $h->checkin->format('d/m/Y'),
                            'horario_entrada' => $h->checkin->format('H:i'),
                            'data_saida' => $h->checkout->format('d/m/Y'),
                            'horario_saida' => $h->checkout->format('H:i'),
                            'descricao' => $h->descricao,
                            'ordem_servico_id' => $h->ordem_servico_id ?? null,
                            'ordem_servico_codigo' => $h->ordem_servico->codigo_sequencial ?? null
                        ],
                        'start' => Carbon::parse($h->checkin)->toIso8601String(),
                        'end'   => Carbon::parse($h->checkout)->toIso8601String(),
                        'allDay' => false,
                    ];
                })
            ->toArray();

            return $agendamentos;
        } catch (\Exception $e) {
            __saveLogError($e, $data['empresa_id'] ?? request()->empresa_id);
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getCreches($data)
    {
        try {
            $agendamentos = Creche::where('empresa_id', $data['empresa_id'])
                ->when(!empty($data['estados']), function ($query) use ($data) {
                    return $query->whereIn('estado', $data['estados']);
                })
                ->when(!empty($data['funcionario_id']), function ($query) use ($data) {
                    return $query->where('colaborador_id', $data['funcionario_id']);
                })
                ->when(!empty($data['cliente_id']), function ($query) use ($data) {
                    return $query->where('cliente_id', $data['cliente_id']);
                })
                ->when(!empty($data['start_date']) && !empty($data['end_date']), function ($query) use ($data) {
                    $start = Carbon::parse($data['start_date']);
                    $end = Carbon::parse($data['end_date']);
                    
                    return $query->where(function ($q) use ($start, $end) {
                        $q->whereBetween('data_entrada', [$start, $end])
                        ->orWhereBetween('data_saida', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->where('data_entrada', '<', $start)
                                ->where('data_saida', '>', $end);
                        });
                    });
                })
                ->with(['cliente', 'colaborador', 'animal', 'servicos'])
                ->get()
                ->map(function ($c) {
                    $c->data_entrada = Carbon::parse($c->data_entrada);
                    $c->data_saida = Carbon::parse($c->data_saida);

                    return [
                        'id' => $c->id,
                        'modulo' => 'CRECHE',
                        'plano' => $c->plano_id,
                        'has_plano' => $c->plano_id ? true : false,
                        'estado' => mb_strtolower($c->estado ?? '', 'UTF-8'),
                        'extendedProps' => [
                            'pet' => [
                                'id' => $c->animal->id ?? null,
                                'nome' => $c->animal->nome ?? null,
                                'raca' => $c->animal->raca->nome ?? null,
                                'raca_id' => $c->animal->raca->id ?? null,
                                'especie' => $c->animal->especie->nome ?? null,
                                'especie_id' => $c->animal->especie->id ?? null,
                                'pelagem' => $c->animal->pelagem->nome ?? null,
                                'pelagem_id' => $c->animal->pelagem->id ?? null,
                                'cor' => $c->animal->cor ?? null,
                                'porte' => $c->animal->porte ?? null,
                                'peso' => $c->animal->peso ?? null,
                                'idade' => $c->animal->idade ?? null,
                                'sexo' => $c->animal->sexo ?? null,
                                'chip' => $c->animal->chip ?? null,
                                'tem_pedigree' => $c->animal->tem_pedigree ?? false,
                                'pedigree' => $c->animal->pedigree ?? null,
                                'origem' => $c->animal->origem ?? null,
                                'data_nascimento' => $c->animal->data_nascimento ?? null,
                                'observacao' => $c->animal->observacao ?? null,
                            ],
                            'cliente' => array_merge(
                                $c->cliente->toArray(),
                                ['nome_cidade' => $c->cliente->cidade->nome ?? null]
                            ),
                            'colaborador_id' => $c->colaborador_id ?? null,
                            'animal_id' => $c->animal->id ?? null,
                            'conta_receber_id' => $c->contaReceber->id ?? null,
                            'turma' => $c->turma->nome ?? '--',
                            'cliente_contato' => $c->cliente->telefone ?? $c->cliente->telefone_secundario ?? $c->cliente->telefone_terciario ?? '--',
                            'colaborador' => $c->colaborador->nome ?? ($c->servico->funcionario->nome ?? '--'),
                            'reserva' => $c->servicos->filter(fn($s) => $s->categoria->nome == 'CRECHE')->first() ?? null,
                            'frete' => $c->servicos->filter(fn($s) => $s->categoria->nome == 'FRETE')->first() ?? null,
                            'endereco_frete' => isset($c->crecheClienteEndereco) ? $c->crecheClienteEndereco->load('cidade') : null,
                            'servicos' => $c->servicos
                            ->filter(fn($s) => $s->categoria->nome != 'CRECHE' && $s->categoria->nome != 'FRETE')
                            ->map(function($s) {
                                return [
                                    'id' => $s->id,
                                    'nome' => $s->nome,
                                    'categoria' => $s->categoria->nome ?? null,
                                    'valor' => $s->valor ?? 0,
                                    'pivot' => $s->pivot,
                                    'tempo_execucao' => $s->tempo_execucao
                                ];
                            })->values(),
                            'produtos' => $c->produtos->map(function($p) {
                                return [
                                    'id' => $p->id,
                                    'nome' => $p->nome,
                                    'categoria' => $p->categoria->nome ?? null,
                                    'valor_unitario' => $p->valor_unitario ?? 0,
                                    'subtotal' => $p->valor_unitario * $p->pivot->quantidade,
                                    'quantidade' => $p->pivot->quantidade,
                                    'pivot' => $p->pivot
                                ];
                            }),
                            'has_checklist' => true,
                            'checklists' => $c->checklists->map(function($c) {
                                return [
                                    'id' => $c->id,
                                    'tipo' => $c->tipo,
                                    'checklist' => $c->checklist,
                                ];
                            }) ?? [],
                            'turma_id' => $c->turma_id,
                            'turma' => $c->turma->nome,
                            'horario' => $c->data_entrada->format('H:i') . ' - ' .
                            $c->data_saida->format('H:i'),
                            'data_entrada' => $c->data_entrada->format('d/m/Y'),
                            'horario_entrada' => $c->data_entrada->format('H:i'),
                            'data_saida' => $c->data_saida->format('d/m/Y'),
                            'horario_saida' => $c->data_saida->format('H:i'),
                            'descricao' => $c->descricao,
                            'ordem_servico_id' => $c->ordem_servico_id ?? null,
                            'ordem_servico_codigo' => $c->ordem_servico->codigo_sequencial ?? null
                        ],
                        'start' => Carbon::parse($c->data_entrada->format('Y-m-d') . ' ' . $c->data_entrada->format('H:i'))->toIso8601String(),
                        'end'   => Carbon::parse($c->data_saida->format('Y-m-d') . ' ' . $c->data_saida->format('H:i'))->toIso8601String(),
                        'allDay' => false,
                    ];
                })
            ->toArray();

            return $agendamentos;
        } catch (\Exception $e) {
            __saveLogError($e, $data['empresa_id'] ?? request()->empresa_id);
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getEsteticas($data)
    {
        try {
            $agendamentos = Estetica::where('empresa_id', $data['empresa_id'])
                ->with(['animal', 'cliente', 'servicos.servico', 'colaborador', 'produtos.produto'])
                ->when(!empty($data['estados']), function ($query) use ($data) {
                    return $query->whereIn('estado', $data['estados']);
                })
                ->when(!empty($data['funcionario_id']), function ($query) use ($data) {
                    return $query->where('colaborador_id', $data['funcionario_id']);
                })
                ->when(!empty($data['cliente_id']), function ($query) use ($data) {
                    return $query->where('cliente_id', $data['cliente_id']);
                })
                ->when(!empty($data['start_date']), function ($query) use ($data) {
                    if (!empty($data['end_date'])) {
                        $query->whereRaw("
                            STR_TO_DATE(CONCAT(data_agendamento, ' ', horario_agendamento), '%Y-%m-%d %H:%i:%s')
                            BETWEEN ? AND ?
                        ", [
                            $data['start_date'] . ' 00:00:00',
                            $data['end_date'] . ' 23:59:59',
                        ]);
                    } else {
                        $query->whereRaw("
                            STR_TO_DATE(CONCAT(data_agendamento, ' ', horario_agendamento), '%Y-%m-%d %H:%i:%s')
                            >= ?
                        ", [
                            $data['start_date'] . ' 00:00:00',
                        ]);
                    }
                })
                ->orderBy('data_agendamento', 'asc')    
                ->orderBy('horario_agendamento', 'asc')
                ->get()
                ->map(function ($a) {
                    $inicio = Carbon::parse($a->data_agendamento)
                            ->setTimeFromTimeString($a->horario_agendamento);   
                    $fim = Carbon::parse($a->data_agendamento)
                            ->setTimeFromTimeString($a->horario_saida);

                    return [
                        'id' => $a->id,
                        'modulo' => 'ESTETICA',
                        'plano' => $a->plano_id,
                        'has_plano' => $a->plano_id ? true : false,
                        'estado' => $a->estado,
                        'extendedProps' => [
                            'pet' => [
                                'id' => $a->animal->id ?? null,
                                'nome' => $a->animal->nome ?? null,
                                'raca' => $a->animal->raca->nome ?? null,
                                'raca_id' => $a->animal->raca->id ?? null,
                                'especie' => $a->animal->especie->nome ?? null,
                                'especie_id' => $a->animal->especie->id ?? null,
                                'pelagem' => $a->animal->pelagem->nome ?? null,
                                'pelagem_id' => $a->animal->pelagem->id ?? null,
                                'cor' => $a->animal->cor ?? null,
                                'porte' => $a->animal->porte ?? null,
                                'peso' => $a->animal->peso ?? null,
                                'idade' => $a->animal->idade ?? null,
                                'sexo' => $a->animal->sexo ?? null,
                                'chip' => $a->animal->chip ?? null,
                                'tem_pedigree' => $a->animal->tem_pedigree ?? false,
                                'pedigree' => $a->animal->pedigree ?? null,
                                'origem' => $a->animal->origem ?? null,
                                'data_nascimento' => $a->animal->data_nascimento ?? null,
                                'observacao' => $a->animal->observacao ?? null,
                            ],
                            'cliente' => array_merge(
                                $a->cliente->toArray(),
                                ['nome_cidade' => $a->cliente->cidade->nome ?? null]
                            ),
                            'nome_plano' => $a->plano ? $a->plano->nome : null,
                            'periodo_plano' => $a->plano ? $a->plano->periodo : null,
                            'frequencia_qtd_plano' => $a->plano ? $a->plano->frequencia_qtd : null,
                            'frequencia_tipo_plano' => $a->plano ? $a->plano->frequencia_tipo : null,
                            'cliente_id' => $a->cliente->id,
                            'colaborador_id' => $a->colaborador_id,
                            'animal_id' => $a->animal->id,
                            'servico_id' => $a->servicos->first()?->servico?->id,
                            'conta_receber_id' => $a->contaReceber->id ?? null,
                            'cliente_contato' => $a->cliente->telefone ?? $a->cliente->telefone_secundario ?? $a->cliente->telefone_terciario ?? '--',
                            'colaborador' => $a->colaborador->nome ?? null,
                            'horario' => $inicio->format('H:i') . ' - ' . $fim->format('H:i'),
                            'data_entrada' => Carbon::parse($a->data_agendamento)->format('d/m/Y'),
                            'horario_entrada' => Carbon::parse($a->horario_agendamento)->format('H:i'),
                            'data_saida' => $fim->format('d/m/Y'),
                            'horario_saida' => $fim->format('H:i'),
                            'descricao' => $a->descricao,
                            'estado' => $a->estado,
                            'ordem_servico_id' => $a->ordem_servico_id ?? null,
                            'ordem_servico_codigo' => $a->ordem_servico->codigo_sequencial ?? null,
                            'frete' => tap(
                                $a->servicos->filter(fn($s) => $s->servico->categoria->nome == 'FRETE')->first(),
                                function ($frete) {
                                    if ($frete) {
                                        $frete->servico_id = $frete->servico->id;
                                    }
                                }
                            ) ?? null,
                            'endereco_frete' => isset($a->esteticaClienteEndereco) ? $a->esteticaClienteEndereco->load('cidade') : null,
                            'servicos' => $a->servicos->filter(fn($s) => $s->servico->categoria->nome != 'FRETE')
                            ->map(function($s) {
                                return [
                                    'id' => $s->servico->id,
                                    'nome' => $s->servico->nome,
                                    'categoria' => $s->servico->categoria->nome ?? null,
                                    'subtotal' => $s->subtotal ?? 0,
                                    'tempo_execucao' => $s->servico->tempo_execucao
                                ];
                            })->values(),
                            'produtos' => $a->produtos->map(fn($p) => [
                                'id' => $p->produto_id,
                                'nome' => $p->produto->nome ?? '',
                                'valor_unitario' => $p->produto->valor ?? 0,
                                'quantidade' => $p->quantidade,
                                'valor' => $p->valor,
                                'subtotal' => $p->subtotal,
                            ])->values(),
                        ],
                        'start' => $inicio->toIso8601String(),
                        'end'   => $fim->toIso8601String(),
                        'allDay' => false,
                    ];
                })
                ->filter()
                ->values()
            ->toArray();

            return $agendamentos;
        } catch (\Exception $e) {
            __saveLogError($e, $data['empresa_id'] ?? request()->empresa_id);
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getVetAtendimentos(array $data)
    {
        try {
            $appointments = Atendimento::query()
                ->with([
                    'animal.cliente',
                    'animal.especie',
                    'animal.raca',
                    'animal.pelagem',
                    'tutor',
                    'veterinario.funcionario',
                    'sala',
                    'servico',
                    'faturamento',
                ])
                ->where('empresa_id', $data['empresa_id'])
                ->whereNotNull('data_atendimento')
                ->when(!empty($data['estados']), function ($query) use ($data) {
                    $query->whereIn('status', $data['estados']);
                })
                ->when(!empty($data['funcionario_id']), function ($query) use ($data) {
                    $query->whereHas('veterinario', function ($veterinarioQuery) use ($data) {
                        $veterinarioQuery->where('funcionario_id', $data['funcionario_id']);
                    });
                })
                ->when(!empty($data['cliente_id']), function ($query) use ($data) {
                    $query->where(function ($builder) use ($data) {
                        $builder
                            ->where('tutor_id', $data['cliente_id'])
                            ->orWhereHas('animal', function ($animalQuery) use ($data) {
                                $animalQuery->where('cliente_id', $data['cliente_id']);
                            });
                    });
                })
                ->when(!empty($data['start_date']), function ($query) use ($data) {
                    $query->whereDate('data_atendimento', '>=', $data['start_date']);
                })
                ->when(!empty($data['end_date']), function ($query) use ($data) {
                    $query->whereDate('data_atendimento', '<=', $data['end_date']);
                })
                ->get()
                ->map(function (Atendimento $atendimento) {
                    $start = $atendimento->start_at ? $atendimento->start_at->copy() : null;

                    if (!$start) {
                        return null;
                    }

                    $duration = $atendimento->servico?->tempo_execucao;
                    $minutes = is_numeric($duration) && (int) $duration > 0 ? (int) $duration : 30;
                    $end = $start->copy()->addMinutes($minutes);

                    $animal = $atendimento->animal;
                    $tutor = $atendimento->tutor ?: $animal?->cliente;

                    $tutor_name = $this->resolveTutorName($tutor, $atendimento->tutor_nome);
                    $contact = $this->resolveTutorContact($tutor, $atendimento->contato_tutor);
                    $email = $atendimento->email_tutor ?: ($tutor?->email ?? null);

                    $veterinarian = $atendimento->veterinario?->funcionario?->nome;

                    if (!$veterinarian && $atendimento->veterinario?->crmv) {
                        $veterinarian = 'CRMV ' . $atendimento->veterinario->crmv;
                    }

                    $room = $atendimento->sala?->nome ?: $atendimento->sala?->identificador;
                    $service_name = $atendimento->servico?->nome ?: ($atendimento->tipo_atendimento ?: null);

                    $notes = $atendimento->observacoes_triagem ?: $atendimento->motivo_visita;

                    if ($notes !== null) {
                        $notes = trim(strip_tags((string) $notes));
                        $notes = $notes === '' ? null : $notes;
                    }

                    $billing = $atendimento->faturamento;

                    return [
                        'id' => $atendimento->id,
                        'title' => $animal?->nome ?? 'Atendimento veterinário',
                        'modulo' => 'VETERINARIO',
                        'plano' => null,
                        'has_plano' => false,
                        'estado' => $atendimento->status,
                        'extendedProps' => [
                            'id' => $atendimento->id,
                            'modulo' => 'VETERINARIO',
                            'estado' => $atendimento->status,
                            'status_label' => $atendimento->status_label,
                            'status_color' => $atendimento->status_color,
                            'codigo' => $atendimento->codigo,
                            'pet' => [
                                'id' => $atendimento->animal->id ?? null,
                                'nome' => $atendimento->animal->nome ?? null,
                                'raca' => $atendimento->animal->raca->nome ?? null,
                                'raca_id' => $atendimento->animal->raca->id ?? null,
                                'especie' => $atendimento->animal->especie->nome ?? null,
                                'especie_id' => $atendimento->animal->especie->id ?? null,
                                'pelagem' => $atendimento->animal->pelagem->nome ?? null,
                                'pelagem_id' => $atendimento->animal->pelagem->id ?? null,
                                'cor' => $atendimento->animal->cor ?? null,
                                'porte' => $atendimento->animal->porte ?? null,
                                'peso' => $atendimento->animal->peso ?? null,
                                'idade' => $atendimento->animal->idade ?? null,
                                'sexo' => $atendimento->animal->sexo ?? null,
                                'chip' => $atendimento->animal->chip ?? null,
                                'tem_pedigree' => $atendimento->animal->tem_pedigree ?? false,
                                'pedigree' => $atendimento->animal->pedigree ?? null,
                                'origem' => $atendimento->animal->origem ?? null,
                                'data_nascimento' => $atendimento->animal->data_nascimento ?? null,
                                'observacao' => $atendimento->animal->observacao ?? null,
                            ],
                            'cliente' => $tutor_name ?? '--',
                            'cliente_id' => $tutor?->id ?? null,
                            'cliente_contato' => $contact ?? '--',
                            'cliente_email' => $email ?? '--',
                            'colaborador' => $veterinarian ?? '--',
                            'veterinario' => $veterinarian ?? '--',
                            'servico' => $service_name ?? '--',
                            'servico_id' => $atendimento->servico_id,
                            'servico_duracao' => $atendimento->servico?->tempo_execucao,
                            'tipo_atendimento' => $atendimento->tipo_atendimento ?? null,
                            'sala' => $room ?? '--',
                            'horario' => $start && $end ? $start->format('H:i') . ' - ' . $end->format('H:i') : null,
                            'data_entrada' => $start?->format('d/m/Y'),
                            'horario_entrada' => $start?->format('H:i'),
                            'data_saida' => $end?->format('d/m/Y'),
                            'horario_saida' => $end?->format('H:i'),
                            'descricao' => $notes ?? '--',
                            'notes' => $notes ?? null,
                            'reserva' => null,
                            'frete' => null,
                            'servicos' => [],
                            'produtos' => [],
                            'has_checklist' => false,
                            'checklists' => [],
                            'has_plano' => false,
                            'links' => [
                                'index' => route('vet.atendimentos.index'),
                                'edit' => route('vet.atendimentos.edit', $atendimento->id),
                                'history' => route('vet.atendimentos.history', $atendimento->id),
                                'billing' => route('vet.atendimentos.billing', $atendimento->id),
                            ],
                            'billing' => $billing ? [
                                'id' => $billing->id,
                                'total' => (float) $billing->total_geral,
                                'total_formatted' => number_format((float) $billing->total_geral, 2, ',', '.'),
                                'services_total' => (float) $billing->total_servicos,
                                'services_total_formatted' => number_format((float) $billing->total_servicos, 2, ',', '.'),
                                'products_total' => (float) $billing->total_produtos,
                                'products_total_formatted' => number_format((float) $billing->total_produtos, 2, ',', '.'),
                            ] : null,
                        ],
                        'start' => $start->toIso8601String(),
                        'end' => $end?->toIso8601String(),
                        'allDay' => false,
                    ];
                })
                ->filter()
                ->values()
                ->toArray();

            return $appointments;
        } catch (\Exception $e) {
            __saveLogError($e, $data['empresa_id'] ?? request()->empresa_id);
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function resolveTutorName($tutor, ?string $fallback = null): ?string
    {
        if ($fallback) {
            return $fallback;
        }

        if ($tutor && !empty($tutor->nome)) {
            return $tutor->nome;
        }

        if ($tutor && !empty($tutor->razao_social)) {
            return $tutor->razao_social;
        }

        return null;
    }

    private function resolveTutorContact($tutor, ?string $fallback = null): ?string
    {
        $candidates = [
            $fallback,
            $tutor?->telefone,
            $tutor?->telefone_secundario,
            $tutor?->telefone_terciario,
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    public function getPlanosAgendamentos(Request $request)
    {
        if (!$request->plano_id) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não informado'
            ], 400);
        }

        if (!$request->cliente_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não informado'
            ], 400);
        }

        $plano = Plano::find($request->plano_id);

        if (!$plano) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não encontrado'
            ], 404);
        }

        try {
            $agendamentos = Estetica::where('plano_id', $request->plano_id)
            ->where('cliente_id', $request->cliente_id)
            ->whereNot('estado', 'pendente_aprovacao')
            ->whereNot('estado', 'rejeitado')
            ->with(['animal', 'cliente', 'servicos.servico', 'colaborador', 'produtos.produto'])
            ->when(isset($request->start_date) && isset($request->end_date), function ($query) use ($request) {
                $query->whereRaw("
                    STR_TO_DATE(CONCAT(data_agendamento, ' ', horario_agendamento), '%Y-%m-%d %H:%i:%s')
                    BETWEEN ? AND ?
                ", [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59',
                ]);
            })
            ->orderBy('data_agendamento', 'asc')     
            ->orderBy('horario_agendamento', 'asc')
            ->get()
            ->map(function ($a) {
                $inicio = Carbon::parse($a->data_agendamento)
                        ->setTimeFromTimeString($a->horario_agendamento);   
                $fim = Carbon::parse($a->data_agendamento)
                        ->setTimeFromTimeString($a->horario_saida);
                        
                return [ 
                    'id' => $a->id,
                    'modulo' => 'ESTETICA',
                    'plano' => $a->plano_id,
                    'has_plano' => $a->plano_id ? true : false,
                    'estado' => $a->estado,
                    'extendedProps' => [
                        'pet' => [
                            'id' => $a->animal->id ?? null,
                            'nome' => $a->animal->nome ?? null,
                            'raca' => $a->animal->raca->nome ?? null,
                            'raca_id' => $a->animal->raca->id ?? null,
                            'especie' => $a->animal->especie->nome ?? null,
                            'especie_id' => $a->animal->especie->id ?? null,
                            'pelagem' => $a->animal->pelagem->nome ?? null,
                            'pelagem_id' => $a->animal->pelagem->id ?? null,
                            'cor' => $a->animal->cor ?? null,
                            'porte' => $a->animal->porte ?? null,
                            'peso' => $a->animal->peso ?? null,
                            'idade' => $a->animal->idade ?? null,
                            'sexo' => $a->animal->sexo ?? null,
                            'chip' => $a->animal->chip ?? null,
                            'tem_pedigree' => $a->animal->tem_pedigree ?? false,
                            'pedigree' => $a->animal->pedigree ?? null,
                            'origem' => $a->animal->origem ?? null,
                            'data_nascimento' => $a->animal->data_nascimento ?? null,
                            'observacao' => $a->animal->observacao ?? null,
                        ],
                        'cliente' => array_merge(
                            $a->cliente->toArray(),
                            ['nome_cidade' => $a->cliente->cidade->nome ?? null]
                        ),
                        'id' => $a->id,
                        'plano' => $a->plano_id,
                        'has_plano' => $a->plano_id ? true : false,
                        'modulo' => 'ESTETICA',
                        'nome_plano' => $a->plano ? $a->plano->nome : null,
                        'periodo_plano' => $a->plano ? $a->plano->periodo : null,
                        'frequencia_qtd_plano' => $a->plano ? $a->plano->frequencia_qtd : null,
                        'frequencia_tipo_plano' => $a->plano ? $a->plano->frequencia_tipo : null,
                        'cliente_id' => $a->cliente->id,
                        'colaborador_id' => $a->colaborador_id,
                        'animal_id' => $a->animal->id,
                        'servico_id' => $a->servicos->first()?->servico?->id,
                        'conta_receber_id' => $a->contaReceber->id ?? null,
                        'cliente_contato' => $a->cliente->telefone ?? $a->cliente->telefone_secundario ?? $a->cliente->telefone_terciario ?? '--',
                        'colaborador' => $a->colaborador->nome ?? null,
                        'horario' => $inicio->format('H:i') . ' - ' . $fim->format('H:i'),
                        'data_entrada' => Carbon::parse($a->data_agendamento)->format('d/m/Y'),
                        'horario_entrada' => Carbon::parse($a->horario_agendamento)->format('H:i'),
                        'data_saida' => $fim->format('d/m/Y'),
                        'horario_saida' => $fim->format('H:i'),
                        'descricao' => $a->descricao,
                        'estado' => $a->estado,
                        'ordem_servico_id' => $a->ordem_servico_id ?? null,
                        'ordem_servico_codigo' => $a->ordem_servico->codigo_sequencial ?? null,
                        'frete' => tap(
                            $a->servicos->filter(fn($s) => $s->servico->categoria->nome == 'FRETE')->first(),
                            function ($frete) {
                                if ($frete) {
                                    $frete->servico_id = $frete->servico->id;
                                }
                            }
                        ) ?? null,
                        'endereco_frete' => isset($a->esteticaClienteEndereco) ? $a->esteticaClienteEndereco->load('cidade') : null,
                        'servicos' => $a->servicos->filter(fn($s) => $s->servico->categoria->nome != 'FRETE')
                        ->map(function($s) {
                            return [
                                'id' => $s->servico->id,
                                'nome' => $s->servico->nome,
                                'categoria' => $s->servico->categoria->nome ?? null,
                                'subtotal' => $s->subtotal ?? 0,
                                'tempo_execucao' => $s->servico->tempo_execucao
                            ];
                        })->values(),
                        'produtos' => $a->produtos->map(fn($p) => [
                            'id' => $p->produto_id,
                            'nome' => $p->produto->nome ?? '',
                            'valor_unitario' => $p->produto->valor ?? 0,
                            'quantidade' => $p->quantidade,
                            'valor' => $p->valor,
                            'subtotal' => $p->subtotal,
                        ])->values(),
                    ],
                    'start' => $inicio->toIso8601String(),
                    'end'   => $fim->toIso8601String(),
                    'allDay' => false,
                ];
            })
            ->filter()
            ->values();

            return response()->json([
                'success' => true,
                'data' => $agendamentos
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => 'Ocorreu um erro desconhecido ao buscar os agendamentos',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatusAgendamento(Request $request) 
    {
        if (!$request->id) {
            return response()->json([
                'success' => false,
                'message' => 'Agendamento não encontrado'
            ], 404);
        }

        if (!$request->status) {
            return response()->json([
                'success' => false,
                'message' => 'Situação não informada'
            ], 400);
        }

        if (!$request->modulo) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo não informado'
            ], 400);
        }

        try {
            switch($request->modulo) {
                case 'HOTEL':
                    $hotel = Hotel::find($request->id);

                    if ($request->status == 'cancelado') {
                        $this->hotel_service->removeContaReceber($hotel->id);
                    }

                    $hotel->update(['estado' => $request->status]);

                    $status_os = Hotel::getStatusHotelForOrdemServico($request->status) ?? null;

                    if ($status_os) {
                        $hotel->ordemServico()->update([
                            'estado' => $status_os
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Agendamento atualizado com sucesso'
                    ]);
                break;
                case 'ESTETICA':
                    $estetica = Estetica::find($request->id);
                    
                    if ($request->status == 'cancelado') {
                        $this->estetica_service->removeContaReceber($estetica->id);
                    }

                    if ($estetica->estado == 'pendente_aprovacao') {
                        if ($request->status == 'rejeitado') {
                            $res = $this->estetica_service->rejeitar($estetica);

                            if (!$res['success']) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ocorreu um erro desconhecido ao rejeitar o agendamento.'
                                ], 500);
                            }
                        } else {
                            $res = $this->estetica_service->aprovar($estetica);

                            if (!$res['success']) {
                                return response()->json([
                                    'success' => false,
                                    'message' => $res['message']
                                ], 500);
                            }
                        }
                    } else {
                        $estetica->update(['estado' => $request->status]);
                    }

                    $status_os = Estetica::getStatusEsteticaForOrdemServico($request->status) ?? null;

                    if ($status_os) {
                        $estetica->ordemServico()->update([
                            'estado' => $status_os
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Agendamento atualizado com sucesso'
                    ]);
                break;
                case 'CRECHE':
                    $creche = Creche::find($request->id);

                    if ($request->status == 'cancelado') {
                        $this->creche_service->removeContaReceber($creche->id);
                    }

                    $creche->update(['estado' => $request->status]);

                    $status_os = Creche::getStatusCrecheForOrdemServico($request->status) ?? null;

                    if ($status_os) {
                        $creche->ordemServico()->update([
                            'estado' => $status_os
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Agendamento atualizado com sucesso'
                    ]);
                break;
                case 'VETERINARIO':
                    $atendimento = Atendimento::find($request->id);

                    if (!$atendimento) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Atendimento não encontrado'
                        ], 404);
                    }

                    $allowed_statuses = array_keys(Atendimento::statusMeta());

                    if (!in_array($request->status, $allowed_statuses, true)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Situação inválida para atendimentos veterinários'
                        ], 422);
                    }

                    $atendimento->forceFill(['status' => $request->status])->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Agendamento atualizado com sucesso'
                    ]);
                break;
                default:
                    return response()->json([
                        'success' => true,
                        'message' => 'Módulo inválido'
                    ], 400);
                break;
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar o agendamento...',
                'exception' => $e->getMessage()
            ];
        }
    }

    public function updateEnderecoFrete(Request $request)
    {
        if (!$request->modulo) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo não informado'
            ], 400);
        }

        $endereco_cliente_data = [
            'cep' => $request->cep,
            'rua' => $request->rua,
            'bairro' => $request->bairro,
            'numero' => $request->numero,
            'complemento' => $request->complemento,

            'cidade_id' => $request->modal_cidade_id,
            'hotel_id' => $request->agendamento_id,
            'cliente_id' => $request->cliente_id,
        ];

        $servico_data = [
            'servico_id' => $request->servico_id,
            'valor_servico' => __convert_value_bd($request->valor_servico),
        ];
        
        try {
            switch($request->modulo) {
                case 'HOTEL':
                    $this->hotel_service->updateOrCreateHotelClienteEndereco($request->agendamento_id, $endereco_cliente_data);
                    $this->hotel_service->updateServicoFrete($request->agendamento_id, $servico_data);
                break;
                case 'CRECHE':
                    $this->creche_service->updateOrCreateCrecheClienteEndereco($request->agendamento_id, $endereco_cliente_data);
                    $this->creche_service->updateServicoFrete($request->agendamento_id, $servico_data);
                break;
                case 'ESTETICA':
                    $this->estetica_service->updateOrCreateEsteticaClienteEndereco($request->agendamento_id, $endereco_cliente_data);
                    $this->estetica_service->updateServicoFrete($request->agendamento_id, $servico_data);
                break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Endereço de frete atualizado com sucesso'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar o endereço de frete',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    public function buscarHorarios(Request $request)
    {
        $servicosParam   = $request->servicos;
        $servicos        = is_string($servicosParam) ? json_decode($servicosParam, true) : (array) $servicosParam;
        $data            = $request->data;
        $empresa_id      = $request->empresa_id;
        $funcionario_id  = $request->funcionario_id;

        Log::info('Buscar horários', [
            'servicos'       => $servicos,
            'data'           => $data,
            'empresa_id'     => $empresa_id,
            'funcionario_id' => $funcionario_id,
        ]);

        $diaSemanaNumerico = date('w', strtotime($data));

        $diaStr = DiaSemana::getDia($diaSemanaNumerico);
        $totalServico = 0;
        $tempoServico = 0;

        foreach($servicos as $s){
            $item = Servico::findOrFail($s);
            $tempoServico += (float)$item->tempo_execucao;
            $totalServico += (float)$item->valor;
        }

        Log::debug('Tempo e valor dos serviços calculados', [
            'tempo_total' => $tempoServico,
            'valor_total' => $totalServico,
        ]);

        $funcionarios = Funcionario::where('empresa_id', $empresa_id)
            ->when(!empty($funcionario_id), function ($query) use ($funcionario_id) {
                $query->where('id', $funcionario_id);
            })
            ->whereHas('funcionamento', function ($q) use ($diaStr) {
                $q->where('dia_id', $diaStr);
            })
            ->get();

        $horarios = [];

        foreach ($funcionarios as $f) {
            Log::info('Processando funcionário', ['id' => $f->id, 'nome' => $f->nome]);

            $funcionamento = Funcionamento::where('funcionario_id', $f->id)
                ->where('dia_id', $diaStr)
                ->first();

            if (!$funcionamento) {
                Log::info('Funcionário sem funcionamento para o dia', ['funcionario_id' => $f->id, 'dia' => $diaStr]);
                continue;
            }

            $inicio = $funcionamento->inicio;
            $fim = $funcionamento->fim;

            $dif = strtotime("$data $fim") - strtotime("$data $inicio");

            $minutosDif = $dif / 60; // converte milesegundos em minutos
            $contador = $tempoServico > 0 ? $minutosDif / $tempoServico : 0;

            $interrupcoes = Interrupcoes::where('funcionario_id', $f->id)
                ->where('dia_id', $diaStr)->get();

            $inicio = strtotime("$data $inicio");
            for ($i = 0; $i < $contador; $i++) {
                $fim = strtotime("+" . $tempoServico . " minutes", $inicio);

                $temp = [
                    'funcionario_id' => $f->id,
                    'funcionario_nome' => $f->nome,
                    'inicio' => date('H:i', $inicio),
                    'fim' => date('H:i', $fim),
                    'data' => $data,
                    'total' => $totalServico,
                    'tempoServico' => $tempoServico,
                ];

                $add = true;

                $interrupcao = Interrupcoes::where('funcionario_id', $f->id)
                    ->where('dia_id', $diaStr)
                    ->whereTime('inicio', '<=', date('H:i', $inicio))
                    ->whereTime('fim', '>=', date('H:i', $inicio))
                    ->first();

                if ($interrupcao != null) {
                    Log::debug('Horário cai em interrupção', [
                        'funcionario_id' => $f->id,
                        'inicio' => date('H:i', $inicio),
                        'fim' => date('H:i', $fim),
                    ]);
                    $add = false;
                } else {
                    $agendamento = Agendamento::where('funcionario_id', $f->id)
                        ->whereDate('data', $data)
                        ->whereTime('inicio', '<=', date('H:i', $inicio))
                        ->whereTime('termino', '>=', date('H:i', $inicio))
                        ->first();

                    if ($agendamento != null) {
                        Log::debug('Horário já possui agendamento', [
                            'funcionario_id' => $f->id,
                            'inicio' => date('H:i', $inicio),
                            'fim' => date('H:i', $fim),
                        ]);
                        $add = false;
                    }
                }

                if ($add == true) {
                    Log::debug('Horário disponível', $temp);
                    array_push($horarios, $temp);
                }

                $inicio = $fim;
            }
        }

        if ($request->wantsJson()) {
            return response()->json($horarios);
        }

        return view('agendamento.partials.agenda_row', compact('horarios'));

    }

    public function excluirAgendamento(Request $request) {
        $tipo_agendamento = $request->tipo_agendamento;
        $id = $request->id;

        if ($tipo_agendamento == 'ESTETICA') {
            $agendamento = Estetica::findOrFail($id);

            $agendamento->delete();

            return response()->json(['success' => true], 200);
        }

        if ($tipo_agendamento == 'HOTEL') {
            $agendamento = Hotel::findOrFail($id);

            $agendamento->delete();

            return response()->json(['success' => true], 200);
        }

        if ($tipo_agendamento == 'CRECHE') {
            $agendamento = Creche::findOrFail($id);

            $agendamento->delete();

            return response()->json(['success' => true], 200);
        }


        if ($tipo_agendamento == 'VETERINARIO') {
            $agendamento = Atendimento::findOrFail($id);

            $agendamento->delete();

            return response()->json(['success' => true], 200);
        }

        try {
            if ($tipo_agendamento == 'PRO') {
                $agendamento = OrdemServico::findOrFail($id);
                
                $agendamento->servicos()->delete();
                $agendamento->relatorios()->delete();
                $agendamento->itens()->delete();
                $agendamento->delete();

                if ($request->set_os_null) {
                    $agendamento->notaServico->ordem_servico_id = null;
                    $agendamento->notaServico->save();
                }


                return response()->json(['success' => true], 200);
            }

            if ($tipo_agendamento == 'OFICINA') {
                $agendamento = OrdemServico::findOrFail($id);

                $agendamento->servicos()->delete();
                $agendamento->relatorios()->delete();
                $agendamento->itens()->delete();
                isset($agendamento->checklist) ? $agendamento->checklist()->delete() : '';

                if ($request->set_os_null) {
                    $agendamento->notaServico->ordem_servico_id = null;
                    $agendamento->notaServico->save();
                }

                $agendamento->delete();

                return response()->json(['success' => true], 200);
            }
        } catch (QueryException $e) {
            $erro_msg = $e->getMessage();

            if ($e->getCode() === '23000' && str_contains($erro_msg, 'nota_servicos_ordem_servico_id_foreign')) {
                return response()->json([
                    'success' => false,
                    'has_nfse' => true,
                ], 400);
            }
        }


        return response()->json(['success' => false, 'message' => 'Nenhum agendamento foi encontrado...'], 404);
    }

    private function _validate(Request $request)
    {
        $rules = [
            'empresa_id' => 'required',
            'categoria' => 'nullable|in:HOTEL,CRECHE,ESTETICA,VETERINARIO',
        ];
        $messages = [];
        $this->validate($request, $rules, $messages);
    }

}
