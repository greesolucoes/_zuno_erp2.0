<tr class="text-left">
    <td style="min-width: max-content; width: auto">
        <form
            action="{{ route('nfe.destroy', $item->id) }}"
            class="d-flex gap-1 align-items-center" action="{{ route('orcamentos.destroy', $item->id) }}"
            method="post"
            id="form-{{ $item->id }}"
        >
            @method('delete')
            @csrf

            @can('orcamento_delete')
                <button
                    title="Excluir Orçamento"
                    type="button"
                        class="border-0 m-0 p-0 bg-transparent text-color-back  btn-delete">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir orçamento"
                    >
                </button>
            @endcan

            @can('orcamento_edit')
                <a
                    title="Editar Orçamento"
                    class="border-0 m-0 p-0 bg-transparent text-color-back "
                    href="{{ route('nfe.edit', [ $item->id, 'page' => request()->query('page', 1), 'orcamento' => request()->query('orcamento', true)]) }}">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar orçamento"
                    >
                </a>
            @endcan
            @cannot('orcamento_edit')
                <img
                    title="Edição desabilitada"
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Edição desabilitada"
                    style="cursor: not-allowed; filter: grayscale(1);"
                >
            @endcannot

            <button
                title="Imprimir Orçamento"
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
            >
                <a href="{{ route('orcamentos.imprimir', [$item->id]) }}" target="_blank">
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone impressoras.svg"
                        alt="Imprimir"
                    >
                </a>
            </button>

            @can('nfe_view')
                <span
                    data-id="{{$item->id}}"
                    data-type="send"
                    class="pointer border-0 m-0 p-0 w-auto h-auto bg-transparent text-color-back send"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone envio email e zap.svg"
                        alt=""
                    >
                </span>
            @endcan

            @if ($item->estado == 'aprovado')
                @can('nfe_create')
                    <button
                        title="Gerar Venda"
                        type="button"
                        class="border-0 m-0 p-0 bg-transparent text-color-back options"
                        data-id="{{$item->id}}"
                        data-type="options"
                    >
                        <img
                            height="30"
                            width="30"
                            src="/assets/images/svg/Icone tres pontos.svg"
                            alt="Opções do orçamento"
                        >
                    </button>
                @endcan
            @endif

        </form>
    </td>
    @can('nfe_view')
        <td style="min-width: max-content; width: auto; position: relative;">
            <nav id="send-{{$item->id}}" class="menu d-none">
                <ul>
                    @if (isset($item->cliente->email) && $item->cliente->email != '')
                        <li>
                            <form action="{{ route('orcamentos.sendmail', $item->id) }}" method="POST" class="d-inline">
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
                                    <span class="text-color-back">Enviar para email cadastrado</span>
                                </button>
                                <input type="hidden" name="email" value={{ $item->cliente->email }}>
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
                            Enviar para email alternativo
                        </button>
                    </li>
                    @if (isset($item->cliente->telefone) && $item->cliente->telefone != '')
                        <form action="{{ route('orcamentos.sendwhatsapp', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button
                                type="submit"
                                style="cursor: pointer"
                                name="email-cadastrado"
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
                            <input type="hidden" name="number" value="{{ $item->cliente->telefone }}">
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
            <nav id="options-{{$item->id}}" class="menu d-none">
                <ul>
                    <li>
                        <a
                            class="d-inline-flex align-items-center gap-2 m-0 border-0 bg-transparent p-0"
                            href="{{ route('orcamentos.show', $item->id) }}"
                        >
                            <img
                                height="18"
                                width="18"
                                src="assets\images\svg\icone etique DANFE.svg"
                                alt="Gerar venda"
                            >
                            <span class="text-color-back">Gerar venda</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </td>
    @endcan
    <td>
        <p class="p-0 m-0 text-center" style="min-width: max-content; width: auto">
            <b>{{$item?->cliente?->razao_social ?? '--'}}</b>
        </p>
        <p class="p-0 m-0 text-center">{{$item?->cliente?->cpf_cnpj??'--'}}</p>
    </td>
    <td class="text-center">{{ $item->id ? $item->id : '--' }}</td>
    <td>
        <form action="{{ route('orcamentos.status', $item->id) }}" method="POST" id="statusForm">
            @method('PUT')
            @csrf
            <select
                    name="estado"
                    class="form-select mx-auto status-select text-center
                        {{ in_array($item->estado, ['rejeitado', 'cancelado'])  ? 'ordem-servico-select-danger' : 'ordem-servico-select' }}"
                    style="width: 214px;"
                    required onchange="this.form.submit()"
                >
                    @foreach ($status as $value => $label)
                        <option
                            value={{$value}}
                            class="{{ $value == 'cancelado' || $value == 'rejeitado' ? 'ordem-servico-select-danger' : 'ordem-servico-select'}}"
                            {{ $item->estado == $value ? 'selected' : '' }}
                        >
                            {{$label}}
                        </option>
                    @endforeach
            </select>
        </form>
    </td>
    <td class="text-center">{{ __data_pt($item->created_at) }}</td>
    <td class="text-center">{{ __data_pt($item->validade_orcamento) }}</td>
    <td class="text-right text-green">R$ <b>{{ __moeda($item->total) }}</b></td>

    @include('modals._enviar_email', ['item' => $item, 'route' => 'orcamentos.sendmail', 'title' => 'Enviar orçamento por e-mail'])
    @include('modals._enviar_whatsapp', ['item' => $item, 'route' => 'orcamentos.sendwhatsapp', 'title' => 'Enviar orçamento por WhatsApp'])
</tr>
