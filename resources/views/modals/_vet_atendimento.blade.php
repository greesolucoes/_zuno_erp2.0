<div class="modal fade" id="handle_modal_atendimento_veterinario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Atendimento Veterinário do pet: <b id="vet-modal-title-pet">--</b>
                <i class="ri-stethoscope-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">
                <div class="d-flex flex-column flex-lg-row align-items-start gap-4">
                    <div class="d-flex flex-lg-column flex-row flex-wrap gap-2" id="vet-status-container">
                        <button class="btn-modal-status btn-agendado" data-value="agendado">
                            <i class="ri-calendar-event-line mr-3"></i> Agendado <br>(AG)
                        </button>
                        <button class="btn-modal-status btn-em-andamento" data-value="em_andamento">
                            <i class="ri-hourglass-fill mr-3"></i> Em andamento <br>(EA)
                        </button>
                        <button class="btn-modal-status btn-concluido" data-value="concluido">
                            <i class="ri-check-double-line mr-3"></i> Concluído <br>(CL)
                        </button>
                        <button class="btn-modal-status btn-cancelado" data-value="cancelado">
                            <i class="ri-close-circle-line mr-3"></i> Cancelado <br>(CC)
                        </button>
                    </div>
                    <div class="row flex-grow-1 g-3" id="vet-modal-details">
                        <div class="col-lg-6 d-flex flex-column gap-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 text-color fw-semibold fs-4">
                                        <i class="ri-calendar-event-line"></i>
                                        Resumo do atendimento
                                    </div>
                                    <span class="badge rounded-pill text-bg-primary mt-3" id="vet-status-badge"></span>
                                    <div id="vet-schedule-content" class="mt-3 d-flex flex-column gap-2 text-black"></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 text-color fw-semibold fs-4">
                                        <i class="ri-clipboard-line"></i>
                                        Observações
                                    </div>
                                    <div id="vet-notes-content" class="mt-3 text-black"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 d-flex flex-column gap-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 text-color fw-semibold fs-4">
                                        <i class="ri-account-circle-line"></i>
                                        Tutor
                                    </div>
                                    <div id="vet-tutor-content" class="mt-3 d-flex flex-column gap-1 text-black"></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 text-color fw-semibold fs-4">
                                        <i class="ri-bear-smile-line"></i>
                                        Paciente
                                    </div>
                                    <div id="vet-patient-content" class="mt-3 d-flex flex-column gap-1 text-black"></div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 text-color fw-semibold fs-4">
                                        <i class="ri-coins-fill"></i>
                                        Faturamento
                                    </div>
                                    <div id="vet-billing-content" class="mt-3 d-flex flex-column gap-1 text-black"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 flex-wrap justify-content-end align-items-center gap-2">
                    <a href="#" class="btn btn-agendamento-secondary" id="vet-list-link" target="_blank">
                        <i class="ri-list-check-2"></i>
                        Ver todos os atendimentos
                    </a>
                    <a href="#" class="btn btn-agendamento-secondary" id="vet-history-link" target="_blank">
                        <i class="ri-time-line"></i>
                        Histórico
                    </a>
                    <a href="#" class="btn btn-agendamento-secondary d-none" id="vet-billing-link" target="_blank">
                        <i class="ri-bill-line"></i>
                        Gerenciar faturamento
                    </a>
                    <a href="#" class="btn btn-primary" id="vet-edit-link" target="_blank">
                        <i class="ri-edit-box-line"></i>
                        Editar atendimento
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>