@php
    use App\Support\Petshop\Vet\ModeloAtendimentoOptions;
    use Carbon\Carbon;
@endphp

<tr>
    <td class="text-center">{{ $item['title'] ?? 'Modelo sem título' }}</td>
    <td class="text-center">
        {{ ModeloAtendimentoOptions::categoryLabel($item['category']) ?? '—-' }}
    </td>
    <td>
        {{ Carbon::parse($item->updated_at)->format('d/m/Y') }} <br>
        <small>
            {{ Carbon::parse($item->updated_at)->format('H:i') }}
        </small>
    </td>
    <td class="text-center">
        <span class="p-2 fw-semibold badge {{$item->status == 'ativo' ? 'bg-success' : 'bg-danger' }}">
            {{ ModeloAtendimentoOptions::statusOptions()[$item->status] ?? '—-' }}
        </span>
    </td>
    <td>
        <div class="d-flex gap-1">
            <button
                type="button"
                class="btn btn-info btn-sm text-white btn-view-modelo"
                data-id="{{ $item->id }}"
                data-title="{{ $item->title }}"
                data-notes="{{ $item->notes }}"
                data-status="{{ $item->status }}"
                data-category="{{ $item->category }}"
                data-content='@json($item->content)'
                data-edit-url="{{ route('vet.modelos-atendimento.edit', $item->id) }}"
                title="Visualizar modelo"
            >
                <i class="bx bx-show"></i>
            </button>

            <a href="{{ route('vet.modelos-atendimento.edit', $item->id) }}" class="btn btn-warning btn-sm text-white" title="Editar modelo">
                <i class="bx bx-edit"></i>
            </a>
        </div>
    </td>
</tr>
