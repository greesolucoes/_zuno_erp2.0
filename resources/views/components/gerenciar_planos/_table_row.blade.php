<tr>
    <td>

        <form action="{{ route('gerenciar-planos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
            @method('delete')
            @csrf
            <a
                type="button"
                title="Histórico do Cliente"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_empresa_planos-{{ $item->id }}"
                data-id="{{$item->id}}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone historico.svg"
                    alt="Histórico de planos da empresa"
                >
            </a>
            <a
                type="button"
                title="Renovar Plano"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn_renovar_plano"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone renovar.svg"
                    alt="Renovar plano da empresa"
                >
            </a>
            @if (isset($item->financeiroPlanoAtual))
                @if ($item->financeiroPlanoAtual->status_pagamento == 'cancelado')
                     <a
                        type="button"
                        title="Ativar Plano"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn_ativar_plano"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/ativar plano.svg"
                            alt="Ativar plano da empresa"
                        >
                    </a>
                @else
                    <a
                        type="button"
                        title="Cancelar Plano"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn_cancelar_plano"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/cancelar plano.svg"
                            alt="cancelar plano da empresa"
                        >
                    </a>
                @endif
            @endif

            <input type="hidden" name="inp-empresa_id" value="{{$item->id}}">
        </form>
    </td>
    <td>{{ $item->nome }}</td>
    <td class="text-center">{{ isset($item->plano->plano->nome) ? $item->plano->plano->nome : 'Sem plano atribuido' }}</td>
    <td class="text-center">
        {{ isset($item->plano->forma_pagamento) ? $item->plano->forma_pagamento : '--' }}
    </td>
    <td class="text-center">
        @if(isset($item->financeiroPlanoAtual))
            @if ($item->financeiroPlanoAtual->status_pagamento == 'recebido')
                <b class="text-green">
                    {{ ucfirst($item->financeiroPlanoAtual->status_pagamento) }}
                </b>
            @elseif ($item->financeiroPlanoAtual->status_pagamento == 'pendente')
                <b class="text-orange">
                    {{ ucfirst($item->financeiroPlanoAtual->status_pagamento) }}
                </b>
            @elseif ($item->financeiroPlanoAtual->status_pagamento == 'cancelado')
                <b class="text-red">
                    {{ ucfirst($item->financeiroPlanoAtual->status_pagamento) }}
                </b>
            @endif
        @else
            --
        @endif
    </td>
    <td>
        <p class="m-0 p-0">{{ isset($item->plano->created_at) ? __data_pt($item->plano->created_at) : '--' }}</p>
    </td>
    <td>
        <p class="m-0 p-0">{{ isset($item->plano->data_expiracao) ? __data_pt($item->plano->data_expiracao) : '--' }}</p>
    </td>
    <td class="text-right"><b class="text-green">{{ isset($item->plano->valor) ? 'R$ ' . __moeda($item->plano->valor) : '--' }}</b></td>
</tr>
