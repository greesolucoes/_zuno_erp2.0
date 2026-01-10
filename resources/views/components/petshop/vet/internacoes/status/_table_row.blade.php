@php($evolutionVariant = match ($statusRecord->evolucao) {
    'sim' => 'success',
    'nao' => 'danger',
    default => 'secondary',
})

<tr>
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
    <td>
        <form action="{{ route('vet.hospitalizations.status.destroy', [$internacao, $statusRecord]) }}" method="post" id="form-{{ $statusRecord->id }}">
            @method('delete')
            <a href="{{ route('vet.hospitalizations.status.edit', [$internacao, $statusRecord]) }}" class="btn btn-warning btn-sm text-white" title="Editar status">
                <i class="bx bx-edit"></i>
            </a>
            @csrf
            <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir status">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
