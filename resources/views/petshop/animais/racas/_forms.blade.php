<div class="row g-3">
  <div class="col-md-6 col-12">
      {!!Form::text('nome', 'Nome da raça')
        ->attrs(['class' => 'form-control text-uppercase'])
        ->placeholder('Digite o nome da raça aqui...')
        ->required()!!}
  </div>

  @if(isset($especies))
    <div class="col-md-6 col-12">
      {!!Form::select('especie_id', 'Espécie', ['' => 'Selecione a espécie'] + $especies->pluck('nome', 'id')->all())
      ->required()
      ->attrs(['class' => 'form-select'])
      !!}
    </div>
  @endif

  <div class="mt-4 col-12 d-flex align-items-center justify-content-center gap-2">
    <a href="{{ route('animais.racas.index') }}" class="btn btn-secondary px-5">Cancelar</a>

    <button type="submit" class="btn btn-success px-5" id="btn-store">
      @isset($item)
        Salvar alterações
      @else
        Cadastrar
      @endisset
    </button>
  </div>
</div>

@section('js')
  <script type="text/javascript">
    console.log('teste')
  </script>
@endsection
