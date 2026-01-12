<div class="modal fade" id="servico_frete_petshop" data-modulo=""{{-- Valor definido pelo JS --}} tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Serviço de Frete
                <i class="ri-e-bike-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">

                {!! 
                    Form::open() 
                    ->id('form-handle-servicos-frete-petshop')
                    ->put()
                !!}
                    <div class="col-12">
                        @include('components.petshop.modal._servico_frete_input_line_agendamento')
                    </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 align-items-center justify-content-end gap-2 new-colors">
                    <button 
                        type="button" 
                        class="btn btn-success" 
                        data-bs-toggle="modal" 
                        data-bs-target="#handle_modal_agendamento"
                    >
                        Voltar
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        id="submit_update_frete"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>