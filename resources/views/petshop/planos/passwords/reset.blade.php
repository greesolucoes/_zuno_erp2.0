@extends('layouts.header_auth', ['title' => 'Redefinição de senha'])

@section('css')

    <style>
        .auth-fluid {
            background: url('/assets/images/background_redefinir.jpg');
            background-size: cover !important;
        }

    </style>
@endsection


@section('content')
    <div class="auth-fluid w-100 h-100 d-flex align-items-center justify-content-center">
        <div class="my-auto">
            <form class="formCard" id="form-email">

                <div class="d-flex justify-content-center">
                    <img class="logo__sistema" src='/logo_sistema.png' width='320' height='64' />
                </div>
                <p class="mt-4 formCard__titulo">REDEFINA SUA SENHA</p>
                <span id="senha-error" style="display: none; color: white"></span>
                @csrf

                <input type="hidden" id="token" name="token" value="{{ $token }}">
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <input id="email" type="hidden" class="custom__input form-control" name="email"
                    value="{{ request('email') }}" required>

                <!-- Campo de Senha -->
                <div class="input-group mb-2" id="show_hide_password">
                    <input id="senha" type="password" class="custom__input form-control" name="senha"
                        placeholder="Senha" required>
                    <a class="input-group-text password__icon" onclick="input1()" title='Mostrar/Ocultar a senha'><i
                            class='ri-eye-line'></i></a>
                </div>

                <!-- Campo de Confirmação de Senha -->
                <div class="input-group mb-2" id="show_hide_password_2">
                    <input id="senha_confirmation" type="password" class="custom__input form-control"
                        name="senha_confirmation" placeholder="Confirme a senha" required>
                    <a class="input-group-text password__icon" onclick="input2()" title='Mostrar/Ocultar a senha'><i
                            class='ri-eye-line'></i></a>
                </div>
                <span id="senha-confirmation-error" style="display: none; color: #ffff"></span>

                <div class="d-grid mb-0 text-center">
                    <button class="custom__button" type="button" onclick="submitButton(event)">
                        <i class="ri-send"></i>
                        Redefinir senha
                    </button>
                </div>
            </form>

          </div>

        @include('components.rights_reserved')
      </div>
  @endsection
@section('js')
    <script>
        let input2 = () => {
            $("#show_hide_password_2 a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_password_2 input').attr("type") == "text") {
                    $('#show_hide_password_2 input').attr('type', 'password');
                    $('#show_hide_password_2 i').addClass("ri-eye-line");
                    $('#show_hide_password_2 i').removeClass("ri-eye-close-fill");
                } else if ($('#show_hide_password_2 input').attr("type") == "password") {
                    $('#show_hide_password_2 input').attr('type', 'text');
                    $('#show_hide_password_2 i').removeClass("ri-eye-line");
                    $('#show_hide_password_2 i').addClass("ri-eye-close-fill");
                }
            });
        }
        let input1 = () => {
            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_password input').attr("type") == "text") {
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass("ri-eye-line");
                    $('#show_hide_password i').removeClass("ri-eye-close-fill");
                } else if ($('#show_hide_password input').attr("type") == "password") {
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').removeClass("ri-eye-line");
                    $('#show_hide_password i').addClass("ri-eye-close-fill");
                }
            });
        }
    </script>

    <script>
        let submitButton = (event) => {
            event.preventDefault();
            let prot = window.location.protocol;
            let host = window.location.host;
            const path_url = prot + "//" + host + "/";
            const email = $('#email').val();
            const senha = $('#senha').val();
            const senhaConfirmation = $('#senha_confirmation').val();
            const senhaError = document.getElementById('senha-error');
            senhaError.style.display = 'none';
            const senhaConfirmationError = document.getElementById('senha-confirmation-error');
            const senhaRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z\d])[A-Za-z\d\S]{8,}$/;
            let isValid = true;
            if (!senhaRegex.test(senha)) {
                senhaError.style.display = 'block';
                senhaError.textContent =
                    'A senha deve conter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula, um número e um caractere especial.';
                isValid = false;
            } else {
                senhaError.style.display = 'none';
            }

            if (senha !== senhaConfirmation) {
                senhaConfirmationError.style.display = 'block';
                senhaConfirmationError.textContent = 'As senhas não correspondem.';
                isValid = false;
            } else {
                senhaConfirmationError.style.display = 'none';
            }

            if (isValid) {
                $.post(`${path_url}api/petshop/planos/password/${$('#token').val()}`, {
                        email: email,
                        senha: senha
                    })
                      .done((success) => {
                          console.log(success);
                          Swal.fire('Sucesso!', 'Sua senha foi redefinida com sucesso.', 'success');
                          setTimeout(() => {
                              window.location.href = `${path_url}petshop/planos/login`;
                          }, 3000);
                      })
                      .fail((error) => {
                          console.log(error);
                          Swal.fire('Erro!', 'Ocorreu um erro ao redefinir a senha. Tente novamente', 'error');
                          setTimeout(() => {
                              window.location.href = `${path_url}petshop/planos/login`;
                          }, 2000);
                      });
            }
        };
    </script>
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