<div class="modal fade" id="modal_categoria_servico" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Nova categoria de serviço
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-8">
                        {!! Form::text('nome_categoria', 'Nome')->attrs([
                                'onkeydown' => "getOnEnter('.btn-store-categoria-servico')(event)",
                            ])->placeholder('Digite o nome da categoria')!!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::select('is_marketplace', 'Marketplace', ['0' => 'Não', '1' => 'Sim'])->attrs(['class' => 'form-select']) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-store-categoria-servico">Salvar</button>
            </div>
        </div>
    </div>
</div>
