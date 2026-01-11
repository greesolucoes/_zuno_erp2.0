<div class="row mt-3">

    <div class="row">
        <div class="col-md-2">
            {!!
            Form::date('data_entrada', 'Data de Entrada')
            ->value(isset($data->data_entrada) ? $data->data_entrada->format('Y-m-d') : null)
            ->attrs([
                'id' => 'data_entrada'
            ])
            ->required()
            !!}
        </div>
        <div class="col-md-2">
            {!!
            Form::time('horario_entrada', 'Horário de Entrada')
            ->value(isset($data->data_entrada) ? $data->data_entrada->format('H:i') : null)
            ->attrs(['id' => 'horario_entrada'])
            ->required()
            !!}
        </div>
        <div class="col-md-2">
            {!!
            Form::date('data_saida', 'Data de Saída')
                ->value(isset($data->data_saida) ? $data->data_saida->format('Y-m-d') : null)
                ->attrs(['id' => 'data_saida'])
                ->required()
                ->disabled()
            !!}
        </div>
        <div class="col-md-2">
            {!!
                Form::time('horario_saida', 'Horário de Saída')
                    ->value(isset($data->data_saida) ? $data->data_saida->format('H:i') : null)
                    ->attrs(['id' => 'horario_saida'])
                    ->required()
                    ->disabled()
            !!}
        </div>
        <div class="col-md-2">
            {!! Form::select('turma_id', 'Turma')
            ->value(old('turma_id', $data->turma_id ?? null))
            ->attrs(['class' => 'form-select'])
            ->required()
            !!}
            <input type="hidden" name="nome_turma" value="{{ $data->turma->nome ?? null }}"/>
            <input type="hidden" name="id_turma" value="{{ $data->turma->id ?? null }}"/>
        </div>
    </div>

    <div class="row col-12">
        <div class="col-6">
            @include('components.petshop.reservas._tempo_execucao_hint', [
                'module' => 'creche',
                'dateInName' => 'data_entrada',
                'timeInName' => 'horario_entrada',
                'dateOutName' => 'data_saida',
                'timeOutName' => 'horario_saida',
            ])
        </div>
    </div>

    <div class="row col-12 mt-3">
        <div class="col-6">
            @include('components.petshop.creches._servico_reserva_input_line')
        </div>
    </div>

</div>
