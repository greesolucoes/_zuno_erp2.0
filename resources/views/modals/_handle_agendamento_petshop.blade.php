<div class="modal fade" id="handle_modal_agendamento" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                {{-- Título definido pelo JS --}}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content" style="height: 95%">
            <div class="modal-body p-3">
                <div class="d-flex align-items-start gap-5">
                    <div class="d-flex flex-column gap-2 justify-content-center" id="status-container">
                        <button 
                            class="btn-modal-status btn-modal-status btn-agendado"
                            data-value="agendado" 
                        >
                            <i class="ri-calendar-event-line mr-3"></i> Agendado <br>(AG)
                        </button>
                        <button 
                            class="btn-modal-status btn-modal-status btn-em-andamento"
                            data-value="em_andamento" 
                        >
                            <i class="ri-hourglass-fill mr-3"></i> Em Andamento <br>(EA)
                        </button>
                        <button 
                            class="btn-modal-status btn-modal-status btn-concluido"
                            data-value="concluido" 
                        >
                            <i class="ri-check-double-line mr-3"></i> Concluído <br>(CL)
                        </button>
                        <button 
                            class="btn-modal-status btn-modal-status btn-cancelado" 
                            data-value="cancelado"
                        >
                            <i class="ri-close-circle-line"></i> Cancelado <br>(CC)
                        </button>
                        <button 
                            class="btn-modal-status btn-modal-status btn-rejeitado" 
                            data-value="rejeitado"
                        >
                            <i class="ri-calendar-close-line mr-3"></i> Rejeitado <br>(RJ)
                        </button>
                        <button 
                            class="btn-modal-status btn-modal-status btn-pendente-aprovacao" 
                            data-value="pendente_aprovacao"
                        >
                            <i class="ri-timer-fill mr-3"></i> Aprovação Pendente <br>(AP)
                        </button>
                    </div>
                    <div class="row col-10">
                        <div class="col">
                            <div class="card d-none" id="reserva-container">
                                <div class="card-body">
                                    <div 
                                        class="d-flex align-items-center gap-1 text-color fw-semibold fs-4"
                                        id="reserva-container-title"
                                    >
                                        
                                    </div>
                                    <div id="reserva-content"></div>
                                    <button 
                                        class="btn btn-sm btn-agendamento-secondary mt-2 ms-auto d-block"
                                        id="btn-edit-reserva"
                                    >
                                    </button>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-1 text-color fw-semibold fs-4">
                                        <i class="ri-list-check-3"></i>
                                        Serviços Extras
                                    </div>
                                    <div 
                                        id="servicos-container"
                                        class="d-flex flex-column justify-content-center gap-2"
                                    >
                                        
                                    </div>
                                    <button id="btn-edit-servicos" class="btn btn-sm btn-agendamento-secondary mt-2 ms-auto d-block">
                                        {{-- Valores definidos pelo JS --}}
                                    </button>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-1 text-color fw-semibold fs-4">
                                        <i class="ri-e-bike-line"></i>
                                        Frete
                                    </div>
                                    <div 
                                        id="servico-frete-container"
                                        class="d-flex flex-column justify-content-center gap-2"
                                    >
                                        
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end align-items-center">
                                        <button 
                                            id="btn-cupom-frete" 
                                            class="btn btn-sm btn-agendamento-secondary mt-2 d-block"
                                        >
                                            <i class="ri-article-fill"></i>
                                            Emitir cupom de entrega
                                        </button>
                                        <button id="btn-edit-frete" class="btn btn-sm btn-agendamento-secondary mt-2 d-block">
                                            {{-- Valores definidos pelo JS --}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-1 text-color fw-semibold fs-4">
                                        <i class="ri-box-2-line"></i>
                                        Produtos
                                    </div>
                                    <div 
                                        id="produtos-container"
                                        class="d-flex flex-column justify-content-center gap-2"
                                    >
                                        
                                    </div>
                                    <button id="btn-edit-produtos" class="btn btn-sm btn-agendamento-secondary mt-2 ms-auto d-block">
                                        {{-- Valores definidos pelo JS --}}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-1 text-color fw-semibold fs-4 mb-3">
                                        <i class="ri-account-box-line"></i>
                                        Cliente e Pet
                                    </div>  
                                    <div id="cliente-pet-content"></div>
                                    <div class="d-flex gap-3 justify-content-end align-items-center mt-2">
                                        <button id="btn-edit-cliente" class="btn btn-sm btn-agendamento-secondary">
                                            <i class="ri-edit-box-line"></i>
                                            Editar Cliente
                                        </button>
                                        <button id="btn-edit-pet" class="btn btn-sm btn-agendamento-secondary">
                                            <i class="ri-edit-box-line"></i>
                                            Editar Pet
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card" id="checklist-container">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-1 text-color fw-semibold fs-4">
                                        <i class="ri-survey-line"></i>
                                        Checklist
                                    </div>
                                    <div id="checklist-content"></div>
                                    <div id="checklist-btns" class="d-flex gap-3 justify-content-end align-items-center mt-2">
                                        <button id="btn-add-checklist-entrada" class="btn btn-sm btn-agendamento-secondary">
                                            <i class="ri-add-circle-line"></i> 
                                            Checklist Entrada
                                        </button>
                                        <button id="btn-add-checklist-saida" class="btn btn-sm btn-agendamento-secondary">
                                            <i class="ri-add-circle-line"></i> 
                                            Checklist Saída
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card" id="plano-container">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-1 text-color fw-semibold fs-4 mb-3">
                                        <i class="ri-bookmark-fill"></i>
                                        Plano
                                    </div>  
                                    <div id="plano-content"></div>
                                    <button id="btn-view-plano" class="btn btn-sm btn-agendamento-secondary mt-2 ms-auto d-block">
                                        <i class="ri-eye-line"></i>
                                        Visualizar agendamentos do plano
                                    </button>
                                </div>
                            </div>
                            <div class="card" id="fatura-container">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-1 text-color fw-semibold fs-4 mb-3">
                                        <i class="ri-coins-fill"></i>
                                        Fatura
                                    </div>  
                                    <div id="fatura-content" class="text-black new-colors pb-2 d-flex flex-column justify-content-center gap-1" style="border-bottom: 1px solid #48185b;">

                                    </div>
                                    <button id="btn-view-conta" class="btn btn-sm btn-agendamento-secondary mt-2 ms-auto d-block">
                                        <i class="ri-eye-line"></i>
                                        Visualizar conta a receber
                                    </button>
                                    <button id="btn-gerar-conta" class="btn btn-sm btn-agendamento-secondary mt-2 ms-auto d-block">
                                        <i class="ri-add-circle-line"></i>
                                        Gerar conta a receber
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 align-items-center justify-content-end gap-3">
                    <div>
                        <a
                            id="os-btn"
                            class="btn btn-agendamento-secondary gap-1 d-none"
                            href={{-- Valor definido pelo JS --}}
                        >
                            {{-- Valores definidos pelo JS --}}
                        </a>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>