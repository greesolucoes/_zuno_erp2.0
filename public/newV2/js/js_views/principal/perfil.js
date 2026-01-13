const translations = window.l || {};

function tr(key, fallback) {
    return translations[key] || fallback || key;
}

function initSelectColaboradores() {
    const select = $('.select_colaboradores');
    if (!select.length) {
        return;
    }

    select.select2({
        placeholder: tr('colaboradores', 'Colaboradores'),
        language: 'pt-BR',
        allowClear: true
    });
}

$('#btn-alterar_senha').off('click');
$('#btn-alterar_senha').on('click', function () {
    $('#senhaAtual').val('');
    $('#senhaNova').val('');
    $('#senhaNovaConfirmacao').val('');
    $('.modal-alterar_senha').modal('toggle');
});

function mostrarSwalErroPadrao() {
    swal(tr('erro!', 'Erro!'), tr('tempoDeRespostaDoServidorEsgotado!', 'Tempo de resposta do servidor esgotou!'), 'error').catch(swal.noop);
}

$(document).ready(function () {
    $image_crop = $('#imagemUsuario').croppie({
        enableExif: true,
        viewport: {
            width: 150,
            height: 150,
            type: 'circle'
        },
        boundary: {
            width: 200,
            height: 200
        }
    });

    $('#upload_imagem').on('change', function () {
        const reader = new FileReader();
        reader.onload = function (event) {
            $image_crop.croppie('bind', {
                url: event.target.result
            });
        };
        reader.readAsDataURL(this.files[0]);
        $('#uploadimagemModal').modal('show');
    });

    $('.crop_image').off('click').on('click', function () {
        const urlAlterarPerfil = $('.data-views').data('imagem_perfil');
        $image_crop.croppie('result', {
            type: 'canvas',
            size: 'viewport'
        }).then(function (response) {
            $.ajax({
                url: urlAlterarPerfil,
                type: 'POST',
                data: {
                    image: response,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    $('#uploadimagemModal').modal('hide');
                    $('#uploaded_imagem').html(data);
                }
            });
        });
    });

    $('a.redefinirSenha').off('click').on('click', function () {
        const obj = $(this);
        const url = obj.data('url');
        const usuarioLogin = $('#usuario').val();

        swal({
            title: tr('solicitarRedefinicaoDeSenhaDoUsuario?', 'Deseja solicitar redefinição de senha para [REPLACE_USUARIO]?').replace('[REPLACE_USUARIO]', usuarioLogin),
            text: tr('casoContinueOUsuarioReceberaUmEmailParaRedefinicaoDaSenhaDesejaProsseguir?', 'O usuário receberá um e-mail para redefinir a senha. Deseja continuar?'),
            icon: 'warning',
            buttons: {
                confirm: tr('sim!', 'Sim'),
                cancel: tr('cancelar!', 'Cancelar')
            },
            dangerMode: true
        }).then(function (result) {
            if (!result) {
                return;
            }

            toggleLoading();
            ajaxRequest(true, url, null, 'text', { usuario: usuarioLogin }, function (ret) {
                try {
                    const response = JSON.parse(ret);
                    swal(response.titulo, response.text, response.class).catch(swal.noop);
                } catch (err) {
                    mostrarSwalErroPadrao();
                    forceToggleLoading();
                } finally {
                    toggleLoading();
                }
            });
        }).catch(swal.noop);
    });

    if (!isOldLayout) {
        alterarPreferenciaIdioma();
        alterarPreferenciaTema();
    }
});

function alterarSenha() {
    $('#btn-alterar_senha_sim').off('click').on('click', function () {
        toggleLoading();
        const url = $(this).data('url');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                current_password: $('#senhaAtual').val(),
                new_password: $('#senhaNova').val(),
                new_password_confirmation: $('#senhaNovaConfirmacao').val()
            },
            dataType: 'json',
            success: function (ret) {
                toggleLoading();
                swal(ret.titulo || 'Sucesso!', ret.text || 'Senha atualizada com sucesso.', ret.class || 'success').then(function () {
                    $('.modal-alterar_senha').modal('hide');
                }).catch(swal.noop);
            },
            error: function (xhr) {
                toggleLoading();
                const errorText = xhr.responseJSON && xhr.responseJSON.text ? xhr.responseJSON.text : 'Erro ao atualizar senha.';
                swal('Erro!', errorText, 'error').catch(swal.noop);
            }
        });
    });
}

function alterarPreferenciaIdioma() {
    let idiomaValorAnterior;
    const select = $('#idioma');

    select.on('select2:selecting', function () {
        idiomaValorAnterior = $(this).val();
    });

    select.on('select2:select', function () {
        const valIdioma = $(this).val();
        const url = $(this).data('url');

        swal({
            title: tr('desejaContinuar?', 'Deseja continuar?'),
            text: tr('desejaAlterarOIdiomaDeLeituraDoPortal', 'Deseja alterar o idioma do portal?'),
            icon: 'warning',
            buttons: {
                confirm: tr('sim!', 'Sim'),
                cancel: tr('cancelar!', 'Cancelar')
            },
            dangerMode: true
        }).then(function (result) {
            if (!result) {
                select.val(idiomaValorAnterior).trigger('change');
                return;
            }

            toggleLoading();
            ajaxRequest(true, url, null, 'text', { idioma: valIdioma }, function (ret) {
                try {
                    const response = JSON.parse(ret);
                    swal(response.titulo, response.text, response.class).then(function () {
                        location.reload();
                    }).catch(swal.noop);
                } catch (err) {
                    mostrarSwalErroPadrao();
                    forceToggleLoading();
                } finally {
                    toggleLoading();
                }
            });
        }).catch(function () {
            select.val(idiomaValorAnterior).trigger('change');
        });
    });
}

function alterarPreferenciaTema() {
    let temaValorAnterior;
    const select = $('#tema');

    select.on('select2:selecting', function () {
        temaValorAnterior = $(this).val();
    });

    select.on('select2:select', function () {
        const valTema = $(this).val();
        const url = $(this).data('url');

        swal({
            title: tr('desejaContinuar?', 'Deseja continuar?'),
            text: tr('desejaAPreferenciaDoTemaDeExibicaoDoPortal', 'Deseja alterar a preferência de tema?'),
            icon: 'warning',
            buttons: {
                confirm: tr('sim!', 'Sim'),
                cancel: tr('cancelar!', 'Cancelar')
            },
            dangerMode: true
        }).then(function (result) {
            if (!result) {
                select.val(temaValorAnterior).trigger('change');
                return;
            }

            toggleLoading();
            ajaxRequest(true, url, null, 'text', {
                tema: valTema,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function (ret) {
                try {
                    const response = JSON.parse(ret);
                    if (response) {
                        toggleTheme(valTema);
                        swal(response.titulo, response.text, response.class);
                    } else {
                        mostrarSwalErroPadrao();
                    }
                } catch (err) {
                    mostrarSwalErroPadrao();
                    forceToggleLoading();
                } finally {
                    toggleLoading();
                }
            });
        }).catch(function () {
            select.val(temaValorAnterior).trigger('change');
        });
    });
}

alterarSenha();
initSelectColaboradores();
