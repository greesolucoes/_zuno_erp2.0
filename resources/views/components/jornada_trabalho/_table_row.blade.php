<tr>
    <td>{{ $item->nome }}</td>
    <td>{{ $item->descricao }}</td>
    <td>
        <form action="{{ route('jornadas-trabalho.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf
            <a class="btn btn-warning btn-sm" title='Editar' href="{{ route('jornadas-trabalho.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <i class="ri-pencil-fill"></i>
            </a>
            <button type="button" title='Excluir' class="btn btn-delete btn-sm btn-danger">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>
    </td>
</tr>