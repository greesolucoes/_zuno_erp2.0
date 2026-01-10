<table class="table table-dynamic table-produtos-plano table-responsive">
    <thead>
        <tr>
            <th width="40%">Produto</th>
            <th>Quantidade</th>
            <th>Valor Unit.</th>
            <th>Subtotal</th>
            @if (isset($show_actions) && $show_actions == 1)
                <th>Ações</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @isset($produtos)
        @forelse($produtos as $item)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 produto_id" name="produto_id[]">
                            <option value="{{ $item->produto_id }}">{{ optional($item->produto)->nome ?? 'Produto não encontrado' }}</option>
                        </select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('produtos.create') }} target='_blank'>
                                <button class="btn btn-dark" type="button">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                    <input name="variacao_id[]" type="hidden" value="{{ $item->variacao_id ?? '' }}">
                </td>
                <td>
                    <input value="{{ $item->quantidade_display ?? intval($item->quantidade) }}" class="form-control qtd-produto" type="tel" name="qtd_produto[]">
                </td>
                <td>
                    <input value="{{ __moeda($item->valor ?? optional($item->produto)->valor_unitario) ?? '' }}" class="form-control moeda valor_unitario-produto" type="tel" name="valor_unitario_produto[]" disabled>
                </td>
                <td>
                    <input value="{{ __moeda($item->subtotal ?? ($item->valor ?? optional($item->produto)->valor_unitario) * ($item->quantidade ?? 0)) }}" class="form-control moeda subtotal-produto" type="tel" name="subtotal_produto[]" disabled>
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
                        <select class="select2 produto_id" name="produto_id[]"></select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('produtos.create') }} target='_blank'>
                                <button class="btn btn-dark" type="button">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                    <input name="variacao_id[]" type="hidden" value="">
                </td>
                <td>
                    <input class="form-control qtd-produto" type="tel" name="qtd_produto[]">
                </td>
                <td>
                    <input class="form-control moeda valor_unitario-produto" type="tel" name="valor_unitario_produto[]" disabled>
                </td>
                <td>
                    <input readonly class="form-control moeda subtotal-produto" type="tel" name="subtotal_produto[]" disabled>
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
                    <select class="select2 produto_id" name="produto_id[]"></select>
                    @if (isset($show_actions) && $show_actions == 1)
                        <a href={{ route('produtos.create') }} target='_blank'>
                            <button class="btn btn-dark" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </a>
                    @endif
                </div>
                <input name="variacao_id[]" type="hidden" value="">
            </td>
            <td>
                <input class="form-control qtd-produto" type="tel" name="qtd_produto[]">
            </td>
            <td>
                <input class="form-control moeda valor_unitario-produto" type="tel" name="valor_unitario_produto[]" disabled>
            </td>
            <td>
                <input readonly class="form-control moeda subtotal-produto" type="tel" name="subtotal_produto[]" disabled>
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
        <button type="button" class="btn btn-dark btn-add-tr px-2" data-content="produtos">
            <i class="ri-add-fill"></i>
            Adicionar Produto
        </button>
    </div>
@endif