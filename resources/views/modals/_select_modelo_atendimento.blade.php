@php
    use App\Support\Petshop\Vet\ModeloAtendimentoOptions;
@endphp

<div class="modal fade" id="modal_select_modelo_atendimento" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Modelosde Atendimento Veterinário</h5>
                    <small class="mt-2 d-block">Modelos de atendimento prontos para o seu atendimento.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-column gap-3">
                    @foreach ($templates as $key => $template)
                        <div class="card template-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title text-purple fw-bold">
                                            <i class="{{ $template['icon'] ?? ''}}"></i>
                                            {{ $template['title'] }}
                                        </h4>
                                        <p class="card-text text-black">
                                            {{ $template['notes'] }}
                                        </p>
                                        <p class="card-text text-black fw-bold">
                                            • {{ ModeloAtendimentoOptions::categoryLabel($template['category']) }}
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button 
                                            class="action-btn px-3 py-1 simulate-template-btn" 
                                            type="button"
                                            data-template-title="{{ $template['title'] }}"
                                            data-template-notes="{{ $template['notes'] }}"
                                            data-template-content='@json($template['content'])'
                                        >
                                            <i class="ri-eye-fill"></i>
                                            Ver conteúdo do modelo
                                        </button>
                                        <button 
                                            class="action-btn px-3 py-1 use-template-btn" 
                                            type="button"
                                            data-template-content='@json($template['content'])'
                                        >
                                            <i class="ri-upload-line"></i>
                                            Utilizar modelo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex align-items-center gap-3 justify-content-end mt-5">
                    <button type="button" class="btn btn-success px-3 float-end" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
