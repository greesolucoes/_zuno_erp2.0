@extends('default.layout', ['title' => 'Agendar vacinação'])

@section('css')
    <style>
        .vet-vaccination-schedule__card {
            border: 1px solid rgba(22, 22, 107, 0.06);
            border-radius: 16px;
            box-shadow: 0 12px 28px rgba(22, 22, 107, 0.07);
        }

        .vet-vaccination-schedule__section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #16166b;
        }

        .vet-vaccination-schedule__section-subtitle {
            color: #6c757d;
            font-size: .825rem;
        }

        .vet-vaccination-schedule__badge-soft {
            background: rgba(85, 110, 230, 0.12);
            color: #556ee6;
            border-radius: 999px;
            font-weight: 600;
            padding: .35rem .75rem;
            font-size: .75rem;
        }

        .vet-vaccination-schedule__info-label {
            color: #6c757d;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .vet-vaccination-schedule__info-value {
            color: #16166b;
            font-weight: 600;
        }

        .vet-vaccination-schedule__alert-item {
            border-radius: 12px;
            padding: .75rem;
            display: flex;
            gap: .75rem;
            align-items: flex-start;
        }

        .vet-vaccination-schedule__alert-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .vet-vaccination-schedule__timeline::before {
            content: '';
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 18px;
            width: 2px;
            background: rgba(85, 110, 230, 0.18);
        }

        .vet-vaccination-schedule__timeline-item {
            padding-left: 48px;
            position: relative;
        }

        .vet-vaccination-schedule__timeline-item::before {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            left: 12px;
            top: 4px;
            background: #556ee6;
            box-shadow: 0 0 0 4px rgba(85, 110, 230, 0.2);
        }

        .vet-vaccination-schedule__timeline-date {
            font-size: .75rem;
            color: #556ee6;
            font-weight: 600;
        }

        .vet-vaccination-schedule__stock-card {
            border-radius: 12px;
            border: 1px solid rgba(52, 195, 143, 0.15);
            background: rgba(52, 195, 143, 0.08);
            padding: 1rem;
        }

        .vet-vaccination-schedule__stock-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1f6f54;
        }

        .vet-vaccination-schedule__slot-card {
            border-radius: 14px;
            background: rgba(85, 110, 230, 0.08);
            border: 1px solid rgba(85, 110, 230, 0.16);
            padding: 1rem;
        }

        .vet-vaccination-schedule__slot-card .badge {
            font-size: .75rem;
            font-weight: 600;
        }

        .vet-vaccination-schedule__checklist-item {
            border: 1px solid rgba(22, 22, 107, 0.08);
            border-radius: 12px;
            padding: .75rem;
            display: flex;
            align-items: flex-start;
            gap: .75rem;
        }

        .vet-vaccination-schedule__form .form-control,
        .vet-vaccination-schedule__form .form-select,
        .vet-vaccination-schedule__form .form-check-input {
            border-radius: 12px;
        }

        .vet-vaccination-schedule__form textarea {
            min-height: 120px;
        }

        .vet-vaccination-dose-card {
            border: 1px solid rgba(22, 22, 107, 0.08);
            border-radius: 14px;
            padding: 1.25rem;
            background: rgba(85, 110, 230, 0.04);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .vet-vaccination-dose-card.is-active {
            border-color: #556ee6;
            box-shadow: 0 0 0 3px rgba(85, 110, 230, 0.12);
        }

        .vet-vaccination-dose-card__header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .vet-vaccination-dose-card__title {
            margin-bottom: 0;
            font-weight: 600;
            color: #16166b;
        }

        .vet-vaccination-tabs {
            display: flex;
            flex-wrap: wrap;
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            box-shadow: 0 12px 24px rgba(22, 22, 107, 0.08);
            padding: .75rem;
        }

        .vet-vaccination-tabs .nav-link {
            border: none;
            border-radius: 12px;
            background: transparent;
            color: #495057;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .65rem 1rem;
            transition: all .2s ease;
            flex: 1 1 220px;
        }

        .vet-vaccination-tabs .nav-link i {
            font-size: 1.1rem;
        }

        .vet-vaccination-tabs .nav-link:hover,
        .vet-vaccination-tabs .nav-link:focus {
            background-color: rgba(114, 59, 233, 0.18);
            color: #3a1e4b !important;
        }

        .vet-vaccination-tabs .nav-link.active {
            background-color: #3a1e4b;
            color: #fff !important;
            box-shadow: 0 12px 30px rgba(119, 85, 230, 0.25);
        }
    </style>
@endsection

@section('content')
    @php($formActiveVaccine = $activeVaccine ?? ($vaccines[0] ?? null))
    @php($formActiveDate = $activeDate ?? ($availability[0] ?? null))
    @php($formActiveSlot = $activeSlot ?? ($formActiveDate['slots'][0] ?? null))
    @php($currentVeterinarianId = old('veterinarian_id', $defaultValues['veterinarian_id'] ?? ($selectedVeterinarian['id'] ?? '')))
    @php($selectedVeterinarian = collect($veterinarians)->firstWhere(fn ($vet) => (string) $vet['id'] === (string) $currentVeterinarianId) ?? ($selectedVeterinarian ?? null))

<x-form-page
    title="Agendar vacinação"
    heading="{{ $formMode === 'edit' ? 'Editar agendamento de vacinação' : 'Agendar nova vacinação' }}"
    :back-url="route('vet.vaccinations.index', ['page' => request()->query('page', 1)])"
>
    <div class="vet-vaccination-schedule">

        @if (!empty($attendanceContext))
            <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
                <i class="ri-calendar-check-line fs-4 text-info mt-1"></i>
                <div>
                    <strong>Atendimento vinculado:</strong>
                    <a href="{{ $attendanceContext['url'] }}" class="text-decoration-none">{{ $attendanceContext['code'] }}</a>
                    <div class="small text-muted">
                        @if (!empty($attendanceContext['status']))
                            {{ $attendanceContext['status'] }}
                        @endif
                        @if (!empty($attendanceContext['scheduled_at']))
                            @if (!empty($attendanceContext['status']))
                                ·
                            @endif
                            {{ $attendanceContext['scheduled_at'] }}
                        @endif
                        @if (!empty($attendanceContext['patient']) || !empty($attendanceContext['veterinarian']))
                            <br>
                            @if (!empty($attendanceContext['patient']))
                                Paciente: {{ $attendanceContext['patient'] }}
                            @endif
                            @if (!empty($attendanceContext['patient']) && !empty($attendanceContext['veterinarian']))
                                •
                            @endif
                            @if (!empty($attendanceContext['veterinarian']))
                                Veterinário: {{ $attendanceContext['veterinarian'] }}
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (!empty($sourceExam))
            <div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
                <i class="ri-flask-line fs-4 text-warning mt-1"></i>
                <div>
                    <strong>Dados importados do exame {{ $sourceExam['type'] ?? 'clínico' }}</strong>
                    <div class="small text-muted">
                        @if (!empty($sourceExam['status']))
                            Status: {{ $sourceExam['status'] }}
                        @endif
                        @if (!empty($sourceExam['patient']))
                            @if (!empty($sourceExam['status']))
                                •
                            @endif
                            Paciente: {{ $sourceExam['patient'] }}
                        @endif
                        @if (!empty($sourceExam['attendance']['code']))
                            <br>
                            Atendimento associado:
                            <a href="{{ $sourceExam['attendance']['url'] }}" class="text-decoration-none">
                                {{ $sourceExam['attendance']['code'] }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ $formAction }}" method="post" class="row g-4" id="vet-vaccination-form">
            @csrf
            @if (!empty($formMethod) && strtoupper($formMethod) !== 'POST')
                @method($formMethod)
            @endif
            <input
                type="hidden"
                name="attendance_id"
                value="{{ old('attendance_id', $defaultValues['attendance_id'] ?? '') }}"
            >

            <div class="col-12">
                <div class="mb-4">
                    <ul class="nav nav-pills gap-2 vet-vaccination-tabs" id="vetVaccinationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="vetVaccinationTabPatient-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#vetVaccinationTabPatient"
                                type="button"
                                role="tab"
                                aria-controls="vetVaccinationTabPatient"
                                aria-selected="true"
                            >
                                <i class="ri-user-heart-line"></i>
                                <span>Paciente</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="vetVaccinationTabVaccine-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#vetVaccinationTabVaccine"
                                type="button"
                                role="tab"
                                aria-controls="vetVaccinationTabVaccine"
                                aria-selected="false"
                            >
                                <i class="ri-syringe-line"></i>
                                <span>Vacinação</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="vetVaccinationTabSchedule-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#vetVaccinationTabSchedule"
                                type="button"
                                role="tab"
                                aria-controls="vetVaccinationTabSchedule"
                                aria-selected="false"
                            >
                                <i class="ri-calendar-check-line"></i>
                                <span>Agendamento</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="vetVaccinationTabChecklist-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#vetVaccinationTabChecklist"
                                type="button"
                                role="tab"
                                aria-controls="vetVaccinationTabChecklist"
                                aria-selected="false"
                            >
                                <i class="ri-task-line"></i>
                                <span>Checklist & comunicação</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card vet-vaccination-schedule__card border-0">
                    <div class="card-body vet-vaccination-schedule__form">
                        <div class="tab-content" id="vetVaccinationTabsContent">
                                <div
                                    class="tab-pane fade show active"
                                    id="vetVaccinationTabPatient"
                                    role="tabpanel"
                                    aria-labelledby="vetVaccinationTabPatient-tab"
                                >
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="vet-vaccination-schedule__section-title mb-1">Identificação do paciente</h5>
                                            <p class="vet-vaccination-schedule__section-subtitle mb-0">Selecione o paciente para carregar histórico, alertas e dados do tutor.</p>
                                        </div>
                                        @if ($activePatient)
                                            <span class="vet-vaccination-schedule__badge-soft" id="vet-vaccination-patient-plan">{{ $activePatient['plan'] }}</span>
                                        @endif
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            @php($currentPatientId = old('patient_id', $defaultValues['patient_id'] ?? ''))
                                            <label class="form-label small text-muted" for="vet-vaccination-patient-select">Paciente</label>
                                            <select
                                                id="vet-vaccination-patient-select"
                                                class="form-select select2"
                                                name="patient_id"
                                                data-placeholder="Selecione o paciente"
                                                data-allow-clear="true"
                                            >
                                                <option value=""></option>
                                                @foreach ($patients as $patient)
                                                    <option
                                                        value="{{ $patient['id'] }}"
                                                        data-patient='@json($patient)'
                                                        {{ (string) $patient['id'] === (string) $currentPatientId ? 'selected' : '' }}
                                                    >
                                                        {{ $patient['name'] }} • {{ $patient['species'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Tutor responsável</label>
                                            <input type="text" class="form-control" id="vet-vaccination-patient-tutor" value="{{ $activePatient['tutor'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Contato do tutor</label>
                                            <input type="text" class="form-control" id="vet-vaccination-patient-contact" value="{{ $activePatient['contact'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small text-muted">Espécie</label>
                                            <input type="text" class="form-control" id="vet-vaccination-patient-species" value="{{ $activePatient['species'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small text-muted">Raça</label>
                                            <input type="text" class="form-control" id="vet-vaccination-patient-breed" value="{{ $activePatient['breed'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small text-muted">Idade</label>
                                            <input type="text" class="form-control" id="vet-vaccination-patient-age" value="{{ $activePatient['age'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label small text-muted">Peso atual</label>
                                            <input type="text" class="form-control" id="vet-vaccination-patient-weight" value="{{ $activePatient['weight'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small text-muted">Observações internas</label>
                                            <textarea class="form-control" id="vet-vaccination-patient-notes" name="patient_notes" placeholder="Registre observações que auxiliem a equipe de aplicação.">{{ old('patient_notes', $defaultValues['patient_notes'] ?? ($activePatient['notes'] ?? '')) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="tab-pane fade"
                                    id="vetVaccinationTabVaccine"
                                    role="tabpanel"
                                    aria-labelledby="vetVaccinationTabVaccine-tab"
                                >
                                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                        <div>
                                            <h5 class="vet-vaccination-schedule__section-title mb-1">Dados da vacinação</h5>
                                            <p class="vet-vaccination-schedule__section-subtitle mb-0">Configure a vacina, lote e orientações técnicas para a aplicação.</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" id="vet-vaccination-add-vaccine">
                                            <i class="ri-add-line"></i>
                                            Adicionar nova vacina
                                        </button>
                                    </div>

                                    <div class="d-flex flex-column gap-3" id="vet-vaccination-vaccine-container">
                                        @foreach ($formVaccinations as $index => $dose)
                                            @php($currentDose = old('vaccinations.' . $index, []))
                                            @php($selectedVaccineId = old("vaccinations.$index.vaccine_id", $dose['vaccine_id'] ?? ''))
                                            <div class="vet-vaccination-dose-card {{ $loop->first ? 'is-active' : '' }}" data-vaccine-card data-index="{{ $index }}">
                                                <div class="vet-vaccination-dose-card__header mb-3">
                                                    <h6 class="vet-vaccination-dose-card__title">Vacinação <span data-role="dose-index">{{ $loop->iteration }}</span></h6>
                                                    <button type="button" class="btn btn-link text-danger text-decoration-none small d-flex align-items-center gap-1 {{ $loop->count === 1 ? 'd-none' : '' }}" data-action="remove-card">
                                                        <i class="ri-close-line"></i>
                                                        Remover
                                                    </button>
                                                </div>
                                                <div class="row g-3">
                                                    <input type="hidden" name="vaccinations[{{ $index }}][planned_id]" value="{{ old("vaccinations.$index.planned_id", $dose['planned_id'] ?? '') }}">
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-select-{{ $index }}">Vacina</label>
                                                        <select
                                                            class="form-select select2"
                                                            id="vet-vaccination-vaccine-select-{{ $index }}"
                                                            data-role="vaccine-select"
                                                            data-placeholder="Selecione a vacina"
                                                            name="vaccinations[{{ $index }}][vaccine_id]"
                                                        >
                                                            @forelse ($vaccines as $vaccine)
                                                                <option value="{{ $vaccine['id'] }}" {{ (string) $vaccine['id'] === (string) $selectedVaccineId ? 'selected' : '' }}>{{ $vaccine['name'] }}</option>
                                                            @empty
                                                                <option value="" selected disabled>Nenhuma vacina cadastrada</option>
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-manufacturer-{{ $index }}">Fabricante</label>
                                                        <input type="text" class="form-control" id="vet-vaccination-vaccine-manufacturer-{{ $index }}" data-field="manufacturer" name="vaccinations[{{ $index }}][manufacturer]" value="{{ old("vaccinations.$index.manufacturer", $dose['manufacturer'] ?? '') }}" readonly>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-volume-{{ $index }}">Volume</label>
                                                        <input type="text" class="form-control" id="vet-vaccination-vaccine-volume-{{ $index }}" data-field="volume" name="vaccinations[{{ $index }}][volume]" value="{{ old("vaccinations.$index.volume", $dose['volume'] ?? '') }}" readonly>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-lot-{{ $index }}">Lote</label>
                                                        <input type="text" class="form-control" id="vet-vaccination-vaccine-lot-{{ $index }}" data-field="lot" name="vaccinations[{{ $index }}][lot]" value="{{ old("vaccinations.$index.lot", $dose['lot'] ?? '') }}" readonly>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-valid-{{ $index }}">Validade</label>
                                                        <input type="text" class="form-control" id="vet-vaccination-vaccine-valid-{{ $index }}" data-field="valid_until" name="vaccinations[{{ $index }}][valid_until]" value="{{ old("vaccinations.$index.valid_until", $dose['valid_until'] ?? '') }}" readonly>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-route-{{ $index }}">Via de aplicação</label>
                                                        <input type="text" class="form-control" id="vet-vaccination-vaccine-route-{{ $index }}" data-field="route" name="vaccinations[{{ $index }}][route]" value="{{ old("vaccinations.$index.route", $dose['route'] ?? '') }}" readonly>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-dose-{{ $index }}">Dose/etapa</label>
                                                        <input type="text" class="form-control" id="vet-vaccination-vaccine-dose-{{ $index }}" data-field="dose" name="vaccinations[{{ $index }}][dose]" value="{{ old("vaccinations.$index.dose", $dose['dose'] ?? '') }}" readonly>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-site-{{ $index }}">Local anatômico</label>
                                                        <input type="text" class="form-control" id="vet-vaccination-vaccine-site-{{ $index }}" data-field="site" name="vaccinations[{{ $index }}][site]" value="{{ old("vaccinations.$index.site", $dose['site'] ?? '') }}" readonly>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small text-muted" for="vet-vaccination-vaccine-observations-{{ $index }}">Orientações ao aplicador</label>
                                                        <textarea class="form-control" id="vet-vaccination-vaccine-observations-{{ $index }}" data-field="observations" name="vaccinations[{{ $index }}][observations]" placeholder="Inclua orientações específicas para a equipe.">{{ old("vaccinations.$index.observations", $dose['observations'] ?? '') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <template id="vet-vaccination-vaccine-template">
                                        <div class="vet-vaccination-dose-card" data-vaccine-card data-index="__INDEX__">
                                            <div class="vet-vaccination-dose-card__header mb-3">
                                                <h6 class="vet-vaccination-dose-card__title">Vacinação <span data-role="dose-index">1</span></h6>
                                                <button type="button" class="btn btn-link text-danger text-decoration-none small d-flex align-items-center gap-1" data-action="remove-card">
                                                    <i class="ri-close-line"></i>
                                                    Remover
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <input type="hidden" name="vaccinations[__INDEX__][planned_id]" value="">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-select-__INDEX__">Vacina</label>
                                                    <select
                                                        class="form-select select2"
                                                        id="vet-vaccination-vaccine-select-__INDEX__"
                                                        data-role="vaccine-select"
                                                        data-placeholder="Selecione a vacina"
                                                        name="vaccinations[__INDEX__][vaccine_id]"
                                                    >
                                                        @forelse ($vaccines as $vaccine)
                                                            <option value="{{ $vaccine['id'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $vaccine['name'] }}</option>
                                                        @empty
                                                            <option value="" selected disabled>Nenhuma vacina cadastrada</option>
                                                        @endforelse
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-3">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-manufacturer-__INDEX__">Fabricante</label>
                                                    <input type="text" class="form-control" id="vet-vaccination-vaccine-manufacturer-__INDEX__" data-field="manufacturer" name="vaccinations[__INDEX__][manufacturer]" readonly>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-volume-__INDEX__">Volume</label>
                                                    <input type="text" class="form-control" id="vet-vaccination-vaccine-volume-__INDEX__" data-field="volume" name="vaccinations[__INDEX__][volume]" readonly>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-lot-__INDEX__">Lote</label>
                                                    <input type="text" class="form-control" id="vet-vaccination-vaccine-lot-__INDEX__" data-field="lot" name="vaccinations[__INDEX__][lot]" readonly>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-valid-__INDEX__">Validade</label>
                                                    <input type="text" class="form-control" id="vet-vaccination-vaccine-valid-__INDEX__" data-field="valid_until" name="vaccinations[__INDEX__][valid_until]" readonly>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-route-__INDEX__">Via de aplicação</label>
                                                    <input type="text" class="form-control" id="vet-vaccination-vaccine-route-__INDEX__" data-field="route" name="vaccinations[__INDEX__][route]" readonly>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-dose-__INDEX__">Dose/etapa</label>
                                                    <input type="text" class="form-control" id="vet-vaccination-vaccine-dose-__INDEX__" data-field="dose" name="vaccinations[__INDEX__][dose]" readonly>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-site-__INDEX__">Local anatômico</label>
                                                    <input type="text" class="form-control" id="vet-vaccination-vaccine-site-__INDEX__" data-field="site" name="vaccinations[__INDEX__][site]" readonly>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small text-muted" for="vet-vaccination-vaccine-observations-__INDEX__">Orientações ao aplicador</label>
                                                    <textarea class="form-control" id="vet-vaccination-vaccine-observations-__INDEX__" data-field="observations" name="vaccinations[__INDEX__][observations]" placeholder="Inclua orientações específicas para a equipe."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div
                                    class="tab-pane fade"
                                    id="vetVaccinationTabSchedule"
                                    role="tabpanel"
                                    aria-labelledby="vetVaccinationTabSchedule-tab"
                                >
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="vet-vaccination-schedule__section-title mb-1">Agendamento e recursos</h5>
                                            <p class="vet-vaccination-schedule__section-subtitle mb-0">Selecione data, horário, sala e profissional responsável pela aplicação.</p>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        @php($currentScheduledDate = old('scheduled_date', $defaultValues['scheduled_date'] ?? ($formActiveDate['id'] ?? '')))
                                        @php($currentScheduledTime = old('scheduled_time', $defaultValues['scheduled_time'] ?? ($formActiveSlot['time'] ?? '')))
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Data preferencial</label>
                                            <select class="form-select" id="vet-vaccination-date-select" name="scheduled_date">
                                                @foreach ($availability as $date)
                                                    <option value="{{ $date['id'] }}" {{ (string) $date['id'] === (string) $currentScheduledDate ? 'selected' : '' }}>{{ $date['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Horário disponível</label>
                                            <select class="form-select" id="vet-vaccination-time-select" name="scheduled_time">
                                                @if ($formActiveDate)
                                                    @foreach ($formActiveDate['slots'] as $slot)
                                                        <option value="{{ $slot['time'] }}" {{ (string) $slot['time'] === (string) $currentScheduledTime ? 'selected' : '' }}>{{ $slot['label'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Sala clínica</label>
                                            <select
                                                class="form-select select2"
                                                id="vet-vaccination-room-select"
                                                name="room_id"
                                                data-placeholder="Selecione a sala clínica"
                                                data-allow-clear="true"
                                            >
                                                @if (!empty($rooms))
                                                    <option value=""></option>
                                                @endif
                                                @php($currentRoomId = old('room_id', $defaultValues['room_id'] ?? ''))
                                                @forelse ($rooms as $room)
                                                    <option value="{{ $room['id'] }}" data-room='@json($room)' {{ (string) $room['id'] === (string) $currentRoomId ? 'selected' : '' }}>
                                                        {{ $room['label'] }}
                                                    </option>
                                                @empty
                                                    <option value="" selected disabled>Nenhuma sala disponível</option>
                                                @endforelse
                                            </select>
                                            <div class="form-text small text-muted mt-1" id="vet-vaccination-room-features">
                                                {{ $rooms[0]['features'] ?? (empty($rooms) ? 'Cadastre salas clínicas para habilitar esta seleção.' : 'Selecione uma sala para visualizar estrutura e equipamentos.') }}
                                            </div>
                                            @if (empty($rooms))
                                                <div class="form-text text-muted small">
                                                    Cadastre salas em <a href="{{ route('vet.salas-atendimento.index') }}" class="text-decoration-none">Salas de atendimento</a> para liberar esta opção.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">Veterinário responsável</label>
                                            <select
                                                class="form-select select2"
                                                id="vet-vaccination-vet-select"
                                                name="veterinarian_id"
                                                data-placeholder="Selecione o profissional"
                                                data-allow-clear="true"
                                                {{ empty($veterinarians) ? 'disabled' : '' }}
                                            >
                                                <option value=""></option>
                                                @forelse ($veterinarians as $veterinarian)
                                                    <option
                                                        value="{{ $veterinarian['id'] }}"
                                                        data-crm="{{ $veterinarian['crm'] }}"
                                                        data-veterinarian='@json($veterinarian)'
                                                        {{ (string) $veterinarian['id'] === (string) old('veterinarian_id', $defaultValues['veterinarian_id'] ?? '') ? 'selected' : '' }}
                                                    >
                                                        {{ $veterinarian['name'] }} — {{ $veterinarian['specialty'] }}
                                                    </option>
                                                @empty
                                                    <option value="" disabled>Nenhum veterinário cadastrado</option>
                                                @endforelse
                                            </select>
                                            @if (empty($veterinarians))
                                                <div class="form-text text-muted small">
                                                    Cadastre profissionais em <a href="{{ route('vet.medicos.index') }}" class="text-decoration-none">Médicos veterinários</a> para habilitar esta seleção.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small text-muted">CRMV</label>
                                            <input type="text" class="form-control" id="vet-vaccination-vet-crm" value="{{ $selectedVeterinarian['crm'] ?? '' }}" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small text-muted">Observações sobre estrutura e logística</label>
                                            <textarea class="form-control" id="vet-vaccination-room-notes" name="room_notes" placeholder="Ex.: reservar bomba de infusão, preparar suporte para paciente ansioso.">{{ old('room_notes', $defaultValues['room_notes'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="tab-pane fade"
                                    id="vetVaccinationTabChecklist"
                                    role="tabpanel"
                                    aria-labelledby="vetVaccinationTabChecklist-tab"
                                >
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="vet-vaccination-schedule__section-title mb-1">Comunicações e checklist</h5>
                                            <p class="vet-vaccination-schedule__section-subtitle mb-0">Defina lembretes ao tutor e confirme os passos obrigatórios antes da aplicação.</p>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-12 col-lg-6">
                                            <div class="p-3 bg-light rounded-3 h-100">
                                                <h6 class="text-muted text-uppercase small mb-3">Lembretes automáticos</h6>
                                                @php($currentReminders = collect(old('reminders', $defaultValues['reminders'] ?? []))->map(fn ($value) => (string) $value)->all())
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach ($reminders as $reminder)
                                                        <div class="form-check">
                                                            @php($isReminderChecked = in_array((string) $reminder['id'], $currentReminders, true))
                                                            <input class="form-check-input" type="checkbox" name="reminders[]" value="{{ $reminder['id'] }}" id="reminder-{{ $reminder['id'] }}" {{ $isReminderChecked ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="reminder-{{ $reminder['id'] }}">{{ $reminder['label'] }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="d-flex flex-column gap-2">
                                                @php($currentChecklist = collect(old('checklist', $defaultValues['checklist'] ?? []))->map(fn ($value) => (string) $value)->all())
                                                @foreach ($checklist as $item)
                                                    @php($isChecklistChecked = in_array((string) $item['id'], $currentChecklist, true))
                                                    <label class="vet-vaccination-schedule__checklist-item" for="{{ $item['id'] }}">
                                                        <input class="form-check-input mt-1" type="checkbox" name="checklist[]" value="{{ $item['id'] }}" id="{{ $item['id'] }}" {{ $isChecklistChecked ? 'checked' : '' }}>
                                                        <span class="text-color small">{{ $item['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label small text-muted">Instruções para o tutor</label>
                                            <textarea class="form-control" id="vet-vaccination-owner-notes" name="owner_notes" placeholder="Informe orientações pré e pós aplicação que serão enviadas ao tutor.">{{ old('owner_notes', $defaultValues['owner_notes'] ?? '') }}</textarea>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label small text-muted">Anexos do agendamento</label>
                                            <input type="file" class="form-control">
                                            <span class="text-muted small d-block mt-2">Aceite arquivos em PDF, JPG ou PNG. Tamanho máximo de 5 MB.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-success px-5">
                                    {{ $formMode === 'edit' ? 'Atualizar agendamento' : 'Salvar' }}
                                </button>
                            </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="d-flex flex-column gap-3">
                    <div
                        class="card shadow-sm"
                        id="vet-vaccination-patient-summary-card"
                        data-tab-context="#vetVaccinationTabPatient"
                    >
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-1 text-color">Resumo do paciente</h5>
                            <p class="text-muted mb-0 small">Informações rápidas sobre o paciente e o tutor.</p>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img
                                    id="vetEncounterPatientPhoto"
                                    src=""
                                    alt="Foto do paciente"
                                    class="rounded-circle mb-3"
                                    style="width: 96px; height: 96px; object-fit: cover;"
                                >
                                <h5 id="vetEncounterPatientName" class="mb-1"></h5>
                                <p id="vetEncounterPatientMeta" class="text-muted mb-0"></p>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Peso</span>
                                        <span id="vetEncounterPatientWeight" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Sexo</span>
                                        <span id="vetEncounterPatientSex" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Nascimento</span>
                                        <span id="vetEncounterPatientBirthDate" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Última visita</span>
                                        <span id="vetEncounterPatientLastVisit" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Porte</span>
                                        <span id="vetEncounterPatientSize" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Origem</span>
                                        <span id="vetEncounterPatientOrigin" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Microchip</span>
                                        <span id="vetEncounterPatientMicrochip" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-4 p-3 h-100">
                                        <span class="text-muted small d-block">Pedigree</span>
                                        <span id="vetEncounterPatientPedigree" class="fw-semibold text-color"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light rounded-4 p-3 mb-3">
                                <h6 class="text-color fs-6 mb-2">Observações clínicas</h6>
                                <p id="vetEncounterPatientNotes" class="text-muted small mb-0"></p>
                            </div>

                            <div class="bg-light rounded-4 p-3">
                                <h6 class="text-color fs-6 mb-2">Tutor responsável</h6>
                                <p id="vetEncounterTutorSummaryName" class="fw-semibold mb-1"></p>
                                <p id="vetEncounterTutorSummaryDocument" class="text-muted small mb-1"></p>
                                <p id="vetEncounterTutorSummaryContacts" class="text-muted small mb-1"></p>
                                <p id="vetEncounterTutorSummaryEmail" class="text-muted small mb-1"></p>
                                <p id="vetEncounterTutorSummaryAddress" class="text-muted small mb-0"></p>
                            </div>


                        </div>
                    </div>

                    <div
                        class="card vet-vaccination-schedule__card border-0"
                        data-tab-context="#vetVaccinationTabPatient"
                    >
                        <div class="card-body">
                            <h6 class="vet-vaccination-schedule__section-title mb-3">Alertas clínicos</h6>
                            <div class="d-flex flex-column gap-2" id="vet-vaccination-patient-alerts">
                                @if ($activePatient && ! empty($activePatient['alerts']))
                                    @foreach ($activePatient['alerts'] as $alert)
                                        @php($variant = [
                                            'danger' => ['class' => 'bg-danger-subtle text-danger', 'icon' => 'ri-error-warning-line'],
                                            'warning' => ['class' => 'bg-warning-subtle text-warning', 'icon' => 'ri-alert-line'],
                                            'info' => ['class' => 'bg-primary-subtle text-primary', 'icon' => 'ri-information-line'],
                                        ][$alert['type']] ?? ['class' => 'bg-light text-muted', 'icon' => 'ri-information-line'])
                                        <div class="vet-vaccination-schedule__alert-item bg-light">
                                            <span class="vet-vaccination-schedule__alert-icon {{ $variant['class'] }}">
                                                <i class="{{ $variant['icon'] }}"></i>
                                            </span>
                                            <div>
                                                <strong class="d-block text-color">{{ $alert['title'] }}</strong>
                                                <span class="text-muted small">{{ $alert['description'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Nenhum alerta clínico registrado para este paciente.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div
                        class="card vet-vaccination-schedule__card border-0 position-relative"
                        data-tab-context="#vetVaccinationTabPatient"
                    >
                        <div class="card-body">
                            <h6 class="vet-vaccination-schedule__section-title mb-3">Histórico recente</h6>
                            <div class="vet-vaccination-schedule__timeline position-relative" id="vet-vaccination-history">
                                @if ($activePatient && ! empty($activePatient['history']))
                                    @foreach ($activePatient['history'] as $history)
                                        <div class="vet-vaccination-schedule__timeline-item mb-3">
                                            <span class="vet-vaccination-schedule__timeline-date">{{ $history['date'] }}</span>
                                            <p class="mb-0 text-color small">{{ $history['event'] }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Sem histórico registrado.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div
                        class="card vet-vaccination-schedule__card border-0 d-none"
                        data-tab-context="#vetVaccinationTabVaccine"
                    >
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="vet-vaccination-schedule__section-title mb-1">Vacina selecionada</h6>
                                    <span class="text-muted small" id="vet-vaccination-summary-manufacturer">{{ $formActiveVaccine['manufacturer'] ?? '—' }}</span>
                                </div>
                                <span class="badge bg-light text-muted" id="vet-vaccination-summary-lot">{{ $formActiveVaccine['lot'] ?? '—' }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <span class="vet-vaccination-schedule__info-label">Validade</span>
                                    <p class="mb-0" id="vet-vaccination-summary-valid">{{ $formActiveVaccine['valid_until'] ?? '—' }}</p>
                                </div>
                                <div class="col-6">
                                    <span class="vet-vaccination-schedule__info-label">Via</span>
                                    <p class="mb-0" id="vet-vaccination-summary-route">{{ $formActiveVaccine['route'] ?? '—' }}</p>
                                </div>
                                <div class="col-6">
                                    <span class="vet-vaccination-schedule__info-label">Dose</span>
                                    <p class="mb-0" id="vet-vaccination-summary-dose">{{ $formActiveVaccine['dose'] ?? '—' }}</p>
                                </div>
                                <div class="col-6">
                                    <span class="vet-vaccination-schedule__info-label">Volume</span>
                                    <p class="mb-0" id="vet-vaccination-summary-volume">{{ $formActiveVaccine['volume'] ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="card vet-vaccination-schedule__card border-0 d-none"
                        data-tab-context="#vetVaccinationTabVaccine"
                    >
                        <div class="card-body">
                            <h6 class="vet-vaccination-schedule__section-title mb-3">Lote e armazenamento</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="vet-vaccination-schedule__stock-card">
                                        <span class="text-muted small">Doses disponíveis</span>
                                        <div class="vet-vaccination-schedule__stock-value" id="vet-vaccination-summary-stock">{{ $formActiveVaccine['stock']['available'] ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="vet-vaccination-schedule__stock-card">
                                        <span class="text-muted small">Reservas confirmadas</span>
                                        <div class="vet-vaccination-schedule__stock-value" id="vet-vaccination-summary-reserved">{{ $formActiveVaccine['stock']['reserved'] ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <span class="vet-vaccination-schedule__info-label">Faixa de temperatura</span>
                                    <p class="mb-0" id="vet-vaccination-summary-temperature">{{ $formActiveVaccine['temperature_range'] ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="card vet-vaccination-schedule__card border-0 d-none"
                        data-tab-context="#vetVaccinationTabSchedule"
                    >
                        <div class="card-body">
                            <h6 class="vet-vaccination-schedule__section-title mb-3">Slot selecionado</h6>
                            <div class="vet-vaccination-schedule__slot-card">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary" id="vet-vaccination-summary-date">{{ $formActiveDate['label'] ?? '—' }}</span>
                                    <span class="badge bg-primary text-white" id="vet-vaccination-summary-time">{{ $formActiveSlot['label'] ?? '—' }}</span>
                                </div>
                                <p class="text-muted small mb-0" id="vet-vaccination-summary-slot-note">{{ $formActiveDate['note'] ?? 'Selecione uma data para visualizar recomendações.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-form-page>
@endsection

@section('js')
    <script>
        window.vetVaccinationScheduleData = {
            patients: @json($patients),
            vaccines: @json($vaccines),
            availability: @json($availability),
            rooms: @json($rooms),
            veterinarians: @json($veterinarians),
            defaultValues: @json($defaultValues ?? []),
            defaultPatientPhoto: @json(asset('assets/images/users/avatar-1.jpg'))
        };
    </script>
    <script src="/js/vet/vacinacoes-agendar.js"></script>
@endsection
