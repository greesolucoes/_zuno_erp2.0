<tr>
    <td class="text-left">{{ $c->id }}</td>
    <td class="text-left">{{ ($c->veiculo?->marca?->nome ?? '') . ' ' . ($c->veiculo?->modelo ?? '') }}</td>
    <td class="text-left">{{ __data_pt($c->created_at) }}</td>
    <td class="text-left">{{ __data_pt($c->data_vencimento, 0) }}</td>
    <td class="text-left">{{ $c->statusLabel}}</td>
    <td class="text-right text-green">R$ <b>{{ __moeda($c->valor) }}</b></td>
</tr>
