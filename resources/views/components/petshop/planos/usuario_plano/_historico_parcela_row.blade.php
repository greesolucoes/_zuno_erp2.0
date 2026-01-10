@php
    $statusLabel = 'Pendente';
    $statusClass = 'orange';

    if ($parcela->status === 'paga') {
        $statusLabel = 'Paga';
        $statusClass = 'green';
    } elseif ($parcela->status === 'parcial') {
        $statusLabel = 'Parcial';
    } elseif ($parcela->status === 'cancelado' || $parcela->status === 'cancelada') {
        $statusLabel = 'Cancelado';
        $statusClass = 'red';
    } elseif (\Carbon\Carbon::parse($parcela->data_vencimento)->isPast()) {
        $statusLabel = 'Vencida';
        $statusClass = 'red';
    }
@endphp
<tr>
    <td>{{ $parcela->contaReceber?->plano?->nome }}</td>
    <td class="text-center">{{ $parcela->numero }}</td>
    <td class="text-center">{{ __data_pt($parcela->data_vencimento) }}</td>
    <td class="text-center"><b class="text-{{ $statusClass }}">{{ $statusLabel }}</b></td>
    <td class="text-right"><b class="text-green">R$ {{ __moeda($parcela->valor_atualizado) }}</b></td>
</tr>