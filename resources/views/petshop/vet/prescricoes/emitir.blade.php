@extends('default.layout', ['title' => 'Emitir prescrição veterinária'])

@section('css')
    <style>
        .vet-prescricao-form__card {
            border-radius: 18px;
            border: 1px solid rgba(22, 22, 107, 0.06);
            box-shadow: 0 18px 36px rgba(22, 22, 107, 0.08);
        }

        .vet-prescricao-form__card--soft {
            background: linear-gradient(180deg, rgba(85, 110, 230, 0.08) 0%, rgba(255, 255, 255, 0.92) 100%);
        }

        .vet-prescricao-form__section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #16166b;
        }

        .vet-prescricao-form__section-subtitle {
            color: #6c757d;
            font-size: .825rem;
        }

        .vet-prescricao-form__info-label {
            text-transform: uppercase;
            letter-spacing: .05em;
            font-size: .7rem;
            color: rgba(22, 22, 107, 0.55);
        }

        .vet-prescricao-form__tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(22, 22, 107, 0.08);
            color: #16166b;
            font-size: .75rem;
            font-weight: 600;
        }

        .vet-prescricao-form__tag--active {
            background: #556ee6;
            color: #fff;
        }

        .vet-prescricao-form__tag-remove {
            border: none;
            background: transparent;
            color: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin-left: .35rem;
            line-height: 1;
            cursor: pointer;
        }

        .vet-prescricao-form__tag-remove:hover,
        .vet-prescricao-form__tag-remove:focus {
            color: #dc3545;
        }

        #vetPrescriptionChannels .vet-prescricao-form__tag {
            cursor: pointer;
        }

        .vet-prescricao-form__medication-item {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 12px 26px rgba(22, 22, 107, 0.08);
        }

        .vet-prescricao-form__medication-item:hover {
            border-color: rgba(85, 110, 230, 0.35);
            box-shadow: 0 18px 40px rgba(22, 22, 107, 0.12);
        }

        .vet-prescricao-form__attachment-card {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .vet-prescricao-form__attachment-card:hover {
            border-color: rgba(85, 110, 230, 0.35);
            box-shadow: 0 18px 36px rgba(22, 22, 107, 0.12);
        }

        .vet-prescricao-form__attachment-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .vet-prescricao-form__pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .65rem;
            border-radius: 999px;
            background: rgba(85, 110, 230, 0.12);
            color: #556ee6;
            font-size: .75rem;
            font-weight: 600;
        }

        .vet-prescricao-form__indicator-card {
            border-radius: 16px;
            padding: 1.25rem;
            background: #fff;
            border: 1px solid rgba(22, 22, 107, 0.08);
            box-shadow: 0 14px 30px rgba(22, 22, 107, 0.1);
        }

        .vet-prescricao-form__indicator-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(85, 110, 230, 0.12);
            color: #556ee6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .vet-prescricao-form__timeline::before {
            content: '';
            position: absolute;
            top: 8px;
            bottom: 8px;
            left: 14px;
            width: 2px;
            background: rgba(85, 110, 230, 0.25);
        }

        .vet-prescricao-form__timeline-item {
            position: relative;
            padding-left: 48px;
        }

        .vet-prescricao-form__timeline-item::before {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            left: 8px;
            top: 4px;
            background: #556ee6;
            box-shadow: 0 0 0 4px rgba(85, 110, 230, 0.2);
        }

        .vet-prescricao-form__checklist-item {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            padding: .75rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .vet-prescricao-form__alert {
            border-radius: 14px;
            padding: .9rem;
            display: flex;
            gap: .75rem;
            align-items: flex-start;
        }

        .vet-prescricao-form__alert-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .vet-prescricao-form__subtle-input {
            border-radius: 12px;
            border: 1px solid rgba(22, 22, 107, 0.12);
        }

        .vet-prescricao-form__subtle-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(85, 110, 230, 0.18);
            border-color: rgba(85, 110, 230, 0.55);
        }

        .vet-prescricao-form__summary-card {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 12px 26px rgba(22, 22, 107, 0.06);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .vet-prescricao-form__summary-card:hover {
            border-color: rgba(85, 110, 230, 0.25);
            box-shadow: 0 18px 40px rgba(22, 22, 107, 0.12);
        }

        .vet-prescricao-form__summary-text {
            color: #6c757d;
            font-size: .8rem;
            margin-bottom: 0;
        }

        .vet-prescricao-form__summary-button {
            width: 40px;
            
            padding: 0;
            font-size: 1.25rem;
        }

        .vet-prescricao-form__modal-description {
            color: #6c757d;
            font-size: .8rem;
        }

        .vet-prescricao-form__modal-add-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            padding: 0;
        }

        .vet-prescricao-form__modal-add-button i {
            pointer-events: none;
        }

        .vet-prescricao-form__list-placeholder {
            border-radius: 14px;
            border: 1px dashed rgba(22, 22, 107, 0.18);
            padding: 1rem;
            text-align: center;
            color: #6c757d;
        }

        .vet-prescricao-form__tabs {
            display: flex;
            flex-wrap: wrap;
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            box-shadow: 0 12px 24px rgba(22, 22, 107, 0.08);
            padding: .75rem;
        }

        .vet-prescricao-form__tabs .nav-link {
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
            flex: 1 1 200px;
        }

        .vet-prescricao-form__tabs .nav-link i {
            font-size: 1.1rem;
        }

        .vet-prescricao-form__tabs .nav-link:hover {
            background-color: rgba(114, 59, 233, 0.18);
            color: #3a1e4b !important;
        }

        .vet-prescricao-form__tabs .nav-link.active {
            background-color: #3a1e4b;
            color: #fff !important;
            box-shadow: 0 12px 30px rgba(119, 85, 230, 0.25);
        }

        .vet-prescricao-form__dynamic-field {
            border: 1px solid rgba(85, 110, 230, 0.15);
            border-radius: 14px;
            background: #f9f9fb;
            padding: 1.25rem;
        }

        .vet-prescricao-form__dynamic-field .badge {
            font-weight: 500;
        }

        .vet-prescricao-form__dynamic-field .form-control,
        .vet-prescricao-form__dynamic-field .form-select {
            background: #fff;
        }

        .vet-prescricao-form__rich-text-editor {
            min-height: 200px;
            border: 1px solid rgba(85, 110, 230, 0.2);
            border-radius: 12px;
            background: #fff;
            width: 100%;
            display: block;
            padding: 1rem;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .vet-prescricao-form__rich-text-editor:focus {
            outline: none;
            border-color: #556ee6;
            box-shadow: 0 0 0 3px rgba(85, 110, 230, 0.15);
        }

        .tox.tox-tinymce {
            border: 1px solid rgba(85, 110, 230, 0.2);
            border-radius: 12px;
            box-shadow: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .tox.tox-tinymce:focus-within {
            border-color: #556ee6;
            box-shadow: 0 0 0 3px rgba(85, 110, 230, 0.15);
        }

        .tox .tox-toolbar, .tox .tox-toolbar__primary {
            border-bottom: 1px solid rgba(85, 110, 230, 0.1);
        }

        .tox .tox-toolbar__primary {
            background: #f9f9fb;
        }
    </style>
@endsection

@section('content')
    @php
        $isEditing = (bool) ($isEditing ?? false);
        $editingPrescription = is_array($editingPrescription ?? null) ? $editingPrescription : null;

        $initialPrescription = $editingPrescription ?? [];

        $initialPatientId = old('patient_id')
            ?? ($prefilledPatientId ?? ($initialPrescription['patient_id'] ?? request()->input('patient_id')));
        $initialVeterinarianId = old('veterinarian_id')
            ?? ($prefilledVeterinarianId ?? ($initialPrescription['veterinarian_id'] ?? request()->input('veterinarian_id')));

        $initialPatientId = $initialPatientId !== null && $initialPatientId !== '' ? (string) $initialPatientId : null;
        $initialVeterinarianId = $initialVeterinarianId !== null && $initialVeterinarianId !== '' ? (string) $initialVeterinarianId : null;

        $availableTemplates = $templates ?? [];

        $initialPatient = collect($patients ?? [])->firstWhere('id', $initialPatientId) ?? [];
        $initialVeterinarian = collect($veterinarians ?? [])->firstWhere('id', $initialVeterinarianId) ?? [];
        $medicationsCatalog = $medicationsCatalog ?? [];

        if (! empty($initialPrescription)) {
            if (array_key_exists('notes', $initialPrescription)) {
                $initialPatient['notes'] = $initialPrescription['notes'];
            }

            if (array_key_exists('allergies', $initialPrescription)) {
                $initialPatient['allergies'] = $initialPrescription['allergies'];
            }

            if (array_key_exists('conditions', $initialPrescription)) {
                $initialPatient['conditions'] = $initialPrescription['conditions'];
            }
        }

        $initialTemplateId = old('template_id')
            ?? ($initialPrescription['template_id'] ?? request()->input('template_id'));
        $initialTemplateId = $initialTemplateId !== null && $initialTemplateId !== '' ? (string) $initialTemplateId : null;

        $initialTemplate = null;

        if ($initialTemplateId !== null) {
            $initialTemplate = collect($availableTemplates)->firstWhere('id', $initialTemplateId);

            if (! $initialTemplate) {
                $initialTemplateId = null;
            }
        }

        $initialTemplate = $initialTemplate ?? [];
        $defaultPatientPhoto = asset('assets/images/users/avatar-1.jpg');
        $initialPatientPhoto = $initialPatient['photo_url'] ?? $defaultPatientPhoto;
        $initialPatientMeta = collect([
            $initialPatient['species'] ?? null,
            $initialPatient['breed'] ?? null,
            $initialPatient['age'] ?? null,
        ])->filter()->implode(' • ');
        $initialPatientNotes = trim((string) ($initialPatient['notes'] ?? ''));
        $initialPatientNotes = $initialPatientNotes !== '' ? $initialPatientNotes : null;
        $attendanceContext = $attendanceContext ?? null;

        $jsonEncode = static fn ($value) => json_encode($value ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $initialAllergiesJson = old('allergies', $jsonEncode($initialPrescription['allergies'] ?? []));
        $initialConditionsJson = old('conditions', $jsonEncode($initialPrescription['conditions'] ?? []));
        $initialMedicationsJson = old('medications', $jsonEncode($initialPrescription['medications'] ?? []));
        $initialChannelsJson = old('channels', $jsonEncode($initialPrescription['channels'] ?? []));

        $initialNotes = old('notes', $initialPrescription['notes'] ?? ($initialPatient['notes'] ?? ''));
        $initialDiagnosis = old('diagnosis', $initialPrescription['diagnosis'] ?? ($initialTemplate['diagnosis'] ?? ''));
        $initialSummary = old('summary', $initialPrescription['summary'] ?? ($initialTemplate['summary'] ?? ''));
        $initialGuidelines = old('guidelines', $initialPrescription['guidelines'] ?? '');

        $initialMedicationsList = $initialPrescription['medications'] ?? ($initialTemplate['medications'] ?? []);
    @endphp

<x-form-page
    title="Emitir prescrição veterinária"
    heading="{{ $isEditing ? 'Editar Prescrição' : 'Emitir Prescrição' }}"
    :back-url="route('vet.prescriptions.index', ['page' => request()->query('page', 1)])"
>
    <div class="vet-prescricao-form">

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

        <form
            id="vetPrescriptionForm"
            class="row g-4"
            action="{{ $formAction ?? route('vet.prescriptions.store') }}"
            method="POST"
            autocomplete="off"
            enctype="multipart/form-data"
        >
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif
            <input type="hidden" name="atendimento_id" value="{{ old('atendimento_id', $atendimentoId) }}">
            <input type="hidden" name="channels" id="vetPrescriptionChannelsField" value="{{ $initialChannelsJson }}">

            <div class="col-12">
                <ul class="nav nav-pills gap-2 vet-prescricao-form__tabs" id="vetPrescriptionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link active"
                            id="vetPrescriptionTabPatient-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#vetPrescriptionTabPatient"
                            type="button"
                            role="tab"
                            aria-controls="vetPrescriptionTabPatient"
                            aria-selected="true"
                        >
                            <i class="ri-user-heart-line"></i>
                            <span>Dados do paciente</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            id="vetPrescriptionTabPlan-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#vetPrescriptionTabPlan"
                            type="button"
                            role="tab"
                            aria-controls="vetPrescriptionTabPlan"
                            aria-selected="false"
                        >
                            <i class="ri-medicine-bottle-line"></i>
                            <span>Plano terapêutico</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            id="vetPrescriptionTabMedications-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#vetPrescriptionTabMedications"
                            type="button"
                            role="tab"
                            aria-controls="vetPrescriptionTabMedications"
                            aria-selected="false"
                        >
                            <i class="ri-capsule-line"></i>
                            <span>Medicações</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            id="vetPrescriptionTabGuidelines-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#vetPrescriptionTabGuidelines"
                            type="button"
                            role="tab"
                            aria-controls="vetPrescriptionTabGuidelines"
                            aria-selected="false"
                        >
                            <i class="ri-route-line"></i>
                            <span>Orientações</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            id="vetPrescriptionTabDocuments-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#vetPrescriptionTabDocuments"
                            type="button"
                            role="tab"
                            aria-controls="vetPrescriptionTabDocuments"
                            aria-selected="false"
                        >
                            <i class="ri-attachment-2"></i>
                            <span>Documentação</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="col-12">
                <div class="tab-content mt-3" id="vetPrescriptionTabsContent">
                    <div
                        class="tab-pane fade show active"
                        id="vetPrescriptionTabPatient"
                        role="tabpanel"
                        aria-labelledby="vetPrescriptionTabPatient-tab"
                    >
                        <div class="row g-4">
                            <div class="col-12 col-xl-8">
                                <div class="card vet-prescricao-form__card mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-1">Paciente e avaliação clínica</h5>
                                                <p class="text-muted small mb-0">Confirme informações essenciais antes de definir o tratamento.</p>
                                            </div>
                                            @if (! empty($initialPatient))
                                                <span class="badge bg-primary-subtle text-primary">Paciente selecionado</span>
                                            @endif
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="vetPrescriptionPatientSelect" class="form-label fw-semibold text-secondary">Paciente</label>
                                                <select
                                                    id="vetPrescriptionPatientSelect"
                                                    name="patient_id"
                                                    class="form-select select2"
                                                    data-placeholder="Selecione o paciente"
                                                    data-allow-clear="true"
                                                    disabled
                                                >
                                                    <option value="">Selecione o paciente</option>
                                                    @foreach ($patients as $patient)
                                                        <option
                                                            value="{{ $patient['id'] }}"
                                                            data-patient='@json($patient)'
                                                            @selected((string) old('patient_id', $initialPatient['id'] ?? '') === (string) $patient['id'])
                                                        >
                                                            {{ $patient['name'] }} • {{ $patient['species'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input
                                                    type="hidden"
                                                    name="patient_id"
                                                    id="vetPrescriptionPatientHidden"
                                                    value="{{ old('patient_id', $initialPatient['id'] ?? '') }}"
                                                >
                                            </div>
                                            <div class="col-md-6">
                                                <label for="vetPrescriptionVeterinarianSelect" class="form-label fw-semibold text-secondary">Veterinário responsável</label>
                                                <select
                                                    id="vetPrescriptionVeterinarianSelect"
                                                    name="veterinarian_id"
                                                    class="form-select select2"
                                                    data-placeholder="Selecione o veterinário"
                                                    data-allow-clear="true"
                                                >
                                                    <option value="">Selecione o veterinário</option>
                                                    @foreach ($veterinarians as $vet)
                                                        <option
                                                            value="{{ $vet['id'] }}"
                                                            data-availability="{{ $vet['next_available'] }}"
                                                            @selected((string) old('veterinarian_id', $initialVeterinarian['id'] ?? '') === (string) $vet['id'])
                                                        >
                                                            {{ $vet['name'] }} — {{ $vet['specialty'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                          

                                        <div class="row g-3 mt-0">
                                            <div class="col-md-6">
                                                <div class="vet-prescricao-form__summary-card">
                                                    <div>
                                                        <span class="vet-prescricao-form__info-label d-block mb-2">Alergias registradas</span>
                                                        <p class="vet-prescricao-form__summary-text" id="vetPrescriptionAllergiesSummary">
                                                            @if (! empty($initialPatient['allergies']))
                                                                @php
                                                                    $allergiesPreview = collect($initialPatient['allergies'])
                                                                        ->map(function ($allergy) {
                                                                            return is_array($allergy)
                                                                                ? ($allergy['name'] ?? $allergy['label'] ?? '')
                                                                                : (string) $allergy;
                                                                        })
                                                                        ->filter()
                                                                        ->take(2);
                                                                    $allergiesCount = collect($initialPatient['allergies'])
                                                                        ->map(function ($allergy) {
                                                                            return is_array($allergy)
                                                                                ? ($allergy['name'] ?? $allergy['label'] ?? '')
                                                                                : (string) $allergy;
                                                                        })
                                                                        ->filter()
                                                                        ->count();
                                                                @endphp
                                                                @if ($allergiesCount === 1)
                                                                    {{ $allergiesPreview->first() }}
                                                                @elseif ($allergiesCount === 2)
                                                                    {{ $allergiesPreview->first() }} e {{ $allergiesPreview->last() }}
                                                                @elseif ($allergiesCount > 2)
                                                                    {{ $allergiesPreview->join(', ') }} e mais {{ $allergiesCount - 2 }} alergia{{ $allergiesCount - 2 === 1 ? '' : 's' }}
                                                                @else
                                                                    Nenhuma alergia registrada.
                                                                @endif
                                                            @else
                                                                Nenhuma alergia registrada.
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary vet-prescricao-form__summary-button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#vetPrescriptionAllergiesModal"
                                                        aria-label="Gerenciar alergias registradas"
                                                    >
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="allergies" id="vetPrescriptionAllergiesField" value="{{ $initialAllergiesJson }}">
                                                <small class="text-muted d-block mt-2">Gerencie alergias em <a href="{{ route('vet.allergies.index') }}" class="text-decoration-none" target="_blank" rel="noopener">Cadastros &gt; Alergias</a>.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="vet-prescricao-form__summary-card">
                                                    <div>
                                                        <span class="vet-prescricao-form__info-label d-block mb-2">Condições crônicas</span>
                                                        <p class="vet-prescricao-form__summary-text" id="vetPrescriptionConditionsSummary">
                                                            @if (! empty($initialPatient['conditions']))
                                                                @php
                                                                    $conditionsPreview = collect($initialPatient['conditions'])
                                                                        ->map(function ($condition) {
                                                                            return is_array($condition)
                                                                                ? ($condition['name'] ?? $condition['label'] ?? '')
                                                                                : (string) $condition;
                                                                        })
                                                                        ->filter()
                                                                        ->take(2);
                                                                    $conditionsCount = collect($initialPatient['conditions'])
                                                                        ->map(function ($condition) {
                                                                            return is_array($condition)
                                                                                ? ($condition['name'] ?? $condition['label'] ?? '')
                                                                                : (string) $condition;
                                                                        })
                                                                        ->filter()
                                                                        ->count();
                                                                @endphp
                                                                @if ($conditionsCount === 1)
                                                                    {{ $conditionsPreview->first() }}
                                                                @elseif ($conditionsCount === 2)
                                                                    {{ $conditionsPreview->first() }} e {{ $conditionsPreview->last() }}
                                                                @elseif ($conditionsCount > 2)
                                                                    {{ $conditionsPreview->join(', ') }} e mais {{ $conditionsCount - 2 }} condição{{ $conditionsCount - 2 === 1 ? '' : 's' }} crônica{{ $conditionsCount - 2 === 1 ? '' : 's' }}
                                                                @else
                                                                    Sem registros recentes.
                                                                @endif
                                                            @else
                                                                Sem registros recentes.
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary vet-prescricao-form__summary-button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#vetPrescriptionConditionsModal"
                                                        aria-label="Gerenciar condições crônicas"
                                                    >
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="conditions" id="vetPrescriptionConditionsField" value="{{ $initialConditionsJson }}">
                                                <small class="text-muted d-block mt-2">Gerencie condições crônicas em <a href="{{ route('vet.chronic-conditions.index') }}" class="text-decoration-none" target="_blank" rel="noopener">Cadastros &gt; Condições crônicas</a>.</small>
                                            </div>
                                        </div>


                                        <div
                                            class="modal fade"
                                            id="vetPrescriptionAllergiesModal"
                                            tabindex="-1"
                                            aria-labelledby="vetPrescriptionAllergiesModalLabel"
                                            aria-hidden="true"
                                        >
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="vetPrescriptionAllergiesModalLabel">Alergias registradas</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="vet-prescricao-form__modal-description mb-3">Visualize ou ajuste as alergias vinculadas ao paciente selecionado.</p>
                                                        <div id="vetPrescriptionAllergiesList" class="d-flex flex-wrap gap-2"></div>
                                                        <div class="mt-3">
                                                            <label for="vetPrescriptionAllergySelect" class="form-label fw-semibold text-secondary">Adicionar alergia cadastrada</label>
                                                            <div class="input-group input-group-sm">
                                                                <select id="vetPrescriptionAllergySelect" class="form-select" @if(empty($allergiesCatalog)) disabled @endif>
                                                                    @if (! empty($allergiesCatalog))
                                                                        <option value="">Selecione uma alergia cadastrada</option>
                                                                        @foreach ($allergiesCatalog as $catalogAllergy)
                                                                            <option value="{{ $catalogAllergy['id'] }}" data-name="{{ $catalogAllergy['name'] }}">{{ $catalogAllergy['name'] }}</option>
                                                                        @endforeach
                                                                    @else
                                                                        <option value="">Nenhuma alergia cadastrada</option>
                                                                    @endif
                                                                </select>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-primary vet-prescricao-form__modal-add-button"
                                                                    id="vetPrescriptionAllergyAddButton"
                                                                    @if(empty($allergiesCatalog)) disabled @endif
                                                                    aria-label="Adicionar alergia selecionada"
                                                                >
                                                                    <i class="ri-add-line"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="modal fade"
                                            id="vetPrescriptionConditionsModal"
                                            tabindex="-1"
                                            aria-labelledby="vetPrescriptionConditionsModalLabel"
                                            aria-hidden="true"
                                        >
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="vetPrescriptionConditionsModalLabel">Condições crônicas</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="vet-prescricao-form__modal-description mb-3">Gerencie as condições crônicas associadas ao paciente antes de prosseguir com a prescrição.</p>
                                                        <div id="vetPrescriptionConditionsList" class="d-flex flex-wrap gap-2"></div>
                                                        <div class="mt-3">
                                                            <label for="vetPrescriptionConditionSelect" class="form-label fw-semibold text-secondary">Adicionar condição cadastrada</label>
                                                            <div class="input-group input-group-sm">
                                                                <select id="vetPrescriptionConditionSelect" class="form-select" @if(empty($chronicConditionsCatalog)) disabled @endif>
                                                                    @if (! empty($chronicConditionsCatalog))
                                                                        <option value="">Selecione uma condição crônica cadastrada</option>
                                                                        @foreach ($chronicConditionsCatalog as $catalogCondition)
                                                                            <option value="{{ $catalogCondition['id'] }}" data-name="{{ $catalogCondition['name'] }}">{{ $catalogCondition['name'] }}</option>
                                                                        @endforeach
                                                                    @else
                                                                        <option value="">Nenhuma condição crônica cadastrada</option>
                                                                    @endif
                                                                </select>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-primary vet-prescricao-form__modal-add-button"
                                                                    id="vetPrescriptionConditionAddButton"
                                                                    @if(empty($chronicConditionsCatalog)) disabled @endif
                                                                    aria-label="Adicionar condição selecionada"
                                                                >
                                                                    <i class="ri-add-line"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="mt-3">
                                            <label for="vetPrescriptionNotes" class="form-label">Observações relevantes</label>
                                            <textarea id="vetPrescriptionNotes" name="notes" rows="3" class="form-control vet-prescricao-form__subtle-input">{{ $initialNotes }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-4">
                                <div class="d-flex flex-column gap-3">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-white border-0 pb-0">
                                            <h5 class="mb-1 text-color">Resumo do paciente</h5>
                                            <p class="text-muted mb-0 small">Informações rápidas sobre o paciente e o tutor.</p>
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <img
                                                    id="vetPrescriptionPatientPhoto"
                                                    src="{{ $initialPatientPhoto }}"
                                                    alt="Foto do paciente"
                                                    class="rounded-circle mb-3"
                                                    style="width: 96px; height: 96px; object-fit: cover;"
                                                    data-default-photo="{{ $defaultPatientPhoto }}"
                                                >
                                                <h5 id="vetPrescriptionPatientName" class="mb-1">{{ $initialPatient['name'] ?? 'Selecione um paciente' }}</h5>
                                                <p id="vetPrescriptionPatientDetails" class="text-muted mb-0 small">
                                                    {{ $initialPatientMeta !== '' ? $initialPatientMeta : 'As informações do paciente aparecerão após a seleção.' }}
                                                </p>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Peso</span>
                                                        <span id="vetPrescriptionSummaryWeight" class="fw-semibold text-color">{{ $initialPatient['weight'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Sexo</span>
                                                        <span id="vetPrescriptionSummarySex" class="fw-semibold text-color">{{ $initialPatient['sex'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Nascimento</span>
                                                        <span id="vetPrescriptionSummaryBirthDate" class="fw-semibold text-color">{{ $initialPatient['birth_date'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Última visita</span>
                                                        <span id="vetPrescriptionSummaryLastVisit" class="fw-semibold text-color">{{ $initialPatient['last_visit'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Porte</span>
                                                        <span id="vetPrescriptionSummarySize" class="fw-semibold text-color">{{ $initialPatient['size'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Origem</span>
                                                        <span id="vetPrescriptionSummaryOrigin" class="fw-semibold text-color">{{ $initialPatient['origin'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Microchip</span>
                                                        <span id="vetPrescriptionSummaryMicrochip" class="fw-semibold text-color">{{ $initialPatient['microchip'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-4 p-3 h-100">
                                                        <span class="text-muted small d-block">Pedigree</span>
                                                        <span id="vetPrescriptionSummaryPedigree" class="fw-semibold text-color">{{ $initialPatient['pedigree'] ?? '—' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bg-light rounded-4 p-3 mb-3">
                                                <h6 class="text-color fs-6 mb-2">Observações clínicas</h6>
                                                <p id="vetPrescriptionPatientNotes" class="text-muted small mb-0">
                                                    {{ $initialPatientNotes ? $initialPatientNotes : 'Sem observações clínicas registradas.' }}
                                                </p>
                                            </div>

                                            <div class="bg-light rounded-4 p-3">
                                                <h6 class="text-color fs-6 mb-2">Tutor responsável</h6>
                                                <p id="vetPrescriptionTutorSummaryName" class="fw-semibold mb-1">{{ $initialPatient['tutor'] ?? '—' }}</p>
                                                <p id="vetPrescriptionTutorSummaryDocument" class="text-muted small mb-1">{{ $initialPatient['tutor_document'] ?? '—' }}</p>
                                                <p id="vetPrescriptionTutorSummaryContacts" class="text-muted small mb-1">{{ $initialPatient['contact'] ?? '—' }}</p>
                                                <p id="vetPrescriptionTutorSummaryEmail" class="text-muted small mb-1">{{ $initialPatient['email'] ?? '—' }}</p>
                                                <p id="vetPrescriptionTutorSummaryAddress" class="text-muted small mb-0">{{ $initialPatient['tutor_address'] ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div
                        class="tab-pane fade"
                        id="vetPrescriptionTabPlan"
                        role="tabpanel"
                        aria-labelledby="vetPrescriptionTabPlan-tab"
                    >
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card vet-prescricao-form__card mb-4 mb-xl-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-1">Plano terapêutico</h5>
                                                <p class="text-muted small mb-0">Selecione um modelo e personalize conforme a necessidade clínica.</p>
                                            </div>
                                            <span class="vet-prescricao-form__pill"><i class="ri-magic-line"></i>Protocolos sugeridos</span>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-lg-6">
                                                <label for="vetPrescriptionTemplateSelect" class="form-label fw-semibold text-secondary">Modelo de prescrição</label>
                                                <select id="vetPrescriptionTemplateSelect" class="form-select">
                                                    <option value="">Selecione o modelo</option>
                                                    @foreach ($templates as $template)
                                                        <option value="{{ $template['id'] }}" @selected((string) old('template_id', $initialTemplateId ?? '') === (string) $template['id'])>{{ $template['label'] }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="template_id" id="vetPrescriptionTemplateHidden" value="{{ old('template_id', $initialTemplateId) }}">
                                            </div>
                                            <div class="col-lg-6">
                                                <label for="vetPrescriptionDiagnosisInput" class="form-label">Diagnóstico principal</label>
                                                <input type="text" id="vetPrescriptionDiagnosisInput" name="diagnosis" class="form-control vet-prescricao-form__subtle-input" value="{{ $initialDiagnosis }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <span class="vet-prescricao-form__info-label d-block mb-2">Objetivos terapêuticos</span>
                                            <ul class="mb-0 ps-3" id="vetPrescriptionObjectivesList">
                                                @foreach ($initialTemplate['objectives'] ?? [] as $objective)
                                                    <li class="mb-1">{{ $objective }}</li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <h6 class="mb-0">Campos personalizados do modelo</h6>
                                                    <small class="text-muted">Os campos abaixo são carregados conforme o modelo selecionado.</small>
                                                </div>
                                            </div>
                                            <div id="vetPrescriptionTemplateFields" class="d-flex flex-column gap-3">
                                                <div class="vet-prescricao-form__list-placeholder w-100">Selecione um modelo de prescrição para visualizar os campos configurados.</div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="vetPrescriptionSummaryTextarea" class="form-label">Resumo do caso</label>
                                            <textarea id="vetPrescriptionSummaryTextarea" name="summary" class="form-control vet-prescricao-form__subtle-input" rows="3">{{ $initialSummary }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="tab-pane fade"
                        id="vetPrescriptionTabMedications"
                        role="tabpanel"
                        aria-labelledby="vetPrescriptionTabMedications-tab"
                    >
                        <div class="row g-4">
                            <div class="col-12 col-xl-8">
                                <div class="card vet-prescricao-form__card mb-4 mb-xl-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-1">Plano medicamentoso</h5>
                                                <p class="text-muted small mb-0">Cadastre e ajuste cada medicamento da prescrição.</p>
                                            </div>
                                            <button type="button" id="vetPrescriptionAddMedication" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                                                <i class="ri-add-line"></i>
                                                Adicionar medicamento
                                            </button>
                                        </div>

                                        <div id="vetPrescriptionMedicationsList" class="d-flex flex-column gap-3">
                                            @forelse ($initialMedicationsList as $index => $medication)
                                                <div
                                                    class="vet-prescricao-form__medication-item p-3"
                                                    data-medication-index="{{ $index }}"
                                                    data-medication-id="{{ $medication['id'] ?? $medication['medication_id'] ?? '' }}"
                                                >
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <span class="vet-prescricao-form__info-label d-block">Medicamento</span>
                                                            @php
                                                                $selectedMedicationId = $medication['id'] ?? $medication['medication_id'] ?? '';
                                                                $selectedMedicationId = $selectedMedicationId !== null ? (string) $selectedMedicationId : '';
                                                                $selectedMedicationLabel = $medication['label'] ?? $medication['name'] ?? '';
                                                            @endphp
                                                            <select
                                                                class="form-select vet-prescricao-form__subtle-input vet-prescricao-form__medication-select"
                                                                data-role="medication-select"
                                                                data-placeholder="Selecione o medicamento"
                                                                data-allow-clear="true"
                                                                data-initial-label="{{ $selectedMedicationLabel }}"
                                                                data-initial-id="{{ $selectedMedicationId }}"
                                                            >
                                                                <option value=""></option>
                                                                @foreach ($medicationsCatalog as $catalogMedication)
                                                                    @if (!empty($catalogMedication['id']) && !empty($catalogMedication['label']))
                                                                        <option
                                                                            value="{{ $catalogMedication['id'] }}"
                                                                            @selected($selectedMedicationId !== '' && $selectedMedicationId === (string) $catalogMedication['id'])
                                                                        >
                                                                            {{ $catalogMedication['label'] }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                                @if ($selectedMedicationLabel && $selectedMedicationId === '')
                                                                    <option value="" selected data-custom-option="true">{{ $selectedMedicationLabel }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-link text-danger vetPrescriptionRemoveMedication">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Dosagem</label>
                                                            <input type="text" class="form-control vet-prescricao-form__subtle-input" value="{{ $medication['dosage'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Frequência</label>
                                                            <input type="text" class="form-control vet-prescricao-form__subtle-input" value="{{ $medication['frequency'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Duração</label>
                                                            <input type="text" class="form-control vet-prescricao-form__subtle-input" value="{{ $medication['duration'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Via</label>
                                                            <input type="text" class="form-control vet-prescricao-form__subtle-input" value="{{ $medication['route'] ?? '' }}">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Observações ao tutor</label>
                                                            <textarea class="form-control vet-prescricao-form__subtle-input" rows="2">{{ $medication['notes'] ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="vet-prescricao-form__list-placeholder w-100">
                                                    Nenhum medicamento configurado para este modelo.
                                                </div>
                                            @endforelse
                                        </div>
                                        <input type="hidden" name="medications" id="vetPrescriptionMedicationsField" value="{{ $initialMedicationsJson }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-4">
                                <div class="vet-prescricao-form__indicator-card h-100">
                                    <div class="d-flex flex-column gap-2">
                                        <span class="vet-prescricao-form__info-label">Dica clínica</span>
                                        <p class="text-muted small mb-0">
                                            Utilize esta aba para concentrar todas as medicações prescritas e facilite a revisão antes do envio ao tutor.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="tab-pane fade"
                        id="vetPrescriptionTabGuidelines"
                        role="tabpanel"
                        aria-labelledby="vetPrescriptionTabGuidelines-tab"
                    >
                        <div class="row g-4">
                            <div class="col-12 col-xl-8">
                                <div class="card vet-prescricao-form__card mb-4 mb-xl-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-1">Orientações e monitoramento</h5>
                                                <p class="text-muted small mb-0">Inclua recomendações detalhadas para o tutor e equipe.</p>
                                            </div>
                                            <span class="vet-prescricao-form__pill"><i class="ri-list-check"></i>Checklist clínico</span>
                                        </div>

                                        <div class="mb-3">
                                            <label for="vetPrescriptionGuidelines" class="form-label">Orientações gerais</label>
                                            <textarea id="vetPrescriptionGuidelines" name="guidelines" class="form-control vet-prescricao-form__subtle-input" rows="4">{{ $initialGuidelines }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <span class="vet-prescricao-form__info-label d-block mb-2">Monitoramento sugerido</span>
                                            <ul class="mb-0 ps-3" id="vetPrescriptionMonitoringList">
                                                @foreach ($initialTemplate['monitoring'] ?? [] as $item)
                                                    <li class="mb-1">{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div>
                                            <span class="vet-prescricao-form__info-label d-block mb-2">Notas de segurança</span>
                                            <div class="d-flex flex-column gap-2" id="vetPrescriptionSafetyNotes">
                                                @forelse ($safetyNotes as $note)
                                                    <div class="vet-prescricao-form__checklist-item">
                                                        <i class="ri-shield-check-line text-primary"></i>
                                                        <span>{{ $note }}</span>
                                                    </div>
                                                @empty
                                                    <span class="text-muted small">Nenhuma nota cadastrada.</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-4">
                                <div class="card vet-prescricao-form__card">
                                    <div class="card-body">
                                        <h6 class="mb-3">Linha do tempo diária</h6>
                                        <div class="position-relative vet-prescricao-form__timeline" id="vetPrescriptionTimeline">
                                            @forelse ($initialTemplate['timeline'] ?? [] as $event)
                                                <div class="vet-prescricao-form__timeline-item mb-3">
                                                    <span class="vet-prescricao-form__info-label d-block">{{ $event['time'] }}</span>
                                                    <strong class="d-block">{{ $event['title'] }}</strong>
                                                    <span class="text-muted small">{{ $event['description'] }}</span>
                                                </div>
                                            @empty
                                                <span class="text-muted small">Nenhum evento registrado.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="tab-pane fade"
                        id="vetPrescriptionTabDocuments"
                        role="tabpanel"
                        aria-labelledby="vetPrescriptionTabDocuments-tab"
                    >
                        <div class="card shadow-sm vet-prescricao-form__card mb-4">
                            <div class="card-header bg-white border-0 pb-0 d-flex align-items-start justify-content-between">
                                <div>
                                    <h5 class="mb-1 text-color">Documentação e anexos</h5>
                                    <p class="text-muted mb-0 small">Anexe exames, laudos e fotos relacionadas à prescrição.</p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input
                                        type="file"
                                        class="d-none"
                                        id="vetPrescriptionAttachmentInput"
                                        multiple
                                        accept="application/pdf,image/*,.doc,.docx,.xls,.xlsx,.txt"
                                    >
                                    <button
                                        type="button"
                                        id="vetPrescriptionAttachmentAdd"
                                        class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                                    >
                                        <i class="ri-upload-2-line"></i>
                                        Adicionar arquivo
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    $attachmentsCollection = collect($attachments ?? [])
                                        ->filter(fn ($item) => is_array($item))
                                        ->map(function ($item, $index) {
                                            $item['id'] = $item['id'] ?? ($item['path'] ?? 'attachment-' . $index);

                                            return $item;
                                        })
                                        ->values();
                                    $hasPrescriptionAttachments = $attachmentsCollection->isNotEmpty();
                                @endphp
                                <div class="row g-3" id="vetPrescriptionAttachmentList">
                                    <div class="col-12 {{ $hasPrescriptionAttachments ? 'd-none' : '' }}" id="vetPrescriptionAttachmentEmpty">
                                        <div class="vet-prescricao-form__list-placeholder">Nenhum documento anexado.</div>
                                    </div>

                                    @foreach ($attachmentsCollection as $attachment)
                                        @php
                                            $metaParts = [];
                                            if (!empty($attachment['uploaded_by'])) {
                                                $metaParts[] = 'por ' . $attachment['uploaded_by'];
                                            }
                                            if (!empty($attachment['uploaded_at'])) {
                                                $metaParts[] = 'em ' . $attachment['uploaded_at'];
                                            }
                                            $metaText = $metaParts
                                                ? 'Enviado ' . implode(' ', $metaParts)
                                                : 'Documento anexado anteriormente.';

                                            $sizeParts = [];
                                            if (!empty($attachment['size'])) {
                                                $sizeParts[] = 'Tamanho ' . $attachment['size'];
                                            }
                                            if (!empty($attachment['extension'])) {
                                                $sizeParts[] = strtoupper($attachment['extension']);
                                            }

                                            $removeButtonClasses = 'btn btn-sm btn-outline-danger';
                                            $removeButtonDisabled = false;
                                            if (empty($attachment['path'])) {
                                                $removeButtonClasses .= ' disabled';
                                                $removeButtonDisabled = true;
                                            }
                                        @endphp
                                        <div class="col-md-6" data-attachment-id="{{ $attachment['id'] }}">
                                            <div class="vet-prescricao-form__attachment-card">
                                                <span class="vet-prescricao-form__attachment-icon bg-primary-subtle text-primary">
                                                    <i class="ri-file-line"></i>
                                                </span>
                                                <div class="flex-fill">
                                                    <h6 class="mb-1">{{ $attachment['name'] ?? 'Documento' }}</h6>
                                                    <span class="text-muted small d-block">{{ $metaText }}</span>
                                                    @if (!empty($sizeParts))
                                                        <span class="text-muted small d-block">{{ implode(' • ', $sizeParts) }}</span>
                                                    @endif

                                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                                        @if (!empty($attachment['url']))
                                                            <a
                                                                href="{{ $attachment['url'] }}"
                                                                target="_blank"
                                                                rel="noopener"
                                                                class="btn btn-sm btn-outline-primary"
                                                            >
                                                                Visualizar
                                                            </a>
                                                        @endif

                                                        <button
                                                            type="button"
                                                            class="{{ $removeButtonClasses }}"
                                                            data-attachment-remove="{{ $attachment['id'] }}"
                                                            @if ($removeButtonDisabled) disabled @endif
                                                        >
                                                            Remover
                                                        </button>
                                                    </div>
                                                </div>
                                                <span class="vet-prescricao-form__pill">{{ strtoupper($attachment['extension'] ?? 'ARQ') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="alert alert-soft-info d-flex align-items-center gap-3 mt-4">
                                    <i class="ri-information-line fs-4 text-info"></i>
                                    <div>
                                        <h6 class="fw-semibold text-info mb-1">Centralize todos os documentos com a prescrição.</h6>
                                        <p class="mb-0 text-muted">Arquivos adicionados ficam disponíveis para a equipe clínica e para a área do tutor.</p>
                                    </div>
                                </div>

                                <div id="vetPrescriptionAttachmentInputs" class="d-none">
                                    @foreach ($attachmentsCollection as $attachment)
                                        <input type="hidden" name="attachments[]" value='@json($attachment)'>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" id="vetPrescriptionSubmit" class="btn btn-primary px-4">
                            Salvar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-form-page>
@endsection

@section('js')
    <script>
        window.vetPrescriptionFormData = {
            patients: @json($patients),
            templates: @json($templates),
            veterinarians: @json($veterinarians),
            checklist: @json($checklist),
            safetyNotes: @json($safetyNotes),
            attachments: @json($attachments),
            attachmentsUploadUrl: @json(route('vet.prescriptions.attachments.store')),
            attachmentsRemoveUrl: @json(route('vet.prescriptions.attachments.remove')),
            attachmentsMaxItems: 8,
            attachmentsMaxSizeBytes: {{ 10 * 1024 * 1024 }},
            pharmacies: @json($pharmacies),
            communicationChannels: @json($communicationChannels),
            dispensingGuidelines: @json($dispensingGuidelines),
            allergiesCatalog: @json($allergiesCatalog),
            chronicConditionsCatalog: @json($chronicConditionsCatalog),
            medicationsCatalog: @json($medicationsCatalog),
            oldTemplateId: @json(old('template_id', null)),
            oldTemplateFields: @json(old('template_fields', null)),
            initialTemplateId: @json($initialTemplateId),
            defaultPatientPhoto: @json($defaultPatientPhoto),
            existingPrescription: @json($initialPrescription),
            isEditing: @json($isEditing),
        };
    </script>
    <script src="/tinymce/tinymce.min.js"></script>
    <script src="/js/vet/prescricoes-form.js"></script>
@endsection
