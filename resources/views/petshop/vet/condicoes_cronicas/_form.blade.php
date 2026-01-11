@php
    $currentCondition = $condicaoCronica ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6 col-12">
        {!! Form::text('nome', 'Nome da condição crônica')
            ->value(old('nome', $currentCondition?->nome))
            ->placeholder('Ex.: Insuficiência renal crônica, Diabetes mellitus, Cardiomiopatia...')
            ->required() !!}
    </div>

    <div class="col-md-3 col-12">
        {!! Form::select('status', 'Status')
            ->options($statusOptions)
            ->value(old('status', $currentCondition?->status ?? 'ativo'))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('descricao', 'Descrição clínica')
            ->value(old('descricao', $currentCondition?->descricao))
            ->placeholder('Descreva quadro clínico, fatores de risco, diagnósticos diferenciais...')
            ->attrs(['rows' => 4, 'style' => 'resize:none;']) !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('orientacoes', 'Planos de cuidado e monitoramento')
            ->value(old('orientacoes', $currentCondition?->orientacoes))
            ->placeholder('Informe protocolos de acompanhamento, exames recomendados e orientações aos tutores...')
            ->attrs(['rows' => 4, 'style' => 'resize:none;']) !!}
    </div>

    <div class="col-12 mt-4 d-flex align-items-center justify-content-end gap-2">
        <button type="submit" class="btn btn-primary px-5">
            {{ isset($currentCondition) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>
