<div class="modal fade" id="edit_reserva_estetica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-height: 90%">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Editar Reserva de Estética
                <i class="ri-sparkling-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">
                {!! 
                    Form::open() 
                    ->id('form-edit-reserva-estetica')
                    ->put()
                !!}
                    <ul class="nav nav-tabs nav-primary" role="tablist">
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link active" data-bs-toggle="tab" href="#editar_estetica_info_geral" role="tab"
                                aria-selected="true">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-file-user-fill"></i>
                                        Informações gerais
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link" data-bs-toggle="tab" href="#editar_estetica_agendamento" role="tab">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-calendar-2-line"></i>
                                        Agendamento
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="editar_estetica_info_geral">
                            <div class="col-10 d-flex align-items-end gap-2 new-colors mt-3">
                                <div class="col-6">
                                    {!! 
                                        Form::select('colaborador_id', 'Colaborador')
                                    !!}

                                    <input type="hidden" name="nome_colaborador"/>
                                    <input type="hidden" name="id_colaborador"/>
                                </div>
                                <div class="col-4">
                                    <button 
                                        id="clear-funcionario" 
                                        class="btn btn-primary" 
                                        type="button"
                                    >
                                        <i class="ri-eraser-fill"></i>Limpar colaborador
                                    </button>
                                </div>  
                            </div>

                            <hr>

                            <div class="col-12">
                                @include('components.estetica.modal.servico_input_line')
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade" id="editar_estetica_agendamento">
                            <div class="col-12 mt-3">
                                @include('components.petshop.esteticas.tabs.estetica_agendamento')
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id_estetica" id="id_estetica"/>
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
                        id="submit_update_reserva_estetica"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>