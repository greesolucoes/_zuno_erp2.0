<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('clientes.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            @can('clientes_delete')
                <button 
                    type="button"
                    class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                    title="Excluir Cliente"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir Cliente"
                    >
                </button>
            @else
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Exclusão desabilitada"
                    title="Exclusão Desabilitada"
                    style="cursor: not-allowed; filter: grayscale(1);"
                >
            @endcan
            @can('clientes_edit')
                <a 
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    title="Editar Cliente"
                    href="{{ route('clientes.edit', [$item->id, 'page' => request()->query('page', 1)]) }}"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar Cliente"
                    >
                </a>
            @else
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Edição desabilitada"
                    style="cursor: not-allowed; filter: grayscale(1);"
                >
            @endcan

            <button 
                type="button" 
                title="Visualilizar Dados do Cliente" 
                class="border-0 mx-1 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_cliente-{{ $item->id }}"
                data-id="{{$item->id}}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Cliente"
                >
            </button>

            {{-- <a title="Informações de cashBack" class="btn btn-dark btn-sm"
                href="{{ route('clientes.cash-back', [$item->id]) }}">
                <i class="ri-coins-fill"></i>
            </a> --}}

            <a 
                title="Histórico do Cliente" 
                class="border-0 m-0 p-0 bg-transparent text-color-back" 
                href="{{ route('clientes.historico', [$item->id]) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone historico.svg"
                    alt="Histórico Cliente"
                >
            </a>

            <button type="button" title="CRM" class="border-0 m-0 p-0 bg-transparent text-color-back"
                onclick="modalCrm('{{ $item->id }}')">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone crm.svg"
                    alt="CRM"
                >
            </button>
        </form>
    </td>
    <td class="text-center">
        <p class="p-0 m-0"">
            <b>{{ $item->razao_social ?? '--' }}</b>
        </p>
        <p class="p-0 m-0">{{$item->cpf_cnpj ?? '--'}}</p>
    </td>
    <td class="text-center">{{ $item->cidade ? $item->cidade->info : '' }}</td>
    <td class="text-center">{{ $item->rua ? $item->endereco : '--' }}</td>
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

@include('modals._view_cliente', ['cliente' => $item])
