<table class="table table-dynamic table-servico-frete">
    <thead>
        <tr>
            <th width="40%">
                <div class="d-flex gap-1 align-items-end">
                    <div>
                        Serviço de Frete
                    </div>
                    <button 
                        type="button" 
                        class="btn btn-link btn-tooltip btn-sm" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Esse é o serviço que se refere ao transporte do pet para o agendamento">
                        <i class="ri-file-info-fill"></i>
                    </button>
                </div>
            </th>
            <th>Valor</th>
            <th>Co-participação</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @isset($frete)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_id[]" 
                            data-label="Serviço de frete"
                            data-is-frete=true
                        >
                            <option class="selected-option" value="{{ $frete->servico_id }}">{{ $frete->servico->nome }}</option>
                        </select>
                        <a href="{{ route('servicos.create') }}" target="_blank">
                            <button class="btn btn-dark" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </a>
                    </div>
                    <input value="{{ $frete->servico->categoria->nome }}" name="servico_categoria[]" type="hidden">
                </td>
                <td>
                    <input 
                        value="{{ __moeda($frete->valor_servico) ?? 0 }}" 
                        class="form-control moeda subtotal-servico" 
                        type="tel" 
                        name="subtotal_servico[]"
                    >
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" name="coparticipacao_tipo[]">
                            <option value="" {{ $frete->coparticipacao_tipo == null ? 'selected' : '' }}>Não</option>
                            <option value="percentual" {{ $frete->coparticipacao_tipo == 'percentual' ? 'selected' : '' }}>%</option>
                            <option value="valor_fixo" {{ $frete->coparticipacao_tipo == 'valor_fixo' ? 'selected' : '' }}>R$</option>
                        </select>
                        <input value="{{ $frete->coparticipacao_valor_display ?? __moeda($frete->coparticipacao_valor) ?? '' }}" class="form-control moeda" type="tel" name="coparticipacao_valor[]">
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-danger os-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        @else
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_id[]" 
                            data-label="Serviço de frete"
                            data-is-frete=true
                        >
                        </select>
                        <a href="{{ route('servicos.create') }}" target="_blank">
                            <button class="btn btn-dark" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </a>
                    </div>
                    <input name="servico_categoria[]" type="hidden">
                    <input name="tempo_execucao" type="hidden">
                </td>
                <td>
                    <input 
                        class="form-control moeda subtotal-servico" 
                        name="subtotal_servico[]" 
                        type="tel"
                        data-label="Valor do frete"
                        placeholder="R$ 0,00"
                    >
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" name="coparticipacao_tipo[]">
                            <option value="">Não</option>
                            <option value="percentual">%</option>
                            <option value="valor_fixo">R$</option>
                        </select>
                        <input class="form-control moeda" type="tel" name="coparticipacao_valor[]">
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-danger os-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        @endif
    </tbody>
</table>