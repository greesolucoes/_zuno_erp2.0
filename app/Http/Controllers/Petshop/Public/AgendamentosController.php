<?php

namespace App\Http\Controllers\Petshop\Public;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Estetica;
use App\Models\Petshop\EsteticaServico;
use App\Models\Petshop\Configuracao;
use App\Models\Servico;
use Illuminate\Http\Request;
use App\Models\ContaReceberParcela;
use App\Models\ContaReceber;
use App\Models\PlanoUser;
use App\Services\Notificacao\EsteticaNotificacaoService;
use App\Services\Petshop\PlanoLimiteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgendamentosController extends Controller
{
    public function __construct(private PlanoLimiteService $limiteService)
    {
    }

    /**
     * Show the scheduling view for authenticated users.
     */
    public function index(Request $request)
    {
        $user = auth('plano')->user() ?? auth('portal')->user();

        $tab = $request->query('tab', 'agendados');

        $agendamentos = collect();

        if ($user) {
            $query = Estetica::with(['servicos.servico', 'colaborador'])
                ->where('cliente_id', $user->cliente_id);

            if ($tab === 'anteriores') {
                $agendamentos = $query
                    ->whereDate('data_agendamento', '<', now()->toDateString())
                    ->orderByDesc('data_agendamento')
                    ->orderByDesc('horario_agendamento')
                    ->get();
            } else {
                $agendamentos = $query
                    ->whereDate('data_agendamento', '>=', now()->toDateString())
                    ->orderBy('data_agendamento')
                    ->orderBy('horario_agendamento')
                    ->get();
            }
        }

        return view('public.petshop.agendamentos.index', [
            'agendamentos' => $agendamentos,
            'tab' => $tab,
        ]);
    }
    /**
     * Show the form for creating a new scheduling.
     */
    public function create(Request $request, $agendamentoId = null)
    {
        $user = auth('plano')->user() ?? auth('portal')->user();

        $agendamentoId = $agendamentoId ?? $request->query('reagendar');

        $agendamento = null;
        $filialAtual = null;
        $filialId = optional($user)->local_id;

        if ($agendamentoId) {
            $agendamento = Estetica::with(['servicos.servico', 'colaborador.usuario'])
                ->where('cliente_id', optional($user)->cliente_id)
                ->find($agendamentoId);

            if (! $agendamento) {
                return redirect()->route('petshop.planos.agendamentos')
                    ->with('flash_error', 'Agendamento não encontrado.');
            }

            $agendamentoLocal = optional(optional($agendamento->colaborador)->usuario)->local_id;
            if ($agendamentoLocal) {
                $filialId = $agendamentoLocal;
            }
        }

        $filiais = collect();
        $servicos = collect();
        $produtos = collect();
        $servicosExtras = collect();

        if (auth('plano')->check() && $this->isInadimplente($user)) {
            return redirect()->route('petshop.planos.agendamentos')
                ->with('flash_error', 'Você possui parcelas em atraso. Regularize seu plano para agendar.');
        }

        if ($user && $user->cliente && $user->cliente->empresa) {
            $empresa = $user->cliente->empresa;

            $filiais = Filial::where('empresa_id', $empresa->id)->get();

            if ($filialId) {
                $filialAtual = $filiais->firstWhere('id', $filialId);
            } elseif ($user->local) {
                $filialAtual = $user->local;
            }

            if ($user->plano) {
                $versao = $user->plano->versoes()
                    ->orderByDesc('vigente_desde')
                    ->first();

                if ($versao) {
                    $servicos = $versao->servicos()->with('servico')->get();
                    $produtos = $versao->produtos()->with('produto')->get();
                }
            }
            $servicosExtras = Servico::where('empresa_id', $empresa->id)
                ->whereHas('categoria', function ($q) {
                    $q->whereRaw('LOWER(nome) = ?', ['estetica']);
                })
                ->whereNotIn('id', $servicos->pluck('servico_id'))
                ->get();
        }

        return view('public.petshop.agendamentos.novo_agendamento', [
            'filiais' => $filiais,
            'servicosPlano' => $servicos,
            'produtosPlano' => $produtos,
            'servicosExtras' => $servicosExtras,
            'agendamentoId' => $agendamentoId,
            'agendamento' => $agendamento,
            'filialAtual' => $filialAtual,
        ]);
    }

    /**
     * Store a new scheduling for the authenticated user.
     */
    public function store(Request $request)
    {
        $user = auth('plano')->user() ?? auth('portal')->user();

        if (auth('plano')->check() && $this->isInadimplente($user)) {
            return response()->json([
                'errors' => [
                    'inadimplencia' => 'Cliente inadimplente. Agendamento bloqueado.',
                ],
            ], 422);
        }

        $request->validate([
            'local_id'        => 'required|integer',
            'servicos'        => 'required|string',
            'servicos_valores'=> 'nullable|string',
            'data'            => 'required|date_format:Y-m-d',
            'inicio'          => 'required|date_format:H:i',
            'total'           => 'nullable|numeric',
            'funcionario_id'  => 'nullable|integer',
        ]);

        $servicoIds = array_filter(explode(',', $request->servicos));
        $servicos   = Servico::whereIn('id', $servicoIds)->get();

        if ($user instanceof PlanoUser && $user->plano && $user->plano->frequencia_tipo === 'limitado') {
            foreach ($servicoIds as $sid) {
                if (! $this->limiteService->podeUsarServico($user, (int) $sid)) {
                    Log::info('[PlanoLimite] Limite atingido ao agendar', [
                        'plano_user_id' => $user->id,
                        'servico_id'    => (int) $sid,
                    ]);
                    return response()->json([
                        'errors' => [
                            'servico_limite' => 'Limite de uso do serviço atingido para este período.',
                        ],
                    ], 422);
                }
            }
        }

        $inicio = Carbon::parse($request->data)->setTimeFromTimeString($request->inicio);
        $duracao = $servicos->sum(fn($s) => $s->tempo_execucao);
        $fim = $inicio->copy()->addMinutes($duracao);

        $config = Configuracao::with('horarios')
            ->where('filial_id', $request->local_id)
            ->first();

        $diaSemana = Carbon::parse($request->data)->dayOfWeek;
        $usaAlternativo = $config && $config->usar_agendamento_alternativo
            && $config->horarios->where('dia_semana', $diaSemana)->isNotEmpty();

        if ($usaAlternativo) {
            $conflito = Estetica::with('servicos.servico')
                ->whereNull('colaborador_id')
                ->whereDate('data_agendamento', $request->data)
                ->where('empresa_id', optional($user->cliente)->empresa_id)
                ->where('estado', '!=', 'rejeitado')
                ->when($request->filled('agendamento_id'), fn($q) => $q->where('id', '!=', $request->agendamento_id))
                ->get()
                ->first(function ($a) use ($inicio, $fim) {
                    $aInicio = Carbon::parse($a->data_agendamento)->setTimeFromTimeString($a->horario_agendamento);
                    $aDuracao = $a->servicos->sum(fn($s) => $s->servico ? $s->servico->tempo_execucao : 0);
                    $aFim = $aInicio->copy()->addMinutes($aDuracao);
                    return $inicio->lt($aFim) && $fim->gt($aInicio);
                });

            if ($conflito) {
                return response()->json([
                    'errors' => [
                        'horario' => 'Horário indisponível.',
                    ],
                ], 422);
            }
        } elseif ($request->filled('funcionario_id')) {
            $conflito = Estetica::with('servicos.servico')
                ->where('colaborador_id', $request->funcionario_id)
                ->whereDate('data_agendamento', $request->data)
                ->where('estado', '!=', 'rejeitado')
                ->when($request->filled('agendamento_id'), fn($q) => $q->where('id', '!=', $request->agendamento_id))
                ->get()
                ->first(function ($a) use ($inicio, $fim) {
                    $aInicio = Carbon::parse($a->data_agendamento)->setTimeFromTimeString($a->horario_agendamento);
                    $aDuracao = $a->servicos->sum(fn($s) => $s->servico ? $s->servico->tempo_execucao : 0);
                    $aFim = $aInicio->copy()->addMinutes($aDuracao);
                    return $inicio->lt($aFim) && $fim->gt($aInicio);
                });

            if ($conflito) {
                return response()->json([
                    'errors' => [
                        'horario' => 'Horário indisponível para este profissional.',
                    ],
                ], 422);
            }
        }

        $animal = Animal::where('cliente_id', $user->cliente_id)->first();

        if (!$animal) {
            return response()->json([
                'errors' => [
                    'animal' => 'É necessário cadastrar um pet antes de agendar.',
                ],
            ], 422);
        }

        $colaboradorId = $usaAlternativo ? null : $request->funcionario_id;

        $novoAgendamento = false;

        if ($request->filled('agendamento_id')) {
            $estetica = Estetica::where('cliente_id', $user->cliente_id)
                ->findOrFail($request->agendamento_id);
            $estetica->update([
                'colaborador_id'     => $colaboradorId,
                'data_agendamento'   => $request->data,
                'horario_agendamento'=> $request->inicio,
                'horario_saida'      => $fim->format('H:i'),
                'estado'             => 'pendente_aprovacao',
                'plano_id'           => auth('plano')->check() ? $user->plano_id : null,
            ]);
            $estetica->servicos()->delete();
        } else {
            $estetica = Estetica::create([
                'empresa_id'         => optional($user->cliente)->empresa_id,
                'animal_id'          => $animal->id,
                'cliente_id'         => $user->cliente_id,
                'colaborador_id'     => $colaboradorId,
                'data_agendamento'   => $request->data,
                'horario_agendamento'=> $request->inicio,
                'estado'             => 'pendente_aprovacao',
                'plano_id'           => auth('plano')->check() ? $user->plano_id : null,
            ]);
            $novoAgendamento = true;
        }

        $valores = collect(json_decode($request->servicos_valores, true) ?: []);
        $mapValores = $valores->keyBy('id');

        foreach ($servicos as $servico) {
            $valor = (float) ($mapValores->get($servico->id)['subtotal'] ?? 0);

            EsteticaServico::create([
                'estetica_id' => $estetica->id,
                'servico_id'  => $servico->id,
                'subtotal'    => $valor,
            ]);
        }

        if ($novoAgendamento) {
            $esteticaParaNotificacao = $estetica->fresh(['empresa', 'cliente', 'animal', 'servicos.servico']);
            (new EsteticaNotificacaoService())->nova($esteticaParaNotificacao ?? $estetica);
        }

        return response()->json(['success' => true]);
    }

    private function isInadimplente($user): bool
    {
        $plano = $user->plano;

        if (! $plano || $plano->bloquear_por_inadimplencia !== 'sim') {
            return false;
        }

        $dias   = (int) ($plano->dias_tolerancia_atraso ?? 0);
        $limite = now()->subDays($dias)->toDateString();

        $contaAtualId = ContaReceber::where('cliente_id', $user->cliente_id)
            ->where('plano_id', $user->plano_id)
            ->where('status', 0)
            ->orderByDesc('created_at')
            ->value('id');

        if (! $contaAtualId) {
            return false;
        }

        return ContaReceberParcela::where('conta_receber_id', $contaAtualId)
            ->whereIn('status', ['aberta', 'parcial'])
            ->whereDate('data_vencimento', '<', $limite)
            ->exists();
    }
}
