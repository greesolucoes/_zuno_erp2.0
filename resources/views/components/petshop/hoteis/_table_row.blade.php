<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('hoteis.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Reserva">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Reserva">
            </button>
            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Reserva"
                href="{{ route('hoteis.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Reserva">
            </a>


            <div class="dropdown">
                <a href="#" id="dropdownChecklist{{ $item->id }}" data-bs-toggle="dropdown" aria-expanded="false"
                title="{{ $item->situacao_checklist ? 'Ver checklist' : 'Realizar checklist' }}">
                    <img height="26" width="26" src="/assets/images/svg/icon_checklist.svg"
                        alt="Checklist">
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownChecklist{{ $item->id }}">
                    <li><a class="dropdown-item" href="{{ route('hoteis.checklist.create', [$item->id, 'tipo' => 'entrada']) }}">Check-in</a></li>
                    <li><a class="dropdown-item" href="{{ route('hoteis.checklist.create', [$item->id, 'tipo' => 'saida']) }}">Check-out</a></li>
                </ul>
            </div>

            @if (isset($item->hotelClienteEndereco))
                <a
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    title="Imprimir cupom de entrega"
                    href="{{ route('hoteis.endereco_entrega', $item->id) }}"
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
        @if($item->ordem_servico_id)
            <a href="{{ route('ordem-servico.show', $item->ordemServico->id) }}">#{{ $item->ordemServico->codigo_sequencial }}</a>
        @else
            --
        @endif
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
          <b>{{ $item->quarto->nome ? $item->quarto->nome : '--' }}</b>
        </p>
    </td>
    <td class="text-center">
        <strong 
            class="m-0 p-0
                {{ $item->estado == 'agendado' ? 'text-purple' : '' }}
                {{ $item->estado == 'em_andamento' ? 'text-orange' : '' }}
                {{ $item->estado == 'concluido' ? 'text-green' : '' }}
                {{ $item->estado == 'cancelado' ? 'text-red' : '' }}
                {{ $item->estado == 'rejeitado' ? 'text-red' : '' }}    
            "
        >
            {{ App\Models\Petshop\Hotel::getStatusHotel(strtolower($item->estado)) }}
        </strong>
    </td>
    <td class="w-max-min-content">
        <p class="m-0 p-0">
            {{ $item->checkin->format('d/m/Y') }}<br>
            <small>{{ $item->checkin->format('H:i') }}</small>
        </p>
    </td>
    <td>
        <p class="m-0 p-0">
            {{ $item->checkout->format('d/m/Y') }}<br>
            <small>{{ $item->checkout->format('H:i') }}</small>
        </p>
    </td>
    <td>
        <p class="m-0 p-0">
            {{ $item->created_at->format('d/m/Y') }}<br>
            <small>{{ $item->created_at->format('H:i') }}</small>
        </p>
    </td>
    <td class="text-right">
        <b class="m-0 p-0 text-green">
            R$ {{ __moedaInput($item->valor) }}
        </b>
    </td>
</tr>

@include('modals._crm')
@include('modals._view_cliente', ['cliente' => $item])