<div class="table-responsive">
    <table class="table table-dynamic table-modal-servicos-estetica">
        <thead>
            <tr>
                <th width="70%">Serviço</th>
                <th width="25%">Total</th>
                <th width="5%">Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr class="dynamic-form d-none">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select name="servico_id[]"></select>
                        <a href={{ route('servicos.create') }} target='_blank'>
                            <button class="btn btn-dark" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </a>
                        <input type="hidden" name="id_servico[]">
                        <input type="hidden" name="label_servico[]">
                        <input type="hidden" name="tempo_execucao">
                    </div>
                </td>
                <td>
                    <input class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]">
                </td>
                    <td>
                        <button type="button" class="btn btn-danger pethsop-modal-btn-remove-tr">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="row col-6 col-lg-3 new-colors">
    <br>
    <button type="button" class="btn btn-dark btn-add-estetica-modal-tr px-2" data-content="servicos">
        <i class="ri-add-fill"></i>
        Adicionar Serviço
    </button>
</div>