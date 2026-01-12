<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Models\Cliente;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Consulta;
use App\Models\Petshop\Medico;
use App\Models\Petshop\SalaAtendimento;
use App\Models\Petshop\Vacina;
use App\Models\Produto;
use App\Http\Requests\Petshop\ApplyVaccinationRequest;
use App\Http\Requests\Petshop\StoreVacinacaoRequest;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Petshop\Vacinacao;
use App\Models\Petshop\VacinacaoDose;
use App\Models\Petshop\VacinacaoEvento;
use App\Models\Petshop\VacinacaoSessao;
use App\Models\Petshop\VacinacaoSessaoDose;
use App\Models\Petshop\VetExame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VacinacoesController
{
    public function index(Request $request): View|ViewFactory
    {
        return $this->renderVaccinationListing($request, [
            'status_filter' => [
                Vacinacao::STATUS_CONCLUIDO,
                Vacinacao::STATUS_PENDENTE_VALIDACAO,
            ],
            'status_options' => [
                Vacinacao::STATUS_CONCLUIDO,
                Vacinacao::STATUS_PENDENTE_VALIDACAO,
            ],
            'view_mode' => 'history',
            'page_title' => 'Histórico de Vacinas',
            'table_title' => 'Histórico de Vacinações',
        ]);
    }

    public function scheduled(Request $request): View|ViewFactory
    {
        return $this->renderVaccinationListing($request, [
            'status_filter' => [
                Vacinacao::STATUS_AGENDADO,
                Vacinacao::STATUS_PENDENTE,
                Vacinacao::STATUS_EM_EXECUCAO,
                Vacinacao::STATUS_ATRASADO,
            ],
            'status_options' => [
                Vacinacao::STATUS_AGENDADO,
                Vacinacao::STATUS_PENDENTE,
                Vacinacao::STATUS_EM_EXECUCAO,
                Vacinacao::STATUS_ATRASADO,
            ],
            'view_mode' => 'scheduled',
            'page_title' => 'Aplicar Vacinação',
            'table_title' => 'Aplicar vacinação',
        ]);
    }

    public function panel(Request $request): View|ViewFactory
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

        $vaccinations = Vacinacao::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'medico.funcionario',
                'salaAtendimento',
                'doses.vacina',
                'sessions',
            ])
            ->where('empresa_id', $companyId)
            ->whereDate('scheduled_at', $selectedDate->toDateString())
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();

        $now = Carbon::now($timezone);

        $queueItems = $vaccinations->map(function (Vacinacao $vacinacao) use ($now, $timezone) {
            return $this->mapVaccinationToQueueItem($vacinacao, $now, $timezone);
        });

        $groupedQueue = $this->groupVaccinationQueue($queueItems);
        $metrics = $this->buildVaccinationMetrics($queueItems);
        $veterinarianBoards = $this->buildVaccinationBoards($queueItems);
        $highlights = $this->buildVaccinationHighlights($queueItems);
        $calendarView = $this->buildVaccinationCalendar($queueItems, $selectedDate);
        $dateLabel = Str::ucfirst($selectedDate->isoFormat('dddd, DD [de] MMMM'));

        return view('petshop.vet.vacinacoes.panel', [
            'selectedDate' => $selectedDate,
            'dateLabel' => $dateLabel,
            'groupedQueue' => $groupedQueue,
            'metrics' => $metrics,
            'veterinarianBoards' => $veterinarianBoards,
            'highlights' => $highlights,
            'hasQueue' => $queueItems->isNotEmpty(),
            'calendarView' => $calendarView,
        ]);
    }

    private function renderVaccinationListing(Request $request, array $options = []): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();
        $statusFilter = $options['status_filter'] ?? null;
        $statusOptions = $options['status_options'] ?? null;

        $vaccinationsQuery = Vacinacao::query()
            ->with([
                'animal.raca',
                'animal.especie',
                'animal.cliente.cidade',
                'attendance.animal',
                'attendance.veterinario.funcionario',
                'doses.vacina.produto.estoque',
                'doses.vacina.produto.estoqueLocais',
                'medico.funcionario',
                'salaAtendimento',
                'sessions.doses',
                'sessions.responsavel',
                'eventos.responsavel',
            ])
            ->where('empresa_id', $companyId);

        if (is_array($statusFilter) && !empty($statusFilter)) {
            $vaccinationsQuery->whereIn('status', $statusFilter);
        }

        $this->applyFilters($vaccinationsQuery, $request);

        $vaccinations = $vaccinationsQuery
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        $vaccinationsData = $vaccinations
            ->map(fn (Vacinacao $vacinacao) => $this->formatVaccination($vacinacao));

        $collection = collect($vaccinationsData);

        $summary = $this->buildSummaryMetrics($companyId);
        $filters = $this->buildFilters($companyId);

        if (is_array($statusOptions)) {
            $allowedStatuses = collect($statusOptions)
                ->map(fn (string $status) => (string) $status)
                ->all();

            $filters['status'] = collect($filters['status'] ?? [])
                ->filter(function (array $option) use ($allowedStatuses) {
                    $value = (string) ($option['value'] ?? '');

                    return in_array($value, $allowedStatuses, true);
                })
                ->values()
                ->all();
        }

        $statusBreakdown = $collection
            ->groupBy(fn ($item) => $item['status'] ?? 'Sem status')
            ->map->count();

        $upcomingVaccinations = $collection
            ->filter(function ($item) {
                if (empty($item['scheduled_at_iso'])) {
                    return false;
                }

                return Carbon::parse($item['scheduled_at_iso'])->isFuture();
            })
            ->values()
            ->take(4);

        $alerts = $collection
            ->flatMap(function ($item) {
                return collect($item['alerts'] ?? [])->map(function ($alert) use ($item) {
                    return $alert + [
                        'patient' => $item['patient'] ?? 'Paciente sem nome',
                        'code' => $item['code'] ?? null,
                    ];
                });
            })
            ->values();

        $reminders = $collection
            ->flatMap(function ($item) {
                return collect($item['reminders'] ?? [])->map(function ($reminder) use ($item) {
                    return [
                        'text' => $reminder,
                        'patient' => $item['patient'] ?? 'Paciente sem nome',
                        'code' => $item['code'] ?? null,
                    ];
                });
            })
            ->values();

        return view('petshop.vet.vacinacoes.index', [
            'summary' => $summary,
            'filters' => $filters,
            'vaccinations' => $collection,
            'statusBreakdown' => $statusBreakdown,
            'upcomingVaccinations' => $upcomingVaccinations,
            'vaccinationAlerts' => $alerts,
            'vaccinationReminders' => $reminders,
            'pageTitle' => $options['page_title'] ?? 'Vacinações Veterinárias',
            'tableTitle' => $options['table_title'] ?? 'Agenda de Vacinações',
            'viewMode' => $options['view_mode'] ?? null,
        ]);
    }

    public function create(Request $request): View|ViewFactory
    {
        return $this->renderVaccinationForm($request);
    }

    public function edit(Request $request, Vacinacao $vacinacao): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();
        $this->ensureVaccinationAccess($vacinacao, $companyId);

        $vacinacao->load(['doses.vacina', 'animal.cliente', 'attendance', 'medico.funcionario']);

        return $this->renderVaccinationForm($request, $vacinacao);
    }

    public function applyForm(Vacinacao $vacinacao): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();
        $this->ensureVaccinationAccess($vacinacao, $companyId);

        $vacinacao->load([
            'animal.raca',
            'animal.especie',
            'animal.cliente',
            'medico.funcionario',
            'salaAtendimento',
            'doses.vacina',
            'sessions.doses',
            'sessions.responsavel',
        ]);

        $formatted = $this->formatVaccination($vacinacao);
        $teamMembers = $this->fetchTeamMembers($companyId);

        $defaultStart = Carbon::now()->format('Y-m-d\TH:i');
        $defaultResponsible = Auth::id() ? (string) Auth::id() : null;

        return view('petshop.vet.vacinacoes.aplicar', [
            'vaccination' => $formatted,
            'vaccinationModel' => $vacinacao,
            'sessionStatusOptions' => VacinacaoSessao::statusLabels(),
            'doseResultOptions' => VacinacaoSessaoDose::resultLabels(),
            'teamMembers' => $teamMembers,
            'viaOptions' => Vacina::opcoesViasAdministracao(),
            'applicationSites' => Vacina::opcoesLocaisAplicacao(),
            'sessionDefaults' => [
                'inicio_execucao_at' => $defaultStart,
                'termino_execucao_at' => null,
                'responsavel_id' => $defaultResponsible,
                'assistentes_ids' => [],
                'status' => VacinacaoSessao::STATUS_CONCLUIDA,
            ],
        ]);
    }

    public function store(StoreVacinacaoRequest $request): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403);
        }

        $animal = Animal::query()
            ->where('empresa_id', $companyId)
            ->with('cliente')
            ->findOrFail($request->integer('patient_id'));

        $attendance = null;
        $attendanceId = $request->input('attendance_id');

        if ($attendanceId) {
            $attendance = Atendimento::query()
                ->forCompany($companyId)
                ->find($attendanceId);

            if (!$attendance) {
                return back()
                    ->withErrors(['attendance_id' => 'O atendimento informado não foi encontrado ou não pertence à sua empresa.'])
                    ->withInput();
            }

            if ((int) $attendance->animal_id !== (int) $animal->id) {
                return back()
                    ->withErrors(['attendance_id' => 'O atendimento selecionado não corresponde ao paciente informado.'])
                    ->withInput();
            }
        }

        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', sprintf(
            '%s %s',
            $request->input('scheduled_date'),
            $request->input('scheduled_time')
        ));

        $status = $request->input('status') ?: Vacinacao::STATUS_AGENDADO;
        if (!array_key_exists($status, Vacinacao::statusOptions())) {
            $status = Vacinacao::STATUS_AGENDADO;
        }

        $reminders = $this->normalizeSelections(
            $request->input('reminders', []),
            Vacinacao::reminderOptions()
        );

        $checklist = $this->normalizeSelections(
            $request->input('checklist', []),
            Vacinacao::checklistOptions()
        );

        try {
            DB::beginTransaction();

            $vacinacao = Vacinacao::create([
                'empresa_id' => $companyId,
                'animal_id' => $animal->id,
                'cliente_id' => $animal->cliente_id,
                'medico_id' => $request->input('veterinarian_id'),
                'attendance_id' => $attendance?->id,
                'sala_atendimento_id' => $request->input('room_id'),
                'codigo' => $this->generateVaccinationCode($companyId),
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'scheduled_by' => Auth::id(),
                'duration_minutes' => $request->input('duration_minutes'),
                'reminders' => $reminders,
                'checklist' => $checklist,
                'observacoes_planejamento' => $request->input('patient_notes'),
                'observacoes_clinicas' => $request->input('patient_notes'),
                'observacoes_logistica' => $request->input('room_notes'),
                'instrucoes_tutor' => $request->input('owner_notes'),
            ]);

            foreach ($request->input('vaccinations', []) as $index => $doseData) {
                $vacinacao->doses()->create([
                    'vacina_id' => $doseData['vaccine_id'],
                    'dose_ordem' => $index + 1,
                    'fabricante' => $doseData['manufacturer'] ?? null,
                    'lote' => $doseData['lot'] ?? null,
                    'validade' => $this->parseDate($doseData['valid_until'] ?? null),
                    'dose' => $doseData['dose'] ?? null,
                    'via_administracao' => $doseData['route'] ?? null,
                    'local_anatomico' => $doseData['site'] ?? null,
                    'volume' => $doseData['volume'] ?? null,
                    'observacoes' => $doseData['observations'] ?? null,
                    'dose_prevista_ml' => $this->parseDoseVolume($doseData['volume'] ?? null),
                    'via_aplicacao_prevista' => $doseData['route'] ?? null,
                    'alertas' => $doseData['alerts'] ?? null,
                ]);
            }

            $this->recordEvent($vacinacao, VacinacaoEvento::TIPO_AGENDAMENTO_CRIADO, [
                'scheduled_at' => $vacinacao->scheduled_at?->format('Y-m-d H:i:s'),
                'status' => $vacinacao->status,
                'patient' => $vacinacao->animal?->nome,
                'code' => $vacinacao->codigo,
            ], $vacinacao->created_at ?? Carbon::now());

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            session()->flash('flash_error', 'Não foi possível salvar o agendamento de vacinação. Tente novamente.');

            return back()
                ->withErrors(['store' => 'Não foi possível salvar o agendamento de vacinação. Tente novamente.'])
                ->withInput();
        }

        session()->flash('flash_success', 'Vacinação agendada com sucesso.');

        return redirect()->route('vet.vaccinations.scheduled');
    }

    public function update(StoreVacinacaoRequest $request, Vacinacao $vacinacao): RedirectResponse
    {
        $companyId = $this->getEmpresaId();
        $this->ensureVaccinationAccess($vacinacao, $companyId);

        $animal = Animal::query()
            ->where('empresa_id', $companyId)
            ->with('cliente')
            ->findOrFail($request->integer('patient_id'));

        $attendance = null;
        $attendanceId = $request->input('attendance_id');

        if ($attendanceId) {
            $attendance = Atendimento::query()
                ->forCompany($companyId)
                ->find($attendanceId);

            if (!$attendance) {
                return back()
                    ->withErrors(['attendance_id' => 'O atendimento informado não foi encontrado ou não pertence à sua empresa.'])
                    ->withInput();
            }

            if ((int) $attendance->animal_id !== (int) $animal->id) {
                return back()
                    ->withErrors(['attendance_id' => 'O atendimento selecionado não corresponde ao paciente informado.'])
                    ->withInput();
            }
        }

        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', sprintf(
            '%s %s',
            $request->input('scheduled_date'),
            $request->input('scheduled_time')
        ));

        $status = $request->input('status') ?: $vacinacao->status;
        if (!array_key_exists($status, Vacinacao::statusOptions())) {
            $status = $vacinacao->status;
        }

        $reminders = $this->normalizeSelections(
            $request->input('reminders', []),
            Vacinacao::reminderOptions()
        );

        $checklist = $this->normalizeSelections(
            $request->input('checklist', []),
            Vacinacao::checklistOptions()
        );

        try {
            DB::beginTransaction();

            $vacinacao->fill([
                'animal_id' => $animal->id,
                'cliente_id' => $animal->cliente_id,
                'medico_id' => $request->input('veterinarian_id'),
                'attendance_id' => $attendance?->id,
                'sala_atendimento_id' => $request->input('room_id'),
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $request->input('duration_minutes'),
                'reminders' => $reminders,
                'checklist' => $checklist,
                'observacoes_planejamento' => $request->input('patient_notes'),
                'observacoes_clinicas' => $request->input('patient_notes'),
                'observacoes_logistica' => $request->input('room_notes'),
                'instrucoes_tutor' => $request->input('owner_notes'),
            ]);

            $vacinacao->save();

            $this->syncVaccinationDoses($vacinacao, $request->input('vaccinations', []));

            $this->recordEvent($vacinacao, VacinacaoEvento::TIPO_OBSERVACAO, [
                'message' => 'Agendamento atualizado',
                'scheduled_at' => $vacinacao->scheduled_at?->format('Y-m-d H:i:s'),
                'status' => $vacinacao->status,
                'patient' => $vacinacao->animal?->nome,
                'code' => $vacinacao->codigo,
            ], Carbon::now());

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            session()->flash('flash_error', 'Não foi possível atualizar o agendamento de vacinação. Tente novamente.');

            return back()
                ->withErrors(['store' => 'Não foi possível atualizar o agendamento de vacinação. Tente novamente.'])
                ->withInput();
        }

        session()->flash('flash_success', 'Agendamento de vacinação atualizado com sucesso.');

        return redirect()->route('vet.vaccinations.scheduled');
    }

    private function renderVaccinationForm(Request $request, ?Vacinacao $vacinacao = null): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $selectedPatientId = $request->input('patient_id');
        $selectedVeterinarianId = $request->input('veterinarian_id');
        $selectedAttendanceId = $request->input('attendance_id');
        $selectedRoomId = $request->input('room_id');
        $scheduledDate = $request->input('scheduled_date');
        $scheduledTime = $request->input('scheduled_time');
        $status = $request->input('status');
        $sourceExamId = $request->input('exam_id');

        if ($vacinacao) {
            $selectedPatientId = $selectedPatientId ?? ($vacinacao->animal_id ? (string) $vacinacao->animal_id : null);
            $selectedVeterinarianId = $selectedVeterinarianId ?? ($vacinacao->medico_id ? (string) $vacinacao->medico_id : null);
            $selectedAttendanceId = $selectedAttendanceId ?? ($vacinacao->attendance_id ? (string) $vacinacao->attendance_id : null);
            $selectedRoomId = $selectedRoomId ?? ($vacinacao->sala_atendimento_id ? (string) $vacinacao->sala_atendimento_id : null);

            if ($vacinacao->scheduled_at) {
                $scheduledDate = $scheduledDate ?: $vacinacao->scheduled_at->format('Y-m-d');
                $scheduledTime = $scheduledTime ?: $vacinacao->scheduled_at->format('H:i');
            }

            $status = $status ?: $vacinacao->status;
        }

        $sourceExam = null;

        if ($sourceExamId) {
            $sourceExam = VetExame::query()
                ->with(['animal.cliente', 'attendance', 'examType', 'medico.funcionario'])
                ->forCompany($companyId)
                ->find($sourceExamId);

            if ($sourceExam) {
                $selectedPatientId = $selectedPatientId ?: (string) $sourceExam->animal_id;

                if ($sourceExam->medico_id) {
                    $selectedVeterinarianId = $selectedVeterinarianId ?: (string) $sourceExam->medico_id;
                }

                if ($sourceExam->atendimento_id) {
                    $selectedAttendanceId = $selectedAttendanceId ?: (string) $sourceExam->atendimento_id;
                }
            }
        }

        $attendanceContext = null;

        if ($selectedAttendanceId) {
            $attendanceModel = Atendimento::query()
                ->with(['animal', 'veterinario.funcionario'])
                ->forCompany($companyId)
                ->find($selectedAttendanceId);

            if ($attendanceModel) {
                $attendanceContext = $this->formatAttendanceForVaccination($attendanceModel);
                $selectedPatientId = $selectedPatientId ?: (string) $attendanceModel->animal_id;

                if ($attendanceModel->veterinario_id) {
                    $selectedVeterinarianId = $selectedVeterinarianId ?: (string) $attendanceModel->veterinario_id;
                }
            }
        } elseif ($vacinacao && $vacinacao->attendance) {
            $attendanceContext = $this->formatAttendanceForVaccination($vacinacao->attendance);
            $selectedAttendanceId = $selectedAttendanceId ?: (string) $vacinacao->attendance_id;
        }

        $patientsCollection = collect($this->fetchPatients($companyId));
        $patients = $patientsCollection->map(function (array $patient) use ($selectedPatientId) {
            $patient['selected'] = $selectedPatientId !== null && (string) $patient['id'] === (string) $selectedPatientId;

            return $patient;
        });

        if ($patients->isNotEmpty() && !$patients->firstWhere('selected', true)) {
            $patients = $patients->map(function (array $patient, int $index) use (&$selectedPatientId) {
                if ($index === 0) {
                    $patient['selected'] = true;
                    $selectedPatientId = $patient['id'];
                }

                return $patient;
            });
        }

        $patientsArray = $patients->values()->all();
        $activePatient = collect($patientsArray)->firstWhere('selected', true);

        $veterinariansCollection = collect($this->fetchVeterinarians($companyId));
        $veterinarians = $veterinariansCollection->map(function (array $veterinarian) use ($selectedVeterinarianId) {
            $veterinarian['selected'] = $selectedVeterinarianId !== null && (string) $veterinarian['id'] === (string) $selectedVeterinarianId;

            return $veterinarian;
        });

        if ($veterinarians->isNotEmpty() && !$veterinarians->firstWhere('selected', true)) {
            $veterinarians = $veterinarians->map(function (array $veterinarian, int $index) use (&$selectedVeterinarianId) {
                if ($index === 0) {
                    $veterinarian['selected'] = true;
                    $selectedVeterinarianId = $veterinarian['id'];
                }

                return $veterinarian;
            });
        }

        $veterinariansArray = $veterinarians->values()->all();

        $roomsCollection = collect($this->fetchRooms($companyId))->map(function (array $room) use ($selectedRoomId) {
            $room['selected'] = $selectedRoomId !== null && (string) $room['id'] === (string) $selectedRoomId;

            return $room;
        });

        $roomsArray = $roomsCollection->values()->all();

        $availability = $this->buildAvailability($roomsArray);

        if (!$scheduledDate && !empty($availability)) {
            $scheduledDate = $availability[0]['id'] ?? null;
        }

        $activeDate = $this->findAvailabilityDate($availability, $scheduledDate);

        if (!$scheduledTime && $activeDate) {
            $scheduledTime = $activeDate['slots'][0]['time'] ?? null;
        }

        $activeSlot = $this->findAvailabilitySlot($activeDate, $scheduledTime);

        if ($activeSlot) {
            $scheduledTime = $activeSlot['time'];
        }

        $vaccines = $this->fetchVaccines($companyId);
        $formVaccinations = $this->buildFormVaccinationEntries($vaccines, $vacinacao);
        $activeVaccine = $this->resolveActiveVaccine($formVaccinations, $vaccines);

        $reminderOptions = collect(Vacinacao::reminderOptions())
            ->map(fn (string $label, string $id) => ['id' => $id, 'label' => $label])
            ->values()
            ->all();

        $checklistOptions = collect(Vacinacao::checklistOptions())
            ->map(fn (string $label, string $id) => ['id' => $id, 'label' => $label])
            ->values()
            ->all();

        $selectedReminders = $vacinacao?->reminders ?: [];

        if (empty($selectedReminders) && !empty($reminderOptions)) {
            $selectedReminders = [$reminderOptions[0]['id']];
        }

        $selectedChecklist = $vacinacao?->checklist ?: [];

        $defaultValues = [
            'patient_id' => $selectedPatientId,
            'attendance_id' => $selectedAttendanceId,
            'exam_id' => $sourceExam ? (string) $sourceExam->id : null,
            'veterinarian_id' => $selectedVeterinarianId,
            'room_id' => $selectedRoomId,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'duration_minutes' => $vacinacao?->duration_minutes,
            'status' => $status ?: Vacinacao::STATUS_AGENDADO,
            'patient_notes' => $vacinacao?->observacoes_planejamento ?? $vacinacao?->observacoes_clinicas,
            'room_notes' => $vacinacao?->observacoes_logistica,
            'owner_notes' => $vacinacao?->instrucoes_tutor,
            'reminders' => $selectedReminders,
            'checklist' => $selectedChecklist,
            'vaccinations' => $formVaccinations,
        ];

        return view('petshop.vet.vacinacoes.agendar', [
            'formMode' => $vacinacao ? 'edit' : 'create',
            'formAction' => $vacinacao
                ? route('vet.vaccinations.update', $vacinacao)
                : route('vet.vaccinations.store'),
            'formMethod' => $vacinacao ? 'PUT' : 'POST',
            'vaccinationModel' => $vacinacao,
            'patients' => $patientsArray,
            'vaccines' => $vaccines,
            'formVaccinations' => $formVaccinations,
            'availability' => $availability,
            'activeDate' => $activeDate,
            'activeSlot' => $activeSlot,
            'veterinarians' => $veterinariansArray,
            'rooms' => $roomsArray,
            'reminders' => $reminderOptions,
            'checklist' => $checklistOptions,
            'activePatient' => $activePatient,
            'activeVaccine' => $activeVaccine,
            'defaultValues' => $defaultValues,
            'selectedVeterinarian' => collect($veterinariansArray)->firstWhere('selected', true),
            'attendanceContext' => $attendanceContext,
            'sourceExam' => $sourceExam ? $this->formatSourceExamContext($sourceExam) : null,
        ]);
    }

    public function apply(ApplyVaccinationRequest $request, Vacinacao $vacinacao): RedirectResponse
    {
        $companyId = $this->getEmpresaId();
        $this->ensureVaccinationAccess($vacinacao, $companyId);

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $inicio = Carbon::createFromFormat('Y-m-d\TH:i', $data['inicio_execucao_at']);
            $termino = !empty($data['termino_execucao_at'])
                ? Carbon::createFromFormat('Y-m-d\TH:i', $data['termino_execucao_at'])
                : null;

            $assistants = empty($data['assistentes_ids'])
                ? null
                : collect($data['assistentes_ids'])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

            $session = $vacinacao->sessions()->create([
                'session_code' => $this->generateSessionCode($vacinacao),
                'inicio_execucao_at' => $inicio,
                'termino_execucao_at' => $termino,
                'responsavel_id' => $data['responsavel_id'] ?? Auth::id(),
                'assistentes_ids' => $assistants,
                'status' => $data['status'],
                'observacoes_execucao' => $data['observacoes_execucao'] ?? null,
                'assinatura_tutor_path' => $data['assinatura_tutor_path'] ?? null,
            ]);

            $this->recordEvent($vacinacao, VacinacaoEvento::TIPO_SESSAO_INICIADA, [
                'session_code' => $session->session_code,
                'status' => $session->status,
            ], $inicio);

            $doseResults = [];

            foreach ($data['doses'] as $doseData) {
                $appliedAt = Carbon::createFromFormat('Y-m-d\TH:i', $doseData['aplicada_em']);

                $sessionDose = $session->doses()->create([
                    'dose_planejada_id' => $doseData['dose_planejada_id'] ?? null,
                    'aplicada_em' => $appliedAt,
                    'responsavel_id' => $doseData['responsavel_id'] ?? ($data['responsavel_id'] ?? Auth::id()),
                    'lote_id' => $doseData['lote_id'] ?? null,
                    'quantidade_ml' => $doseData['quantidade_ml'] ?? null,
                    'via_aplicacao' => $doseData['via_aplicacao'] ?? null,
                    'local_anatomico' => $doseData['local_anatomico'] ?? null,
                    'temperatura_pet' => $doseData['temperatura_pet'] ?? null,
                    'observacoes' => $doseData['observacoes'] ?? null,
                    'resultado' => $doseData['resultado'],
                    'motivo_nao_aplicacao' => $doseData['motivo_nao_aplicacao'] ?? null,
                ]);

                $doseResults[] = $sessionDose->resultado;

                if ($sessionDose->resultado === VacinacaoSessaoDose::RESULT_APLICADA) {
                    $this->recordEvent($vacinacao, VacinacaoEvento::TIPO_DOSE_APLICADA, [
                        'session_code' => $session->session_code,
                        'dose_planejada_id' => $sessionDose->dose_planejada_id,
                        'quantidade_ml' => $sessionDose->quantidade_ml,
                        'via_aplicacao' => $sessionDose->via_aplicacao,
                    ], $appliedAt);
                } elseif ($sessionDose->resultado === VacinacaoSessaoDose::RESULT_REAGENDADA) {
                    $this->recordEvent($vacinacao, VacinacaoEvento::TIPO_REAGENDAMENTO, [
                        'session_code' => $session->session_code,
                        'dose_planejada_id' => $sessionDose->dose_planejada_id,
                    ], $appliedAt);
                } elseif ($sessionDose->resultado === VacinacaoSessaoDose::RESULT_NAO_APLICADA) {
                    $this->recordEvent($vacinacao, VacinacaoEvento::TIPO_OBSERVACAO, [
                        'session_code' => $session->session_code,
                        'message' => $sessionDose->motivo_nao_aplicacao ?? 'Dose marcada como não aplicada.',
                    ], $appliedAt);
                }
            }

            if ($session->status !== VacinacaoSessao::STATUS_EM_EXECUCAO) {
                $finalEventTime = $termino ?? Carbon::now();
                $this->recordEvent($vacinacao, VacinacaoEvento::TIPO_SESSAO_FINALIZADA, [
                    'session_code' => $session->session_code,
                    'status' => $session->status,
                ], $finalEventTime);
            }

            $newStatus = $this->determineVaccinationStatusAfterSession($doseResults, $session->status);
            $vacinacao->status = $newStatus;
            $vacinacao->save();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            session()->flash('flash_error', 'Não foi possível registrar a aplicação da vacinação. Tente novamente.');

            return redirect()
                ->back()
                ->withErrors(['general' => 'Não foi possível registrar a aplicação da vacinação. Tente novamente.'])
                ->withInput();
        }

        session()->flash('flash_success', 'Aplicação da vacinação registrada com sucesso.');

        return redirect()->route('vet.vaccinations.index');
    }

    public function roomsOptions(Request $request): JsonResponse
    {
        $companyId = $this->getEmpresaId();

        return response()->json($this->fetchRooms($companyId));
    }

    public function veterinariansOptions(Request $request): JsonResponse
    {
        $companyId = $this->getEmpresaId();

        return response()->json($this->fetchVeterinarians($companyId));
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($search = trim((string) $request->input('pesquisa'))) {
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('codigo', 'like', "%{$search}%")
                    ->orWhereHas('animal', fn (Builder $animal) => $animal->where('nome', 'like', "%{$search}%"))
                    ->orWhereHas('animal.cliente', function (Builder $client) use ($search) {
                        $client
                            ->where('razao_social', 'like', "%{$search}%")
                            ->orWhere('nome_fantasia', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($vaccineType = $request->string('vaccine_type')->toString()) {
            $query->whereHas('doses.vacina', function (Builder $builder) use ($vaccineType) {
                $builder->where('categoria', $vaccineType);
            });
        }

        if ($species = $request->input('species')) {
            $query->whereHas('animal', function (Builder $builder) use ($species) {
                $builder->where('especie_id', (int) $species);
            });
        }

        if ($veterinarian = $request->input('veterinarian')) {
            $query->where('medico_id', (int) $veterinarian);
        }

        if ($period = $request->string('period')->toString()) {
            $today = Carbon::today();

            match ($period) {
                'today' => $query->whereDate('scheduled_at', $today),
                'week' => $query->whereBetween('scheduled_at', [$today->copy()->startOfDay(), $today->copy()->addDays(7)->endOfDay()]),
                'month' => $query->whereBetween('scheduled_at', [$today->copy()->startOfDay(), $today->copy()->addDays(30)->endOfDay()]),
                default => null,
            };
        }
    }

    private function buildSummaryMetrics(?int $companyId): array
    {
        if (!$companyId) {
            return [
                ['label' => 'Vacinações agendadas', 'value' => 0, 'icon' => 'ri-calendar-check-line', 'variant' => 'primary'],
                ['label' => 'Doses aplicadas no mês', 'value' => 0, 'icon' => 'ri-shield-check-line', 'variant' => 'success'],
                ['label' => 'Lotes próximos da validade', 'value' => 0, 'icon' => 'ri-error-warning-line', 'variant' => 'warning'],
                ['label' => 'Pendências críticas', 'value' => 0, 'icon' => 'ri-alert-line', 'variant' => 'danger'],
            ];
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $scheduled = Vacinacao::where('empresa_id', $companyId)
            ->whereIn('status', [
                Vacinacao::STATUS_AGENDADO,
                Vacinacao::STATUS_EM_EXECUCAO,
            ])
            ->count();

        $appliedThisMonth = Vacinacao::where('empresa_id', $companyId)
            ->where('status', Vacinacao::STATUS_CONCLUIDO)
            ->whereBetween('scheduled_at', [$startOfMonth, $endOfMonth])
            ->count();

        $nearExpiry = VacinacaoDose::whereHas('vacinacao', function (Builder $builder) use ($companyId) {
                $builder->where('empresa_id', $companyId);
            })
            ->whereNotNull('validade')
            ->whereBetween('validade', [$now->copy()->startOfDay(), $now->copy()->addDays(30)])
            ->count();

        $overdue = Vacinacao::where('empresa_id', $companyId)
            ->whereIn('status', [
                Vacinacao::STATUS_ATRASADO,
                Vacinacao::STATUS_PENDENTE_VALIDACAO,
            ])
            ->count();

        return [
            ['label' => 'Vacinações agendadas', 'value' => $scheduled, 'icon' => 'ri-calendar-check-line', 'variant' => 'primary'],
            ['label' => 'Doses aplicadas no mês', 'value' => $appliedThisMonth, 'icon' => 'ri-shield-check-line', 'variant' => 'success'],
            ['label' => 'Lotes próximos da validade', 'value' => $nearExpiry, 'icon' => 'ri-error-warning-line', 'variant' => 'warning'],
            ['label' => 'Pendências críticas', 'value' => $overdue, 'icon' => 'ri-alert-line', 'variant' => 'danger'],
        ];
    }

    private function buildFilters(?int $companyId): array
    {
        $status = collect(Vacinacao::statusOptions())
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        return [
            'status' => $status,
            'vaccine_types' => $this->fetchVaccineTypeOptions($companyId),
            'species' => $this->fetchSpeciesFilterOptions($companyId),
            'periods' => $this->buildPeriodOptions(),
            'veterinarians' => $this->fetchVeterinarianFilterOptions($companyId),
        ];
    }

    private function buildPeriodOptions(): array
    {
        return [
            ['value' => 'today', 'label' => 'Hoje'],
            ['value' => 'week', 'label' => 'Próximos 7 dias'],
            ['value' => 'month', 'label' => 'Próximos 30 dias'],
            ['value' => 'custom', 'label' => 'Personalizado'],
        ];
    }

    private function fetchVaccineTypeOptions(?int $companyId): array
    {
        $query = Vacina::query()->select('categoria')->whereNotNull('categoria');

        if ($companyId) {
            $query->where(function (Builder $builder) use ($companyId) {
                $builder
                    ->whereNull('empresa_id')
                    ->orWhere('empresa_id', $companyId);
            });
        }

        $labels = Vacina::opcoesCategorias();

        return $query
            ->distinct()
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->map(fn (string $value) => [
                'value' => $value,
                'label' => $labels[$value] ?? mb_convert_case(str_replace('_', ' ', $value), MB_CASE_TITLE, 'UTF-8'),
            ])
            ->values()
            ->all();
    }

    private function fetchSpeciesFilterOptions(?int $companyId): array
    {
        if (!$companyId) {
            return [];
        }

        return Especie::query()
            ->select(['id', 'nome'])
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get()
            ->map(fn (Especie $especie) => [
                'value' => (string) $especie->id,
                'label' => $especie->nome,
            ])
            ->values()
            ->all();
    }

    private function fetchVeterinarianFilterOptions(?int $companyId): array
    {
        return collect($this->fetchVeterinarians($companyId))
            ->map(fn (array $vet) => ['value' => $vet['id'], 'label' => $vet['name']])
            ->values()
            ->all();
    }

    private function fetchTeamMembers(?int $companyId): array
    {
        if (!$companyId) {
            return Auth::check()
                ? [[
                    'id' => (string) Auth::id(),
                    'name' => Auth::user()?->name ?? 'Usuário atual',
                ]]
                : [];
        }

        $users = User::query()
            ->select(['users.id', 'users.name'])
            ->whereHas('empresa', function ($query) use ($companyId) {
                $query->where('empresa_id', $companyId);
            })
            ->orderBy('users.name')
            ->get();

        if ($users->isEmpty() && Auth::check()) {
            $users = collect([Auth::user()]);
        }

        return $users
            ->map(fn (User $user) => [
                'id' => (string) $user->id,
                'name' => $user->name ?: sprintf('Usuário #%d', $user->id),
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    private function buildAvailability(array $rooms): array
    {
        $slots = ['09:00', '11:00', '14:00', '16:00'];
        $defaultRoom = $rooms[0]['label'] ?? 'Sala principal';
        $roomCount = max(count($rooms), 1);

        $days = [];
        for ($offset = 0; $offset < 5; $offset++) {
            $date = Carbon::today()->addDays($offset);
            $daySlots = [];

            foreach ($slots as $index => $time) {
                $room = $rooms[($offset + $index) % $roomCount]['label'] ?? $defaultRoom;
                $daySlots[] = [
                    'time' => $time,
                    'label' => sprintf('%s - %s', $time, $room),
                ];
            }

            $days[] = [
                'id' => $date->toDateString(),
                'label' => $this->formatAvailabilityLabel($date),
                'note' => $offset === 0
                    ? 'Agenda sugerida para hoje.'
                    : 'Disponibilidade sugerida com base nas salas cadastradas.',
                'slots' => $daySlots,
            ];
        }

        return $days;
    }

    private function findAvailabilityDate(array $availability, ?string $selectedDate): ?array
    {
        if ($selectedDate !== null) {
            foreach ($availability as $date) {
                if ((string) ($date['id'] ?? '') === (string) $selectedDate) {
                    return $date;
                }
            }
        }

        return $availability[0] ?? null;
    }

    private function findAvailabilitySlot(?array $date, ?string $selectedTime): ?array
    {
        if (!$date) {
            return null;
        }

        $slots = $date['slots'] ?? [];

        if ($selectedTime !== null) {
            foreach ($slots as $slot) {
                if ((string) ($slot['time'] ?? '') === (string) $selectedTime) {
                    return $slot;
                }
            }
        }

        return $slots[0] ?? null;
    }

    private function formatAvailabilityLabel(Carbon $date): string
    {
        $labels = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        return sprintf('%s • %s', $labels[$date->dayOfWeek], $date->format('d/m/Y'));
    }

    private function mapVaccinationToQueueItem(Vacinacao $vacinacao, Carbon $now, string $timezone): array
    {
        $scheduledAt = $this->resolveVaccinationSchedule($vacinacao, $timezone);

        $latestSession = $vacinacao->sessions
            ->filter(fn (VacinacaoSessao $session) => $session->inicio_execucao_at !== null)
            ->sortByDesc(fn (VacinacaoSessao $session) => $session->inicio_execucao_at)
            ->first();

        $sessionStart = null;
        $sessionEnd = null;

        if ($latestSession) {
            if ($latestSession->inicio_execucao_at instanceof Carbon) {
                $sessionStart = $latestSession->inicio_execucao_at->copy()->setTimezone($timezone);
            } elseif ($latestSession->inicio_execucao_at) {
                try {
                    $sessionStart = Carbon::parse((string) $latestSession->inicio_execucao_at, $timezone);
                } catch (\Throwable $exception) {
                    $sessionStart = null;
                }
            }

            if ($latestSession->termino_execucao_at instanceof Carbon) {
                $sessionEnd = $latestSession->termino_execucao_at->copy()->setTimezone($timezone);
            } elseif ($latestSession->termino_execucao_at) {
                try {
                    $sessionEnd = Carbon::parse((string) $latestSession->termino_execucao_at, $timezone);
                } catch (\Throwable $exception) {
                    $sessionEnd = null;
                }
            }
        }

        $startAt = $sessionStart ?? $scheduledAt;
        $minutesToStart = $scheduledAt ? $now->diffInMinutes($scheduledAt, false) : null;
        $waitingMinutes = ($minutesToStart !== null && $minutesToStart <= 0)
            ? abs($minutesToStart)
            : 0;

        $elapsedMinutes = null;

        if ($sessionStart) {
            if ($vacinacao->status === Vacinacao::STATUS_EM_EXECUCAO) {
                $elapsedMinutes = max(0, $sessionStart->diffInMinutes($now));
            } elseif ($sessionEnd) {
                $elapsedMinutes = max(0, $sessionStart->diffInMinutes($sessionEnd));
            }
        } elseif ($scheduledAt && $vacinacao->status === Vacinacao::STATUS_EM_EXECUCAO) {
            $elapsedMinutes = max(0, $scheduledAt->diffInMinutes($now));
        } elseif ($scheduledAt && $sessionEnd && in_array($vacinacao->status, [Vacinacao::STATUS_CONCLUIDO, Vacinacao::STATUS_PENDENTE_VALIDACAO], true)) {
            $elapsedMinutes = max(0, $scheduledAt->diffInMinutes($sessionEnd));
        }

        $category = $this->resolveVaccinationCategory($vacinacao->status, $minutesToStart);

        $isDelayed = $vacinacao->status === Vacinacao::STATUS_ATRASADO;

        if (!$isDelayed && $category === 'waiting' && $waitingMinutes >= 10) {
            $isDelayed = true;
        }

        if (!$isDelayed && $category === 'in_progress' && $minutesToStart !== null && $minutesToStart < -20) {
            $isDelayed = true;
        }

        if (in_array($category, ['completed', 'cancelled'], true)) {
            $waitingMinutes = 0;
        }

        $animal = $vacinacao->animal;
        $client = $animal?->cliente;
        $mappedDoses = $vacinacao->doses
            ->sortBy(fn (VacinacaoDose $dose) => $dose->dose_ordem ?? $dose->id)
            ->map(fn (VacinacaoDose $dose) => $this->mapDose($dose));

        $primaryDose = $mappedDoses->first();
        $serviceName = $primaryDose['name'] ?? 'Vacinação';

        $tutorName = $animal ? $this->formatTutorName($animal) : null;
        $tutorContact = $this->formatTutorContact($client);
        $tutorLabel = $tutorName;

        if ($tutorLabel && $tutorContact) {
            $tutorLabel .= ' • ' . $tutorContact;
        } elseif (!$tutorLabel && $tutorContact) {
            $tutorLabel = $tutorContact;
        }

        $vetEmployee = optional($vacinacao->medico)->funcionario;
        $vetName = $vetEmployee?->nome ?: 'Equipe de vacinação';

        $statusLabel = Vacinacao::statusOptions()[$vacinacao->status] ?? Str::title(str_replace('_', ' ', $vacinacao->status));
        $statusColor = Vacinacao::statusColor($vacinacao->status);

        $priority = null;
        if ($vacinacao->status === Vacinacao::STATUS_ATRASADO) {
            $priority = 'Vacinação atrasada';
        } elseif ($vacinacao->status === Vacinacao::STATUS_PENDENTE_VALIDACAO) {
            $priority = 'Validar aplicação';
        }

        $notes = $this->shortenDescription($vacinacao->observacoes_planejamento ?? $vacinacao->observacoes_clinicas);

        return [
            'id' => (int) $vacinacao->id,
            'code' => $vacinacao->codigo,
            'category' => $category,
            'status' => [
                'code' => $vacinacao->status,
                'label' => $statusLabel,
                'color' => $statusColor,
            ],
            'patient' => [
                'name' => $animal?->nome ?? 'Paciente sem identificação',
                'species' => optional($animal?->especie)->nome ?? 'Espécie não informada',
                'breed' => optional($animal?->raca)->nome ?? 'Raça não informada',
                'avatar' => $this->buildAvatarUrl($animal?->nome ?? 'Paciente'),
            ],
            'tutor' => $tutorLabel,
            'tutor_contact' => $tutorContact,
            'service' => $serviceName,
            'room' => $vacinacao->salaAtendimento?->nome,
            'priority' => $priority,
            'notes' => $notes,
            'scheduled_for' => $scheduledAt ? $scheduledAt->format('H:i') : null,
            'start_at' => $startAt,
            'minutes_to_start' => $minutesToStart ?? ($startAt ? $now->diffInMinutes($startAt, false) : null),
            'waiting_minutes' => $waitingMinutes,
            'elapsed_minutes' => $elapsedMinutes,
            'duration_minutes' => $vacinacao->duration_minutes,
            'is_delayed' => $isDelayed,
            'veterinarian' => [
                'id' => $vacinacao->medico_id ? (string) $vacinacao->medico_id : null,
                'name' => $vetName,
                'avatar' => $this->buildAvatarUrl($vetName),
            ],
            'vaccine' => $primaryDose,
            'vaccines' => $mappedDoses->values()->all(),
            'dose_count' => $mappedDoses->count(),
            'tags' => $this->buildTags($vacinacao, $primaryDose),
            'checklist' => $this->mapChecklistState($vacinacao->checklist),
            'reminders' => $this->translateSelections($vacinacao->reminders, Vacinacao::reminderOptions()),
        ];
    }

    private function resolveVaccinationSchedule(Vacinacao $vacinacao, string $timezone): ?Carbon
    {
        $scheduledAt = $vacinacao->scheduled_at;

        if ($scheduledAt instanceof Carbon) {
            return $scheduledAt->copy()->setTimezone($timezone);
        }

        if ($scheduledAt) {
            try {
                return Carbon::parse((string) $scheduledAt, $timezone);
            } catch (\Throwable $exception) {
                return null;
            }
        }

        return null;
    }

    private function resolveVaccinationCategory(string $status, ?int $minutesToStart): string
    {
        return match ($status) {
            Vacinacao::STATUS_EM_EXECUCAO => 'in_progress',
            Vacinacao::STATUS_CONCLUIDO, Vacinacao::STATUS_PENDENTE_VALIDACAO => 'completed',
            Vacinacao::STATUS_CANCELADO => 'cancelled',
            default => ($minutesToStart !== null && $minutesToStart > 0) ? 'upcoming' : 'waiting',
        };
    }

    private function groupVaccinationQueue(Collection $queueItems): array
    {
        return [
            'in_progress' => $queueItems->where('category', 'in_progress')->values(),
            'waiting' => $queueItems->where('category', 'waiting')->values(),
            'upcoming' => $queueItems->where('category', 'upcoming')->values(),
            'completed' => $queueItems->where('category', 'completed')->values(),
            'cancelled' => $queueItems->where('category', 'cancelled')->values(),
        ];
    }

    private function buildVaccinationMetrics(Collection $queueItems): array
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
            : 'Nenhum paciente aguardando agora.';

        $nextScheduled = $upcoming
            ->sortBy('minutes_to_start')
            ->first();

        $delayedCount = $queueItems
            ->filter(fn (array $item) => $item['is_delayed'] ?? false)
            ->count();

        return [
            [
                'label' => 'Aguardando',
                'value' => $waiting->count(),
                'icon' => 'ri-time-line',
                'variant' => 'warning',
                'description' => $waitingDescription,
            ],
            [
                'label' => 'Em aplicação',
                'value' => $inProgress->count(),
                'icon' => 'ri-syringe-line',
                'variant' => 'info',
                'description' => $inProgress->count()
                    ? 'Aplicações em execução neste momento.'
                    : 'Nenhuma vacina sendo aplicada agora.',
            ],
            [
                'label' => 'Próximas doses',
                'value' => $upcoming->count(),
                'icon' => 'ri-calendar-check-line',
                'variant' => 'primary',
                'description' => $nextScheduled
                    ? 'Próxima dose às ' . ($nextScheduled['scheduled_for'] ?? '—')
                    : 'Nenhuma dose futura para hoje.',
            ],
            [
                'label' => 'Concluídas',
                'value' => $completed->count(),
                'icon' => 'ri-checkbox-circle-line',
                'variant' => 'success',
                'description' => $delayedCount > 0
                    ? $delayedCount . ' vacinação(ões) com atraso.'
                    : 'Todas dentro do cronograma.',
            ],
        ];
    }

    private function buildVaccinationHighlights(Collection $queueItems): array
    {
        $current = $queueItems
            ->where('category', 'in_progress')
            ->sortBy('minutes_to_start')
            ->first();

        $waitingNext = $queueItems
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

        $priority = $queueItems->first(fn (array $item) => !empty($item['priority']));

        return [
            'current' => $current,
            'next' => $waitingNext ?? $upcoming,
            'delayed' => $delayed,
            'priority' => $priority,
        ];
    }

    private function buildVaccinationBoards(Collection $queueItems): array
    {
        if ($queueItems->isEmpty()) {
            return [];
        }

        return $queueItems
            ->groupBy(fn (array $item) => $item['veterinarian']['id'] ?? 'unassigned')
            ->map(function (Collection $items) {
                $first = $items->first();
                $name = $first['veterinarian']['name'] ?? 'Sem responsável';
                $avatar = $first['veterinarian']['avatar'] ?? $this->buildAvatarUrl($name);

                $waiting = $items->where('category', 'waiting')->count();
                $inProgress = $items->where('category', 'in_progress')->count();
                $upcoming = $items->where('category', 'upcoming')->count();

                $active = $items
                    ->where('category', 'in_progress')
                    ->sortBy('minutes_to_start')
                    ->first();

                $next = $items
                    ->where('category', 'waiting')
                    ->sortByDesc('waiting_minutes')
                    ->first();

                if (!$next) {
                    $next = $items
                        ->where('category', 'upcoming')
                        ->sortBy('minutes_to_start')
                        ->first();
                }

                return [
                    'id' => $first['veterinarian']['id'] ?? null,
                    'name' => $name,
                    'avatar' => $avatar,
                    'waiting' => $waiting,
                    'in_progress' => $inProgress,
                    'upcoming' => $upcoming,
                    'total_today' => $items->count(),
                    'active' => $active,
                    'next' => $next,
                ];
            })
            ->values()
            ->all();
    }

    private function buildVaccinationCalendar(Collection $queueItems, Carbon $selectedDate): array
    {
        $categoryLabels = [
            'in_progress' => 'Em aplicação',
            'waiting' => 'Aguardando',
            'upcoming' => 'Agendada',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
        ];

        $scheduledEvents = $queueItems
            ->filter(fn (array $item) => $item['start_at'] instanceof Carbon)
            ->map(function (array $item) use ($categoryLabels) {
                /** @var Carbon $startAt */
                $startAt = $item['start_at']->copy();

                $duration = $item['duration_minutes'] ?? null;

                if ($duration === null || $duration <= 0) {
                    if (!empty($item['elapsed_minutes'])) {
                        $duration = (int) $item['elapsed_minutes'];
                    } elseif ($item['category'] === 'in_progress') {
                        $duration = 40;
                    } else {
                        $duration = 30;
                    }
                }

                $duration = max(15, min(120, (int) $duration));

                $endAt = $startAt->copy()->addMinutes($duration);

                $startHour = (int) $startAt->format('H');
                $endHour = (int) $endAt->format('H');

                return [
                    'id' => $item['id'],
                    'patient' => $item['patient']['name'] ?? 'Paciente',
                    'service' => $item['service'] ?? 'Vacinação',
                    'tutor' => $item['tutor'] ?? null,
                    'veterinarian' => $item['veterinarian']['name'] ?? null,
                    'room' => $item['room'] ? 'Sala ' . $item['room'] : null,
                    'status_color' => $item['status']['color'] ?? 'primary',
                    'status_code' => $item['status']['code'] ?? null,
                    'category' => $item['category'],
                    'category_label' => $categoryLabels[$item['category']] ?? 'Vacinação',
                    'priority' => $item['priority'] ?? null,
                    'is_delayed' => $item['is_delayed'] ?? false,
                    'waiting_minutes' => $item['waiting_minutes'] ?? null,
                    'minutes_to_start' => $item['minutes_to_start'] ?? null,
                    'start_time' => $startAt->format('H:i'),
                    'end_time' => $endAt->format('H:i'),
                    'start_hour' => $startHour,
                    'end_hour' => max($startHour, $endHour),
                    'duration_label' => $this->formatMinutes($duration),
                ];
            })
            ->values();

        if ($scheduledEvents->isNotEmpty()) {
            $firstStart = (int) $scheduledEvents->min('start_hour');
            $lastEnd = (int) $scheduledEvents->max('end_hour');
            $startHour = max(7, $firstStart - 1);
            $endHour = min(22, $lastEnd + 1);
        } else {
            $startHour = 8;
            $endHour = 18;
        }

        $eventsByHour = $scheduledEvents->groupBy(function (array $event) {
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

        $unscheduled = $queueItems
            ->reject(fn (array $item) => $item['start_at'] instanceof Carbon)
            ->map(function (array $item) use ($categoryLabels) {
                return [
                    'id' => $item['id'],
                    'patient' => $item['patient']['name'] ?? 'Paciente',
                    'service' => $item['service'] ?? 'Vacinação',
                    'veterinarian' => $item['veterinarian']['name'] ?? null,
                    'status_color' => $item['status']['color'] ?? 'primary',
                    'category_label' => $categoryLabels[$item['category']] ?? 'Vacinação',
                    'priority' => $item['priority'] ?? null,
                ];
            })
            ->values();

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
                'label' => 'Em aplicação',
                'variant' => 'info',
                'icon' => 'ri-syringe-line',
                'count' => $summary['in_progress'],
            ],
            [
                'label' => 'Aguardando',
                'variant' => 'warning',
                'icon' => 'ri-time-line',
                'count' => $summary['waiting'],
            ],
            [
                'label' => 'Próximas doses',
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
                'peak_hour' => $peakHour['label'] ?? null,
                'peak_count' => isset($peakHour) ? count($peakHour['events']) : null,
            ]),
            'legend' => array_values(array_filter($legend, static fn (array $item) => $item['count'] > 0)),
        ];
    }

    private function formatMinutes(int $minutes): string
    {
        $minutes = abs($minutes);

        if ($minutes === 0) {
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

    private function formatVaccination(Vacinacao $vacinacao): array
    {
        $animal = $vacinacao->animal;
        $client = $animal?->cliente;
        $attendance = $vacinacao->attendance;

        $doses = $vacinacao->doses
            ->sortBy(fn (VacinacaoDose $dose) => $dose->dose_ordem ?? $dose->id)
            ->values();

        $mappedDoses = $doses->map(fn (VacinacaoDose $dose) => $this->mapDose($dose));
        $primary = $mappedDoses->first();

        $sessions = $this->formatSessions($vacinacao);
        $lastSession = $vacinacao->sessions
            ->sortByDesc(fn (VacinacaoSessao $session) => $session->inicio_execucao_at)
            ->first();

        $lastAppliedDose = $lastSession?->doses
            ->sortByDesc(fn (VacinacaoSessaoDose $dose) => $dose->aplicada_em)
            ->first();

        $lastApplication = $lastAppliedDose?->aplicada_em
            ? $lastAppliedDose->aplicada_em->format('d/m/Y H:i')
            : ($lastSession?->termino_execucao_at?->format('d/m/Y H:i'));

        $statusLabel = Vacinacao::statusOptions()[$vacinacao->status] ?? mb_convert_case($vacinacao->status, MB_CASE_TITLE, 'UTF-8');
        $statusColor = Vacinacao::statusColor($vacinacao->status);
        $scheduledAt = $vacinacao->scheduled_at;
        $scheduledDisplay = $scheduledAt ? $scheduledAt->format('d/m/Y H:i') : null;

        return [
            'id' => (string) $vacinacao->id,
            'code' => $vacinacao->codigo,
            'patient' => $animal?->nome,
            'species' => $animal?->especie?->nome,
            'breed' => $animal?->raca?->nome,
            'tutor' => $client ? $this->formatTutorName($animal) : null,
            'vaccine' => $primary,
            'vaccines' => $mappedDoses->all(),
            'status' => $statusLabel,
            'status_color' => $statusColor,
            'scheduled_at' => $scheduledDisplay,
            'scheduled_at_iso' => $scheduledAt?->toIso8601String(),
            'last_application' => $lastApplication,
            'next_due' => $scheduledAt ? $scheduledAt->format('d/m/Y') : null,
            'veterinarian' => $this->formatVeterinarianName($vacinacao),
            'clinic_room' => $this->formatClinicRoom($vacinacao),
            'planning_notes' => $vacinacao->observacoes_planejamento,
            'observations' => $vacinacao->observacoes_clinicas ?: ($primary['observations'] ?? null),
            'reminders' => $this->translateSelections($vacinacao->reminders, Vacinacao::reminderOptions()),
            'checklist' => $this->mapChecklistState($vacinacao->checklist),
            'alerts' => $this->buildAlerts($vacinacao, $primary),
            'timeline' => $this->buildTimeline($vacinacao, $scheduledDisplay),
            'inventory' => $this->buildInventoryFromDose($primary),
            'follow_up' => $this->buildFollowUp($vacinacao),
            'tags' => $this->buildTags($vacinacao, $primary),
            'documents' => [],
            'attendance' => $attendance
                ? $this->formatAttendanceForVaccination($attendance)
                : null,
            'sessions' => $sessions,
        ];
    }

    private function buildFormVaccinationEntries(array $vaccines, ?Vacinacao $vacinacao): array
    {
        if ($vacinacao) {
            return $vacinacao->doses
                ->sortBy(fn (VacinacaoDose $dose) => $dose->dose_ordem ?? $dose->id)
                ->map(function (VacinacaoDose $dose) {
                    $mapped = $this->mapDose($dose);

                    return [
                        'planned_id' => $mapped['planned_id'] ?? null,
                        'vaccine_id' => $mapped['id'] ?? null,
                        'manufacturer' => $mapped['manufacturer'] ?? null,
                        'lot' => $mapped['lot'] ?? null,
                        'valid_until' => $mapped['valid_until'] ?? null,
                        'dose' => $mapped['dose'] ?? null,
                        'route' => $mapped['route'] ?? null,
                        'site' => $mapped['site'] ?? null,
                        'volume' => $mapped['volume'] ?? null,
                        'observations' => $mapped['observations'] ?? null,
                    ];
                })
                ->values()
                ->all();
        }

        $first = $vaccines[0] ?? null;

        if ($first) {
            return [[
                'planned_id' => null,
                'vaccine_id' => $first['id'],
                'manufacturer' => $first['manufacturer'] ?? null,
                'lot' => $first['lot'] ?? null,
                'valid_until' => $first['valid_until'] ?? null,
                'dose' => $first['dose'] ?? null,
                'route' => $first['route'] ?? null,
                'site' => $first['site'] ?? null,
                'volume' => $first['volume'] ?? null,
                'observations' => $first['observations'] ?? null,
            ]];
        }

        return [[
            'planned_id' => null,
            'vaccine_id' => null,
            'manufacturer' => null,
            'lot' => null,
            'valid_until' => null,
            'dose' => null,
            'route' => null,
            'site' => null,
            'volume' => null,
            'observations' => null,
        ]];
    }

    private function resolveActiveVaccine(array $formVaccinations, array $vaccines): ?array
    {
        $first = $formVaccinations[0] ?? null;

        $selectedVaccine = null;

        if ($first && !empty($first['vaccine_id'])) {
            $selectedVaccine = collect($vaccines)->firstWhere('id', (string) $first['vaccine_id']);
        } elseif (!empty($vaccines)) {
            $selectedVaccine = $vaccines[0];
        }

        if (!$first && !$selectedVaccine) {
            return null;
        }

        $base = $selectedVaccine ?? [];

        return [
            'manufacturer' => $first['manufacturer'] ?? $base['manufacturer'] ?? null,
            'lot' => $first['lot'] ?? $base['lot'] ?? null,
            'valid_until' => $first['valid_until'] ?? $base['valid_until'] ?? null,
            'dose' => $first['dose'] ?? $base['dose'] ?? null,
            'route' => $first['route'] ?? $base['route'] ?? null,
            'site' => $first['site'] ?? $base['site'] ?? null,
            'volume' => $first['volume'] ?? $base['volume'] ?? null,
            'observations' => $first['observations'] ?? $base['observations'] ?? null,
            'stock' => $base['stock'] ?? null,
            'temperature_range' => $base['temperature_range'] ?? null,
        ];
    }

    private function mapDose(VacinacaoDose $dose): array
    {
        $base = $dose->vacina ? $this->mapVaccine($dose->vacina) : [];
        $predictedVolume = $dose->dose_prevista_ml ?? $this->parseDoseVolume($dose->volume ?? null);
        $volumeLabel = $predictedVolume !== null
            ? sprintf('%s ml', number_format((float) $predictedVolume, 2, ',', '.'))
            : ($dose->volume ?? ($base['volume'] ?? null));

        return [
            'id' => $dose->vacina_id ? (string) $dose->vacina_id : null,
            'planned_id' => (string) $dose->id,
            'name' => $base['name'] ?? null,
            'manufacturer' => $dose->fabricante ?? ($base['manufacturer'] ?? null),
            'lot' => $dose->lote ?? ($base['lot'] ?? null),
            'valid_until' => $dose->validade ? $dose->validade->format('d/m/Y') : ($base['valid_until'] ?? null),
            'dose' => $dose->dose ?? ($base['dose'] ?? null),
            'route' => $this->labelViaAplicacao($dose->via_administracao) ?? ($base['route'] ?? null),
            'route_value' => $dose->via_aplicacao_prevista ?? $dose->via_administracao,
            'site' => $dose->local_anatomico ?? ($base['site'] ?? null),
            'volume' => $volumeLabel,
            'predicted_volume' => $predictedVolume,
            'observations' => $dose->observacoes ?? ($base['observations'] ?? null),
            'temperature_range' => $base['temperature_range'] ?? null,
            'stock' => $base['stock'] ?? null,
        ];
    }

    private function formatSessions(Vacinacao $vacinacao): array
    {
        return $vacinacao->sessions
            ->sortByDesc(fn (VacinacaoSessao $session) => $session->inicio_execucao_at)
            ->map(function (VacinacaoSessao $session) {
                return [
                    'id' => (string) $session->id,
                    'code' => $session->session_code,
                    'status' => VacinacaoSessao::statusLabels()[$session->status] ?? $session->status,
                    'status_value' => $session->status,
                    'started_at' => $session->inicio_execucao_at?->format('d/m/Y H:i'),
                    'finished_at' => $session->termino_execucao_at?->format('d/m/Y H:i'),
                    'responsible' => $session->responsavel?->name,
                    'assistants' => $this->resolveAssistantsNames($session->assistentes_ids),
                    'notes' => $session->observacoes_execucao,
                    'doses' => $session->doses
                        ->sortByDesc(fn (VacinacaoSessaoDose $dose) => $dose->aplicada_em)
                        ->map(function (VacinacaoSessaoDose $dose) {
                            return [
                                'id' => (string) $dose->id,
                                'planned_id' => $dose->dose_planejada_id ? (string) $dose->dose_planejada_id : null,
                                'applied_at' => $dose->aplicada_em?->format('d/m/Y H:i'),
                                'responsible' => $dose->responsavel?->name,
                                'lot_id' => $dose->lote_id ? (string) $dose->lote_id : null,
                                'quantity' => $dose->quantidade_ml !== null
                                    ? sprintf('%s ml', number_format((float) $dose->quantidade_ml, 2, ',', '.'))
                                    : null,
                                'route' => $this->labelViaAplicacao($dose->via_aplicacao),
                                'site' => $dose->local_anatomico,
                                'temperature' => $dose->temperatura_pet !== null
                                    ? number_format((float) $dose->temperatura_pet, 1, ',', '.') . ' ºC'
                                    : null,
                                'observations' => $dose->observacoes,
                                'result' => VacinacaoSessaoDose::resultLabels()[$dose->resultado] ?? $dose->resultado,
                                'result_value' => $dose->resultado,
                                'motivo' => $dose->motivo_nao_aplicacao,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function resolveAssistantsNames(?array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $unique = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($unique->isEmpty()) {
            return [];
        }

        static $cache = [];

        $missing = $unique->filter(fn ($id) => !array_key_exists($id, $cache));

        if ($missing->isNotEmpty()) {
            $fetched = User::query()
                ->whereIn('id', $missing->all())
                ->pluck('name', 'id');

            foreach ($fetched as $id => $name) {
                $cache[$id] = trim((string) $name);
            }
        }

        return $unique
            ->map(fn ($id) => $cache[$id] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function labelViaAplicacao(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return Vacina::opcoesViasAdministracao()[$value] ?? $value;
    }

    private function formatVeterinarianName(Vacinacao $vacinacao): ?string
    {
        $medico = $vacinacao->medico;

        if (!$medico) {
            return null;
        }

        $name = optional($medico->funcionario)->nome ?? $medico->nome;

        if ($name) {
            return $medico->especialidade
                ? sprintf('%s — %s', $name, $medico->especialidade)
                : $name;
        }

        return null;
    }

    private function formatClinicRoom(Vacinacao $vacinacao): ?string
    {
        $room = $vacinacao->salaAtendimento;

        if (!$room) {
            return null;
        }

        return $room->nome ?: $room->identificador;
    }

    private function translateSelections(?array $selected, array $options): array
    {
        if (!$selected) {
            return [];
        }

        return collect($selected)
            ->filter(fn ($id) => isset($options[$id]))
            ->map(fn ($id) => $options[$id])
            ->values()
            ->all();
    }

    private function mapChecklistState(?array $selected): array
    {
        $selectedMap = array_fill_keys($selected ?? [], true);

        return collect(Vacinacao::checklistOptions())
            ->map(fn (string $label, string $id) => [
                'id' => $id,
                'label' => $label,
                'checked' => array_key_exists($id, $selectedMap),
            ])
            ->values()
            ->all();
    }

    private function buildAlerts(Vacinacao $vacinacao, ?array $primaryDose): array
    {
        $alerts = [];

        if ($vacinacao->status === Vacinacao::STATUS_ATRASADO) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Vacinação atrasada',
                'description' => 'Contato com o tutor recomendado para reagendamento imediato.',
            ];
        }

        if ($vacinacao->status === Vacinacao::STATUS_PENDENTE) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Pendência de confirmação',
                'description' => 'Confirme a presença do tutor e finalize o checklist pré-aplicação.',
            ];
        }

        if ($vacinacao->status === Vacinacao::STATUS_EM_EXECUCAO) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Aplicação em andamento',
                'description' => 'Há uma sessão de vacinação em execução. Acompanhe o registro das doses em tempo real.',
            ];
        }

        if ($vacinacao->status === Vacinacao::STATUS_PENDENTE_VALIDACAO) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Validação pendente',
                'description' => 'Revise os registros da sessão antes de finalizar a vacinação no sistema.',
            ];
        }

        $hasAbortedSession = $vacinacao->sessions
            ->contains(fn (VacinacaoSessao $session) => $session->status === VacinacaoSessao::STATUS_ABORTADA);

        if ($hasAbortedSession) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Sessão abortada',
                'description' => 'Uma das sessões foi interrompida. Avalie a necessidade de reagendamento ou justificativa clínica.',
            ];
        }

        $hasFailedDose = $vacinacao->sessions
            ->flatMap(fn (VacinacaoSessao $session) => $session->doses)
            ->contains(fn (VacinacaoSessaoDose $dose) => $dose->resultado === VacinacaoSessaoDose::RESULT_NAO_APLICADA);

        if ($hasFailedDose) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Dose não aplicada',
                'description' => 'Verifique os motivos e considere reagendar a dose pendente.',
            ];
        }

        if (!empty($primaryDose['valid_until'])) {
            $validUntil = $this->parseDisplayDate($primaryDose['valid_until']);

            if ($validUntil && $validUntil->isBetween(Carbon::today(), Carbon::today()->addDays(30))) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Validade próxima',
                    'description' => sprintf('O lote selecionado vence em %s.', $validUntil->format('d/m/Y')),
                ];
            }
        }

        return $alerts;
    }

    private function buildTimeline(Vacinacao $vacinacao, ?string $scheduledDisplay): array
    {
        $timeline = [];

        $hasCreationEvent = $vacinacao->eventos
            ? $vacinacao->eventos->contains(fn (VacinacaoEvento $event) => $event->tipo === VacinacaoEvento::TIPO_AGENDAMENTO_CRIADO)
            : false;

        if ($vacinacao->created_at && !$hasCreationEvent) {
            $timeline[] = [
                'date' => $vacinacao->created_at->format('d/m/Y H:i'),
                'title' => 'Agendamento criado',
                'description' => 'Registro cadastrado no sistema.',
            ];
        }

        if ($scheduledDisplay) {
            $timeline[] = [
                'date' => $scheduledDisplay,
                'title' => 'Aplicação programada',
                'description' => $this->formatClinicRoom($vacinacao) ?: 'Aplicação prevista na clínica.',
            ];
        }

        if ($vacinacao->eventos) {
            foreach ($vacinacao->eventos->sortBy('registrado_em') as $evento) {
                $timeline[] = $this->mapEventToTimeline($evento);
            }
        }

        return $timeline;
    }

    private function mapEventToTimeline(VacinacaoEvento $evento): array
    {
        $payload = $evento->payload ?? [];
        $responsavel = $evento->responsavel?->name;

        $description = match ($evento->tipo) {
            VacinacaoEvento::TIPO_SESSAO_INICIADA => $responsavel
                ? sprintf('Sessão %s iniciada por %s.', $payload['session_code'] ?? '#', $responsavel)
                : sprintf('Sessão %s iniciada.', $payload['session_code'] ?? '#'),
            VacinacaoEvento::TIPO_DOSE_APLICADA => sprintf(
                'Dose registrada (%s ml) via %s.',
                $payload['quantidade_ml'] !== null
                    ? number_format((float) $payload['quantidade_ml'], 2, ',', '.')
                    : '—',
                $this->labelViaAplicacao($payload['via_aplicacao'] ?? null) ?? 'via não informada'
            ),
            VacinacaoEvento::TIPO_SESSAO_FINALIZADA => sprintf(
                'Sessão %s finalizada com status %s.',
                $payload['session_code'] ?? '#',
                VacinacaoSessao::statusLabels()[$payload['status'] ?? ''] ?? ($payload['status'] ?? 'desconhecido')
            ),
            VacinacaoEvento::TIPO_REAGENDAMENTO => 'Vacinação reagendada a partir deste registro.',
            VacinacaoEvento::TIPO_CANCELAMENTO => 'Vacinação cancelada.',
            VacinacaoEvento::TIPO_OBSERVACAO => $payload['message'] ?? 'Observação adicionada à linha do tempo.',
            VacinacaoEvento::TIPO_LEMBRETE_ENVIADO => 'Lembrete enviado ao tutor.',
            VacinacaoEvento::TIPO_AGENDAMENTO_CRIADO => 'Agendamento registrado no sistema.',
            default => $payload['description'] ?? 'Atualização registrada.',
        };

        return [
            'date' => $evento->registrado_em?->format('d/m/Y H:i'),
            'title' => VacinacaoEvento::tipoLabels()[$evento->tipo] ?? mb_convert_case(str_replace('_', ' ', $evento->tipo), MB_CASE_TITLE, 'UTF-8'),
            'description' => $description,
        ];
    }

    private function buildInventoryFromDose(?array $dose): array
    {
        $stock = $dose['stock'] ?? null;

        return [
            'stock_available' => $stock['available'] ?? '0',
            'reserved_doses' => $stock['reserved'] ?? '0',
            'wastage' => '0',
            'temperature_monitoring' => $dose['temperature_range'] ?? '—',
        ];
    }

    private function buildFollowUp(Vacinacao $vacinacao): array
    {
        if (!$vacinacao->instrucoes_tutor) {
            return [];
        }

        return collect(preg_split('/\r?\n+/', $vacinacao->instrucoes_tutor))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function buildTags(Vacinacao $vacinacao, ?array $primaryDose): array
    {
        $tags = [];

        if ($primaryDose && !empty($primaryDose['dose'])) {
            $tags[] = $primaryDose['dose'];
        }

        if ($primaryDose && !empty($primaryDose['name'])) {
            $tags[] = $primaryDose['name'];
        }

        $tags[] = Vacinacao::statusOptions()[$vacinacao->status] ?? $vacinacao->status;

        return array_values(array_unique(array_filter($tags)));
    }

    private function normalizeSelections(array $selected, array $options): array
    {
        return collect($selected)
            ->map(fn ($value) => (string) $value)
            ->filter(fn ($value) => isset($options[$value]))
            ->unique()
            ->values()
            ->all();
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDoseVolume(?string $value): ?float
    {
        if (!$value) {
            return null;
        }

        $normalized = preg_replace('/[^0-9,\.]/', '', $value);

        if ($normalized === '' || $normalized === null) {
            return null;
        }

        $normalized = str_replace(',', '.', $normalized);

        if (!is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    private function parseDisplayDate(string $value): ?Carbon
    {
        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateVaccinationCode(int $companyId): string
    {
        $year = Carbon::now()->year;
        $prefix = sprintf('VAC-%s', $year);

        $sequence = Vacinacao::where('empresa_id', $companyId)
            ->whereYear('scheduled_at', $year)
            ->count() + 1;

        do {
            $code = sprintf('%s-%04d', $prefix, $sequence);
            $sequence++;
        } while (Vacinacao::where('codigo', $code)->exists());

        return $code;
    }

    private function generateSessionCode(Vacinacao $vacinacao): string
    {
        $base = $vacinacao->codigo ?: 'VAC';
        $sequence = $vacinacao->sessions()->count() + 1;

        do {
            $code = sprintf('%s-S%02d', $base, $sequence);
            $sequence++;
        } while (VacinacaoSessao::where('session_code', $code)->exists());

        return $code;
    }

    private function syncVaccinationDoses(Vacinacao $vacinacao, array $dosesData): void
    {
        $existing = $vacinacao->doses()->get()->keyBy('id');
        $retainedIds = [];

        foreach ($dosesData as $index => $doseData) {
            if (empty($doseData['vaccine_id'])) {
                continue;
            }

            $attributes = [
                'vacina_id' => $doseData['vaccine_id'],
                'dose_ordem' => $index + 1,
                'fabricante' => $doseData['manufacturer'] ?? null,
                'lote' => $doseData['lot'] ?? null,
                'validade' => $this->parseDate($doseData['valid_until'] ?? null),
                'dose' => $doseData['dose'] ?? null,
                'via_administracao' => $doseData['route'] ?? null,
                'local_anatomico' => $doseData['site'] ?? null,
                'volume' => $doseData['volume'] ?? null,
                'observacoes' => $doseData['observations'] ?? null,
                'dose_prevista_ml' => $this->parseDoseVolume($doseData['volume'] ?? null),
                'via_aplicacao_prevista' => $doseData['route'] ?? null,
                'alertas' => $doseData['alerts'] ?? null,
            ];

            $plannedId = isset($doseData['planned_id']) ? (int) $doseData['planned_id'] : null;

            if ($plannedId && $existing->has($plannedId)) {
                $dose = $existing->get($plannedId);
                $dose->fill($attributes);
                $dose->save();
                $retainedIds[] = $dose->id;
            } else {
                $dose = $vacinacao->doses()->create($attributes);
                $retainedIds[] = $dose->id;
            }
        }

        if (!empty($retainedIds)) {
            $vacinacao->doses()->whereNotIn('id', $retainedIds)->delete();
        } else {
            $vacinacao->doses()->delete();
        }
    }

    private function recordEvent(Vacinacao $vacinacao, string $type, array $payload, Carbon $dateTime): void
    {
        $vacinacao->eventos()->create([
            'tipo' => $type,
            'payload' => $payload,
            'registrado_por' => Auth::id(),
            'registrado_em' => $dateTime,
        ]);
    }

    private function determineVaccinationStatusAfterSession(array $doseResults, string $sessionStatus): string
    {
        $results = collect($doseResults)->filter();

        if ($sessionStatus === VacinacaoSessao::STATUS_EM_EXECUCAO) {
            return Vacinacao::STATUS_EM_EXECUCAO;
        }

        if ($sessionStatus === VacinacaoSessao::STATUS_ABORTADA) {
            return Vacinacao::STATUS_PENDENTE_VALIDACAO;
        }

        if ($results->isEmpty()) {
            return $sessionStatus === VacinacaoSessao::STATUS_CONCLUIDA
                ? Vacinacao::STATUS_PENDENTE_VALIDACAO
                : Vacinacao::STATUS_AGENDADO;
        }

        if ($results->every(fn ($value) => $value === VacinacaoSessaoDose::RESULT_APLICADA)) {
            return Vacinacao::STATUS_CONCLUIDO;
        }

        $hasApplied = $results->contains(VacinacaoSessaoDose::RESULT_APLICADA);
        $hasRescheduled = $results->contains(VacinacaoSessaoDose::RESULT_REAGENDADA);
        $hasNotApplied = $results->contains(VacinacaoSessaoDose::RESULT_NAO_APLICADA);

        if ($hasRescheduled && !$hasApplied && !$hasNotApplied) {
            return Vacinacao::STATUS_AGENDADO;
        }

        if ($hasNotApplied && !$hasApplied && !$hasRescheduled) {
            return Vacinacao::STATUS_PENDENTE_VALIDACAO;
        }

        return Vacinacao::STATUS_PENDENTE_VALIDACAO;
    }

    private function ensureVaccinationAccess(Vacinacao $vacinacao, ?int $companyId): void
    {
        if (!$companyId || (int) $vacinacao->empresa_id !== (int) $companyId) {
            abort(403);
        }
    }

    private function getEmpresaId(): ?int
    {
        return Auth::user()?->empresa?->empresa_id;
    }

    private function formatAttendanceForVaccination(Atendimento $attendance): array
    {
        return [
            'id' => (string) $attendance->id,
            'code' => $attendance->codigo,
            'scheduled_at' => $attendance->start_at ? $attendance->start_at->format('d/m/Y H:i') : null,
            'patient' => $attendance->animal?->nome,
            'veterinarian' => optional($attendance->veterinario?->funcionario)->nome,
            'url' => route('vet.atendimentos.history', $attendance->id),
            'status' => $attendance->status_label,
            'status_color' => $attendance->status_color,
        ];
    }

    private function formatSourceExamContext(VetExame $exam): array
    {
        return [
            'id' => (string) $exam->id,
            'type' => $exam->examType?->nome ?? 'Exame',
            'status' => VetExame::statusLabels()[$exam->status] ?? $exam->status,
            'patient' => $exam->animal?->nome,
            'attendance' => $exam->attendance
                ? $this->formatAttendanceForVaccination($exam->attendance)
                : null,
        ];
    }

    private function fetchPatients(?int $companyId): array
    {
        if (!$companyId) {
            return [];
        }

        $animals = Animal::query()
            ->with(['raca', 'especie', 'cliente.cidade'])
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get();

        if ($animals->isEmpty()) {
            return [];
        }

        $consultations = Consulta::query()
            ->where('empresa_id', $companyId)
            ->whereIn('animal_id', $animals->pluck('id'))
            ->orderByDesc('datahora_consulta')
            ->get()
            ->groupBy('animal_id');

        return $animals
            ->map(function (Animal $animal) use ($consultations) {
                $tutor = $animal->cliente;
                $animalConsultations = collect($consultations->get($animal->id));

                return [
                    'id' => (string) $animal->id,
                    'name' => $animal->nome,
                    'photo' => null,
                    'meta' => $this->buildPatientMeta($animal),
                    'species' => optional($animal->especie)->nome ?? 'Não informado',
                    'breed' => optional($animal->raca)->nome ?? 'Sem raça definida',
                    'age' => $this->formatAge($animal),
                    'weight' => $this->formatWeight($animal->peso),
                    'sex' => $this->formatSex($animal->sexo),
                    'birth_date' => $this->formatBirthDate($animal->data_nascimento),
                    'last_visit' => $this->extractLastVisitDate($animalConsultations),
                    'size' => $this->normalizeLabel($animal->porte),
                    'origin' => $this->normalizeLabel($animal->origem),
                    'microchip' => $this->formatMicrochip($animal->chip),
                    'pedigree' => $this->formatPedigree($animal),
                    'plan' => null,
                    'tutor' => $this->formatTutorName($animal),
                    'contact' => $this->formatTutorContact($tutor) ?? $this->formatTutorEmail($tutor),
                    'email' => $this->formatTutorEmail($tutor),
                    'tutor_document' => $this->formatTutorDocument($tutor),
                    'tutor_address' => $this->formatTutorAddress($tutor),
                    'notes' => $animal->observacao,
                    'alerts' => [],
                    'history' => $this->buildHistoryRecords($animalConsultations),
                ];
            })
            ->values()
            ->all();
    }

    private function fetchVaccines(?int $companyId): array
    {
        $query = Vacina::query()
            ->with(['produto.estoque', 'produto.estoqueLocais'])
            ->where('status', 'ativa')
            ->orderBy('nome');

        if ($companyId) {
            $query->where(function ($builder) use ($companyId) {
                $builder->whereNull('empresa_id')
                    ->orWhere('empresa_id', $companyId);
            });
        }

        return $query
            ->get()
            ->map(fn (Vacina $vacina) => $this->mapVaccine($vacina))
            ->filter()
            ->values()
            ->all();
    }

    private function mapVaccine(Vacina $vacina): array
    {
        $product = $vacina->produto;
        [$available, $reserved] = $this->resolveInventory($product);

        return [
            'id' => (string) $vacina->id,
            'name' => $vacina->nome,
            'manufacturer' => $this->nullIfBlank($vacina->fabricante),
            'lot' => $this->resolveLot($vacina, $product),
            'valid_until' => $this->formatValidity($vacina),
            'dose' => $this->formatDose($vacina),
            'route' => $this->resolveRoute($vacina),
            'volume' => $this->resolveVolume($vacina),
            'site' => $this->resolveApplicationSite($vacina),
            'temperature_range' => $this->formatTemperatureRange($vacina),
            'stock' => $product ? [
                'available' => $available,
                'reserved' => $reserved,
            ] : null,
            'observations' => $this->formatObservations($vacina),
        ];
    }

    private function resolveLot(Vacina $vacina, ?Produto $product): ?string
    {
        $candidates = [
            $product?->lote,
            $vacina->registro_mapa,
            $vacina->codigo,
        ];

        foreach ($candidates as $candidate) {
            $value = $this->nullIfBlank($candidate);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function formatValidity(Vacina $vacina): ?string
    {
        $closed = $this->nullIfBlank($vacina->validade_fechada);
        $opened = $this->nullIfBlank($vacina->validade_aberta);

        if ($closed && $opened) {
            return sprintf('Frasco fechado: %s • Após aberto: %s', $closed, $opened);
        }

        return $closed ?? $opened;
    }

    private function formatDose(Vacina $vacina): ?string
    {
        $candidates = [
            $vacina->protocolo_reforco,
            $vacina->protocolo_inicial,
            $vacina->protocolo_revacinar,
        ];

        foreach ($candidates as $candidate) {
            $value = $this->nullIfBlank($candidate);
            if ($value !== null) {
                return $value;
            }
        }

        if ($vacina->intervalo_reforco) {
            $interval = Vacina::opcoesIntervalosReforco()[$vacina->intervalo_reforco] ?? $vacina->intervalo_reforco;

            return $this->nullIfBlank($interval);
        }

        return null;
    }

    private function resolveRoute(Vacina $vacina): ?string
    {
        if (!$vacina->via_administracao) {
            return null;
        }

        $route = Vacina::opcoesViasAdministracao()[$vacina->via_administracao] ?? $vacina->via_administracao;

        return $this->nullIfBlank($route);
    }

    private function resolveVolume(Vacina $vacina): ?string
    {
        $dosage = $this->nullIfBlank($vacina->dosagem);

        if ($dosage !== null) {
            return $dosage;
        }

        if ($vacina->apresentacao) {
            $presentation = Vacina::opcoesApresentacoes()[$vacina->apresentacao] ?? $vacina->apresentacao;

            return $this->nullIfBlank($presentation);
        }

        return null;
    }

    private function resolveApplicationSite(Vacina $vacina): ?string
    {
        if (!$vacina->local_aplicacao) {
            return null;
        }

        $site = Vacina::opcoesLocaisAplicacao()[$vacina->local_aplicacao] ?? $vacina->local_aplicacao;

        return $this->nullIfBlank($site);
    }

    private function formatTemperatureRange(Vacina $vacina): ?string
    {
        $temperature = $this->nullIfBlank($vacina->temperatura_armazenamento);
        $condition = $vacina->condicao_armazenamento
            ? (Vacina::opcoesCondicoesArmazenamento()[$vacina->condicao_armazenamento] ?? $vacina->condicao_armazenamento)
            : null;

        $condition = $this->nullIfBlank($condition);

        if ($temperature && $condition) {
            return sprintf('%s (%s)', $condition, $temperature);
        }

        return $temperature ?? $condition;
    }

    private function formatObservations(Vacina $vacina): ?string
    {
        $candidates = [
            $vacina->orientacoes_pos_vacinacao,
            $vacina->requisitos_pre_vacinacao,
            $vacina->observacoes,
        ];

        foreach ($candidates as $candidate) {
            $value = $this->nullIfBlank($candidate);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function resolveInventory(?Produto $product): array
    {
        if (!$product) {
            return ['0', '0'];
        }

        $stocks = $product->relationLoaded('estoqueLocais') ? $product->estoqueLocais : collect();

        if ($stocks->isEmpty() && $product->relationLoaded('estoque') && $product->estoque) {
            $stocks = collect([$product->estoque]);
        }

        $available = (float) $stocks->sum(fn ($stock) => (float) ($stock->quantidade ?? 0));
        $reserved = (float) $stocks->sum(fn ($stock) => (float) ($stock->reservado ?? 0));

        return [
            $this->normalizeQuantity($available),
            $this->normalizeQuantity($reserved),
        ];
    }

    private function normalizeQuantity(float $value): string
    {
        if (abs($value - round($value)) < 0.0001) {
            return (string) (int) round($value);
        }

        $formatted = number_format($value, 3, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function nullIfBlank(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
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
                $phone = $this->formatPhoneNumber($medico->telefone) ?: $this->formatPhoneNumber(optional($medico->funcionario)->telefone);
                $email = $this->normalizeEmail($medico->email ?: optional($medico->funcionario)->email);

                return [
                    'id' => (string) $medico->id,
                    'name' => $name,
                    'crm' => $medico->crmv ?: null,
                    'specialty' => $medico->especialidade ?: 'Especialidade não informada',
                    'phone' => $phone,
                    'email' => $email,
                    'notes' => $this->shortenDescription($medico->observacoes),
                ];
            })
            ->values()
            ->all();
    }

    private function fetchRooms(?int $companyId): array
    {
        if (!$companyId) {
            return [];
        }

        return SalaAtendimento::query()
            ->where('empresa_id', $companyId)
            ->where('tipo', 'vacinacao')
            ->where(function ($query) {
                $query
                    ->whereNull('status')
                    ->orWhereIn('status', ['disponivel', 'ativo']);
            })
            ->orderBy('nome')
            ->orderBy('identificador')
            ->get()
            ->map(function (SalaAtendimento $room) {
                return [
                    'id' => (string) $room->id,
                    'label' => $room->nome ?: ($room->identificador ?: 'Sala sem nome'),
                    'identifier' => $room->identificador,
                    'type' => $this->getRoomTypeLabel($room->tipo),
                    'features' => $this->summarizeRoomFeatures($room),
                ];
            })
            ->values()
            ->all();
    }

    private function buildPatientMeta(Animal $animal): ?string
    {
        $parts = [
            optional($animal->especie)->nome,
            optional($animal->raca)->nome,
        ];

        $age = $this->formatAge($animal);
        if ($age) {
            $parts[] = $age;
        }

        $parts = array_values(array_filter(array_map(function ($value) {
            if ($value === null) {
                return null;
            }

            $normalized = trim((string) $value);

            return $normalized !== '' ? $normalized : null;
        }, $parts)));

        return $parts === [] ? null : implode(' • ', $parts);
    }

    private function formatSex(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtoupper(trim($value), 'UTF-8');

        return match ($normalized) {
            'MACHO' => 'Macho',
            'FEMEA', 'FÊMEA' => 'Fêmea',
            default => mb_convert_case(trim((string) $value), MB_CASE_TITLE, 'UTF-8'),
        };
    }

    private function formatBirthDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->format('d/m/Y');
    }

    private function extractLastVisitDate($consultations): ?string
    {
        if (!$consultations) {
            return null;
        }

        $collection = collect($consultations);
        $latest = $collection->first();

        if (!$latest instanceof Consulta || !$latest->datahora_consulta) {
            return null;
        }

        return $this->formatDate($latest->datahora_consulta);
    }

    private function normalizeLabel(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
    }

    private function formatMicrochip(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function formatPedigree(Animal $animal): ?string
    {
        if ($animal->pedigree) {
            $normalized = trim((string) $animal->pedigree);

            if ($normalized !== '') {
                return $normalized;
            }
        }

        if ($animal->tem_pedigree) {
            return 'Sim';
        }

        return null;
    }

    private function buildHistoryRecords($consultations): array
    {
        if (!$consultations) {
            return [];
        }

        return collect($consultations)
            ->take(3)
            ->map(function (Consulta $consulta) {
                return [
                    'date' => $this->formatDate($consulta->datahora_consulta),
                    'event' => $consulta->observacao
                        ? mb_strimwidth(trim($consulta->observacao), 0, 120, '…', 'UTF-8')
                        : 'Consulta registrada',
                ];
            })
            ->values()
            ->all();
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

        $name = $client?->razao_social
            ?: $client?->nome_fantasia
            ?: 'Tutor não informado';

        return mb_strtoupper($name, 'UTF-8');
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

    private function normalizeEmail(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $normalized = strtolower(trim($value));

        return $normalized !== '' ? $normalized : null;
    }

    private function shortenDescription(?string $value, int $limit = 80): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/', ' ', $value));

        if ($normalized === '') {
            return null;
        }

        if (mb_strlen($normalized, 'UTF-8') <= $limit) {
            return $normalized;
        }

        return mb_strimwidth($normalized, 0, $limit, '…', 'UTF-8');
    }

    private function summarizeRoomFeatures(SalaAtendimento $room): ?string
    {
        $parts = [
            $room->identificador ? 'Identificador: ' . trim($room->identificador) : null,
            ($type = $this->getRoomTypeLabel($room->tipo)) ? 'Tipo: ' . $type : null,
            $room->capacidade ? 'Capacidade: ' . trim((string) $room->capacidade) . ' paciente(s)' : null,
            $this->shortenDescription($room->equipamentos),
            $this->shortenDescription($room->observacoes),
        ];

        $parts = array_values(array_filter(array_map(function ($value) {
            if ($value === null) {
                return null;
            }

            $normalized = trim((string) $value);

            return $normalized !== '' ? $normalized : null;
        }, $parts)));

        if (empty($parts)) {
            return null;
        }

        return implode(' • ', array_slice($parts, 0, 3));
    }

    private function getRoomTypeLabel(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $normalized = trim(mb_strtolower($type, 'UTF-8'));

        if ($normalized === '') {
            return null;
        }

        $types = [
            'consultorio' => 'Consultório',
            'triagem' => 'Sala de triagem',
            'vacinacao' => 'Sala de vacinação',
            'cirurgia' => 'Centro cirúrgico',
            'emergencia' => 'Sala de emergência',
            'internacao' => 'Sala de internação',
            'laboratorio' => 'Laboratório',
            'outro' => 'Outro',
        ];

        return $types[$normalized] ?? mb_convert_case($type, MB_CASE_TITLE, 'UTF-8');
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

    private function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)->format('d/m/Y');
    }
}