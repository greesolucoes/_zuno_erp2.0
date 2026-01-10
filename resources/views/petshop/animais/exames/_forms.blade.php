<div class="row g-3">
  <div class="col-md-6 col-12">
      {!!Form::text('nome', 'Exame')->placeholder('Digite o nome do exame aqui...')->required()!!}
  </div>

  <div class="col-md-12 col-12">
    {!!Form::textarea('descricao', 'Descrição')
    ->placeholder('Digite a descrição aqui...')
    ->attrs(['rows' => '7', 'class' => 'tiny'])
    !!}
  </div>

  <div class="mt-4 col-12 d-flex align-items-center justify-content-center gap-2">
    <a href="{{ route('animais.exames.index') }}" class="btn btn-secondary px-5">Cancelar</a>

    <button type="submit" class="btn btn-success px-5" id="btn-store">
      Salvar
    </button>
  </div>
</div>

@section('js')
  <script src="/tinymce/tinymce.min.js"></script>

  <script type="text/javascript">
      $(function(){
          tinymce.init({ selector: 'textarea.tiny', language: 'pt_BR'})

          setTimeout(() => {
              $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
          }, 500)
      })

  </script>
@endsection
