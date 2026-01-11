<div class="modal fade" id="modal_raca" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Nova Raça
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        {!! Form::text('nome_raca', 'Nome da raça')->attrs([
                                'onkeydown' => "getOnEnter('.btn-store-raca')(event)",
                                'class' => 'text-uppercase',
                            ])->placeholder('Digite a raça')->required() !!}
                    </div>
                    <div class="col-md-6">
                        {!! 
                            Form::select('especie_id', 'Espécie', ['' => 'Selecione a espécie'])
                            ->attrs(['class' => 'form-select']) 
                            ->id('inp-raca_especie_id')
                            ->required()
                        !!}
                    </div>
                </div>
                
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-store-raca">Salvar</button>
            </div>
        </div>
    </div>
</div>