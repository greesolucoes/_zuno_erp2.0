<tr>
    <td class="text-center"><b>{{ $item->nome }}</b></td>
    <td class="text-center">{{ $item->sexo == 'F' ? 'Fêmea' : 'Macho' }}</td>
    <td class="text-center">{{ isset($item->especie->nome) ? $item->especie->nome : '--' }}</td>
    <td class="text-center">{{ isset($item->raca->nome) ? $item->raca->nome : '--' }}</td>
    <td class="text-center">{{ isset($item->pelagem->nome) ? $item->pelagem->nome . (isset($item->cor) && !empty($item->cor) ? ' - ' . $item->cor : '') : '--' }}</td>
    <td class="text-center">{{ $item->cliente->razao_social ?? $item->cliente->nome_fantasia ?? '' }}</td>
    <td>
        <form action="{{ route('animais.pacientes.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
            @method('delete')

            <a href="{{ route('animais.pacientes.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                <i class="bx bx-edit"></i>
            </a>

            <button
                type="button"
                class="btn btn-info btn-sm"
                title="Visualizar Dados do Pet"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_animal-{{ $item->id }}"
                data-id="{{ $item->id }}"
            >
                <i class="bx bx-show"></i>
            </button>

            <a href="{{ route('animais.pacientes.crm', [$item->id]) }}" class="btn btn-secondary btn-sm" title="CRM veterinário">
                <i class="bx bx-id-card"></i>
            </a>

            @if (($item->vaccine_cards_count ?? 0) > 0)
                <a
                    href="{{ route('vet.vaccine-cards.print', [\Illuminate\Support\Str::slug(sprintf('%s-%s', $item->nome ?? 'cartao', $item->id))]) }}"
                    class="btn btn-primary btn-sm"
                    title="Emitir cartão de vacina"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="bx bx-printer"></i>
                </a>
            @endif

            @csrf

            <button type="button" class="btn btn-delete btn-sm btn-danger">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>

@include('modals._view_animal', ['nome' => $item])
