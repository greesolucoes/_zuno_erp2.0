<tr>
    <td>{{ __data_pt($c->created_at) }}</td>
    <td class="text-center">
        @if($c->estado == 'aprovado')
        <span class="text-center text-green"><b>Aprovado</b></span>
        @elseif($c->estado == 'cancelado')
        <span class="text-center text-red"><b>Cancelado</b></span>
        @elseif($c->estado == 'rejeitado')
        <span class="text-center text-orange"><b>Rejeitado</b></span>
        @else
        <span class="text-center text-purple"><b>Novo</b></span>
        @endif
    </td>
    <td class="text-center">{{ $c->estado == 'aprovado' ? $c->chave : '--' }}</td>
    <td class="text-center">{{ $c->estado == 'aprovado' ? $c->numero : '--' }}</td>
    <td class="text-center">{{ $c->tipo == 'nfce' ? 'PDV NFC-e' : 'NF-e' }}</td>
    <td class="text-green text-right">R$ <b>{{ __moeda($c->total) }}</b></td>
</tr>