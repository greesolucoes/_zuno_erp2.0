@php
    $modalId = $modalId ?? ('vet-encounter-modal-' . \Illuminate\Support\Str::slug($encounter['code'] ?? uniqid()));
    $latestRecordId = data_get($encounter, 'latest_record.id');
    $vaccinationRouteParams = collect([
        'attendance_id' => $encounter['id'] ?? null,
        'patient_id' => $encounter['patient_id'] ?? null,
        'veterinarian_id' => $encounter['veterinarian_id'] ?? null,
    ])->filter(function ($value) {
        return $value !== null && $value !== '';
    })->all();
    $prescriptionRouteParams = collect([
        'atendimento' => $encounter['id'] ?? null,
        'patient_id' => $encounter['patient_id'] ?? ($encounter['animal_id'] ?? null),
        'veterinarian_id' => $encounter['veterinarian_id'] ?? null,
    ])->filter(function ($value) {
        return $value !== null && $value !== '';
    })->all();
    $attendanceId = $encounter['id'] ?? null;
    $latestExamRequestId = data_get($encounter, 'latest_exam_request.id');
    $examRouteParams = collect([
        'attendance' => $attendanceId,
        'exam_id' => $latestExamRequestId,
    ])->filter(function ($value) {
        return $value !== null && $value !== '';
    })->all();
    $latestPrescriptionId = data_get($encounter, 'latest_prescription.id');
    $prescriptionsCount = (int) data_get($encounter, 'prescriptions_count', 0);
    $examRequestsCount = (int) data_get($encounter, 'exam_requests_count', 0);
    $latestVaccinationId = data_get($encounter, 'latest_vaccination.id');
    $vaccinationsCount = (int) data_get($encounter, 'vaccinations_count', 0);
    $hasRecord = !empty($latestRecordId);
    $hasPrescriptions = $prescriptionsCount > 0 && !empty($latestPrescriptionId);
    $hasExamRequests = $examRequestsCount > 0 && !empty($attendanceId);
    $hasVaccinations = $vaccinationsCount > 0 && !empty($latestVaccinationId);
    $canEditPrescription = \Illuminate\Support\Facades\Route::has('vet.prescriptions.edit');
    $canCreatePrescription = \Illuminate\Support\Facades\Route::has('vet.prescriptions.create');
    $recordActionUrl = $hasRecord
        ? route('vet.records.edit', $latestRecordId)
        : route('vet.records.create', ['attendance' => $encounter['id']]);
    $recordActionLabel = $hasRecord ? 'Editar prontuário' : 'Emitir prontuário';
    $recordActionTitle = $hasRecord
        ? 'Editar prontuário registrado'
        : 'Registrar consulta médica';
    $examActionUrl = $hasExamRequests
        ? route('vet.exams.index', $examRouteParams)
        : route('vet.exams.create', $examRouteParams);
    $examActionLabel = $hasExamRequests ? 'Visualizar exames' : 'Solicitar exame';
    $examActionTitle = $hasExamRequests
        ? 'Abrir exames vinculados a este atendimento'
        : 'Solicitar exame para este atendimento';
    $prescriptionActionLabel = $hasPrescriptions ? 'Editar prescrição' : 'Emitir prescrição';
    $prescriptionActionTitle = $hasPrescriptions
        ? 'Editar prescrição vinculada a este atendimento'
        : 'Emitir prescrição';
    $prescriptionActionUrl = null;
    if ($hasPrescriptions) {
        if ($canEditPrescription) {
            $prescriptionActionUrl = route('vet.prescriptions.edit', $latestPrescriptionId);
        }
    } elseif ($canCreatePrescription) {
        $prescriptionActionUrl = route('vet.prescriptions.create', $prescriptionRouteParams);
    }
    $prescriptionActionDisabled = empty($prescriptionActionUrl);    
    $vaccinationCreateUrl = route('vet.vaccinations.create', $vaccinationRouteParams);
    $vaccinationCreateTitle = 'Agendar vacinação para este atendimento';
    $vaccinationApplyUrl = $hasVaccinations
        ? route('vet.vaccinations.apply', $latestVaccinationId)
        : null;
    $vaccinationEditUrl = $hasVaccinations
        ? route('vet.vaccinations.edit', $latestVaccinationId)
        : null;
    $vaccinationCardUrl = null;
    if ($hasVaccinations && !empty($encounter['patient_id']) && \Illuminate\Support\Facades\Route::has('vet.vaccine-cards.print')) {
        $cardPatientName = $encounter['patient'] ?? 'cartao';
        $vaccinationCardSlug = \Illuminate\Support\Str::slug(sprintf(
            '%s-%s',
            $cardPatientName !== '' ? $cardPatientName : 'cartao',
            $encounter['patient_id']
        ));
        $vaccinationCardUrl = route('vet.vaccine-cards.print', ['card' => $vaccinationCardSlug]);
    }
    $hospitalizationRouteParams = collect([
        'attendance' => $encounter['id'] ?? null,
        'patient' => $encounter['patient_id'] ?? null,
    ])->filter(function ($value) {
        return $value !== null && $value !== '';
    })->all();
    $hospitalizationCreateUrl = \Illuminate\Support\Facades\Route::has('vet.hospitalizations.create')
        ? route('vet.hospitalizations.create', $hospitalizationRouteParams)
        : null;
    $activeHospitalization = data_get($encounter, 'active_hospitalization');
    $hospitalizationViewUrl = null;

    if ($activeHospitalization) {
        $hospitalizationViewUrl = \Illuminate\Support\Facades\Route::has('vet.hospitalizations.status.index')
            ? route('vet.hospitalizations.status.index', $activeHospitalization['id'])
            : null;
    }

    $hospitalizationActionUrl = $hospitalizationViewUrl ?? $hospitalizationCreateUrl;
    $hospitalizationActionLabel = $hospitalizationViewUrl ? 'Ver internação' : 'Enviar para internação';
    $hospitalizationActionTitle = $hospitalizationViewUrl
        ? ($activeHospitalization['status'] ?? 'Visualizar internação ativa deste paciente')
        : 'Registrar internação para este atendimento';
    if ($hospitalizationViewUrl && !empty($activeHospitalization['status'])) {
        $hospitalizationActionTitle = sprintf(
            'Visualizar internação ativa deste paciente (%s)',
            $activeHospitalization['status']
        );
    }
    $actionsMenuKey = $encounter['id'] ?? ($encounter['code'] ?? uniqid());
    $actionsMenuId = 'vet-encounter-actions-' . \Illuminate\Support\Str::slug((string) $actionsMenuKey);
    $vaccinationCardId = $actionsMenuId . '-vaccination-card';
    $vaccinationLinks = [];
    if ($vaccinationApplyUrl) {
        $vaccinationLinks[] = [
            'label' => 'Aplicar vacina',
            'url' => $vaccinationApplyUrl,
            'title' => 'Aplicar vacina agendada',
            'target' => null,
            'rel' => null,
        ];
    }
    if ($vaccinationEditUrl) {
        $vaccinationLinks[] = [
            'label' => 'Editar vacinação',
            'url' => $vaccinationEditUrl,
            'title' => 'Editar vacinação vinculada a este atendimento',
            'target' => null,
            'rel' => null,
        ];
    }
    if ($vaccinationCardUrl) {
        $vaccinationLinks[] = [
            'label' => 'Cartão de vacina',
            'url' => $vaccinationCardUrl,
            'title' => 'Visualizar cartão de vacina do paciente',
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ];
    }
    $hasVaccinationLinks = count($vaccinationLinks) > 0;
@endphp

<tr>
    <td style="min-width: max-content; width: auto; position: relative;">
        <button
            type="button"
            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2 vet-encounter-actions-toggle"
            data-menu-id="{{ $actionsMenuId }}"
            title="Ações do atendimento"
        >
            <i class="ri-menu-line"></i>
            Ações
        </button>

        <nav id="{{ $actionsMenuId }}" class="menu vet-encounter-menu d-none">
            <ul class="gap-2">
                <li>
                    <i class="ri-stethoscope-line text-info"></i>
                    <a
                        href="{{ $recordActionUrl }}"
                        class="text-decoration-none text-color-back"
                        title="{{ $recordActionTitle }}"
                    >
                        {{ $recordActionLabel }}
                    </a>
                </li>
                <li>
                    <i class="ri-flask-line text-warning"></i>
                    <a
                        href="{{ $examActionUrl }}"
                        class="text-decoration-none text-color-back"
                        title="{{ $examActionTitle }}"
                    >
                        {{ $examActionLabel }}
                    </a>
                </li>
                 <li>
                    <i class="ri-capsule-line text-success"></i>
                    @if (! $prescriptionActionDisabled)
                        <a
                            href="{{ $prescriptionActionUrl }}"
                            class="text-decoration-none text-color-back"
                            title="{{ $prescriptionActionTitle }}"
                        >
                            {{ $prescriptionActionLabel }}
                        </a>
                    @else
                        <span class="text-muted" title="{{ $prescriptionActionTitle }}">
                            {{ $prescriptionActionLabel }}
                        </span>
                    @endif
                </li>
                <li class="position-relative">
                    <i class="ri-syringe-line text-primary"></i>
                    @if ($hasVaccinations && $hasVaccinationLinks)
                        <button
                            type="button"
                            class="border-0 bg-transparent text-color-back p-0 fw-semibold vet-vaccination-actions-toggle d-flex align-items-center gap-1"
                            data-card-id="{{ $vaccinationCardId }}"
                            aria-controls="{{ $vaccinationCardId }}"
                            aria-expanded="false"
                            aria-haspopup="true"
                            title="Visualizar ações de vacinação"
                        >
                            Vacinação
                            <i class="ri-arrow-right-s-line text-muted"></i>
                        </button>

                        <div
                            id="{{ $vaccinationCardId }}"
                            class="vet-vaccination-actions-card card shadow-sm border-0 position-absolute start-0 top-100 mt-3 d-none"
                            role="dialog"
                            aria-label="Ações de vacinação"
                            aria-hidden="true"
                            tabindex="-1"
                            style="min-width: 240px; z-index: 2;"
                        >
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fw-semibold text-color">Ações de vacinação</span>
                                    <button
                                        type="button"
                                        class="btn-close vet-vaccination-actions-close"
                                        data-card-id="{{ $vaccinationCardId }}"
                                        aria-label="Fechar ações de vacinação"
                                    ></button>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($vaccinationLinks as $vaccinationLink)
                                        <a
                                            href="{{ $vaccinationLink['url'] }}"
                                            class="btn btn-outline-primary btn-sm w-100 d-flex justify-content-between align-items-center"
                                            title="{{ $vaccinationLink['title'] }}"
                                            @if (!empty($vaccinationLink['target'])) target="{{ $vaccinationLink['target'] }}" @endif
                                            @if (!empty($vaccinationLink['rel'])) rel="{{ $vaccinationLink['rel'] }}" @endif
                                        >
                                            <span>{{ $vaccinationLink['label'] }}</span>
                                            <i class="ri-arrow-right-line"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ $vaccinationCreateUrl }}"
                            class="text-decoration-none text-color-back"
                            title="{{ $vaccinationCreateTitle }}"
                        >
                            Agendar vacinação
                        </a>
                    @endif
                </li>
                @if ($hospitalizationActionUrl)
                    <li>
                        <i class="ri-hotel-bed-line text-info"></i>
                        <a
                            href="{{ $hospitalizationActionUrl }}"
                            class="text-decoration-none text-color-back"
                            title="{{ $hospitalizationActionTitle }}"
                        >
                            {{ $hospitalizationActionLabel }}
                        </a>
                    </li>
                @endif
                <li>
                    <i class="ri-history-line text-secondary"></i>
                    <a
                        href="{{ route('vet.atendimentos.history', $encounter['id']) }}"
                        class="text-decoration-none text-color-back"
                        title="Visualizar histórico do atendimento"
                    >
                        Histórico do atendimento
                    </a>
                </li>
                <li>
                    <i class="ri-edit-line text-success"></i>
                    <a
                        href="{{ route('vet.atendimentos.edit', $encounter['id']) }}"
                        class="text-decoration-none text-color-back"
                        title="Editar atendimento"
                    >
                        Editar atendimento
                    </a>
                </li>
                @php
                    $billingInfo = $encounter['billing'] ?? null;
                    $hasBilling = filled($billingInfo);
                    $billingLabel = $hasBilling ? 'Editar faturamento' : 'Faturar atendimento';
                    $billingTitle = $hasBilling
                        ? 'Editar o faturamento existente deste atendimento'
                        : 'Faturar atendimento';
                    $billingTotal = data_get($billingInfo, 'totals.grand_total');
                @endphp
                <li>
                    <i class="ri-money-dollar-circle-line text-secondary"></i>
                    <a
                        href="{{ route('vet.atendimentos.billing', $encounter['id']) }}"
                        class="text-decoration-none text-color-back"
                        title="{{ $billingTitle }}"
                    >
                        {{ $billingLabel }}
                    </a>
                    @if ($hasBilling && $billingTotal)
                        <span class="badge bg-success-subtle text-success ms-1">R$ {{ $billingTotal }}</span>
                    @endif
                </li>
                <li>
                    <i class="ri-delete-bin-6-line text-danger"></i>
                    <form
                        action="{{ route('vet.atendimentos.destroy', $encounter['id']) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Deseja realmente excluir este atendimento? Essa ação não poderá ser desfeita.');"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="border-0 bg-transparent text-danger p-0 text-start"
                            title="Excluir atendimento"
                        >
                            Excluir atendimento
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $encounter['patient'] ?? '—' }}</div>
        <div class="text-muted small">
            {{ $encounter['species'] ?? '—' }}
            @if(!empty($encounter['tutor']))
                • Tutor: {{ $encounter['tutor'] }}
            @endif
        </div>
        <div class="text-muted small">{{ $encounter['code'] ?? '—' }}</div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $encounter['veterinarian'] ?? '—' }}</div>
    </td>
    <td class="text-center">
        <div class="fw-semibold text-color">{{ $encounter['service'] ?? '—' }}</div>
        @if(!empty($encounter['room']))
            <div class="text-muted small">{{ $encounter['room'] }}</div>
        @endif
    </td>
    <td>
        <div class="fw-semibold text-color">
            @if(!empty($encounter['start']))
                {{ \Illuminate\Support\Carbon::parse($encounter['start'])->format('d/m/Y') }} <br>
                <small>{{ \Illuminate\Support\Carbon::parse($encounter['start'])->format('H:i') }}</small>
            @else
                —-
            @endif
        </div>
    </td>
    <td class="text-center">
        <span class="badge p-2 fw-semibold bg-{{ $encounter['status_color'] ?? 'primary' }} text-uppercase">
            {{ $encounter['status'] ?? '—' }}
        </span>
    </td>
</tr>