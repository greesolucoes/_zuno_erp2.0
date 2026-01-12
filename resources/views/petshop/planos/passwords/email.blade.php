@extends('layouts.header_auth', ['title' => 'Esqueci minha senha'])

@section('css')
@endsection

@section('content')
    <div class="auth-fluid w-100 h-100 d-flex align-items-center justify-content-center">
        <div class="my-auto">
            <form class="formCard" id="form-email" method="POST" action="{{ route('petshop.planos.reset.pass') }}">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('petshop.planos.login') }}">
                        <img class="logo__sistema" src='/logo_sistema.png' width='332' height='46' />
                    </a>
                </div>

                @csrf

                <h1 class="formCard__titulo">Redefinir senha</h1>

                <p class="formCard__descricao">Informe o e-mail para redefinir uma nova senha</p>

                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <div class="mb-2">
                    <input id="email" type="email"
                        class=" custom__input form-control @error('email') is-invalid @enderror" name="email"
                        value="{{ old('email') }}" placeholder="E-mail" required autocomplete="email" autofocus>

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="d-grid mb-0 text-center">
                    <button class="custom__button" type="submit">
                        <i class="ri-send"></i>
                        Redefinir senha
                    </button>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                    <a href="{{ route('petshop.planos.login') }}" class="texto__link">
                        Voltar para o login
                    </a>
                </div>

            </form>
        </div>
        
        @include('components.rights_reserved')
    </div>

    <script src="https://www.google.com/recaptcha/api.js?render=6Lcu73sqAAAAAEOawr3PaIA7Wxu2A14FpU7fL9TH"></script>
@endsection

  @section('js')
      <script>
          grecaptcha.ready(function() {
              grecaptcha.execute('6Lcu73sqAAAAAEOawr3PaIA7Wxu2A14FpU7fL9TH', {
                  action: 'submit'
              }).then(function(token) {
                  document.getElementById('g-recaptcha-response').value = token;
              });
          });
      </script>
  @endsection