<tr>
    <td class="text-center">{{ $c->descricao }}</td>
    <td>{{ __data_pt($c->created_at) }}</td>
    <td>{{ __data_pt($c->data_vencimento, 0) }}</td>
    <td>{{ $c->status ? __data_pt($c->data_recebimento, 0) : '--' }}</td>
    <td class="text-center">
        @if($c->status)
            <span class="text-center text-green">
                <b>Recebido</b>
            </span>
        @else
            <span class="text-center text-orange">
                <b>Pendente<b>
            </span>
        @endif
    </td>
    <td class="text-right text-green">R$ <b>{{ __moeda($c->valor_integral) }}</b></td>
</tr>