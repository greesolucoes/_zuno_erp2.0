<table class="table table-dynamic table-creche-servicos table-responsive">
    <thead>
        <tr>
            <th width="40%">Serviço Extra</th>
            <th width="20%">Data de Início</th>
            <th width="15%">Hora de Início</th>
            <th width="15%">Valor</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    @isset($servicos)
        @forelse($servicos as $item)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_ids[]" 
                            data-label="Serviço extra"
                        >
                            <option class="selected-option" value="{{ $item->id }}">{{ $item->nome }}</option>
                        </select>
                        <a href="{{ route('servicos.create') }}" target="_blank">
                            <button class="btn btn-dark" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </a>
                    </div>
                    <input value="{{ $item->categoria->nome }}" name="servico_categoria[]" type="hidden">
                    <input name="tempo_execucao" value="{{ $item->tempo_execucao }}" type="hidden">
                </td>
                <td>
                    <input 
                        type="date" 
                        class="form-control" 
                        name="servico_datas[]" 
                        value="{{ $item->pivot->data_servico }}" 
                        data-label="Dia de início do serviço extra"
                    >
                </td>
                <td>
                    <input 
                        type="time" 
                        class="form-control" 
                        name="servico_horas[]" 
                        value="{{ $item->pivot->hora_servico }}" 
                        data-label="Hora de início do serviço extra"
                    >
                </td>
                <td>
                    <input 
                        value="{{ __moeda($item->pivot->valor_servico) }}" 
                        class="form-control moeda valor-servico" 
                        type="tel" 
                        name="servico_valor[]" 
                    >
                </td>
                <td>
                    <button type="button" class="btn btn-danger creche-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select 
                            class="servico_id" 
                            name="servico_ids[]" 
                            data-label="Serviço"
                        ></select>
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
                        type="date" 
                        class="form-control" 
                        name="servico_datas[]" 
                        data-label="Dia de início do serviço extra"
                    >
                </td>
                <td>
                    <input 
                        type="time" 
                        class="form-control" 
                        name="servico_horas[]" 
                        data-label="Hora de início do serviço extra"
                    >
                </td>
                <td>
                    <input 
                        class="form-control moeda valor-servico" 
                        name="servico_valor[]" 
                        type="tel"
                    >
                </td>
                <td>
                    <button type="button" class="btn btn-danger creche-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        @endforelse
    @else
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select class="servico_id" name="servico_ids[]" data-label="Serviço"></select>
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
                    type="date" 
                    class="form-control" 
                    name="servico_datas[]" 
                    data-label="Dia de início do serviço extra"
                >
            </td>
            <td>
                <input 
                    type="time" 
                    class="form-control" 
                    name="servico_horas[]" 
                    data-label="Hora de início do serviço extra"
                    disabled
                >
            </td>
            <td>
                <input class="form-control moeda valor-servico" type="tel" name="servico_valor[]" >
            </td>
            <td>
                <button type="button" class="btn btn-danger creche-btn-remove-tr">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    @endisset
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="new-colors">
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-dark btn-add-tr px-2" data-content="servicos">
                        <i class="ri-add-fill"></i>
                        Adicionar Serviço
                    </button>
                    <div class="text-right text-green">
                        Total: <strong class="total-servicos"></strong>
                    </div>
                </div>
            </td>
        </tr>
    </tfoot>
</table>