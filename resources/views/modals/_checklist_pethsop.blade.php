
<div class="modal fade" id="checklist_petshop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    {{-- Título definido pelo JS --}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Dados do Pet --}}
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="text-color">Informações do Pet</h3>
                        <button id="edit-pet-checklist-btn" type="button" class="btn btn-sm btn-warning">
                            <i class="ri-edit-line"></i> Editar
                        </button>
                    </div>
                    <div class="row new-colors fs-5 mb-3" style="line-height: 2;">
                        <div class="col-12 col-md-6">
                            <li>
                                <strong class="text-purple">Nome do pet: </strong>
                                <span class="text-orange" id="pet_nome">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                            <li>
                                <strong class="text-purple">Espécie: </strong>
                                <span class="text-orange" id="pet_especie">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                            <li>
                                <strong class="text-purple">Raça: </strong>
                                <span class="text-orange" id="pet_raca">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                            <li>
                                <strong class="text-purple">Peso: </strong>
                                <span class="text-orange" id="pet_peso">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                        </div>
                        <div class="col-12 col-md-6">
                            <li>
                                <strong class="text-purple">Idade: </strong>
                                <span class="text-orange" id="animal_idade">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                            <li>
                                <strong class="text-purple">Sexo: </strong>
                                <span class="text-orange" id="pet_sexo">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                            <li>
                                <strong class="text-purple">Porte: </strong>
                                <span class="text-orange" id="pet_porte">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                            <li>
                                <strong class="text-purple">Chip de identificação: </strong>
                                <span class="text-orange" id="pet_chip">
                                    {{-- Valor adicionado pelo JS --}}
                                </span>
                            </li>
                        </div>
                    </div>
                </div>

                <h3 class="mb-4 text-color">
                    {{-- Valor adicionado pelo JS --}}
                </h3>

                <input 
                    type="hidden" 
                    name="tipo" 
                    value="" {{-- Valor adicionado pelo JS --}}
                >

                <textarea class="form-control tiny" name="texto_checklist" id="texto_checklist">
                    {{-- Valor adicionado pelo JS --}}
                </textarea>
                <hr class="my-4">
                @include('components.petshop_checklist.modal._images_checklist', ['anexos' => []])
            </div>
            <div class="modal-footer">
                <div class="new-colors d-flex align-items-center justify-content-end gap-3 mt-5">
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
                        class="btn btn-primary gap-1 d-none"
                        id="go-edit-btn"
                    >
                        <i class="ri-edit-box-line"></i>
                        Ir para edição
                    </button>
                    <button
                        target="_blank"
                        class="btn btn-primary gap-1 submit-checklist-btn"
                        id="btn-print"
                        data-print="true"
                    >
                        <i class="ri-printer-line"></i>
                        Baixar ou Imprimir
                    </button>
                    <button 
                        type="submit" 
                        id="btn-save"
                        class="btn btn-success px-5 submit-checklist-btn"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


