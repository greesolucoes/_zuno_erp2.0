@extends('default.layout', ['title' => 'Registrar prontuário'])

@section('css')
    <style>
        #vet-record-form .card {
            border-radius: 16px;
        }

        #vet-record-form .card.shadow-sm {
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        #vet-record-form .card-header {
            background-color: #ffffff;
            border-bottom: 0;
        }

        #vet-record-form .vet-record-form__badge-soft-primary,
        #vet-record-form .vet-record-form__badge-soft-success,
        #vet-record-form .vet-record-form__badge-soft-info {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.85rem;
        }

        #vet-record-form .vet-record-form__badge-soft-primary {
            background-color: rgba(85, 110, 230, 0.18);
            color: #556ee6;
        }

        #vet-record-form .vet-record-form__badge-soft-success {
            background-color: rgba(52, 195, 143, 0.18);
            color: #1f6f54;
        }

        #vet-record-form .vet-record-form__badge-soft-info {
            background-color: rgba(85, 110, 230, 0.16);
            color: #556ee6;
        }

        #vet-record-form .vet-record-form__alert-item {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            padding: 0.75rem 1rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            background-color: #ffffff;
        }

        #vet-record-form .vet-record-form__alert-item.is-highlight {
            border-color: rgba(85, 110, 230, 0.3);
            background-color: rgba(85, 110, 230, 0.08);
        }

        #vet-record-form .vet-record-form__timeline {
            position: relative;
        }

        #vet-record-form .vet-record-form__timeline::before {
            content: '';
            position: absolute;
            top: 0.25rem;
            bottom: 0.25rem;
            left: 18px;
            width: 2px;
            background: rgba(85, 110, 230, 0.2);
        }

        #vet-record-form .vet-record-form__timeline-item {
            padding-left: 48px;
            position: relative;
        }

        #vet-record-form .vet-record-form__timeline-item::before {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            left: 12px;
            top: 0.35rem;
            background: #556ee6;
            box-shadow: 0 0 0 4px rgba(85, 110, 230, 0.2);
        }

        #vet-record-form .vet-record-form__timeline-time {
            font-size: 0.75rem;
            font-weight: 600;
            color: #556ee6;
        }

        #vet-record-form .vet-record-form__note-card {
            border-radius: 14px;
            border: 1px dashed rgba(85, 110, 230, 0.4);
            background: rgba(85, 110, 230, 0.08);
            padding: 1rem 1.25rem;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #vet-record-form .vet-record-form__note-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        }

        #vet-record-form .vet-record-form__attachment-card {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            padding: 0.75rem 1rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
            background-color: #ffffff;
        }

        body.vet-record-form__no-scroll {
            overflow: hidden;
        }

        .vet-record-form__assessment-card {
            transition: box-shadow 0.3s ease;
        }

        .vet-record-form__assessment-placeholder {
            display: none;
        }

        .vet-record-form__fullscreen-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            z-index: 1080;
        }

        .vet-record-form__fullscreen-overlay.is-active {
            display: flex;
        }

        .vet-record-form__fullscreen-wrapper {
            width: min(1024px, 100%);
            max-height: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .vet-record-form__fullscreen-toolbar {
            display: flex;
            justify-content: flex-end;
        }

        .vet-record-form__fullscreen-wrapper .vet-record-form__assessment-card {
            margin-bottom: 0;
            width: 100%;
            max-height: calc(100vh - 6rem);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.35);
            flex: 1 1 auto;
        }

        .vet-record-form__fullscreen-wrapper .vet-record-form__assessment-card .card-body {
            overflow-y: auto;
        }

        .vet-record-form__fullscreen-card-container {
            flex: 1 1 auto;
            overflow: hidden;
            display: flex;
        }

        .vet-record-form__fullscreen-card-container > .vet-record-form__assessment-card {
            flex: 1 1 auto;
        }

        #vet-record-form .vet-record-form__assessment-card .vet-record-form__fullscreen-trigger.is-active {
            background-color: #556ee6;
            color: #ffffff;
            border-color: #556ee6;
        }

        #vet-record-form .vet-record-form__patient-photo {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            object-fit: cover;
        }

        #vet-record-form .vet-record-form__floating-tag {
            border-radius: 999px;
            background: rgba(85, 110, 230, 0.12);
            color: #556ee6;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            font-size: 0.75rem;
        }

        #vet-record-form .vet-record-form__tab-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            box-shadow: 0 12px 24px rgba(22, 22, 107, 0.08);
            padding: 0.75rem;
        }

        #vet-record-form .vet-record-form__tab-nav .nav-link {
            border: none;
            border-radius: 12px;
            background: transparent;
            color: #495057;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            transition: all 0.2s ease;
            flex: 1 1 220px;
        }

        #vet-record-form .vet-record-form__tab-nav .nav-link i {
            font-size: 1.1rem;
        }

        #vet-record-form .vet-record-form__tab-nav .nav-link:hover,
        #vet-record-form .vet-record-form__tab-nav .nav-link:focus {
            background-color: rgba(114, 59, 233, 0.18);
            color: #3a1e4b !important;
        }

        #vet-record-form .vet-record-form__tab-nav .nav-link.active {
            background-color: #3a1e4b;
            color: #fff !important;
            box-shadow: 0 12px 30px rgba(119, 85, 230, 0.25);
        }

        #vet-record-form .vet-record-form__tab-pane {
            animation: vet-record-form-fade-in 0.3s ease;
        }

        @keyframes vet-record-form-fade-in {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #vet-record-form .vet-record-form__checklist-item {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            padding: 0.75rem 1rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            background-color: #ffffff;
        }

        #vet-record-form .vet-record-form__checklist-item.is-completed {
            border-color: rgba(52, 195, 143, 0.3);
            background-color: rgba(52, 195, 143, 0.08);
        }

        #vet-record-form .vet-record-form__skeleton {
            background: linear-gradient(90deg, rgba(85, 110, 230, 0.08) 25%, rgba(85, 110, 230, 0.16) 37%, rgba(85, 110, 230, 0.08) 63%);
            background-size: 400% 100%;
            animation: vet-record-form-skeleton 1.5s ease infinite;
        }

        @keyframes vet-record-form-skeleton {
            0% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0 50%;
            }
        }
    </style>

@endsection

@php
    $isEditMode = isset($record);
    $recordId = $record->id ?? ($prefill['id'] ?? null);
    $formMode = $isEditMode ? 'edit' : 'create';
    $storeUrl = route('vet.records.store');
    $updateUrl = $isEditMode ? route('vet.records.update', $record) : '';
    $editUrlTemplate = route('vet.records.edit', ['prontuario' => '__RECORD__']);
    $attendanceId = $prefill['attendance']['id'] ?? ($record->atendimento_id ?? null);
    $currentStatus = $record->status ?? ($prefill['status'] ?? null);

    $patientSnapshot = $prefill['patient_snapshot'] ?? null;
    $defaultPatientPhoto = asset('assets/images/users/avatar-1.jpg');
    $initialPatientPhoto = $patientSnapshot['photo']
        ?? $patientSnapshot['photo_url']
        ?? $defaultPatientPhoto;

    $initialPatientName = $patientSnapshot['name'] ?? 'Selecione um paciente';
    $initialPatientMetaParts = $patientSnapshot
        ? collect([
            $patientSnapshot['species'] ?? null,
            $patientSnapshot['breed'] ?? null,
            $patientSnapshot['age'] ?? null,
        ])
        : collect();
    $initialPatientMeta = $initialPatientMetaParts->filter()->implode(' • ');
    if ($initialPatientMeta === '') {
        $initialPatientMeta = 'Informações serão exibidas após a seleção.';
    }

    $initialPatientSummaryText = $patientSnapshot['summary'] ?? null;
    if (! $initialPatientSummaryText && ! empty($patientSnapshot['recent_notes'][0]['content'])) {
        $initialPatientSummaryText = $patientSnapshot['recent_notes'][0]['content'];
    }
    $initialPatientSummaryText = $initialPatientSummaryText ?: 'Sem observações clínicas registradas.';

    $initialPatientMetrics = [
        'weight' => $patientSnapshot['weight'] ?? '—',
        'sex' => $patientSnapshot['sex'] ?? ($patientSnapshot['gender'] ?? '—'),
        'birth_date' => $patientSnapshot['birth_date'] ?? '—',
        'last_visit' => $patientSnapshot['last_visit'] ?? '—',
        'size' => $patientSnapshot['size'] ?? '—',
        'origin' => $patientSnapshot['origin'] ?? '—',
        'microchip' => $patientSnapshot['microchip'] ?? '—',
        'pedigree' => $patientSnapshot['pedigree'] ?? '—',
    ];

    $initialPatientTags = collect($patientSnapshot['tags'] ?? [])->filter()->values();

    $initialAlerts = collect($patientSnapshot['alerts'] ?? [])->filter()->values();

    $initialConditionsList = collect($patientSnapshot['allergies'] ?? [])
        ->filter()
        ->map(fn ($item) => 'Alergia: ' . $item)
        ->merge($patientSnapshot['chronic_conditions'] ?? [])
        ->filter()
        ->values();

    $initialMedications = collect($patientSnapshot['medications'] ?? [])
        ->filter(function ($medication) {
            if (is_array($medication)) {
                return ! empty($medication['name']) || ! empty($medication['schedule']);
            }

            return (string) $medication !== '';
        })
        ->values();

    $initialContactsCollection = collect($patientSnapshot['tutor_contacts'] ?? [])
        ->filter(function ($contact) {
            if (is_array($contact)) {
                return ! empty($contact['value']);
            }

            return (string) $contact !== '';
        })
        ->values();

    $formatContact = static function ($contact) {
        if (is_array($contact)) {
            $type = $contact['type'] ?? null;
            $value = $contact['value'] ?? null;

            if ($type && $value) {
                return $type . ': ' . $value;
            }

            return $value ?? $type ?? null;
        }

        return $contact ?: null;
    };

    $initialTutorContact = $patientSnapshot['contact'] ?? null;
    if (! $initialTutorContact && $patientSnapshot) {
        $initialTutorContact = $formatContact($patientSnapshot['primary_contact'] ?? null);
    }
    if (! $initialTutorContact && $initialContactsCollection->isNotEmpty()) {
        $initialTutorContact = $formatContact($initialContactsCollection->first());
    }
    $initialTutorContact = $initialTutorContact ?: '—';

    $initialTutorName = data_get($patientSnapshot, 'tutor', '—');
    $initialTutorDocument = data_get($patientSnapshot, 'tutor_document', '—');
    $initialTutorEmail = data_get($patientSnapshot, 'email', '—');
    $initialTutorAddress = data_get($patientSnapshot, 'tutor_address', '—');

    $initialPatientDetails = collect([
        ['label' => 'Espécie', 'value' => $patientSnapshot['species'] ?? null],
        ['label' => 'Raça', 'value' => $patientSnapshot['breed'] ?? null],
        ['label' => 'Sexo', 'value' => $patientSnapshot['sex'] ?? ($patientSnapshot['gender'] ?? null)],
        ['label' => 'Idade', 'value' => $patientSnapshot['age'] ?? null],
        ['label' => 'Peso', 'value' => $patientSnapshot['weight'] ?? null],
        ['label' => 'Última visita', 'value' => $patientSnapshot['last_visit'] ?? null],
        ['label' => 'Próximo retorno', 'value' => $patientSnapshot['next_follow_up'] ?? null],
        ['label' => 'Tutor(a)', 'value' => $patientSnapshot['tutor'] ?? null],
        ['label' => 'Contato principal', 'value' => $initialTutorContact !== '—' ? $initialTutorContact : null],
    ])->filter(function ($detail) {
        return isset($detail['value']) && $detail['value'] !== null && $detail['value'] !== '';
    })->values();

    $recordAttachmentsCollection = collect($attachments ?? [])
        ->filter(fn ($item) => is_array($item))
        ->map(function ($item, $index) {
            $item['id'] = $item['id'] ?? ($item['path'] ?? ('attachment-' . $index));

            if (empty($item['type']) && ! empty($item['extension'])) {
                $item['type'] = strtoupper((string) $item['extension']);
            }

            return $item;
        })
        ->values();
    $hasRecordAttachments = $recordAttachmentsCollection->isNotEmpty();
@endphp

@section('content')
    <div
        id="vet-record-form"
        class="container-fluid px-3 px-lg-4"
        data-mode="{{ $formMode }}"
        data-record-id="{{ $recordId ?? '' }}"
        data-store-url="{{ $storeUrl }}"
        data-update-url="{{ $updateUrl }}"
        data-edit-url-template="{{ $editUrlTemplate }}"
        data-attendance-id="{{ $attendanceId ?? '' }}"
        data-current-status="{{ $currentStatus ?? '' }}"
        data-patients='@json($patients)'
        data-veterinarians='@json($veterinarians)'
        data-slots='@json($appointmentSlots)'
        data-assessment-models='@json($assessmentModels)'
        data-assessment-model-fetch-url="{{ route('vet.records.assessment-models.fetch', ['modeloAvaliacao' => '__MODEL__']) }}"
        data-checklists='@json($checklists)'
        data-reminders='@json($reminders)'
        data-attachments='@json($recordAttachmentsCollection)'
        data-attachments-upload-url="{{ route('vet.records.attachments.store') }}"
        data-attachments-remove-url="{{ route('vet.records.attachments.remove') }}"
        data-attachments-max-items="8"
        data-attachments-max-size-bytes="{{ 10 * 1024 * 1024 }}"
        data-communications='@json($communicationTemplates)'
        data-quick-notes='@json($quickNotes)'
        data-evolution='@json($evolutionTimeline)'
        data-prefill='@json($prefill)'
        data-default-patient-photo="{{ $defaultPatientPhoto }}"
    >
        <div id="recordFormFeedback" class="mb-3"></div>
        <div class="d-flex flex-wrap align-items-start align-items-lg-center justify-content-between gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <h2 class="text-color mb-1 mb-lg-0">Registrar Consulta</h2>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                <a href="{{ route('vet.records.index', ['page' => request()->query('page', 1)]) }}"
                    class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                    <i class="ri-arrow-left-double-fill"></i>Voltar
                </a>
            </div>
        </div>

        @if (!empty($attendanceContext))
            <div class="alert alert-info d-flex align-items-start gap-3 mb-4 vet-record-form__attendance-alert">
                <i class="ri-calendar-check-line fs-4 text-info mt-1"></i>
                <div>
                    <strong>Atendimento vinculado:</strong>
                    <a href="{{ $attendanceContext['url'] }}" class="text-decoration-none">{{ $attendanceContext['code'] }}</a>
                    <div class="small text-muted mt-1">
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

        <div class="row g-4">
            <div class="col-12">
                <div class="mb-4">
                    <ul class="nav nav-pills gap-2 vet-record-form__tab-nav" id="recordFormTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-overview-tab" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab" aria-controls="tab-overview" aria-selected="true">
                                <i class="ri-hospital-line"></i>
                                Atendimento
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-assessment-tab" data-bs-toggle="pill" data-bs-target="#tab-assessment" type="button" role="tab" aria-controls="tab-assessment" aria-selected="false">
                                <i class="ri-stethoscope-line"></i>
                                Avaliação clínica
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-documentation-tab" data-bs-toggle="pill" data-bs-target="#tab-documentation" type="button" role="tab" aria-controls="tab-documentation" aria-selected="false">
                                <i class="ri-folder-2-line"></i>
                                Documentação
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-xl-8" id="recordMainColumn">
                <div class="tab-content" id="recordTabContent">
                    <div class="tab-pane fade show active vet-record-form__tab-pane" id="tab-overview" role="tabpanel" aria-labelledby="tab-overview-tab">
                        <div class="d-flex flex-column gap-4">
                            <div class="card shadow-sm vet-record-form__card">
                                <div class="card-header bg-white border-0 pb-0 vet-record-form__card-header">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <h5 class="mb-1 text-color">Paciente e atendimento</h5>
                                            <p class="text-muted mb-0 small">Selecione o paciente e configure as informações iniciais da evolução.</p>
                                        </div>
                                        <span class="badge bg-soft-primary text-primary vet-record-form__badge-soft-primary">
                                            <i class="ri-hospital-line me-1"></i>Informações gerais
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <label for="recordPatientSelect" class="form-label fw-semibold text-secondary">Paciente</label>
                                            <select id="recordPatientSelect" class="form-select select2"
                                                data-placeholder="Selecione o paciente" data-allow-clear="true" disabled>
                                                <option value="">Selecione o paciente</option>
                                                @foreach ($patients as $patient)
                                                    <option value="{{ $patient['id'] }}">
                                                        {{ $patient['name'] }} • {{ $patient['species'] }} • Tutor(a): {{ $patient['tutor'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="recordVeterinarianSelect" class="form-label fw-semibold text-secondary">Profissional responsável</label>
                                            <select id="recordVeterinarianSelect" class="form-select select2"
                                                data-placeholder="Selecione o veterinário" data-allow-clear="true">
                                                <option value="">Selecione o veterinário</option>
                                                @foreach ($veterinarians as $veterinarian)
                                                    <option value="{{ $veterinarian['id'] }}" data-specialty="{{ $veterinarian['specialty'] }}">
                                                        {{ $veterinarian['name'] }} • {{ $veterinarian['specialty'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="recordTypeSelect" class="form-label fw-semibold text-secondary">Tipo de atendimento</label>
                                            <select id="recordTypeSelect" class="form-select">
                                                <option value="">Selecione o tipo</option>
                                                <option value="consulta">Consulta geral</option>
                                                <option value="retorno">Retorno programado</option>
                                                <option value="pos-operatorio">Pós-operatório</option>
                                                <option value="emergencia">Emergência</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="recordSlotSelect" class="form-label fw-semibold text-secondary">Data e horário</label>
                                            <select id="recordSlotSelect" class="form-select">
                                                <option value="">Selecione o horário</option>
                                                @foreach ($appointmentSlots as $slot)
                                                    <option value="{{ $slot['value'] }}">{{ $slot['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="recordHighlightsInput" class="form-label fw-semibold text-secondary">Resumo rápido</label>
                                            <textarea
                                                id="recordHighlightsInput"
                                                class="form-control rich-text"
                                                placeholder="Ex.: Pós-operatório de retirada de nódulo - evolução estável com leve edema."
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade vet-record-form__tab-pane" id="tab-assessment" role="tabpanel" aria-labelledby="tab-assessment-tab">
                        <div class="d-flex flex-column gap-4">
                            <div class="card shadow-sm vet-record-form__card vet-record-form__assessment-card">
                                <div class="card-header bg-white border-0 pb-0 vet-record-form__card-header d-flex align-items-start justify-content-between">
                                    <div>
                                        <h5 class="mb-1 text-color" id="recordAssessmentTitle">Avaliação clínica</h5>
                                        <p class="text-muted mb-0 small">Selecione um modelo personalizado para registrar a evolução.</p>
                                    </div>
                                    <div class="text-end d-flex flex-column align-items-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 vet-record-form__fullscreen-trigger" id="recordAssessmentFullscreenToggle" aria-pressed="false" aria-expanded="false">
                                            <i class="ri-fullscreen-line" aria-hidden="true"></i>
                                            <span class="vet-record-form__fullscreen-label">Modo tela grande</span>
                                        </button>
                                        <div class="w-100">
                                            <label for="recordTemplateSelect" class="form-label fw-semibold text-secondary mb-1">Modelo de avaliação</label>
                                            <select id="recordTemplateSelect" class="form-select">
                                                <option value="">Selecionar modelo</option>
                                                @foreach ($assessmentModels as $model)
                                                    <option value="{{ $model['id'] }}" data-category="{{ $model['category_label'] }}">
                                                        {{ $model['title'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted small d-block mt-1">Os campos configurados serão carregados automaticamente abaixo.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-soft-primary d-flex gap-3 align-items-start" id="recordTemplateSummary">
                                        <i class="ri-magic-line fs-3 text-primary"></i>
                                        <div>
                                            @if (empty($assessmentModels))
                                                <h6 class="fw-semibold text-primary mb-1">Cadastre um modelo de avaliação para começar.</h6>
                                                <p class="mb-0 text-muted">
                                                    Crie modelos em
                                                    <a class="text-primary fw-semibold" href="{{ route('vet.assessment-models.index') }}">Modelos de avaliação</a>
                                                    e utilize-os para agilizar o registro clínico.
                                                </p>
                                            @else
                                                <h6 class="fw-semibold text-primary mb-1">Selecione um modelo para carregar os campos clínicos.</h6>
                                                <p class="mb-0 text-muted">A estrutura configurada será exibida abaixo e você poderá editar as informações antes de salvar.</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div id="assessmentModelFields" class="mt-4">
                                        <div id="assessmentModelEmptyState" class="text-center text-muted py-5">
                                            <i class="ri-file-list-3-line text-primary fs-1 mb-3 d-block"></i>
                                            <p class="fw-semibold text-secondary mb-1">Nenhum modelo aplicado.</p>
                                            <p class="mb-0">Escolha um modelo de avaliação para carregar o formulário clínico personalizado.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm vet-record-form__card">
                                <div class="card-header bg-white border-0 pb-0 vet-record-form__card-header d-flex align-items-start justify-content-between">
                                    <div>
                                        <h5 class="mb-1 text-color">Checklist clínico</h5>
                                        <p class="text-muted mb-0 small">Garanta que todas as etapas do atendimento foram registradas.</p>
                                    </div>
                                    <span class="badge bg-soft-success text-success vet-record-form__badge-soft-success">
                                        <i class="ri-task-line me-1"></i>Processos assistidos
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4" id="recordChecklist"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade vet-record-form__tab-pane" id="tab-documentation" role="tabpanel" aria-labelledby="tab-documentation-tab">
                        <div class="d-flex flex-column gap-4">
                            <div class="card shadow-sm vet-record-form__card">
                                <div class="card-header bg-white border-0 pb-0 vet-record-form__card-header d-flex align-items-start justify-content-between">
                                    <div>
                                        <h5 class="mb-1 text-color">Documentação e anexos</h5>
                                        <p class="text-muted mb-0 small">Anexe exames, laudos e fotos relacionadas ao atendimento.</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <input
                                            type="file"
                                            class="d-none"
                                            id="recordAttachmentInput"
                                            multiple
                                            accept="application/pdf,image/*,.doc,.docx,.xls,.xlsx,.txt"
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                                            id="recordAttachmentAdd"
                                        >
                                            <i class="ri-upload-2-line"></i>
                                            Adicionar arquivo
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3" id="recordAttachmentList"></div>
                                    <div class="text-muted small mt-2 {{ $hasRecordAttachments ? 'd-none' : '' }}" id="recordAttachmentEmpty">
                                        Nenhum arquivo anexado até o momento.
                                    </div>
                                    <div class="alert alert-soft-info d-flex align-items-center gap-3 mt-4">
                                        <i class="ri-information-line fs-4 text-info"></i>
                                        <div>
                                            <h6 class="fw-semibold text-info mb-1">Centralize todos os documentos no prontuário.</h6>
                                            <p class="mb-0 text-muted">Arquivos adicionados aqui ficam disponíveis para toda a equipe clínica e para a área do tutor.</p>
                                        </div>
                                    </div>
                                    <div id="recordAttachmentInputs" class="d-none">
                                        @foreach ($recordAttachmentsCollection as $attachment)
                                            <input type="hidden" name="anexos[]" value='@json($attachment)'>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4" id="recordSidebarColumn">
                <div
                    class="card shadow-sm vet-record-form__summary-card mb-4"
                    data-tab-context="#tab-overview"
                >
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1 text-color">Resumo do paciente</h5>
                        <p class="text-muted mb-0 small">Informações rápidas sobre o paciente e o tutor.</p>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img
                                src="{{ $initialPatientPhoto }}"
                                alt="Foto do paciente"
                                class="rounded-circle mb-3"
                                style="width: 96px; height: 96px; object-fit: cover;"
                                id="recordPatientPhoto"
                                data-default-photo="{{ $defaultPatientPhoto }}"
                            >
                            <h5 class="mb-1" id="recordPatientName">{{ $initialPatientName }}</h5>
                            <p class="text-muted mb-0 small" id="recordPatientMeta">{{ $initialPatientMeta }}</p>
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-2 mb-3" id="recordPatientTags">
                            @foreach ($initialPatientTags as $tag)
                                <span class="badge bg-light text-secondary">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Peso</span>
                                    <span id="recordPatientSummaryWeight" class="fw-semibold text-color">{{ $initialPatientMetrics['weight'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Sexo</span>
                                    <span id="recordPatientSummarySex" class="fw-semibold text-color">{{ $initialPatientMetrics['sex'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Nascimento</span>
                                    <span id="recordPatientSummaryBirthDate" class="fw-semibold text-color">{{ $initialPatientMetrics['birth_date'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Última visita</span>
                                    <span id="recordPatientSummaryLastVisit" class="fw-semibold text-color">{{ $initialPatientMetrics['last_visit'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Porte</span>
                                    <span id="recordPatientSummarySize" class="fw-semibold text-color">{{ $initialPatientMetrics['size'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Origem</span>
                                    <span id="recordPatientSummaryOrigin" class="fw-semibold text-color">{{ $initialPatientMetrics['origin'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Microchip</span>
                                    <span id="recordPatientSummaryMicrochip" class="fw-semibold text-color">{{ $initialPatientMetrics['microchip'] }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <span class="text-muted small d-block">Pedigree</span>
                                    <span id="recordPatientSummaryPedigree" class="fw-semibold text-color">{{ $initialPatientMetrics['pedigree'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-light rounded-4 p-3 mb-3">
                            <h6 class="text-color fs-6 mb-2">Observações clínicas</h6>
                            <p id="recordPatientSummary" class="text-muted small mb-0">{{ $initialPatientSummaryText }}</p>
                        </div>
                        <div class="bg-light rounded-4 p-3">
                            <h6 class="text-color fs-6 mb-2">Tutor responsável</h6>
                            <p id="recordTutorSummaryName" class="fw-semibold mb-1">{{ $initialTutorName }}</p>
                            <p id="recordTutorSummaryDocument" class="text-muted small mb-1">{{ $initialTutorDocument }}</p>
                            <p id="recordTutorSummaryContacts" class="text-muted small mb-1">{{ $initialTutorContact }}</p>
                            <p id="recordTutorSummaryEmail" class="text-muted small mb-1">{{ $initialTutorEmail }}</p>
                            <p id="recordTutorSummaryAddress" class="text-muted small mb-0">{{ $initialTutorAddress }}</p>
                        </div>
                    </div>
                </div>

                <div
                    class="card shadow-sm vet-record-form__card mb-4 d-none"
                    data-tab-context="#tab-documentation"
                >
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 text-color">Linha do tempo</h5>
                            <span class="vet-record-form__floating-tag">Atualizada em tempo real</span>
                        </div>
                        <div class="position-relative vet-record-form__timeline" id="recordTimeline"></div>
                    </div>
                </div>

                <div
                    class="card shadow-sm vet-record-form__card d-none"
                    data-tab-context="#tab-communication"
                >
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 text-color">Notas rápidas</h5>
                            <span class="vet-record-form__floating-tag">Clique para inserir</span>
                        </div>
                        <div class="row g-3" id="recordQuickNotes"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 d-flex align-items-center justify-content-end gap-2 flex-wrap">
                <button type="button" class="btn btn-success px-5" id="recordSubmitButton">
                    Salvar
                </button>
            </div>
        </div>
    </div>
@endsection


@section('js')
    <script src="/tinymce/tinymce.min.js"></script>
    <script src="{{ asset('js/vet/prontuarios-form.js') }}"></script>

@endsection