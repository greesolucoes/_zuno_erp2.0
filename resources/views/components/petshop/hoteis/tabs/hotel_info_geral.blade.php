<div class="row g-2 mt-3">
    <div class="row col-12">
        <div class="col-md-4">
            {!! Form::select('animal_id', 'Pet')
                ->options(['' => 'Selecione um pet'])
                ->value(old('animal_id' ?? null))
                ->attrs(['class' => 'form-select select2'])
                ->required()
            !!}
            
            <input type="hidden" name="animal_info" value="{{ $data->animal->animal_info ?? null }}"/>
            <input type="hidden" name="id_animal" value="{{ $data->animal_id ?? null }}"/>
            <input type="hidden" name="cliente_id" value="{{ $data->animal->cliente_id ?? null }}"/>
        </div>
        <div class="col-md-4">
            {!! Form::select('colaborador_id', 'Colaborador')
            ->attrs(['class' => 'select2'])
            ->options([])
            ->value($data->colaborador_id ?? null) !!}

            <input type="hidden" name="nome_colaborador" value="{{ $data->colaborador->nome ?? null }}"/>
            <input type="hidden" name="id_colaborador" value="{{ $data->colaborador_id ?? null }}"/>
        </div>
        <div class="col-md-3">
            {!! Form::select('estado', 'Situação')
                ->value($data->estado ?? 'agendado')
                ->options(App\Models\Petshop\Hotel::statusHotel())
                ->attrs([
                    'class' => 'form-select select2',
                ])
                ->disabled(!isset($data->checkout))
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
