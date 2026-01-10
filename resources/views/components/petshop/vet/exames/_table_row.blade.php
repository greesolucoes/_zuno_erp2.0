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
    <td>
        <form
            class="d-flex align-items-center gap-2"
            action="{{ route('vet.exams.destroy', $exam['id']) }}"
            method="POST"
            id="form-{{ $exam['id'] }}"
        >
            @csrf
            @method('DELETE')

            <button
                type="button"
                class="btn btn-outline-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#{{ $exam['modal_id'] }}"
            >
                <i class="ri-file-search-line"></i>
            </button>
            <a
                href="{{ $exam['report_url'] }}"
                class="btn btn-outline-info btn-sm"
                title="Emitir laudo do exame"
                aria-label="Emitir laudo do exame"
            >
                <i class="ri-sticky-note-line"></i>
            </a>
            @if (!empty($exam['form_url']))
                <a
                    href="{{ $exam['form_url'] }}"
                    class="btn btn-outline-success btn-sm"
                    title="Registrar coleta do exame"
                    aria-label="Registrar coleta do exame"
                >
                    <i class="ri-file-edit-line"></i>
                </a>
            @else
                <button
                    type="button"
                    class="btn btn-outline-success btn-sm"
                    title="Exame já entregue ou disponível"
                    aria-label="Exame já entregue ou disponível"
                    disabled
                    aria-disabled="true"
                >
                    <i class="ri-checkbox-circle-line"></i>
                </button>
            @endif
            <button
                type="button"
                class="btn btn-outline-danger btn-sm btn-delete"
                title="Excluir solicitação de exame"
                aria-label="Excluir solicitação de exame"
            >
                <i class="ri-delete-bin-6-line"></i>
            </button>
        </form>
    </td>
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
</tr>

@include('components.petshop.vet.exames.exam-document-modal', [
    'modalId' => $exam['modal_id'],
    'exam' => $exam,
    'documents' => $exam['documents'],
])