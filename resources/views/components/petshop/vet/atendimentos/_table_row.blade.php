@php
    $page = request()->query('page', 1);

    $recordsCount = (int) ($encounter['records_count'] ?? 0);
    $prescriptionsCount = (int) ($encounter['prescriptions_count'] ?? 0);
    $examRequestsCount = (int) ($encounter['exam_requests_count'] ?? 0);
    $vaccinationsCount = (int) ($encounter['vaccinations_count'] ?? 0);
    $billingInfo = $encounter['billing'] ?? null;
    $hasBilling = filled($billingInfo);

    $hasRecord = $recordsCount > 0 || !empty(data_get($encounter, 'latest_record.id'));
    $hasPrescription = $prescriptionsCount > 0 || !empty(data_get($encounter, 'latest_prescription.id'));
    $hasExamRequest = $examRequestsCount > 0 || !empty(data_get($encounter, 'latest_exam_request.id'));
    $hasVaccination = $vaccinationsCount > 0 || !empty(data_get($encounter, 'latest_vaccination.id'));
@endphp

<tr>
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
    <td style="min-width: max-content; width: auto;">
        <div class="d-flex align-items-center gap-2">
            <div class="vet-encounters-row-actions">
                <a
                    href="{{ route('vet.atendimentos.history', [$encounter['id'], 'page' => $page]) }}"
                    class="btn btn-sm btn-outline-primary vet-encounters-row-btn vet-encounters-row-btn--actions"
                    title="Ações do atendimento"
                >
                    <i class="bx bx-menu"></i>
                    Ações
                </a>

                <div class="vet-encounters-row-indicators">
                    <span
                        class="vet-encounters-row-indicator {{ $hasRecord ? 'is-linked' : 'is-new' }}"
                        title="{{ $hasRecord ? 'Prontuário vinculado' : 'Sem prontuário' }}"
                    >
                        <i class="bx bx-notepad"></i>
                        @if ($recordsCount > 0)
                            <span class="vet-encounters-row-indicator__count">{{ $recordsCount }}</span>
                        @endif
                    </span>
                    <span
                        class="vet-encounters-row-indicator {{ $hasExamRequest ? 'is-linked' : 'is-new' }}"
                        title="{{ $hasExamRequest ? 'Exames vinculados' : 'Sem exames' }}"
                    >
                        <i class="bx bx-test-tube"></i>
                        @if ($examRequestsCount > 0)
                            <span class="vet-encounters-row-indicator__count">{{ $examRequestsCount }}</span>
                        @endif
                    </span>
                    <span
                        class="vet-encounters-row-indicator {{ $hasPrescription ? 'is-linked' : 'is-new' }}"
                        title="{{ $hasPrescription ? 'Receitas vinculadas' : 'Sem receitas' }}"
                    >
                        <i class="bx bx-receipt"></i>
                        @if ($prescriptionsCount > 0)
                            <span class="vet-encounters-row-indicator__count">{{ $prescriptionsCount }}</span>
                        @endif
                    </span>
                    <span
                        class="vet-encounters-row-indicator {{ $hasVaccination ? 'is-linked' : 'is-new' }}"
                        title="{{ $hasVaccination ? 'Vacinas vinculadas' : 'Sem vacinas' }}"
                    >
                        <i class="bx bx-first-aid"></i>
                        @if ($vaccinationsCount > 0)
                            <span class="vet-encounters-row-indicator__count">{{ $vaccinationsCount }}</span>
                        @endif
                    </span>

                    <span class="vet-encounters-row-indicators__divider" aria-hidden="true"></span>

                    <a
                        href="{{ route('vet.atendimentos.billing', $encounter['id']) }}"
                        class="text-decoration-none"
                        title="{{ $hasBilling ? 'Cobrança cadastrada' : 'Sem cobrança (faturamento)' }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <span class="vet-encounters-row-indicator is-finance {{ $hasBilling ? 'is-billed' : '' }}">
                            <i class="bx bx-money"></i>
                        </span>
                    </a>
                </div>
            </div>

            <form
                action="{{ route('vet.atendimentos.destroy', $encounter['id']) }}"
                method="POST"
                id="form-delete-{{ $encounter['id'] }}"
                class="d-inline"
            >
                @csrf
                @method('delete')
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger vet-encounters-row-btn vet-encounters-row-btn--icon vet-encounters-row-btn--delete btn-delete"
                    title="Excluir atendimento"
                >
                    <i class="bx bx-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
