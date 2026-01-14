<tr>
    <td class="text-center">{{ $item->nome }}</td>
    <td class="text-center">{{ $item->especie->nome ?? 'N/A' }}</td> {{-- Exibe o nome da espécie ou 'N/A' --}}
    <td>
        <form action="{{ route('animais.racas.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')

            <a href="{{ route('animais.racas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                <i class="bx bx-edit"></i>
            </a>

            @csrf

            <button type="button" class="btn btn-delete btn-sm btn-danger">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
