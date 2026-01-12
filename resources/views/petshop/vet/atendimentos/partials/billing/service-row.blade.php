@php
    $serviceId = data_get($row ?? [], 'id');
    $serviceName = data_get($row ?? [], 'nome');
    $serviceValue = data_get($row ?? [], 'valor');
    $serviceDate = data_get($row ?? [], 'data');
    $serviceTime = data_get($row ?? [], 'hora');
    $serviceCategory = data_get($row ?? [], 'categoria');
@endphp

<tr class="dynamic-form">
    <td>
        <div class="d-flex align-items-center gap-2">
            <select
                class="form-select vet-billing-service-select"
                name="servico_ids[]"
                data-placeholder="Selecione o serviço"
            >
                @if ($serviceId)
                    <option value="{{ $serviceId }}" selected>{{ $serviceName }}</option>
                @endif
            </select>
            <a
                href="{{ route('servicos.create') }}"
                class="btn btn-outline-secondary btn-sm"
                target="_blank"
                title="Cadastrar serviço"
            >
                <i class="ri-add-circle-line"></i>
            </a>
        </div>
        <input type="hidden" name="servico_categoria[]" value="{{ $serviceCategory }}">
    </td>
    <td>
        <input
            type="date"
            class="form-control"
            name="servico_datas[]"
            value="{{ $serviceDate }}"
        >
    </td>
    <td>
        <input
            type="time"
            class="form-control"
            name="servico_horas[]"
            value="{{ $serviceTime }}"
        >
    </td>
    <td>
        <input
            type="tel"
            class="form-control moeda vet-billing-service-value"
            name="servico_valor[]"
            value="{{ $serviceValue !== null ? __moeda($serviceValue) : '' }}"
        >
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm vet-billing-remove-row" title="Remover linha">
            <i class="ri-delete-bin-line"></i>
        </button>
    </td>
</tr>