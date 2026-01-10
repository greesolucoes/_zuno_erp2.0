<tr>
    <td>{{ $conta->plano_atual ? '*' : '' }} {{ $conta->plano?->nome }}</td>
    <td class="text-center">{{ $conta->formaPagamento?->nome }}</td>
    <td class="text-center">{{ __data_pt($conta->created_at) }}</td>
    <td class="text-center">{{ __data_pt($conta->data_vencimento) }}</td>
    <td class="text-center">{{ $conta->status_plano }}</td>
    <td class="text-right"><b class="text-green">R$ {{ __moeda($conta->valor_integral) }}</b></td>
</tr>