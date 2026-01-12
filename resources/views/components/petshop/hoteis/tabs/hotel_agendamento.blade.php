<div class="row mt-3">
    <div class="row">
        <div class="col-md-2">
            {!!
            Form::date('checkin', 'Check in')
            ->value(isset($data->checkin) ? $data->checkin->format('Y-m-d') : null)
            ->attrs([
                'id' => 'checkin'
            ])
            ->required()
            !!}
        </div>
        <div class="col-md-2">
            {!!
            Form::time('timecheckin', 'Horário de Check in')
            ->value(isset($data->checkin) ? $data->checkin->format('H:i') : null)
            ->attrs(['id' => 'timecheckin'])
            ->required()
            !!}
        </div>
        <div class="col-md-2">
            {!!
            Form::date('checkout', 'Check out')
                ->value(isset($data->checkout) ? $data->checkout->format('Y-m-d') : null)
                ->attrs(['id' => 'checkout'])
                ->required()
                ->disabled()
            !!}
        </div>
        
        <div class="col-md-2">
            {!!
                Form::time('timecheckout', 'Horário de Check out')
                    ->value(isset($data->checkout) ? $data->checkout->format('H:i') : null)
                    ->attrs(['id' => 'timecheckout'])
                    ->required()
                    ->disabled()
            !!}
        </div>
        
        <div class="col-md-2">
            {!! Form::select('quarto_id', 'Quarto')
                ->options([])
                ->value(old('quarto_id', $data->quarto_id ?? null))
                ->attrs(['class' => 'form-select'])
                ->required()
            !!}
            <input type="hidden" name="nome_quarto" value="{{ $data->quarto->nome ?? null }}"/>
            <input type="hidden" name="id_quarto" value="{{ $data->quarto->id ?? null }}"/>
        </div>
    </div>

    <div class="row col-12">
        <div class="col-6">
            @include('components.petshop.reservas._tempo_execucao_hint', [
                'module' => 'hotel',
                'dateInName' => 'checkin',
                'timeInName' => 'timecheckin',
                'dateOutName' => 'checkout',
                'timeOutName' => 'timecheckout',
            ])
        </div>
    </div>

    <div class="row col-12 mt-3">
        <div class="col-6">
            @include('components.petshop.hoteis._servico_reserva_input_line')
        </div>
    </div>
</div>
