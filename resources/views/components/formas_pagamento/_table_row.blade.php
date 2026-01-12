<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('formas-pagamento.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button 
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Forma de Pagamento"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Forma de Pagamento"
                >
            </button>
            <a 
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Forma de Pagamento"
                href="{{ route('formas-pagamento.edit', [$item->id, 'page' => request()->query('page', 1)]) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Forma de Pagamento"
                >
            </a>

            <button 
                type="button" 
                title="Visualilizar Dados da Forma de Pagamento" 
                class="border-0 mx-1 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_forma_pagamento-{{ $item->id }}"
                data-id="{{$item->id}}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Forma de Pagamento"
                >
            </button>
        </form>
    </td>
    <td class="text-center">
       {{ $item->nome }}
    </td>
    <td class="text-center">
        {{ $item->tipo_pagamento }} - {{ $item::getTipoPagamentoSefaz($item->tipo_pagamento) }}
    </td>
    <td class="text-center w-max-min-content">
        <span 
            title="Clique para alterar o status para ativo/inativo"
            class="status-dot {{ $item->status == '1' ? 'on' : 'off' }} pointer dot-{{ $item->id }}"
            data-id="{{ $item->id }}"
            data-status="{{ $item->status }}">
        </span>
    </td>
    <td>
        <b class="m-0 p-0">Cadastro</b>
        <p class="m-0 p-0">{{ __data_pt($item->created_at) }}</p>
    </td>
</tr>

@include('modals._view_forma_pagamento', ['item' => $item])