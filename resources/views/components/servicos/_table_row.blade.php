<tr>
    <td>
        <form action="{{ route('servicos.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @csrf
            @method('delete')
            @can('servico_delete')
                <button type="button" title="Deletar Serviço"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir Serviço"
                    >
                </button>
            @endcan
            @can('servico_edit')
                <a class="border-0 m-0 p-0 bg-transparent text-color-back"
                   href="{{ route('servicos.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar Serviço"
                    >
                </a>
            @endcan
        </form>
    </td>
    <td>{{ $item->codigo ?? '-' }}</td>
    <td>{{ $item->nome }}</td>
    <td>{{ $item->categoria->nome }}</td>
    <td>
        @if($item->tipo_servico == 2)
            {{ $item->fornecedor?->razao_social }}
        @else
            {{ $item->funcionario?->nome }}
        @endif
    </td>
    <td>{{ $item->tipo_servico_label }}</td>
    <td>
        <b class="text-purple">{{ $item->tempo_execucao_info }}</b>
    </td>
    <td>
        @if ($item->status)
            <i class="ri-checkbox-circle-fill text-success"></i>
        @else
            <i class="ri-close-circle-fill text-danger"></i>
        @endif
    </td>
    @if (__isActivePlan(Auth::user()->empresa, 'Reservas'))
        <td>
            @if ($item->reserva)
                <i class="ri-checkbox-circle-fill text-success"></i>
            @else
                <i class="ri-close-circle-fill text-danger"></i>
            @endif
        </td>
    @endif
    @if (__isActivePlan(Auth::user()->empresa, 'Delivery'))
        <td>
            @if ($item->marketplace)
                <i class="ri-checkbox-circle-fill text-success"></i>
            @else
                <i class="ri-close-circle-fill text-danger"></i>
            @endif
        </td>
    @endif
    <td class="text-right"><small>R$</small> {{ __moeda($item->valor + $item->valor_adicional) }}</td>
</tr>
