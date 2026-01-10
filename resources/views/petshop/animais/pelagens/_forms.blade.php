<div class="row g-3">
  <div class="col-md-6 col-12">
      {!!Form::text('nome', 'Nome')
        ->attrs(['class' => 'form-control text-uppercase'])
        ->placeholder('Digite o nome da pelagem aqui...')
        ->required()!!}
  </div>

  <div class="mt-4 col-12 d-flex align-items-center justify-content-center gap-2">
    <a href="{{ route('animais.pelagens.index') }}" class="btn btn-secondary px-5">Cancelar</a>

    <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
  </div>
</div>

@section('js')
  <script type="text/javascript">
    console.log('teste')
  </script>
@endsection
