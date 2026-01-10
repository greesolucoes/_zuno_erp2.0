<tr>
    <td style="min-width: max-content; width: auto">
        <form action="{{ route('ordem-servico.destroy', $item->id) }}"
            method="post"
            id="form-{{ $item->id }}"
            class="gap-3"
        >
            @method('delete')
            @csrf

            @can('ordem_servico_delete')
                <button
                    type="button"
                    class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                    title="Excluir Ordem de Serviço"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir ordem de serviço"
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

            @if ($item->estado != 'PG')
                @can('ordem_servico_edit')
                    <a title='Editar' class="border-0 m-0 p-0 bg-transparent text-color-back"
                    href="{{ route('ordem-servico.show', [$item->id]) }}">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone editar nfe.svg"
                            alt="Editar Ordem de Serviço"
                        >
                    </a>
                @else
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar Ordem de Serviço"
                        style="cursor: not-allowed; filter: grayscale(1);"
                    >
                @endcan
            @else
                <a title='Visualizar O.S' class="border-0 m-0 p-0 bg-transparent text-color-back"
                href="{{ route('ordem-servico.show', [$item->id]) }}">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone visualizacao.svg"
                        alt="Editar Ordem de Serviço"
                    >
                </a>
            @endif


            <button
                title="Imprimir Ordem de Serviço"
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
            >
                <a href="{{ route('ordem-servico.imprimir', $item->id) }}" target="_blank">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone impressoras.svg"
                        alt="Imprimir"
                    >
                </a>
            </button>

            <span
                data-id="{{$item->id}}"
                data-type="send"
                class="send pointer border-0 m-0 p-0 w-auto h-auto bg-transparent text-color-back"
                title="Enviar Ordem de Serviço por E-mail ou WhatsApp"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone envio email e zap.svg"
                    alt=""
                >
            </span>
            @if (
                    (
                        $item->servicos()->count() > 0 ||
                        $item->itens()->count() > 0
                    ) &&
                    $item->nfe_id == 0 &&
                    $item->estado == 'PG'
                )
                <span 
                    data-id="{{$item->id}}" 
                    data-type="emitir-nf"
                    class="emitir-nf pointer border-0 m-0 p-0 w-auto h-auto bg-transparent text-color-back"
                    title="Emitir Notas Fiscais"
                >
                    <img height="26" width="26" src="/assets/images/svg/icone transmitir nota.svg" alt="">
                </span>
            @endif
            @if($item->estado == 'PG')
                <span 
                    data-id="{{$item->id}}" 
                    data-type="nf"
                    class="nf pointer border-0 m-0 p-0 w-auto h-auto bg-transparent text-color-back"
                    title="Situação Fiscal"
                >
                    <img height="26" width="26" src="/assets/images/svg/icone lupa exibir nota.svg" alt="">
                </span>
            @endif

        </form>
    </td>
    <td style="min-width: max-content; width: auto; position: relative;">
        <nav id="send-{{$item->id}}" class="menu d-none">
            <ul>
                @if (isset($item->cliente->email) && $item->cliente->email != '')
                    <li>
                        <form action="{{ route('ordem-servico.enviar-email', ['id' => $item->id]) }}" method="POST" class="d-inline">
                            @csrf
                            <button
                                type="submit"
                                style="cursor: pointer"
                                name="email-cadastrado"
                                title="Enviar para email cadastrado"
                                class="d-inline-flex align-items-center gap-2 m-0 border-0 bg-transparent p-0"
                            >
                                <img
                                    height="18"
                                    width="18"
                                    src="/assets/images/svg/icone e-mail cadastrado.svg"
                                    alt="E-mail cadastrado"
                                >
                                <span class="text-color-back">Enviar para e-mail cadastrado</span>
                            </button>
                            <input type="hidden" name="email" value="{{ $item->cliente->email }}">
                        </form>
                    </li>
                @endif
                <li>
                    <img
                        height="18"
                        width="18"
                        src="/assets/images/svg/icone e-mail terceiros.svg"
                        alt="E-mail terceiros"
                    >
                    <button title="Enviar por email"
                            class="email-nao-cadastrado border-0 m-0 p-0 bg-transparent text-color-back"
                            type="button" data-bs-toggle="modal"
                            data-bs-target="#emailModal-{{ $item->id }}"
                            data-id="{{$item->id}}"
                    >
                        Enviar para e-mail alternativo
                    </button>
                </li>
                @if (isset($item->cliente->telefone) && $item->cliente->telefone != '')
                    <form action="{{ route('ordem-servico.enviar-whatsapp', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button
                            type="submit"
                            style="cursor: pointer"
                            name="numero-cadastrado"
                            title="Enviar para WhatsApp"
                            class="d-inline-flex align-items-center gap-2 m-0 border-0 bg-transparent p-0"
                        >
                            <img
                                height="18"
                                width="18"
                                src="/assets/images/svg/icone envio zap.svg"
                                alt="WhatsApp"
                            >
                            <span class="text-color-back">Enviar para WhatsApp</span>
                        </button>
                        <input type="hidden" name="number" value="{{ $item->cliente->telefone }}" />
                    </form>
                @else
                    <li>
                        <img
                            height="18"
                            width="18"
                            src="/assets/images/svg/icone envio zap.svg"
                            alt="WhatsApp"
                        >
                        <button title="Enviar para WhatsApp"
                                class="email-nao-cadastrado border-0 m-0 p-0 bg-transparent text-color-back"
                                type="button" data-bs-toggle="modal"
                                data-bs-target="#whatsappModal-{{ $item->id }}"
                                data-id="{{$item->id}}"
                        >
                            Enviar para WhatsApp
                        </button>
                    </li>
                @endif
            </ul>
        </nav>
        <nav id="emitir-nf-{{$item->id}}" class="menu d-none">
            <ul class="gap-0">
                <li>
                    @if (
                        $item->nfces()->count() == 0 && 
                        {{-- $item->servicos()->count() == 0 && --}}
                        $item->itens()->count() > 0 &&
                        $item->nfe_id == 0 &&
                        $item->estado == 'PG'
                    )
                        <a 
                            title="Gerar NF-e"
                            class="border-0 m-0 p-0 bg-transparent text-color-back d-flex align-items-center gap-2"
                            href="{{ route('ordem-servico.gerar-nfe', $item->id) }}"
                        >
                            <img 
                                height="18" 
                                width="18" 
                                src="/assets/images/svg/Icone_transmistir NFe.svg" 
                                alt="Gerar NF-e"
                            >
                            <div>
                                Gerar NF-e
                            </div>
                        </a>
                    @endif
                </li>
                <li>
                    @if (
                        $item->estado == 'PG' &&
                        $item->nfe_id == 0 &&  
                        $item->servicos()->count() > 0 
                    )
                        <a 
                            title="Gerar NFS-e"
                            class="border-0 m-0 p-0 bg-transparent text-color-back d-flex align-items-center gap-2"
                            href="{{ route('ordem-servico.gerar-nfse', $item->id) }}"
                        >
                            <img 
                                height="18" 
                                width="18" src="/assets/images/svg/impressao DANFE.svg"
                                alt="Gerar NFS-e"
                            >
                            <div>
                                Gerar NFS-e
                            </div>
                        </a>
                    @endif
                </li>
            </ul> 
        </nav>
        <nav id="nf-{{$item->id}}" class="menu d-none">
            <ul>
                <li>
                    <span class="text-color-back font-semibold fw-semibold text-purple">NFS-e:</span>
                    @if($item->notaServico)
                        @if($item->notaServico->numero_nfse && $item->notaServico->numero_nfse > 0)
                            <a class="text-color-back" href="{{ route('nota-servico.show', $item->notaServico->id) }}">
                                #{{ $item->notaServico->numero_nfse }}
                            </a>
                        @else
                            <span class="text-warning">EM PROCESSAMENTO</span>
                        @endif
                    @else
                        <span class="text-danger">NÃO GERADA</span>
                    @endif
                </li>
                <li>
                    <span class="text-color-back fw-semibold text-purple">NFC-e:</span>
                    @if($item->nfce)
                        @if($item->nfce->numero && $item->nfce->numero > 0)
                            <a class="text-color-back" href="{{ route('nfce.show', $item->nfce->id) }}">
                                #{{ $item->nfce->numero }}
                            </a>
                        @else
                            <span class="text-warning">EM PROCESSAMENTO</span>
                        @endif
                    @else
                        <span class="text-danger">NÃO GERADA</span>
                    @endif
                </li>
                <li>
                    <span class="text-color-back fw-semibold text-purple">NF-e:</span>
                    @if($item->nfe)
                        @if($item->nfe->numero && $item->nfe->numero > 0)
                            <a class="text-color-back" href="{{ route('nfe.show', $item->nfe->id) }}">
                                #{{ $item->nfe->numero }}
                            </a>
                        @else
                            <span class="text-warning">EM PROCESSAMENTO</span>
                        @endif
                    @else
                        <span class="text-danger">NÃO GERADA</span>
                    @endif
                </li>
            </ul>
        </nav>
    </td>
    <td class="text-center">{{ $item->codigo_sequencial }}</td>
    <td class="text-center">
        <p class="p-0 m-0 text-center" style="min-width: max-content; width: auto">
            <b>{{ isset($item->tipo_nome) && $item->tipo_nome == 'nome_fantasia' ? $item?->cliente?->nome_fantasia : $item?->cliente?->razao_social }}</b>
        </p>
        <p class="p-0 m-0 text-center">{{$item?->cliente?->cpf_cnpj ?? '--'}}</p>
    </td>
    <td class="text-center">
        <form action="{{ route('ordem-servico.update', [ $item->id, 'atualiza_status' => 'true']) }}" method="POST" id="statusForm">
            @method('PUT')
            @csrf
            <select
                name="estado"
                class="form-select status-select
           {{ in_array($item->estado, ['RJ', 'CC']) ? 'ordem-servico-select-danger' : 'ordem-servico-select' }}"
                required
                onchange="this.form.submit()"
            >
                @foreach ($status as $value => $label)
                    <option
                        value="{{ $value }}"
                        class="{{ in_array($value, ['CC', 'RJ']) ? 'ordem-servico-select-danger' : 'ordem-servico-select' }} text-center"
                        {{ $item->estado == $value ? 'selected' : '' }}
                        {{-- trava as outras opções quando a OS já está paga --}}
                        {{ $item->estado === 'PG' && $value !== 'PG' ? 'disabled' : '' }}
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

        </form>
    </td>
 
    <td class="text-center">
        @isset($item->data_inicio)
            {{ $item->data_inicio ? __data_pt($item->data_inicio, 1) : '--' }}
        @else
            {{ item->created_at ? __data_pt($item->created_at, 1) : '--' }}
        @endisset
        
    </td>
    <td class="text-center">{{ __data_pt($item->data_entrega, 1) }}</td>
    <td class="text-right text-green">R$ <b>{{ __moeda($item->valor) }}</b></td>

    @include('modals._enviar_email', ['item' => $item, 'route' => 'ordem-servico.enviar-email', 'title' => 'Enviar ordem de serviço por e-mail'] )
    @include('modals._enviar_whatsapp', ['item' => $item, 'route' => 'ordem-servico.enviar-whatsapp', 'title' => 'Enviar ordem de serviço por Whatsapp'])
</tr>
