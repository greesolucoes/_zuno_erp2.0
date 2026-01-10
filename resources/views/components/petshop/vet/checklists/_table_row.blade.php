<tr>
    <td>
        <form class="d-flex align-items-center gap-1" action="{{ route('vet.checklist.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')
            @csrf

            <button
                type="button"
                class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                title="Excluir Checklist">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone excluir.svg"
                    alt="Excluir Checklist">
            </button>

            <a
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar Checklist"
                href="{{ route('vet.checklist.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar Checklist">
            </a>
        </form>
    </td>
    <td class="align-middle text-start">
        <p class="m-0 p-0 fw-semibold">{{ $item->titulo }}</p>
        @if ($item->itens)
            <small class="text-muted">Primeiro item: {{ $item->itens[0] }}</small>
        @endif
    </td>
    <td class="text-center fw-semibold">{{ $statusOptions[$item->status] ?? ucfirst($item->status) }}</td>
    <td class="text-center">{{ is_array($item->itens) ? count($item->itens) : 0 }}</td>
    <td class="text-center">{{ optional($item->updated_at)->format('d/m/Y H:i') ?? '--' }}</td>
    <td class="align-middle text-start">
        <small class="text-muted">{{ $item->descricao ? \Illuminate\Support\Str::limit($item->descricao, 120) : 'Sem descrição cadastrada.' }}</small>
    </td>
</tr>