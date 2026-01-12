<tr>
    <td class="text-center">{{ $item['title'] ?? 'Modelo sem título' }}</td>
    <td class="text-center">{{ $item['category'] ?? '—' }}</td>
    <td>
        {{ $item['updated_at'] ?? '—' }}
    </td>
    <td class="text-center">
        <span class="p-2 fw-semibold {{ $item['status_class'] ?? 'badge bg-light text-dark' }}">{{ $item['status'] ?? '—' }}</span>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('vet.prescription-models.show', ['modeloPrescricao' => $item['id'], 'page' => request()->query('page', 1)]) }}" class="btn btn-info btn-sm text-white" title="Visualizar modelo">
                <i class="bx bx-show"></i>
            </a>
            <a href="{{ route('vet.prescription-models.edit', ['modeloPrescricao' => $item['id'], 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white" title="Editar modelo">
                <i class="bx bx-edit"></i>
            </a>
        </div>
    </td>
</tr>
