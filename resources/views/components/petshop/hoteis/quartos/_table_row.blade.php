<tr>
    <td>
        <form class='d-flex align-items-center gap-1' action="{{ route('quartos.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button type="button" class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete" title="Excluir Quarto">
                <img height="26" width="26" src="/assets/images/svg/icone excluir.svg" alt="Excluir Quarto">
            </button>

            <a class="border-0 m-0 p-0 bg-transparent text-color-back" title="Editar Quarto" href="{{ route('quartos.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img height="26" width="26" src="/assets/images/svg/icone editar nfe.svg" alt="Editar Quarto">
            </a>

            <a class="border-0 m-0 p-0 bg-transparent text-color-back" title="Eventos do Quarto" href="{{ route('quartos.eventos.index', ['quarto_id' => $item->id]) }}">
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
        <strong class="p-0 m-0">
            {{ ['pequeno' => 'Pequeno Porte', 'grande' => 'Grande Porte', 'individual' => 'Individual', 'coletivo' => 'Coletivo'][$item->tipo] ?? '--' }}
        </strong>
    </td>
    <td class="text-center">
        <strong 
            class="p-0 m-0
                {{ $item->status == 'disponivel' ? 'text-green' : '' }}
                {{ $item->status == 'bloqueado' ? 'text-red' : '' }}
                {{ $item->status == 'reservado' ? 'text-purple' : '' }}
                {{ 
                    $item->status == 'em_limpeza' ||
                    $item->status == 'manutencao' ||
                    $item->status == 'em_uso' 
                    ? 'text-orange' : '' 
                }}
            "
        >
            {{ \App\Models\Petshop\Quarto::statusList()[$item->status] ?? '--' }}
        </strong>
    </td>
    
    <td class="text-center">
        <strong class="m-0 p-0 text-purple">
            {{ $item->capacidade ?? '--' }}
        </strong>
    </td>
    <td class="text-left">
        <p class="m-0 p-0">
            {{ $item->created_at->format('d/m/Y') }}<br>
            <small>{{ $item->created_at->format('H:i') }}</small>
        </p>
    </td>
</tr>