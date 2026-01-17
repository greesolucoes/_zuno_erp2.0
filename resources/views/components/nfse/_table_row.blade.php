<tr>
    <td style="min-width: max-content; width: auto">
        <form action="{{ route('nota-servico.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            @if ($item->estado == 'novo' || $item->estado == 'rejeitado')
                @can('nfse_delete')
                    <button
                        type="button"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                        title="Excluir NFS-e"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone excluir.svg"
                            alt="Excluir NFS-e"
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

                @can('nfse_edit')
                    <a title='Editar' class="border-0 m-0 p-0 bg-transparent text-color-back"
                        href="{{ route('nota-servico.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone editar nfe.svg"
                            alt="Editar NFS-e"
                        >
                    </a>
                @else
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar NFS-e"
                        style="cursor: not-allowed; filter: grayscale(1);"
                    >
                @endcan

                <button title="Transmitir NFS-e" type="button"
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    onclick="transmitir('{{ $item->id }}')">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone transmitir nota.svg"
                            alt="Editar NFS-e"
                        >
                </button>
            @endif

            @if ($item->estado == 'aprovado')
                <a class="border-0 m-0 p-0 bg-transparent text-color-back" title="Imprimir NFS-e" target="_blank"
                    href="{{ route('nota-servico.imprimir', [$item->id]) }}">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone impressoras.svg"
                            alt="Imprimr"
                        >
                </a>

                <button title="Cancelar NFS-e" type="button"
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    onclick="cancelar('{{ $item->id }}', '{{ $item->numero }}')">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone cancelar nota.svg"
                        alt="Cancelar NFS-e"
                    >
                </button>
            @else
                <a title="Visualizar PDF Temporário" class="border-0 m-0 p-0 bg-transparent text-color-back"
                    target='_blank'
                    href="{{ route('nota-servico.preview', [$item->id]) }}">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone impressoras.svg"
                            alt="PDF temporário"
                        >
                </a>
            @endif

            <button title="Consultar NFS-e" type="button" class="border-0 m-0 p-0 bg-transparent text-color-back"
                onclick="consultar('{{ $item->id }}', '{{ $item->numero }}')">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone lupa exibir nota.svg"
                        alt="Editar NFS-e"
                    >
            </button>


        </form>

        @if ($item->estado == 'aprovado')
            <button
                title="Rearmazenar XML"
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                onclick="document.getElementById('rearmazenar-xml-nfse-{{$item->id}}').submit();"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone xml NF-e.svg"
                    alt="Rearmazenar XML"
                >
            </button>
        @endif

        <form id="rearmazenar-xml-nfse-{{$item->id}}"
              action="{{ route('nota-servico.rearmazenar-xml', $item->id) }}"
              method="POST"
              class="d-none">
            @csrf
        </form>
    </td>
    <td class="text-left">
        <b class="m-0 p-0">Emissão</b>
        <p class="m-0 p-0">{{ __data_pt($item->created_at) }}</p>
        @if (isset($item->data_emissao))
            <b class="m-0 p-0">Transmissão</b>
            <p class="m-0 p-0">{{ __data_pt($item->data_emissao) }}</p>
        @endif
    </td>
    <td class="text-center">{{ $item->numero_nfse ? $item->numero_nfse : '--' }}</td>
    <td class="text-center">
        <p class="p-0 m-0" style="min-width: max-content; width: auto">
            <b>{{ $item->razao_social ?? '--' }}</b>
        </p>
        <p class="p-0 m-0">{{$item->documento ?? '--'}}</p>
    </td>
    <td class="text-center">
        {{ $item->natureza_operacao ?? '--' }}
    </td>
    <td class="text-center {{
    $item?->estado === 'cancelado' ? 'text-red': (
        $item?->estado === 'rejeitado' ? ' text-orange' : (
            $item?->estado === 'aprovado' ? 'text-green' : 'text-purple'
        )
        )}}" style="text-transform: capitalize;">
            <b>{{ $status[$item->estado] ?? '--' }}</b>
    </td>

    {{-- <td class="text-center text-purple"><b>{{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</b></td> --}}
    {{-- <td class="text-center">{{ $item->chave ?? '--'}}</td> --}}
    <td class="text-right text-green">R$ <b>{{ __moeda($item->valor_total) }}</b></td>

    <td>
        @if($item->ordemServico)
            <a href="{{ route('ordemServico.show', $item->ordemServico->id) }}">
                #{{ $item->ordemServico->codigo_sequencial }}
            </a>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>

</tr>
