@php
    $medicoFuncionario = $medico->funcionario;
    $cargoNome = $medicoFuncionario?->cargo?->nome;
@endphp

<tr>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $medicoFuncionario?->nome ?? 'Colaborador removido' }}</div>
        @if ($cargoNome)
            <small class="text-muted">{{ $cargoNome }}</small>
        @endif
    </td>
    <td class="text-center">{{ $medico->crmv }}</td>
    <td class="text-center">{{ $medico->especialidade ?: '--' }}</td>
    <td class="text-center">{{ $medico->status === 'ativo' ? 'Ativo' : 'Inativo' }}</td>
    <td>
        <form action="{{ route('vet.medicos.destroy', $medico) }}" method="post" id="form-{{ $medico->id }}">
            @method('delete')
            <a href="{{ route('vet.medicos.edit', [$medico->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                <i class="bx bx-edit"></i>
            </a>
            @csrf
            <button type="button" class="btn btn-delete btn-sm btn-danger">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
