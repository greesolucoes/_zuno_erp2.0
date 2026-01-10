@php
    use App\Support\Petshop\Vet\ModeloAtendimentoOptions;
    use Carbon\Carbon;
@endphp

<tr>
    <td>
        <div class="d-inline-flex gap-2">
            <button 
                class="border-0 bg-transparent text-color-back btn-view-modelo"
                data-id="{{ $item->id }}"
                data-title="{{ $item->title }}"
                data-notes="{{ $item->notes }}"
                data-status="{{ $item->status }}"
                data-category="{{ $item->category }}"
                data-content='@json($item->content)'
                data-edit-url="{{ route('vet.modelos-atendimento.edit', $item->id) }}"
                title="Visualizar Modelo"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Modelo"
                >
            </button>

            <a
                href="{{ route('vet.modelos-atendimento.edit', $item->id) }}"
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Editar modelo"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone editar nfe.svg"
                    alt="Editar modelo"
                >
            </a>
        </div>
    </td>
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
</tr>