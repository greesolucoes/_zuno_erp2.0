<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('turmas.destroy', $item->id) }}" method="post"
            id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            @can('clientes_delete')
            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Turma">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Turma">
            </button>
            @else
            <img
                height="26"
                width="26"
                src="/assets/images/svg/icone excluir.svg"
                alt="Exclusão desabilitada"
                title="Exclusão Desabilitada"
                style="cursor: not-allowed; filter: grayscale(1);">
            @endcan

            @can('clientes_edit')
            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Turma"
                href="{{ route('turmas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Turma">
            </a>
            @else
            <img
                height="26"
                width="26"
                src="/assets/images/svg/icone editar nfe.svg"
                alt="Edição desabilitada"
                style="cursor: not-allowed; filter: grayscale(1);">
            @endcan

            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Eventos da Turma"
                href="{{ route('turmas.eventos.index', ['turma_id' => $item->id]) }}">
                <i class="ri-calendar-todo-line" style="font-size:26px; color: #f87618"></i>
            </a>
        </form>
    </td>
    <td class="text-center">
        <p class="p-0 m-0">
            <b>{{ $item->colaborador->nome ?? '--' }}</b>
        </p>
    </td>
    <td class="text-center">
        <p class="p-0 m-0">
            <b>{{ $item->nome ?? '--' }}</b>
        </p>
    </td>
    <td class="text-center">
        <b class="p-0 m-0 text-purple">
            {{ ['pequeno' => 'Pequeno Porte', 'grande' => 'Grande Porte', 'individual' => 'Individual', 'coletivo' => 'Coletivo'][$item->tipo] ?? '--' }}
        </b>
    </td>
    <td class="text-center">
        <b class="m-0 p-0 text-orange">
            {{ $item->capacidade ?? '--' }}
        </b>
    </td>
    <td class="text-center">
        <b 
            class="
                m-0 p-0
                {{ $item->status == 'disponivel' ? 'text-green' : '' }}
                {{ $item->status == 'ocupado' ? 'text-purple' : '' }}
                {{ $item->status == 'em_limpeza' ? 'text-orange' : '' }}
                {{ $item->status == 'manutencao' ? 'text-red' : '' }}
            "
        >
            {{ 
                [
                    'disponivel' => 'Disponível',
                    'ocupado' => 'Ocupado',
                    'em_limpeza' => 'Em Limpeza',
                    'manutencao' => 'Manutenção'
                ][$item->status] ?? '--' 
            }}
        </b>
    </td>
    <td class="text-center">
        <p class="p-0 m-0">
            {{ $item->descricao ?? '--' }}
        </p>
    </td>
    <td class="text-left">
        <p class="m-0 p-0">
            {{ __data_pt($item->created_at, false) ?? '--' }} <br>
            <small>{{ __data_pt($item->created_at, false, 'H:i') ?? '--' }}</small>
        </p>
    </td>
</tr>