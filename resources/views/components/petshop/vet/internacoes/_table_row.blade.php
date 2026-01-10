@php($modalId = $modalId ?? ('vet-hosp-modal-' . ($hospitalization->id ?? uniqid())))
@php($patient = $hospitalization->animal)
@php($tutor = $patient?->cliente)
@php($primaryContact = collect([
    $tutor?->telefone,
    $tutor?->telefone_secundario,
    $tutor?->telefone_terciario,
    $tutor?->contato,
    $tutor?->email,
])->first(fn ($value) => filled($value)))
@php($colorMap = [
    'primary' => 'primary',
    'success' => 'success',
    'warning' => 'warning',
    'danger' => 'danger',
    'secondary' => 'secondary',
    'info' => 'info',
])
@php($statusVariant = $colorMap[$hospitalization->status_color] ?? 'primary')
@php($riskVariant = $colorMap[$hospitalization->risk_color] ?? 'secondary')

<tr>
    <td>
        <div class="d-flex align-items-center gap-1">
            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}"
                title="Visualizar internação"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualizar internação"
                >
            </button>
            
            <a
                href="{{ route('vet.hospitalizations.edit', $hospitalization) }}"
                class="border-0 m-0 p-0 bg-transparent text-color-back d-inline-flex"
                title="Editar internação"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar internação"
                >
            </a>
             <a
                href="{{ route('vet.hospitalizations.status.index', $hospitalization) }}"
                class="border-0 m-0 p-0 bg-transparent text-color-back d-inline-flex"
                title="Status da internação"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/new_orcamento.svg"
                    alt="Status da internação"
                >
            </a>
        </div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $patient?->nome ?? '—' }}</div>
        <div class="text-muted small">
            {{ $patient?->especie?->nome ?? 'Espécie não informada' }}
            @if ($patient?->raca?->nome)
                • {{ $patient->raca->nome }}
            @endif
            @if ($patient?->idade)
                • {{ $patient->idade }} {{ $patient->idade == 1 ? 'ano' : 'anos' }}
            @endif
        </div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">
            {{ $tutor->razao_social ?? $tutor->nome_fantasia ?? $tutor->contato ?? '—' }}
        </div>
        <div class="text-muted small">
            {{ $primaryContact ?? '—' }}
        </div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">
            {{ optional($hospitalization->veterinarian?->funcionario)->nome ?? '—' }}
        </div>
        <div class="text-muted small">{{ $hospitalization->veterinarian?->especialidade ?? '' }}</div>
    </td>
    <td>
        <div class="d-flex align-items-center flex-column gap-1">
            <span class="badge p-1 fw-semibold text-bg-{{ $statusVariant }}" style="width: min-content">
                {{ $hospitalization->status_label }}
            </span>
            <span class="badge p-1 fw-semibold text-bg-{{ $riskVariant }}" style="width: min-content">
                {{ $hospitalization->risk_label }}
            </span>
        </div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $hospitalization->room?->nome ?? '—' }}</div>
        <div class="text-muted small">
            @if ($hospitalization->room?->identificador)
                {{ $hospitalization->room->identificador }}
                @if ($hospitalization->room?->tipo)
                    •
                @endif
            @endif
            {{ $hospitalization->room?->tipo ?? '' }}
        </div>
    </td>
    <td class="text-start">
        {{ optional($hospitalization->internado_em)->format('d/m/Y') ?? '—' }}<br>
        {{ optional($hospitalization->internado_em)->format('H:i') ?? '—' }}
    </td>
    <td class="text-start">
        {{ optional($hospitalization->previsao_alta_em)->format('d/m/Y') ?? '—' }}<br>
        {{ optional($hospitalization->previsao_alta_em)->format('H:i') ?? '—' }}
    </td>
</tr>