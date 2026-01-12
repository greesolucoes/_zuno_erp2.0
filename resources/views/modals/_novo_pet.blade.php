<div class="modal fade" id="modal_novo_pet" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
                 <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Novo Pet
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
                >
                </button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-3 col-12">
                        {!! 
                            Form::text('nome', 'Nome do pet')
                            ->placeholder('Digite o nome do pet')
                            ->attrs(['class' => 'text-uppercase'])
                            ->required() 
                        !!}
                    </div>

                    <div class="col-md-4 col-12">
                        {!! 
                            Form::select(
                                'cliente_id',
                                'Cliente',
                            )
                            ->attrs(['class' => 'form-select'])
                            ->id('inp-pet_cliente_id')
                            ->required() 
                        !!}
                    </div>

                    <hr>

                    
                    <div class="col-md-4 col-6 d-flex align-items-end gap-2">
                        <div class="w-100">
                            {!! 
                                Form::select('especie_id', 'Espécie', ['' => 'Selecione a espécie'])
                                ->attrs(['class' => 'form-select']) 
                                ->required()
                                !!}
                            </div>
                            <button 
                            class="btn btn-dark" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modal_especie"
                            id="btn-modal-especie"
                            data-modal-back="#modal_novo_pet"
                            type="button"
                            >
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </div>
                    
                    <div class="col-md-4 col-6 d-flex align-items-end gap-2">
                        <div class="w-100">
                            {!! 
                                Form::select('raca_id', 'Raça', ['' => 'Selecione a raça'])
                                ->attrs(['class' => 'form-select']) 
                                ->required()
                                !!}
                            </div>
                            <button 
                            class="btn btn-dark" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modal_raca"
                            id="btn-modal-raca"
                            data-modal-back="#modal_novo_pet"
                            type="button"
                            >
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </div>
                    <div class="col-md-4 col-6 d-flex align-items-end gap-2">
                        <div class="w-100">
                          {!! 
                                Form::select('pelagem_id', 'Pelagem', ['' => 'Selecione a pelagem'])
                               ->required()
                               ->attrs(['class' => 'form-select text-upptext-uppercase']) 
                            !!}
                        </div>
                        <button 
                            class="btn btn-dark" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modal_pelagem"
                            id="btn-modal-pelagem"
                            data-modal-back="#modal_novo_pet"
                            type="button"
                             >
                         <i class="ri-add-circle-fill"></i>
                        </button>
                    </div>
                    <div class="col-md-3 col-6 d-flex align-items-end gap-2">
                                <div class="w-100">
                                   {!! Form::text('cor', 'Cor')
                                        ->value(isset($item) ? $item->cor : '')
                                        ->attrs(['class' => 'form-control text-uppercase'])
                                        ->placeholder('Digite a Cor')
                                    !!}
                                </div>
                            
                            </div>
                    <hr>

                    <div class="col-md-3 col-6">
                        {!! Form::select('sexo', 'Sexo', ['' => 'Selecione', 'M' => 'Macho', 'F' => 'Fêmea', 'I' => 'Indefinido'])->required()->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::text('peso', 'Peso')->placeholder('Digite o peso') !!}
                    </div>

                    <div class="col-md-3">
                            {!!
                            Form::text('porte', 'Porte')
                            ->placeholder('Digite o porte')
                            ->required()
                            ->attrs(['class' => 'text-uppercase'])
                            !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::text('origem', 'Origem')->placeholder('Digite a origem') !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::date('data_nascimento', 'Data de nascimento')->id('inp-pet_data_nascimento') !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::text('chip', 'Chip')->placeholder('Digite o chip') !!}
                    </div>

                    <div class="col-md-3 col-6">
                        {!! Form::select('tem_pedigree', 'Possui pedigree?', [ 'N' => 'Não', 'S' => 'Sim'])->required()->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::text('pedigree', 'Número do pedigree')->placeholder('Digite o número do pedigree') !!}
                    </div>

                    <hr>

                    <div class="col-md-6 col-8">
                        {!! 
                            Form::textarea('observacao', 'Observações')
                            ->placeholder('Digite as observações') 
                            ->attrs([
                                'rows' => 5,
                                'style' => 'resize: none;'
                            ])
                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-store-pet">Salvar</button>
            </div>
        </div>
    </div>
</div>

@include('modals._pelagem')
@include('modals._raca')
@include('modals._especie')