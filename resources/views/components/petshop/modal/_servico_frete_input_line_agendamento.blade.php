<table class="table table-dynamic table-servico-frete">
    <thead>
        <tr>
            <th width="70%">
                Serviço de Frete
            </th>
            <th width="15%">Valor</th>
            <th width="15%">Ações</th>
        </tr>
    </thead>
    <tbody>
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select 
                        class="servico_id" 
                        name="servico_frete" 
                        data-label="Serviço de frete"
                    >
                    </select>
                    <input name="servico_frete_id" type="hidden">
                    <input name="servico_frete_nome" type="hidden">
                    <a href="{{ route('servicos.create') }}" target="_blank">
                        <button class="btn btn-dark" type="button">
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </a>
                </div>
            </td>
            <td>
                <input 
                    class="form-control moeda valor-servico" 
                    name="servico_frete_valor" 
                    type="tel"
                    data-label="Valor do frete"
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
            <td colspan="3" class="text-right">
                <button
                    class="btn btn-primary"
                    id="handle-address-btn"
                    type="button"
                    disabled
                >
                    {{-- Conteúdo definido pelo JS --}}
                </button>     
            </td>
        </tr>

        <input type="hidden" name="endereco_cliente" id="endereco_cliente" />
    </tfoot>
</table>