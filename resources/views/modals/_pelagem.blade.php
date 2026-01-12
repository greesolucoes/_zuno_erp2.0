<div class="modal fade" id="modal_pelagem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Nova Pelagem
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-12">
                        {!! Form::text('nome_pelagem', 'Nome da pelagem')->attrs([
                                'onkeydown' => "getOnEnter('.btn-store-pelagem')(event)",
                                'class' => 'text-uppercase',
                            ])->placeholder('Digite a pelagem')->required() !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-store-pelagem">Salvar</button>
            </div>
        </div>
    </div>
</div>