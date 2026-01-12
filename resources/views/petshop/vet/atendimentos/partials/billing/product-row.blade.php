@php
    $productId = data_get($row ?? [], 'id');
    $productName = data_get($row ?? [], 'nome');
    $quantity = data_get($row ?? [], 'quantidade');
    $unitValue = data_get($row ?? [], 'valor_unitario', data_get($row ?? [], 'valor'));
    $subtotal = data_get($row ?? [], 'subtotal');

    if ($subtotal === null && $quantity !== null && $unitValue !== null) {
        $subtotal = (float) $quantity * (float) $unitValue;
    }
@endphp

<tr class="dynamic-form">
    <td>
        <div class="d-flex align-items-center gap-2">
            <select
                class="form-select vet-billing-product-select"
                name="produto_id[]"
                data-placeholder="Selecione o produto"
            >
                @if ($productId)
                    <option value="{{ $productId }}" selected>{{ $productName }}</option>
                @endif
            </select>
            <a
                href="{{ route('produtos.create') }}"
                class="btn btn-outline-secondary btn-sm"
                target="_blank"
                title="Cadastrar produto"
            >
                <i class="ri-add-circle-line"></i>
            </a>
        </div>
    </td>
    <td>
        <input
            type="tel"
            class="form-control vet-billing-product-quantity"
            name="qtd_produto[]"
            value="{{ $quantity !== null ? $quantity : '' }}"
            inputmode="decimal"
            min="0"
        >
    </td>
    <td>
        <input
            type="tel"
            class="form-control moeda vet-billing-product-unit"
            name="valor_unitario_produto[]"
            value="{{ $unitValue !== null ? __moeda($unitValue) : '' }}"
            readonly
        >
    </td>
    <td>
        <input
            type="tel"
            class="form-control moeda vet-billing-product-subtotal"
            name="subtotal_produto[]"
            value="{{ $subtotal !== null ? __moeda($subtotal) : '' }}"
            readonly
        >
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm vet-billing-remove-row" title="Remover linha">
            <i class="ri-delete-bin-line"></i>
        </button>
    </td>
</tr>