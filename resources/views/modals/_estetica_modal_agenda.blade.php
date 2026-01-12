<div class="modal fade modal-config" id="selectHorarioModalAgenda" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md" style="height: max-content;">
        <div class="modal-content border-0 rounded-3">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center" id="selectHorarioModalAgendaTitle">
                    <img src="/logo_simples_branco_laranja.svg" alt="Logo Diprosoft" width="40" height="28" />
                    <span class="ms-2">Selecione data e hora</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <button id="prev-week" type="button" class="btn btn-light btn-sm rounded-2" style="width:32px;height:32px;">&lt;</button>
                    <p id="current-month" class="text-center flex-grow-1 mb-0"></p>
                    <button id="next-week" type="button" class="btn btn-light btn-sm rounded-2" style="width:32px;height:32px;">&gt;</button>
                </div>
                <div class="d-flex flex-wrap justify-content-center mb-4">
                    <div class="text-center">
                        <div id="week-days" class="d-flex flex-wrap justify-content-center mb-2"></div>
                        <div id="week-dates" class="d-flex flex-wrap justify-content-center"></div>
                    </div>
                </div>
                <hr>
                <p id="selected-day-text" class="text-center mb-4 d-none"></p>
                <div id="available-times" class="d-flex flex-wrap justify-content-center"></div>
            </div>
            <div class="modal-footer new-colors">
                <button id="confirm-horario-btn" type="button" class="btn btn-primary w-100 d-none" data-bs-dismiss="modal">Confirmar</button>
            </div>
        </div>
    </div>
</div>