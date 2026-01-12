<tr>
    <td class="text-left">{{ $sos->ordem_servico_id }}</td>
    <td class="text-left">{{ $sos->servico->nome}}</td>
    <td class="text-left">{{ $sos->quantidade}}</td>
    <td class="text-left">{{ __data_pt($sos->created_at, 0) }}</td>
    <td class="text-left">R$ {{ __moeda($sos->valor)}}</td>
    <td class="text-right text-green">R$ <b>{{ __moeda($sos->subtotal) }}</b></td>
</tr>
