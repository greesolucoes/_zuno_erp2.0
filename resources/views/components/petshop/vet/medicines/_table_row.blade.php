@php
    $species = $medicine['species'] ?? [];
    $statusLabel = $medicine['status'] ?? '--';
    $normalizedStatus = strtolower($statusLabel);
    $statusClasses = match ($normalizedStatus) {
        'ativo' => 'badge bg-success-subtle text-success',
        'inativo' => 'badge bg-secondary-subtle text-secondary',
        default => 'badge bg-primary-subtle text-primary',
    };

    $stockColor = strtolower($medicine['stock_color'] ?? '');
    $stockClasses = match ($stockColor) {
        'success' => 'badge bg-success-subtle text-success',
        'warning' => 'badge bg-warning-subtle text-warning',
        'danger' => 'badge bg-danger-subtle text-danger',
        default => 'badge bg-secondary-subtle text-secondary',
    };
@endphp

<tr>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $medicine['commercial_name'] ?? '--' }}</div>

        @if (!empty($medicine['generic_name']))
            <small class="text-muted d-block">{{ $medicine['generic_name'] }}</small>
        @endif

        <small class="text-muted d-block">
            {{ collect([$medicine['presentation'] ?? null, $medicine['concentration'] ?? null])->filter()->implode(' • ') }}
        </small>

        @if (!empty($medicine['supplier']) || !empty($medicine['sku']))
            <small class="text-muted d-block">
                {{ collect([$medicine['supplier'] ?? null, $medicine['sku'] ?? null])->filter()->implode(' • ') }}
            </small>
        @endif
    </td>

    <td class="text-center">{{ $medicine['therapeutic_class'] ?? '--' }}</td>
    <td class="text-center">{{ $medicine['route'] ?? '--' }}</td>

    <td class="text-center">
        @if (!empty($species))
            @foreach ($species as $speciesItem)
                <span class="badge bg-secondary-subtle text-secondary me-1">{{ $speciesItem }}</span>
            @endforeach
        @else
            <span class="text-muted">--</span>
        @endif
    </td>

    <td class="text-center">
        <span class="{{ $stockClasses }}">{{ $medicine['stock_status'] ?? '--' }}</span>

        <div class="small text-muted mt-1">
            {{ 'Atual: ' . ($medicine['current_stock'] ?? 0) }}
            @if (!empty($medicine['minimum_stock']))
                {{ ' • Mín.: ' . $medicine['minimum_stock'] }}
            @endif
        </div>
    </td>

    <td class="text-center">
        <span class="{{ $statusClasses }}">{{ $statusLabel }}</span>

        @if (!empty($medicine['control_category']))
            <div class="small text-muted mt-1">{{ $medicine['control_category'] }}</div>
        @endif
    </td>
    <td>
        <form id="form-delete-medicine-{{ $medicine['id'] }}" action="{{ route('vet.medicines.destroy', [$medicine['id'], 'page' => request()->query('page', 1)]) }}" method="post">
            @method('delete')
            <a href="{{ route('vet.medicines.edit', [$medicine['id'], 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                <i class="bx bx-edit"></i>
            </a>
            @csrf
            <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir medicamento">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
