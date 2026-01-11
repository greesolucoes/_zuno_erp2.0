<table class="table table-dynamic table-creche-produtos table-responsive">
    <thead>
        <tr>
            <th width="40%">Produto</th>
            <th>Qtd</th>
            <th>Valor Unit.</th>
            <th>Subtotal</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    @isset($produtos)
        @forelse($produtos as $item)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 produto_id" name="produto_id[]">
                            <option value="{{ $item->id }}">{{ $item->nome }}</option>
                        </select>
                        <a href="{{ route('produtos.create') }}" target="_blank">
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                </td>
                <td>
                    <input value="{{ $item->pivot->quantidade ?? 1 }}" class="form-control qtd-produto" type="tel" name="qtd_produto[]">
                </td>
                <td>
                    <input value="{{ __moeda($item->valor_unitario ?? 0) }}" class="form-control moeda valor_unitario-produto" type="tel" name="valor_unitario_produto[]" disabled>
                </td>
                <td>
                    <input value="{{ __moeda(($item->valor_unitario ?? 0) * ($item->pivot->quantidade ?? 1)) }}" class="form-control moeda subtotal-produto" type="tel" name="subtotal_produto[]" disabled>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm creche-btn-remove-tr">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 produto_id" name="produto_id[]"></select>
                        <a href="{{ route('produtos.create') }}" target="_blank">
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                </td>
                <td>
                    <input class="form-control qtd-produto" type="tel" name="qtd_produto[]">
                </td>
                <td>
                    <input class="form-control moeda valor_unitario-produto" type="tel" name="valor_unitario_produto[]" disabled>
                </td>
                <td>
                    <input class="form-control moeda subtotal-produto" type="tel" name="subtotal_produto[]" disabled>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm creche-btn-remove-tr">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        @endforelse
    @else
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select class="select2 produto_id" name="produto_id[]"></select>
                    <a href="{{ route('produtos.create') }}" target="_blank">
                        <button class="btn btn-primary" type="button">
                            <i class="bx bx-plus"></i>
                        </button>
                    </a>
                </div>
            </td>
            <td>
                <input class="form-control qtd-produto" type="tel" name="qtd_produto[]">
            </td>
            <td>
                <input class="form-control moeda valor_unitario-produto" type="tel" name="valor_unitario_produto[]" disabled>
            </td>
            <td>
                <input class="form-control moeda subtotal-produto" type="tel" name="subtotal_produto[]" disabled>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm creche-btn-remove-tr">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    @endisset
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="new-colors">
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-primary btn-add-tr px-2" data-content="produtos">
                        <i class="bx bx-plus"></i>
                        Adicionar Produto
                    </button>
                    <div class="text-right text-green">
                        Total: <strong class="total-produtos"></strong>
                    </div>
                </div>
            </td>
        </tr>
    </tfoot>
</table>
