@php
    $medicoFuncionario = $medico->funcionario;
    $cargoNome = $medicoFuncionario?->cargo?->nome;
@endphp

<tr>
    <td>
        <form class="d-flex align-items-center gap-1" action="{{ route('vet.medicos.destroy', $medico) }}" method="POST"
            id="form-{{ $medico->id }}">
            @csrf
            @method('DELETE')

            <button type="button" class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir médico">
                <img height="26" width="26" src="/assets/images/svg/icone excluir.svg" alt="Excluir médico">
            </button>

            <a class="border-0 m-0 p-0 bg-transparent text-color-back" title="Editar médico"
                href="{{ route('vet.medicos.edit', [$medico->id, 'page' => request()->query('page', 1)]) }}">
                <img height="26" width="26" src="/assets/images/svg/icone editar nfe.svg" alt="Editar médico">
            </a>
        </form>
    </td>

    <td class="text-start">
        <div class="fw-semibold text-color">{{ $medicoFuncionario?->nome ?? 'Colaborador removido' }}</div>
        @if ($cargoNome)
            <small class="text-muted">{{ $cargoNome }}</small>
        @endif
    </td>
    <td class="text-center">{{ $medico->crmv }}</td>
    <td class="text-center">{{ $medico->especialidade ?: '--' }}</td>
    <td class="text-center">{{ $medico->status === 'ativo' ? 'Ativo' : 'Inativo' }}</td>
</tr>