<div class="modal fade" id="plano_petshop_info" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    {{-- Título definido pelo JS --}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 class="text-color my-3" id="periodo-abrangido-label">
                    {{-- Título definido pelo JS --}}
                </h4>
                <div 
                    class="d-flex flex-column gap-2 justify-content-center" 
                    style="width: 75%"
                    id="agendamento-plano-container"
                >
                    {{-- Conteúdo definido pelo JS --}}
                </div>
            </div>
            <div class="modal-footer">
                <div class="new-colors d-flex align-items-center justify-content-end gap-3 mt-2">
                    <button 
                        type="button" 
                        class="btn btn-success" 
                        data-bs-toggle="modal" 
                        data-bs-target="#handle_modal_agendamento"
                    >
                        Voltar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>