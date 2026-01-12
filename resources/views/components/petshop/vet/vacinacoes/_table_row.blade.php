@php(
    $modalId = $modalId ?? ('vet-vacc-modal-' . \Illuminate\Support\Str::slug($vaccination['code'] ?? uniqid()))
)
@php($canApply = $canApply ?? true)

<tr>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $vaccination['patient'] ?? '—' }}</div>
        <div class="text-muted small">
            {{ $vaccination['species'] ?? '—' }}
            @if(!empty($vaccination['breed']))
                • {{ $vaccination['breed'] }}
            @endif
            @if(!empty($vaccination['tutor']))
                • Tutor: {{ $vaccination['tutor'] }}
            @endif
        </div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ data_get($vaccination, 'vaccine.name', '—') }}</div>
        <div class="text-muted small">{{ data_get($vaccination, 'vaccine.dose', '') }}</div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $vaccination['scheduled_at'] ?? '—' }}</div>
        <div class="text-muted small">{{ $vaccination['clinic_room'] ?? '—' }}</div>
    </td>
    <td class="text-center">
        <div class="fw-semibold text-color">{{ $vaccination['veterinarian'] ?? '—' }}</div>
    </td>
    <td class="text-center">
        <span class="badge p-2 fw-semibold bg-{{ $vaccination['status_color'] ?? 'secondary' }} text-uppercase">
            {{ $vaccination['status'] ?? '—' }}
        </span>
    </td>
    <td>
        <div class="text-color fw-semibold">{{ $vaccination['next_due'] ?? '—' }}</div>
        <div class="text-muted small">
            {{ \Illuminate\Support\Str::limit($vaccination['observations'] ?? 'Sem observações adicionais.', 60) }}
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center flex-wrap gap-1">
            <button
                type="button"
                class="btn btn-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}"
                title="Visualizar detalhes da vacinação"
            >
                <i class="bx bx-show"></i>
            </button>

            @if($canApply && !empty($vaccination['id']))
                <a href="{{ route('vet.vaccinations.apply', $vaccination['id']) }}" class="btn btn-success btn-sm" title="Registrar aplicação">
                    <i class="bx bx-plus-medical"></i>
                </a>
            @endif
            @if(!empty(data_get($vaccination, 'attendance.url')))
                <a href="{{ data_get($vaccination, 'attendance.url') }}" class="btn btn-info btn-sm text-white" title="Abrir atendimento vinculado">
                    <i class="bx bx-calendar-check"></i>
                </a>
            @endif
        </div>
    </td>
</tr>
