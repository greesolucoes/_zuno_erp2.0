<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.png" type="image/x-icon" />

    <title>{{ env('APP_NAME') }} - Recuperar senha</title>

    <link href="{{ asset('newV2/css/newV2/libs/bootstrap_5.3/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/newV2/libs/slick_1.9.0/slick.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/newV2/libs/slick_1.9.0/slick-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/newV2/libs/fontawesome_6.5.2/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/newV2/style.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/newV2/templates/default.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/newV2/templates/views.css') }}" rel="stylesheet">
    <link href="{{ asset('newV2/css/newV2/login/login.css') }}" rel="stylesheet">
</head>

<body>
    <main>
        <section class="login position-relative">
            <div class="container-fluid g-0 d-none d-lg-block">
                <div class="banner-slider m-0">
                    <figure class="slide-item m-0 vh-100" data-slide="1">
                        <img class="banner-notebook" src="{{ asset('newV2/images/banner1.jpeg') }}" alt="Banner 1">
                        <img class="banner-desktop" src="{{ asset('newV2/images/banner1.jpeg') }}" alt="Banner 1">
                        <img class="banner-2k" src="{{ asset('newV2/images/banner1.jpeg') }}" alt="Banner 1">
                        <img class="banner-4k" src="{{ asset('newV2/images/banner1.jpeg') }}" alt="Banner 1">
                    </figure>

                    <figure class="slide-item m-0 vh-100" data-slide="2">
                        <img class="banner-notebook" src="{{ asset('newV2/images/banner2.jpeg') }}" alt="Banner 2">
                        <img class="banner-desktop" src="{{ asset('newV2/images/banner2.jpeg') }}" alt="Banner 2">
                        <img class="banner-2k" src="{{ asset('newV2/images/banner2.jpeg') }}" alt="Banner 2">
                        <img class="banner-4k" src="{{ asset('newV2/images/banner2.jpeg') }}" alt="Banner 2">
                    </figure>
                </div>
            </div>

            <div class="container-fluid position-absolute top-0">
                <div class="row">
                    <div
                        class="login-content col-lg-6 col-12 d-flex justify-content-center align-items-lg-center align-items-start px-5 px-lg-0 py-5 py-lg-0 vh-100">
                        <div
                            class="login-form col-xl-6 col-xxl-5 col-lg-10 col-md-8 col-12 bg-white p-5 d-flex flex-column align-items-center justify-content-center">
                            <div class="mt-3 pt-3 pt-lg-5 mt-lg-5"></div>

                            <form autocomplete="off" action="{{ route('recuperarSenha') }}" method="POST"
                                class="col-lg-10" id="form-recuperar-senha">
                                @csrf

                                @if (session()->has('flash_login'))
                                    <div class="alert alert-danger mt-3">
                                        {{ session()->get('flash_login') }}
                                    </div>
                                @endif

                                @if (session()->has('flash_sucesso'))
                                    <div class="alert alert-success mt-3">
                                        {{ session()->get('flash_sucesso') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger mt-3">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="user-input mt-5">
                                    <label for="email-recuperar" class="form-label txt-blue2">E-mail</label>
                                    <input class="form-control bg-grey3 txt-black" type="email" name="email"
                                        id="email-recuperar" placeholder="E-mail cadastrado" autocomplete="off"
                                        required tabindex="1">
                                </div>

                                <button
                                    class="login mt-5 d-block text-decoration-none bg-blue1 txt-white-absolute fw-bold"
                                    type="submit" tabindex="2">
                                    Solicitar nova senha
                                </button>

                                <div class="d-flex text-center justify-content-end mt-3">
                                    <a href="/login" class="text-decoration-none txt-blue1" tabindex="3">
                                        Voltar para o login
                                    </a>
                                </div>
                            </form>

                            <div class="help-text d-flex mt-5">
                                <a class="text-center text-decoration-none" href="#" target="_blank">
                                    <span class="fw-bold">Precisa de ajuda?</span><br>
                                    <span class="fw-bold">Clique aqui</span> e entre em contato<br>
                                    com nossa equipe
                                </a>
                            </div>

                            <div class="mb-3 pb-3 pb-lg-5 mb-lg-5"></div>
                        </div>
                    </div>

                    <div
                        class="slide-content col-lg-6 col-12 flex-column align-items-center justify-content-end position-relative vh-100 d-none d-lg-flex">
                        <div
                            class="banner-content position-relative d-flex flex-column align-items-center justify-content-end col-12 mb-5">
                            <div class="slide-text text-center col-12 ativo" data-slide="1">
                                <h1 class="txt-white-absolute">Gestão de Pet Shop sem complicação.</h1>
                                <p class="mt-4 txt-white-absolute">Agendamentos, clientes e vendas em um só lugar</p>
                                <div class="col-lg-8 offset-lg-2 col-xl-6 offset-xl-3 col-12">
                                    <a class="mt-5 d-block text-decoration-none bg-blue1 txt-white-absolute" href="#"
                                        target="_blank">
                                        Visualizar
                                    </a>
                                </div>
                            </div>

                            <div class="slide-text text-center col-12" data-slide="2">
                                <h1 class="txt-white-absolute">
                                    Para o seu <span class="txt-orange-absolute text-uppercase">Pet Shop</span>
                                </h1>
                                <p class="mt-4 txt-white-absolute">Banho &amp; tosa, estoque e financeiro organizados</p>
                                <div class="col-lg-8 offset-lg-2 col-xl-6 offset-xl-3 col-12">
                                    <a class="mt-5 d-block text-decoration-none bg-blue1 txt-white-absolute" href="#"
                                        target="_blank">
                                        Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="slider-nav bg-white-absolute"></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('newV2/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('newV2/js/newV2/libs/bootstrap_5.3/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('newV2/js/newV2/libs/slick_1.9.0/slick.min.js') }}"></script>

    <script src="{{ asset('newV2/js/newV2/templates/templateLogin.js') }}" defer></script>
</body>

</html>
