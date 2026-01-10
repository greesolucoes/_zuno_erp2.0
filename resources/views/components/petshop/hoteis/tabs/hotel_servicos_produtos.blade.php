<div class="row mt-3">
    <div class="col-12">
        <div class="row">
            <div class="col-12">
                @include('components.petshop.hoteis._servicos_input_line', ['servicos' => isset($data) ? $data->servicos : null, 'reserva' => $data ?? null])
            </div>
            <div class="col-12 mt-3">
                @include('components.petshop.hoteis._produtos_input_line', ['produtos' => isset($data) ? $data->produtos : null])
            </div>
            <div class="col-8">
                @include('components.petshop.hoteis._servico_frete_input_line', ['frete' => isset($frete) ? $frete : null])
            </div>
        </div>
    </div>
</div>

@include('modals._endereco_cliente')