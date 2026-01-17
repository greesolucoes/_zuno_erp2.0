<div class="modal fade" id="modal_termo_garantia_os" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="margin: 0px auto !important">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Termo de garantia da Ordem de Serviço
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {!! Form::open()->post()->route('ordemServico.termoGarantia.update') !!}
            {!! Form::hidden('empresa_id', request()->empresa_id) !!}
                <div class="modal-body">
                    {!! 
                        Form::textarea('termo_garantia_os', '')
                        ->attrs([
                            'rows' => 25,
                            'style' => 'resize: none;',
                            'class' => 'tiny'
                        ])
                        ->id('inp-termo_garantia_os')
                        ->value(($empresa?->termo_garantia_os ?? null))
                        ->placeholder('Digite o termo de garantia que irá aparecer na Ordem de Serviço')
                    !!}
                </div>

                <div class="modal-footer new-colors">
                    <button type="button" class="btn btn-destroy" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
