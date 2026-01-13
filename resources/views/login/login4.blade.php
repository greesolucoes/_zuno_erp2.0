<!doctype html>
<html lang="pt-BR">

    <title>{{ env('APP_NAME') }} - Login</title>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.png" type="image/x-icon" />


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
    @php
        $login = '';
        if (session('login') !== null) {
            $login = session('login');
        } elseif (isset($loginCookie) && $loginCookie) {
            $login = $loginCookie;
        } elseif (isset($_COOKIE['ckLogin'])) {
            $login = base64_decode($_COOKIE['ckLogin']);
        }
    @endphp

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

                            <form autocomplete="off" action="{{ route('login.request') }}" method="POST"
                                class="col-lg-10" id="form-login">
                                @csrf
                                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                                @if (session()->has('flash_login'))
                                    <div class="alert alert-danger mt-3">
                                        {{ session()->get('flash_login') }}
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
                                    <label for="login" class="form-label txt-blue2">Usuário</label>
                                    <input type="text" name="login" id="login"
                                        class="form-control usuario animated bounceInLeft bg-grey3 txt-black"
                                        value="{{ old('login', $login) }}" placeholder="Digite seu usuário"
                                        autocomplete="off" maxlength="100" tabindex="1" />
                                </div>

                                <div class="pass-input mt-4">
                                    <label for="senha" class="form-label txt-blue2">Senha</label>
                                    <div class="position-relative d-flex align-items-center animated bounceInRight">
                                        <button type="button"
                                            class="border-0 bg-transparent position-absolute exibir-senha z-2"
                                            data-input="senha" style="right: 1rem;">
                                            <img src="{{ asset('newV2/images/newV2/icones/eye-locked-icon.svg') }}"
                                                alt="visualizarSenha" />
                                        </button>
                                        <input type="password" name="senha" id="senha"
                                            class="form-control senha bg-grey3 txt-black" value=""
                                            placeholder="Digite sua senha" autocomplete="off" maxlength="30"
                                            tabindex="2" />
                                    </div>
                                </div>

                                <div class="d-flex text-center justify-content-end mt-3">
                                    <a href="{{ route('recuperarSenha.view') }}" class="text-decoration-none txt-blue1"
                                        tabindex="3">
                                        Esqueceu a senha?
                                    </a>
                                </div>

                                <button
                                    class="login mt-5 d-block text-decoration-none bg-blue1 txt-white-absolute fw-bold"
                                    type="submit" name="ok" value="ok" tabindex="4">
                                    Entrar
                                </button>
                            </form>

                            <div class="login-banner-mobile d-block d-lg-none position-relative col-md-9 col-12 mt-5">
                                <div id="login-banner-mobile">
                                    <div class="slide-banner">
                                        <div class="slide-card">
                                            <div class="slide-text-content col-12 text-start">
                                                <h1 class="txt-blue2 fw-bold">Gestão de Pet Shop sem complicação.</h1>
                                                <p class="mt-3 txt-blue2 fw-normal">Fique por dentro de nossos produtos
                                                </p>
                                            </div>
                                            <a class="slide-button mt-4" href="#" target="_blank">Visualizar</a>
                                        </div>
                                    </div>
                                    <div class="slide-banner">
                                        <div class="slide-card">
                                            <div class="slide-text-content col-12 text-start">
                                                <h1 class="txt-blue2 fw-bold">Banho &amp; tosa, agenda e caixa em um só
                                                    lugar.</h1>
                                            </div>
                                            <a class="slide-button mt-5" href="#" target="_blank">Visualizar</a>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="slider-arrow-mobile position-absolute d-flex justify-content-between align-items-center col-12 bottom-0 top-0">
                                    <button class="btn-prev slide-arrows">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </button>
                                    <button class="btn-next slide-arrows">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>

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

    <script src="{{ asset('newV2/js/js_views/login/login.js') }}"></script>
    <script src="{{ asset('newV2/js/newV2/templates/templateLogin.js') }}" defer></script>
    <script>
        $(document).ready(function() {
            $('#form-login button[type="submit"]').off('click').on('click', function(e) {
                e.preventDefault();
                if (!e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    $('#form-login').submit();
                    $('#form-login :submit').prop('disabled', true);
                    $('#form-login').unbind('submit');
                    $('.loadingPortalRSI, .many-loading').css('display', 'block');
                }
            });
        });
    </script>

    <script src="https://www.google.com/recaptcha/api.js?render=6Lcu73sqAAAAAEOawr3PaIA7Wxu2A14FpU7fL9TH"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6Lcu73sqAAAAAEOawr3PaIA7Wxu2A14FpU7fL9TH', {
                action: 'submit'
            }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
    </script>
</body>

</html>
