<table class="table table-dynamic table-servicos table-responsive">
    <thead>
        <tr>
            <th width="40%">Serviço</th>
            <th>Quantidade</th>
            <th>Valor Unit.</th>
            <th>Subtotal</th>
            @if (isset($show_actions) && $show_actions == 1)
                <th>Ações</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @isset($servicos_agendados)
        @forelse($servicos as $item)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 servico_id" name="servicos[]" >
                            <option class="selected-option" value="{{ $item->servico_id }}">{{ $item->nome }}</option>
                        </select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <button class="btn btn-dark" type="button" id="btn-add-servico">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        @endif
                    </div>
                </td>
                <td>
                    <input 
                        value="{{ $item->quantidade ?? ''}}"
                        class="form-control qtd-servico" 
                        type="tel" 
                        name="qtd_servico[]" 
                        data-mask="000000"
                        placeholder="0"
                    >
                </td>
                <td>
                    <input 
                        value="{{ __moeda($item->valor) ?? ''}}"
                        class="form-control moeda valor_unitario-servico" 
                        type="tel" 
                        name="valor[]"
                        placeholder="R$ 0,00"
                    >
                </td>
                <td>
                    <input value="{{ __moeda($item->valor * $item->quantidade) ?? ''}}"
                    class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]" disabled>
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger os-btn-remove-tr">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                @endif
                <input type="hidden" name="tempo_execucao[]" value="{{ $item->tempo_execucao ?? ''}}"/>
            </tr>
        @empty
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 servico_id" name="servicos[]">
                        </select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <button class="btn btn-dark btn-add-servico" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        @endif
                    </div>
                </td>
                <td>
                    <input 
                        class="form-control qtd-servico" 
                        type="tel"
                        name="qtd_servico[]" 
                        data-mask="000000"
                        placeholder="0"
                    >
                </td>
                <td>
                    <input 
                        class="form-control moeda valor_unitario-servico" 
                        type="tel"
                        name="valor[]" 
                        placeholder="R$ 0,00"
                    >
                </td>
                <td>
                    <input 
                        readonly 
                        class="form-control moeda subtotal-servico"
                        type="tel" 
                        name="subtotal_servico[]" 
                        placeholder="R$ 0,00"
                    >
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger os-btn-remove-tr">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                @endif
                <input type="hidden" name="tempo_execucao[]"/>
            </tr>
        @endforelse
    @else
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select class="select2 servico_id" name="servicos[]">
                    </select>
                </div>
            </td>
            <td>
                <input 
                    class="form-control qtd-servico" 
                    data-mask="000000"
                    type="tel"
                    name="qtd_servico[]" 
                    placeholder="0"
                >
            </td>
            <td>
                <input 
                    class="form-control moeda valor_unitario-servico" 
                    type="tel"
                    name="valor[]"
                    placeholder="R$ 0,00"
                >
            </td>
            <td>
                <input 
                readonly 
                class="form-control moeda subtotal-servico"
                type="tel" name="subtotal_servico[]" 
                placeholder="R$ 0,00"
                >
            </td>
            @if (isset($show_actions) && $show_actions == 1)
                <td>
                    <button type="button" class="btn btn-danger os-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            @endif
            <input type="hidden" name="tempo_execucao[]"/>
        </tr>
    @endisset
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="new-colors">
                <div class="d-flex justify-content-end align-items-center">
                    <div class="text-right text-green">
                        Total: <strong class="total-servicos">R$ 0,00</strong>
                    </div>
                </div>
            </td>
        </tr>
    </tfoot>
</table>

@if (isset($show_actions) && $show_actions == 1)
    <div class="row col-12 col-lg-2">
        <div class="d-flex gap-2">
            <div class="new-colors">
            <button type="button" class="btn btn-dark btn-add-tr d-flex align-items-center gap-1 px-2" data-content="servicos" style="white-space: nowrap;">
                <i class="ri-add-fill"></i>
                Adicionar Serviço
            </button>
            </div>
            <button type="button" class="btn btn-dark d-flex align-items-center gap-1 px-2" id="btn-add-servico" style="white-space: nowrap;">
                <i class="ri-add-circle-fill"></i>
                Novo Serviço
            </button>
        </div>
    </div>
@endif
