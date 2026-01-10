<div class="row g-3">
  @if(isset($animais))
    <div class="col-md-4 col-12">
      @isset($item)
        {!!Form::select('animal_id', 'Pet', [$item->animal->id => $item->animal->nome] + $animais->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
      @else
        {!!Form::select('animal_id', 'Pet', ['' => 'Selecione'] + $animais->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
      @endif
    </div>
  @endif

  @if(isset($exames))
    <div class="col-md-4 col-12">
        @isset($item)
        {!!Form::select('exame_id', 'Exame', $exames->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
        @else
        {!!Form::select('exame_id', 'Exame', ['' => 'Selecione'] + $exames->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        ->required()
        !!}
        @endif
    </div>
    @endif

  @if(isset($diagnosticos))
    <div class="col-md-4 col-12">
        {!!Form::select('diagnostico_id', 'Diagnóstico', ['' => 'Selecione'] + $diagnosticos->pluck('nome', 'id')->all())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
  @endif

  <div class="col-md-4 col-12">
    {!!Form::select('status', 'Status', ['' => 'Selecione o status', 'pendente' => 'Em andamento', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'])
    ->required()
    ->attrs(['class' => 'form-select'])
    !!}
  </div>

  <div class="col-md-3 col-12">
      {!!Form::date('datahora_consulta', 'Data e hora da consulta')->type('datetime-local')->required()!!}
  </div>

  <div class="col-md-12">
    {!!Form::textarea('observacao', 'Observações')->placeholder('Digite as observações aqui...')!!}
  </div>

  <div class="mt-4 col-12 d-flex align-items-center justify-content-center gap-2">
    <a href="{{ route('animais.consultas.index') }}" class="btn btn-secondary px-5">Cancelar</a>

    <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
  </div>
</div>

@section('js')
  <script type="text/javascript">
     const urlParams = new URLSearchParams(window.location.search);
    const animalId = urlParams.get('animal_id');

      // 2. Verificar se "animal_id" existe e selecionar a opção correspondente
      if (animalId) {
          const selectElement = document.getElementById('inp-animal_id');
          const optionToSelect = selectElement.querySelector(`option[value="${animalId}"]`);
          if (optionToSelect) {
              optionToSelect.selected = true;
          }
      }
  </script>
@endsection
