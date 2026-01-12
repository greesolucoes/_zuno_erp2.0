<tr>
    <td class="align-middle text-start">
        <p class="m-0 p-0 fw-semibold">{{ $item->nome }}</p>
    </td>
    <td class="text-center fw-semibold">{{ $statusOptions[$item->status] ?? ucfirst($item->status) }}</td>
    <td class="text-center">{{ optional($item->updated_at)->format('d/m/Y H:i') ?? '--' }}</td>
    <td class="align-middle text-start">
        <small class="text-muted d-block">{{ $item->descricao ? \Illuminate\Support\Str::limit($item->descricao, 120) : 'Sem descrição clínica cadastrada.' }}</small>
        @if ($item->orientacoes)
            <small class="text-muted d-block mt-1"><strong>Planos e cuidados:</strong> {{ \Illuminate\Support\Str::limit($item->orientacoes, 120) }}</small>
        @endif
    </td>
    <td class="align-middle">
        <form action="{{ route('vet.chronic-conditions.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            <a href="{{ route('vet.chronic-conditions.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                <i class="bx bx-edit"></i>
            </a>
            @csrf
            <button type="button" class="btn btn-delete btn-sm btn-danger">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
