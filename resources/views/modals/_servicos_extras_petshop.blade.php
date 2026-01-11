<div class="modal fade" id="servicos_extras_petshop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                {{-- Título definido pelo JS --}}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">
                <div class="horario-container text-black" style="border: none !important; flex-direction: row !important; justify-content: start !important;">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="d-flex flex-column align-items-center justify-content-center gap-1 px-2">
                            <div id="data-entrada-area">
                                {{-- Data preenchida pelo JS --}}
                            </div>
                            <span id="horario-entrada-area" class="fw-semibold fs-4">
                                {{-- Horário preenchido pelo JS --}}
                            </span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="connect-circle"></div>
                            <div class="connect-row"></div>
                            <div class="connect-circle"></div>
                        </div>
                        <div>
                            <div class="d-flex flex-column align-items-center justify-content-center gap-1 p-2">
                                <div id="data-saida-area">
                                    {{-- Data preenchida pelo JS --}}
                                </div>
                                <span class="fw-semibold fs-4" id="horario-saida-area">
                                    {{-- Horário preenchido pelo JS --}}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                {!! 
                    Form::open() 
                    ->id('form-handle-extra-servicos-petshop')
                    ->put()
                !!}
                    <div class="col-12">
                        @include('components.petshop.modal._servico_input_line_agendamento')
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
                        id="submit_update_servicos_extras"
                        data-modulo=""{{-- Valor definido pelo JS --}}
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>