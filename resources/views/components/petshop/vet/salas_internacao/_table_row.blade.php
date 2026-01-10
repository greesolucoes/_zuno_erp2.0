<tr>
    <td class="text-center">
        <p class="m-0 p-0 fw-semibold">{{ $item->nome }}</p>
        <small class="text-muted">{{ $item->identificador ?: 'Sem identificador' }}</small>
    </td>
    <td class="text-center">{{ $tiposSala[$item->tipo] ?? ucfirst($item->tipo) }}</td>
    <td class="text-center fw-semibold">{{ $statusSala[$item->status] ?? ucfirst($item->status) }}</td>
    <td class="text-center">{{ $item->capacidade ? $item->capacidade . ' leitos' : '--' }}</td>
    <td class="text-center">{{ optional($item->updated_at)->format('d/m/Y H:i') ?? '--' }}</td>
    <td>
        <form action="{{ route('vet.salas-internacao.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            <a href="{{ route('vet.salas-internacao.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                <i class="bx bx-edit"></i>
            </a>
            @csrf
            <button type="button" class="btn btn-delete btn-sm btn-danger">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
