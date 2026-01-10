@php
    $isEdit = $isEdit ?? false;
    $pageTitle = $pageTitle ?? ($isEdit ? 'Editar internação' : 'Registrar internação');
@endphp

@extends('layouts.app', ['title' => $pageTitle])

@section('css')
    <style>
        .vet-hospitalization-card {
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background-color: #ffffff;
        }

        .vet-hospitalization-card.shadow-sm {
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
        }

        .vet-hospitalization-summary {
            border-radius: 16px;
            border: 1px solid rgba(85, 110, 230, 0.18);
            background: linear-gradient(180deg, rgba(85, 110, 230, 0.06) 0%, rgba(255, 255, 255, 0.9) 100%);
            padding: 1.25rem;
        }

        .vet-hospitalization-avatar {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            object-fit: cover;
        }

        .vet-hospitalization-metric {
            border-radius: 12px;
            border: 1px solid rgba(85, 110, 230, 0.15);
            background: #ffffff;
            padding: 0.75rem;
            text-align: center;
        }

        .vet-hospitalization-metric span {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .vet-hospitalization-tutor-card {
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #ffffff;
            padding: 1.5rem;
        }

        .vet-hospitalization-actions {
            border-top: 1px solid rgba(15, 23, 42, 0.08);
            margin-top: 2rem;
            padding-top: 1.5rem;
        }
    </style>
@endsection

@php
    $formAction = $formAction ?? '#';
    $formMethod = strtoupper($formMethod ?? 'POST');
    $shouldPreserveStatus = $shouldPreserveStatus ?? $isEdit;
    $submitButtonLabel = $submitButtonLabel ?? ($isEdit ? 'Salvar alterações' : 'Registrar internação');
    $submitButtonIcon = $submitButtonIcon ?? ($isEdit ? 'ri-save-3-line' : 'ri-hospital-line');
    $pageHeading = $pageHeading ?? ($isEdit ? 'Editar internação' : 'Internação de paciente');
    $pageDescription = $pageDescription ?? ($isEdit ? 'Atualize as informações essenciais da internação.' : 'Organize as informações essenciais para admitir o paciente na unidade de internação.');
    $patientsOptions = $patients ?? [];
    $selectedPatient = $selectedPatient ?? null;
    $veterinarianOptions = $veterinarians ?? [];
    $roomOptions = $rooms ?? [];
    $riskOptions = $riskOptions ?? [];
    $initialValues = $initialValues ?? [];
    $attendanceContext = $attendanceContext ?? null;
    $defaultAvatar = asset('assets/images/users/avatar-1.jpg');

    $patientPhoto = $selectedPatient['photo'] ?? $defaultAvatar;
    $patientName = $selectedPatient['name'] ?? 'Selecione um paciente';
    $patientMeta = $selectedPatient['meta'] ?? 'As informações do paciente aparecerão aqui após a seleção.';
    $patientNotes = $selectedPatient['notes'] ?? null;
    $patientMetrics = $selectedPatient['metrics'] ?? [];
    $tutorData = $selectedPatient['tutor'] ?? [];
    $tutorPhones = $tutorData['phones'] ?? [];

    $defaultStatus = \App\Models\Petshop\Internacao::STATUS_ACTIVE;
    $selectedPatientId = old('patient_id', $initialValues['patient_id'] ?? null);
    $selectedAttendanceId = old('atendimento_id', $initialValues['atendimento_id'] ?? null);
    $selectedVeterinarianId = old('veterinario_id', $initialValues['veterinario_id'] ?? null);
    $selectedRoomId = old('sala_internacao_id', $initialValues['sala_internacao_id'] ?? null);
    $selectedAdmissionDate = old('admission_date', $initialValues['admission_date'] ?? now()->format('Y-m-d'));
    $selectedAdmissionTime = old('admission_time', $initialValues['admission_time'] ?? now()->format('H:i'));
    $selectedExpectedDischarge = old('expected_discharge_date', $initialValues['expected_discharge_date'] ?? now()->addDays(2)->format('Y-m-d'));
    $selectedReason = old('reason', $initialValues['reason'] ?? '');
    $selectedNotes = old('notes', $initialValues['notes'] ?? '');
    $selectedStatus = old('status', $initialValues['status'] ?? $defaultStatus);
    $selectedRiskLevel = old('nivel_risco', $initialValues['nivel_risco'] ?? null);
@endphp

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="text-color mb-1">{{ $pageHeading }}</h2>
                <p class="text-muted mb-0">{{ $pageDescription }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('vet.hospitalizations.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i>
                    Voltar
                </a>
            </div>
        </div>

        @if ($attendanceContext)
            <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
                <i class="ri-calendar-check-line fs-4 text-info mt-1"></i>
                <div>
                    <strong>Atendimento vinculado:</strong>
                    <a href="{{ $attendanceContext['url'] }}" class="text-decoration-none">{{ $attendanceContext['code'] }}</a>
                    <div class="small text-muted mt-1">
                        @if (!empty($attendanceContext['status']))
                            <span class="badge bg-{{ $attendanceContext['status_color'] ?? 'primary' }}-subtle text-{{ $attendanceContext['status_color'] ?? 'primary' }} me-2">
                                {{ $attendanceContext['status'] }}
                            </span>
                        @endif
                        @if (!empty($attendanceContext['scheduled_at']))
                            {{ $attendanceContext['scheduled_at'] }}
                        @endif
                        @if (!empty($attendanceContext['patient']) || !empty($attendanceContext['veterinarian']))
                            <div class="mt-1">
                                @if (!empty($attendanceContext['patient']))
                                    Paciente: {{ $attendanceContext['patient'] }}
                                @endif
                                @if (!empty($attendanceContext['patient']) && !empty($attendanceContext['veterinarian']))
                                    •
                                @endif
                                @if (!empty($attendanceContext['veterinarian']))
                                    Veterinário: {{ $attendanceContext['veterinarian'] }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <form
            id="vet-hospitalization-form"
            class="row g-4"
            method="POST"
            action="{{ $formAction }}"
            autocomplete="off"
            data-default-avatar="{{ $defaultAvatar }}"
            data-preserve-status="{{ $shouldPreserveStatus ? 'true' : 'false' }}"
        >
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif
            @if ($selectedAttendanceId)
                <input type="hidden" name="atendimento_id" value="{{ $selectedAttendanceId }}">
            @endif
            <input type="hidden" name="status" id="hospitalizationStatus" value="{{ $selectedStatus }}">

            <div class="col-12 col-xl-8">
                <div class="vet-hospitalization-card shadow-sm p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Dados do paciente</h5>
                            <p class="text-muted small mb-0">Selecione o paciente e confirme as informações principais antes da admissão.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="hospitalizationPatient" class="form-label">Paciente</label>
                            <select
                                class="form-select"
                                id="hospitalizationPatient"
                                name="patient_id"
                            >
                                <option value="">Selecione o paciente</option>
                                @foreach ($patientsOptions as $option)
                                    <option
                                        value="{{ $option['id'] }}"
                                        data-patient='@json($option['patient'])'
                                        @selected((string) $selectedPatientId === (string) $option['id'])
                                    >
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="hospitalizationTutorName" class="form-label">Tutor</label>
                            <input
                                type="text"
                                class="form-control"
                                id="hospitalizationTutorName"
                                value="{{ $tutorData['name'] ?? '' }}"
                                readonly
                            >
                        </div>
                    </div>

                    <div class="vet-hospitalization-summary d-flex gap-3 align-items-start mt-4">
                        <img
                            src="{{ $patientPhoto }}"
                            alt="Paciente"
                            class="vet-hospitalization-avatar"
                            id="hospitalizationPatientPhoto"
                        >
                        <div>
                            <h5 class="mb-1" id="hospitalizationPatientName">{{ $patientName }}</h5>
                            <p class="text-muted mb-2" id="hospitalizationPatientMeta">{{ $patientMeta }}</p>
                            <p class="small text-muted mb-0" id="hospitalizationPatientNotes">
                                {{ $patientNotes ?? 'Adicione observações iniciais na seção de observações abaixo.' }}
                            </p>
                        </div>
                    </div>

                    <div class="row g-2 mt-3" id="hospitalizationPatientMetrics">
                        @if (!empty($patientMetrics))
                            @foreach ($patientMetrics as $metric)
                                <div class="col-6 col-lg-3">
                                    <div class="vet-hospitalization-metric">
                                        <span class="text-muted d-block mb-1">{{ $metric['label'] }}</span>
                                        <strong>{{ $metric['value'] }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="text-muted small">Os dados clínicos serão exibidos após a seleção do paciente.</div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <span class="text-muted small d-block mb-1">Telefones adicionais</span>
                        <ul class="list-unstyled small mb-0" id="hospitalizationTutorPhones" data-empty-placeholder="Nenhum telefone adicional cadastrado.">
                            @if (!empty($tutorPhones))
                                @foreach ($tutorPhones as $phone)
                                    <li>{{ $phone }}</li>
                                @endforeach
                            @else
                                <li class="text-muted">Nenhum telefone adicional cadastrado.</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="vet-hospitalization-card shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Dados da internação</h5>
                            <p class="text-muted small mb-0">Defina local, responsável e observações para a admissão.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="hospitalizationRoom" class="form-label">Unidade / sala de internação</label>
                            <select class="form-select" id="hospitalizationRoom" name="sala_internacao_id">
                                <option value="">Selecione a sala</option>
                                @foreach ($roomOptions as $room)
                                    <option value="{{ $room['id'] }}" @selected((string) $selectedRoomId === (string) $room['id'])>
                                        {{ $room['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="hospitalizationVeterinarian" class="form-label">Profissional responsável</label>
                            <select class="form-select" id="hospitalizationVeterinarian" name="veterinario_id">
                                <option value="">Selecione o profissional</option>
                                @foreach ($veterinarianOptions as $vet)
                                    <option value="{{ $vet['id'] }}" @selected((string) $selectedVeterinarianId === (string) $vet['id'])>
                                        {{ $vet['label'] ?? $vet['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="hospitalizationAdmissionDate" class="form-label">Data da admissão</label>
                            <input type="date" class="form-control" id="hospitalizationAdmissionDate" name="admission_date" value="{{ $selectedAdmissionDate }}">
                        </div>
                        <div class="col-md-3">
                            <label for="hospitalizationAdmissionTime" class="form-label">Horário</label>
                            <input type="time" class="form-control" id="hospitalizationAdmissionTime" name="admission_time" value="{{ $selectedAdmissionTime }}">
                        </div>
                        <div class="col-md-3">
                            <label for="hospitalizationExpectedDischarge" class="form-label">Previsão de alta</label>
                            <input type="date" class="form-control" id="hospitalizationExpectedDischarge" name="expected_discharge_date" value="{{ $selectedExpectedDischarge }}">
                        </div>
                        <div class="col-md-3">
                            <label for="hospitalizationRiskLevel" class="form-label">Nível de risco</label>
                            <select class="form-select" id="hospitalizationRiskLevel" name="nivel_risco">
                                @foreach ($riskOptions as $risk)
                                    <option value="{{ $risk['value'] }}" @selected((string) $selectedRiskLevel === (string) $risk['value'])>
                                        {{ $risk['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="hospitalizationReason" class="form-label">Motivo da internação</label>
                            <textarea class="form-control" id="hospitalizationReason" name="reason" rows="3" placeholder="Descreva o motivo principal da internação">{{ $selectedReason }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="hospitalizationNotes" class="form-label">Observações iniciais</label>
                            <textarea class="form-control" id="hospitalizationNotes" name="notes" rows="3" placeholder="Inclua recomendações importantes para a equipe de enfermagem">{{ $selectedNotes }}</textarea>
                        </div>
                    </div>

                    <div class="vet-hospitalization-actions d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="{{ $submitButtonIcon }} me-1"></i>
                            {{ $submitButtonLabel }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="hospitalizationSaveDraft">
                            <i class="ri-draft-line me-1"></i>
                            Salvar rascunho
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="vet-hospitalization-card shadow-sm p-4 mb-4">
                    <h5 class="mb-2">Resumo rápido</h5>
                    <p class="text-muted small mb-0" id="hospitalizationPatientOverview">
                        @if ($selectedPatient)
                            Confirme as informações antes de concluir a admissão do paciente.
                        @else
                            Selecione um paciente para visualizar os dados clínicos e contatos do tutor.
                        @endif
                    </p>
                </div>

                <div class="vet-hospitalization-tutor-card shadow-sm">
                    <h6 class="mb-2">Tutor</h6>
                    <p class="mb-1" id="hospitalizationTutorNameDisplay"><strong>{{ $tutorData['name'] ?? 'Tutor não informado' }}</strong></p>
                    <p class="text-muted small mb-2" id="hospitalizationTutorDocument">{{ $tutorData['document'] ?? '' }}</p>
                    <p class="text-muted small mb-1" id="hospitalizationTutorContactDisplay">
                        @if (!empty($tutorData['contact']))
                            <i class="ri-phone-line me-1"></i>{{ $tutorData['contact'] }}
                        @endif
                    </p>
                    <p class="text-muted small mb-0" id="hospitalizationTutorEmailDisplay">
                        @if (!empty($tutorData['email']))
                            <i class="ri-mail-line me-1"></i>{{ $tutorData['email'] }}
                        @endif
                    </p>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/vet/internacoes-form.js') }}"></script>
@endpush