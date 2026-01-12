<table class="table table-dynamic table-modal-servicos-extras table-responsive">
    <thead>
        <tr>
            <th width="40%">Serviço Extra</th>
            <th width="20%">Data de início</th>
            <th width="15%">Hora de início</th>
            <th width="15%">Valor</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select name="extra_servico_ids[]" data-label="Serviço"></select>
                </div>
                <input name="extra_tempo_execucao" type="hidden">
                <input name="id_servico" type="hidden">
                <input name="nome_servico" type="hidden">
            </td>
            <td>
                <input 
                    type="date" 
                    class="form-control" 
                    name="extra_servico_datas[]" 
                    data-label="Dia de início do serviço extra"
                >
            </td>
            <td>
                <input 
                    type="time" 
                    class="form-control" 
                    name="extra_servico_horas[]" 
                    data-label="Hora de início do serviço extra"
                >
            </td>
            <td>
                <input class="form-control moeda valor-servico" type="tel" name="extra_servico_valor[]">
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
                    <button type="button" class="btn btn-dark btn-add-servico-extra-tr px-2" data-content="servicos">
                        <i class="ri-add-fill"></i>
                        Adicionar Serviço
                    </button>
                    <div class="text-right text-green">
                        Total: <strong class="total-servicos-extra"></strong>
                    </div>
                </div>
            </td>
        </tr>
    </tfoot>
</table>