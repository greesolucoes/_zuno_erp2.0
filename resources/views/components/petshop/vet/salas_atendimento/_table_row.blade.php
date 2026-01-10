<tr>
    <td>
        <form class="d-flex align-items-center gap-1" action="{{ route('vet.salas-atendimento.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Sala">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Sala">
            </button>

            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Sala"
                href="{{ route('vet.salas-atendimento.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Sala">
            </a>
        </form>
    </td>
    <td class="text-center">
        <p class="m-0 p-0 fw-semibold">{{ $item->nome }}</p>
        <small class="text-muted">{{ $item->identificador ?: 'Sem identificador' }}</small>
    </td>
    <td class="text-center">{{ $tiposSala[$item->tipo] ?? ucfirst($item->tipo) }}</td>
    <td class="text-center fw-semibold">{{ $statusSala[$item->status] ?? ucfirst($item->status) }}</td>
    <td class="text-center">{{ $item->capacidade ? $item->capacidade . ' pacientes' : '--' }}</td>
    <td class="text-center">{{ optional($item->updated_at)->format('d/m/Y H:i') ?? '--' }}</td>
</tr>