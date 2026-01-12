@extends('layouts.header_auth', ['title' => 'Login Plano Petshop'])

@section('content')
<div class="auth-fluid w-100 h-100 d-flex align-items-center justify-content-center">
    <div class="my-auto">
        <form class="formCard" method="POST" action="{{ route('petshop.planos.login.submit') }}">
            @csrf

            <div class="d-flex justify-content-center">
                <img class="logo__sistema" src="/logo_sistema.png" width="348" height="49" />
            </div>

            <h3 class="formCard__titulo">Faça o login</h3>

            <div class="mb-2">
                <input class="form-control custom__input" type="email" name="email" required placeholder="E-mail">
            </div>

            <div class="mb-2">
                <input class="form-control custom__input" type="password" name="password" required placeholder="Senha">
            </div>

            <div class="d-grid mt-2 mb-0 text-center">
                <button class="custom__button" type="submit">Acessar</button>
            </div>
            <div class="mt-3 text-center">
                <a href="{{ route('petshop.planos.password.request') }}" class="texto__link">Esqueceu sua senha?</a>
            </div>
        </form>
    </div>

    @include('components.rights_reserved')
</div>
@endsection