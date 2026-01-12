<table class="table table-dynamic table-produtos-agendamento table-responsive">
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
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select name="agendamento_produto_id[]"></select>
                    <a href="{{ route('produtos.create') }}" target="_blank">
                        <button class="btn btn-dark" type="button">
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </a>

                    <input type="hidden" name="id_produto">
                    <input type="hidden" name="nome_produto">
                </div>
            </td>
            <td>
                <input 
                    class="form-control quantidade qtd-produto" 
                    type="tel" 
                    name="agendamento_qtd_produto[]"
                    placeholder="0.00"
                >
            </td>
            <td>
                <input 
                    class="form-control moeda valor_unitario-produto" 
                    type="tel" 
                    name="agendamento_valor_unitario_produto[]" 
                    disabled
                    placeholder="R$ 0,00"
                >
            </td>
            <td>
                <input 
                    class="form-control moeda subtotal-produto" 
                    type="tel" 
                    name="agendamento_subtotal_produto[]" 
                    disabled
                    placeholder="R$ 0,00"
                >
            </td>
            <td>
                <button type="button" class="btn btn-danger pethsop-modal-btn-remove-tr">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="new-colors">
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-dark btn-add-produto px-2" data-content="produtos">
                        <i class="ri-add-fill"></i>
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