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
    <tbody class="table-scroll-wrapper">
    @isset($servicos)
        @forelse($servicos as $item)
            @php
            $desc_servico = $item->servico->nome . ' (' . $item->servico->tipo_servico_label . ')';
            @endphp
            <tr class="dynamic-form">

                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 servico_id" name="servico_id[]">
                            <option class="selected-option" value="{{ $item->servico_id }}">{{ $desc_servico }}</option>
                        </select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('servicos.create') }} target='_blank'>
                                <button class="btn btn-dark" type="button">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                    
                </td>

                <td>
                    <input value="{{ $item->quantidade ?? ''}}"
                        class="form-control qtd-servico" type="tel" name="qtd_servico[]">
                </td>
                <td>
                    <input value="{{ __moeda($item->valor) ?? ''}}"
                        class="form-control moeda valor_unitario-servico" type="tel" name="valor_unitario[]">
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
            </tr>
        @empty
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 servico_id" name="servico_id[]">
                        </select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('servicos.create') }} target='_blank'>
                                <button class="btn btn-dark" type="button">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                </td>
                <td>
                    <input class="form-control qtd-servico" type="tel"
                            name="qtd_servico[]" placeholder="Informe a quantidade">
                </td>
                <td>
                    <input class="form-control moeda valor_unitario-servico" type="tel"
                        name="valor_unitario[]">
                </td>
                <td>
                    <input readonly class="form-control moeda subtotal-servico"
                        type="tel" name="subtotal_servico[]" disabled>
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger os-btn-remove-tr">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                @endif
            </tr>
        @endforelse
    @else
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select class="select2 servico_id" name="servico_id[]">
                    </select>
                    @if (isset($show_actions) && $show_actions == 1)
                        <a href={{ route('servicos.create') }} target='_blank'>
                            <button class="btn btn-dark" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </a>
                    @endif
                </div>
            </td>

            <td>
                <input class="form-control qtd-servico" type="tel" name="qtd_servico[]" id="qtd_servico" required>
            <td>
                <input class="form-control moeda valor_unitario-servico" type="tel" name="valor_unitario[]" id="valor_unitario">
            </td>

            <td>
                <input readonly class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]" disabled>
            </td>
            @if (isset($show_actions) && $show_actions == 1)
                <td>
                    <button type="button" class="btn btn-danger os-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            @endif
        </tr>
    @endisset
    </tbody>
</table>

@if (isset($show_actions) && $show_actions == 1)
    <div class="row col-12 col-lg-2 new-colors">
        <br>
        <button type="button" class="btn btn-dark btn-add-tr px-2" data-content="servicos">
            <i class="ri-add-fill"></i>
            Adicionar Serviço
        </button>
    </div>
@endif
