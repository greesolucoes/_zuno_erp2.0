function toBase64(str) {
	// first we use encodeURIComponent to get percent-encoded UTF-8,
	// then we convert the percent encodings into raw bytes which
	// can be fed into btoa.
	return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
		function toSolidBytes(match, p1) {
			return String.fromCharCode('0x' + p1);
		}));
}

function allFunctions() {
    $("input[data-mask='decimalCostumize']").fnMascaraNumero();
    $("input[data-mask='moeda']").fnMascaraMoeda();
    $("input[data-mask='numerov2']").fnMascaraNumeroV2();

    $('select.select2').not('.nosearch, .ajax').select2Simple();
    $('select.select2.nosearch').select2Simple(null, true);
    $('select.select2.ajax').select2Ajax();

    $('[data-toggle="tooltip"]').tooltip();
    $("input[data-mask='time']").mask('99:99');
    $("input[data-mask='cnpj']").mask('99.999.999/9999-99');
    $("input[data-mask='cpf']").mask('999.999.999-99');
    $("input[data-mask='insestadual']").mask('999.999.999.999');
    $("input[data-mask='telefone']").mask('(99)9999.9999');
    $("input[data-mask='celular']").mask('(99)99999.9999');
    $("input[data-mask='percent']").maskMoney({thousands: '', decimal: '.', precision: 2, suffix: ' %'});
    $("input[data-mask='percent'][data-mask-precision='3']").maskMoney({
        thousands: '',
        decimal: '.',
        precision: 3,
        suffix: ' %',
        allowZero: true
    });

    $("input[data-mask='data']").each(function (indexInput) {
        var formato = $(this).data('formato');
        if(is_empty(formato, 1)){
            formato = "99/99/9999";
        }

        $(this).mask(formato);
    });
    $("input[data-mask='percent'][data-mask-precision='3'][allow-zero='false']").maskMoney({
        thousands: '',
        decimal: '.',
        precision: 3,
        suffix: ' %'
    });
    $("input[data-mask='decimal']").maskMoney({thousands: '.', decimal: ',', precision: 2});
    $("input[data-mask='negative']").maskMoney({thousands: '.', decimal: ',', precision: 2, allowNegative: true});
    $("input[data-mask='realNegative']").maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 2,
        allowNegative: true,
        prefix: 'R$ '
    });
    $("input[data-mask='real']").maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 2,
        prefix: 'R$ '
    });
    $("input[data-mask='moedaZero']").maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 2,
        allowNegative: true,
        allowZero: true,
        prefix: 'R$ '
    });
    $("input[data-mask='km']").maskMoney({thousands: '.', decimal: '', precision: 0});
    $("input[data-mask='integer']").keypress(function (e) {
        return maskInteger(e);
    });
    $("input[data-mask='upper']").keyup(function (e) {
        $(this).val($(this).val().toUpperCase());
    });

    // $('[data-toggle="popover"]').popover({
    //     trigger: 'click',
    //     html: true,
    //     title: ''
    // });
    // $('[data-toggle="popover-hover"]').popover({
    //     trigger: 'hover',
    //     html: true,
    //     title: ''
    // });
    // $('[data-toggle="popover"]').click(function () {
    //     $('[data-toggle="popover"]').not(this).popover('hide');
    //     hover(this);
    // });
    //
    // $('[data-toggle="popover-text"]').popover({
    //     html: true
    // });
    // $('[data-toggle="popover-text"]').click(function () {
    //     $('[data-toggle="popover-text"]').not(this).popover('hide');
    // });

    configDateTimePicker();

    //TA BUGANDO
    // onSelectCheckBootstrap();
}

function getSaudacao() {
    var hora = new Date().getHours();
    var ret = l['boaNoite'];
    if (hora >= 12 && hora < 18) ret = l['boaTarde'];
    else if (hora >= 0 && hora < 12) ret = l['bomDia'];
    return ret;
}


//TA BUGANDO
function onSelectCheckBootstrap() {
    var label = 'label.form-check-label';
    var input = $('.form-check ' + label + ' input.form-check-input');

    $(input).off('change');
    $(input).on('change', function () {
        $(this).parents(label).removeClass('btn-primary');
        $(this).parents(label).removeClass('btn-success');

        if(this.checked) $(this).parents(label).addClass('btn-success');
        else             $(this).parents(label).addClass('btn-primary');
    });

    $(input).trigger('change');
}

function autoCheck(){
    function addIcon(obj){
        var ret;

        if ($(obj).find('input[type="checkbox"].form-check-input').prop('checked')) {
			ret = `<i class="${isOldLayout ? 'fa fa-check-square' : 'fa-solid fa-square-check me-3'} icone-check"></i>`;
		} else {
			ret = `<i class="${isOldLayout ? 'fa fa-square-o' : 'fa-regular fa-square me-3'} icone-check"></i>`;
		}

        $(obj).find(`${isOldLayout ? 'label i' : 'label svg'}.icone-check`).remove();
        $(obj).find('label').prepend(ret);
    }

    $('input[type="checkbox"].form-check-input:not(.not-check)').change(function () {
        addIcon($(this).parents('div.form-check.form-group.btn-group'));
    });

    $('input[type="checkbox"].form-check-input:not(.not-check)').each(function () {
        addIcon($(this).parents('div.form-check.form-group.btn-group'));
    });
}

/**
 * Obtem um token único para evitar ataques de Cross Site
 * @param callback //O callback será a função que espera receber o token
 * @param async
 */
function getCsrfToken(callback, async = false) {
	const tokenUrl = $('.datas_template').data('url_token');
	let returnData = null;

	$.ajax({
		async: async,
		url: tokenUrl,
		type: 'GET',
		dataType: 'json',
		success: function(data) {
			if (callback) {
				callback(data);
			}
			//Caso tenha um formulário na página, atualiza para o próximo token a ser utilizado
			if ($('.csrf-token').length > 0) {
				updateCsrfTokenForms();
			}

			returnData = data;
		},
		error: function(xhr, status, error) {
			console.error('Erro ao obter token CSRF:', error);
		}
	});

	return returnData;
}

/**
 * Atualiza o input hidden de um formulário com o token CSRF, a cada requisição AJAX
 */
function updateCsrfTokenForms() {
	//Foi necessário adição do timeout por que essa requisição era executada antes do Codeigniter gerar um novo token
	setTimeout(() => {
		const tokenUrl = $('.datas_template').data('url_token');
		$.ajax({
			async: true,
			url: tokenUrl,
			method: 'GET',
			dataType: 'json',
			success: function (data) {
				$('.csrf-token').val(data.value);
			},
			error: function (xhr, status, error) {
				console.error('Erro ao atualizar o token do formulário:', error);
			}
		})
	}, 500)
}

/**
 * Verifica as divs exibir-separador, para exibir um separador (apenas visual)
 */
function exibirSeparadores() {
	const separadores = $('.exibir-separador');
	separadores.each(function () {
		const separador = $(this);
		const divRelacionadaId = separador.data('div_relacionada');
		const divRelacionada = $(`#${divRelacionadaId}`);

		if (divRelacionada.length && divRelacionada.html().trim() !== '') {
			// Busca as divs filhas e verifica se alguma tem a classe 'ocultar'
			const verificaVisibilidade = divRelacionada.children(':not(.ocultar):not(.d-none)').length > 0;

			// Se houver filhos visíveis, remove a classe 'd-none' do separador
			if (verificaVisibilidade) {
				separador.removeClass('d-none');
			} else {
				separador.addClass('d-none');
			}
		}
	});
}

/**
 * Verifica se o conteúdo retornado de uma string é HTML
 * @param str
 * @returns {boolean}
 */
function strIsHTML(str) {
	var doc = new DOMParser().parseFromString(str, 'text/html');
	return Array.from(doc.body.childNodes).some(node => node.nodeType === 1); // Verifica se há tags HTML
}

/**
 * Define o tema caso não esteja definido baseado no padrão do navegador
 * @returns {string}
 */
function verifySystemTheme() {
	// Verifica se o many_minds_theme já está definido no localStorage
	if (!localStorage.getItem('many_minds_theme')) {
		// Se não estiver definido, verifica o tema definido no navegador do usuário
		if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
			localStorage.setItem('many_minds_theme', 'dark');
		} else {
			localStorage.setItem('many_minds_theme', 'light');
		}

		return localStorage.getItem('many_minds_theme');
	}
}

/**
 * Define o tema de preferência do usuário
 */
function setLoggedUserPreferenceTheme() {
	const url = $('.datas_template').data('url_theme');
	const themeDefault = localStorage.getItem('many_minds_theme') ?? verifySystemTheme();

	ajaxRequest(true, url, null, 'text', {
		'defaultTheme': themeDefault
	}, function (ret) {
		try {
			if (!is_empty(ret, true) && !strIsHTML(ret)) {
				localStorage.setItem('many_minds_theme', ret.replace(/['"]+/g, ''));
			}
		} catch(err) {
			console.log(l["erro!"], ": " + l["tempoDeRespostaDoServidorEsgotado!"])
		}
	});
}

