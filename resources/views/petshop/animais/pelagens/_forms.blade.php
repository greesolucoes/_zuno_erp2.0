<div class="row g-3">
  <div class="col-md-6 col-12">
      {!!Form::text('nome', 'Nome')
        ->attrs(['class' => 'text-uppercase'])
        ->placeholder('Digite o nome da pelagem aqui...')
        ->required()!!}
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
  </div>
</div>
