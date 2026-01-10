<tr>
    <td class="align-middle text-start">
        <p class="m-0 p-0 fw-semibold">{{ $item->titulo }}</p>
        @if ($item->itens)
            <small class="text-muted">Primeiro item: {{ $item->itens[0] }}</small>
        @endif
    </td>
    <td class="text-center fw-semibold">{{ $statusOptions[$item->status] ?? ucfirst($item->status) }}</td>
    <td class="text-center">{{ is_array($item->itens) ? count($item->itens) : 0 }}</td>
    <td class="text-center">{{ optional($item->updated_at)->format('d/m/Y H:i') ?? '--' }}</td>
    <td class="align-middle text-start">
        <small class="text-muted">{{ $item->descricao ? \Illuminate\Support\Str::limit($item->descricao, 120) : 'Sem descrição cadastrada.' }}</small>
    </td>
    <td>
        <form action="{{ route('vet.checklist.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            <a href="{{ route('vet.checklist.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                <i class="bx bx-edit"></i>
            </a>
            @csrf
            <button type="button" class="btn btn-delete btn-sm btn-danger">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
