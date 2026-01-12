<div class="modal fade" id="editar_agendamento_pet" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />  
                    Editar Informações do Pet:
                    <b>
                        {{-- Conteúdo definido pelo JS --}}
                    </b>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! Form::open()->put()->id('modal-editar-pet') !!}
                    <div class="row g-3">
                        <div class="col-md-4 col-12 d-flex align-items-end gap-2 ">
                            <div class="w-100">
                                {!!
                                    Form::text('nome', 'Nome do pet')
                                    ->placeholder('Digite o nome do pet')
                                    ->attrs(['class' => 'form-control text-uppercase'])
                                    ->required()
                                !!}
                            </div>
                        </div>

                        <div class="col-md-4">
                            {!!
                                Form::text('cliente', 'Cliente',)
                                ->disabled()
                            !!}
                        </div>

                        <hr>
                        
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <div class="w-100">
                                {!!
                                    Form::select('agendamento_especie_id', 'Espécie', ['' => 'Selecione a espécie'])
                                    ->required()
                                    ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                            <button class="btn btn-dark" id="btn-agendamento-nova-especie" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <div class="w-100">
                                {!!
                                    Form::select('agendamento_raca_id', 'Raça', ['' => 'Selecione a raça'])
                                    ->required()
                                    ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                            <button 
                                class="btn btn-dark"type="button" id="btn-agendamento-nova-raca"
                            >
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <div class="w-100">
                                {!!
                                    Form::select('agendamento_pelagem_id', 'Pelagem', ['' => 'Selecione a pelagem'])
                                    ->attrs(['class' => 'form-select'])
                                !!}
                            </div>
                            <button class="btn btn-dark" id="btn-agendamento-nova-pelagem" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <div class="w-100">
                                {!! 
                                    Form::text('cor', 'Cor')
                                    ->attrs(['class' => 'form-control text-uppercase'])
                                    ->placeholder('Digite a Cor')
                                !!}
                            </div>
                        </div>

                        <hr>
                        
                        <div class="col-md-2 col-6">
                            {!!
                                Form::select('sexo', 'Sexo', ['' => 'Selecione', 'M' => 'Macho', 'F' => 'Fêmea', 'I' => 'Indefinido'])
                                ->required()
                                ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2 col-6">
                            {!!
                                Form::text('peso', 'Peso')
                                ->placeholder('Digite o peso')
                            !!}
                        </div>

                        <div class="col-md-2 col-6">
                            {!!
                                Form::text('porte', 'Porte')
                                ->placeholder('Digite o porte')
                                ->required()
                                ->attrs(['class' => 'text-uppercase'])
                            !!}
                        </div>

                        <div class="col-md-2 col-6">
                            {!!
                                Form::text('origem', 'Origem')
                                ->attrs(['class' => 'text-uppercase'])
                                ->placeholder('Digite a origem')
                            !!}
                        </div>

                        <hr>

                        <div class="col-md-2 col-6">
                            {!!
                                Form::date('data_nascimento', 'Data de nascimento')
                            !!}
                        </div>

                        <div class="col-md-2 col-6">
                            {!!
                                Form::text('chip', 'Chip')
                                ->placeholder('Digite chip')
                            !!}
                        </div>

                        <div class="col-md-2 col-6">
                            {!!
                                Form::select('tem_pedigree', 'Possui pedigree?', ['' => 'Selecione', 'S' => 'Sim', 'N' => 'Não'])
                                ->required()
                                ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2 col-6">
                            {!!
                                Form::text('pedigree', 'Número do pedigree')->placeholder('Digite o número do pedigree')
                            !!}
                        </div>

                        <hr>

                        <div class="col-md-6">
                            {!!
                                Form::textarea('observacao', 'Observações')
                                ->attrs(['rows' => '6', 'style' => 'resize:none;'])
                                ->placeholder('Digite as observações')
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
                        class="btn btn-primary px-5 submit-editar-pet-btn"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>