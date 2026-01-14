<tr>
    <td class="text-center">
        @if($item->ordem_servico_id)
            <a href="{{ route('ordem-servico.show', $item->ordem_servico_id) }}">#{{ $item->ordemServico->codigo_sequencial }}</a>
        @else
            --
        @endif
    </td>
    <td class="text-center">
        <p class="p-0 m-0">
            <b>{{ $item->animal->nome ?? '--' }}</b>
        </p>
    </td>
    <td class=" text-center">
        <p class="p-0 m-0">
        <b>{{ $item->Cliente->razao_social ?? '--' }}</b>
        </p>
    <p class=" p-0 m-0">{{$item->cliente->cpf_cnpj ?? '--'}}</p>
    </td>

    <td class="text-center">
        <strong 
            class="m-0 p-0 text-capitalize
                {{ $item->estado == 'agendado' ? 'text-purple' : '' }}
                {{ $item->estado == 'em_andamento' ? 'text-orange' : '' }}
                {{ $item->estado == 'concluido' ? 'text-green' : '' }}
                {{ $item->estado == 'rejeitado' ? 'text-red' : '' }}
                {{ $item->estado == 'cancelado' ? 'text-red' : '' }}
            "
        >
            {{ App\Models\Petshop\Creche::getStatusCreche(strtolower($item->estado)) }}
        </strong>
    </td>

    <td class=" text-center">
        <p class="p-0 m-0">
        <b>{{ $item->turma->nome ? $item->turma->nome : '--' }}</b>
        </p>
    </td>
    <td>
        <p class="m-0 p-0">
            {{ $item->data_entrada->format('d/m/Y') }}<br>
            <small>{{ $item->data_entrada->format('H:i') }}</small>
        </p>
    </td>
    <td>
        <p class="m-0 p-0">
            {{ $item->data_saida->format('d/m/Y') }}<br>
            <small>{{ $item->data_saida->format('H:i') }}</small>
        </p>
    </td>
    <td>
        <p class="m-0 p-0">
            {{ $item->created_at->format('d/m/Y') }}<br>
            <small>{{ $item->created_at->format('H:i') }}</small>
        </p>
    </td>
    <td class="text-right">
        <strong class="m-0 p-0 text-green">R$ {{ __moedaInput($item->valor) }}</strong>
    </td>
    <td>
        <form action="{{ route('creches.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <a href="{{ route('creches.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white" title="Editar">
                <i class="bx bx-edit"></i>
            </a>

            <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir">
                <i class="bx bx-trash"></i>
            </button>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn btn-secondary btn-sm" id="dropdownChecklist{{ $item->id }}" data-bs-toggle="dropdown" aria-expanded="false"
                    title="{{ $item->situacao_checklist ? 'Ver checklist' : 'Realizar checklist' }}">
                    <i class="bx bx-check-square"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownChecklist{{ $item->id }}">
                    <li><a class="dropdown-item" href="{{ route('creches.checklist.create', [$item->id, 'tipo' => 'entrada']) }}">Checklist de entrada</a></li>
                    <li><a class="dropdown-item" href="{{ route('creches.checklist.create', [$item->id, 'tipo' => 'saida']) }}">Checklist de saída</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('creches.checklist.imprimir', [$item->id, 'tipo' => 'entrada']) }}" target="_blank">Imprimir checklist de entrada</a></li>
                    <li><a class="dropdown-item" href="{{ route('creches.checklist.imprimir', [$item->id, 'tipo' => 'saida']) }}" target="_blank">Imprimir checklist de saída</a></li>
                </ul>
            </div>

            @if (isset($item->crecheClienteEndereco))
                <a href="{{ route('creches.endereco_entrega', $item->id) }}" class="btn btn-primary btn-sm" title="Imprimir cupom" target="_blank">
                    <i class="bx bx-printer"></i>
                </a>
            @endif
        </form>
    </td>
</tr>

@include('modals._view_cliente', ['cliente' => $item])
