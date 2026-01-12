<div class="row g-2 mt-3">
    <div class="row">
        <div class="col-md-4">
            {!!
                Form::select('animal_id', 'Pet')
                ->value(old('animal_id', $data->animal_info ?? null))
                ->attrs(['class' => 'form-select select2'])
                ->required()
            !!}
            <input type="hidden" name="animal_info" value="{{ $data->animal->animal_info ?? null }}"/>
            <input type="hidden" name="id_animal" value="{{ $data->animal_id ?? null }}"/>
            <input type="hidden" name="cliente_id" value="{{ $data->animal->cliente_id ?? null }}"/>
        </div>
        <div class="col-md-4">
            {!!
                Form::select('colaborador_id', 'Colaborador')
                ->attrs(['class' => 'select2'])
                ->value($data->colaborador_id ?? null)
            !!}
            <input type="hidden" name="nome_colaborador" value="{{ $data->colaborador->nome ?? null }}"/>
            <input type="hidden" name="id_colaborador" value="{{ $data->colaborador_id ?? null }}"/>
        </div>
        <div class="col-md-3">
            {!! Form::select('estado', 'Situação')
                ->value($data->estado ?? 'agendado')
                ->options(App\Models\Petshop\Creche::statusCreche())
                ->attrs([
                    'class' => 'form-select select2',
                ])
                ->disabled(!isset($data->data_saida))
            !!}
        </div>
    </div>

    <div class="col-md-4">
        {!!
        Form::textarea('descricao', 'Descrição')
        ->attrs(['class' => 'text-uppercase','rows' => '4','style' => 'resize: none'])
        ->placeholder('Digite uma descrição')
        !!}
    </div>
</div>
