<div class="modal fade" id="modal_novo_agendamento_estetica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-height: 90%">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Nova Reserva de Estética
                <i class="ri-sparkling-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">

                {!! 
                    Form::open() 
                    ->id('form-novo-agendamento-estetica')
                    ->post()
                !!}
                    <ul class="nav nav-tabs nav-primary" role="tablist">
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link active" data-bs-toggle="tab" href="#estetica_info_geral" role="tab"
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
                            <a class="px-3 nav-link" data-bs-toggle="tab" href="#estetica_servicos_produtos" role="tab">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-box-2-line"></i>
                                        Serviços e produtos
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link" data-bs-toggle="tab" href="#estetica_agendamento" role="tab">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-calendar-2-line"></i>
                                        Agendamento
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    
                    <input type="hidden" id="empresa_id" value="{{ auth()->user()->empresa->empresa_id }}">

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="estetica_info_geral">
                            @include('components.petshop.esteticas.tabs.estetica_info_geral')
                        </div>  
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade" id="estetica_servicos_produtos">
                            @include('components.petshop.esteticas.tabs.estetica_servicos_produtos')
                        </div>  
                    </div>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade" id="estetica_agendamento">
                            @include('components.petshop.esteticas.tabs.estetica_agendamento')
                        </div>  
                    </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 align-items-center justify-content-end gap-2 new-colors">
                    <button 
                        type="button" 
                        class="btn btn-success btn-cancel" 
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        id="submit_novo_agendamento_estetica"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>