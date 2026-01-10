@php
    use App\Models\Petshop\Vacina;

    $speciesData = $vaccine['species'] ?? [];

    if ($speciesData instanceof \Illuminate\Contracts\Support\Arrayable) {
        $speciesData = $speciesData->toArray();
    } elseif ($speciesData instanceof Traversable) {
        $speciesData = iterator_to_array($speciesData);
    } elseif (is_string($speciesData)) {
        $decodedSpecies = json_decode($speciesData, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $speciesData = $decodedSpecies;
        } elseif ($speciesData !== '') {
            $speciesData = array_map('trim', explode(',', $speciesData));
        } else {
            $speciesData = [];
        }
    }

    if (! is_array($speciesData)) {
        $speciesData = [];
    }

    $speciesLabels = array_values(array_filter($speciesData));
    $coverage = $vaccine['coverage'] ?? [];
    $protocol = $vaccine['protocol'] ?? [];
    $inventory = $vaccine['inventory'] ?? [];
    $statusColor = strtolower($vaccine['status_color'] ?? 'secondary');
    $statusClasses = match ($statusColor) {
        'success' => 'badge bg-success-subtle text-success',
        'warning' => 'badge bg-warning-subtle text-warning',
        'danger' => 'badge bg-danger-subtle text-danger',
        default => 'badge bg-secondary-subtle text-secondary',
    };
@endphp

<tr>
    <td>
        @if (!empty($vaccine['id']))
            <form
                id="delete-vaccine-{{ $vaccine['id'] }}"
                action="{{ route('vacina.vacinas.destroy', ['vacina' => $vaccine['id']]) }}"
                method="post"
                class="d-inline-flex align-items-center justify-content-center gap-1"
            >
                @csrf
                @method('delete')

                <button
                    type="button"
                    class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                    title="Excluir vacina"
                >
                    <img
                        src="/assets/images/svg/icone excluir.svg"
                        alt="Excluir vacina"
                        width="26"
                        height="26"
                    >
                </button>

                <a
                    class="border-0 m-0 p-0 bg-transparent text-color-back"
                    href="{{ route('vacina.vacinas.edit', ['vacina' => $vaccine['id']]) }}"
                    title="Editar vacina"
                >
                    <img
                        src="/assets/images/svg/icone editar nfe.svg"
                        alt="Editar vacina"
                        width="26"
                        height="26"
                    >
                </a>
            </form>
        @endif
    </td>

    <td class="text-start">
        <div class="fw-semibold text-color">{{ $vaccine['name'] ?? '--' }}</div>
        <small class="text-muted d-block">
            {{ collect([$vaccine['code'] ?? null, $vaccine['category'] ?? null, $vaccine['group_label'] ?? null])->filter()->implode(' • ') }}
        </small>
        <small class="text-muted d-block">
            {{ collect([$vaccine['manufacturer'] ?? null, Vacina::opcoesApresentacoes()[$vaccine['presentation']] ?? null, $vaccine['concentration'] ?? null])->filter()->implode(' • ') }}
        </small>
    </td>

    <td>
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-1">
            @foreach ($coverage as $disease)
                <span style="background-color: #f3ecf8 !important" class="badge bg-primary-subtle text-purple text-capitalize">{{ $disease }}</span>
            @endforeach
        </div>
        <small class="text-muted d-block mt-2">
            {{ collect([$protocol['primary'] ?? null, $protocol['booster'] ?? null, $protocol['revaccination'] ?? null])->filter()->implode(' • ') }}
        </small>
    </td>

    <td class="text-center">
        @if (!empty($speciesLabels))
            @foreach ($speciesLabels as $label)
                <span class="badge bg-primary-subtle text-primary me-1">{{ $label }}</span>
            @endforeach
        @else
            <span class="text-muted">--</span>
        @endif
        <small class="text-muted d-block mt-1">{{ $vaccine['minimum_age_label'] ?? '—' }}</small>
    </td>

    <td class="text-center">
        <div>{{ $vaccine['route_label'] ?? '—' }}</div>
        <small class="text-muted d-block">{{ $vaccine['application_site_label'] ?? '—' }}</small>
        <small class="text-muted d-block">{{ $vaccine['dosage'] ?? '—' }}</small>
    </td>

    <td class="text-center">
        <div class="small text-black">
            <b class="text-purple">Atual:</b> {{ $inventory['current_stock'] ?? 0 }}
        </div>
        <div class="small text-black">
            <b class="text-purple">Mínimo:</b> {{ $inventory['minimum_stock'] ?? 0 }}
        </div>
        <div class="small text-black">
            <b class="text-purple">Seguro:</b> {{ $inventory['safety_stock'] ?? 0 }}
        </div>
    </td>

    <td class="text-center">
        <span class="{{ $statusClasses }}">{{ $vaccine['status_label'] ?? '--' }}</span>
        <small class="text-muted d-block mt-1">{{ $vaccine['booster_interval_label'] ?? '—' }}</small>
    </td>
</tr>