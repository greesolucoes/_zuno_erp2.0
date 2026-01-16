@php
    $latestRecordId = data_get($encounter, 'latest_record.id');
    $attendanceId = $encounter['id'] ?? null;
    $latestExamRequestId = data_get($encounter, 'latest_exam_request.id');
    $latestPrescriptionId = data_get($encounter, 'latest_prescription.id');
    $prescriptionsCount = (int) data_get($encounter, 'prescriptions_count', 0);
    $examRequestsCount = (int) data_get($encounter, 'exam_requests_count', 0);
    $latestVaccinationId = data_get($encounter, 'latest_vaccination.id');
    $vaccinationsCount = (int) data_get($encounter, 'vaccinations_count', 0);

    $hasRecord = !empty($latestRecordId);
    $hasPrescriptions = $prescriptionsCount > 0 && !empty($latestPrescriptionId);
    $hasExamRequests = $examRequestsCount > 0 && !empty($attendanceId);
    $hasVaccinations = $vaccinationsCount > 0 && !empty($latestVaccinationId);

    $vaccinationRouteParams = collect([
        'attendance_id' => $attendanceId,
        'patient_id' => $encounter['patient_id'] ?? null,
        'veterinarian_id' => $encounter['veterinarian_id'] ?? null,
    ])->filter(fn ($value) => $value !== null && $value !== '')->all();

    $prescriptionRouteParams = collect([
        'atendimento' => $attendanceId,
        'patient_id' => $encounter['patient_id'] ?? ($encounter['animal_id'] ?? null),
        'veterinarian_id' => $encounter['veterinarian_id'] ?? null,
    ])->filter(fn ($value) => $value !== null && $value !== '')->all();

    $examRouteParams = collect([
        'attendance' => $attendanceId,
        'exam_id' => $latestExamRequestId,
    ])->filter(fn ($value) => $value !== null && $value !== '')->all();

    $recordActionUrl = $hasRecord
        ? route('vet.records.edit', $latestRecordId)
        : route('vet.records.create', ['attendance' => $attendanceId]);
    $recordActionLabel = $hasRecord ? 'Prontuário' : 'Emitir prontuário';
    $recordActionTitle = $hasRecord ? 'Editar prontuário registrado' : 'Registrar consulta médica';

    $examActionUrl = $hasExamRequests
        ? route('vet.exams.index', $examRouteParams)
        : route('vet.exams.create', $examRouteParams);
    $examActionLabel = 'Exames';
    $examActionTitle = $hasExamRequests ? 'Abrir exames do atendimento' : 'Solicitar exame';

    $canEditPrescription = \Illuminate\Support\Facades\Route::has('vet.prescriptions.edit');
    $canCreatePrescription = \Illuminate\Support\Facades\Route::has('vet.prescriptions.create');
    $prescriptionActionUrl = null;
    if ($hasPrescriptions && $canEditPrescription) {
        $prescriptionActionUrl = route('vet.prescriptions.edit', $latestPrescriptionId);
    } elseif (!$hasPrescriptions && $canCreatePrescription) {
        $prescriptionActionUrl = route('vet.prescriptions.create', $prescriptionRouteParams);
    }
    $prescriptionActionDisabled = empty($prescriptionActionUrl);

    $vaccinationCreateUrl = route('vet.vaccinations.create', $vaccinationRouteParams);
    $vaccinationApplyUrl = $hasVaccinations ? route('vet.vaccinations.apply', $latestVaccinationId) : null;
    $vaccinationEditUrl = $hasVaccinations ? route('vet.vaccinations.edit', $latestVaccinationId) : null;

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
        'attendance' => $attendanceId,
        'patient' => $encounter['patient_id'] ?? null,
    ])->filter(fn ($value) => $value !== null && $value !== '')->all();

    $hospitalizationCreateUrl = \Illuminate\Support\Facades\Route::has('vet.hospitalizations.create')
        ? route('vet.hospitalizations.create', $hospitalizationRouteParams)
        : null;

    $activeHospitalization = data_get($encounter, 'active_hospitalization');
    $hospitalizationViewUrl = null;

    if ($activeHospitalization && !empty($activeHospitalization['id']) && \Illuminate\Support\Facades\Route::has('vet.hospitalizations.status.index')) {
        $hospitalizationViewUrl = route('vet.hospitalizations.status.index', $activeHospitalization['id']);
    }

    $hospitalizationActionUrl = $hospitalizationViewUrl ?? $hospitalizationCreateUrl;
    $hospitalizationActionLabel = 'Internação';
    $hospitalizationActionTitle = $hospitalizationViewUrl ? 'Ver internação ativa' : 'Enviar para internação';

    $billingInfo = $encounter['billing'] ?? null;
    $hasBilling = filled($billingInfo);
    $billingTitle = $hasBilling ? 'Editar faturamento' : 'Faturar atendimento';

    $actionCards = [
        [
            'label' => 'Histórico',
            'url' => route('vet.atendimentos.history', $attendanceId),
            'title' => 'Visualizar histórico do atendimento',
            'icon' => 'ri-history-line',
            'textClass' => 'text-secondary',
            'disabled' => false,
        ],
        [
            'label' => 'Atendimento',
            'url' => route('vet.atendimentos.edit', $attendanceId),
            'title' => 'Editar atendimento',
            'icon' => 'ri-edit-line',
            'textClass' => 'text-success',
            'disabled' => false,
        ],
        [
            'label' => $hospitalizationActionLabel,
            'url' => $hospitalizationActionUrl,
            'title' => $hospitalizationActionTitle,
            'icon' => 'ri-hotel-bed-line',
            'textClass' => 'text-info',
            'disabled' => empty($hospitalizationActionUrl),
        ],
        [
            'label' => 'Vacinas',
            'url' => $vaccinationCreateUrl,
            'title' => 'Agendar vacinação para este atendimento',
            'icon' => 'ri-syringe-line',
            'textClass' => 'text-primary',
            'disabled' => false,
        ],
        [
            'label' => $examActionLabel,
            'url' => $examActionUrl,
            'title' => $examActionTitle,
            'icon' => 'ri-flask-line',
            'textClass' => 'text-warning',
            'disabled' => false,
        ],
        [
            'label' => 'Receitas',
            'url' => $prescriptionActionUrl,
            'title' => $hasPrescriptions ? 'Editar prescrição vinculada a este atendimento' : 'Emitir prescrição',
            'icon' => 'ri-capsule-line',
            'textClass' => 'text-success',
            'disabled' => $prescriptionActionDisabled,
        ],
        [
            'label' => $recordActionLabel,
            'url' => $recordActionUrl,
            'title' => $recordActionTitle,
            'icon' => 'ri-file-list-3-line',
            'textClass' => 'text-info',
            'disabled' => false,
        ],
        [
            'label' => 'Faturamento',
            'url' => route('vet.atendimentos.billing', $attendanceId),
            'title' => $billingTitle,
            'icon' => 'ri-money-dollar-circle-line',
            'textClass' => 'text-secondary',
            'disabled' => false,
        ],
    ];

    if ($vaccinationApplyUrl) {
        $actionCards[] = [
            'label' => 'Aplicar vacina',
            'url' => $vaccinationApplyUrl,
            'title' => 'Aplicar vacina agendada',
            'icon' => 'ri-syringe-line',
            'textClass' => 'text-primary',
            'disabled' => false,
        ];
    }

    if ($vaccinationEditUrl) {
        $actionCards[] = [
            'label' => 'Editar vacina',
            'url' => $vaccinationEditUrl,
            'title' => 'Editar vacinação vinculada a este atendimento',
            'icon' => 'ri-syringe-line',
            'textClass' => 'text-primary',
            'disabled' => false,
        ];
    }

    if ($vaccinationCardUrl) {
        $actionCards[] = [
            'label' => 'Cartão',
            'url' => $vaccinationCardUrl,
            'title' => 'Visualizar cartão de vacina do paciente',
            'icon' => 'ri-id-card-line',
            'textClass' => 'text-primary',
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
            'disabled' => false,
        ];
    }
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="text-color mb-0">Ações</h5>
            <small class="text-muted">Opções rápidas deste atendimento.</small>
        </div>
        <form
            action="{{ route('vet.atendimentos.destroy', $attendanceId) }}"
            method="POST"
            id="form-delete-encounter-{{ $attendanceId }}"
            class="d-inline"
        >
            @csrf
            @method('delete')
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Excluir atendimento">
                <i class="ri-delete-bin-6-line"></i>
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="row row-cols-2 row-cols-md-2 row-cols-xl-3 g-3">
            @foreach ($actionCards as $card)
                <div class="col">
                    @php
                        $isDisabled = !empty($card['disabled']);
                        $url = $card['url'] ?? '#';
                    @endphp
                    @if ($isDisabled)
                        <div class="card h-100 border-0 shadow-sm opacity-50" title="{{ $card['title'] ?? '' }}">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                <div class="mb-2">
                                    <i class="{{ $card['icon'] ?? 'ri-apps-line' }} fs-2 {{ $card['textClass'] ?? 'text-secondary' }}"></i>
                                </div>
                                <div class="fw-semibold text-color">{{ $card['label'] }}</div>
                                <div class="small text-muted">Indisponível</div>
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ $url }}"
                            class="card h-100 border-0 shadow-sm text-decoration-none"
                            title="{{ $card['title'] ?? '' }}"
                            @if (!empty($card['target'])) target="{{ $card['target'] }}" @endif
                            @if (!empty($card['rel'])) rel="{{ $card['rel'] }}" @endif
                        >
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                <div class="mb-2">
                                    <i class="{{ $card['icon'] ?? 'ri-apps-line' }} fs-2 {{ $card['textClass'] ?? 'text-secondary' }}"></i>
                                </div>
                                <div class="fw-semibold text-color">{{ $card['label'] }}</div>
                            </div>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

