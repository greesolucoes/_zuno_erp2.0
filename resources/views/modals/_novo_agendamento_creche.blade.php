<div class="modal fade" id="modal_novo_agendamento_creche" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Nova Reserva de Creche
                <i class="ri-graduation-cap-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">

                {!! 
                    Form::open() 
                    ->id('form-novo-agendamento-creche')
                    ->post()
                !!}
                    <ul class="nav nav-tabs nav-primary" role="tablist">
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link active" data-bs-toggle="tab" href="#creche_info_geral" role="tab"
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
                            <a class="px-3 nav-link" data-bs-toggle="tab" href="#creche_agendamento" role="tab">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-calendar-2-line"></i>
                                        Agendamento
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link" data-bs-toggle="tab" href="#creche_servicos_produtos" role="tab">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-box-2-line"></i>
                                        Serviços e produtos
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="creche_info_geral" role="tabpanel">
                            @include('components.petshop.creches.tabs.creche_info_geral')
                        </div>

                        <div class="tab-pane fade" id="creche_agendamento" role="tabpanel">
                            @include('components.petshop.creches.tabs.creche_agendamento')
                        </div>
                        
                        <div class="tab-pane fade" id="creche_servicos_produtos" role="tabpanel">
                            @include('components.petshop.creches.tabs.creche_servicos_produtos')
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 align-items-center justify-content-end gap-2 new-colors">
                    <button 
                        type="button" 
                        class="btn btn-success btn-close-modal" 
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        id="submit_novo_agendamento_creche"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>