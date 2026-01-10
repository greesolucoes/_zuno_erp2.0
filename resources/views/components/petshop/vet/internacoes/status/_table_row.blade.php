@php($evolutionVariant = match ($statusRecord->evolucao) {
    'sim' => 'success',
    'nao' => 'danger',
    default => 'secondary',
})

<tr>
    <td>
        <form
            class="d-flex align-items-center gap-1"
            action="{{ route('vet.hospitalizations.status.destroy', [$internacao, $statusRecord]) }}"
            method="post"
        >
            @csrf
            @method('delete')

            <button
                type="submit"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Excluir status"
                onclick="return confirm('Deseja realmente remover este status da internação?')"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir status"
                >
            </button>

            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar status"
                href="{{ route('vet.hospitalizations.status.edit', [$internacao, $statusRecord]) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar status"
                >
            </a>
        </form>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $statusRecord->status }}</div>
    </td>
    <td class="text-start">
        <div class="text-muted small">
            {{ $statusRecord->anotacao ? \Illuminate\Support\Str::limit($statusRecord->anotacao, 180) : '—' }}
        </div>
    </td>
    <td class="text-center">
        <span class="badge p-1 fw-semibold text-bg-{{ $evolutionVariant }}">{{ $statusRecord->evolucao_label }}</span>
    </td>
    <td>
        {{ optional($statusRecord->created_at)->format('d/m/Y') ?? '—' }}<br>
        <small>{{ optional($statusRecord->created_at)->format('H:i') ?? '—' }}</small>
    </td>
    <td>
        {{ optional($statusRecord->updated_at)->format('d/m/Y') ?? '—' }}<br>
        <small>{{ optional($statusRecord->updated_at)->format('H:i') ?? '—' }}</small>
    </td>
</tr>