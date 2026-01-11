<div class="row g-3">
    <input type="hidden" id="reservasAtivas" value="{{ $reservasAtivas ?? 0 }}">

    <div class="col-md-3">
        {!! Form::text('nome', 'Nome')
        ->attrs(['class' => 'text-uppercase'])
        ->placeholder('Digite um nome')
        ->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('status', 'Situação', ['' => 'Selecione'] + \App\Models\Petshop\Quarto::statusList())
        ->attrs(['class' => 'select2'])
        ->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('colaborador_id', 'Colaborador', ['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())
        ->attrs(['class' => 'select2']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('tipo', 'Porte dos pets', [
            '' => 'Selecione',
            'pequeno' => 'Pequeno porte',
            'grande' => 'Grande porte',
            'individual' => 'Individual',
            'coletivo' => 'Coletivo',
        ])->attrs(['class' => 'select2'])->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::tel('capacidade', 'Capacidade')
        ->placeholder('Digite uma quantidade')
        ->required() !!}
    </div>

    <div class="col-md-6">
        {!! Form::textarea('descricao', 'Descrição')
        ->attrs(['class' => 'text-uppercase', 'rows' => '4', 'style' => 'resize: none'])
        ->placeholder('Digite uma descrição') !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/quartos.js"></script>
@endsection
