<div class="row g-3">
    <div class="col-md-3">
        {!! Form::select('turma_id', 'Turma')
        ->options(['' => 'Selecione'] + $turmas->pluck('nome', 'id')->all())
        ->value(isset($item->turma_id) ? $item->turma_id : $turmaId)
        ->attrs(['class' => 'select2'])
        ->required() !!}
    </div>

    <div class="col-md-6">
        {!! Form::select('servico_id', 'Serviço')
        ->options(['' => 'Selecione'] + $servicos->mapWithKeys(function($s){
            $prestador = $s->tipo_servico == 2 ? ($s->fornecedor->razao_social ?? '--') : ($s->funcionario->nome ?? '--');
            return [$s->id => $s->nome.' - '.$prestador];
        })->all())
        ->value(old('servico_id', $item->servico_id ?? null))
        ->attrs(['class' => 'select2']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::date('inicio', 'Início')
        ->type('datetime-local')
        ->value(isset($item->inicio) ? $item->inicio : null)
        ->required() !!}
    </div>

    <div class="col-md-3">
        {!! Form::date('fim', 'Fim')
        ->type('datetime-local')
        ->value(isset($item->fim) ? $item->fim : null)
        ->required() !!}
    </div>

    <div class="col-md-6">
        {!! Form::textarea('descricao', 'Descrição')
        ->attrs(['rows' => 4, 'style' => 'resize: none;', 'class' => 'text-uppercase'])
        ->placeholder('Digite uma descrição')
        ->value(isset($item) ? $item->descricao : null) !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>
