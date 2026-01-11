<div class="modal fade" id="modal_endereco_cliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Endereço do cliente
                <i class="ri-e-bike-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">
                <div class="row gap-2">               
                    <div class="col-md-3">
                        {!!
                            Form::text('cep', 'CEP')
                            ->attrs(['class' => 'cep'])
                            ->placeholder('Digite seu CEP')
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!
                            // Campo com prefixo no nome para evitar conflitos com outros campos com o mesmo nome 
                            Form::select('modal_cidade_id', 'Cidade')
                            ->attrs(['class' => 'ignore'])
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-6">
                        {!!
                            Form::text('rua', 'Rua')
                            ->placeholder('Digite o nome da rua')
                            ->attrs(['class' => 'ignore'])
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!
                            Form::text('numero', 'Número')
                            ->attrs(['class' => 'text-uppercase ignore'])
                            ->placeholder('N°')
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!
                            Form::text('bairro', 'Bairro')
                            ->placeholder('Digite o nome do bairro')
                            ->attrs(['class' => 'ignore'])
                            ->required()
                        !!}
                    </div>

                    <hr>

                    <div class="col-md-12">
                        {!!
                            Form::textarea('complemento', 'Complemento')
                            ->placeholder('Digite o complemento, caso haja algum')
                            ->attrs([
                                'class' => 'text-uppercase',
                                'rows' => 3
                            ])
                        !!}
                    </div>
                 </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 align-items-center justify-content-end gap-2 new-colors">
                    <button 
                        type="button" 
                        class="btn btn-success btn-cancel" 
                        data-bs-dismiss="modal"
                    >
                        Fechar
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        id="submit_endereco_cliente"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>