<tr>
    <td>
        @php($modalTarget = $modalId ?? ('vet-prescription-modal-' . \Illuminate\Support\Str::slug(($prescription['code'] ?? 'prescricao') . '-' . ($prescription['id'] ?? uniqid()))))

        @if (!empty($prescription['id']))
            <form
                class="d-flex align-items-center gap-1"
                action="{{ route('vet.prescriptions.destroy', $prescription['id']) }}"
                method="post"
                id="form-{{ $prescription['id'] }}"
            >
                @csrf
                @method('delete')

                <button
                    type="button"
                    class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                    title="Excluir prescrição"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir prescrição"
                    >
                </button>

                <a
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    title="Editar prescrição"
                    href="{{ route('vet.prescriptions.edit', [$prescription['id'], 'page' => request()->query('page', 1)]) }}"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar prescrição"
                    >
                </a>

               
            </form>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
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
</tr>