<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('animais.pacientes.destroy', $item->id) }}" method="post"
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
                href="{{ route('animais.pacientes.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
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

            
            <button
                type="button"
                title="Visualilizar Dados do Pet"
                class="border-0 mx-1 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_animal-{{ $item->id }}"
                data-id="{{$item->id}}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Pet"
                >
            </button>

            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="CRM veterinário"
                href="{{ route('animais.pacientes.crm', [$item->id]) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone crm.svg"
                    alt="CRM veterinário"
                >
            </a>

            @if(($item->vaccine_cards_count ?? 0) > 0)
            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Emitir cartão de vacina"
                href="{{ route('vet.vaccine-cards.print', [\Illuminate\Support\Str::slug(sprintf('%s-%s', $item->nome ?? 'cartao', $item->id))]) }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone impressoras.svg"
                    alt="Emitir cartão de vacina"
                >
            </a>
            @endif


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
    <td class="text-center"><b>{{ $item->nome }}</b></td>
    <td class="text-center">{{ $item->sexo == 'F' ? 'Fêmea' : 'Macho' }}</td>
    <td class="text-center">{{ isset($item->especie->nome) ? $item->especie->nome : '--' }}</td>
    <td class="text-center">{{ isset($item->raca->nome) ? $item->raca->nome : '--' }}</td>
    <td class="text-center">{{ isset($item->pelagem->nome) ? $item->pelagem->nome . (isset($item->cor) && !empty($item->cor) ? ' - ' . $item->cor : '') : '--' }}</td>
    <td class="text-center">{{ $item->cliente->razao_social ?? $item->cliente->nome_fantasia ?? '' }}</td>
</tr>

@include('modals._view_animal', ['nome' => $item])