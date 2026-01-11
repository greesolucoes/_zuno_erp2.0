<div class="row g-3">

 <input type="hidden" id="reservasAtivas" value="{{ $reservasAtivas ?? 0 }}">

    <div class="col-md-2">
        {!!
        Form::text('nome', 'Nome')
            ->attrs(['class' => 'text-uppercase'])
            ->value(old('nome', $quarto->nome ?? null))
            ->placeholder('Digite um nome')
            ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!
        Form::select('status', 'Situação')
            ->options([
                '' => 'Selecione',
                'disponivel' => 'Disponível',
                'em_limpeza' => 'Em Limpeza',
                'manutencao' => 'Manutenção/Organização',
                'em_uso' => 'Em uso com animal',
                'reservado' => 'Reservado para serviço',
                'bloqueado' => 'Bloqueado',
            ])
            ->value(old('status', $quarto->status ?? null))
            ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('colaborador_id', 'Colaborador')
            ->attrs(['class' => 'select2'])
            ->options(['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())
            ->value(old('colaborador_id', $quarto->colaborador_id ?? null)) !!}
    </div>

<hr>
    <div class="col-md-2">
        {!!
        Form::select('tipo', 'Porte do Animal')
            ->options([
                '' => 'Selecione',
                'pequeno' => 'Pequeno Porte',
                'grande' => 'Grande Porte',
                'individual' => 'Individual',
                'coletivo' => 'Coletivo',
            ])
            ->value(old('tipo', $quarto->tipo ?? null))
            ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!
        Form::tel('capacidade', 'Capacidade')
            ->placeholder('Digite uma quantidade')
            ->value(old('capacidade', $quarto->capacidade ?? null))
            ->required()
        !!}
    </div>

    <hr>

     <div class="col-md-4">

        {!!
        Form::textarea('descricao', 'Descrição')
            ->attrs(['class' => 'text-uppercase','rows' => '4','style' => 'resize: none'])
            ->placeholder('Digite uma descrição')
            ->value(old('descricao', $quarto->descricao ?? null))
        !!}
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/quartos.js"></script>
@endsection
