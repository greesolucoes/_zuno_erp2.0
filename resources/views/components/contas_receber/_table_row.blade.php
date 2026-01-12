<tr>
    <td>
        <form 
            action="{{ route('conta-receber.destroy', $item->id) }}" 
            method="post"
            id="form-{{ $item->id }}" style="width: 200px;"
        >

            @if (!$item->status && $item->valor_pendente > 0)
                @method('delete')
                @csrf

                @can('conta_receber_delete')
                    <button 
                        type="button"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                        title="Excluir Conta a Receber"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone excluir.svg"
                            alt="Excluir Conta a Receber"
                        >
                    </button>
                @else
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Exclusão desabilitada"
                        title="Exclusão Desabilitada"
                        style="cursor: not-allowed; filter: grayscale(1);"
                    >
                @endcan

                @can('conta_receber_edit')
                    <a 
                        class="border-0 m-0 p-0 bg-transparent text-color-back"
                        title="Editar Conta a Receber"
                        href="{{ route('conta-receber.edit', [$item->id, 'page' => request()->query('page', 1)]) }}"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone editar nfe.svg"
                            alt="Editar Conta a Receber"
                        >
                    </a>
                @else
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Edição desabilitada"
                        style="cursor: not-allowed; filter: grayscale(1);"
                    >
                @endcan

                @can('conta_receber_edit')
                    <a title="Receber conta" href="{{ route('conta-receber.pay', $item) }}"
                        class="border-0 m-0 p-0 bg-transparent text-color-back">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/receber conta.svg"
                            alt="Receber Conta"
                        >
                    </a>
                @endcan
            @endif

            @if (!$item->parcelas->count() && !$item->boleto && !$item->status && $item->valor_pendente > 0)
                @can('boleto_create')
                    <a 
                        class="border-0 m-0 p-0 bg-transparent text-color-back"
                        title="Gerar boleto" 
                        href="{{ route('boleto.create', [$item->id]) }}"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/gerar boleto.svg"
                            alt="Gerar Boleto"
                        >
                    </a>
                @endcan
            @elseif(!$item->parcelas->count())
                @can('boleto_view')
                    @if ($item->boleto)
                        <a 
                            title="Visualizar boleto" 
                            class="btn btn-success btn-sm"
                            href="{{ route('boleto.show', [$item->id]) }}"
                        >
                            <img
                                height="26"
                                width="26"
                                src="/assets/images/svg/icone exibir nota.svg"
                                alt="Visualizar Boleto"
                            >
                        </a>
                    @endif
                @endcan
            @endif
            @if ($item->parcelas->count())
                <button 
                    type="button" 
                    class="border-0 m-0 p-0 bg-transparent text-color-back" 
                    title="Parcelas"
                    data-bs-toggle="modal" data-bs-target="#modal_parcelas_{{ $item->id }}"
                >
                    <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/parcelas conta.svg"
                            alt="Parcelas da conta"
                        >
                </button>
            @endif
        </form>
        @if ($item->parcelas->count())
            @include('modals._parcelas_conta_receber', ['conta' => $item])
        @endif
    </td>
    <td class="text-center">
        <p class="p-0 m-0"">
            <b>{{ $item->cliente->razao_social ?? '--' }}</b>
        </p>
        <p class="p-0 m-0">{{$item->cliente->cpf_cnpj ?? '--'}}</p>
    </td>
    <td class="text-purple text-center">
        <b>{{ $item->localizacao->descricao }}<b>
    </td>
    <td class="text-center">
        @if (in_array($item->status, ['cancelado', 'cancelada']))
            <b class="text-red">Cancelado</b>
        @elseif ($item->status || $item->valor_pendente <= 0)
            <b class="text-green">Recebido</b>
        @else
            <b class="text-orange">Pendente</b>
        @endif
    </td>
    <td>
        {{ __data_pt($item->created_at, 1) }}
    </td>
    <td>
        {{ __data_pt($item->data_vencimento, 0) }}
        @if (!$item->status && $item->valor_pendente > 0)
            <br>
            <span 
                class="text-danger"
                style="font-size: 10px"
            >
                {{ $item->diasAtraso() }}
            </span>
        @endif
    </td>
    <td class="text-green text-right">
        <b>R$ {{ __moeda($item->valor_integral) }}</b>
    </td>
    <td class="text-green text-right">
        <b>R$ {{ __moeda($item->valor_recebido) }}</b>
    </td>
    <td class="text-green text-right">
        <b>R$ {{ __moeda($item->valor_pendente) }}</b>
    </td>
</tr>