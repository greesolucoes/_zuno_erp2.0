<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('operadores-caixa.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Operador"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Operador"
                >
            </button>
            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Operador"
                href="{{ route('operadores-caixa.edit', $item->id) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Operador"
                >
            </a>

            <button
                type="button"
                title="Visualilizar Dados do Operador"
                class="border-0 mx-1 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_operador-{{ $item->id }}"
                data-id="{{$item->id}}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Operador"
                >
            </button>
        </form>
    </td>
    <td class="text-center">
        {{ $item->operador->matricula ?? '--' }}
    </td>
    <td class="text-center">{{ $item->operador->name ?? '--' }}</td>
    {{-- <td class="text-center">{{ $item->turno ?? '--' }}</td> --}}
    <td class="text-center w-max-min-content">
        <span
            title="Clique para alterar para permitir/desabilitar o fechamento cego"
            class="status-dot {{ $item->operador->is_fechamento_cego == '1' ? 'on' : 'off' }} pointer dot-{{ $item->id }}"
            data-id="{{ $item->id }}"
            data-role="fechamento_cego"
            data-status={{ $item->operador->is_fechamento_cego }}
        >
        </span>
    </td>
    <td class="text-center w-max-min-content">
        <span
            title="Clique para alterar para permitir/desabilitar o acesso"
            class="status-dot {{ $item->operador->acesso_todos_caixas == '1' ? 'on' : 'off' }} pointer dot-{{ $item->id }}"
            data-id="{{ $item->id }}"
            data-role="acesso_todos_caixas"
            data-status={{ $item->operador->acesso_todos_caixas }}
        >
        </span>
    </td>
    <td>
        <b class="m-0 p-0">Cadastro</b>
        <p class="m-0 p-0">{{ __data_pt($item->created_at) }}</p>
    </td>
</tr>

@include('modals._view_operador', ['item' => $item])
