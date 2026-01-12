@php
    $statusClasses = [
        'success' => 'badge bg-soft-success text-success',
        'warning' => 'badge bg-soft-warning text-warning',
        'info' => 'badge bg-soft-info text-info',
        'danger' => 'badge bg-soft-danger text-danger',
    ];

    $statusBadgeClass = $statusClasses[$exam['status_badge']] ?? 'badge bg-light text-dark';
@endphp

<tr>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $exam['type'] }}</div>
        <small class="text-muted">Protocolo #{{ $exam['id'] }}</small>
        @if (!empty($exam['attendance']))
            <div class="mt-1">
                <a
                    href="{{ $exam['attendance']['url'] ?? '#' }}"
                    class="badge bg-{{ $exam['attendance']['status_color'] ?? 'primary' }}-subtle text-{{ $exam['attendance']['status_color'] ?? 'primary' }} text-decoration-none"
                >
                    Atendimento {{ $exam['attendance']['code'] ?? '—' }}
                </a>
            </div>
        @endif
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $exam['animal'] }}</div>
        <small class="text-muted">Tutor {{ $exam['guardian'] }}</small>
    </td>
    <td class="text-start">{{ $exam['laboratory'] }}</td>
    <td class="text-start">{{ $exam['veterinarian'] }}</td>
    <td class="text-center">
        <span class="{{ $statusBadgeClass }}">{{ $exam['status'] }}</span>
    </td>
    <td class="text-center">
        {{ $exam['requested_at'] ? \Carbon\Carbon::parse($exam['requested_at'])->format('d/m/Y H:i') : '—' }}
    </td>
    <td class="text-center">
        {{ $exam['completed_at'] ? \Carbon\Carbon::parse($exam['completed_at'])->format('d/m/Y H:i') : '--' }}
    </td>
    <td>
        <form action="{{ route('vet.exams.destroy', $exam['id']) }}" method="post" id="form-{{ $exam['id'] }}">
            @method('delete')

            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $exam['modal_id'] }}" title="Visualizar documentos">
                <i class="bx bx-file"></i>
            </button>

            <a href="{{ $exam['report_url'] }}" class="btn btn-info btn-sm text-white" title="Emitir laudo do exame" aria-label="Emitir laudo do exame">
                <i class="bx bx-receipt"></i>
            </a>

            @if (!empty($exam['form_url']))
                <a href="{{ $exam['form_url'] }}" class="btn btn-success btn-sm" title="Registrar coleta do exame" aria-label="Registrar coleta do exame">
                    <i class="bx bx-edit"></i>
                </a>
            @else
                <button type="button" class="btn btn-success btn-sm" title="Exame já entregue ou disponível" aria-label="Exame já entregue ou disponível" disabled aria-disabled="true">
                    <i class="bx bx-check-circle"></i>
                </button>
            @endif

            @csrf

            <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir solicitação de exame" aria-label="Excluir solicitação de exame">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>

@include('components.petshop.vet.exames.exam-document-modal', [
    'modalId' => $exam['modal_id'],
    'exam' => $exam,
    'documents' => $exam['documents'],
])
