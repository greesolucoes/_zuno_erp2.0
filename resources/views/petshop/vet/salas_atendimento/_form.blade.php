<div class="row g-3">
    <div class="col-md-4 col-12">
        {!! Form::text('nome', 'Nome da sala')
            ->value(old('nome', optional($salaAtendimento ?? null)->nome))
            ->placeholder('Consultório 01, Sala Azul, Laboratório...')
            ->required() !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::text('identificador', 'Identificador')
            ->value(old('identificador', optional($salaAtendimento ?? null)->identificador))
            ->placeholder('Código interno ou abreviação') !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::select('tipo', 'Tipo de ambiente')
            ->options($tiposSala)
            ->value(old('tipo', optional($salaAtendimento ?? null)->tipo ?? array_key_first($tiposSala)))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
    </div>

    <hr>

    <div class="col-md-4 col-12">
        {!! Form::select('status', 'Status')
            ->options($statusSala)
            ->value(old('status', optional($salaAtendimento ?? null)->status ?? 'disponivel'))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::text('capacidade', 'Capacidade')
            ->value(old('capacidade', optional($salaAtendimento ?? null)->capacidade))
            ->placeholder('Quantidade de pacientes atendidos')
            ->attrs(['type' => 'number', 'min' => 1, 'max' => 999]) !!}
    </div>

    <div class="col-md-4 col-12">
        {!! Form::text('equipamentos', 'Equipamentos principais')
            ->value(old('equipamentos', optional($salaAtendimento ?? null)->equipamentos))
            ->placeholder('Monitor cardíaco, foco cirúrgico, balança...') !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('observacoes', 'Observações')
            ->value(old('observacoes', optional($salaAtendimento ?? null)->observacoes))
            ->attrs(['rows' => '4', 'style' => 'resize:none;'])
            ->placeholder('Instruções de preparo, agenda preferencial, protocolos específicos...') !!}
    </div>

    <div class="col-12 mt-4 d-flex align-items-center justify-content-end gap-2">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            {{ isset($salaAtendimento) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>

@section('js')
    <script type="text/javascript" src="/js/vet/salas_atendimento.js"></script>
@endsection