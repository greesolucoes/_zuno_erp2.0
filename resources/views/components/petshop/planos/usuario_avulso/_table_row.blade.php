<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('petshop.planos.usuarios-avulso.destroy', ['avulsoUser' => $item->id]) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Usuário">
                <img height="26" width="26" src="/assets/images/svg/icone excluir.svg" alt="Excluir Usuário">
            </button>
            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Usuário"
                href="{{ route('petshop.planos.usuarios-avulso.edit', ['avulsoUser' => $item->id, 'page' => request()->query('page', 1)]) }}">
                <img height="26" width="26" src="/assets/images/svg/icone editar nfe.svg" alt="Editar Usuário">
            </a>
        </form>
    </td>
    <td class="text-center">{{ $item->name }}</td>
    <td class="text-center">{{ $item->email }}</td>
    <td class="text-center">{{ $item->created_at->format('d/m/Y H:i') }}</td>
</tr>
