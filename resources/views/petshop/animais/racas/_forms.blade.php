<div class="row g-3">
  <div class="col-md-6 col-12">
      {!!Form::text('nome', 'Nome da raça')
        ->attrs(['class' => 'text-uppercase'])
        ->placeholder('Digite o nome da raça aqui...')
        ->required()!!}
  </div>

  @if(isset($especies))
    <div class="col-md-6 col-12">
      {!!Form::select('especie_id', 'Espécie', ['' => 'Selecione a espécie'] + $especies->pluck('nome', 'id')->all())
      ->required()
      ->attrs(['class' => 'form-select select2'])
      !!}
    </div>
  @endif

  <div class="col-12">
    <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
  </div>
</div>
