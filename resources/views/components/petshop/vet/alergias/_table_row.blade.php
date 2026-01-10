<tr>
    <td class="align-middle">
        <form class="d-flex align-items-center gap-1" action="{{ route('vet.allergies.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir alergia">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir alergia">
            </button>

            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar alergia"
                href="{{ route('vet.allergies.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar alergia">
            </a>
        </form>
    </td>
    <td class="align-middle text-start">
        <p class="m-0 p-0 fw-semibold">{{ $item->nome }}</p>
    </td>
    <td class="text-center fw-semibold">{{ $statusOptions[$item->status] ?? ucfirst($item->status) }}</td>
    <td class="text-center">{{ optional($item->updated_at)->format('d/m/Y H:i') ?? '--' }}</td>
    <td class="align-middle text-start">
        <small class="text-muted d-block">{{ $item->descricao ? \Illuminate\Support\Str::limit($item->descricao, 120) : 'Sem descrição cadastrada.' }}</small>
        @if ($item->orientacoes)
            <small class="text-muted d-block mt-1"><strong>Orientações:</strong> {{ \Illuminate\Support\Str::limit($item->orientacoes, 120) }}</small>
        @endif
    </td>
</tr>