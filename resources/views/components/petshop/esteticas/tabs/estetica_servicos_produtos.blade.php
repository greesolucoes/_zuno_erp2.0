<div class="row mt-3">
    <div class="col-12">
        @include('components.estetica.servicos_input_line', ['show_actions' => 1, 'servicos' => isset($data) ? $data->servicos : []])
    </div>

    <div class="col-12 mt-5">
        @include('components.estetica.produtos_input_line', ['show_actions' => 1, 'produtos' => isset($data) ? $data->produtos : []])
    </div>

    <div class="col-8 mt-5">
        @include('components.estetica._servico_frete_input_line', ['frete' => isset($frete) ? $frete : null])
    </div>
</div>

@include('modals._endereco_cliente')