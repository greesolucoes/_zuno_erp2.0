$('input[type=file]').change(() => {
    var filename = $('input[type=file]')
        .val()
        .replace(/.*(\/|\\)/, '');
    $('#filename').html(filename);
});

var mask = '00';

var SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11
            ? '(00) 00000-0000'
            : '(00) 0000-00009';
    },
    spOptions = {
        onKeyPress: function (val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        },
    };

$('.fone').mask(SPMaskBehavior, spOptions);

$(document).on('input', '#numero_whatsapp', function () {
    const input = $(this);
    const digits = input.val().replace(/\D/g, '').slice(0, 11);
    input.val(digits);
    input.mask(SPMaskBehavior, spOptions);
});

if (!window.SPMaskBehavior) {
    window.SPMaskBehavior = SPMaskBehavior;
}

var cpfMascara = function (val) {
        return val.replace(/\D/g, '').length > 11
            ? '00.000.000/0000-00'
            : '000.000.000-009';
    },
    cpfOptions = {
        onKeyPress: function (val, e, field, options) {
            field.mask(cpfMascara.apply({}, arguments), options);
        },
    };

$(document).on('focus', '.cnpj', function () {
    $(this).mask('00.000.000/0000-00', { reverse: true });
});

$(document).on('focus', '.cpf', function () {
    $(this).mask('000.000.000-00', { reverse: true });
});

$(document).on('focus', '.moeda', function () {
    $(this).mask('00000000,00', { reverse: true });
});
$(document).ready(function () {
    $('.moeda').mask('00000000,00', { reverse: true });
});

$(document).on('focus', '.coordenada', function () {
    $(this).mask('-00.0000000', { placeholder: '-11.1111111' });
});

$(document).on('focus', '.comissao', function () {
    $(this).mask('000,00', { reverse: true });
});

$(document).on('focus', '.minutes', function () {
    $(this).mask('00', { reverse: true });
});
$(document).on('focus', '.timer', function () {
    $(this).mask('00:00', { reverse: true });
});

$(document).on('focus', '.dias', function () {
    $(this).mask('000000000', { reverse: true });
});
$(document).ready(function () {
    $('.dias').mask('000000000', { reverse: true });
});

$(document).on('focus', '.qtd', function () {
    $(this).mask('00000000,00', { reverse: true });
});

$(document).on('focus', '.quantidade', function () {
    $(this).mask('0000000.000', { reverse: true });
});

$(document).on('focus', '.peso', function () {
    $(this).mask('00000000.000', { reverse: true });
});

$(document).on('focus', '.percentual', function () {
    $(this).mask('000,00%', { reverse: true });
});
$(document).ready(function () {
    $('.percentual').mask('000,00%', { reverse: true });
});

$(document).on('focus', '.parcela', function () {
    $(this).mask('00x', { reverse: true });
});
$(document).ready(function () {
    $('.parcela').mask('00x', { reverse: true });
});
$(document).on('focus', '.percentual_lucro', function () {
    $(this).mask('00000.00', { reverse: true });
});
$(document).on('focus', '.cpf_cnpj', function () {
    $(this).mask(cpfMascara, cpfOptions);
});
$(document).on('focus', '.codigo_servico', function () {
    $(this).mask('00.00.00');
});
$(document).on('focus', '.cnae', function () {
    $(this).mask('00.00-0/00', { reverse: true });
});

$(document).on('blur', '.form-control', function () {
    let input = $(this);

    if (input.hasClass('ignore')) return;

    if (!$(this).prop('required') && input.val() === '') {
        input.removeClass('is-invalid');
        input.tooltip('dispose');

        return;
    }

    if (input.prop('required') && input.val() === '') {
        input.addClass('is-invalid');
        initializeTooltip(input, 'Esse campo é obrigatório.');
    }

    if (input.val() != '') {
        input.removeClass('is-invalid');
        input.tooltip('dispose');
        input.addClass('is-valid');
    } else {
        input.removeClass('is-valid');
    }
})
function validateSelect2(element) {
    const $select = element.is('select')
        ? element
        : (function () {
              const withSelect2Class = element.prev('select.select2');

              if (withSelect2Class.length) {
                  return withSelect2Class;
              }

              return element.prev('select');
          })();
    const $container = element.is('select')
        ? element.next('.select2-container')
        : element.hasClass('select2-container')
            ? element
            : element.next('.select2-container');

    if (!$select.length && !element.is('select')) {
        return;
    }

    const $baseSelect = $select.length ? $select : element;
    const $effectiveContainer = $container.length ? $container : $baseSelect.next('.select2-container');
    const $selections = $effectiveContainer.find('.select2-selection');
    const $tooltipTarget = $effectiveContainer.length ? $effectiveContainer : $baseSelect;
    const $labels = $baseSelect.siblings('label');

    const isRequired = $baseSelect.prop('required') || $baseSelect.hasClass('required') || $labels.hasClass('required');
    const hasIgnoreClass = $baseSelect.hasClass('ignore') || $baseSelect.siblings('.form-control').hasClass('ignore');
    const isDisabled = $baseSelect.prop('disabled');

    const rawValue = $baseSelect.val();
    const hasValue = Array.isArray(rawValue)
        ? rawValue.some(function (item) {
              if (item === null || item === undefined) {
                  return false;
              }

              if (typeof item === 'string') {
                  return item.trim() !== '';
              }

              return true;
          })
        : (function (value) {
              if (value === null || value === undefined) {
                  return false;
              }

              if (typeof value === 'string') {
                  return value.trim() !== '';
              }

              return true;
          })(rawValue);

    if (hasIgnoreClass || isDisabled || (!isRequired && !hasValue)) {
        $selections.removeClass('select2-valid select2-invalid');
        $tooltipTarget.tooltip('dispose');

        return;
    }

    if (!hasValue) {
        $selections.removeClass('select2-valid').addClass('select2-invalid');
        initializeTooltip($tooltipTarget, 'Esse campo é obrigatório.');

        return;
    }

    $selections.removeClass('select2-invalid').addClass('select2-valid');
    $tooltipTarget.tooltip('dispose');
}

$(document).on('blur change', '.select2', function () {
    validateSelect2($(this));
});

$(document).on('select2:close select2:clear select2:select', '.select2', function () {
    validateSelect2($(this));
});

$(document).on('blur', '.fone', function () {
    const input = $(this);

    const digits = input.val().replace(/\D/g, '');

    if (!$(this).prop('required') && input.val() === '') {
        input.removeClass('is-invalid');
        input.tooltip('dispose');
        return;
    }

    if (digits.length < 10 || digits.length > 11) {
        input.addClass('is-invalid');
        initializeTooltip(input, 'Número inválido.');

        return;
    }

    input.removeClass('is-invalid');
    input.tooltip('dispose');
    input.addClass('is-valid');
    input.prop('title', '');
})
$(document).on('blur', 'input[type="email"]', function() {
    const input = $(this);
    const email = input.val().trim();

    const email_regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!$(this).prop('required') && input.val() === '') {
        input.removeClass('is-invalid');
        input.tooltip('dispose');

        return;
    }

    if (email === '' || !email_regex.test(email)) {
        input.addClass('is-invalid');
        input.removeClass('is-valid');
        initializeTooltip(input, 'E-mail inválido.');
        return;
    }

    input.removeClass('is-invalid');
    input.addClass('is-valid');
    input.tooltip('dispose');
});

$(document).on('input blur', '#user_password', function() {
    const input = $(this);
    const password = input.val().trim();

    const min_length = password.length >= 8;
    const has_lowercase = /[a-z]/.test(password);
    const has_uppercase = /[A-Z]/.test(password);
    const has_number = /[0-9]/.test(password);
    const has_special = /[@$!%*?&]/.test(password);

    const missing = [];

    if (!min_length) missing.push('mínimo de 8 caracteres');
    if (!has_lowercase) missing.push('uma letra minúscula');
    if (!has_uppercase) missing.push('uma letra maiúscula');
    if (!has_number) missing.push('um número');
    if (!has_special) missing.push('um caractere especial (@$!%*?&)');

    if (missing.length > 0) {
        input.addClass('is-invalid').removeClass('is-valid');
        initializeTooltip(input, 'A senha deve conter ' + missing[0] + '.');
    } else {
        input.removeClass('is-invalid').addClass('is-valid');
        input.tooltip('dispose');
    }
});

$(document).on('blur', '.cep', function () {
    const input = $(this);
    let cep = input.val().trim();

    cep = cep.replace(/\D/g, '');

    if (!$(this).prop('required') && input.val() === '') {
        input.removeClass('is-invalid');
        input.tooltip('dispose');

        return;
    }

    if (cep.length !== 8) {
        input.addClass('is-invalid');
        input.removeClass('is-valid');
        initializeTooltip(input, 'CEP inválido.');

        return;
    }

    input.removeClass('is-invalid');
    input.addClass('is-valid');
    input.tooltip('dispose');

})
$(document).on('blur', '.cpf', function () {
    const input = $(this);
    const digits = input.val().replace(/\D/g, '');
    let res = false;

    if (digits.length !== 11) {
        input.addClass('is-invalid');
        initializeTooltip(input, 'CPF inválido.');
        return;
    }

    if (digits.length === 11) {
        res = validateCpf(digits);
    }

    if (!res) {
        input.addClass('is-invalid');
        initializeTooltip(input, 'CPF inválido.');
    }
});
$(document).on('blur', '.cnpj', function () {
    const input = $(this);
    const digits = input.val().replace(/\D/g, '');
    let res = false;

    if (digits.length != 14) {
        input.addClass('is-invalid');
        initializeTooltip(input, 'CNPJ inválido.');
        return;
    }

    res = validateCnpj(digits);

    if (!res) {
        input.addClass('is-invalid');
        initializeTooltip(input, 'CNPJ inválido.');
    }
});

$(document).find("input[name='cep']").each(function () {
    $(this).on("blur", function () {
        const container = $(this).closest('.row') || $(this).closest('form') || $(this).closest('.modal-body');

        let cep = $(this).val().replace(/[^0-9]/g,'')

        if(cep.length == 8){
            $.get('https://viacep.com.br/ws/' + cep + '/json/')
            .done((data) => {
                if (data!= null) {
                    container.find('input[name="rua"]').val(data.logradouro).trigger('blur');
                    container.find('input[name="bairro"]').val(data.bairro).trigger('blur');

                    findCidade(data.ibge)
                }
            })
            .fail((err) => {
                new swal("Erro ao buscar ", err.responseJSON['detalhes'], "warning")
            })
        }
    })
})


$(document).on('focus', '.dimensao', function () {
    $(this).mask('00000.00', { reverse: true });
});
$(document).on('focus', '.peso', function () {
    $(this).mask('000000.000', { reverse: true });
});

$(document).ready(function() {
    $("#show_hide_password a").on('click', function(event) {
        event.preventDefault();
        if ($('#show_hide_password input').attr("type") == "text") {
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass("bx-hide");
            $('#show_hide_password i').removeClass("bx-show");
        } else if ($('#show_hide_password input').attr("type") == "password") {
            $('#show_hide_password input').attr('type', 'text');
            $('#show_hide_password i').removeClass("bx-hide");
            $('#show_hide_password i').addClass("bx-show");
        }
    });
});

$(function () {
    $('.cep').mask('00000-000', { reverse: true });
    $('.ncm').mask('0000.00.00', { reverse: true });
    $('.cest').mask('00.000.00', { reverse: true });
    $('.placa').mask('AAA-AAAA', { reverse: true });
    $('.cfop').mask('0000', { reverse: true });
    $('.ie').mask('0000000000000', { reverse: true });

    $body = $('body');

    $(document).on({
        ajaxStart: function () {
            $body.addClass('loading');
        },
        ajaxStop: function () {
            $body.removeClass('loading');
        },
    });

    $('input[required], select[required], textarea[required]')
        .siblings('label')
        .addClass('required');

    $('input.tooltipp, select.tooltipp, textarea.tooltipp')
        .siblings('label')
        .append(
            '<button id="btn_tooltip" type="button" class="btn btn-link btn-tooltip btn-sm" data-toggle="tooltip" data-placement="top" title="Tooltip on top"><i class="ri-file-info-fill"></i></button>',
        );

    $(document).on('focus', '#chave_nfe', function () {
        $(this).mask('0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000', {
            reverse: true,
        });
    });

    $('.datetime-datepicker').flatpickr({
        enableTime: true, // Ativa o seletor de horário
        time_24hr: true, // Usa o formato de 24 horas
        dateFormat: 'Y-m-d H:i', // Formato do valor retornado (interno)
        altInput: true, // Ativa a exibição alternativa
        altFormat: 'd/m/Y H:i', // Formato para exibição no input
        locale: 'pt', // Define o idioma para português
    });

    if ($('.text-tooltip')) {
        let texto = $('.text-tooltip').html();
        $('#btn_tooltip').prop('title', texto);
        $('#btn_tooltip').tooltip();
    }

    $('input.tooltipp2, select.tooltipp2, textarea.tooltipp2')
        .siblings('label')
        .append(
            '<button id="btn_tooltip2" type="button" class="btn btn-link btn-tooltip btn-sm" data-toggle="tooltip" data-placement="top" title="Tooltip on top"><i class="ri-file-info-fill"></i></button>',
        );

    if ($('.text-tooltip2')) {
        let texto = $('.text-tooltip2').html();

        $('#btn_tooltip2').prop('title', texto);
        $('#btn_tooltip2').tooltip();
    }

    $('input.tooltipp3, select.tooltipp3, textarea.tooltipp3')
        .siblings('label')
        .append(
            '<button id="btn_tooltip3" type="button" class="btn btn-link btn-tooltip btn-sm" data-toggle="tooltip" data-placement="top" title="Tooltip on top"><i class="ri-file-info-fill"></i></button>',
        );

    if ($('.text-tooltip3')) {
        let texto = $('.text-tooltip3').html();

        $('#btn_tooltip3').prop('title', texto);
        $('#btn_tooltip3').tooltip();
    }

    $('input.tooltipp4, select.tooltipp4, textarea.tooltipp4')
        .siblings('label')
        .append(
            '<button type="button" class="btn btn-link btn-tooltip4 btn-sm" data-toggle="tooltip" data-placement="top" title="Tooltip on top"><i class="ri-file-info-fill"></i></button>',
        );

    if ($('.text-tooltip4')) {
        let texto = $('.text-tooltip4').html();

        $('.btn-tooltip4').prop('title', texto);
        $('.btn-tooltip4').tooltip();
    }

    setTimeout(() => {
        notifications();
        videoSuporte();
    }, 10);
});

function videoSuporte() {
    let currentUrl = window.location.href;
    $.get(path_url + 'api/video-suporte', { url: currentUrl })
        .done((success) => {
            if (success) {
                $('.video').append(success);
            }
        })
        .fail((err) => {
            console.log(err);
        });
}

function convertMoedaToFloat(value) {
    if (!value) {
        return 0;
    }

    const number_without_mask = value.replaceAll('.', '').replaceAll(',', '.');
    const parsedValue = parseFloat(
        number_without_mask.replace(/[^0-9\.]+/g, ''),
    );
    if (isNaN(parsedValue)) {
        return 0;
    }
    return parsedValue;
}

function parseToFloat(strValue) {
    try {
        const value = String(strValue || 0)
            ?.replace('R$ ', '')
            ?.replace('R$', '');
        return [',', '.'].every((calc) => value.includes(calc))
            ? Number(value.replace('.', '').replace(',', '.'))
            : Number(value?.replace(',', '.'));
    } catch (e) {
        console.trace('parseToFloat', { strValue });
        console.error(e);
        return 0;
    }
}

if (!window?.parseToFloat) {
    window.parseToFloat = parseToFloat;
}

function convertFloatToMoeda(value) {
    const parsedValue = parseFloat(value);
    //console.trace('convertFloatToMoeda', { value, parsedValue });
    return parsedValue.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

/**
  * Converte o valor percentual com máscara para float
  * 
  * Ex: 25% -> 0.25
  * 
  * @param {string} value 
  * 
  * @returns Valor limpo  
*/
function convertPercentualToFloat(value) {
    let clean_value = value.replace('%', '').replace(',', '.');

    clean_value = parseFloat(clean_value);

    return clean_value;
}

/**
 * Converte o valor float para percentual com máscara
 *  
 * Ex: 25.5 -> 25,5%
 * 
 * @param {string} value 
 * 
 * @returns Valor formatado
*/
function convertFloatToPercentual(value) {
    let formatted_value = value.replace('.', ',');
    formatted_value = formatted_value + '%';

    return formatted_value;
}

function convertDateToDb(date) {
    const date_class = new Date(date);
    const year = date_class.getFullYear();
    const month = String(date_class.getMonth() + 1).padStart(2, '0');
    const day = String(date_class.getDate()).padStart(2, '0');
    const hours = String(date_class.getHours()).padStart(2, '0');
    const minutes = String(date_class.getMinutes()).padStart(2, '0');
    const seconds = String(date_class.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

/**
 * Converte a data do formato brasileiro para o internacional
 * 
 * @param {string} date - Data que vem no formato brasileiro (DD/MM/YYYY)
 * 
 * @returns {string} Data no formato internacional
 */
function convertPtDateToInternational (date) {
    const [day, month, year] = date.split('/').map(String);

    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
}

/**
 * Converte a data do formato internacional para o brasileiro
 * 
 * @param {string} date - Data que vem no formato internacional (YYYY-MM-DD)
 * 
 * @returns {string} Data no formato brasileiro
 */
function convertInternationalDateToPt (date) {
    const [year, month, day] = date.split('-').map(String);

    return `${day.padStart(2, '0')}/${month.padStart(2, '0')}/${year}`;
}

function convertHoursAndMinutesToInt (time) {
    const [hours, minutes] = time.split(':');

    return parseInt(hours) * 60 + parseInt(minutes);
}

$('.btn-delete').on('click', async function (e) {
    e.preventDefault();
    var form = $(this).parents('form').attr('id');
    const { isConfirmed, ...others } = await Swal.fire({
        html: '<section class="section_modal"><div class="row"><div class="col-12"><p class="text-center">Uma vez deletado, você não poderá recuperar esse item novamente!</p></div></div> </section>',
        icon: 'warning',
        title: 'Você está certo?',
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não',
        customClass: {
            popup: 'larger-modal',
            actions: 'gird-col-1 mt-5',
            title: 'my-1',
        },
    });
    if (isConfirmed) {
        document.getElementById(form).submit();
    } else {
        await Swal.fire({
            icon: 'info',
            title: 'Este item está salvo!',
            showConfirmButton: false,
            showCancelButton: false,
            timer: 3000,
            customClass: {
                popup: 'larger-modal',
                actions: 'gird-col-1',
                title: 'my-1',
            },
        });
    }
});

$('.btn-confirm').on('click', function (e) {
    e.preventDefault();
    var form = $(this).parents('form').attr('id');

    new swal({
        title: 'Você está certo?',
        text: 'Uma vez alterado, você não poderá voltar o estado desse item!',
        icon: 'warning',
        buttons: true,
        buttons: ['Cancelar', 'OK'],
        dangerMode: true,
    }).then((isConfirm) => {
        if (isConfirm) {
            document.getElementById(form).submit();
        } else {
            new swal('', 'Este item não foi alterado', 'info');
        }
    });
});

$(document).on('submit', 'form', function () {
  const form = $(this);

    form.find('button:not(:disabled)')
        .attr('data-temp-disabled', '1')
        .prop('disabled', true);

    setTimeout(() => {
        form.find('input, select, textarea').filter(function () {
            return !this.disabled;
        })
        .attr('data-temp-disabled', '1').prop('disabled', true);
    }, 100);

    const btn = form.find("button:contains('Salvar')");
    if (btn.length) {
        btn.attr('data-original-text', btn.html());
        btn.html('Salvando...');
    }
});

function restoreTempDisabled() {
  $('[data-temp-disabled]')
    .prop('disabled', false)
    .removeAttr('data-temp-disabled');

  $("button[data-original-text]").each(function () {
    $(this).html($(this)
    .attr('data-original-text'))
    .removeAttr('data-original-text');
  });
}
$(window).on('pageshow', function (e) {
  restoreTempDisabled();
});
$(window).on('pagehide', function () {
  restoreTempDisabled();
});


function select2Defaults(options = {}) {
    return Object.assign(
        {
            minimumInputLength: 2,
            language: 'pt-BR',
            width: '100%',
            theme: 'bootstrap4',
        },
        options,
    );
}

$('.select2').select2({
    width: $(this).data('width')
        ? $(this).data('width')
        : $(this).hasClass('w-100')
          ? '100%'
          : 'style',
    placeholder: $(this).data('placeholder'),
    allowClear: Boolean($(this).data('allow-clear')),
    theme: 'bootstrap4',
});

$('.cidade_select2').select2(select2Defaults({
    placeholder: 'Digite para buscar a cidade',
    ajax: {
        cache: true,
        url: path_url + 'api/buscaCidades',
        dataType: 'json',
        data: function (params) {
            return { pesquisa: params.term };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.info;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('.codigo_unico').select2(select2Defaults({
    placeholder: 'Digite para buscar o código',
    ajax: {
        cache: true,
        url: path_url + 'api/produtos/codigo-unico',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.codigo;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-cidade_id').select2(select2Defaults({
    placeholder: 'Digite para buscar a cidade',
    ajax: {
        cache: true,
        url: path_url + 'api/buscaCidades',
        dataType: 'json',
        data: function (params) {
            return { pesquisa: params.term };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.info;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

// Configuração geral do select2 do modal de endereço do cliente 
$('select[name="modal_cidade_id"]').not('.select2-hidden-accessible').first().select2(select2Defaults({
    placeholder: 'Digite para buscar a cidade',
    dropdownParent: $('#modal_endereco_cliente'),
    ajax: {
        cache: true,
        url: path_url + 'api/buscaCidades',
        dataType: 'json',
        data: function (params) {
            return { pesquisa: params.term };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.info;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-plano_conta_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o plano',
    ajax: {
        cache: true,
        url: path_url + 'api/planos-conta',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.descricao;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-conta_empresa_id').select2(select2Defaults({
    placeholder: 'Digite para buscar a conta',
    ajax: {
        cache: true,
        url: path_url + 'api/contas-empresa',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-categoria_nuvem_shop').select2(select2Defaults({
    placeholder: 'Digite para buscar a categoria da nuvem shop',
    ajax: {
        cache: true,
        url: path_url + 'api/nuvemshop/get-categorias',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v._id;

                o.text = v.nome;
                o.value = v._id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-mercado_livre_categoria').select2(select2Defaults({
    placeholder: 'Digite para buscar a categoria do anúncio',
    ajax: {
        cache: true,
        url: path_url + 'api/mercadolivre/get-categorias',
        dataType: 'json',
        data: function (params) {
            return { pesquisa: params.term };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v._id;

                o.text = v.nome;
                o.value = v._id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-ncm').select2(select2Defaults({
    placeholder: 'Digite para buscar o NCM',
    ajax: {
        cache: true,
        url: path_url + 'api/ncm',
        dataType: 'json',
        data: function (params) {
            return { pesquisa: params.term };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.codigo;
                if (v.codigo.length != 10) {
                    o.disabled = 1;
                }

                o.text = v.descricao;
                o.value = v.codigo;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-empresa').select2(select2Defaults({
    placeholder: 'Digite para buscar a empresa',
    ajax: {
        cache: true,
        url: path_url + 'api/empresas/find-all',
        dataType: 'json',
        data: function (params) {
            return { pesquisa: params.term };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.info;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-servico_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o seviço',
    ajax: {
        cache: true,
        url: path_url + 'api/servicos',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.nome + ' R$ ' + convertFloatToMoeda(v.valor);
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-produto_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o produto',
    ajax: {
        cache: true,
        url: path_url + 'api/produtos',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
                usuario_id: $('#usuario_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];
            let compra = 0;
            if ($('#is_compra') && $('#is_compra').val() == 1) {
                compra = 1;
            }

            $.each(response, function (i, v) {
                results.push(parseProduto(v, compra));
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-produto_composto_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o produto composto',
    ajax: {
        cache: true,
        url: path_url + 'api/produtos-composto',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];
            let compra = 0;
            if ($('#is_compra') && $('#is_compra').val() == 1) {
                compra = 1;
            }

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                if (v.codigo_variacao) {
                    o.codigo_variacao = v.codigo_variacao;
                }

                o.text = v.nome;
                if (compra == 0) {
                    if (parseFloat(v.valor_unitario) > 0) {
                        o.text +=
                            ' R$ ' + convertFloatToMoeda(v.valor_unitario);
                    }
                } else {
                    o.text += ' R$ ' + convertFloatToMoeda(v.valor_compra);
                }
                if (v.codigo_barras) {
                    o.text += ' [' + v.codigo_barras + ']';
                }
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-produto_combo_id').select2(select2Defaults({
    placeholder: 'Digite para adicionar o produto no combo',
    ajax: {
        cache: true,
        url: path_url + 'api/produtos-combo',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];
            let compra = 0;

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                if (v.codigo_variacao) {
                    o.codigo_variacao = v.codigo_variacao;
                }

                o.text = v.nome;

                o.text += ' R$ ' + convertFloatToMoeda(v.valor_compra);
                if (v.codigo_barras) {
                    o.text += ' [' + v.codigo_barras + ']';
                }
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-empresa_contador_id').select2(select2Defaults({
    placeholder: 'Digite para buscar a empresa',
    ajax: {
        cache: true,
        url: path_url + 'api/empresas',
        dataType: 'json',
        data: function (params) {
            return { pesquisa: params.term };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text = v.info;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-ingrediente_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o produto',
    ajax: {
        cache: true,
        url: path_url + 'api/produtos',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                results.push(parseProduto(v));
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-funcionario_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o colaborador',

    ajax: {
        cache: true,
        url: path_url + 'api/funcionarios/pesquisa',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text =
                    v.nome +
                    ' - Cargo: ' +
                    v.cargo;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-prestador_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o prestador de serviço',

    ajax: {
        cache: true,
        url: path_url + 'api/prestadores/pesquisa',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                if (v.nome) {
                    o.text = v.nome;
                } else if (v.nome_fantasia) {
                    o.text = v.nome_fantasia;
                } else if (v.razao_social) {
                    o.text = v.razao_social;
                } else {
                    o.text = '';
                }

                if (v.is_fornecedor) {
                    o.id = 'forn_' + v.id;

                    $('#inp-hidden_fornecedor_id').val(v.id);
                    $('#inp-hidden_funcionario_id').val(null);
                } else {
                    o.id = 'func_' + v.id;

                    $('#inp-hidden_fornecedor_id').val(null);
                    $('#inp-hidden_funcionario_id').val(v.id);
                }

                results.push(o);
            });

            return {
                results: results,
            };
        },
    },
}));

$('#inp-animal_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o animal (pet)',

    ajax: {
        cache: true,
        url: path_url + 'api/animais/',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];
            $.each(response.data, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text =
                    v.nome +
                    ' -  Tutor: ' + v.cliente.razao_social;
                o.value = v.id;
                o.cliente_id = v.cliente_id;

                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
})).on('select2:select', function (e) {
    var data = e.params.data;

    if ($('input[name="cliente_id"]').length > 0) {
        $('input[name="cliente_id"]').val(data.cliente_id);
    }
});
const selected_animal = $('input[name="id_animal"]').val();
const label_animal = $('input[name="animal_info"]').val();
if (selected_animal && label_animal) {
    const option = new Option(label_animal, selected_animal, true, true);
    $('select[name="animal_id"]').append(option).trigger('change');
}

$('#inp-cliente_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o cliente',

    ajax: {
        cache: true,
        url: path_url + 'api/clientes/pesquisa',
        dataType: 'json',
        data: function (params) {
            // console.clear();
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text = v.razao_social + ' - ' + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-cliente_delivery_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o cliente',

    ajax: {
        cache: true,
        url: path_url + 'api/clientes/pesquisa-delivery',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.razao_social + ' - ' + v.telefone;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('.cliente_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o cliente',

    ajax: {
        cache: true,
        url: path_url + 'api/clientes/pesquisa',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };

        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.razao_social + ' - ' + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-fornecedor_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o fornecedor',
    ajax: {
        cache: true,
        url: path_url + 'api/fornecedores/pesquisa',
        dataType: 'json',
        data: function (params) {
            return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.razao_social + ' - ' + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$(document).ready(function() {
    $('.status-select').each(function() {
        $(this).css('--ct-form-select-bg-img', 'none');
        $(this).css('padding', '0.45rem 2px');
    });
});

$('.button-toggle-menu').on('click', function () {
    $.post(path_url + 'api/usuarios/set-sidebar', {
        usuario_id: $('#usuario_id').val(),
    })
        .done((success) => {})
        .fail((err) => {
            console.log(err);
        });
});

$('.btn-add-tr').on('click', function () {
    console.clear();
    var $table = $(this).closest('.row').prev().find('.table-dynamic');

    var hasEmpty = false;

    $table.find('input, select').each(function () {
        if (
            ($(this).val() == '' || $(this).val() == null) &&
            $(this).attr('type') != 'hidden' &&
            $(this).attr('type') != 'file' &&
            !$(this).hasClass('ignore')
        ) {
            hasEmpty = true;
        }
    });

    if (hasEmpty) {
        new swal(
            'Atenção',
            'Preencha todos os campos antes de adicionar novos.',
            'warning',
        );
        return;
    }
    // $table.find("select.select2").select2("destroy");
    var $tr = $table.find('.dynamic-form').first();
    $tr.find('select.select2').select2('destroy');
    var $clone = $tr.clone();
    $clone.show();

    $clone.find('input,select').val('');
    $table.append($clone);
    setTimeout(function () {
        $('tbody select.select2').select2({
            language: 'pt-BR',
            width: '100%',
            theme: 'bootstrap4',
        });
    }, 100);
});

$(document).delegate('.btn-remove-tr', 'click', function (e) {
    e.preventDefault();

    new swal({
        title: 'Você esta certo?',
        text: 'Deseja remover esse item mesmo?',
        icon: 'warning',
        buttons: true,
    }).then((willDelete) => {
        if (willDelete) {
            var trLength = $(this)
                .closest('tr')
                .closest('tbody')
                .find('tr')
                .not('.dynamic-form-document').length;
            if (!trLength || trLength > 1) {
                $(this).closest('tr').remove();
            } else {
                new swal(
                    'Atenção',
                    'Você deve ter ao menos um item na lista',
                    'warning',
                );
            }
        }
    });
});

$('.multi-select').bootstrapDualListbox({
    nonSelectedListLabel: 'Disponíveis',
    selectedListLabel: 'Selecionados',
    filterPlaceHolder: 'Filtrar',
    filterTextClear: 'Mostrar Todos',
    moveSelectedLabel: 'Mover Selecionados',
    moveAllLabel: 'Mover Todos',
    removeSelectedLabel: 'Remover Selecionado',
    removeAllLabel: 'Remover Todos',
    infoText: 'Mostrando Todos - {0}',
    infoTextFiltered:
        '<span class="label label-warning">Filtrado</span> {0} DE {1}',
    infoTextEmpty: 'Sem Dados',
    moveOnSelect: false,
    selectorMinimalHeight: 300,
});

function notifications() {
    if ($('#empresa_id').val()) {
        $.get(path_url + 'api/notificacoes-alertas', {
            empresa_id: $('#empresa_id').val(),
        })
            .done((success) => {
                $('.spinner-border').addClass('d-none');
                if (success.length > 0) {
                    $('.noti-icon-badge').removeClass('d-none');
                }
                $('.alertas-main').html(success);
            })
            .fail((err) => {
                $('.spinner-border').addClass('d-none');
            });
    } else {
        if ($('#usuario_id').val()) {
            $.get(path_url + 'api/notificacoes-alertas-super', {
                usuario_id: $('#usuario_id').val(),
            })
                .done((success) => {
                    $('.spinner-border').addClass('d-none');
                    if (success.length > 0) {
                        $('.noti-icon-badge').removeClass('d-none');
                    }
                    $('.alertas-main').html(success);
                })
                .fail((err) => {
                    $('.spinner-border').addClass('d-none');
                });
            $('.spinner-border').addClass('d-none');
        }
    }
}

$('#inp-cliente_id').change(function () {
    let cliente = $(this).val();

    $.ajax({
        cache: true,
        url: path_url + 'api/clientes/pesquisa',
        dataType: 'json',
        method: 'GET',
        data: {
            id: cliente,
            pesquisa: cliente,
            empresa_id: $('#empresa_id').val(),
        },
        success: function (response) {
            if (response.length > 0) {
                let clienteData = response[0];
                let selectTipoNome = $('#inp-tipo_nome');
                selectTipoNome.empty();

                let razaoSocialOption = new Option(
                    clienteData.razao_social,
                    'razao_social',
                );
                let nomeFantasiaOption = new Option(
                    clienteData.nome_fantasia,
                    'nome_fantasia',
                );
                razaoSocialOption.text = `Razão Social - ${clienteData.razao_social}`;
                nomeFantasiaOption.text = `Nome Fantasia - ${clienteData.nome_fantasia}`;
                selectTipoNome
                    .append(razaoSocialOption)
                    .append(nomeFantasiaOption);

                selectTipoNome.trigger('change.select2');
            }
        },
    });
});

function parseProduto(v, compra = 0, estoque = false) {
    var o = {};
    o.id = v.id;
    if (v.codigo_variacao) {
        o.codigo_variacao = v.codigo_variacao;
    }

    o.text = v.nome;
    if (v?.combinacoes) {
        o.text += ` ${v.combinacoes}`;
    }
    if (compra == 0) {
        if (parseFloat(v.valor_unitario) > 0) {
            o.text += ' R$ ' + convertFloatToMoeda(v.valor_unitario);
        }
    } else {
        o.text += ' R$ ' + convertFloatToMoeda(v.valor_compra);
    }

    if (estoque) {
        o.text += ' | Estoque: ' + v.estoque_atual;
    }
    if (v.codigo_barras) {
        o.text += ' [' + v.codigo_barras + ']';
    }
    o.value = v.id;
    return o;
}

if (!window.parseProduto) {
    window.parseProduto = parseProduto;
}

function getOnEnter(elementClass) {
    return function saveOnEnter(event) {
        if (event.target.value.trim() && event.key.toLowerCase() === 'enter') {
            event.preventDefault();
            document.querySelector(elementClass)?.click();
        }
    };
}

if (!window.getOnEnter) {
    window.getOnEnter = getOnEnter;
}

function recalculate() {
    const inpValorTotal = document.getElementById('inp-valor_unitario');
    const inpValorCompra = document.getElementById('inp-valor_total');
    const inpMargemBruta = document.getElementById('inp-margem_bruta');
    const inpLucro = document.getElementById('inp-lucro');
    const inpValorTotal_value = convertMoedaToFloat(inpValorTotal.value);
    const inpValorCompra_value = convertMoedaToFloat(inpValorCompra.value);

    if (inpValorTotal_value > 0) {
        const value =
            ((inpValorTotal_value - inpValorCompra_value) /
                inpValorTotal_value) *
            100;
        inpMargemBruta.value = convertFloatToMoeda(value);
    }
    inpLucro.value = convertFloatToMoeda(
        inpValorTotal_value - inpValorCompra_value,
    );
}

if (!window.recalculate) {
    window.recalculate = recalculate;
}

function addClassRequired(form, is_modal = false) {
    let infMsg = "<div class='pending-fields-modal'>"; // lista com indentação

    $(`body ${form}`)
        .find('input, select, textarea, .input-group input')
        .each(function () {
            if (
                $(this).prop('required') &&
                !$(this).hasClass('ignore') &&
                (!is_modal ? $(this).closest('.modal').length == 0 : true )
            ) {
                if ($(this).val() == '' || $(this).val() == null) {
                    try {
                        $(this).trigger('blur');


                        let tabPanelParent = $(this).closest('div[role="tabpanel"]');
                        let tabLabel = tabPanelParent.data('label');

                        let label =
                            $(this).prev('label').text() ||
                            $(this).parent().siblings('label').text() ||
                            $(this).attr('data-label') ||
                            $(this).closest('.form-group').find('label').text();

                        if (label) {
                            infMsg += `
                                <li class="pending-field">
                                    ${label} ${tabLabel ? `- ${tabLabel}` : ''}
                                </li>`
                        }
                    } catch {}
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

    infMsg += '</div>';

    if (infMsg !== "<div class='pending-fields-modal'></div>") {
        new swal({
            title: 'Campos pendentes',
            html: infMsg,
            icon: 'warning',
            width: '500px',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ff6b00',
        }).then(() => {
            if ($('.is-invalid').length > 0) {
                $('.is-invalid')[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            } else {
                $('.select2-invalid')[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
            }
        });

        return false;
    }

    return true;
}

if (!window.addClassRequired) {
    window.addClassRequired = addClassRequired;
}

function validateCpf(cpf) {
    if (/^(\d)\1{10}$/.test(cpf)) return false;

    for (let t = 9; t < 11; t++) {
        let d = 0;
        for (let c = 0; c < t; c++) {
            d += parseInt(cpf[c]) * ((t + 1) - c);
        }

        d = ((10 * d) % 11) % 10;

        if (parseInt(cpf[t]) !== d) return false;
    }

    return true;
}

function validateCnpj(cnpj) {
    if (/^(\d)\1{13}$/.test(cnpj)) return false;

    const peso1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    const peso2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    let soma1 = 0;
    let soma2 = 0;

    for (let i = 0; i < 12; i++) {
        soma1 += parseInt(cnpj[i]) * peso1[i];
    }

    let resto1 = soma1 % 11;
    let dig1 = resto1 < 2 ? 0 : 11 - resto1;

    if (parseInt(cnpj[12]) !== dig1) return false;

    for (let i = 0; i < 13; i++) {
        soma2 += parseInt(cnpj[i]) * peso2[i];
    }

    let resto2 = soma2 % 11;
    let dig2 = resto2 < 2 ? 0 : 11 - resto2;

    return parseInt(cnpj[13]) === dig2;
}

function isValidCpfCnpj(value) {
    const digits = value.replace(/\D/g, '');

    if (digits.length <= 11 && !validateCpf(digits)) {
        return 'CPF inválido.';
    }
    if (digits.length <= 14 && digits.length > 11 && !validateCnpj(digits)) {
        return 'CNPJ inválido.';
    }

    return true;
}

if (!window.validateCpf) {
    window.validateCpf = validateCpf;
}
if (!window.validateCnpj) {
    window.validateCnpj = validateCnpj;
}
if (!window.isValidCpfCnpj) {
    window.isValidCpfCnpj = isValidCpfCnpj;
}

function checkIfCpfCnpjIsAvailable(element, endpoint) {
    let digits = element.val().replace(/[^0-9]/g,'');

    const id = window.location.pathname.split('/edit')[0].split('/').pop();

    $.post(path_url + endpoint, {
        cpf_cnpj: element.val(),
        empresa_id: $('#empresa_id').val(),
        id
    })
    .done(function (res) {
        if (res.success) {
            element.addClass('is-invalid');

            if (digits.length == 11) {
                initializeTooltip(element, 'Esse CPF já está em uso.');
            } else {
                initializeTooltip(element, 'Esse CNPJ já está em uso.');
            }
        }
    })
    .fail(function (res) {
        if (res.status === 404) {
            searchDataByCnpj(digits);
            element.removeClass('is-invalid');
            element.tooltip('dispose');
            element.addClass('is-valid');
        } else {
            new swal(`Erro ao verificar ${digits.length == 11 ? 'CPF' : 'CNPJ'}`, `Ocorreu um erro desconhecido ao verificar o ${digits.length == 11 ? 'CPF' : 'CNPJ'}. Tente novamente mais tarde.`, 'warning');
            element.val('');
        }
    });
}

function searchDataByCnpj (cnpj) {
    cnpj = cnpj.replace(/[^0-9]/g,'')

    if (cnpj.length != 14) return;

    $.get('https://publica.cnpj.ws/cnpj/' + cnpj)
    .done((data) => {
        if (data!= null) {
            let ie = ''
            if (data.estabelecimento.inscricoes_estaduais.length > 0) {
                ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual
            }

            $('#inp-ie').val(ie)
            $('#inp-ie').trigger('blur');
            if(ie != ""){
                $('#inp-contribuinte').val(1).change()
            }
            $('#inp-razao_social').val(data.razao_social)
            $('#inp-razao_social').trigger('blur');
            $('#inp-nome_fantasia').val(data.estabelecimento.nome_fantasia)
            $('#inp-nome_fantasia').trigger('blur');
            $("#inp-rua").val(data.estabelecimento.tipo_logradouro + " " + data.estabelecimento.logradouro)
            $("#inp-rua").trigger('blur');
            $('#inp-numero').val(data.estabelecimento.numero)
            $('#inp-numero').trigger('blur');
            $("#inp-bairro").val(data.estabelecimento.bairro);
            $("#inp-bairro").trigger('blur');
            let cep = data.estabelecimento.cep.replace(/[^\d]+/g, '');
            $('#inp-cep').val(cep.substring(0, 5) + '-' + cep.substring(5, 9))
            $('#inp-cep').trigger('blur');
            $('#inp-email').val(data.estabelecimento.email)
            $('#inp-email').trigger('blur');
            let telefone = data.estabelecimento.ddd1 + data.estabelecimento.telefone1
            $('#inp-telefone').val(telefone).mask('(00) 00000-0000');
            $('#inp-telefone').trigger('change');
            $('#inp-telefone').trigger('blur');

            findCidade(data.estabelecimento.cidade.ibge_id)

        }
    })
    .fail((err) => {
        new swal('Erro ao buscar CNPJ', 'CNPJ não encontrado ou inválido.', 'warning');
    })
}

if (!window.checkIfCpfCnpjIsAvailable) {
    window.checkIfCpfCnpjIsAvailable = checkIfCpfCnpjIsAvailable;
}

if (!window.searchDataByCnpj) {
    window.searchDataByCnpj = searchDataByCnpj;
}

function findCidade(codigo_ibge){
    $('select[name="cidade_id"]').html('');
    $('select[name="modal_cidade_id"]').html('');

    $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
    .done((res) => {
        var newOption = new Option(res.info, res.id, false, false);
        $('select[name="cidade_id"]').append(newOption).trigger('change');
        $('select[name="cidade_id"]').siblings('.select2').find('.select2-selection--single').addClass('select2-valid')

        if ($('select[name="modal_cidade_id"]').length > 0) {
            $('select[name="modal_cidade_id"]').append(newOption).trigger('change');
            $('select[name="modal_cidade_id"]').siblings('.select2').find('.select2-selection--single').addClass('select2-valid')
        }
    })
    .fail((err) => {
        console.log(err)
    })
}

if (!window.findCidade) {
    window.findCidade = findCidade;
}

function initializeTooltip(element, message) {
    if (element instanceof jQuery) {    
        element.tooltip({
            title: message,
            placement: 'bottom',
            trigger: 'hover',
            template: `
                <div class="tooltip tooltip-invalid" role="tooltip">
                    <div class="arrow"></div>
                    <div class="tooltip-inner"></div>
                </div>
            `,
            container: 'body'
        });
    } else {
        $(element).tooltip({
            title: message,
            placement: 'bottom',
            trigger: 'hover',
            template: `
                <div class="tooltip tooltip-invalid" role="tooltip">
                    <div class="arrow"></div>
                    <div class="tooltip-inner"></div>
                </div>
            `,
            container: 'body'
        });
    }
}

if (!window.initializeTooltip) {
    window.initializeTooltip = initializeTooltip;
}

function changeStatusHandler (element, endpoint, message, refresh = false,) {
    const id = element.data('id');
    const status = element.data('status');

    $.ajax({
        url: path_url + endpoint + id,
        type: 'GET',
        success: function (data) {
            if (Number(status) === 1) {
                element.removeClass('on').addClass('off').data('status', 0);
            } else {
                element.removeClass('off').addClass('on').data('status', 1);
            }

            if (refresh) {
                location.reload();
            }
        },
        error: function (xhr, status, error) {
            new swal(
                message ? message : 'Erro ao tentar alterar o status!',
                'Tente novamente, mais tarde.',
                'warning'
            );
        },
    });
}

if (!window.changeStatusHandler) {
    window.changeStatusHandler = changeStatusHandler;
}

/**
 * Função responsável por desabilitar os campos de check in e check out e 
 * ainda sim passar os valores dos mesmo para o formulário
 * 
 * @param {JQuery} input Campo a ser desabilitado e passado para o formulário
 * @returns
 */
function disableWithHidden(element) {
    if (!element || element.length === 0) return;
    
    if (element.is('input')) {
        $(`input[type="hidden"][name="${element.attr('name')}"]`).remove();
    } 

    if (element.is('select')) {
        $(`select[name="${element.attr('name')}"]`).prop('disabled', true);
    }
    
    const hidden = $('<input>', {
        type: 'hidden',
        name: element.attr('name'),
        value: element.val()
    });

    element.after(hidden);
    element.prop('disabled', true);
}

if (!window.disableWithHidden) {
    window.disableWithHidden = disableWithHidden;
}
