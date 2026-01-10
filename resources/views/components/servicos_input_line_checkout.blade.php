<div class='d-flex flex-column'>

    <div class='d-flex align-items-center justify-content-between'>
        <h3 class='os__subtotal'>DESCONTO</h3>

        <input type="hidden" value="{{ $ordem->id ?? '' }}" name="ordem_servico_id">
        <div class="d-flex-col mb-2">
            <input style='width: 180px;' id='desconto' name='desconto' class='form-control moeda desconto-field'
                placeholder='Valor do desconto R$'
                value="{{ isset($ordem) && $ordem?->desconto == 0 ? '' : 'R$' . __moeda(isset($ordem) ? $ordem->desconto : 0) }}" />
        </div>

    </div>

    <div class='d-flex align-items-center justify-content-between'>
        <h3 class='os__subtotal'>SUBTOTAL</h3>
        <input style='width: 160px;' id="inp-subtotal_final"
            class='os__textInputReset os__textSubInput form-control moeda subtotal-itens' placeholder="R$ 0,00" disabled
            value="{{ 'R$ ' . __moeda(isset($ordem) ? $ordem->getSubtotalItensAndServicesAttribute() : 0) }}" />
    </div>

    <div class='d-flex align-items-center justify-content-between'>
        <h3 class='os__total'>TOTAL</h3>
        <input style='width: 160px;' id="inp-total_final"
            class='os__textInputReset os__textInput form-control moeda total-itens' placeholder="R$ 0,00" disabled
            value="{{ 'R$ ' . __moeda(isset($ordem) ? $ordem->getTotalValueAttribute() : 0) }}" />
    </div>
</div>
