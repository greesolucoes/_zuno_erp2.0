<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Models\Cliente;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Internacao;
use App\Models\Petshop\Medico;
use App\Models\Petshop\SalaInternacao;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class InternacoesController
{
    public function index(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $hospitalizationsQuery = Internacao::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'veterinarian.funcionario',
                'room',
            ])
            ->where('empresa_id', $companyId);

        $search = trim((string) $request->input('pesquisa'));

        if ($search !== '') {
            $hospitalizationsQuery->where(function ($query) use ($search) {
                $query->whereHas('animal', function ($animalQuery) use ($search) {
                    $animalQuery->where('nome', 'like', "%{$search}%");
                })->orWhereHas('tutor', function ($tutorQuery) use ($search) {
                    $tutorQuery->where('razao_social', 'like', "%{$search}%")
                        ->orWhere('nome_fantasia', 'like', "%{$search}%")
                        ->orWhere('contato', 'like', "%{$search}%");
                });
            });
        }

        $status = $request->input('status');

        if ($status && array_key_exists($status, Internacao::statusMeta())) {
            $hospitalizationsQuery->where('status', $status);
        }

        $riskLevel = $request->input('nivel_risco');

        if ($riskLevel && array_key_exists($riskLevel, Internacao::riskMeta())) {
            $hospitalizationsQuery->where('nivel_risco', $riskLevel);
        }

        $sector = $request->input('sector');

        if ($sector) {
            $hospitalizationsQuery->whereHas('room', function ($roomQuery) use ($sector) {
                $roomQuery->where('tipo', $sector);
            });
        }

        if ($request->filled('start_date')) {
            $hospitalizationsQuery->whereDate('internado_em', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $hospitalizationsQuery->whereDate('internado_em', '<=', $request->input('end_date'));
        }

        $hospitalizations = $hospitalizationsQuery
            ->orderByDesc('internado_em')
            ->get();

        $rooms = SalaInternacao::query()
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get();

        return view('petshop.vet.internacoes.index', [
            'hospitalizations' => $hospitalizations,
            'overview' => $this->buildOverviewMetrics($hospitalizations),
            'capacity' => $this->buildCapacityMetrics($rooms, $hospitalizations),
            'filters' => [
                'status' => $this->buildStatusFilters(),
                'sectors' => $this->buildSectorFilters($rooms),
                'risk_levels' => $this->buildRiskFilters(),
            ],
            'alerts' => $this->buildAlerts($hospitalizations),
        ]);
    }

    public function occupancy(): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $rooms = SalaInternacao::query()
            ->with([
                'internacoes' => function ($query) {
                    $query->with([
                        'animal.cliente',
                        'animal.especie',
                        'animal.raca',
                        'tutor',
                        'veterinarian.funcionario',
                    ])
                        ->where('status', Internacao::STATUS_ACTIVE)
                        ->whereNull('alta_em')
                        ->orderBy('internado_em');
                },
            ])
            ->where('empresa_id', $companyId)
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        return view('petshop.vet.internacoes.occupancy', [
            'overview' => $this->buildOccupancyOverview($rooms),
            'sectors' => $this->presentOccupancySectors($rooms),
        ]);
    }

    public function inpatients(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $rooms = SalaInternacao::query()
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get();

        $veterinarianOptions = Medico::query()
            ->with('funcionario')
            ->where('empresa_id', $companyId)
            ->get()
            ->mapWithKeys(function (Medico $veterinarian) {
                $name = $veterinarian->funcionario?->nome ?? $veterinarian->nome;

                return $name ? [$veterinarian->id => $name] : [];
            })
            ->sortBy(fn ($name) => Str::lower($name))
            ->toArray();

        $hospitalizations = Internacao::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'animal.pelagem',
                'tutor',
                'veterinarian.funcionario',
                'room',
            ])
            ->where('empresa_id', $companyId)
            ->when(
                $request->input('status', Internacao::STATUS_ACTIVE),
                function ($query, $status) {
                    if ($status !== '__all__') {
                        $query->where('status', $status);
                    }
                }
            )
            ->whereNull('alta_em')
            ->when($request->filled('pesquisa'), function ($query) use ($request) {
                $search = trim((string) $request->input('pesquisa'));

                if ($search === '') {
                    return;
                }

                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->whereHas('animal', function ($animalQuery) use ($search) {
                            $animalQuery->where('nome', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tutor', function ($tutorQuery) use ($search) {
                            $tutorQuery
                                ->where('razao_social', 'like', "%{$search}%")
                                ->orWhere('nome_fantasia', 'like', "%{$search}%")
                                ->orWhere('contato', 'like', "%{$search}%")
                                ->orWhere('celular', 'like', "%{$search}%");
                        })
                        ->orWhereHas('room', function ($roomQuery) use ($search) {
                            $roomQuery->where('nome', 'like', "%{$search}%");
                        })
                        ->orWhere('motivo', 'like', "%{$search}%")
                        ->orWhere('observacoes', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('nivel_risco'), function ($query) use ($request) {
                $riskLevel = $request->input('nivel_risco');

                if ($riskLevel && array_key_exists($riskLevel, Internacao::riskMeta())) {
                    $query->where('nivel_risco', $riskLevel);
                }
            })
            ->when($request->filled('sector'), function ($query) use ($request) {
                $sector = $request->input('sector');

                $query->whereHas('room', function ($roomQuery) use ($sector) {
                    $roomQuery->where('tipo', $sector);
                });
            })
            ->when($request->filled('veterinario_id'), function ($query) use ($request) {
                $query->where('veterinario_id', $request->input('veterinario_id'));
            })
            ->orderBy('internado_em')
            ->get();

        $inpatients = $hospitalizations
            ->map(fn (Internacao $hospitalization) => $this->presentInpatient($hospitalization))
            ->values();

        $groups = $inpatients
            ->groupBy(fn (array $item) => $item['admission']['date_key'])
            ->map(function (Collection $items, string $key) {
                $date = $key !== 'sem-data'
                    ? Carbon::createFromFormat('Y-m-d', $key)
                    : null;

                return [
                    'key' => $key,
                    'date' => $date,
                    'label' => $date
                        ? ucfirst($date->translatedFormat('dddd'))
                        : 'Sem data de admissão',
                    'formatted_date' => $date
                        ? $date->translatedFormat('DD [de] MMMM')
                        : null,
                    'is_today' => $date ? $date->isToday() : false,
                    'patients' => $items
                        ->sortBy(fn (array $patient) => $patient['admission']['timestamp'] ?? PHP_INT_MAX)
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy(fn (array $group) => $group['date'] ? $group['date']->timestamp : PHP_INT_MAX)
            ->values()
            ->all();

        return view('petshop.vet.internacoes.inpatients', [
            'groups' => $groups,
            'totalInpatients' => $inpatients->count(),
            'statusLegend' => array_values($this->inpatientStatusMeta()),
            'filters' => [
                'status' => $this->buildStatusFilters(),
                'risks' => $this->buildRiskFilters(),
                'sectors' => $this->buildSectorFilters($rooms),
                'veterinarians' => $veterinarianOptions,
            ],
            'activeFilters' => [
                'status' => $request->input('status', Internacao::STATUS_ACTIVE),
                'risk' => $request->input('nivel_risco'),
                'sector' => $request->input('sector'),
                'veterinarian' => $request->input('veterinario_id'),
                'search' => $request->input('pesquisa'),
            ],
        ]);
    }

    public function create(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $attendanceId = $this->normalizeId($request->input('attendance') ?? $request->input('atendimento_id'));
        $patientId = $this->normalizeId($request->input('patient') ?? $request->input('patient_id'));

        $patients = $this->fetchPatientOptions($companyId);
        $veterinarians = $this->fetchVeterinarianOptions($companyId);
        $rooms = $this->fetchRoomOptions($companyId);

        $attendance = null;

        if ($attendanceId !== null) {
            $attendance = Atendimento::query()
                ->where('empresa_id', $companyId)
                ->with(['animal.cliente', 'animal.especie', 'animal.raca', 'veterinario.funcionario'])
                ->find($attendanceId);
        }

        if ($attendance && $patientId === null) {
            $patientId = $attendance->animal?->id ? (string) $attendance->animal->id : null;
        }

        $selectedPatient = null;

        if ($patientId !== null) {
            $selectedPatient = $this->findPatientInOptions($patients, $patientId)
                ?? $this->loadPatientDetails($companyId, $patientId);
        }

        if (! $selectedPatient && $attendance?->animal) {
            $selectedPatient = $this->presentPatient($attendance->animal);
            $patientId = $selectedPatient['id'] ?? $patientId;
        }

        $attendanceContext = $attendance ? $this->formatAttendanceContext($attendance) : null;

        $selectedVeterinarianId = $attendance?->veterinario_id ? (string) $attendance->veterinario_id : null;

        $now = Carbon::now();

        $initialValues = [
            'patient_id' => $patientId,
            'atendimento_id' => $attendanceId,
            'veterinario_id' => $selectedVeterinarianId,
            'sala_internacao_id' => null,
            'admission_date' => $now->format('Y-m-d'),
            'admission_time' => $now->format('H:i'),
            'expected_discharge_date' => $now->copy()->addDays(2)->format('Y-m-d'),
            'status' => Internacao::STATUS_ACTIVE,
            'nivel_risco' => Internacao::RISK_LOW,
            'notes' => '',
            'reason' => '',
        ];

        $formAction = Route::has('vet.hospitalizations.store')
            ? route('vet.hospitalizations.store')
            : '#';

        return view('petshop.vet.internacoes.registrar', [
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'veterinarians' => $veterinarians,
            'rooms' => $rooms,
            'initialValues' => $initialValues,
            'formAction' => $formAction,
            'attendanceContext' => $attendanceContext,
            'riskOptions' => $this->buildRiskFilters(),
        ]);
    }



    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        if (! $request->filled('nivel_risco')) {
            $request->merge(['nivel_risco' => null]);
        }

        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'atendimento_id' => ['nullable', 'integer'],
            'veterinario_id' => ['nullable', 'integer'],
            'sala_internacao_id' => ['nullable', 'integer'],
            'admission_date' => ['required', 'date'],
            'admission_time' => ['required', 'date_format:H:i'],
            'expected_discharge_date' => ['nullable', 'date', 'after_or_equal:admission_date'],
            'reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:' . implode(',', array_keys(Internacao::statusMeta()))],
            'nivel_risco' => ['nullable', 'in:' . implode(',', array_keys(Internacao::riskMeta()))],
        ]);

        $animal = Animal::query()
            ->with('cliente')
            ->where('empresa_id', $companyId)
            ->findOrFail($validated['patient_id']);

        $attendance = null;
        $attendanceId = $validated['atendimento_id'] ?? null;

        if ($attendanceId) {
            $attendance = Atendimento::query()
                ->where('empresa_id', $companyId)
                ->find($attendanceId);

            abort_unless($attendance, 422, 'Atendimento inválido para esta empresa.');
        }

        $veterinarian = null;
        $veterinarianId = $validated['veterinario_id'] ?? null;

        if ($veterinarianId) {
            $veterinarian = Medico::query()
                ->where('empresa_id', $companyId)
                ->find($veterinarianId);

            abort_unless($veterinarian, 422, 'Profissional inválido para esta empresa.');
        }

        $room = null;
        $roomId = $validated['sala_internacao_id'] ?? null;

        if ($roomId) {
            $room = SalaInternacao::query()
                ->where('empresa_id', $companyId)
                ->find($roomId);

            abort_unless($room, 422, 'Sala de internação inválida para esta empresa.');
        }

        $admissionAt = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $validated['admission_date'], $validated['admission_time']));

        $expectedDischargeAt = null;

        if (! empty($validated['expected_discharge_date'])) {
            $expectedDischargeAt = Carbon::createFromFormat('Y-m-d H:i', $validated['expected_discharge_date'] . ' 12:00');
        }

        $status = $validated['status'] ?? Internacao::STATUS_ACTIVE;
        $riskLevel = $validated['nivel_risco'] ?? null;
        $riskLevel = $riskLevel === '' ? null : $riskLevel;

        $hospitalization = Internacao::create([
            'empresa_id' => $companyId,
            'animal_id' => $animal->id,
            'tutor_id' => $animal->cliente?->id,
            'atendimento_id' => $attendance?->id,
            'veterinario_id' => $veterinarian?->id,
            'sala_internacao_id' => $room?->id,
            'status' => $status,
            'nivel_risco' => $riskLevel,
            'internado_em' => $admissionAt,
            'previsao_alta_em' => $expectedDischargeAt,
            'motivo' => $this->normalizeNullableString($validated['reason'] ?? null),
            'observacoes' => $this->normalizeNullableString($validated['notes'] ?? null),
        ]);

        return redirect()
            ->route('vet.hospitalizations.index')
            ->with('success', $hospitalization->status === Internacao::STATUS_DRAFT
                ? 'Internação salva como rascunho.'
                : 'Internação registrada com sucesso.');
    }

    public function edit(Internacao $internacao): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        abort_unless($internacao->empresa_id === $companyId, 403);

        $internacao->loadMissing([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'veterinarian.funcionario',
            'room',
            'attendance.animal.cliente',
            'attendance.animal.especie',
            'attendance.animal.raca',
            'attendance.veterinario.funcionario',
        ]);

        $patients = $this->fetchPatientOptions($companyId);
        $veterinarians = $this->fetchVeterinarianOptions($companyId);
        $rooms = $this->fetchRoomOptions($companyId);

        $selectedPatient = $internacao->animal
            ? $this->presentPatient($internacao->animal)
            : null;

        $attendanceContext = $internacao->attendance
            ? $this->formatAttendanceContext($internacao->attendance)
            : null;

        $expectedDischarge = optional($internacao->previsao_alta_em)->format('Y-m-d');

        $initialValues = [
            'patient_id' => $internacao->animal_id ? (string) $internacao->animal_id : null,
            'atendimento_id' => $internacao->atendimento_id ? (string) $internacao->atendimento_id : null,
            'veterinario_id' => $internacao->veterinario_id ? (string) $internacao->veterinario_id : null,
            'sala_internacao_id' => $internacao->sala_internacao_id ? (string) $internacao->sala_internacao_id : null,
            'admission_date' => optional($internacao->internado_em)->format('Y-m-d'),
            'admission_time' => optional($internacao->internado_em)->format('H:i'),
            'expected_discharge_date' => $expectedDischarge ?? '',
            'status' => $internacao->status,
            'nivel_risco' => $internacao->nivel_risco,
            'reason' => $internacao->motivo ?? '',
            'notes' => $internacao->observacoes ?? '',
        ];

        return view('petshop.vet.internacoes.registrar', [
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'veterinarians' => $veterinarians,
            'rooms' => $rooms,
            'initialValues' => $initialValues,
            'formAction' => route('vet.hospitalizations.update', $internacao),
            'formMethod' => 'PUT',
            'attendanceContext' => $attendanceContext,
            'riskOptions' => $this->buildRiskFilters(),
            'isEdit' => true,
            'submitButtonLabel' => 'Salvar alterações',
            'submitButtonIcon' => 'ri-save-3-line',
            'pageHeading' => 'Editar internação',
            'pageDescription' => 'Atualize as informações essenciais da internação.',
        ]);
    }

    public function update(Request $request, Internacao $internacao): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        abort_unless($internacao->empresa_id === $companyId, 403);

        if (! $request->filled('nivel_risco')) {
            $request->merge(['nivel_risco' => null]);
        }

        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'atendimento_id' => ['nullable', 'integer'],
            'veterinario_id' => ['nullable', 'integer'],
            'sala_internacao_id' => ['nullable', 'integer'],
            'admission_date' => ['required', 'date'],
            'admission_time' => ['required', 'date_format:H:i'],
            'expected_discharge_date' => ['nullable', 'date', 'after_or_equal:admission_date'],
            'reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:' . implode(',', array_keys(Internacao::statusMeta()))],
            'nivel_risco' => ['nullable', 'in:' . implode(',', array_keys(Internacao::riskMeta()))],
        ]);

        $animal = Animal::query()
            ->with('cliente')
            ->where('empresa_id', $companyId)
            ->findOrFail($validated['patient_id']);

        $attendance = null;
        $attendanceId = $validated['atendimento_id'] ?? null;

        if ($attendanceId) {
            $attendance = Atendimento::query()
                ->where('empresa_id', $companyId)
                ->find($attendanceId);

            abort_unless($attendance, 422, 'Atendimento inválido para esta empresa.');
        }

        $veterinarian = null;
        $veterinarianId = $validated['veterinario_id'] ?? null;

        if ($veterinarianId) {
            $veterinarian = Medico::query()
                ->where('empresa_id', $companyId)
                ->find($veterinarianId);

            abort_unless($veterinarian, 422, 'Profissional inválido para esta empresa.');
        }

        $room = null;
        $roomId = $validated['sala_internacao_id'] ?? null;

        if ($roomId) {
            $room = SalaInternacao::query()
                ->where('empresa_id', $companyId)
                ->find($roomId);

            abort_unless($room, 422, 'Sala de internação inválida para esta empresa.');
        }

        $admissionAt = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $validated['admission_date'], $validated['admission_time']));

        $expectedDischargeAt = null;

        if (! empty($validated['expected_discharge_date'])) {
            $expectedDischargeAt = Carbon::createFromFormat('Y-m-d H:i', $validated['expected_discharge_date'] . ' 12:00');
        }

        $status = $validated['status'] ?? $internacao->status ?? Internacao::STATUS_ACTIVE;
        $riskLevel = $validated['nivel_risco'] ?? null;
        $riskLevel = $riskLevel === '' ? null : $riskLevel;

        $internacao->update([
            'animal_id' => $animal->id,
            'tutor_id' => $animal->cliente?->id,
            'atendimento_id' => $attendance?->id,
            'veterinario_id' => $veterinarian?->id,
            'sala_internacao_id' => $room?->id,
            'status' => $status,
            'nivel_risco' => $riskLevel,
            'internado_em' => $admissionAt,
            'previsao_alta_em' => $expectedDischargeAt,
            'motivo' => $this->normalizeNullableString($validated['reason'] ?? null),
            'observacoes' => $this->normalizeNullableString($validated['notes'] ?? null),
        ]);

        $message = $status === Internacao::STATUS_DRAFT
            ? 'Internação salva como rascunho.'
            : 'Internação atualizada com sucesso.';

        return redirect()
            ->route('vet.hospitalizations.index')
            ->with('success', $message);
    }

    private function buildStatusFilters(): array
    {
        return collect(Internacao::statusMeta())
            ->map(fn (array $meta, string $value) => [
                'value' => $value,
                'label' => $meta['label'],
            ])
            ->values()
            ->all();
    }

    private function buildRiskFilters(): array
    {
        return collect(Internacao::riskMeta())
            ->map(fn (array $meta, string $value) => [
                'value' => $value,
                'label' => $meta['label'],
            ])
            ->values()
            ->all();
    }

    private function buildSectorFilters(Collection $rooms): array
    {
        return $rooms
            ->pluck('tipo')
            ->filter()
            ->unique()
            ->map(fn ($type) => [
                'value' => $type,
                'label' => $this->formatTitleCase($type),
            ])
            ->values()
            ->all();
    }

    private function buildOverviewMetrics(Collection $hospitalizations): array
    {
        $active = $hospitalizations->where('status', Internacao::STATUS_ACTIVE)->count();
        $dischargeToday = $hospitalizations
            ->filter(fn (Internacao $internacao) => $internacao->status === Internacao::STATUS_ACTIVE && $internacao->previsao_alta_em?->isToday())
            ->count();
        $drafts = $hospitalizations->where('status', Internacao::STATUS_DRAFT)->count();
        $highRisk = $hospitalizations->where('nivel_risco', Internacao::RISK_HIGH)->count();

        return [
            [
                'label' => 'Pacientes internados',
                'value' => $active,
                'icon' => 'ri-hotel-bed-line',
                'variant' => 'primary',
            ],
            [
                'label' => 'Altas previstas hoje',
                'value' => $dischargeToday,
                'icon' => 'ri-calendar-check-line',
                'variant' => 'success',
            ],
            [
                'label' => 'Internações em rascunho',
                'value' => $drafts,
                'icon' => 'ri-file-list-line',
                'variant' => 'warning',
            ],
            [
                'label' => 'Pacientes em risco alto',
                'value' => $highRisk,
                'icon' => 'ri-alert-line',
                'variant' => 'danger',
            ],
        ];
    }

    private function buildCapacityMetrics(Collection $rooms, Collection $hospitalizations): array
    {
        $totalBeds = (int) $rooms->sum(fn (SalaInternacao $room) => (int) ($room->capacidade ?? 0));

        $active = $hospitalizations->filter(fn (Internacao $internacao) => $internacao->status === Internacao::STATUS_ACTIVE);

        $occupiedBeds = min($active->count(), $totalBeds);
        $availableBeds = max($totalBeds - $occupiedBeds, 0);
        $occupancyRate = $totalBeds > 0
            ? (int) round(($occupiedBeds / $totalBeds) * 100)
            : 0;

        $icuRooms = $rooms->filter(function (SalaInternacao $room) {
            return $room->tipo && str_contains(mb_strtolower((string) $room->tipo), 'uti');
        });

        $icuCapacity = (int) $icuRooms->sum(fn (SalaInternacao $room) => (int) ($room->capacidade ?? 0));
        $icuOccupied = $active->filter(function (Internacao $internacao) {
            $type = $internacao->room?->tipo;

            return $type && str_contains(mb_strtolower((string) $type), 'uti');
        })->count();
        $icuOccupied = min($icuOccupied, $icuCapacity);
        $icuRate = $icuCapacity > 0
            ? (int) round(($icuOccupied / $icuCapacity) * 100)
            : 0;

        $wardCapacity = max($totalBeds - $icuCapacity, 0);
        $wardOccupied = max($occupiedBeds - $icuOccupied, 0);
        $wardOccupied = min($wardOccupied, $wardCapacity);
        $wardRate = $wardCapacity > 0
            ? (int) round(($wardOccupied / $wardCapacity) * 100)
            : 0;

        return [
            'total_beds' => $totalBeds,
            'occupied_beds' => $occupiedBeds,
            'available_beds' => $availableBeds,
            'occupancy_rate' => $occupancyRate,
            'icu_rate' => $icuRate,
            'ward_rate' => $wardRate,
        ];
    }

    private function buildOccupancyOverview(Collection $rooms): array
    {
        $statusMeta = $this->roomStatusMeta();

        $totalCapacity = (int) $rooms->sum(fn (SalaInternacao $room) => $room->capacidade ?? 0);
        $totalOccupiedBeds = (int) $rooms->sum(fn (SalaInternacao $room) => $room->internacoes->count());
        $availableBeds = max(0, $totalCapacity - $totalOccupiedBeds);

        $statusSummary = $rooms
            ->groupBy(fn (SalaInternacao $room) => $room->status ?: SalaInternacao::STATUS_AVAILABLE)
            ->map(function (Collection $items, string $status) use ($statusMeta) {
                $capacity = (int) $items->sum(fn (SalaInternacao $room) => $room->capacidade ?? 0);
                $occupied = (int) $items->sum(fn (SalaInternacao $room) => $room->internacoes->count());

                return [
                    'status' => $status,
                    'label' => $statusMeta[$status]['label'] ?? $this->formatRoomStatusLabel($status),
                    'color' => $statusMeta[$status]['color'] ?? 'secondary',
                    'rooms' => $items->count(),
                    'capacity' => $capacity,
                    'occupied' => $occupied,
                ];
            })
            ->values()
            ->all();

        $criticalRooms = $rooms
            ->filter(function (SalaInternacao $room) {
                return $room->capacidade && $room->internacoes->count() >= $room->capacidade;
            })
            ->map(function (SalaInternacao $room) {
                return [
                    'id' => $room->id,
                    'name' => $room->nome,
                    'identifier' => $room->identificador,
                    'type' => $room->tipo,
                    'capacity' => (int) ($room->capacidade ?? 0),
                    'occupied' => $room->internacoes->count(),
                ];
            })
            ->values()
            ->all();

        return [
            'total_rooms' => $rooms->count(),
            'total_capacity' => $totalCapacity,
            'occupied_beds' => $totalOccupiedBeds,
            'available_beds' => $availableBeds,
            'occupancy_rate' => $totalCapacity > 0 ? (int) round(($totalOccupiedBeds / $totalCapacity) * 100) : 0,
            'status_summary' => $statusSummary,
            'critical_rooms' => $criticalRooms,
        ];
    }

    private function presentOccupancySectors(Collection $rooms): array
    {
        $typeLabels = $this->roomTypeLabels();
        $statusMeta = $this->roomStatusMeta();

        return $rooms
            ->groupBy(fn (SalaInternacao $room) => $room->tipo ?: 'outro')
            ->map(function (Collection $items, string $type) use ($typeLabels, $statusMeta) {
                return [
                    'type' => $type,
                    'label' => $typeLabels[$type] ?? $this->formatRoomTypeLabel($type),
                    'rooms' => $items
                        ->sortBy('nome')
                        ->values()
                        ->map(fn (SalaInternacao $room) => $this->presentOccupancyRoom($room, $statusMeta))
                        ->all(),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function presentOccupancyRoom(SalaInternacao $room, array $statusMeta): array
    {
        $patients = $room->internacoes
            ->map(fn (Internacao $hospitalization) => $this->presentRoomPatient($hospitalization))
            ->values()
            ->all();

        $capacity = max(0, (int) ($room->capacidade ?? 0));
        $occupied = count($patients);
        $available = max(0, $capacity - $occupied);
        $occupancyPercentage = $capacity > 0 ? (int) round(($occupied / $capacity) * 100) : null;

        $statusValue = $room->status ?: SalaInternacao::STATUS_AVAILABLE;

        return [
            'id' => $room->id,
            'name' => $room->nome,
            'identifier' => $room->identificador,
            'type' => $room->tipo,
            'status' => [
                'value' => $statusValue,
                'label' => $statusMeta[$statusValue]['label'] ?? $this->formatRoomStatusLabel($statusValue),
                'color' => $statusMeta[$statusValue]['color'] ?? 'secondary',
            ],
            'capacity' => $capacity,
            'occupied' => $occupied,
            'available' => $available,
            'occupancy_percentage' => $occupancyPercentage,
            'equipments' => $room->equipamentos
                ? collect(explode(',', (string) $room->equipamentos))
                    ->map(fn ($item) => trim($item))
                    ->filter()
                    ->values()
                    ->all()
                : [],
            'notes' => $room->observacoes ?: null,
            'patients' => $patients,
        ];
    }

    private function presentRoomPatient(Internacao $hospitalization): array
    {
        $patient = $hospitalization->animal;
        $tutor = $hospitalization->tutor ?: $patient?->cliente;

        return [
            'id' => $hospitalization->id,
            'name' => $patient?->nome ?? 'Paciente',
            'avatar' => $this->generateAvatarUrl($patient?->nome),
            'species' => $patient?->especie?->nome,
            'breed' => $patient?->raca?->nome,
            'age' => $patient ? $this->formatAge($patient) : null,
            'weight' => $patient ? $this->formatWeight($patient->peso ?? null) : null,
            'status' => [
                'label' => $hospitalization->status_label,
                'color' => $hospitalization->status_color,
            ],
            'risk' => [
                'label' => $hospitalization->risk_label,
                'color' => $hospitalization->risk_color,
            ],
            'admitted_at' => optional($hospitalization->internado_em)->format('d/m/Y H:i'),
            'expected_discharge_at' => optional($hospitalization->previsao_alta_em)->format('d/m/Y'),
            'reason' => $hospitalization->motivo ?: null,
            'notes' => $hospitalization->observacoes ?: null,
            'tutor' => $this->presentTutor($tutor instanceof Cliente ? $tutor : null),
            'veterinarian' => optional($hospitalization->veterinarian?->funcionario)->nome,
            'urls' => [
                'status' => Route::has('vet.hospitalizations.status.index')
                    ? route('vet.hospitalizations.status.index', $hospitalization)
                    : null,
                'edit' => Route::has('vet.hospitalizations.edit')
                    ? route('vet.hospitalizations.edit', $hospitalization)
                    : null,
            ],
        ];
    }

    private function presentInpatient(Internacao $hospitalization): array
    {
        $patient = $hospitalization->animal;
        $tutor = $hospitalization->tutor ?: $patient?->cliente;
        $room = $hospitalization->room;

        $statusValue = $hospitalization->status ?: Internacao::STATUS_ACTIVE;
        $statusMeta = $this->inpatientStatusMeta()[$statusValue]
            ?? $this->defaultInpatientStatusMeta($statusValue);

        $admissionAt = $hospitalization->internado_em ? $hospitalization->internado_em->copy() : null;
        $expectedDischargeAt = $hospitalization->previsao_alta_em
            ? $hospitalization->previsao_alta_em->copy()
            : null;

        $riskColor = $hospitalization->risk_color ?: 'secondary';
        $accentColor = $this->resolveRiskHexColor($riskColor);

        $admission = $this->formatAdmission($admissionAt);
        $expectedDischarge = $this->formatExpectedDischarge($expectedDischargeAt);

        $searchIndex = Str::lower(collect([
            $patient?->nome,
            $patient?->especie?->nome,
            $patient?->raca?->nome,
            $patient?->pelagem?->nome ?: $patient?->cor,
            $patient?->porte,
            $tutor?->razao_social,
            $tutor?->nome_fantasia,
            $tutor?->contato,
            $tutor?->celular,
            $room?->nome,
            $room?->tipo ? $this->formatRoomTypeLabel($room->tipo) : null,
            optional($hospitalization->veterinarian?->funcionario)->nome,
            $hospitalization->motivo,
            $hospitalization->observacoes,
        ])->filter()->implode(' '));

        return [
            'id' => $hospitalization->id,
            'status' => $statusMeta,
            'accent_color' => $accentColor,
            'admission' => $admission,
            'expected_discharge' => $expectedDischarge,
            'patient' => [
                'name' => $patient?->nome ?? 'Paciente',
                'species' => $patient?->especie?->nome,
                'breed' => $patient?->raca?->nome,
                'coat' => $patient?->pelagem?->nome ?: $patient?->cor,
                'size' => $patient?->porte,
            ],
            'risk' => [
                'label' => $hospitalization->risk_label,
                'color' => $riskColor,
                'hex' => $accentColor,
                'value' => $hospitalization->nivel_risco,
            ],
            'room' => [
                'name' => $room?->nome,
                'type' => $room?->tipo ? $this->formatRoomTypeLabel($room->tipo) : null,
                'type_value' => $room?->tipo,
            ],
            'tutor' => [
                'name' => $tutor?->razao_social ?? $tutor?->nome_fantasia ?? null,
                'phone' => $this->formatPhoneNumber($tutor?->contato ?? $tutor?->celular ?? null),
                'id' => $tutor?->id,
            ],
            'veterinarian' => optional($hospitalization->veterinarian?->funcionario)->nome,
            'reason' => $hospitalization->motivo ?: null,
            'notes' => $hospitalization->observacoes ?: null,
            'status_value' => $statusMeta['value'] ?? $hospitalization->status,
            'veterinarian_id' => $hospitalization->veterinario_id,
            'search_index' => $searchIndex,
            'admission_date' => $admission['date'] ?? null,
            'discharge_date' => $expectedDischarge['date'] ?? null,
        ];
    }

    private function inpatientStatusMeta(): array
    {
        return [
            Internacao::STATUS_DRAFT => [
                'value' => Internacao::STATUS_DRAFT,
                'label' => 'Rascunho',
                'short' => 'RS',
                'icon' => 'ri-draft-line',
                'class' => 'estado-pendente-aprovacao-area',
                'color' => '#efc90b',
            ],
            Internacao::STATUS_ACTIVE => [
                'value' => Internacao::STATUS_ACTIVE,
                'label' => 'Internado',
                'short' => 'IN',
                'icon' => 'ri-heart-pulse-line',
                'class' => 'estado-em-andamento-area',
                'color' => '#0a7cff',
            ],
            Internacao::STATUS_DISCHARGED => [
                'value' => Internacao::STATUS_DISCHARGED,
                'label' => 'Alta',
                'short' => 'AL',
                'icon' => 'ri-door-open-line',
                'class' => 'estado-concluido-area',
                'color' => '#28A745',
            ],
            Internacao::STATUS_CANCELLED => [
                'value' => Internacao::STATUS_CANCELLED,
                'label' => 'Cancelado',
                'short' => 'CC',
                'icon' => 'ri-close-circle-line',
                'class' => 'estado-cancelado-area',
                'color' => '#f40707',
            ],
        ];
    }

    private function defaultInpatientStatusMeta(string $status): array
    {
        $normalized = trim((string) $status);
        $label = $normalized !== ''
            ? ucfirst(str_replace(['_', '-'], ' ', $normalized))
            : 'Indefinido';

        $compactLabel = str_replace(' ', '', $label);
        $short = mb_strtoupper(mb_substr($compactLabel, 0, 2, 'UTF-8'), 'UTF-8');

        if ($short === '') {
            $short = 'IN';
        }

        return [
            'value' => $status,
            'label' => $label,
            'short' => $short,
            'icon' => 'ri-information-line',
            'class' => 'estado-pendente-aprovacao-area',
            'color' => '#56327A',
        ];
    }

    private function formatAdmission(?Carbon $date): array
    {
        if (! $date) {
            return [
                'date' => null,
                'time' => null,
                'date_key' => 'sem-data',
                'timestamp' => null,
            ];
        }

        return [
            'date' => $date->format('d/m/Y'),
            'time' => $date->format('H:i'),
            'date_key' => $date->format('Y-m-d'),
            'timestamp' => $date->timestamp,
        ];
    }

    private function formatExpectedDischarge(?Carbon $date): array
    {
        if (! $date) {
            return [
                'date' => null,
                'time' => null,
            ];
        }

        return [
            'date' => $date->format('d/m/Y'),
            'time' => $date->format('H:i'),
        ];
    }

    private function resolveRiskHexColor(?string $color): string
    {
        return match ($color) {
            'success' => '#28A745',
            'warning' => '#f68e38',
            'danger' => '#f40707',
            'info' => '#0dcaf0',
            'secondary' => '#6c6a82',
            'dark' => '#343a40',
            default => '#56327A',
        };
    }

    private function roomStatusMeta(): array
    {
        return [
            SalaInternacao::STATUS_AVAILABLE => [
                'label' => 'Disponível',
                'color' => 'success',
            ],
            SalaInternacao::STATUS_OCCUPIED => [
                'label' => 'Ocupada',
                'color' => 'danger',
            ],
            SalaInternacao::STATUS_RESERVED => [
                'label' => 'Reservada',
                'color' => 'warning',
            ],
            SalaInternacao::STATUS_MAINTENANCE => [
                'label' => 'Em manutenção',
                'color' => 'secondary',
            ],
        ];
    }

    private function roomTypeLabels(): array
    {
        return [
            'internacao-geral' => 'Internação geral',
            'isolamento' => 'Isolamento',
            'terapia-intensiva' => 'Terapia intensiva',
            'pos-operatorio' => 'Pós-operatório',
            'recuperacao' => 'Sala de recuperação',
            'infectocontagioso' => 'Controle de infectocontagiosos',
            'neonatal' => 'Internação neonatal',
            'outro' => 'Outro',
        ];
    }

    private function formatRoomTypeLabel(?string $type): string
    {
        if (! $type) {
            return 'Setor não informado';
        }

        return collect(explode('-', (string) $type))
            ->filter()
            ->map(fn ($part) => ucfirst($part))
            ->implode(' ');
    }

    private function formatRoomStatusLabel(?string $status): string
    {
        if (! $status) {
            return 'Indefinido';
        }

        return ucfirst(str_replace('-', ' ', $status));
    }

    private function buildAlerts(Collection $hospitalizations): array
    {
        $alerts = [];

        foreach ($hospitalizations as $internacao) {
            if ($internacao->status === Internacao::STATUS_ACTIVE && $internacao->nivel_risco === Internacao::RISK_HIGH) {
                $alerts[] = [
                    'icon' => 'ri-alert-line',
                    'color' => 'danger',
                    'message' => sprintf(
                        'Paciente %s com risco alto. Intensificar monitoramento.',
                        $internacao->animal?->nome ?? 'sem identificação'
                    ),
                ];
            }

            if ($internacao->status === Internacao::STATUS_ACTIVE && $internacao->previsao_alta_em?->isToday()) {
                $alerts[] = [
                    'icon' => 'ri-calendar-check-line',
                    'color' => 'warning',
                    'message' => sprintf(
                        'Confirmar alta do paciente %s prevista para hoje.',
                        $internacao->animal?->nome ?? 'sem identificação'
                    ),
                ];
            }
        }

        return collect($alerts)
            ->unique('message')
            ->values()
            ->all();
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function findPatientInOptions(array $patients, string $patientId): ?array
    {
        foreach ($patients as $option) {
            if (($option['id'] ?? null) === $patientId) {
                return $option['patient'] ?? null;
            }
        }

        return null;
    }

    private function normalizeId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function fetchPatientOptions(int $companyId): array
    {
        return Animal::query()
            ->with(['cliente', 'especie', 'raca'])
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get()
            ->map(function (Animal $animal) {
                $patient = $this->presentPatient($animal);

                return [
                    'id' => $patient['id'],
                    'label' => $patient['label'],
                    'patient' => $patient,
                ];
            })
            ->values()
            ->all();
    }

    private function loadPatientDetails(int $companyId, string $patientId): ?array
    {
        $animal = Animal::query()
            ->with(['cliente', 'especie', 'raca'])
            ->where('empresa_id', $companyId)
            ->find($patientId);

        return $animal ? $this->presentPatient($animal) : null;
    }

    private function presentPatient(Animal $animal): array
    {
        $species = $animal->especie?->nome;
        $breed = $animal->raca?->nome;
        $age = $this->formatAge($animal);
        $weight = $this->formatWeight($animal->peso);
        $sex = $this->formatSex($animal->sexo);

        $metaParts = array_filter([$species, $breed, $age]);
        $meta = $metaParts ? implode(' • ', $metaParts) : null;

        $labelParts = array_filter([
            $animal->nome,
            $species,
        ]);

        return [
            'id' => (string) $animal->id,
            'name' => $animal->nome,
            'species' => $species,
            'breed' => $breed,
            'age' => $age,
            'sex' => $sex,
            'weight' => $weight,
            'microchip' => $animal->chip ? trim((string) $animal->chip) : null,
            'pedigree' => $this->formatPedigree($animal),
            'notes' => $animal->observacao ? trim((string) $animal->observacao) : null,
            'label' => $labelParts ? implode(' • ', $labelParts) : $animal->nome,
            'meta' => $meta,
            'photo' => $this->generateAvatarUrl($animal->nome),
            'tutor' => $this->presentTutor($animal->cliente),
            'metrics' => [
                ['label' => 'Peso', 'value' => $weight ?? '—'],
                ['label' => 'Sexo', 'value' => $sex ?? '—'],
                ['label' => 'Idade', 'value' => $age ?? '—'],
                ['label' => 'Microchip', 'value' => $animal->chip ? trim((string) $animal->chip) : '—'],
                ['label' => 'Pedigree', 'value' => $this->formatPedigree($animal) ?? '—'],
            ],
        ];
    }

    private function presentTutor(?Cliente $tutor): array
    {
        $phones = $this->collectTutorPhones($tutor);

        return [
            'id' => $tutor?->id ? (string) $tutor->id : null,
            'name' => $this->resolveTutorName($tutor),
            'document' => $this->formatTutorDocument($tutor),
            'contact' => $phones[0] ?? null,
            'email' => $this->formatTutorEmail($tutor),
            'phones' => $phones,
        ];
    }

    private function resolveTutorName(?Cliente $tutor): ?string
    {
        if (! $tutor) {
            return null;
        }

        $candidates = array_filter([
            $tutor->razao_social,
            $tutor->nome_fantasia,
            $tutor->contato,
        ]);

        return $candidates ? (string) reset($candidates) : null;
    }

    private function collectTutorPhones(?Cliente $tutor): array
    {
        if (! $tutor) {
            return [];
        }

        return collect([
            $tutor->telefone,
            $tutor->telefone_secundario,
            $tutor->telefone_terciario,
            $tutor->celular ?? null,
        ])
            ->filter(fn ($phone) => $phone !== null && trim((string) $phone) !== '')
            ->map(fn ($phone) => $this->formatPhoneNumber($phone))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function fetchVeterinarianOptions(int $companyId): array
    {
        return Medico::query()
            ->with('funcionario')
            ->where('empresa_id', $companyId)
            ->orderBy('id')
            ->get()
            ->map(fn (Medico $medico) => [
                'id' => (string) $medico->id,
                'label' => $this->buildVeterinarianLabel($medico),
                'name' => optional($medico->funcionario)->nome ?? 'Profissional',
                'specialty' => $medico->especialidade ?: null,
            ])
            ->values()
            ->all();
    }

    private function buildVeterinarianLabel(Medico $medico): string
    {
        return collect([
            optional($medico->funcionario)->nome,
            $medico->especialidade ?: null,
            $medico->crmv ? 'CRMV ' . $medico->crmv : null,
        ])->filter()->implode(' • ');
    }

    private function fetchRoomOptions(int $companyId): array
    {
        return SalaInternacao::query()
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get()
            ->map(fn (SalaInternacao $room) => [
                'id' => (string) $room->id,
                'label' => $this->buildRoomLabel($room),
                'status' => $room->status,
                'capacity' => $room->capacidade,
                'type' => $room->tipo,
                'notes' => $room->observacoes,
            ])
            ->values()
            ->all();
    }

    private function buildRoomLabel(SalaInternacao $room): string
    {
        return collect([
            $room->nome,
            $room->identificador,
            $room->tipo,
        ])->filter()->implode(' • ');
    }

    private function formatAttendanceContext(Atendimento $attendance): array
    {
        return [
            'id' => (string) $attendance->id,
            'code' => $attendance->codigo,
            'scheduled_at' => $attendance->start_at ? $attendance->start_at->format('d/m/Y H:i') : null,
            'status' => $attendance->status_label,
            'status_color' => $attendance->status_color,
            'patient' => $attendance->animal?->nome,
            'veterinarian' => optional($attendance->veterinario?->funcionario)->nome,
            'url' => route('vet.atendimentos.history', $attendance->id),
        ];
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

    private function formatSex(?string $sex): ?string
    {
        $normalized = strtoupper((string) $sex);

        return match ($normalized) {
            'M' => 'Macho',
            'F' => 'Fêmea',
            default => $normalized !== '' ? ucfirst(strtolower($normalized)) : null,
        };
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

    private function formatTutorDocument(?Cliente $tutor): ?string
    {
        if (! $tutor || ! $tutor->cpf_cnpj) {
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

    private function formatTutorEmail(?Cliente $tutor): ?string
    {
        if (! $tutor || ! $tutor->email) {
            return null;
        }

        return strtolower(trim($tutor->email));
    }

    private function formatPhoneNumber(?string $value): ?string
    {
        if (! $value) {
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

    private function generateAvatarUrl(?string $name): string
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

        $index = 0;

        if ($name) {
            $index = abs(crc32($name)) % count($avatars);
        }

        return asset($avatars[$index]);
    }

    private function getEmpresaId(): ?int
    {
        return Auth::user()?->empresa?->empresa_id;
    }

}