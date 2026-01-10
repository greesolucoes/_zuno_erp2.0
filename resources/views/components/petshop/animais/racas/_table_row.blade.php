<tr>
    <td class="text-center">
        <form class='d-flex align-items-center gap-1' action="{{ route('animais.racas.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            @can('clientes_delete')
            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Pet">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Pet">
            </button>
            @else
            <img
                height="26"
                width="26"
                src="/assets/images/svg/icone excluir.svg"
                alt="Exclusão desabilitada"
                title="Exclusão Desabilitada"
                style="cursor: not-allowed; filter: grayscale(1);">
            @endcan
            @can('clientes_edit')
            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Pet"
                href="{{ route('animais.racas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Pet">
            </a>
            @else
            <img
                height="26"
                width="26"
                src="/assets/images/svg/icone editar nfe.svg"
                alt="Edição desabilitada"
                style="cursor: not-allowed; filter: grayscale(1);">
            @endcan

            {{--<button
                type="button"
                title="Visualilizar Dados do Cliente"
                class="border-0 mx-1 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_cliente-{{ $item->id }}"
            data-id="{{$item->id}}">
            <img
                height="26"
                width="26"
                src="/assets/images/svg/icone visualizacao.svg"
                alt="Visualilizar Cliente">
            </button>--}}

        </form>
    </td>
    <td class="text-center">{{ $item->nome }}</td>
    <td class="text-center">{{ $item->especie->nome ?? 'N/A' }}</td> {{-- Exibe o nome da espécie ou 'N/A' --}}

</tr>