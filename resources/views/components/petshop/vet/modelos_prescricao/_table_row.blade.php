<tr>
    <td>
        <div class="d-inline-flex gap-2">
            <a 
                class="border-0 m-0 p-0 bg-transparent text-color-back"
                title="Visualizar Modelo"
                href="{{ route('vet.prescription-models.show', ['modeloPrescricao' => $item['id'], 'page' => request()->query('page', 1)]) }}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Modelo"
                >
            </a>

            <a
                href="{{ route('vet.prescription-models.edit', ['modeloPrescricao' => $item['id'], 'page' => request()->query('page', 1)]) }}"
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
    <td class="text-center">{{ $item['category'] ?? '—' }}</td>
    <td>
        {{ $item['updated_at'] ?? '—' }}
    </td>
    <td class="text-center">
        <span class="p-2 fw-semibold {{ $item['status_class'] ?? 'badge bg-light text-dark' }}">{{ $item['status'] ?? '—' }}</span>
    </td>
</tr>