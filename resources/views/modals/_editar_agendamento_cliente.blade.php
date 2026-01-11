<div class="modal fade" id="editar_agendamento_cliente" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />  
                    Editar Informações do Cliente:
                    <b>
                        {{-- Conteúdo definido pelo JS --}}
                    </b>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! Form::open()->put()->id('modal-editar-cliente') !!}
                    <div class="row g-3">
                        <div class="col-md-2" data-person="fisica">
                        {!!
                            Form::text('cpf', 'CPF')
                            ->attrs([
                                'class' => 'cpf',
                                'data-iscnpj' => 'false',
                                'data-cpf_autocomplet' => auth()->user()->can('cpf_autocomplet') ? 'true' : null
                            ])
                            ->placeholder('Digite seu CPF')
                        !!}
                    </div>
                    <div class="col-md-3" data-person="juridica">
                        {!!
                            Form::text('cnpj', 'CNPJ')
                            ->attrs([
                                'class' => 'cnpj',
                                'data-iscnpj' => 'true',
                            ])
                            ->placeholder('Digite seu CNPJ')
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-4" data-person="fisica">
                        {!!
                            Form::text('nome', 'Nome')
                            ->attrs(['class' => 'text-uppercase'])
                            ->placeholder('Digite seu nome')
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-2" data-person="fisica">
                        {!!Form::date('data_nascimento', 'Nascimento')->attrs(['class' => ''])
                        ->placeholder('Digite a data de nascimento')
                    !!}
                    </div>
                    <div class="col-md-3" data-person="juridica">
                        {!!
                            Form::text('razao_social', 'Razão Social')
                            ->attrs(['class' => 'text-uppercase'])
                            ->placeholder('Digite sua razão social')
                            ->required()
                        !!}
                    </div>
                    <div class="col-md-3" data-person="juridica">
                        {!!
                            Form::text('nome_fantasia', 'Nome Fantasia')
                            ->attrs(['class' => 'text-uppercase'])
                            ->placeholder('Digite seu nome fantasia')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!
                            Form::text('ie', 'IE')
                            ->attrs(['class' => 'ie'])
                            ->placeholder('Digite seu IE')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('status', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('contribuinte', 'Contribuinte', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('consumidor_final', 'Consumidor Final', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!
                            Form::text('limite_credito', 'Limite de crédito')
                                ->attrs(['class' => 'moeda tooltipp3'])
                        !!}
                        <div class="text-tooltip3 d-none">
                            Campo utilizado para compras no crediário
                        </div>
                    </div>


                    <hr>

                    <div class="col-md-3" data-person="juridica">
                        {!!
                            Form::text('contato', 'Responsável')
                            ->attrs(['class' => 'text-uppercase'])
                            ->placeholder('Digite o nome do responsável')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!
                            Form::tel('telefone', 'Whatsapp')
                            ->attrs(['class' => 'fone'])
                            ->placeholder('Digite o Whatsapp')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!
                            Form::tel('telefone_secundario', 'Telefone fixo')
                            ->attrs(['class' => 'fone'])
                            ->placeholder('Digite o telefone fixo')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!
                            Form::tel('telefone_terciario', '3º Telefone')
                            ->attrs(['class' => 'fone'])
                            ->placeholder('Digite o 3º telefone')
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!
                            Form::text('email', 'E-mail')
                            ->type('email')
                            ->placeholder('Digite seu e-mail')
                        !!}
                    </div>
                    <hr>

                    <div class="col-md-2">
                        {!!
                            Form::text('cep', 'CEP')
                            ->attrs(['class' => 'cep'])
                            ->placeholder('Digite seu CEP')
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!
                            Form::select('cidade_id', 'Cidade')
                            ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!
                            Form::text('rua', 'Rua')
                            ->placeholder('Digite o nome da rua')
                        !!}
                    </div>
                    <div class="col-md-1">
                        {!!
                            Form::text('numero', 'Número')
                            ->attrs(['class' => 'text-uppercase'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!
                            Form::text('bairro', 'Bairro')
                            ->placeholder('Digite o nome do bairro')
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!
                            Form::text('complemento', 'Complemento')
                            ->placeholder('Digite o complemento')
                            ->attrs(['class' => 'text-uppercase'])
                        !!}
                    </div>
                    </div>
                {!! Form::close() !!}
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
                        type="submit" 
                        id="btn-save"
                        class="btn btn-primary px-5 submit-editar-cliente-btn"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>