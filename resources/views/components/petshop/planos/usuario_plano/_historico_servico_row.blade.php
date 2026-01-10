<tr>
    <td>{{ $servico->servico?->nome }}</td>
    <td class="text-center">
        {{ __data_pt($servico->ordemServico?->data_inicio, 0) }} -
        {{ __data_pt($servico->ordemServico?->data_entrega, 0) }}
    </td>
    <td class="text-center">{{ __data_pt($servico->ordemServico?->created_at, 0) }}</td>
    <td class="text-center">{{ $servico->quantidade }}</td>
</tr>