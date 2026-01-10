<tr>
    <td class="text-center">
        <div class="d-flex gap-1 justify-content-center align-items-center flex-wrap">
            <form action="{{ route('ordem-checklist.destroy', $item->id) }}" method="POST" id="form-{{ $item->id }}">
                @csrf
                @method('DELETE')
                <button
                    type="button"
                    class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                    title="Excluir Veículo"
                >
                    <img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir Veículo"
                    >
                </button>
            </form>
            <a title='Editar' class="border-0 m-0 p-0 bg-transparent text-color-back"
               href="{{ route('ordem-checklist.edit', [$item->id]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Checklist">
            </a>

            @if(!$item->orcamento_id)
                <div class="btn-orcamento" data-checklist-id="{{ $item->id }}">
                    @if ($item->status_checklist == 1)
                        <a title="Criar Orçamento" class="border-0 m-0 p-0 bg-transparent text-color-back"
                           href="{{ route('nfe.create', ['orcamento' => 1, 'checklist' => $item->id]) }}" target="_blank">
                            <img
                                height="26"
                                width="26"
                                src="/assets/images/svg/new_orcamento.svg"
                                alt="Criar Orçamento">
                        </a>
                    @else
                        <span title="Criação de Orçamento desabilitada">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/new_orcamento.svg"
                            alt="Criação de Orçamento desabilitada"
                            style="cursor: not-allowed; filter: grayscale(1); opacity: 0.6;">
                    </span>
                    @endif
                </div>
            @else
                @can('orcamento_edit')
                    <a
                        title="Editar Orçamento"
                        class="border-0 m-0 p-0 bg-transparent text-color-back"
                        href="{{ route('nfe.edit', [
                        $item->orcamento_id,
                        'page' => request()->query('page', 1),
                        'orcamento' => request()->query('orcamento', true),
                        'checklist' => request()->query('checklist', $item->id)
                    ]) }}">
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone visualizacao.svg"
                            alt="Editar orçamento">
                    </a>
                @endcan
                @cannot('orcamento_edit')
                    <img
                        title="Edição desabilitada"
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Edição desabilitada"
                        style="cursor: not-allowed; filter: grayscale(1);">
                @endcannot
            @endif
            <button
                title="Imprimir Checklist"
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
            >
                <a href="{{ route('checklist.imprimir', [$item->id]) }}" target="_blank">
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
                class="doc pointer border-0 m-0 p-0 w-auto h-auto bg-transparent text-color-back"><img
                        height="26"
                        width="26"
                        src="/assets/images/svg/icone envio email e zap.svg"
                        alt="">
                </span>
        </div>
    </td>
    <td class="text-center" style="min-width: max-content; width: auto; position: relative;">
        @include('ordem_servico_checklist/_send')
    </td>
    <td class="text-center">{{ $item->cliente->razao_social ?? '-' }}</td>
    <td class="text-center">{{ $item->veiculoCliente->placa ?? '-' }}</td>
    <td class="text-center">
        {{ $item->veiculoCliente->marca->nome ?? '-' }}
        {{ $item->veiculoCliente->modelo ?? '' }}
    </td>
    <td class="text-center">
        {{ $item->veiculoCliente->cor ?? '' }}
    </td>
    <td class="text-center">
        {{$item->tipo_checklist_label}}
    </td>
    <td class="text-center">
        @php
            $status = $item->status_checklist;

            $selectClass = 'ordem-servico-select';

            if ($status == 1) {
                $selectClass = 'ordem-servico-select-success';
            } elseif ($status == 0) {
                $selectClass = 'ordem-servico-select';
            } elseif (in_array($status, [2, 3])) {
                $selectClass = 'ordem-servico-select-danger';
            }

            $optionClassResolver = function ($value) {
                return match ((int) $value) {
                    1 => 'ordem-servico-select-success',
                    0 => 'ordem-servico-select',
                    2, 3 => 'ordem-servico-select-danger',
                    default => 'ordem-servico-select',
                };
            };
        @endphp

        <select
            name="status_checklist"
            id="status_checklist"
            data-checklist-id="{{ $item->id }}"
            class="form-select status-select {{ $optionClassResolver($status) }} text-center w-80 status_checklist"
            style="width: 80%; margin: 0 auto;"
        >
            @foreach ($statusOptions as $value => $label)
                <option
                    value="{{ $value }}"
                    class="{{ $optionClassResolver($value) }} text-center"
                    @if ((string)$status === (string)$value) selected @endif
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

    </td>
    <td class="text-center">
        {{ $item->ordem_id ?? '-' }}
    </td>
    @include('modals._enviar_email', ['item' => $item, 'route' => null, 'title' => 'Enviar Checklist por e-mail'])
    @include('modals._enviar_whatsapp', ['item' => $item, 'route' => 'orcamentos.sendwhatsapp', 'title' => 'Enviar orçamento por WhatsApp'])
</tr>
