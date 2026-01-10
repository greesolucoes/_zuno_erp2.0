<tr>
    <td>
        <form action="{{ route('empresas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 300px">
            @method('delete')
            @csrf

            <button 
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Empresa"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Empresa"
                >
            </button>

            <a 
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Empresa"
                href="{{ route('empresas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Empresa"
                >
            </a>

            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-settings-4-line"></i><span class="caret"></span>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('natureza-operacao-adm.index', ['empresa='. $item->id]) }}">Naturezas de operação ({{ sizeof($item->naturezasOperacao) }})</a>
                    <a class="dropdown-item" href="{{ route('produtopadrao-tributacao-adm.index', ['empresa='. $item->id]) }}">Padrão para tributação ({{ sizeof($item->padraoTributacaoProduto) }})</a>
                </div>
            </div>

            <!-- <button title="Acessar Empresa" onclick="acesso('{{ $item->id }}')" type="button" class="btn btn-dark btn-sm btn-danger">
                <i class="ri-fingerprint-line"></i>
            </button> -->
        </form>
    </td>
    <td class="text-center text-purple fw-bold">{{ $item->nome }}</td>
    <td class="text-center fw-bold">{{ $item->nome_fantasia }}</td>
    <td class="text-center">{{ formatarCpfCnpj($item->cpf_cnpj) }}</td>
    <td class="text-center">{{ $item->ie }}</td>
    <td class="text-center fw-bold text-orange">{{ $item->tributacao }}</td>
    <td class="text-center fw-bold text-green">
        @if($item->plano)
        {{ $item->plano->plano->nome }}
        @else
            <span 
                class="status-dot off"
            >
            </span>
        @endif
    </td>
    <td class="text-center">
        @if($item->arquivo)
            <span 
                class="status-dot on"
            >
            </span>
        @else
            <span 
                class="status-dot off"
            >
            </span>
        @endif
    </td>
    <td class="text-center">
        @if($item->status)
            <span 
                class="status-dot on"
            >
            </span>
        @else
            <span 
                class="status-dot off"
            >
            </span>
        @endif
    </td>
    <td>
        {{ __data_pt($item->created_at, false) }} <br>
        <small>{{ __data_pt($item->created_at, false, 'H:i') }}</small>
    </td>
</tr>