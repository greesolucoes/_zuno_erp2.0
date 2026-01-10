<tr>
    <td>{{ $plano->plano->nome }}</td>
    <td class="text-center">{{ $plano->forma_pagamento }}</td>
    <td class="text-center">{{ __data_pt($plano->created_at) }}</td>
    <td class="text-center">{{ __data_pt($plano->data_expiracao) }}</td>
    <td class="text-center w-max-min-content">
        <span 
            class="status-dot {{ $plano->ativo == '1' ? 'on' : 'off' }}"
        >
        </span>
    </td>
    <td class="text-right"><b class="text-green">R$ {{ __moeda($plano->valor) }}</b></td>
</tr>