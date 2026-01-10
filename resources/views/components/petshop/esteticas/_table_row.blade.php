<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('esteticas.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            @can('clientes_delete')
            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Cliente">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Cliente">
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
                title="Editar Cliente"
                href="{{ route('esteticas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Cliente">
            </a>
            @else
            <img
                height="26"
                width="26"
                src="/assets/images/svg/icone editar nfe.svg"
                alt="Edição desabilitada"
                style="cursor: not-allowed; filter: grayscale(1);">
            @endcan

            @if (isset($item->esteticaClienteEndereco))
                <a
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    title="Imprimir cupom de entrega"
                    href="{{ route('esteticas.endereco_entrega', $item->id) }}"
                    target="_blank"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone cupom entrega.svg"
                        alt="Imprimir cupom de entrega"
                    >
                </a>
            @endif
        </form>
    </td>
    <td class="text-center">
        <p class="p-0 m-0">

            <b>{{ $item->animal->nome ?? '--' }}</b>
        </p>

    </td>
    <td class=" text-center">
        <p class="p-0 m-0">

            <b>{{ $item->Cliente->razao_social ?? '--' }}</b>
        </p>
        <p class=" p-0 m-0">{{$item->cliente->cpf_cnpj ?? '--'}}</p>
    </td>
    <td class=" text-center">
        <p class="p-0 m-0">

            <b>{{ $item->servicos->pluck('servico.nome')->implode(', ') ?: '--' }}</b>
        </p>

    </td>

    <td class="text-center w-max-min-content">
        <p class="m-0 p-0">
            {{ optional($item->data_agendamento)->format('d/m/Y') }}
        </p>
    </td>
    <td class="text-center">
        <p class="m-0 p-0">
            {{ $item->horario_agendamento }}
        </p>
    </td>
    <td class="text-center">
        <p class="m-0 p-0">
            {{ $item->created_at->format('d/m/Y') }}<br>
            <small>{{ $item->created_at->format('H:i') }}</small>
        </p>
    </td>
    <td class="text-center">
        @if ($item->estado == 'pendente_aprovacao')
            <form action="{{ route('petshop.esteticista.agendamentos.aprovar', $item->id) }}" method="post" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Aprovar</button>
            </form>
            <form action="{{ route('petshop.esteticista.agendamentos.rejeitar', $item->id) }}" method="post" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Rejeitar</button>
            </form>
        @else
            <strong 
                class="m-0 p-0 text-capitalize
                    {{ $item->estado == 'agendado' ? 'text-purple' : '' }}
                    {{ $item->estado == 'em_andamento' ? 'text-orange' : '' }}
                    {{ $item->estado == 'pendente_aprovacao' ? 'text-orange' : '' }}
                    {{ $item->estado == 'concluido' ? 'text-green' : '' }}
                    {{ $item->estado == 'cancelado' ? 'text-red' : '' }}
                    {{ $item->estado == 'rejeitado' ? 'text-red' : '' }}
                "
            >
                {{ App\Models\Petshop\Estetica::getStatusEstetica(strtolower($item->estado)) }}
            </strong>
        @endif
    </td>
    <td class="text-center">
        @if($item->ordemServico)
            <a href="{{ route('ordem-servico.show', $item->ordem_servico_id) }}" target="_blank">
                #{{ $item->ordemServico->codigo_sequencial }}
            </a>
        @else
            --
        @endif
    </td>
    @php
        $total = ($item->servicos->sum('subtotal') + $item->produtos->sum('subtotal'));
    @endphp
    <td class="text-center">
        <p class="m-0 p-0">{{ __moedaInput($total) }}</p>
    </td>
</tr>

@include('modals._crm')
@include('modals._view_cliente', ['cliente' => $item])