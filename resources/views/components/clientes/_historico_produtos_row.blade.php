<tr>
    @if(!isset($print))
    <td class="text-left">
        <img class="img-60" src="{{ $p->produto->img }}">
    </td>
    @endif
    <td class="text-left">{{ $p->produto->nome }}</td>
    <td class="text-left">{{ number_format($p->quantidade, 2) }}</td>
    <td class="text-left">{{$p->origem ?? ''}}</td>
    <td class="text-left">{{__data_pt($p->created_at) ?? ''}}</td>
    <td class="text-left text-green">
        R$ <b>{{ __moeda($p->valor_unitario) }}</b>
    </td>
    <td class="text-right text-green">
        R$ <b>{{ __moeda($p->quantidade * $p->valor_unitario) }}</b>
    </td>
</tr>
