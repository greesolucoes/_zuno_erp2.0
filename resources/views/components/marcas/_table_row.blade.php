<tr>
    <td>
        <form class="d-flex align-items-center gap-1"
              action="{{ route('marcas.destroy', $item->id) }}"
              method="POST"
              id="form-{{ $item->id }}">
            @csrf
            @method('delete')
            @can('marcas_delete')
                <button type="button" title="Deletar"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir Cliente"
                    >
                </button>
            @endcan
            @can('marcas_edit')
                <a class="border-0 m-0 p-0 bg-transparent text-color-back" title="Editar"
                   href="{{ route('marcas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar Marcas"
                    >
                </a>
            @endcan
        </form>
    </td>

    <td class="text-center">{{ $item->nome }}</td>
    <td class="text-center">{{ $item->produtos->count() }}</td>
</tr>
