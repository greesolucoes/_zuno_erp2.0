<div class="row mt-3">
    <div class="col-md-4">
        {!! Form::select('animal_id', 'Pet')
        ->options([])
        ->value($data->animal_id ?? null)
        ->attrs(['class' => 'form-select select2'])
        ->required()
        !!}

        <input type="hidden" name="animal_info" value="{{ $data->animal->animal_info ?? null }}"/>
        <input type="hidden" name="id_animal" value="{{ $data->animal_id ?? null }}"/>
        <input type="hidden" name="cliente_id" value="{{ $data->animal->cliente_id ?? null }}"/>
    </div>

     <div class="col-md-4">
        {!! Form::select('colaborador_id', 'Colaborador')
        ->id('colaborador_id')
        ->attrs(['class' => 'select2'])
        ->options([])
        ->value($data->colaborador_id ?? null) !!}

        <input type="hidden" name="nome_colaborador" value="{{ $data->colaborador->nome ?? null }}"/>
        <input type="hidden" name="id_colaborador" value="{{ $data->colaborador_id ?? null }}"/>
    </div>


    <div class="col-md-3">
        {!! Form::select('estado', 'Situação')
            ->options(App\Models\Petshop\Estetica::statusEstetica())
            ->value(
                isset($data->estado) ? $data->estado : 'agendado'
            )
            ->attrs(['class' => 'form-select select2'])
            ->disabled(!isset($data->estado) ? true : false)
            ->required()
        !!}
    </div>

    <hr class="mt-3">

    <div class="col-md-4">
        {!!
            Form::textarea('descricao', 'Descrição')
            ->id('descricao_estetica')
            ->attrs([
                'class' => 'text-uppercase',
                'style' => 'resize: none',
                'rows' => 5
            ])
            ->placeholder('Digite uma descrição')
        !!}
    </div>
</div>

