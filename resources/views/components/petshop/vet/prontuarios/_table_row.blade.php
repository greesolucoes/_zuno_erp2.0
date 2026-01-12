@php
    if (!isset($modalId)) {
        $modalId = 'vet-record-modal-' . \Illuminate\Support\Str::slug($record['code'] ?? uniqid());
    }

    $deleteFormId = 'vet-record-delete-' . \Illuminate\Support\Str::slug($record['code'] ?? $record['id'] ?? uniqid());
@endphp

<tr>
    <td class="text-center">
        <div class="fw-semibold text-color">{{ $record['patient'] ?? '—' }}</div>
        <div class="text-muted small">
            {{ $record['species'] ?? '—' }}
            @if(!empty($record['breed']))
                • {{ $record['breed'] }}
            @endif
        </div>
        <div class="text-muted small">{{ $record['code'] ?? '—' }}</div>
    </td>
    <td class="text-center">
        <div class="fw-semibold text-color">{{ $record['tutor'] ?? '—' }}</div>
        @if(!empty($record['contact']))
            <div class="text-muted small">{{ $record['contact'] }}</div>
        @endif
    </td>
    <td class="text-center">
        <div class="fw-semibold text-color">{{ $record['veterinarian'] ?? '—' }}</div>
        @if(!empty($record['team']))
            <div class="text-muted small">{{ $record['team'] }}</div>
        @endif
    </td>
    <td class="text-center">
        <span class="badge vet-prontuarios__badge-soft-{{ $record['status_color'] ?? 'primary' }}">
            {{ $record['status'] ?? '—' }}
        </span>
    </td>
    <td class="text-center">
        <span class="badge vet-prontuarios__badge-outline-{{ $record['type_color'] ?? 'info' }}">
            {{ $record['type'] ?? '—' }}
        </span>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $record['updated_at'] ?? '—' }}</div>
        <div class="text-muted small">{{ $record['clinic_room'] ?? '—' }}</div>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('vet.records.edit', $record['id']) }}" class="btn btn-warning btn-sm text-white" title="Editar prontuário">
                <i class="bx bx-edit"></i>
            </a>
            <form id="{{ $deleteFormId }}" action="{{ route('vet.records.destroy', $record['id']) }}" method="post" class="d-inline">
                @method('delete')
                @csrf
                <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir prontuário">
                    <i class="bx bx-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
