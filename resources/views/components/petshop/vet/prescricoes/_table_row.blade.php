<tr>
    <td class="text-center align-middle">
        <p class="m-0 p-0 fw-semibold text-color">{{ $prescription['patient'] ?? '—' }}</p>
        <small class="text-muted d-block">
            {{ $prescription['species'] ?? '—' }}
            @if(!empty($prescription['breed']))
                • {{ $prescription['breed'] }}
            @endif
        </small>
        <small class="text-muted d-block">{{ $prescription['code'] ?? '—' }}</small>
    </td>
    <td class="text-center align-middle">
        <p class="m-0 p-0 fw-semibold text-color">{{ $prescription['tutor'] ?? '—' }}</p>
    </td>
    <td class="text-center align-middle">
        <span class="badge bg-{{ $prescription['status_color'] ?? 'primary' }}-subtle text-{{ $prescription['status_color'] ?? 'primary' }} text-uppercase">
            {{ $prescription['status'] ?? '—' }}
        </span>
    </td>
    <td class="text-center align-middle">
        <span class="badge rounded-pill border border-{{ $prescription['priority_color'] ?? 'primary' }} text-{{ $prescription['priority_color'] ?? 'primary' }}">
            {{ $prescription['priority'] ?? '—' }}
        </span>
    </td>
    <td class="text-center align-middle">
        <p class="m-0 p-0 fw-semibold text-color">{{ $prescription['next_revalidation'] ?? '—' }}</p>
        <small class="text-muted d-block">
            Válida até {{ $prescription['valid_until'] ?? '—' }}
        </small>
    </td>
    <td class="text-center align-middle">
        <p class="text-muted small mb-1">
            {{ \Illuminate\Support\Str::limit($prescription['summary'] ?? 'Sem observações adicionais.', 90) }}
        </p>
        <div class="text-muted small">
            <span class="me-3">
                <i class="ri-attachment-2 me-1"></i>{{ $prescription['attachments'] ?? 0 }}
            </span>
            <span>
                <i class="ri-repeat-line me-1"></i>{{ $prescription['refills'] ?? 0 }}
            </span>
        </div>
    </td>
    <td>
        @if (!empty($prescription['id']))
            <form action="{{ route('vet.prescriptions.destroy', $prescription['id']) }}" method="post" id="form-{{ $prescription['id'] }}">
                @method('delete')
                <a href="{{ route('vet.prescriptions.edit', [$prescription['id'], 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white" title="Editar prescrição">
                    <i class="bx bx-edit"></i>
                </a>
                @csrf
                <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir prescrição">
                    <i class="bx bx-trash"></i>
                </button>
            </form>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
</tr>
