<tr>
    <td>
        <div class="d-flex gap-1">
            @can('veiculos_cliente_delete')
                <form action="{{ route('veiculos_cliente.destroy', $item->id) }}" method="POST" id="form-{{ $item->id }}">
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                        title="Excluir Veículo"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone excluir.svg"
                            alt="Excluir Veículo"
                        >
                    </button>
                </form>
            @endcan
            @can('veiculos_cliente_edit')
                <a href="{{ route('veiculos_cliente.edit', $item->id) }}" class="border-0 m-0 p-0 bg-transparent text-color-back">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar Veículo"
                    >
                </a>
            @endcan

                <a
                    title="Histórico do Veículo"
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    href="{{ route('veiculos_cliente.history', [$item->id]) }}"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone historico.svg"
                        alt="Histórico Veículo"
                    >
                </a>
        </div>
    </td>

    <td class="text-center">{{ $item->placa }}</td>
    <td class="text-center">{{ $item->marca->nome ?? '-' }}</td>
    <td class="text-center">{{ $item->modelo }}</td>
    <td class="text-center">{{ $item->chassi }}</td>
    <td class="text-center">{{ $item->ano }}</td>
    <td class="text-center">{{ $item->cor }}</td>
    <td class="text-center">{{ $item->cliente->razao_social ?? '-' }}</td>
</tr>
