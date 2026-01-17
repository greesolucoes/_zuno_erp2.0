@php
    $latestRecordId = data_get($encounter, 'latest_record.id');
    $attendanceId = $encounter['id'] ?? null;
    $latestExamRequestId = data_get($encounter, 'latest_exam_request.id');
    $latestPrescriptionId = data_get($encounter, 'latest_prescription.id');
    $recordsCount = (int) data_get($encounter, 'records_count', 0);
    $prescriptionsCount = (int) data_get($encounter, 'prescriptions_count', 0);
    $examRequestsCount = (int) data_get($encounter, 'exam_requests_count', 0);
    $latestVaccinationId = data_get($encounter, 'latest_vaccination.id');
    $vaccinationsCount = (int) data_get($encounter, 'vaccinations_count', 0);

    $hasRecord = $recordsCount > 0 || !empty($latestRecordId);
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

    $patientId = $encounter['patient_id'] ?? ($encounter['animal_id'] ?? null);
    $patientCrmUrl = null;
    if (!empty($patientId) && \Illuminate\Support\Facades\Route::has('animais.pacientes.crm')) {
        $patientCrmUrl = route('animais.pacientes.crm', $patientId);
    }

    $billingInfo = $encounter['billing'] ?? null;
    $hasBilling = filled($billingInfo);
    $billingTitle = $hasBilling ? 'Editar faturamento' : 'Faturar atendimento';

    $indicatorNew = ['label' => 'Novo', 'class' => 'is-new', 'title' => 'Nenhum registro vinculado'];
    $recordIndicator = $hasRecord
        ? [
            'label' => (string) max(1, $recordsCount),
            'class' => 'is-linked',
            'title' => 'Já possui prontuário vinculado',
        ]
        : $indicatorNew;
    $prescriptionIndicator = $hasPrescriptions
        ? [
            'label' => (string) max(1, $prescriptionsCount),
            'class' => 'is-linked',
            'title' => 'Já possui receita vinculada',
        ]
        : $indicatorNew;
    $examIndicator = $hasExamRequests
        ? [
            'label' => (string) max(1, $examRequestsCount),
            'class' => 'is-linked',
            'title' => 'Já possui solicitação de exame vinculada',
        ]
        : $indicatorNew;
    $vaccinationIndicator = $hasVaccinations
        ? [
            'label' => (string) max(1, $vaccinationsCount),
            'class' => 'is-linked',
            'title' => 'Já possui vacinação vinculada',
        ]
        : $indicatorNew;
    $hospitalizationIndicator = $hospitalizationViewUrl
        ? [
            'label' => 'Ativa',
            'class' => 'is-linked',
            'title' => 'Já possui internação ativa',
        ]
        : [
            'label' => 'Nova',
            'class' => 'is-new',
            'title' => 'Sem internação ativa',
        ];

    $actionCards = [
        [
            'label' => 'Histórico',
            'url' => $patientCrmUrl,
            'title' => 'Visualizar histórico (CRM) do paciente',
            'icon' => 'bx bx-history',
            'textClass' => 'text-secondary',
            'disabled' => empty($patientCrmUrl),
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
        [
            'label' => 'Atendimento',
            'url' => route('vet.atendimentos.edit', $attendanceId),
            'title' => 'Editar atendimento',
            'icon' => 'bx bx-edit',
            'textClass' => 'text-success',
            'disabled' => false,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
        [
            'label' => $hospitalizationActionLabel,
            'url' => $hospitalizationActionUrl,
            'title' => $hospitalizationActionTitle,
            'icon' => 'bx bx-bed',
            'textClass' => 'text-info',
            'disabled' => empty($hospitalizationActionUrl),
            'indicator' => $hospitalizationIndicator,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
        [
            'label' => 'Vacinas',
            'url' => $vaccinationCreateUrl,
            'title' => 'Agendar vacinação para este atendimento',
            'icon' => 'bx bx-first-aid',
            'textClass' => 'text-primary',
            'disabled' => false,
            'indicator' => $vaccinationIndicator,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
        [
            'label' => $examActionLabel,
            'url' => $examActionUrl,
            'title' => $examActionTitle,
            'icon' => 'bx bx-test-tube',
            'textClass' => 'text-warning',
            'disabled' => false,
            'indicator' => $examIndicator,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
        [
            'label' => 'Receitas',
            'url' => $prescriptionActionUrl,
            'title' => $hasPrescriptions ? 'Editar prescrição vinculada a este atendimento' : 'Emitir prescrição',
            'icon' => 'bx bx-receipt',
            'textClass' => 'text-success',
            'disabled' => $prescriptionActionDisabled,
            'indicator' => $prescriptionActionDisabled ? null : $prescriptionIndicator,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
        [
            'label' => $recordActionLabel,
            'url' => $recordActionUrl,
            'title' => $recordActionTitle,
            'icon' => 'bx bx-notepad',
            'textClass' => 'text-info',
            'disabled' => false,
            'indicator' => $recordIndicator,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
    ];

    $financeCards = [
        [
            'label' => 'Faturamento',
            'url' => route('vet.atendimentos.billing', $attendanceId),
            'title' => $billingTitle,
            'icon' => 'bx bx-money',
            'textClass' => 'text-secondary',
            'disabled' => false,
            'variant' => 'finance',
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
    ];

    if ($vaccinationApplyUrl) {
        $actionCards[] = [
            'label' => 'Aplicar vacina',
            'url' => $vaccinationApplyUrl,
            'title' => 'Aplicar vacina agendada',
            'icon' => 'bx bx-vial',
            'textClass' => 'text-primary',
            'disabled' => false,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ];
    }

    if ($vaccinationEditUrl) {
        $actionCards[] = [
            'label' => 'Editar vacina',
            'url' => $vaccinationEditUrl,
            'title' => 'Editar vacinação vinculada a este atendimento',
            'icon' => 'bx bx-vial',
            'textClass' => 'text-primary',
            'disabled' => false,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ];
    }

    if ($vaccinationCardUrl) {
        $actionCards[] = [
            'label' => 'Cartão',
            'url' => $vaccinationCardUrl,
            'title' => 'Visualizar cartão de vacina do paciente',
            'icon' => 'bx bx-id-card',
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
                <i class="bx bx-trash"></i>
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
                                <div class="vet-encounter-actions__icon mb-2">
                                    <i class="{{ $card['icon'] ?? 'bx bx-grid-alt' }} vet-encounter-actions__glyph {{ $card['textClass'] ?? 'text-secondary' }}"></i>
                                </div>
                                <div class="fw-semibold text-color">{{ $card['label'] }}</div>
                                <div class="small text-muted">Indisponível</div>
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ $url }}"
                            class="card h-100 border-0 shadow-sm text-decoration-none vet-encounter-actions__card {{ ($card['variant'] ?? null) === 'finance' ? 'vet-encounter-actions__card--finance' : '' }}"
                            title="{{ $card['title'] ?? '' }}"
                            @if (!empty($card['target'])) target="{{ $card['target'] }}" @endif
                            @if (!empty($card['rel'])) rel="{{ $card['rel'] }}" @endif
                        >
                            @if (!empty($card['indicator']))
                                <span
                                    class="vet-encounter-actions__indicator {{ $card['indicator']['class'] ?? '' }}"
                                    title="{{ $card['indicator']['title'] ?? '' }}"
                                >
                                    {{ $card['indicator']['label'] ?? '' }}
                                </span>
                            @endif
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                <div class="vet-encounter-actions__icon mb-2">
                                    <i class="{{ $card['icon'] ?? 'bx bx-grid-alt' }} vet-encounter-actions__glyph {{ $card['textClass'] ?? 'text-secondary' }}"></i>
                                </div>
                                <div class="fw-semibold text-color">{{ $card['label'] }}</div>
                            </div>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        @if (!empty($financeCards))
            <div class="vet-encounter-actions__divider"></div>
            <div class="vet-encounter-actions__section-title">Financeiro</div>
            <div class="row row-cols-2 row-cols-md-2 row-cols-xl-3 g-3 mt-1">
                @foreach ($financeCards as $card)
                    <div class="col">
                        @php
                            $isDisabled = !empty($card['disabled']);
                            $url = $card['url'] ?? '#';
                        @endphp
                        @if ($isDisabled)
                            <div class="card h-100 border-0 shadow-sm opacity-50" title="{{ $card['title'] ?? '' }}">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                    <div class="vet-encounter-actions__icon mb-2">
                                        <i class="{{ $card['icon'] ?? 'bx bx-grid-alt' }} vet-encounter-actions__glyph {{ $card['textClass'] ?? 'text-secondary' }}"></i>
                                    </div>
                                    <div class="fw-semibold text-color">{{ $card['label'] }}</div>
                                    <div class="small text-muted">Indisponível</div>
                                </div>
                            </div>
                        @else
                            <a
                                href="{{ $url }}"
                                class="card h-100 border-0 shadow-sm text-decoration-none vet-encounter-actions__card vet-encounter-actions__card--finance"
                                title="{{ $card['title'] ?? '' }}"
                                @if (!empty($card['target'])) target="{{ $card['target'] }}" @endif
                                @if (!empty($card['rel'])) rel="{{ $card['rel'] }}" @endif
                            >
                                <span class="vet-encounter-actions__tag vet-encounter-actions__tag--finance" title="Cobrança do atendimento">
                                    Cobrança
                                </span>
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                    <div class="vet-encounter-actions__icon mb-2">
                                        <i class="{{ $card['icon'] ?? 'bx bx-grid-alt' }} vet-encounter-actions__glyph {{ $card['textClass'] ?? 'text-secondary' }}"></i>
                                    </div>
                                    <div class="fw-semibold text-color">{{ $card['label'] }}</div>
                                </div>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
