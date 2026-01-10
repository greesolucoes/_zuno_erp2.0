<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('quartos.eventos.destroy', $evento->id) }}" method="post"
        id="form-{{ $evento->id }}">
            @method('delete')
            @csrf
            
            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Evento">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Evento">
            </button>

            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Evento"
                href="{{ route('quartos.eventos.edit', $evento->id) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Evento">
            </a>
        </form>
    </td>
    <td class="text-center">
        <p class="m-0 p-0">
            {{ $evento->descricao ?? '--' }}
        </p>
    </td>
    <td>
        {{ __data_pt($evento->inicio, false) }} <br/>
        <small>{{ $evento->inicio->format('H:i') }}</small>
    </td>
    <td>
        {{ $evento->fim ? __data_pt($evento->fim, false) : '--' }} <br/>
        <small>{{ $evento->fim->format('H:i') }}</small>
    </td>
    <td class="text-center">
        <strong class="text-purple">{{ $evento->servico->nome ?? '--' }}</strong>
    </td>
    <td class="text-center">
        <strong>
            @isset($evento->servico)
                @if($evento->servico->tipo_servico == 2)
                    {{ $evento->fornecedor->razao_social ?? '--' }}
                @else
                    {{ $evento->prestador->nome ?? '--' }}
                @endif
            @else
                --
            @endif
        </strong>
    </td>            
</tr>