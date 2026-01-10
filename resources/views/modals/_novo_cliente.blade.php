<div class="modal fade" id="modal_novo_cliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Novo Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        {!!Form::text('novo_cpf_cnpj', 'CPF/CNPJ')
                        ->attrs(
                            ['class' => 'cpf_cnpj ignore'],
                            ['data-cpf_autocomplet' => auth()->user()->can('cpf_autocomplet') ? 'true' : null])
                        ->placeholder('Digite o CPF/CNPJ')
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::text('novo_razao_social', 'Nome')->attrs(['class' => ''])
                            ->placeholder('Digite o Nome ou razão social')
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::text('data_nascimento', 'Nascimento')->attrs(['class' => 'ignore'])
                            ->placeholder('Informe a data de nascimento');
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::text('novo_nome_fantasia', 'Nome Fantasia')
                        ->placeholder('Digite o nome fantasia')
                        ->attrs(['class' => 'ignore'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::text('novo_ie', 'IE')->attrs(['class' => 'ie ignore'])
                            ->placeholder('Digite o IE')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('novo_contribuinte', 'Contribuinte', [0 => 'Não', 1 => 'Sim'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('novo_consumidor_final', 'Consumidor Final', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])->required()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('novo_status', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select'])->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::tel('novo_telefone', 'Telefone')->attrs(['class' => 'fone ignore'])
                        ->placeholder('Digite o telefone')
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::text('novo_email', 'E-mail')->attrs(['class' => 'ignore'])
                        ->placeholder('Digite o e-mail')->type('email') !!}
                    </div>
                       <div class="col-md-2">
                        {!! Form::text('novo_limite_credito', 'Limite de crédito')
                        ->attrs(['class' => 'moeda tooltipp3 ignore']) !!}
                        <div class="text-tooltip3 d-none">
                            Campo utilizado para compras no crediário
                        </div>
                    </div>
                    <hr class="mt-4">

                    <div class="col-md-3">
                        {!!Form::text('novo_cep', 'CEP')->attrs(['class' => 'cep ignore'])
                        ->placeholder('Digite o CEP')
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::select('novo_cidade_id', 'Cidade')
                        ->attrs(['class' => 'select2 ignore'])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('novo_rua', 'Rua')->attrs(['class' => 'ignore'])
                        ->placeholder('Digite a rua')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::text('novo_numero', 'Número')->attrs(['class' => 'ignore'])
                        ->placeholder('Digite o número')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('novo_bairro', 'Bairro')->attrs(['class' => 'ignore'])
                        ->placeholder('Digite o bairro')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('novo_complemento', 'Complemento')->attrs(['class' => 'ignore'])
                        ->placeholder('Digite o complemento')
                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-store-cliente">Salvar</button>
            </div>
        </div>
    </div>
</div>
