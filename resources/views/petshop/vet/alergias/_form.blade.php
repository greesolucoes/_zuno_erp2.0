@php
    $currentAllergy = $alergia ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6 col-12">
        {!! Form::text('nome', 'Nome da alergia')
            ->value(old('nome', $currentAllergy?->nome))
            ->placeholder('Ex.: Alergia alimentar, Dermatite atópica, Hipersensibilidade a pulgas...')
            ->required() !!}
    </div>

    <div class="col-md-3 col-12">
        {!! Form::select('status', 'Status')
            ->options($statusOptions)
            ->value(old('status', $currentAllergy?->status ?? 'ativo'))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('descricao', 'Descrição')
            ->value(old('descricao', $currentAllergy?->descricao))
            ->placeholder('Descreva os principais sintomas, gatilhos e histórico da alergia...')
            ->attrs(['rows' => 4, 'style' => 'resize:none;']) !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('orientacoes', 'Orientações e cuidados')
            ->value(old('orientacoes', $currentAllergy?->orientacoes))
            ->placeholder('Informe protocolos recomendados, instruções para tutores ou cuidados especiais...')
            ->attrs(['rows' => 4, 'style' => 'resize:none;']) !!}
    </div>

    <div class="col-12 mt-4 d-flex align-items-center justify-content-end gap-2">
        <button type="submit" class="btn btn-primary px-5">
            {{ isset($currentAllergy) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>
