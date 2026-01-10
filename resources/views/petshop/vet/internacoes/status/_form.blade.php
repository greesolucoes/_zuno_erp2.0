@php($statusRecord = $statusRecord ?? null)

<div class="row g-3">
    <div class="col-md-6 col-12">
        {!! Form::text('status', 'Status')
            ->value(old('status', optional($statusRecord)->status))
            ->placeholder('Em observação, Estável, Pós-operatório...')
            ->required() !!}
    </div>

    <div class="col-md-6 col-12">
        {!! Form::select('evolucao', 'Evolução')
            ->options($evolutionOptions)
            ->value(old('evolucao', optional($statusRecord)->evolucao ?? 'normal'))
            ->attrs(['class' => 'form-select'])
            ->required() !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('anotacao', 'Anotação')
            ->value(old('anotacao', optional($statusRecord)->anotacao))
            ->attrs(['rows' => 4, 'style' => 'resize:none;'])
            ->placeholder('Detalhes clínicos, protocolos e orientações...') !!}
    </div>

    <div class="col-12 mt-4 d-flex align-items-center justify-content-end gap-2">
        <button type="submit" class="btn btn-success px-5">
            {{ isset($statusRecord) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>