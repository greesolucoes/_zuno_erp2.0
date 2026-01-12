<div class="modal fade" id="produtos_petshop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                {{-- Título definido pelo JS --}}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">
                {!! 
                    Form::open() 
                    ->id('form-handle-produtos-petshop')
                    ->put()
                !!}
                    <div class="col-12">
                        @include('components.petshop.modal._produto_input_line_agendamento')
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
                        id="submit_update_produtos"
                        data-modulo=""{{-- Valor definido pelo JS --}}
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>