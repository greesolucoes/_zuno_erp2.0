<table class="table table-dynamic table-creche-servico-frete">
    <thead>
        <tr>
            <th width="60%">
                <div class="d-flex gap-1 align-items-end">
                    <div>
                        Serviço de Frete
                    </div>
                    <button 
                        type="button" 
                        class="btn btn-link btn-tooltip btn-sm" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Esse é o serviço que se refere ao transporte do pet para a reserva">
                        <i class="ri-file-info-fill"></i>
                    </button>
                </div>
            </th>
            <th width="25%">Valor</th>
            <th width="15%">Ações</th>
        </tr>
    </thead>
    <tbody>
        @isset($frete)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_ids[]" 
                            data-label="Serviço de frete"
                            data-is-frete=true
                        >
                            <option class="selected-option" value="{{ $frete->id }}">{{ $frete->nome }}</option>
                        </select>
                        <a href="{{ route('servicos.create') }}" target="_blank">
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                    <input value="{{ $frete->categoria->nome }}" name="servico_categoria[]" type="hidden">
                </td>
                <td>
                    <input 
                        value="{{ __moeda($frete->pivot->valor_servico) ?? 0 }}" 
                        class="form-control moeda valor-servico" 
                        type="tel" 
                        name="servico_valor[]"
                    >
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm creche-btn-remove-tr">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        @else
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_ids[]" 
                            data-label="Serviço de frete"
                            data-is-frete=true
                        >
                        </select>
                        <a href="{{ route('servicos.create') }}" target="_blank">
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                    <input name="servico_categoria[]" type="hidden">
                    <input name="tempo_execucao" type="hidden">
                </td>
                <td>
                    <input 
                        class="form-control moeda valor-servico" 
                        name="servico_valor[]" 
                        type="tel"
                        data-label="Valor do frete"
                        placeholder="R$ 0,00"
                        disabled
                    >
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm creche-btn-remove-tr">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right">
                <button
                    class="btn btn-primary"
                    id="handle-address-btn"
                    type="button"
                    disabled
                    data-bs-toggle="modal"
                    data-bs-target="#modal_endereco_cliente"
                >
                    <i class="ri-map-pin-user-line"></i>
                    @if (!isset($data->crecheClienteEndereco))
                        Adicionar endereço do frete
                    @else 
                        Alterar endereço do frete
                    @endif
                </button>     
            </td>
        </tr>

        <input 
            type="hidden" 
            name="endereco_cliente" 
            id="endereco_cliente" 
            value="{{ isset($data->crecheClienteEndereco) ? $data->crecheClienteEndereco : null}}"
        />
    </tfoot>
</table>
