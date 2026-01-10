<div class="row g-3">
    <div class="col-md-4 col-12">
        {!! Form::text('nome', 'Nome da sala')
            ->value(old('nome', optional($salaInternacao ?? null)->nome))
            ->placeholder('Internação 01, Isolamento, Sala Intensiva...')
            ->required() !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::text('identificador', 'Identificador')
            ->value(old('identificador', optional($salaInternacao ?? null)->identificador))
            ->placeholder('Código interno ou abreviação') !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::select('tipo', 'Tipo de internação')
            ->options($tiposSala)
            ->value(old('tipo', optional($salaInternacao ?? null)->tipo ?? array_key_first($tiposSala)))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
    </div>

    <hr>

    <div class="col-md-4 col-12">
        {!! Form::select('status', 'Status')
            ->options($statusSala)
            ->value(old('status', optional($salaInternacao ?? null)->status ?? 'disponivel'))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::text('capacidade', 'Quantidade de leitos')
            ->value(old('capacidade', optional($salaInternacao ?? null)->capacidade))
            ->placeholder('Número máximo de pacientes internados')
            ->attrs(['type' => 'number', 'min' => 1, 'max' => 999]) !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::text('equipamentos', 'Recursos e equipamentos')
            ->value(old('equipamentos', optional($salaInternacao ?? null)->equipamentos))
            ->placeholder('Jaulas aquecidas, bombas de infusão, monitores...') !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('observacoes', 'Observações')
            ->value(old('observacoes', optional($salaInternacao ?? null)->observacoes))
            ->attrs(['rows' => '4', 'style' => 'resize:none;'])
            ->placeholder('Protocolos de higiene, restrições de uso, escala de cuidados...') !!}
    </div>

    <div class="col-12 mt-4 d-flex align-items-center justify-content-end gap-2">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            {{ isset($salaInternacao) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>