<div class="modal fade" id="modal_servico" tabindex="-1" aria-labelledby="modalServicoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title d-flex align-items-center" id="modalServicoLabel">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Cadastrar Serviço
                </h5>
                <button 
                    type="button" 
                    class="btn-close btn-close-white" 
                    @if (isset($back_modal)) 
                        data-bs-toggle="modal" 
                        data-bs-target="{{ $back_modal }}"
                    @else 
                        data-bs-dismiss="modal" 
                    @endif
                ></button>
            </div>

            <div class="modal-body">
                {!! Form::open()->id('form-modal-servico') !!}

                <ul class="nav nav-tabs nav-primary mt-5" role="tablist">
                    <li class="nav-item">
                        <a class="px-3 nav-link active" data-bs-toggle="tab" href="#novo_servico_info" role="tab"
                            aria-selected="true">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="tab-title">
                                    <i class="ri-file-text-line"></i>
                                    Informações Gerais
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="px-3 nav-link" data-bs-toggle="tab" href="#servico" role="tab">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="tab-title">
                                    <i class="ri-tools-fill"></i>
                                    Serviço
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="px-3 nav-link" data-bs-toggle="tab" href="#tempo" role="tab">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="tab-title">
                                    <i class="ri-timer-fill"></i>
                                    Tempo de execução
                                </div>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="px-3 nav-link" data-bs-toggle="tab" href="#tributacao" role="tab">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="tab-title">
                                    <i class="ri-percent-line"></i>
                                    Tributação
                                </div>
                            </div>
                        </a>
                    </li>
                </ul>

                <div class="tab-content pt-5 p-3">
                    <div class="tab-pane fade show active" id="novo_servico_info" role="tabpanel" data-label="Informações Gerais">
                        @include('servicos.tabs._tab_info_geral')
                    </div>
                    <div class="tab-pane fade" id="servico" role="tabpanel" data-label="Serviço">
                        @include('servicos.tabs._tab_servico')
                    </div>
                    <div class="tab-pane fade" id="tempo" role="tabpanel" data-label="Tempo de execução">
                        @include('servicos.tabs._tab_tempo')
                    </div>
                    <div class="tab-pane fade" id="tributacao" role="tabpanel" data-label="Tributação">
                        @include('servicos.tabs._tab_tributacao')
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" id="btn-store-modal-servico" class="btn btn-success px-5">Salvar</button>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@include('modals._categoria_servico')
