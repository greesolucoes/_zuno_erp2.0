@php
    $statusLabel = 'Pendente';
    $statusClass = 'orange';
    
    if ($parcela->status === 'paga') {
        $statusLabel = 'Paga';
        $statusClass = 'green';
    } elseif ($parcela->status === 'parcial') {
        $statusLabel = 'Parcial';
        $statusClass = 'orange';
    } elseif ($parcela->status === 'cancelado' || $parcela->status === 'cancelada') {
        $statusLabel = 'Cancelado';
        $statusClass = 'red';
    } elseif (\Carbon\Carbon::parse($parcela->data_vencimento)->isPast()) {
        $statusLabel = 'Vencida';
        $statusClass = 'red';
    }
@endphp

<tr class="new-colors">
    <td>
        <div class="form-check form-checkbox-danger mb-2">
            <input class="form-check-input parcel-check" type="checkbox" value="{{ $parcela->id }}" data-valor="{{ $parcela->valor_atualizado }}" @if($parcela->status === 'paga' || $parcela->status === 'cancelado' || $parcela->status === 'cancelada' || $conta->boleto) disabled @endif>
        </div>
    </td>
    <td>
        @if($parcela->status !== 'paga' && $parcela->status !== 'cancelado' && $parcela->status !== 'cancelada' && !$conta->boleto)
            <a 
                title="Receber parcela" 
                href="{{ route('conta-receber-parcela.pay', $parcela->id) }}" 
                class="border-0 m-0 p-0 bg-transparent text-color-back"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/receber conta.svg"
                    alt="Receber Parcela"
                >
            </a>
            @can('boleto_create')
                <a 
                    title="Gerar boleto" 
                    href="{{ route('boleto.create', [$conta->id, $parcela->id]) }}" 
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/gerar boleto.svg"
                        alt="Gerar Boleto"
                    >
                </a>
            @endcan
        @else
            --
        @endif
    </td>
    <td class="text-center">
        <b>{{ $parcela->numero }}°</b>
    </td>
    <td>{{ __data_pt($parcela->data_vencimento, 0) }}</td>
    <td>{{ $parcela->data_pagamento ? __data_pt($parcela->data_pagamento, 0) : '--' }}</td>
    <td class="text-center">
        <b class="text-{{ $statusClass }}">
            {{ $statusLabel }}
        </b>
    </td>
    <td class="text-center">
        @if($conta->boleto)
            <span class="badge bg-info">Boleto gerado</span>
        @else
            --
        @endif
    </td>
    <td class="text-right text-green"><b>R$ {{ __moeda($parcela->valor_atualizado) }}</b></td>
</tr>