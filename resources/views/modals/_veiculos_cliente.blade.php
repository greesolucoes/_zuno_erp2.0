<style>
    .modal-header.bg-dark {
        background-color: #3a1e4b !important;
        color: #fff;
    }
</style>
<div class="modal fade" id="modal_novo_veiculos_cliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalNovoVeiculoLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title d-flex align-items-center" id="modalNovoVeiculoLabel">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Novo Veículo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-5">
                    <div class="d-flex mb-4 gap-4 flex-wrap">
                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('placa', 'Placa do veículo')->attrs(['class' => 'placa'])->placeholder('Digite a placa do veículo') !!}
                        </div>

                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('marca', 'Marca')->placeholder('Digite a marca') !!}
                        </div>

                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('modelo', 'Modelo')->placeholder('Digite o modelo') !!}
                        </div>

                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('chassi', 'Chassi')->placeholder('Digite o número do chassi') !!}
                        </div>

                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('tipo_veiculo', 'Tipo do veículo')->placeholder('Digite o tipo do veículo') !!}
                        </div>
                    </div>

                    <div class="d-flex gap-4 flex-wrap">
                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('ano', 'Ano')->placeholder('Digite o ano do veículo') !!}
                        </div>

                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('cor', 'Cor')->placeholder('Digite a cor do veículo') !!}
                        </div>

                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('tipo_combustivel', 'Tipo do combustível')->placeholder('Digite o tipo do combustível') !!}
                        </div>

                        <div style="flex: 1; min-width: 200px;">
                            {!! Form::text('km', 'KM')->placeholder('Digite o KM') !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-veiculos-cliente-store">Salvar</button>
            </div>
        </div>
    </div>
</div>
