<div class="row g-3">
    <div class="col-md-2">
        {!! Form::select('quarto_id', 'Quarto')
            ->options(['' => 'Selecione um quarto'] + $quartos->pluck('nome', 'id')->all())
            ->value(isset($item) ? $item->quarto_id : $quartoId)
            ->attrs(['class' => 'select2'])
            ->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::select('servico_id', 'Serviço')
            ->options(['' => 'Selecione'] + $servicos->mapWithKeys(function($s){
                $prestador = $s->tipo_servico == 2 ? ($s->fornecedor->razao_social ?? '--') : ($s->funcionario->nome ?? '--');
                return [$s->id => $s->nome.' - '.$prestador];
            })->all())
            ->value(isset($item) ? $item->servico_id : null)
            ->attrs(['class' => 'select2'])
        !!}
    </div>

    <hr>

    <div class="col-md-2">
        {!! 
            Form::date('inicio', 'Início do evento')
            ->type('datetime-local')
            ->value(isset($item) ? $item->inicio : null)
            ->required() 
        !!}
    </div>
    <div class="col-md-2">
        {!! 
            Form::date('fim', 'Fim do evento')
            ->type('datetime-local')
            ->value(isset($item) ? $item->fim : null)
            ->required() 
        !!}
    </div>

    <hr>

    <div class="col-md-5">
        {!! 
            Form::textarea('descricao', 'Descrição')
            ->attrs(['rows' => 5, 'style' => 'resize: none;', 'class' => 'text-uppercase'])
            ->placeholder('Digite uma descrição')
            ->value(isset($item) ? $item->descricao : null)
        !!}
    </div>

    <div class="col-12 text-end mt-5">
        <button type="submit" class="btn btn-success" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
    <script type="text/javascript" src="/js/evento_quarto.js"></script>
@endsection