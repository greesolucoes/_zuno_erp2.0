<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('petshop.planos.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf
            <button type="button" class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete" title="Excluir Plano">
                <img height="26" width="26" src="/assets/images/svg/icone excluir.svg" alt="Excluir Plano">
            </button>
            <a class="border-0 m-0 p-0 bg-transparent text-color-back" title="Editar Plano" href="{{ route('petshop.planos.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img height="26" width="26" src="/assets/images/svg/icone editar nfe.svg" alt="Editar Plano">
            </a>
        </form>
    </td>
    <td class="text-center">{{ $item->slug }}</td>
    <td class="text-center">{{ $item->nome }}</td>
    <td class="text-center">{{ $item->ativo ? 'Sim' : 'Não' }}</td>
</tr>