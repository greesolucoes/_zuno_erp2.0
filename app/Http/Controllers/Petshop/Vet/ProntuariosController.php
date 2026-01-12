<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Requests\Petshop\StoreProntuarioRequest;
use App\Http\Requests\Petshop\UpdateProntuarioRequest;
use App\Models\Cliente;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Checklist;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Consulta;
use App\Models\Petshop\Medico;
use App\Models\Petshop\ModeloAvaliacao;
use App\Models\Petshop\Prontuario;
use App\Models\Petshop\ProntuarioEvolucao;
use App\Support\Petshop\Vet\AssessmentModelOptions;
use App\Utils\UploadUtil;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProntuariosController
{
    private const ATTACHMENT_STORAGE_DISK = 's3';
    private const ATTACHMENT_DIRECTORY = 'uploads/vet/prontuario/';

    /**
     * Cache das colunas disponíveis na tabela de prontuários.
     */
    private ?array $prontuarioTableColumns = null;

    public function __construct(private UploadUtil $uploadUtil)
    {
    }

    public function index(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $filters = [
            'search' => trim((string) $request->input('search')),
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'veterinarian' => $request->input('veterinarian'),
            'timeframe' => $request->input('timeframe'),
        ];

        $baseQuery = Prontuario::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'tutor',
                'veterinario.funcionario',
                'atendimento.sala',
                'evolucoes',
            ])
            ->withCount(['evolucoes'])
            ->forCompany($companyId);

        $filteredQuery = $this->applyRecordFilters(clone $baseQuery, $filters);

        $records = (clone $filteredQuery)
            ->orderByDesc('data_registro')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->map(fn (Prontuario $record) => $this->mapRecordToListItem($record))
            ->values()
            ->all();

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $summary = $this->buildRecordSummaryMetrics($statusCounts, $companyId);
        $recentNotes = $this->buildRecentRecordNotes($companyId);
        $clinicalAlerts = $this->buildClinicalAlerts($statusCounts);

        $filtersOptions = [
            'status' => $this->formatFilterOptions(Prontuario::statusOptions()),
            'types' => $this->buildTypeFilterOptions($companyId),
            'veterinarians' => $this->loadVeterinarianFilters($companyId),
            'timeframes' => $this->defaultTimeframeFilters(),
        ];

        return view('petshop.vet.prontuarios.index', [
            'summary' => $summary,
            'filters' => $filtersOptions,
            'records' => $records,
            'recentNotes' => $recentNotes,
            'clinicalAlerts' => $clinicalAlerts,
        ]);
    }

    public function queue(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $timezone = config('app.timezone');
        $dateInput = $request->input('date');

        $selectedDate = null;

        if ($dateInput) {
            try {
                $selectedDate = Carbon::createFromFormat('Y-m-d', (string) $dateInput, $timezone);
            } catch (\Throwable $exception) {
                $selectedDate = null;
            }
        }

        if (!$selectedDate) {
            $selectedDate = Carbon::now($timezone);
        }

        $selectedDate = $selectedDate->copy()->startOfDay()->locale(app()->getLocale());
        $now = Carbon::now($timezone);

        $attendances = Atendimento::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'tutor',
                'veterinario.funcionario',
                'sala',
                'servico',
                'latestRecord',
            ])
            ->forCompany($companyId)
            ->whereDate('data_atendimento', $selectedDate->toDateString())
            ->orderBy('horario')
            ->orderBy('id')
            ->get();

        $queueItems = $attendances->map(function (Atendimento $atendimento) use ($now, $timezone) {
            return $this->mapAttendanceToQueueItem($atendimento, $now, $timezone);
        });

        $groupedQueue = $this->groupQueueItems($queueItems);
        $metrics = $this->buildQueueMetrics($queueItems, $selectedDate);
        $veterinarianBoards = $this->buildVeterinarianBoards($queueItems);
        $highlights = $this->buildQueueHighlights($queueItems);
        $calendarView = $this->buildCalendarView($queueItems, $selectedDate);

        return view('petshop.vet.prontuarios.queue', [
            'selectedDate' => $selectedDate,
            'groupedQueue' => $groupedQueue,
            'metrics' => $metrics,
            'veterinarianBoards' => $veterinarianBoards,
            'highlights' => $highlights,
            'hasQueue' => $queueItems->isNotEmpty(),
            'calendarView' => $calendarView,
        ]);
    }

    public function create(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        return view('petshop.vet.prontuarios.registrar', $this->prepareFormViewData($companyId, $request));
    }

    public function store(StoreProntuarioRequest $request)
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $record = Prontuario::create($this->buildProntuarioPayload($companyId, $data));

            $this->syncRecordRelations($record, $data);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Não foi possível registrar o prontuário.'], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Não foi possível salvar o prontuário. Tente novamente.']);
        }

        $record->load([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'tutor',
            'veterinario.funcionario',
            'atendimento.sala',
            'atendimento.servico',
            'evolucoes.autor',
        ]);

        if ($request->wantsJson()) {
            return response()->json($this->transformRecordForPrefill($record), 201);
        }

        session()->flash('flash_success', 'Prontuário cadastrado com sucesso.');

        return redirect()->route('vet.records.edit', [$record->id]);
    }

    public function show(Prontuario $prontuario): JsonResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureRecordCompany($prontuario, $companyId);

        $prontuario->load([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'tutor',
            'veterinario.funcionario',
            'atendimento.sala',
            'atendimento.servico',
            'evolucoes.autor',
        ]);

        return response()->json($this->transformRecordForPrefill($prontuario));
    }

    public function edit(Request $request, Prontuario $prontuario): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureRecordCompany($prontuario, $companyId);

        $prontuario->load([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'tutor',
            'veterinario.funcionario',
            'atendimento.sala',
            'atendimento.servico',
            'evolucoes.autor',
        ]);

        return view('petshop.vet.prontuarios.registrar', array_merge(
            $this->prepareFormViewData($companyId, $request, $prontuario),
            ['record' => $prontuario]
        ));
    }

    public function update(UpdateProntuarioRequest $request, Prontuario $prontuario)
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureRecordCompany($prontuario, $companyId);

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $prontuario->fill($this->buildProntuarioPayload($companyId, $data, $prontuario));
            $prontuario->updated_by = Auth::id();
            $prontuario->save();

            $this->syncRecordRelations($prontuario, $data);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Não foi possível atualizar o prontuário.'], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Não foi possível atualizar o prontuário. Tente novamente.']);
        }

        $prontuario->load([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'tutor',
            'veterinario.funcionario',
            'atendimento.sala',
            'atendimento.servico',
            'evolucoes.autor',
        ]);

        if ($request->wantsJson()) {
            return response()->json($this->transformRecordForPrefill($prontuario));
        }

        session()->flash('flash_success', 'Prontuário atualizado com sucesso.');

        return redirect()->route('vet.records.edit', [$prontuario->id]);
    }

    public function storeAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');

        if (!$file) {
            return response()->json([
                'message' => 'Arquivo inválido.',
            ], 422);
        }

        try {
            $fileName = $this->uploadUtil->uploadFile($file, '/vet/prontuario');
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Não foi possível salvar o documento. Tente novamente.',
            ], 500);
        }

        $path = self::ATTACHMENT_DIRECTORY . ltrim((string) $fileName, '/');
        $url = $this->buildAttachmentUrl($path) ?? (rtrim((string) env('AWS_URL'), '/') . '/' . ltrim($path, '/'));

        $sizeBytes = (int) $file->getSize();
        $uploadedAt = Carbon::now();
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return response()->json([
            'id' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName(),
            'extension' => $extension,
            'mime_type' => $file->getClientMimeType(),
            'type' => $extension !== '' ? strtoupper($extension) : $file->getClientMimeType(),
            'size' => $this->formatFileSize($sizeBytes),
            'size_in_bytes' => $sizeBytes,
            'uploaded_at' => $uploadedAt->format('d/m/Y H:i'),
            'uploaded_at_iso' => $uploadedAt->toIso8601String(),
            'uploaded_by' => Auth::user()?->name,
            'url' => $url,
            'path' => $path,
        ]);
    }

    public function removeAttachment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => 'required|string',
        ]);

        $rawPath = $validated['path'];
        $parsedPath = parse_url($rawPath, PHP_URL_PATH);

        $path = is_string($parsedPath) && $parsedPath !== ''
            ? ltrim($parsedPath, '/')
            : ltrim($rawPath, '/');

        if (!$this->isManagedAttachmentPath($path)) {
            return response()->json([
                'message' => 'Arquivo inválido informado.',
            ], 422);
        }

        try {
            $this->deleteAttachmentFromStorage($path);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Não foi possível remover o documento. Tente novamente.',
            ], 500);
        }

        return response()->json([
            'deleted' => true,
        ]);
    }

    public function destroy(Request $request, Prontuario $prontuario)
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureRecordCompany($prontuario, $companyId);

        $prontuario->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Prontuário removido com sucesso.']);
        }

        session()->flash('flash_success', 'Prontuário removido com sucesso.');

        return redirect()->route('vet.records.index');
    }

    private function applyRecordFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where(function (Builder $builder) use ($filters) {
                $builder
                    ->where('tipo', $filters['type'])
                    ->orWhereRaw('LOWER(tipo) = ?', [Str::of($filters['type'])->lower()->value()]);
            });
        }

        if (!empty($filters['veterinarian'])) {
            $query->where('veterinario_id', (int) $filters['veterinarian']);
        }

        if (!empty($filters['timeframe'])) {
            $now = Carbon::now();
            $timeframe = (string) $filters['timeframe'];

            $query->where(function (Builder $builder) use ($timeframe, $now) {
                $start = null;

                switch ($timeframe) {
                    case 'today':
                        $start = $now->copy()->startOfDay();
                        break;
                    case 'last_7_days':
                        $start = $now->copy()->subDays(6)->startOfDay();
                        break;
                    case 'last_30_days':
                        $start = $now->copy()->subDays(29)->startOfDay();
                        break;
                    case 'last_90_days':
                        $start = $now->copy()->subDays(89)->startOfDay();
                        break;
                    case 'this_month':
                        $start = $now->copy()->startOfMonth();
                        break;
                    case 'this_year':
                        $start = $now->copy()->startOfYear();
                        break;
                    default:
                        if (Str::startsWith($timeframe, 'range:')) {
                            [$rawStart, $rawEnd] = array_pad(explode(',', Str::after($timeframe, 'range:'), 2), 2, null);

                            if ($rawStart) {
                                try {
                                    $start = Carbon::parse($rawStart)->startOfDay();
                                } catch (\Throwable $exception) {
                                    $start = null;
                                }
                            }

                            if ($rawEnd) {
                                try {
                                    $end = Carbon::parse($rawEnd)->endOfDay();
                                } catch (\Throwable $exception) {
                                    $end = null;
                                }

                                if (isset($end)) {
                                    $builder->where(function (Builder $subQuery) use ($start, $end) {
                                        if ($start) {
                                            $subQuery->whereDate('data_registro', '>=', $start->toDateString());
                                        }

                                        $subQuery->whereDate('data_registro', '<=', $end->toDateString());
                                    });

                                    return;
                                }
                            }
                        }
                        break;
                }

                if ($start) {
                    $builder->where(function (Builder $subQuery) use ($start) {
                        $subQuery->whereDate('data_registro', '>=', $start->toDateString())
                            ->orWhereDate('created_at', '>=', $start->toDateString());
                    });
                }
            });
        }

        return $query;
    }

    private function mapRecordToListItem(Prontuario $record): array
    {
        $animal = $record->animal;
        $species = $animal?->especie?->nome;
        $breed = $animal?->raca?->nome;
        $tutor = $record->tutor ?: $animal?->cliente;
        $statusMeta = Prontuario::statusMeta()[$record->status] ?? null;

        $tutorContacts = $this->extractTutorContacts($tutor);
        $primaryContact = collect($tutorContacts)->pluck('value')->filter()->first();

        $typeOption = $record->tipo ? $this->resolveAttendanceTypeOption($record->tipo) : null;
        $typeLabel = $typeOption['label'] ?? Str::of((string) $record->tipo)->squish()->title()->toString() ?: 'Não informado';
        $typeColor = match ($typeOption['value'] ?? $record->tipo) {
            'emergencia' => 'danger',
            'retorno' => 'warning',
            'pos-operatorio' => 'info',
            'consulta' => 'primary',
            default => 'info',
        };

        $summary = Str::of($this->resolveRecordSummary($record))
            ->squish()
            ->toString();

        $tags = [];

        if ($animal) {
            $tags = array_merge($tags, $this->buildPatientTags($animal));
        }

        if ($typeLabel && $typeLabel !== 'Não informado') {
            $tags[] = 'Tipo: ' . $typeLabel;
        }

        if (!empty($record->plano_terapeutico)) {
            $tags[] = 'Plano terapêutico registrado';
        }

        $tags = array_values(array_unique(array_filter($tags)));

        $vitalSigns = collect($record->sinais_vitais ?? [])
            ->filter(fn ($item) => is_array($item) && (!empty($item['label']) || !empty($item['value'])))
            ->map(fn ($item) => [
                'label' => Str::of((string) ($item['label'] ?? ''))->squish()->toString() ?: 'Medida',
                'value' => Str::of((string) ($item['value'] ?? ''))->squish()->toString() ?: '—',
            ])
            ->values()
            ->all();

        $metrics = array_values(array_filter([
            [
                'label' => 'Evoluções',
                'value' => $record->evolucoes_count ?? $record->evolucoes->count(),
                'icon' => 'ri-clipboard-pulse-line',
            ],
            $record->data_registro ? [
                'label' => 'Registrado em',
                'value' => $record->data_registro->format('d/m/Y'),
                'icon' => 'ri-calendar-line',
            ] : null,
        ]));

        $timeline = collect([
            $record->data_registro ? [
                'time' => $record->data_registro->format('d/m/Y H:i'),
                'title' => 'Registro criado',
                'description' => $summary !== '' ? $summary : 'Prontuário registrado no sistema.',
            ] : null,
        ])
            ->merge($record->evolucoes
                ->sortByDesc(function (ProntuarioEvolucao $evolution) {
                    return $evolution->registrado_em ?: $evolution->created_at;
                })
                ->take(5)
                ->map(function (ProntuarioEvolucao $evolution) {
                    $date = $evolution->registrado_em ?: $evolution->created_at;

                    return [
                        'time' => $date ? Carbon::parse($date)->format('d/m/Y H:i') : '—',
                        'title' => $evolution->titulo ?: 'Evolução clínica',
                        'description' => Str::limit(strip_tags((string) $evolution->descricao), 160) ?: 'Atualização registrada.',
                    ];
                })
                ->values())
            ->filter()
            ->values()
            ->all();

        $reminders = collect(is_array($record->lembretes) ? $record->lembretes : [])
            ->map(function ($reminder) {
                if (is_string($reminder)) {
                    return $reminder;
                }

                if (is_array($reminder)) {
                    $message = Str::of((string) ($reminder['message'] ?? $reminder['label'] ?? ''))
                        ->squish()
                        ->toString();

                    $time = $reminder['time'] ?? $reminder['due_date'] ?? null;

                    if ($time) {
                        try {
                            $time = Carbon::parse($time)->format('d/m/Y');
                        } catch (\Throwable $exception) {
                            // Ignora erro de parsing e mantém valor original
                        }
                    }

                    return $message !== '' ? trim($message . ($time ? ' • ' . $time : '')) : null;
                }

                return null;
            })
            ->filter()
            ->take(4)
            ->values()
            ->all();

        return [
            'id' => (int) $record->id,
            'patient_id' => $animal ? (string) $animal->id : null,
            'code' => $record->codigo,
            'patient' => $animal?->nome ?? 'Paciente não informado',
            'species' => $species ?: 'Espécie não informada',
            'breed' => $breed ?: 'Sem raça definida',
            'age' => $animal ? $this->formatAge($animal) : null,
            'tutor' => $this->formatTutorName($animal ?? new Animal()),
            'contact' => $primaryContact,
            'status' => $statusMeta['label'] ?? Str::title($record->status ?? '—'),
            'status_color' => $statusMeta['color'] ?? 'secondary',
            'type' => $typeLabel,
            'type_color' => $typeColor,
            'updated_at' => optional($record->updated_at)->format('d/m/Y H:i'),
            'clinic_room' => optional($record->atendimento?->sala)->nome,
            'veterinarian' => optional($record->veterinario?->funcionario)->nome ?? 'Sem responsável',
            'veterinarian_id' => $record->veterinario_id ? (string) $record->veterinario_id : null,
            'team' => $record->veterinario?->especialidade,
            'summary' => $summary !== '' ? $summary : null,
            'tags' => $tags,
            'metrics' => $metrics,
            'vital_signs' => $vitalSigns,
            'next_steps' => $reminders,
            'timeline' => $timeline,
        ];
    }

    private function buildRecordSummaryMetrics(array $statusCounts, int $companyId): array
    {
        $total = array_sum($statusCounts);
        $active = ($statusCounts[Prontuario::STATUS_IN_PROGRESS] ?? 0)
            + ($statusCounts[Prontuario::STATUS_AWAITING_REVIEW] ?? 0);
        $finished = $statusCounts[Prontuario::STATUS_FINISHED] ?? 0;
        $draft = $statusCounts[Prontuario::STATUS_DRAFT] ?? 0;

        $recentWeek = Prontuario::query()
            ->forCompany($companyId)
            ->where(function (Builder $query) {
                $query
                    ->whereDate('data_registro', '>=', Carbon::now()->subDays(6)->toDateString())
                    ->orWhereDate('created_at', '>=', Carbon::now()->subDays(6)->toDateString());
            })
            ->count();

        return [
            [
                'label' => 'Total de prontuários',
                'value' => $total,
                'variant' => 'primary',
                'icon' => 'ri-folder-2-line',
            ],
            [
                'label' => 'Em atendimento',
                'value' => $active,
                'variant' => 'info',
                'icon' => 'ri-stethoscope-line',
            ],
            [
                'label' => 'Concluídos',
                'value' => $finished,
                'variant' => 'success',
                'icon' => 'ri-checkbox-circle-line',
            ],
            [
                'label' => 'Novos (7 dias)',
                'value' => $recentWeek,
                'variant' => 'warning',
                'icon' => 'ri-time-line',
            ],
            [
                'label' => 'Em rascunho',
                'value' => $draft,
                'variant' => 'secondary',
                'icon' => 'ri-draft-line',
            ],
        ];
    }

    private function buildRecentRecordNotes(int $companyId): array
    {
        $recentEvolutions = ProntuarioEvolucao::query()
            ->with(['prontuario.animal', 'autor'])
            ->whereHas('prontuario', function (Builder $query) use ($companyId) {
                $query->forCompany($companyId);
            })
            ->orderByDesc(DB::raw('COALESCE(registrado_em, created_at)'))
            ->limit(6)
            ->get();

        if ($recentEvolutions->isEmpty()) {
            return Prontuario::query()
                ->with(['animal'])
                ->forCompany($companyId)
                ->orderByDesc(DB::raw('COALESCE(data_registro, created_at)'))
                ->limit(6)
                ->get()
                ->map(function (Prontuario $record) {
                    $animal = $record->animal;
                    $summary = Str::of($this->resolveRecordSummary($record, false))
                        ->limit(120)
                        ->toString();

                    return [
                        'title' => $animal?->nome ?? 'Prontuário atualizado',
                        'description' => $summary !== '' ? $summary : 'Atualização registrada no prontuário.',
                        'time' => optional($record->updated_at)->diffForHumans() ?? 'Agora',
                        'icon' => 'ri-health-book-line',
                    ];
                })
                ->values()
                ->all();
        }

        return $recentEvolutions
            ->map(function (ProntuarioEvolucao $evolution) {
                $record = $evolution->prontuario;
                $animal = $record?->animal;
                $date = $evolution->registrado_em ?: $evolution->created_at;
                $author = $evolution->autor?->name;

                return [
                    'title' => $evolution->titulo ?: ($animal?->nome ?: 'Atualização clínica'),
                    'description' => Str::limit(strip_tags((string) $evolution->descricao), 140) ?: 'Atualização registrada pela equipe clínica.',
                    'time' => $date ? Carbon::parse($date)->diffForHumans() : 'Agora',
                    'icon' => 'ri-clipboard-line',
                    'author' => $author,
                ];
            })
            ->values()
            ->all();
    }

    private function buildClinicalAlerts(array $statusCounts): array
    {
        $alerts = [];

        $awaitingReview = $statusCounts[Prontuario::STATUS_AWAITING_REVIEW] ?? 0;
        $inProgress = $statusCounts[Prontuario::STATUS_IN_PROGRESS] ?? 0;
        $archived = $statusCounts[Prontuario::STATUS_ARCHIVED] ?? 0;

        if ($awaitingReview > 0) {
            $alerts[] = [
                'label' => 'Prontuários aguardando revisão',
                'value' => $awaitingReview,
                'description' => 'É recomendada a avaliação clínica antes da alta.',
                'type' => 'warning',
            ];
        }

        if ($inProgress > 0) {
            $alerts[] = [
                'label' => 'Prontuários em andamento',
                'value' => $inProgress,
                'description' => 'Mantenha o acompanhamento dos pacientes atualmente em atendimento.',
                'type' => 'info',
            ];
        }

        if ($archived > 0) {
            $alerts[] = [
                'label' => 'Arquivados recentemente',
                'value' => $archived,
                'description' => 'Prontuários arquivados ficam disponíveis para consulta histórica.',
                'type' => 'secondary',
            ];
        }

        return $alerts;
    }

    private function formatFilterOptions(array $options): array
    {
        return collect($options)
            ->map(function ($label, $value) {
                return [
                    'value' => (string) $value,
                    'label' => (string) $label,
                ];
            })
            ->values()
            ->all();
    }

    private function buildTypeFilterOptions(int $companyId): array
    {
        return Prontuario::query()
            ->forCompany($companyId)
            ->select('tipo')
            ->whereNotNull('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo')
            ->map(function ($type) {
                $label = Str::of((string) $type)->squish()->title()->toString();

                return [
                    'value' => (string) $type,
                    'label' => $label !== '' ? $label : 'Outro tipo',
                ];
            })
            ->values()
            ->all();
    }

    private function loadVeterinarianFilters(int $companyId): array
    {
        return Medico::query()
            ->with('funcionario')
            ->where('empresa_id', $companyId)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'ativo');
            })
            ->orderBy('id')
            ->get()
            ->map(function (Medico $medico) {
                $name = optional($medico->funcionario)->nome ?? 'Profissional sem nome';

                return [
                    'value' => (string) $medico->id,
                    'label' => $name,
                ];
            })
            ->values()
            ->all();
    }

    private function defaultTimeframeFilters(): array
    {
        return [
            ['value' => 'today', 'label' => 'Hoje'],
            ['value' => 'last_7_days', 'label' => 'Últimos 7 dias'],
            ['value' => 'last_30_days', 'label' => 'Últimos 30 dias'],
            ['value' => 'last_90_days', 'label' => 'Últimos 90 dias'],
            ['value' => 'this_month', 'label' => 'Este mês'],
            ['value' => 'this_year', 'label' => 'Este ano'],
        ];
    }

    private function prepareFormViewData(int $companyId, Request $request, ?Prontuario $record = null): array
    {
        $patients = collect($this->fetchPatients($companyId));
        $veterinarians = $this->fetchVeterinarians($companyId);
        $assessmentModels = ModeloAvaliacao::query()
            ->select(['id', 'title', 'category', 'notes', 'fields', 'status'])
            ->where('empresa_id', $companyId)
            ->where('status', AssessmentModelOptions::STATUS_ACTIVE)
            ->orderBy('title')
            ->get()
            ->map(function (ModeloAvaliacao $modelo) {
                $modelData = $this->transformAssessmentModel($modelo);
                $modelData['status'] = $modelo->status;

                return $modelData;
            })
            ->values()
            ->all();

        if (empty($assessmentModels)) {
            $assessmentModels = array_values(array_map(
                function (array $model): array {
                    $model['fields_count'] = $model['fields_count'] ?? count($model['fields'] ?? []);
                    $model['status'] = AssessmentModelOptions::STATUS_ACTIVE;

                    return $model;
                },
                $this->demoAssessmentModels()
            ));
        }

        $checklists = $this->loadClinicalChecklists($companyId);

        $appointmentSlots = collect(range(8, 19))
            ->map(function (int $hour) {
                $label = sprintf('%02d:00', $hour);

                return [
                    'value' => $label,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();

        $timezone = config('app.timezone', 'America/Sao_Paulo');

        $prefill = null;
        $linkedAttendance = $record?->atendimento;

        if ($record) {
            $prefill = $this->transformRecordForPrefill($record);
        } elseif ($request->filled('attendance')) {
            $attendanceId = (int) $request->input('attendance');

            $attendance = Atendimento::query()
                ->with(['animal.cliente', 'animal.especie', 'animal.raca', 'tutor', 'veterinario.funcionario', 'sala', 'servico'])
                ->forCompany($companyId)
                ->find($attendanceId);

            if ($attendance) {
                $prefill = $this->buildAttendancePrefill($attendance, $patients, $timezone);
                $linkedAttendance = $attendance;
            }
        }

        $attendanceContext = $linkedAttendance
            ? $this->formatAttendanceContext($linkedAttendance)
            : null;

        $reminders = $record && is_array($record->lembretes)
            ? $record->lembretes
            : [
                ['message' => 'Agendar retorno em 7 dias', 'time' => 'Sugestão automática'],
                ['message' => 'Confirmar vacinação pendente', 'time' => 'Próximo contato com o tutor'],
            ];

        $attachments = $record && is_array($record->anexos) ? $record->anexos : [];

        $communications = $record && is_array($record->comunicacoes)
            ? $record->comunicacoes
            : [
                [
                    'channel' => 'E-mail',
                    'subject' => 'Retorno agendado para o seu pet',
                    'message' => "Olá {{ tutor }},\nRegistramos o atendimento do {{ paciente }}. Recomendamos retorno em {{ data_retorno }} para acompanhamento.",
                ],
                [
                    'channel' => 'WhatsApp',
                    'subject' => 'Cuidados pós-atendimento',
                    'message' => "Oi {{ tutor }}! Seguem as orientações principais sobre o {{ paciente }}. Qualquer dúvida estamos à disposição.",
                ],
            ];

        $quickNotes = [
            'vital_signs' => [
                ['label' => 'Sinais vitais estáveis', 'value' => 'Temperatura, FC e FR dentro dos parâmetros de referência.'],
                ['label' => 'Peso atualizado', 'value' => 'Peso aferido e compatível com o histórico clínico.'],
            ],
            'monitoring' => [
                ['label' => 'Acompanhamento domiciliar', 'value' => 'Orientado tutor a observar apetite e nível de atividade nas próximas 48 horas.'],
                ['label' => 'Reavaliação', 'value' => 'Programar nova avaliação clínica para revisar resposta ao tratamento.'],
            ],
        ];

        $evolutionTimeline = $record
            ? collect($record->evolucoes)
                ->sortBy(function (ProntuarioEvolucao $evolution) {
                    return $evolution->registrado_em ?: $evolution->created_at;
                })
                ->map(function (ProntuarioEvolucao $evolution) {
                    $date = $evolution->registrado_em ?: $evolution->created_at;

                    return [
                        'time' => $date ? Carbon::parse($date)->format('d/m/Y H:i') : '—',
                        'title' => $evolution->titulo ?: 'Evolução clínica',
                        'description' => Str::limit(strip_tags((string) $evolution->descricao), 160) ?: 'Atualização registrada pela equipe.',
                    ];
                })
                ->values()
                ->all()
            : [];

        return [
            'patients' => $patients->all(),
            'veterinarians' => $veterinarians,
            'appointmentSlots' => $appointmentSlots,
            'assessmentModels' => $assessmentModels,
            'checklists' => $checklists,
            'reminders' => $reminders,
            'attachments' => $attachments,
            'communicationTemplates' => $communications,
            'quickNotes' => $quickNotes,
            'evolutionTimeline' => $evolutionTimeline,
            'prefill' => $prefill,
            'attendanceContext' => $attendanceContext,
        ];
    }

    private function formatAttendanceContext(Atendimento $attendance): array
    {
        $scheduledAt = $attendance->start_at instanceof Carbon
            ? $attendance->start_at->format('d/m/Y H:i')
            : null;

        $code = $attendance->codigo ?: Atendimento::generateCode($attendance->getKey());

        return [
            'id' => (string) $attendance->id,
            'code' => $code,
            'status' => $attendance->status_label,
            'status_color' => $attendance->status_color,
            'scheduled_at' => $scheduledAt,
            'patient' => $attendance->animal?->nome,
            'veterinarian' => optional($attendance->veterinario?->funcionario)->nome,
            'url' => route('vet.atendimentos.history', $attendance->id),
        ];
    }

    private function buildProntuarioPayload(int $companyId, array $data, ?Prontuario $record = null): array
    {
        $dataRegistro = $data['data_registro'] ?? null;

        if ($dataRegistro) {
            try {
                $dataRegistro = Carbon::parse($dataRegistro);
            } catch (\Throwable $exception) {
                $dataRegistro = null;
            }
        }

        $payload = [
            'empresa_id' => $companyId,
            'atendimento_id' => $data['atendimento_id'] ?? null,
            'animal_id' => $data['paciente_id'] ?? null,
            'tutor_id' => $data['tutor_id'] ?? null,
            'veterinario_id' => $data['veterinario_id'] ?? null,
            'status' => $data['status'] ?? ($record?->status ?? Prontuario::STATUS_DRAFT),
            'tipo' => $data['tipo'] ?? ($record?->tipo),
            'data_registro' => $dataRegistro,
        ];

        $optionalTextColumns = [
            'queixa_principal' => $data['queixa_principal'] ?? null,
            'historico_clinico' => $data['historico_clinico'] ?? null,
            'avaliacao_fisica' => $data['avaliacao_fisica'] ?? null,
            'diagnostico_presuntivo' => $data['diagnostico_presuntivo'] ?? null,
            'diagnostico_definitivo' => $data['diagnostico_definitivo'] ?? null,
            'plano_terapeutico' => $data['plano_terapeutico'] ?? null,
            'orientacoes_tutor' => $data['orientacoes_tutor'] ?? null,
            'observacoes_adicionais' => $data['observacoes_adicionais'] ?? null,
        ];

        foreach ($optionalTextColumns as $column => $value) {
            $this->assignColumnIfExists($payload, $column, $value);
        }

        $jsonColumns = [
            'sinais_vitais' => $this->normaliseArrayPayload($data['sinais_vitais'] ?? null),
            'snapshot_paciente' => $this->normaliseArrayPayload($data['snapshot_paciente'] ?? null),
            'snapshot_tutor' => $this->normaliseArrayPayload($data['snapshot_tutor'] ?? null),
            'dados_triagem' => $this->normaliseArrayPayload($data['dados_triagem'] ?? null),
            'lembretes' => $this->normaliseArrayPayload($data['lembretes'] ?? null),
            'checklists' => $this->normaliseArrayPayload($data['checklists'] ?? null),
            'comunicacoes' => $this->normaliseArrayPayload($data['comunicacoes'] ?? null),
            'anexos' => $this->normaliseArrayPayload($data['anexos'] ?? null),
            'metadata' => $this->normaliseArrayPayload($data['metadata'] ?? null),
        ];

        foreach ($jsonColumns as $column => $value) {
            if (is_array($value) && $value === []) {
                $value = null;
            }

            $this->assignColumnIfExists($payload, $column, $value);
        }

        $assessmentMeta = [];
        $assessmentData = $this->normaliseArrayPayload($data['avaliacao_personalizada'] ?? null);

        if (is_array($assessmentData) && isset($assessmentData['__meta']) && is_array($assessmentData['__meta'])) {
            $assessmentMeta = $assessmentData['__meta'];
        }

        if (is_array($assessmentData) && $assessmentData === []) {
            $assessmentData = null;
        }

        if ($this->prontuarioTableHasColumn('avaliacao_personalizada')) {
            $payload['avaliacao_personalizada'] = $assessmentData;
        } elseif ($this->prontuarioTableHasColumn('campos_avaliacao')) {
            $payload['campos_avaliacao'] = $assessmentData;
        }

        $summaryValue = $data['resumo_rapido'] ?? null;
        $summaryColumn = $this->prontuarioTableHasColumn('resumo_rapido')
            ? 'resumo_rapido'
            : ($this->prontuarioTableHasColumn('resumo') ? 'resumo' : null);

        if ($summaryColumn) {
            $payload[$summaryColumn] = $summaryValue;
        }

        if (!$record) {
            $this->assignColumnIfExists($payload, 'created_by', Auth::id());
        }

        $this->assignColumnIfExists($payload, 'updated_by', Auth::id());

        if (empty($payload['tutor_id']) && !empty($payload['animal_id'])) {
            $payload['tutor_id'] = Animal::query()->whereKey($payload['animal_id'])->value('cliente_id');
        }

        $assessmentModelId = $this->resolveAssessmentModelId($data, $assessmentMeta);

        if ($assessmentModelId !== null && $this->prontuarioTableHasColumn('modelo_avaliacao_id')) {
            $payload['modelo_avaliacao_id'] = $assessmentModelId;
        }

        if ($this->prontuarioTableHasColumn('metadata')) {
            $metadata = $payload['metadata'] ?? [];

            if (!is_array($metadata)) {
                $metadata = [];
            }

            $payload['metadata'] = $this->ensureAssessmentModelMetadata(
                $metadata,
                $payload['modelo_avaliacao_id'] ?? $assessmentModelId,
                $assessmentMeta
            );
        }

        return $payload;
    }

    private function normaliseArrayPayload($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        return $value ? (array) $value : null;
    }

    private function assignColumnIfExists(array &$payload, string $column, $value): void
    {
        if (!$this->prontuarioTableHasColumn($column)) {
            return;
        }

        $payload[$column] = $value;
    }

    private function syncRecordRelations(Prontuario $record, array $data): void
    {
        $evolutions = collect($data['evolucoes'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) {
                $title = Str::of((string) ($item['titulo'] ?? $item['title'] ?? ''))
                    ->squish()
                    ->toString();
                $description = $item['descricao'] ?? $item['description'] ?? null;

                if ($title === '' && empty($description)) {
                    return null;
                }

                $registeredAt = $item['registrado_em'] ?? $item['date'] ?? null;

                if ($registeredAt) {
                    try {
                        $registeredAt = Carbon::parse($registeredAt);
                    } catch (\Throwable $exception) {
                        $registeredAt = null;
                    }
                }

                return [
                    'categoria' => $item['categoria'] ?? null,
                    'titulo' => $title !== '' ? $title : 'Evolução clínica',
                    'descricao' => $description,
                    'registrado_em' => $registeredAt,
                    'registrado_por' => $item['registrado_por'] ?? Auth::id(),
                    'dados' => $item['dados'] ?? [],
                ];
            })
            ->filter()
            ->values();

        $record->evolucoes()->delete();

        foreach ($evolutions as $evolution) {
            $record->evolucoes()->create($evolution);
        }
    }

    private function transformRecordForPrefill(Prontuario $record): array
    {
        $animal = $record->animal;
        $tutor = $record->tutor ?: $animal?->cliente;
        $veterinarian = $record->veterinario;
        $slotValue = $record->data_registro ? $record->data_registro->format('H:i') : null;
        $slotOption = $slotValue ? ['value' => $slotValue, 'label' => $record->data_registro->format('H:i')] : null;
        $typeOption = $record->tipo ? $this->resolveAttendanceTypeOption($record->tipo) : null;

        $patientSnapshot = $record->snapshot_paciente ?? [];
        $defaultAvatar = $this->buildAvatarUrl($animal?->nome);

        $defaultSnapshot = [
            'id' => $animal?->id ? (string) $animal->id : null,
            'name' => $animal?->nome ?? 'Paciente',
            'species' => $animal?->especie?->nome ?? 'Espécie não informada',
            'breed' => $animal?->raca?->nome ?? 'Sem raça definida',
            'gender' => $animal ? $this->formatGender($animal->sexo) : null,
            'sex' => $animal ? $this->formatGender($animal->sexo) : null,
            'age' => $animal ? $this->formatAge($animal) : null,
            'weight' => $this->formatWeight($animal?->peso),
            'birth_date' => $animal ? $this->formatDate($animal->data_nascimento) : null,
            'size' => $animal ? $this->formatTitleCase($animal->porte) : null,
            'origin' => $animal ? $this->formatTitleCase($animal->origem) : null,
            'microchip' => $animal?->chip ?: null,
            'pedigree' => $animal ? $this->formatPedigree($animal) : null,
            'tutor' => $animal ? $this->formatTutorName($animal) : null,
            'tutor_document' => $this->formatTutorDocument($tutor),
            'tutor_address' => $this->formatTutorAddress($tutor),
            'contact' => $this->formatTutorContact($tutor),
            'email' => $this->formatTutorEmail($tutor),
            'tutor_contacts' => $this->extractTutorContacts($tutor),
            'photo' => $defaultAvatar,
            'photo_url' => $defaultAvatar,
            'tags' => $animal ? $this->buildPatientTags($animal) : [],
            'recent_notes' => [],
            'summary' => $this->resolveRecordSummary($record, false),
            'notes' => $animal?->observacao,
        ];

        if (!is_array($patientSnapshot) || empty($patientSnapshot)) {
            $patientSnapshot = $defaultSnapshot;
        } else {
            $patientSnapshot = array_merge($defaultSnapshot, $patientSnapshot);
        }

        $patientSnapshot['photo_url'] = $this->normalizeAvatarUrl(
            $patientSnapshot['photo_url'] ?? null,
            $defaultAvatar
        );

        $patientSnapshot['photo'] = $this->normalizeAvatarUrl(
            $patientSnapshot['photo'] ?? null,
            $patientSnapshot['photo_url'] ?? $defaultAvatar
        );

        if (empty($patientSnapshot['primary_contact']) && !empty($patientSnapshot['tutor_contacts'])) {
            $patientSnapshot['primary_contact'] = $patientSnapshot['tutor_contacts'][0];
        }

        $evolutions = $record->evolucoes
            ->map(function (ProntuarioEvolucao $evolution) {
                return [
                    'id' => (int) $evolution->id,
                    'categoria' => $evolution->categoria,
                    'titulo' => $evolution->titulo,
                    'descricao' => $evolution->descricao,
                    'registrado_em' => optional($evolution->registrado_em)->toIso8601String(),
                    'registrado_por' => $evolution->registrado_por,
                    'dados' => $evolution->dados ?? [],
                ];
            })
            ->values()
            ->all();

        $patientLabel = collect([
            $animal?->nome ?? 'Paciente',
            $animal?->especie?->nome,
        ])->filter()->implode(' • ');

        $veterinarianLabel = $veterinarian ? collect([
            optional($veterinarian->funcionario)->nome ?? 'Profissional',
            $veterinarian->especialidade ?: null,
        ])->filter()->implode(' • ') : null;

        $triageData = is_array($record->dados_triagem) ? $record->dados_triagem : [];
        $assessmentPrefill = $this->resolveAssessmentPrefill($record);

        $assessmentPayload = null;

        if (
            !empty($assessmentPrefill['model'])
            || !empty($assessmentPrefill['values'])
            || !empty($assessmentPrefill['meta'])
        ) {
            $assessmentPayload = [
                'model' => $assessmentPrefill['model'],
                'values' => $assessmentPrefill['values'],
                'meta' => $assessmentPrefill['meta'],
            ];
        }

        return [
            'id' => (int) $record->id,
            'code' => $record->codigo,
            'status' => $record->status,
            'type' => $record->tipo,
            'data_registro' => optional($record->data_registro)->toIso8601String(),
            'patient' => [
                'id' => $animal?->id ? (string) $animal->id : null,
                'name' => $animal?->nome ?? 'Paciente',
                'label' => $patientLabel !== '' ? $patientLabel : ($animal?->nome ?? 'Paciente'),
            ],
            'patient_snapshot' => $patientSnapshot,
            'tutor' => [
                'id' => $tutor?->id ? (string) $tutor->id : null,
                'name' => $this->formatTutorName($animal ?? new Animal()),
                'contacts' => $this->extractTutorContacts($tutor),
                'document' => $this->formatTutorDocument($tutor),
                'address' => $this->formatTutorAddress($tutor),
                'contact' => $this->formatTutorContact($tutor),
                'email' => $this->formatTutorEmail($tutor),
            ],
            'veterinarian' => $veterinarian ? [
                'id' => (string) $veterinarian->id,
                'name' => optional($veterinarian->funcionario)->nome ?? 'Profissional',
                'specialty' => $veterinarian->especialidade ?: 'Especialidade não informada',
                'label' => $veterinarianLabel ?: (optional($veterinarian->funcionario)->nome ?? 'Profissional'),
            ] : null,
            'attendance' => [
                'id' => $record->atendimento_id,
                'type' => $typeOption['value'] ?? $record->tipo,
                'type_label' => $typeOption['label'] ?? ($record->tipo ? Str::title($record->tipo) : null),
                'type_option' => $typeOption,
                'slot' => $slotOption['value'] ?? null,
                'slot_label' => $slotOption['label'] ?? null,
                'slot_option' => $slotOption,
                'summary' => $this->resolveRecordSummary($record, false),
            ],
            'triage' => [
                'vital_signs' => $record->sinais_vitais ?? [],
                'monitoring' => Arr::get($triageData, 'monitoring', []),
                'timeline' => collect(Arr::get($triageData, 'timeline', []))->values()->all(),
                'checklists_completed' => $this->collectChecklistCompletions($record->checklists),
            ],
            'assessment' => $assessmentPayload,
            'evolucoes' => $evolutions,
            'lembretes' => $record->lembretes ?? [],
            'checklists' => $record->checklists ?? [],
            'comunicacoes' => $record->comunicacoes ?? [],
            'anexos' => $record->anexos ?? [],
            'metadata' => $record->metadata ?? [],
            'avaliacao_personalizada' => $assessmentPrefill['raw'],
        ];
    }

    private function isManagedAttachmentPath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        $normalized = ltrim($path, '/');

        return Str::startsWith($normalized, self::ATTACHMENT_DIRECTORY);
    }

    private function deleteAttachmentFromStorage(string $path): void
    {
        $normalized = ltrim($path, '/');

        $disk = Storage::disk(self::ATTACHMENT_STORAGE_DISK);

        if ($disk->exists($normalized)) {
            $disk->delete($normalized);
        }
    }

    private function buildAttachmentUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (!$this->isManagedAttachmentPath($normalized)) {
            return $path;
        }

        try {
            return Storage::disk(self::ATTACHMENT_STORAGE_DISK)->url($normalized);
        } catch (\Throwable $exception) {
            report($exception);

            $baseUrl = rtrim((string) env('AWS_URL'), '/');

            return $baseUrl ? $baseUrl . '/' . $normalized : null;
        }
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $size = $bytes / 1024;

        foreach ($units as $index => $unit) {
            if ($size < 1024 || $index === count($units) - 1) {
                $precision = $size >= 10 ? 0 : 2;

                return number_format($size, $precision, ',', '.') . ' ' . $unit;
            }

            $size /= 1024;
        }

        return number_format($size, 2, ',', '.') . ' PB';
    }

    private function ensureRecordCompany(Prontuario $record, int $companyId): void
    {
        if ((int) $record->empresa_id !== (int) $companyId) {
            abort(403, 'Prontuário não pertence à empresa autenticada.');
        }
    }

    private function prontuarioTableHasColumn(string $column): bool
    {
        if ($this->prontuarioTableColumns === null) {
            $table = (new Prontuario())->getTable();

            $this->prontuarioTableColumns = Schema::hasTable($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return in_array($column, $this->prontuarioTableColumns, true);
    }

    private function resolveRecordSummary(Prontuario $record, bool $includeFallbackFields = true): string
    {
        $summary = $record->resumo_rapido;

        if ($summary === null && $this->prontuarioTableHasColumn('resumo')) {
            $summary = $record->getAttribute('resumo');
        }

        $summaryString = (string) ($summary ?? '');

        if ($includeFallbackFields) {
            $normalized = Str::of($summaryString)->squish()->toString();

            if ($normalized === '') {
                $fallback = $record->queixa_principal ?? $record->observacoes_adicionais ?? '';

                return (string) $fallback;
            }
        }

        return $summaryString;
    }

    private function loadClinicalChecklists(int $companyId): array
    {
        return Checklist::query()
            ->select(['id', 'titulo', 'descricao', 'itens'])
            ->where('empresa_id', $companyId)
            ->where('status', 'ativo')
            ->where('tipo', 'prontuario')
            ->orderBy('titulo')
            ->get()
            ->map(function (Checklist $checklist) {
                $items = collect($checklist->itens ?? [])
                    ->map(function ($item, int $index) use ($checklist) {
                        if (is_string($item)) {
                            $label = trim($item);

                            if ($label === '') {
                                return null;
                            }

                            return [
                                'id' => sprintf('checklist-%s-item-%d', $checklist->getKey(), $index + 1),
                                'label' => $label,
                            ];
                        }

                        if (is_array($item)) {
                            $label = trim((string) ($item['label'] ?? $item['titulo'] ?? ''));

                            if ($label === '') {
                                return null;
                            }

                            $itemId = $item['id'] ?? sprintf('checklist-%s-item-%d', $checklist->getKey(), $index + 1);

                            return [
                                'id' => (string) $itemId,
                                'label' => $label,
                            ];
                        }

                        return null;
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => (string) $checklist->getKey(),
                    'title' => $checklist->titulo,
                    'description' => $checklist->descricao,
                    'items' => $items,
                ];
            })
            ->filter(static fn (array $checklist) => ! empty($checklist['items']))
            ->values()
            ->all();
    }

    public function fetchAssessmentModel(string $modeloAvaliacao): JsonResponse
    {
        $companyId = $this->getEmpresaId();

        $modeloId = $modeloAvaliacao;

        if ($companyId) {
            $modelo = ModeloAvaliacao::query()
                ->whereKey($modeloId)
                ->where('empresa_id', $companyId)
                ->first();

            if ($modelo && $modelo->status === AssessmentModelOptions::STATUS_ACTIVE) {
                return response()->json($this->transformAssessmentModel($modelo));
            }
        }

        $demoModels = $this->demoAssessmentModels();

        if (isset($demoModels[$modeloId])) {
            return response()->json($demoModels[$modeloId]);
        }

        abort(404);
    }

    private function buildAttendancePrefill(Atendimento $attendance, Collection $patients, string $timezone): array
    {
        $queueItem = $this->mapAttendanceToQueueItem($attendance, Carbon::now($timezone), $timezone);

        $animal = $attendance->animal;
        $patientId = $animal?->id ? (string) $animal->id : null;
        $patientSnapshot = $patientId ? ($patients->firstWhere('id', $patientId) ?: null) : null;

        $summarySource = Str::of((string) ($attendance->observacoes_triagem ?: $attendance->motivo_visita ?: ''))
            ->trim()
            ->limit(500)
            ->toString();

        $triageNotes = $this->buildTriageNotes($attendance, $timezone);

        if ($patientSnapshot) {
            if ($attendance->peso !== null && $attendance->peso !== '') {
                $patientSnapshot['weight'] = $this->formatWeight($attendance->peso);
            }

            if ($summarySource !== '') {
                $patientSnapshot['summary'] = $summarySource;
            }

            $existingNotes = isset($patientSnapshot['recent_notes']) && is_array($patientSnapshot['recent_notes'])
                ? $patientSnapshot['recent_notes']
                : [];

            $patientSnapshot['recent_notes'] = array_values(array_merge($existingNotes, $triageNotes));
        } else {
            $patientSnapshot = [
                'id' => $patientId,
                'name' => $queueItem['patient']['name'] ?? 'Paciente',
                'species' => $queueItem['patient']['species'] ?? 'Espécie não informada',
                'breed' => $queueItem['patient']['breed'] ?? 'Sem raça definida',
                'gender' => $animal ? $this->formatGender($animal->sexo) : null,
                'sex' => $animal ? $this->formatGender($animal->sexo) : null,
                'age' => $animal ? $this->formatAge($animal) : null,
                'weight' => $attendance->peso !== null && $attendance->peso !== ''
                    ? $this->formatWeight($attendance->peso)
                    : $this->formatWeight($animal?->peso),
                'birth_date' => $animal ? $this->formatDate($animal->data_nascimento) : null,
                'size' => $animal ? $this->formatTitleCase($animal->porte) : null,
                'origin' => $animal ? $this->formatTitleCase($animal->origem) : null,
                'microchip' => $animal?->chip ?: null,
                'pedigree' => $animal ? $this->formatPedigree($animal) : null,
                'tutor' => $queueItem['tutor'] ?? ($animal ? $this->formatTutorName($animal) : null),
                'tutor_contacts' => $queueItem['tutor_contacts'] ?? $this->extractTutorContacts($attendance->tutor ?? $animal?->cliente),
                'tutor_document' => $attendance->tutor ? $this->formatTutorDocument($attendance->tutor) : ($animal ? $this->formatTutorDocument($animal->cliente) : null),
                'tutor_address' => $attendance->tutor ? $this->formatTutorAddress($attendance->tutor) : ($animal ? $this->formatTutorAddress($animal->cliente) : null),
                'contact' => $attendance->tutor ? $this->formatTutorContact($attendance->tutor) : ($animal ? $this->formatTutorContact($animal->cliente) : null),
                'email' => $attendance->tutor ? $this->formatTutorEmail($attendance->tutor) : ($animal ? $this->formatTutorEmail($animal->cliente) : null),
                'photo' => $queueItem['patient']['avatar'] ?? $this->buildAvatarUrl($queueItem['patient']['name'] ?? 'Paciente'),
                'photo_url' => $queueItem['patient']['avatar'] ?? $this->buildAvatarUrl($queueItem['patient']['name'] ?? 'Paciente'),
                'tags' => $queueItem['patient']['tags'] ?? [],
                'alerts' => [],
                'chronic_conditions' => [],
                'medications' => [],
                'recent_notes' => $triageNotes,
                'summary' => $summarySource !== '' ? $summarySource : null,
                'notes' => $animal?->observacao,
            ];
        }

        $avatarReference = $animal?->nome
            ?? ($patientSnapshot['name'] ?? ($queueItem['patient']['name'] ?? null));
        $avatarFallback = $this->buildAvatarUrl($avatarReference);
        $patientSnapshot['photo_url'] = $this->normalizeAvatarUrl(
            $patientSnapshot['photo_url'] ?? null,
            $avatarFallback
        );
        $patientSnapshot['photo'] = $this->normalizeAvatarUrl(
            $patientSnapshot['photo'] ?? null,
            $patientSnapshot['photo_url'] ?? $avatarFallback
        );

        $slotOption = $this->resolveAttendanceSlotOption($attendance, $timezone);
        $typeOption = $this->resolveAttendanceTypeOption($attendance->tipo_atendimento);

        $vitalSigns = array_values(array_filter([
            $attendance->peso !== null && $attendance->peso !== '' ? [
                'label' => 'Peso',
                'value' => $this->formatWeight($attendance->peso),
            ] : null,
            $attendance->temperatura !== null ? [
                'label' => 'Temperatura',
                'value' => number_format((float) $attendance->temperatura, 1, ',', '.') . ' °C',
            ] : null,
            $attendance->frequencia_cardiaca !== null ? [
                'label' => 'Frequência cardíaca',
                'value' => (int) $attendance->frequencia_cardiaca . ' bpm',
            ] : null,
            $attendance->frequencia_respiratoria !== null ? [
                'label' => 'Frequência respiratória',
                'value' => (int) $attendance->frequencia_respiratoria . ' irpm',
            ] : null,
        ]));

        $triageTimeline = array_values(array_filter([
            $attendance->motivo_visita ? [
                'time' => 'Triagem',
                'title' => 'Motivo da visita',
                'description' => Str::of($attendance->motivo_visita)->trim()->toString(),
            ] : null,
            $attendance->observacoes_triagem ? [
                'time' => 'Triagem',
                'title' => 'Observações da triagem',
                'description' => Str::of($attendance->observacoes_triagem)->trim()->toString(),
            ] : null,
            !empty($vitalSigns) ? [
                'time' => 'Triagem',
                'title' => 'Sinais vitais coletados',
                'description' => implode(' • ', array_map(static function (array $sign) {
                    return $sign['label'] . ': ' . $sign['value'];
                }, $vitalSigns)),
            ] : null,
        ]));

        $checklistsCompleted = $this->collectChecklistCompletions($attendance->checklists);

        $patientLabelParts = array_filter([
            $patientSnapshot['name'] ?? 'Paciente',
            $patientSnapshot['species'] ?? null,
            isset($patientSnapshot['tutor']) ? 'Tutor(a): ' . $patientSnapshot['tutor'] : null,
        ]);

        $vet = $attendance->veterinario;
        $vetEmployee = $vet?->funcionario;
        $vetName = $vetEmployee?->nome ?? 'Profissional não definido';
        $vetSpecialty = $vet?->especialidade ?: 'Especialidade não informada';

        return [
            'attendance' => [
                'id' => (int) $attendance->id,
                'code' => $attendance->codigo,
                'category' => $queueItem['category'] ?? null,
                'status' => $queueItem['status'] ?? null,
                'type' => $typeOption['value'] ?? null,
                'type_option' => $typeOption,
                'slot' => $slotOption['value'] ?? null,
                'slot_label' => $slotOption['label'] ?? null,
                'slot_option' => $slotOption,
                'summary' => $summarySource !== '' ? $summarySource : null,
                'notes' => $attendance->observacoes_triagem ? Str::of($attendance->observacoes_triagem)->trim()->toString() : null,
                'service' => $queueItem['service'] ?? null,
                'room' => $queueItem['room'] ?? null,
            ],
            'patient' => [
                'id' => $patientId,
                'name' => $patientSnapshot['name'] ?? ($queueItem['patient']['name'] ?? 'Paciente'),
                'label' => $patientLabelParts ? implode(' • ', $patientLabelParts) : null,
            ],
            'patient_snapshot' => $patientSnapshot,
            'veterinarian' => [
                'id' => $vet?->id ? (string) $vet->id : null,
                'name' => $vetName,
                'specialty' => $vetSpecialty,
                'label' => trim($vetName . ' • ' . $vetSpecialty),
            ],
            'triage' => [
                'vital_signs' => $vitalSigns,
                'monitoring' => [],
                'notes' => $attendance->observacoes_triagem ? Str::of($attendance->observacoes_triagem)->trim()->toString() : null,
                'timeline' => $triageTimeline,
                'checklists_completed' => $checklistsCompleted,
            ],
        ];
    }

    private function buildTriageNotes(Atendimento $attendance, string $timezone): array
    {
        $notes = [];

        $referenceDate = $attendance->data_atendimento instanceof Carbon
            ? $attendance->data_atendimento->copy()
            : ($attendance->data_atendimento
                ? Carbon::parse($attendance->data_atendimento, $timezone)
                : Carbon::now($timezone));

        if ($attendance->horario) {
            try {
                [$hour, $minute] = array_pad(explode(':', $attendance->horario), 2, '00');
                $referenceDate->setTime((int) $hour, (int) $minute);
            } catch (\Throwable $exception) {
                // Ignore parsing errors and keep original date
            }
        }

        $formattedDateTime = $referenceDate->format('d/m/Y H:i');

        if ($attendance->motivo_visita) {
            $notes[] = [
                'date' => $formattedDateTime,
                'author' => 'Motivo da visita',
                'content' => Str::of($attendance->motivo_visita)->trim()->toString(),
            ];
        }

        if ($attendance->observacoes_triagem) {
            $notes[] = [
                'date' => $formattedDateTime,
                'author' => 'Observações da triagem',
                'content' => Str::of($attendance->observacoes_triagem)->trim()->toString(),
            ];
        }

        return $notes;
    }

    private function resolveAttendanceSlotOption(Atendimento $attendance, string $timezone): ?array
    {
        if (!$attendance->data_atendimento) {
            return null;
        }

        $date = $attendance->data_atendimento instanceof Carbon
            ? $attendance->data_atendimento->copy()
            : Carbon::parse($attendance->data_atendimento, $timezone);

        if ($attendance->horario) {
            try {
                [$hour, $minute] = array_pad(explode(':', $attendance->horario), 2, '00');
                $date->setTime((int) $hour, (int) $minute);
            } catch (\Throwable $exception) {
                // Ignore invalid time formats
            }
        }

        $value = $date->format('Y-m-d\TH:i');
        $now = Carbon::now($timezone);

        $label = $date->isSameDay($now)
            ? 'Hoje'
            : $date->format('d/m');

        $label .= ' • ' . $date->format('H:i');

        if ($attendance->sala?->nome) {
            $label .= ' - ' . $attendance->sala->nome;
        } elseif ($attendance->servico?->nome) {
            $label .= ' - ' . $attendance->servico->nome;
        }

        return [
            'value' => $value,
            'label' => $label,
        ];
    }

    private function resolveAttendanceTypeOption(?string $type): ?array
    {
        if (!$type) {
            return null;
        }

        $normalized = Str::slug($type, '-');

        $map = [
            'consulta' => ['value' => 'consulta', 'label' => 'Consulta geral'],
            'consulta-geral' => ['value' => 'consulta', 'label' => 'Consulta geral'],
            'retorno' => ['value' => 'retorno', 'label' => 'Retorno programado'],
            'pos-operatorio' => ['value' => 'pos-operatorio', 'label' => 'Pós-operatório'],
            'emergencia' => ['value' => 'emergencia', 'label' => 'Emergência'],
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        $label = Str::of($type)->trim()->title()->toString();

        if ($label === '') {
            $label = 'Outro tipo';
        }

        $value = $normalized !== '' ? $normalized : 'outro';

        return [
            'value' => $value,
            'label' => $label,
        ];
    }

    private function collectChecklistCompletions($checklists): array
    {
        if (!is_array($checklists)) {
            return [];
        }

        $completed = [];

        $walker = function ($items) use (&$walker, &$completed): void {
            foreach ($items as $key => $value) {
                if (is_array($value)) {
                    if (isset($value['id']) && array_key_exists('completed', $value)) {
                        if ($value['completed']) {
                            $completed[] = (string) $value['id'];
                        }
                    } else {
                        $walker($value);
                    }
                } elseif ($value) {
                    $completed[] = is_string($value) ? $value : (string) $key;
                }
            }
        };

        $walker($checklists);

        return array_values(array_unique(array_filter(array_map('strval', $completed))));
    }

    private function resolveAssessmentModelId(array $data, array $assessmentMeta): ?int
    {
        $candidates = [
            $data['modelo_avaliacao_id'] ?? null,
            $data['assessment_model_id'] ?? null,
            Arr::get($data, 'metadata.assessment_model_id'),
            $assessmentMeta['model_id'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            if (is_string($candidate) && !is_numeric($candidate)) {
                continue;
            }

            $id = (int) $candidate;

            if ($id > 0) {
                return $id;
            }
        }

        return null;
    }

    private function ensureAssessmentModelMetadata(array $metadata, ?int $modelId, array $assessmentMeta): array
    {
        if ($modelId !== null && !array_key_exists('assessment_model_id', $metadata)) {
            $metadata['assessment_model_id'] = $modelId;
        }

        if (!empty($assessmentMeta)) {
            $existing = [];

            if (isset($metadata['assessment_model_meta']) && is_array($metadata['assessment_model_meta'])) {
                $existing = $metadata['assessment_model_meta'];
            }

            $metadata['assessment_model_meta'] = array_merge($existing, $assessmentMeta);
        }

        return $metadata;
    }

    private function resolveAssessmentPrefill(Prontuario $record): array
    {
        $raw = $record->avaliacao_personalizada;

        if (!is_array($raw) || $raw === []) {
            $raw = $record->campos_avaliacao;
        }

        if (!is_array($raw)) {
            $raw = [];
        }

        $meta = [];

        if (isset($raw['__meta']) && is_array($raw['__meta'])) {
            $meta = $raw['__meta'];
        }

        $values = Arr::except($raw, ['__meta']);

        $modelId = $record->modelo_avaliacao_id
            ?? (isset($meta['model_id']) && is_numeric($meta['model_id']) ? (int) $meta['model_id'] : null)
            ?? (is_numeric(Arr::get($record->metadata, 'assessment_model_id'))
                ? (int) Arr::get($record->metadata, 'assessment_model_id')
                : null);

        $modelData = null;

        if ($modelId) {
            $model = ModeloAvaliacao::query()
                ->select(['id', 'title', 'category', 'notes', 'status'])
                ->find($modelId);

            if ($model) {
                $modelData = [
                    'id' => (string) $model->id,
                    'title' => $model->title,
                    'category' => $model->category,
                    'category_label' => AssessmentModelOptions::categoryLabel($model->category) ?? 'Personalizado',
                    'notes' => $model->notes,
                    'status' => $model->status,
                ];
            }
        }

        if (!$modelData && !empty($meta)) {
            $modelData = [
                'id' => isset($meta['model_id']) ? (string) $meta['model_id'] : null,
                'title' => $meta['model_title'] ?? null,
                'category' => $meta['model_category'] ?? null,
                'category_label' => $meta['model_category_label'] ?? (
                    isset($meta['model_category'])
                        ? AssessmentModelOptions::categoryLabel((string) $meta['model_category'])
                        : null
                ),
                'notes' => $meta['model_notes'] ?? null,
            ];
        }

        return [
            'raw' => $raw,
            'values' => $values,
            'meta' => $meta,
            'model' => $modelData,
        ];
    }

    private function transformAssessmentModel(ModeloAvaliacao $modelo): array
    {
        $fields = collect($modelo->fields ?? [])
            ->filter(fn ($field) => is_array($field))
            ->values()
            ->map(function (array $field, int $index) {
                $label = Str::of((string) ($field['label'] ?? ''))
                    ->trim()
                    ->toString();

                if ($label === '') {
                    $label = 'Campo ' . ($index + 1);
                }

                $type = is_string($field['type'] ?? null)
                    ? $field['type']
                    : 'text';

                $config = is_array($field['config'] ?? null)
                    ? $this->normaliseAssessmentConfig($field['config'])
                    : [];

                return [
                    'label' => $label,
                    'type' => $type,
                    'type_label' => AssessmentModelOptions::fieldTypeLabel($type),
                    'config' => $config,
                ];
            })
            ->all();

        return [
            'id' => (string) $modelo->id,
            'title' => $modelo->title,
            'category' => $modelo->category,
            'category_label' => AssessmentModelOptions::categoryLabel($modelo->category) ?? 'Personalizado',
            'notes' => $modelo->notes,
            'fields' => $fields,
            'fields_count' => count($fields),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function normaliseAssessmentConfig(array $config): array
    {
        $normalised = [];

        foreach ($config as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $normalised[$key] = array_values(array_map(static fn ($item) => (string) $item, $value));
                continue;
            }

            $normalised[$key] = is_scalar($value)
                ? (string) $value
                : (string) json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $normalised;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function demoAssessmentModels(): array
    {
        $models = [
            'demo-anamnese-completa' => [
                'id' => 'demo-anamnese-completa',
                'title' => 'Modelo de Anamnese Completa',
                'category' => 'anamnese',
                'category_label' => 'Anamnese',
                'notes' => 'Estrutura sugerida para triagem clínica completa de pequenos animais.',
                'fields' => [
                    [
                        'label' => 'Queixa principal',
                        'type' => 'textarea',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('textarea'),
                        'config' => [
                            'textarea_placeholder' => 'Descreva a queixa principal informada pelo tutor.',
                        ],
                    ],
                    [
                        'label' => 'Histórico clínico',
                        'type' => 'textarea',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('textarea'),
                        'config' => [
                            'textarea_placeholder' => 'Alimentação, rotina, ambiente e eventos recentes.',
                        ],
                    ],
                    [
                        'label' => 'Exame físico',
                        'type' => 'rich_text',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('rich_text'),
                        'config' => [
                            'rich_text_default' => '<p>Temperatura, mucosas, hidratação, palpação abdominal...</p>',
                        ],
                    ],
                    [
                        'label' => 'Plano terapêutico',
                        'type' => 'textarea',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('textarea'),
                        'config' => [
                            'textarea_placeholder' => 'Exames complementares, medicações e recomendações.',
                        ],
                    ],
                ],
            ],
            'demo-pos-operatorio' => [
                'id' => 'demo-pos-operatorio',
                'title' => 'Acompanhamento Pós-operatório',
                'category' => 'pos-operatorio',
                'category_label' => 'Pós-operatório',
                'notes' => 'Checklist focado em pacientes recém-operados e monitoramento das funções vitais.',
                'fields' => [
                    [
                        'label' => 'Data da cirurgia',
                        'type' => 'date',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('date'),
                        'config' => [
                            'date_hint' => 'Informe a data do procedimento cirúrgico.',
                        ],
                    ],
                    [
                        'label' => 'Curativo',
                        'type' => 'select',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('select'),
                        'config' => [
                            'select_options' => ['Íntegro', 'Úmido', 'Sinais de infecção'],
                        ],
                    ],
                    [
                        'label' => 'Dor percebida',
                        'type' => 'radio_group',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('radio_group'),
                        'config' => [
                            'radio_group_options' => ['0 - Sem dor', '1 - Leve', '2 - Moderada', '3 - Intensa'],
                            'radio_group_default' => '1 - Leve',
                        ],
                    ],
                    [
                        'label' => 'Prescrição atualizada',
                        'type' => 'checkbox',
                        'type_label' => AssessmentModelOptions::fieldTypeLabel('checkbox'),
                        'config' => [
                            'checkbox_label_checked' => 'Prescrição revisada e entregue ao tutor',
                            'checkbox_label_unchecked' => 'Revisar prescrição antes da alta',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($models as &$model) {
            $model['fields_count'] = count($model['fields']);
        }

        return $models;
    }

    private function mapAttendanceToQueueItem(Atendimento $attendance, Carbon $now, string $timezone): array
    {
        $startAt = $this->resolveAttendanceStart($attendance, $timezone);
        $minutesToStart = $startAt ? $now->diffInMinutes($startAt, false) : null;
        $waitingMinutes = ($minutesToStart !== null && $minutesToStart <= 0)
            ? abs($minutesToStart)
            : 0;

        $elapsedMinutes = null;

        if ($startAt && $attendance->status === Atendimento::STATUS_IN_PROGRESS) {
            $elapsedMinutes = $startAt->diffInMinutes($now);
        } elseif ($startAt && $attendance->status === Atendimento::STATUS_COMPLETED) {
            $finishedAt = $attendance->updated_at
                ? Carbon::parse($attendance->updated_at, $timezone)
                : $now;

            $elapsedMinutes = $startAt->diffInMinutes($finishedAt);
        }

        $category = $this->resolveQueueCategory($attendance->status, $minutesToStart);

        $isDelayed = false;

        if ($category === 'waiting' && $waitingMinutes >= 5) {
            $isDelayed = true;
        }

        if ($category === 'in_progress' && $minutesToStart !== null && $minutesToStart < -15) {
            $isDelayed = true;
        }

        $animal = $attendance->animal;
        $vetEmployee = optional($attendance->veterinario)->funcionario;
        $vetName = $vetEmployee?->nome ?: 'Profissional não definido';
        $serviceName = $attendance->servico?->nome;

        $priorityLabel = $attendance->tipo_atendimento
            ? Str::title(str_replace('_', ' ', Str::lower($attendance->tipo_atendimento)))
            : null;

        return [
            'id' => (int) $attendance->id,
            'code' => $attendance->codigo ?? null,
            'category' => $category,
            'status' => [
                'code' => $attendance->status,
                'label' => $attendance->status_label,
                'color' => $attendance->status_color,
            ],
            'patient' => [
                'name' => $animal?->nome ?? 'Paciente sem identificação',
                'species' => optional($animal?->especie)->nome ?? 'Espécie não informada',
                'breed' => optional($animal?->raca)->nome ?? 'Sem raça definida',
                'avatar' => $this->buildAvatarUrl($animal?->nome ?? 'Paciente'),
            ],
            'tutor' => $this->formatTutorFromAttendance($attendance),
            'tutor_contacts' => $this->extractTutorContacts($attendance->tutor ?? $animal?->cliente),
            'service' => $serviceName ?: ($priorityLabel ?? 'Atendimento'),
            'room' => $attendance->sala?->nome,
            'priority' => $priorityLabel,
            'notes' => $attendance->motivo_visita
                ? Str::title(Str::lower($attendance->motivo_visita))
                : null,
            'scheduled_for' => $startAt ? $startAt->format('H:i') : null,
            'start_at' => $startAt,
            'minutes_to_start' => $minutesToStart,
            'waiting_minutes' => $waitingMinutes,
            'elapsed_minutes' => $elapsedMinutes,
            'is_delayed' => $isDelayed,
            'veterinarian' => [
                'id' => optional($attendance->veterinario)->id
                    ? (string) $attendance->veterinario->id
                    : null,
                'name' => $vetName,
                'avatar' => $this->buildAvatarUrl($vetName ?: 'Equipe'),
            ],
            'record' => $attendance->latestRecord
                ? [
                    'id' => (int) $attendance->latestRecord->id,
                    'created_at' => $attendance->latestRecord->created_at
                        ? $attendance->latestRecord->created_at->copy()->timezone($timezone)
                        : null,
                ]
                : null,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $queueItems
     * @return array<string, mixed>
     */
    private function buildCalendarView(Collection $queueItems, Carbon $selectedDate): array
    {
        $categoryLabels = [
            'in_progress' => 'Em atendimento',
            'waiting' => 'Em espera',
            'upcoming' => 'Agendado',
            'completed' => 'Finalizado',
            'cancelled' => 'Cancelado',
        ];

        $dayStart = $selectedDate->copy()->startOfDay();

        $scheduledEvents = $queueItems
            ->filter(static fn (array $item) => $item['start_at'] instanceof Carbon)
            ->map(function (array $item) use ($categoryLabels, $dayStart) {
                /** @var Carbon $startAt */
                $startAt = $item['start_at']->copy();

                $defaultDuration = $item['elapsed_minutes'] ?? null;

                if (is_int($defaultDuration) && $defaultDuration > 0) {
                    $duration = max(15, min(120, $defaultDuration));
                } else {
                    $duration = $item['category'] === 'in_progress' ? 50 : 40;
                }

                $endAt = $startAt->copy()->addMinutes($duration);

                $startMinutes = (int) $startAt->diffInMinutes($dayStart);
                $endMinutes = (int) $endAt->diffInMinutes($dayStart);

                $startHour = (int) $startAt->format('H');
                $startMinute = (int) $startAt->format('i');
                $endHour = (int) $endAt->format('H');
                $endMinute = (int) $endAt->format('i');

                return [
                    'id' => $item['id'],
                    'start_time' => $startAt->format('H:i'),
                    'end_time' => $endAt->format('H:i'),
                    'start_minutes' => $startMinutes,
                    'end_minutes' => $endMinutes,
                    'start_hour' => $startHour,
                    'end_hour' => $endHour,
                    'start_minute' => $startMinute,
                    'end_minute' => $endMinute,
                    'duration' => $duration,
                    'duration_label' => $duration . ' min',
                    'patient' => $item['patient']['name'] ?? 'Paciente',
                    'service' => $item['service'],
                    'veterinarian' => $item['veterinarian']['name'] ?? null,
                    'room' => $item['room'],
                    'status' => $item['status'],
                    'status_color' => $item['status']['color'] ?? 'primary',
                    'category' => $item['category'],
                    'category_label' => $categoryLabels[$item['category']] ?? Str::title(str_replace('_', ' ', (string) $item['category'])),
                    'priority' => $item['priority'],
                    'tutor' => $item['tutor'],
                    'is_delayed' => $item['is_delayed'],
                    'waiting_minutes' => $item['waiting_minutes'],
                    'minutes_to_start' => $item['minutes_to_start'],
                ];
            })
            ->sortBy(static fn (array $event) => $event['start_minutes'])
            ->values();

        $unscheduled = $queueItems
            ->filter(static fn (array $item) => ! ($item['start_at'] instanceof Carbon))
            ->map(function (array $item) use ($categoryLabels) {
                return [
                    'id' => $item['id'],
                    'patient' => $item['patient']['name'] ?? 'Paciente',
                    'service' => $item['service'],
                    'status' => $item['status'],
                    'status_color' => $item['status']['color'] ?? 'primary',
                    'category' => $item['category'],
                    'category_label' => $categoryLabels[$item['category']] ?? Str::title(str_replace('_', ' ', (string) $item['category'])),
                    'priority' => $item['priority'],
                    'veterinarian' => $item['veterinarian']['name'] ?? null,
                ];
            })
            ->values();

        if ($scheduledEvents->isNotEmpty()) {
            $minMinutes = (int) $scheduledEvents->min(static fn (array $event) => $event['start_minutes']);
            $maxMinutes = (int) $scheduledEvents->max(static fn (array $event) => $event['end_minutes']);

            $startHour = max(6, ((int) floor($minMinutes / 60)) - 1);
            $endHour = min(22, ((int) ceil($maxMinutes / 60)) + 1);

            if ($endHour <= $startHour) {
                $endHour = min(22, $startHour + 6);
            }
        } else {
            $startHour = 8;
            $endHour = 18;
        }

        $eventsByHour = $scheduledEvents->groupBy(static function (array $event) {
            return str_pad((string) $event['start_hour'], 2, '0', STR_PAD_LEFT);
        });

        $hours = collect(range($startHour, $endHour))
            ->map(function (int $hour) use ($eventsByHour) {
                $key = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);

                return [
                    'label' => sprintf('%02d:00', $hour),
                    'value' => $key,
                    'events' => $eventsByHour->get($key, collect())->values()->all(),
                ];
            })
            ->all();

        $summary = [
            'scheduled' => $scheduledEvents->count(),
            'unscheduled' => $unscheduled->count(),
            'in_progress' => $queueItems->where('category', 'in_progress')->count(),
            'waiting' => $queueItems->where('category', 'waiting')->count(),
            'upcoming' => $queueItems->where('category', 'upcoming')->count(),
        ];

        $peakHour = collect($hours)
            ->sortByDesc(static fn (array $hour) => count($hour['events']))
            ->first();

        $legend = [
            [
                'label' => 'Em atendimento',
                'variant' => 'info',
                'icon' => 'ri-pulse-line',
                'count' => $summary['in_progress'],
            ],
            [
                'label' => 'Em espera',
                'variant' => 'warning',
                'icon' => 'ri-time-line',
                'count' => $summary['waiting'],
            ],
            [
                'label' => 'Próximos',
                'variant' => 'primary',
                'icon' => 'ri-calendar-schedule-line',
                'count' => $summary['upcoming'],
            ],
        ];

        return [
            'hours' => $hours,
            'events' => $scheduledEvents->all(),
            'unscheduled' => $unscheduled->all(),
            'summary' => array_merge($summary, [
                'peak_hour' => $peakHour ? $peakHour['label'] : null,
                'peak_count' => $peakHour ? count($peakHour['events']) : null,
            ]),
            'legend' => array_values(array_filter($legend, static fn (array $item) => $item['count'] > 0)),
        ];
    }

    private function resolveQueueCategory(string $status, ?int $minutesToStart): string
    {
        if ($status === Atendimento::STATUS_IN_PROGRESS) {
            return 'in_progress';
        }

        if ($status === Atendimento::STATUS_COMPLETED) {
            return 'completed';
        }

        if ($status === Atendimento::STATUS_CANCELLED) {
            return 'cancelled';
        }

        if ($status === Atendimento::STATUS_SCHEDULED) {
            return 'waiting';
        }

        if ($minutesToStart === null) {
            return 'waiting';
        }

        return $minutesToStart <= 0 ? 'waiting' : 'upcoming';
    }

    private function resolveAttendanceStart(Atendimento $attendance, string $timezone): ?Carbon
    {
        $dateValue = $attendance->data_atendimento;

        if ($dateValue instanceof Carbon) {
            $date = $dateValue->copy()->setTimezone($timezone);
        } elseif (!empty($dateValue)) {
            try {
                $date = Carbon::parse((string) $dateValue, $timezone);
            } catch (\Throwable $exception) {
                $date = null;
            }
        } else {
            $date = null;
        }

        if (!$date) {
            return null;
        }

        if ($attendance->horario) {
            try {
                [$hour, $minute] = array_pad(explode(':', $attendance->horario), 2, '0');
                $date->setTime((int) $hour, (int) $minute);
            } catch (\Throwable $exception) {
                // Ignora formato inválido de horário.
            }
        }

        return $date;
    }

    private function groupQueueItems(Collection $queueItems): array
    {
        return [
            'in_progress' => $queueItems->where('category', 'in_progress')->values(),
            'waiting' => $queueItems->where('category', 'waiting')->values(),
            'upcoming' => $queueItems->where('category', 'upcoming')->values(),
            'completed' => $queueItems->where('category', 'completed')->values(),
            'cancelled' => $queueItems->where('category', 'cancelled')->values(),
        ];
    }

    private function buildQueueMetrics(Collection $queueItems, Carbon $selectedDate): array
    {
        $waiting = $queueItems->where('category', 'waiting');
        $inProgress = $queueItems->where('category', 'in_progress');
        $upcoming = $queueItems->where('category', 'upcoming');
        $completed = $queueItems->where('category', 'completed');

        $waitingAverage = (int) round($waiting
            ->filter(fn (array $item) => ($item['waiting_minutes'] ?? 0) > 0)
            ->avg('waiting_minutes') ?? 0);

        $waitingDescription = $waitingAverage > 0
            ? 'Tempo médio de espera: ' . $this->formatMinutes($waitingAverage)
            : 'Sem fila no momento.';

        $nextUpcoming = $upcoming
            ->sortBy('minutes_to_start')
            ->first();

        $delayedCount = $queueItems
            ->filter(fn (array $item) => $item['is_delayed'] ?? false)
            ->count();

        $onTimeRate = $queueItems->count() > 0
            ? (int) round((($queueItems->count() - $delayedCount) / $queueItems->count()) * 100)
            : 100;

        return [
            [
                'label' => 'Em espera',
                'value' => $waiting->count(),
                'icon' => 'ri-time-line',
                'variant' => 'warning',
                'description' => $waitingDescription,
            ],
            [
                'label' => 'Em atendimento',
                'value' => $inProgress->count(),
                'icon' => 'ri-stethoscope-line',
                'variant' => 'info',
                'description' => $inProgress->count()
                    ? 'Consultas em andamento neste momento.'
                    : 'Nenhum atendimento em andamento agora.',
            ],
            [
                'label' => 'Próximos horários',
                'value' => $upcoming->count(),
                'icon' => 'ri-calendar-check-line',
                'variant' => 'primary',
                'description' => $nextUpcoming
                    ? 'Próximo paciente às ' . ($nextUpcoming['scheduled_for'] ?? '—')
                    : 'Nenhum novo horário programado.',
            ],
            [
                'label' => 'Finalizados',
                'value' => $completed->count(),
                'icon' => 'ri-checkbox-circle-line',
                'variant' => 'success',
                'description' => 'Pontualidade geral: ' . $onTimeRate . '%',
            ],
        ];
    }

    private function buildQueueHighlights(Collection $queueItems): array
    {
        $current = $queueItems
            ->where('category', 'in_progress')
            ->sortBy('minutes_to_start')
            ->first();

        $nextWaiting = $queueItems
            ->where('category', 'waiting')
            ->sortByDesc('waiting_minutes')
            ->first();

        $upcoming = $queueItems
            ->where('category', 'upcoming')
            ->sortBy('minutes_to_start')
            ->first();

        $delayed = $queueItems
            ->filter(fn (array $item) => $item['is_delayed'] ?? false)
            ->sortByDesc('waiting_minutes')
            ->first();

        $priority = $queueItems->first(function (array $item) {
            if (!isset($item['priority'])) {
                return false;
            }

            return Str::contains(Str::lower($item['priority']), 'emerg');
        });

        return [
            'current' => $current,
            'next' => $nextWaiting ?? $upcoming,
            'delayed' => $delayed,
            'priority' => $priority,
        ];
    }

    private function buildVeterinarianBoards(Collection $queueItems): array
    {
        if ($queueItems->isEmpty()) {
            return [];
        }

        return $queueItems
            ->groupBy(fn (array $item) => $item['veterinarian']['id'] ?? 'unassigned')
            ->map(function (Collection $items, string $vetId) {
                $first = $items->first();
                $name = $first['veterinarian']['name'] ?? 'Sem responsável';
                $avatar = $first['veterinarian']['avatar'] ?? $this->buildAvatarUrl($name);

                $active = $items->where('category', 'in_progress')->first();
                $next = $items
                    ->filter(fn (array $item) => in_array($item['category'], ['waiting', 'upcoming'], true))
                    ->sortBy('minutes_to_start')
                    ->first();

                return [
                    'id' => $vetId,
                    'name' => $name,
                    'avatar' => $avatar,
                    'waiting' => $items->where('category', 'waiting')->count(),
                    'in_progress' => $items->where('category', 'in_progress')->count(),
                    'upcoming' => $items->where('category', 'upcoming')->count(),
                    'total_today' => $items->count(),
                    'active' => $active,
                    'next' => $next,
                ];
            })
            ->values()
            ->all();
    }

    private function formatTutorFromAttendance(Atendimento $attendance): string
    {
        if ($attendance->tutor_nome) {
            return $attendance->tutor_nome;
        }

        $tutor = $attendance->tutor;

        if ($tutor) {
            return $tutor->nome_fantasia
                ?: $tutor->razao_social
                ?: $tutor->nome
                ?: 'Tutor não informado';
        }

        if ($attendance->animal) {
            return $this->formatTutorName($attendance->animal);
        }

        return 'Tutor não informado';
    }

    private function normalizeCurrencyValue($value): float
    {
        if ($value === null) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace('R$', '', $value);
            $normalized = preg_replace('/\s+/', '', $normalized ?? '');

            if ($normalized === '' || $normalized === null) {
                return 0.0;
            }

            $hasComma = Str::contains($normalized, ',');
            $hasDot = Str::contains($normalized, '.');

            if ($hasComma && $hasDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } elseif ($hasComma) {
                $normalized = str_replace(',', '.', $normalized);
            }

            return is_numeric($normalized) ? (float) $normalized : 0.0;
        }

        return 0.0;
    }

    private function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($remaining > 0) {
            $parts[] = $remaining . 'min';
        }

        return implode(' ', $parts);
    }

    private function getEmpresaId(): ?int
    {
        return Auth::user()?->empresa?->empresa_id;
    }

    private function fetchPatients(?int $companyId): array
    {
        if (!$companyId) {
            return [];
        }

        $animals = Animal::query()
            ->with(['raca', 'especie', 'cliente'])
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get();

        if ($animals->isEmpty()) {
            return [];
        }

        $latestConsultations = Consulta::query()
            ->where('empresa_id', $companyId)
            ->whereIn('animal_id', $animals->pluck('id'))
            ->orderByDesc('datahora_consulta')
            ->get()
            ->unique('animal_id')
            ->keyBy('animal_id');

        return $animals
            ->map(function (Animal $animal) use ($latestConsultations) {
                $latestConsultation = $latestConsultations->get($animal->id);
                $tutor = $animal->cliente;

                $photoUrl = $this->buildAvatarUrl($animal->nome);
                $contacts = $this->extractTutorContacts($tutor);
                $primaryContact = $contacts[0] ?? null;

                return [
                    'id' => (string) $animal->id,
                    'name' => $animal->nome,
                    'photo' => $photoUrl,
                    'photo_url' => $photoUrl,
                    'species' => optional($animal->especie)->nome ?? 'Não informado',
                    'breed' => optional($animal->raca)->nome ?? 'Sem raça definida',
                    'gender' => $this->formatGender($animal->sexo),
                    'sex' => $this->formatGender($animal->sexo),
                    'age' => $this->formatAge($animal),
                    'weight' => $this->formatWeight($animal->peso),
                    'birth_date' => $this->formatDate($animal->data_nascimento),
                    'size' => $this->formatTitleCase($animal->porte),
                    'origin' => $this->formatTitleCase($animal->origem),
                    'microchip' => $animal->chip ?: null,
                    'pedigree' => $this->formatPedigree($animal),
                    'last_visit' => $this->formatDateTime($latestConsultation?->datahora_consulta),
                    'next_follow_up' => null,
                    'tutor_id' => optional($tutor)->id ? (string) $tutor->id : null,
                    'tutor' => $this->formatTutorName($animal),
                    'tutor_document' => $this->formatTutorDocument($tutor),
                    'tutor_address' => $this->formatTutorAddress($tutor),
                    'contact' => $this->formatTutorContact($tutor),
                    'email' => $this->formatTutorEmail($tutor),
                    'tutor_contacts' => $contacts,
                    'primary_contact' => $primaryContact,
                    'alerts' => [],
                    'allergies' => [],
                    'chronic_conditions' => [],
                    'vaccination_status' => null,
                    'medications' => [],
                    'recent_notes' => [],
                    'summary' => $latestConsultation?->observacao,
                    'notes' => $animal->observacao,
                    'tags' => $this->buildPatientTags($animal),
                ];
            })
            ->values()
            ->all();
    }

    private function fetchVeterinarians(?int $companyId): array
    {
        if (!$companyId) {
            return [];
        }

        return Medico::query()
            ->with('funcionario')
            ->where('empresa_id', $companyId)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'ativo');
            })
            ->get()
            ->sortBy(fn (Medico $medico) => optional($medico->funcionario)->nome)
            ->map(function (Medico $medico) {
                $name = optional($medico->funcionario)->nome ?? 'Profissional sem nome';

                return [
                    'id' => (string) $medico->id,
                    'name' => $name,
                    'specialty' => $medico->especialidade ?: 'Especialidade não informada',
                ];
            })
            ->values()
            ->all();
    }

    private function formatGender(?string $gender): ?string
    {
        return match (strtoupper((string) $gender)) {
            'M' => 'Macho',
            'F' => 'Fêmea',
            default => $gender,
        };
    }

    private function formatAge(Animal $animal): ?string
    {
        if ($animal->data_nascimento) {
            $birth = Carbon::parse($animal->data_nascimento);
            $now = Carbon::now();

            $years = $birth->diffInYears($now);
            $months = $birth->addYears($years)->diffInMonths($now);

            $parts = [];

            if ($years > 0) {
                $parts[] = $years . ' ' . ($years === 1 ? 'ano' : 'anos');
            }

            if ($months > 0 && $years < 2) {
                $parts[] = $months . ' ' . ($months === 1 ? 'mês' : 'meses');
            }

            return $parts ? implode(' e ', $parts) : null;
        }

        if ($animal->idade) {
            return $animal->idade . ' ' . ($animal->idade === 1 ? 'ano' : 'anos');
        }

        return null;
    }

    private function formatWeight($weight): ?string
    {
        if ($weight === null || $weight === '') {
            return null;
        }

        $normalized = str_replace(',', '.', (string) $weight);

        if (is_numeric($normalized)) {
            return number_format((float) $normalized, 2, ',', '.') . ' kg';
        }

        return (string) $weight;
    }

    private function formatTutorName(Animal $animal): string
    {
        $client = $animal->cliente;

        return $client?->razao_social
            ?: $client?->nome_fantasia
            ?: 'Tutor não informado';
    }

    private function extractTutorContacts(?Cliente $tutor): array
    {
        if (!$tutor) {
            return [];
        }

        $contacts = [];

        $phones = [
            ['label' => 'Telefone', 'value' => $tutor->telefone],
            ['label' => 'Telefone 2', 'value' => $tutor->telefone_secundario],
            ['label' => 'Telefone 3', 'value' => $tutor->telefone_terciario],
        ];

        foreach ($phones as $phone) {
            if (!empty($phone['value'])) {
                $formatted = $this->formatPhoneNumber($phone['value']);
                if ($formatted) {
                    $contacts[] = ['type' => $phone['label'], 'value' => $formatted];
                }
            }
        }

        if ($tutor->email) {
            $contacts[] = ['type' => 'E-mail', 'value' => strtolower(trim($tutor->email))];
        }

        return $contacts;
    }

    private function formatTutorDocument(?Cliente $tutor): ?string
    {
        if (!$tutor || !$tutor->cpf_cnpj) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $tutor->cpf_cnpj);

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        }

        if (strlen($digits) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
        }

        return $tutor->cpf_cnpj;
    }

    private function formatTutorAddress(?Cliente $tutor): ?string
    {
        if (!$tutor) {
            return null;
        }

        $parts = [];

        if ($tutor->rua) {
            $street = trim($tutor->rua);

            if ($tutor->numero) {
                $street .= ', ' . trim($tutor->numero);
            }

            $parts[] = $street;
        }

        if ($tutor->bairro) {
            $parts[] = trim($tutor->bairro);
        }

        $city = $tutor->cidade;
        if ($city) {
            $cityLabel = trim($city->nome);
            if ($city->uf) {
                $cityLabel .= ' - ' . $city->uf;
            }

            $parts[] = $cityLabel;
        }

        if ($tutor->cep) {
            $parts[] = $this->formatPostalCode($tutor->cep);
        }

        $parts = array_filter($parts);

        return $parts ? implode(' • ', $parts) : null;
    }

    private function formatTutorContact(?Cliente $tutor): ?string
    {
        if (!$tutor) {
            return null;
        }

        foreach ([$tutor->telefone, $tutor->telefone_secundario, $tutor->telefone_terciario, $tutor->contato] as $contact) {
            $formatted = $this->formatPhoneNumber($contact);
            if ($formatted) {
                return $formatted;
            }
        }

        return null;
    }

    private function formatTutorEmail(?Cliente $tutor): ?string
    {
        if (!$tutor || !$tutor->email) {
            return null;
        }

        return strtolower(trim($tutor->email));
    }

    private function buildAvatarUrl(?string $name): string
    {
        $avatars = [
            'assets/images/users/avatar-1.jpg',
            'assets/images/users/avatar-2.jpg',
            'assets/images/users/avatar-3.jpg',
            'assets/images/users/avatar-4.jpg',
            'assets/images/users/avatar-5.jpg',
            'assets/images/users/avatar-6.jpg',
            'assets/images/users/avatar-7.jpg',
            'assets/images/users/avatar-8.jpg',
            'assets/images/users/avatar-9.jpg',
            'assets/images/users/avatar-10.jpg',
        ];

        if ($name) {
            $normalized = Str::lower(trim($name));
            $index = abs(crc32($normalized)) % count($avatars);

            return asset($avatars[$index]);
        }

        return asset($avatars[0]);
    }

    private function normalizeAvatarUrl(?string $current, string $fallback): string
    {
        if (!$current) {
            return $fallback;
        }

        if (Str::contains($current, 'ui-avatars.com')) {
            return $fallback;
        }

        return $current;
    }

    private function formatDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->format('d/m/Y');
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function formatTitleCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        return mb_convert_case(mb_strtolower($trimmed, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function formatPedigree(Animal $animal): ?string
    {
        if ($animal->tem_pedigree === null) {
            return null;
        }

        if ((bool) $animal->tem_pedigree) {
            return $animal->pedigree ?: 'Sim';
        }

        return 'Não';
    }

    private function formatPhoneNumber(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6));
        }

        if (strlen($digits) === 9) {
            return sprintf('%s-%s', substr($digits, 0, 5), substr($digits, 5));
        }

        return trim($value);
    }

    private function formatPostalCode(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 8) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5);
        }

        return trim($value);
    }

    private function buildPatientTags(Animal $animal): array
    {
        $tags = [];

        if ($animal->porte) {
            $tags[] = 'Porte: ' . ucfirst(strtolower($animal->porte));
        }

        if ($animal->tem_pedigree) {
            $tags[] = 'Possui pedigree';
        }

        if ($animal->chip) {
            $tags[] = 'Chipado';
        }

        return $tags;
    }

    private function formatDateTime(?string $dateTime): ?string
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format('d/m/Y H:i');
    }
}