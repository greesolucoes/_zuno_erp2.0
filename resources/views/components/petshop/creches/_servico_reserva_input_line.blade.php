<table class="table table-dynamic table-creche-servico-reserva">
    <thead>
        <tr>
            <th width="40%">
                <div class="d-flex gap-1 align-items-end">
                    <div class="required">
                        Serviço de Reserva
                    </div>
                    <button 
                        type="button" 
                        class="btn btn-link btn-tooltip btn-sm" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Esse é o serviço que se refere a reserva do hotel">
                        <i class="ri-file-info-fill"></i>
                    </button>
                </div>
            </th>
            <th width="25%" class="required">Valor</th>
        </tr>
    </thead>
    <tbody>
        @isset($reserva)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_ids[]" 
                            required 
                            data-label="Serviço de reserva"
                            data-is-reserva=true
                        >
                            <option class="selected-option" value="{{ $reserva->id }}">{{ $reserva->nome }}</option>
                        </select>
                        <a href="{{ route('servicos.create') }}" target="_blank">
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                    <input value="{{ $reserva->categoria->nome }}" name="servico_categoria[]" type="hidden">
                </td>
                <td>
                    <input 
                        value="{{ __moeda($reserva->pivot->valor_servico) ?? 0 }}" 
                        class="form-control moeda valor-servico" 
                        type="tel" name="servico_valor[]"
                    >
                </td>
                <input name="tempo_execucao" type="hidden" value="{{ $reserva->tempo_execucao }}"/>
            </tr>
        @else
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_ids[]" 
                            required 
                            data-label="Serviço de reserva"
                            data-is-reserva=true
                            disabled
                        >
                        </select>
                        <a href="{{ route('servicos.create') }}" target="_blank">
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                    <input name="servico_categoria[]" type="hidden">
                </td>
                <td>
                    <input 
                        class="form-control moeda valor-servico" 
                        name="servico_valor[]" 
                        type="tel"
                        data-label="Valor da reserva"
                        placeholder="R$ 0,00"
                        required
                        disabled
                    >
                </td>
                <input name="tempo_execucao" type="hidden"/>
            </tr>
        @endif
    </tbody>
</table>
