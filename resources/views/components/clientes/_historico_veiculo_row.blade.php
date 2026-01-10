<tr>
    <td class="text-left">{{ $item->placa }}</td>
    <td class="text-left">{{ $item->marca->nome ?? '-' }}</td>
    <td class="text-left">{{ $item->modelo }}</td>
    <td class="text-left">{{ $item->cor }}</td>
    <td class="text-left">{{ $item->ano }}</td>
    <td class="text-left">{{ $item->lastedOrdemServico?->created_at ? __data_pt($item->lastedOrdemServico->created_at) : '--' }}</td>
</tr>
