<div class="modal fade" id="modal_editar_pet_checklist" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Editar Pet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <input type="hidden" id="inp-edit_animal_id" value="{{ isset($item->animal->id) ? $item->animal->id : '' }}">
                    <input type="hidden" id="inp-edit_cliente_id" value="{{ isset($item->animal->cliente_id) ? $item->animal->cliente_id : '' }}">
                    <input type="hidden" id="inp-edit_pelagem_id" value="{{ isset($item->animal->pelagem_id) ? $item->animal->pelagem_id : '' }}">
                    <input type="hidden" id="inp-edit_tem_pedigree" value="{{ isset($item->animal->tem_pedigree) ? ($item->animal->tem_pedigree ? 'S' : 'N') : '' }}">
                    <input type="hidden" id="inp-edit_pedigree" value="{{ isset($item->animal->pedigree) ? $item->animal->pedigree : '' }}">
                    <input type="hidden" id="inp-edit_origem" value="{{ isset($item->animal->origem) ? $item->animal->origem : '' }}">
                    <input type="hidden" id="inp-edit_observacao" value="{{ isset($item->animal->observacao) ? $item->animal->observacao : '' }}">
                    <input type="hidden" id="inp-edit_data_nascimento_pet" value="{{ isset($item->animal->data_nascimento) ? $item->animal->data_nascimento : '' }}">

                    <div class="col-md-6 col-12">
                        {!! Form::text('nome', 'Nome do pet')
                            ->value(isset($item->animal->nome) ? $item->animal->nome : '')
                            ->id('inp-edit_nome')
                            ->required() !!}
                    </div>

                    <div class="col-md-3 col-12">
                        {!! 
                            Form::select('especie_id', 'Espécie', ['' => 'Selecione a espécie'] + (
                                isset($especies) ? $especies->pluck('nome', 'id')->all() : []
                            ))
                            ->value(isset($item->animal->especie_id) ? $item->animal->especie_id : '')
                            ->attrs(['class' => 'form-select'])
                            ->id('inp-edit_especie_id')
                            ->required() 
                        !!}
                    </div>

                    <div class="col-md-3 col-12">
                        {!! 
                            Form::select('raca_id', 'Raça', ['' => 'Selecione a raça'] + (
                                isset($racas) ? $racas->pluck('nome', 'id')->all() : []
                            ))
                            ->value(isset($item->animal->raca_id) ? $item->animal->raca_id : '')
                            ->attrs(['class' => 'form-select'])
                            ->id('inp-edit_raca_id')
                            ->required() 
                        !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::text('idade', 'Idade')
                            ->value(isset($item->animal->idade) ? $item->animal->idade : '')
                            ->id('inp-edit_idade')
                            ->required() !!}
                    </div>

                    <div class="col-md-2 col-4">
                        {!!
                            Form::tel('peso', 'Peso')
                            ->value(isset($item->animal->peso) ? $item->animal->peso : '')
                            ->id('inp-edit_peso')
                        !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::select('sexo', 'Sexo', ['M' => 'Macho', 'F' => 'Fêmea'])
                            ->value(isset($item->animal->sexo) ? $item->animal->sexo : '')
                            ->attrs(['class' => 'form-select'])
                            ->id('inp-edit_sexo')
                            ->required() !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::text('porte', 'Porte')
                            ->value(isset($item->animal->porte) ? $item->animal->porte : '')
                            ->id('inp-edit_porte')
                            ->required() !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::text('chip', 'Chip')
                            ->value(isset($item->animal->chip) ? $item->animal->chip : '')
                            ->id('inp-edit_chip') !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer new-colors">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Voltar</button>
                <button type="button" class="btn btn-primary btn-update-pet">Salvar</button>
            </div>
        </div>
    </div>
</div>
