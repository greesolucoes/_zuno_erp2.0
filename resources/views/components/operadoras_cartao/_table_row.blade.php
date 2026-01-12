<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('operadoras-cartao.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button 
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Operadora"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Operadora"
                >
            </button>
            <a 
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Operadora"
                href="{{ route('operadoras-cartao.edit', [$item->id, 'page' => request()->query('page', 1)]) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Operadora"
                >
            </a>

            <button 
                type="button" 
                title="Visualilizar Dados do Operadora" 
                class="border-0 mx-1 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_operadora-{{ $item->id }}"
                data-id="{{$item->id}}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Operadora"
                >
            </button>
        </form>
    </td>
    <td class="text-center">
        {{ $item->nome }}
    </td>
    <td class="text-center text-orange">
        <b>{{ __moeda($item->juros_debito) }}%</b>
    </td>
    <td class="text-center text-orange">
        <b>{{ __moeda($item->juros_credito) }}%</b>
    </td>
    <td class="text-center text-purple">
        <b>{{ $item->limite_parcelas_sem_acrescimo }}x</b>
    </td>
    <td class="text-center text-purple">
        <b>{{ __moeda($item->acrescimo_parcelamento) }}%</b>
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

@include('modals._view_operadora', ['item' => $item])