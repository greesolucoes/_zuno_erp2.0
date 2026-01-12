<tr>
    <td>
        @if ($item->status_global == 0)
            <form class="d-flex align-items-center gap-1"
                  action="{{ route('produto-setores.destroy', $item->id) }}"
                  method="POST" id="form-{{ $item->id }}">
                @method('delete')
                @csrf
                <button type="button" title="Deletar Setor"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir Cliente"
                    >
                </button>
                <a class="border-0 m-0 p-0 bg-transparent text-color-back" title="Editar Setor"
                   href="{{ route('produto-setores.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar Setor"
                    >
                </a>
            </form>
        @endif
    </td>
    <td class="text-center">{{ $item->nome_setor }}</td>
    <td class="text-center w-max-min-content">
        @if (!empty($item->status_global))
        <span
            title="Clique para alterar o status para ativo/inativo"
            class="status-dot {{ empty($item->statusEmpresa) || $item->statusEmpresa->status == 1 ? 'on' : 'off' }} pointer dot-{{ $item->id }}"
            data-id="{{ $item->id }}"
            data-status="{{ $item->statusEmpresa?->status ?? 1 }}"
        >
        </span>
        @endif
    </td>
</tr>
