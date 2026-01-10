<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('petshop.planos.usuarios-plano.destroy', ['planoUser' => $item->id]) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf
            <input type="hidden" name="inp-plano_user_id" value="{{ $item->id }}">

            <button
            type="button"
            class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
            title="Excluir Usuário">
            <img height="26" width="26" src="/assets/images/svg/icone excluir.svg" alt="Excluir Usuário">
            </button>


            @if ($item->plano_id)
            <a
                type="button"
                title="Cancelar Plano"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn_cancelar_plano"
                data-bs-toggle="modal"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/cancelar plano.svg"
                    alt="Cancelar Plano"
                >
            </a>
        @endif

        @if ($item->plano_id)
            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Usuário"
                href="{{ route('petshop.planos.usuarios-plano.edit', ['planoUser' => $item->id, 'page' => request()->query('page', 1)]) }}">
                <img height="26" width="26" src="/assets/images/svg/icone editar nfe.svg" alt="Editar Usuário">
            </a>
        @endif

        @if ($item->plano_id)
            <a
                type="button"
                title="Renovar Plano"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn_renovar_plano"
                data-bs-toggle="modal"
                >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone renovar.svg"
                    alt="Renovar Plano"
                >
            </a>
            @endif

             <a
                type="button"
                title="Histórico do Plano"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_plano_user-{{ $item->id }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone historico.svg"
                    alt="Histórico de mensalidades"
                >
            </a>

            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn_reenviar_acesso"
                title="Reenviar dados de acesso">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone envio email e zap.svg"
                    alt="Reenviar dados de acesso"
                >
            </button>

        </form>
    </td>
    <td class="text-center">{{ $item->name }}</td>
    <td class="text-center">{{ $item->email }}</td>
    <td class="text-center">{{ $item->plano?->nome ?? 'Cancelado' }}</td>
    <td class="text-center">{{ $item->created_at->format('d/m/Y H:i') }}</td>
</tr>
