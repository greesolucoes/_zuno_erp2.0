var ultimoAjaxModalByBtn = null;

/**
 * Function carregaModalByBtn.
 * Carrega o modal especifico
 * @param objBtn   => object
 * @param objModal => string
 */
function carregaModalByBtn(objBtn, objModal){
    var urlAjax = $(objBtn).data('url');

    if(!is_empty(ultimoAjaxModalByBtn)) ultimoAjaxModalByBtn.abort();
    ultimoAjaxModalByBtn = null;

    var dataTitulo = $(objBtn).data('titulo');
    if(is_empty(dataTitulo, 1)) dataTitulo = "";
    $(objModal + ' .modal-content .modal-title').html(dataTitulo);

    toggleLoadingOnDivSmall(objModal + " .modal-content .modal-body", true);
    $(objModal).modal('show');

    ultimoAjaxModalByBtn = ajaxRequest(true, urlAjax, null, 'text', null, function(ret){
        $(objModal + " .modal-content .modal-body").html(ret);
    });
}

/**
 * Function criaTodosSelectsAjax
 * Realiza a criação de todos os selects por ajax da pagina
 *
 * @param obj Especifica a linha para criar os selects
 */
function criaTodosSelectsAjax(obj){
	if(is_empty(obj, 1)) {
		obj = $("select.select_ajax_FI");
	} else {
		obj = $($(obj).find("select.select_ajax_FI"));
	}

	$(obj).select2Ajax();
}

/**
 * Function criaTodosSelects
 * Realiza a criação de todos os selects da pagina
 */
function criaTodosSelects(){
    $('.select_FI').select2Simple();
}

/**
 * Function destroyTodosInitsSelects.
 * Destrói todos os inits de selects da pagina
 */
function destroyTodosInitsSelects(){
    $("select.select_ajax_FI").data('init', '');
}

/**
 * Function controlaTabelaSuite
 * Controla as configs de tabelas
 * @param opts Array: Opções para criar as funções?
 *                    'ref' => referencia de uma tabela especifica para as funções
 *                    'funAposAddItem' => Função para ser executada após add um item na tabela
 *                    'funAposRemoverItem' => Função para ser executada após remover um item da tabela
 *                    'funDepois' => Função para ser executada ao fim de tudo
 */
function controlaTabelaSuite(opts) {
    criaTodosSelects();
    criaTodosSelectsAjax();
    destroyTodosInitsSelects();

    if(is_empty(opts, 1)){
        opts =
            {
                'ref' : '',
                'funAposAddItem' : function () {

                },
                'funAposRemoverItem' : function () {

                },
                'funDepois' : function () {

                }
            };
    }
    if(is_empty(opts['ref'], 1)) opts['ref'] = "";
    $('table' + opts['ref'] + ' button.remove-itens-table-geral:not(.ajax-delete)').off('click:no_effect');
    $('table' + opts['ref'] + ' button.remove-itens-table-geral:not(.ajax-delete)').on("click:no_effect", function () {
		$($(this).parents('tr')).remove();
		if (!is_empty(opts['funAposRemoverItem'], 1)) {
			opts['funAposRemoverItem']();
		}
    });
    $('table' + opts['ref'] + ' button.remove-itens-table-geral:not(.ajax-delete)').off('click');
    $('table' + opts['ref'] + ' button.remove-itens-table-geral:not(.ajax-delete)').on("click", function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
			if (!is_empty(opts['funAposRemoverItem'], 1)) {
				opts['funAposRemoverItem']();
			}
		});
    });

    $('table' + opts['ref'] + ' tfoot button.add-itens-table-geral').off("click");
    $('table' + opts['ref'] + ' tfoot button.add-itens-table-geral').on("click", function () {
        var tbody  = $($(this).parents('table').find('tbody'));
        var modelo = $(tbody).find('tr').first().html();

        $(tbody).append('<tr>' + modelo + '</tr>');
        $($(tbody).find('tr').last()).find('input.is_fake-no_post').val("0");
        if (!is_empty(opts['funAposAddItem'], 1)) opts['funAposAddItem']();

        allFunctions();
		criaTodosSelects();
        criaTodosSelectsAjax($($(tbody).find('tr').last()));
        controlaTabelaSuite(opts);
    });

    if (!is_empty(opts['funDepois'], 1)) opts['funDepois']();
}

function consoleCustom(text, msgType) {
    if(is_empty(text, 1))  return false;
    if(is_empty(msgType, 1)) msgType = 'color: black;';

    switch (msgType) {
        case "success":
            msgType = "color: Green;";
            break;
        case "info":
            msgType = "color: DodgerBlue;";
            break;
        case "error":
            msgType = "color: Red;";
            break;
        case "warning":
            msgType = "color: Orange;";
            break;
    }

    console.log("%c" + text, msgType);
}

function consoleSystem(text, msgType, cssCustom){
    switch (msgType) {
        case "info":
            if(is_empty(cssCustom, 1)) console.info(text);
            else                       console.info("%c" + text, cssCustom);
            break;
        case "error":
            if(is_empty(cssCustom, 1)) console.error(text);
            else                       console.error("%c" + text, cssCustom);
            break;
        case "warning":
            if(is_empty(cssCustom, 1)) console.warn(text);
            else                       console.warn("%c" + text, cssCustom);
            break;
        case "debug":
            if(is_empty(cssCustom, 1)) console.debug(text);
            else                       console.debug("%c" + text, cssCustom);
            break;
        default:
            if(is_empty(cssCustom, 1)) console.log(text);
            else                       console.log("%c" + text, cssCustom);
            break;
    }
}

function is_empty(str, zeroIsEmpty) {
    if(
    	zeroIsEmpty === null || zeroIsEmpty === '' || zeroIsEmpty === undefined ||
		zeroIsEmpty === 0 || zeroIsEmpty === '0' || zeroIsEmpty === false ||
		zeroIsEmpty === 'false'
	) {
    	zeroIsEmpty = false;
	} else {
    	zeroIsEmpty = true;
	}

    if(
    	str === null ||
    	str === undefined ||
    	str === false ||
		(Array.isArray(str) && str.length <= 0)
	) {
    	return true;
	}
    try {
    	if(
			(!Array.isArray(str) && str.toString().trim() === "") ||
    		str.toString().trim().toLowerCase() === "false" ||
    		str.toString().trim().toLowerCase() === "undefined"
		) {
    		return true;
		}
	} catch (e) {}
    if(zeroIsEmpty) {
		try {
			if(str.toString() === "0" || str.toString() === ".0" || str.toString() === "0.0" || str.toString() === "0.") {
				return true;
			}
		} catch (e) {}
	}
}

function is_empty_numeric(str) {
    return isNaN(str) || is_empty(str, 1);
}

function retDiasDiff(date1_ms, date2_ms) {
    if (date1_ms > date2_ms) return null;

    // The number of milliseconds in one day
    var ONE_DAY = 1000 * 60 * 60 * 24;
    // Calculate the difference in milliseconds
    var difference_ms = Math.abs(date1_ms - date2_ms);
    var retDay = Math.round(difference_ms / ONE_DAY);
    // Convert back to days and return
    return retDaysNoWeekends(date1_ms, retDay, retDay);
}

function retDaysNoWeekends(data_ms, dias, result) {
    var data = new Date(data_ms);
    if (dias <= 0) return result;

    // adiciona um dia
    data.setDate(data.getDate() + 1);

    var isFimDeSemana = ([0, 6].indexOf(data.getDay()) != -1);

    //Se for util remove um result
    if (isFimDeSemana) result--;

    dias--;
    return retDaysNoWeekends(data, dias, result);
}

function diffDays(fromDate, toDate){
	var dt1 = new Date(fromDate);
	var dt2 = new Date(toDate);

	if(fromDate > toDate)
		return false;

	var time_difference = dt2.getTime() - dt1.getTime();
	return (time_difference / (1000 * 60 * 60 * 24) + 1);
}

function formatDate(date) {
	var dia  = date.split("/")[0];
	var mes  = date.split("/")[1];
	var ano  = date.split("/")[2];

	return ano + '-' + ("0"+mes).slice(-2) + '-' + ("0"+dia).slice(-2);
}

function formataCasasDecimais(num, casas) {
    if(is_empty_numeric(num))   num = 0;
    if(is_empty_numeric(casas)) casas = 0;

    return num.toFixed(casas);
}

function funcReplaceNumber(numero, separador, resultZero) {
    if (numero === 0) {
        if(!resultZero) return 0;
        else  return "0,00";
    }
    var n = numero.toString().split(".");
    if(separador) n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    if(resultZero) {
        if (!n[1]) {
            n[1] = '00';
        } else if (n[1] < 10) {
            n[1] = n[1].toString() + '0';
        }
        n[1] = n[1].toString().substring(0, 2);
    } else if(n[1] == '00') {
        return n[0];
    }

    return n.join(",");
}

function replaceNumber(numero) {
    return funcReplaceNumber(numero, true, true);
}

function valueToFloat(numero) {
    if (numero == undefined || numero == null || numero === 0) return 0;

    numero = numero.replace(/[\ R$~%.]/g, '');
    var n = numero.toString().split(",");
    numero = n.join("#");
    n = numero.toString().split(".");
    numero = n.join("");
    n = numero.toString().split("#");
    numero = n.join(".");

    return parseFloat(numero);
}

function toPercent(i, separador, casasPorcentagem){
    i = formataCasasDecimais(i, casasPorcentagem);
    return funcReplaceNumber(i, separador, false) + '%';
}

function intVal(i) {
    return typeof i === 'string' ? (i.replace(/[\ R$~%.]/g, '')).replace(/[\,]/g, '.') * 1 : typeof i === 'number' ? i : 0;
}

/**
 * Converte string para float à partir da quantidade de casas decimais
 * Remove: " R$~%,."
 */
function toFloat(i, decimal, locationCode = 'BR') {
    if(typeof i === 'undefined') return 0;
    if(typeof i === 'number') return parseFloat(i.toFixed(decimal));

    i = i.trim();
    i = i.replace(' ', '');
    i = i.replace(/\s/g, '');
    i = i.replace('R$', '');
    i = i.replace('$', '');
    i = i.replace('~', '');
    i = i.replace('%', '');
    i = i.replace(locationCode === 'BR' ? '.' : ',', '');
    i = i.replace(locationCode === 'BR' ? ',' : '.', '.');
    if(is_empty(i, 1)) i = '0';

    try{
        i = parseFloat(parseFloat(i).toFixed(decimal));
    }catch (err) {
        i = 0;
    }

    return i;
}
// function toFloat(i, decimal){
//     return parseFloat(typeof i === 'string' ? ((i.replace(/[\ R$~%,.]/g, '')) / Math.pow(10, decimal)).toFixed(decimal) : typeof i === 'number' ? i : 0);
// }

function formataFloat(num, sep_decimal, decimals){
    if(is_empty(num, 1)) return 0;
    if(is_empty(decimals, 1)) decimals = 6;

    var ret;
    var arrNum = num.toString().split(sep_decimal);

    arrNum[0] = arrNum[0].toString().replace(/\D/g,'');
    if(is_empty(arrNum[0], 1)) arrNum[0] = "0";

    if(arrNum.length > 1){
        arrNum[1] = arrNum[1].toString().replace(/\D/g,'');
        if(is_empty(arrNum[1], 1)) arrNum[1] = "0";
    }else{
        arrNum[1] = "0";
    }

    ret = (arrNum[0].toString() + '.' + arrNum[1].toString());
    try{
        ret = parseFloat(parseFloat(ret).toFixed(decimals).toString());
    }catch (err) {
        ret = 0;
    }

    return ret;
}

function float2real(valor, cifrao, casas) {
    if(casas === undefined || casas === '' || casas === null) casas = 2;
    if(is_empty_numeric(valor)) valor = 0;

    valor = parseFloat(valor).toFixed(casas).toString();
    valor = parseFloat(valor).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: casas
    });
    valor = valor.trim();
    valor = valor.replace('R$', '');
    valor = valor.replace(' ', '');
    valor = valor.replace(/\s/g, '');

    if(cifrao) valor = "R$ " + valor;
    return valor;
}

function maskInteger(e) {
    var tecla = (window.e) ? e.keyCode : e.which;
    if ((tecla > 47 && tecla < 58))
        return true;
    else {
        if (tecla !== 8)
            return false;
        else
            return true;
    }
}

function dataBRToDiaSemana(data) {
    if (data != null && data != '') {
        data = data.split("/");
        data = new Date(data[2], data[1] - 1, data[0]);

        var dia = data.getDay();
        var semana = new Array(6);
        semana[0] = l["domingo"];
        semana[1] = l["segundaFeira"];
        semana[2] = l["terçaFeira"];
        semana[3] = l["quartaFeira"];
        semana[4] = l["quintaFeira"];
        semana[5] = l["sextaFeira"];
        semana[6] = l["sábado"];

        return semana[dia];
    } else {
        return data;
    }
}

function agoraDataBR() {
    var data = new Date(),
        dia = pad(data.getDate(), 2),
        mes = pad(data.getMonth() + 1, 2),
        ano = data.getFullYear();
    return [dia, mes, ano].join('/');
}

function pad(str, max, type) {
	if(is_empty(type, 1)) {
		type = "L";
	}
	type = type.toUpperCase();
    str = str.toString();
    return str.length < max ? pad((type === "L" ? ("0" + str) : (str + "0")), max, type) : str;
}

function mesAnoToAnoMes(data){
    if(data == ''){
        return '';
    }
    data = data.split('/');
    return data[1] + '-' + data[0];
}

function dateBrToDate(data){
    if(is_empty(data, 1)){
        return '';
    }
    data = data.split('/');
    return data[2] + '-' + data[1] + '-' + data[0];
}

/**
 * Function arrayMenosArray.
 * Tem por função remover todos os itens que estão no arrayB no arrayA
 * @param arrayA Array que será refeito, retirando os itens de B
 * @param arrayB Array que define oque será tirado do A
 * @returns {*} Novo array
 */
function arrayMenosArray(arrayA, arrayB) {
    arrayA = arrayA.filter(function (item) {
        return arrayB.indexOf(item) === -1;
    });

    return arrayA;
}

/**
 * Function stringParaFloat.
 * Converte qualquer string para float
 * @param valor String de valor para ser convertido para float
 * @param separadorDecimal String de separador decimal do float
 * @param permitirNegativo Int que define se permite numeros negativos ou não
 */
function stringParaFloat(valor, separadorDecimal, permitirNegativo) {
    if(separadorDecimal === undefined) separadorDecimal = ',';
    if(permitirNegativo === undefined) permitirNegativo = true;
    if(
        is_empty(separadorDecimal, 1) || (
            separadorDecimal !== ',' &&
            separadorDecimal !== '.' &&
            separadorDecimal !== "," &&
            separadorDecimal !== "."
        )
    ) separadorDecimal = ',';
    separadorDecimal = separadorDecimal.toString();
    permitirNegativo = !is_empty(permitirNegativo, 1);
    if(is_empty(valor, 1)) return 0.0;

    valor = valor.toString().replace(/[^e0-9,.\-]/g, '');
    var isNegativo = valor.charAt(0) === '-' && permitirNegativo;
    var valorAux = null;
    if(hasString(valor, "e")) {
    	valor = pad(valor.split("e")[0].replace(/[^0-9]/g, ''), valor.split("e")[1].replace(/[^0-9]/g, ''));
    	valor = (isNegativo ? "-" : "") + "0" + separadorDecimal + valor
	}

    if(!valor.includes(separadorDecimal)){
        valor = valor.replace(/[^0-9]/g, '').trim();
    }else{
        valorAux = valor.split(separadorDecimal);
        valorAux[0] = valorAux[0].toString().replace(/[^0-9]/g, '').trim();
        valorAux[1] = valorAux[1].toString().replace(/[^0-9]/g, '').trim();

        valor = valorAux[0] + '.' + valorAux[1];
    }
    if(isNegativo) valor = '-' + valor;

    isNegativo = null;
    permitirNegativo = null;
    separadorDecimal = null;
    valorAux = null;

    return !is_empty(valor, 1) ? parseFloat(valor) : 0.0;
}

/**
 * Function number_format
 * Formatação de numero equivalente ao number_format do PHP
 * @param number Numero para ser formatado
 * @param decimals Casas decimais maximas
 * @param dec_point Separador decimal que deve ser alocado no retorno
 * @param thousands_sep Separador de milhar que deve ser alocado no retorno
 * @param prefixo Prefixo de retorno
 * @return {string}
 */
function number_format (number, decimals, dec_point, thousands_sep, prefixo) {
    // Strip all characters but numerical ones.
    if(is_empty(prefixo, 1)) prefixo = '';
    else                                prefixo += ' ';

    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return prefixo + s.join(dec);
}

/**
 * Function formataDecimal.
 * Função para formatar um numero em JS
 * @param decimal Numero ou string numerica para ser formatada
 * @param separadorDecimalDe Separador decimal do presente decimal
 * @param separadorDecimalPara Separador decimal que deve ser retornado no numero
 * @param separadorMilharPara Separador de milhar que deve ser retornado no numero
 * @param cifrao Cifrão para ser retornado no numero
 * @param cifraoIsPrefixo Define se o cifrão será prefixo ou sufixo
 * @param casasDecimais Numero de casas decimais que o numero deve ter
 * @return {*}
 */
function formataDecimal(decimal, separadorDecimalDe, separadorDecimalPara, separadorMilharPara, cifrao, cifraoIsPrefixo, casasDecimais) {
    decimal = stringParaFloat(decimal, separadorDecimalDe, true);
	decimal = decimal.toString();

    if(
        is_empty(separadorDecimalPara, 1) || (
            separadorDecimalPara !== ',' &&
            separadorDecimalPara !== '.' &&
            separadorDecimalPara !== "," &&
            separadorDecimalPara !== "."
        )
    ) separadorDecimalPara = ',';
    separadorDecimalPara = separadorDecimalPara.toString();
    if(is_empty(separadorMilharPara, 1)) separadorMilharPara = '';
    separadorMilharPara = separadorMilharPara.toString();
    if(is_empty(cifrao, 1)) cifrao = '';
    cifrao = cifrao.toString();
    if(is_empty(casasDecimais, 1) && casasDecimais !== null) {
    	casasDecimais = 0;
	}

    if(
        is_empty(decimal, 1) ||
        separadorMilharPara === separadorDecimalPara
    ) {
        decimal = "0";

        if(!is_empty(cifrao, 1)){
            if(!is_empty(cifraoIsPrefixo, 1)){
                decimal = cifrao + " " + decimal;
            }else{
                decimal = decimal + " " + cifrao;
            }
        }

        return decimal;
    }

	if(hasString(decimal, "e")) {
		let eCasas = decimal.split("e")[1].replace(/[^0-9]/g, '');
		let eVal   = decimal.split("e")[0].replace(/[^0-9]/g, '');
		if(is_empty(casasDecimais, 1)) {
			casasDecimais = eCasas;
		}

		decimal = pad(pad(eVal, eCasas, "L").substring(0, casasDecimais), casasDecimais, "R");
		decimal = (decimal.charAt(0) === '-' ? "-" : "") + "0" + separadorDecimalPara + decimal
		if(is_empty(stringParaFloat(decimal, separadorDecimalPara, true), 1)) {
			decimal = "0";
		}
		if(!is_empty(cifrao, 1)){
			if(!is_empty(cifraoIsPrefixo, 1)){
				decimal = cifrao + " " + decimal;
			}else{
				decimal = decimal + " " + cifrao;
			}
		}

		return decimal;
	}
	if(is_empty(casasDecimais, 1)) {
		casasDecimais = 2;
		if(hasString(decimal, ".")) {
			casasDecimais = decimal.split(".")[1].toString().length;
		}
	}

    decimal = number_format(decimal, casasDecimais, separadorDecimalPara, separadorMilharPara);
    if(!is_empty(cifrao, 1)){
        if(!is_empty(cifraoIsPrefixo, 1)){
            decimal = cifrao + " " + decimal;
        }else{
            decimal = decimal + " " + cifrao;
        }
    }

    return decimal;
}

/**
 * Function download_file
 * Cria dinamicamente um arquivo e faz download do mesmo
 * @param name Nome do arquivo
 * @param contents Conteudo do arquivo
 * @param mime_type Tipo do arquivo
 */
function download_file(name, contents, mime_type) {
    mime_type = mime_type || "text/plain";
    // mime_type += ";charset=UTF-8";
    // mime_type += ";charset=ISO-8859-1";

    var blob = new Blob(["\ufeff", contents], {
        // encoding: "UTF-8",
        // encoding: "ISO-8859-1",
        type: mime_type
    });

    var dlink = document.createElement('a');
    dlink.download = name;
    dlink.href = window.URL.createObjectURL(blob);
    dlink.onclick = function(e) {
        // revokeObjectURL needs a delay to work properly
        var that = this;
        setTimeout(function() {
            window.URL.revokeObjectURL(that.href);
        }, 1500);
    };

    dlink.click();
    dlink.remove();
}

function recriar(obj){
	obj.each(function () {
		$(this).after($(this).clone());
	});
	obj.remove();
}

function contaCaracteres(__max_chars, __id_field, __el) {
	if(is_empty(__max_chars, 1)) __max_chars = 254;
	if(is_empty(__id_field, 1)) __id_field = "observacoes";
	if(is_empty(__el, 1)) __el = "textarea";

	let __id_count = "count_chars-" + __id_field;
	let __field = __el + '#' + __id_field;

	__id_field = null;
	__el = null;

	$(__field).after( "<p id='" + __id_count + "' class='count_chars text-muted float-right'></p>" );

	let char = 0;
	let len = 0;
	$(__field).keyup(function () {
		len = $(this).val().length;
		if (len > __max_chars) {
			char = 0;
			$(this).val($(this).val().substring(0, __max_chars));
		} else {
			char = __max_chars - len;
		}
		$(('p#' + __id_count)).text(char + ' ' + l["caracteresRestantes"]);
	});
	char = __max_chars - $(__field).val().length;
	$(('p#' + __id_count)).text(char + ' ' + l["caracteresRestantes"]);
}

function encodeHTMLEntities(text) {
	return $("<textarea/>")
	.text(text)
	.html();
}

function decodeHTMLEntities(text) {
	return $("<textarea/>")
	.html(text)
	.text();
}

function hasString(str, find) {
	return str.toString().indexOf(find) !== -1;
}

function isArray(what) {
	return Object.prototype.toString.call(what) === '[object Array]';
}

function formToStringJson(form, zero_is_empty = 1) {
	$($(form).find('select.select2-hidden-accessible')).each(function () {
		if(!is_empty($(this).val(), 1)) {
			return;
		}

		$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change');
	});

	let data = $(form).serializeArray(); //deve ser antes do disabled
	let ret = {};
	let auxNome = "";
	let auxArr = "";
	for (let i = 0; i < data.length; i++) {
		auxNome = data[i].name;
		auxNome = auxNome.replace(/\[.*\]/, '');
		if (hasString(data[i].name, '[')) {
			if(is_empty(ret[auxNome], zero_is_empty)) {
				ret[auxNome] = [];
			}
			ret[auxNome][ret[auxNome].length] = data[i].value;
		} else if (ret.hasOwnProperty(auxNome)) {
			if (!isArray(ret[auxNome])) {
				auxArr = ret[auxNome];
				ret[auxNome] = [];
				ret[auxNome][ret[auxNome].length] = auxArr;
				auxArr = null;
			}
			ret[auxNome][ret[auxNome].length] = data[i].value;
		} else {
			ret[auxNome] = data[i].value;
		}
		auxNome = null;
	}
	auxNome = null;
	auxArr = null;
	data = JSON.stringify(ret);
	ret = null;
	return data
}

function consoleProduction(imprimir) {
	// console.log(imprimir);
}

function strFormatDate(date, padraoDe, padraoPara) {
	if(is_empty(date, 1)) {
		return null;
	}
	if(is_empty(padraoDe, 1)) {
		padraoDe = "DD/MM/YYYY";
	}
	if(is_empty(padraoPara, 1)) {
		padraoPara = "YYYY-MM-DD";
	}
	if(padraoDe === padraoPara) {
		return date;
	}

	date = moment(date, padraoDe).format(padraoPara);
	if(is_empty(date, 1)) {
		return null;
	}

	return date;
}

/**
 * FUNCOES PARA DOWNLOAD DE DOCUMENTOS: PDF ou EXCEL
 *
 * function: prepararHTMLParaImpressao
 * 	- CRIAR O HTML COM O LAYOUT
 *
 * function: imprimirConteudoPorTipo
 * 	- IMPRIME O CONTEÚDO NO FORMATO SOLICITADO
 *
 */

/**
 * funcao para preparar o HTML de forma genérica
 * @param idAreaImpressao = Ex.: '#print-area-itens_nota'  ou  '#print-area-pagamentos'
 * @param idAreaImpressaoDestino = Ex.: '#print-resumo'    ou  '#area-impressao' (OPCIONAL)
 */
function prepararHTMLParaImpressao(idAreaImpressao, idAreaImpressaoDestino){
	const cabecalho = idAreaImpressao + ' .cabecalho-print';
	const conteudo 		= idAreaImpressao + ' .conteudo-print';
	const rodape 	= idAreaImpressao + ' .rodape-print';

	let htmlPrint = "";
	htmlPrint += $(cabecalho).html();
	htmlPrint += '<hr />';
	htmlPrint += $(conteudo).html();
	htmlPrint += '<hr />';
	htmlPrint += $(rodape).html();
	htmlPrint += '<hr />';
	// div vazia para impressao
	if(is_empty(idAreaImpressaoDestino)){
		return htmlPrint;
	}else{
		$(idAreaImpressaoDestino).html(htmlPrint);
	}
	htmlPrint = null;
}

/**
 * Funcao para gerar impressao de PDF, EXCEL ou outro tipo
 * @param buttonObj = DOM element object com os seguintes parametros data-:
 * data-tipo = 'PDF', 'EXCEL'
 * data-nome_arquivo = lista_de_itens
 * data-id_area_impressao = id da DIV que contem o conteudo a ser impresso no documento
 * data-id_area_impressao_destino = id da DIV que receberá os dados montados (OPCIONAL, caso vazio, criará automaticamente)
 */
function imprimirConteudoParaArquivo(buttonObj){

	// parametros do objeto
	const tipo = buttonObj.data('tipo');
	const nome_arquivo = buttonObj.data('nome_arquivo');
	const idAreaImpressao = buttonObj.data('id_area_impressao');
	const idAreaImpressaoDestino = buttonObj.data('id_area_impressao_destino');
	const printDelay = 1000; // pode ser passado por parâmetro se necessitar depois

	if(	   is_empty(buttonObj, true)
		|| is_empty(tipo, true)
		|| is_empty(idAreaImpressao, true)
	){
		swal({
			title: l["atenção!"],
			text: l["nenhumRegistroEncontrado"],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		});
	}else{

		// pelo id do elemento pai do HTML de impressao: #print-area-itens_nota
		// prepara o conteúdo a ser impresso
		let conteudo = null;
		if(is_empty(idAreaImpressaoDestino, true)){
			conteudo = prepararHTMLParaImpressao(idAreaImpressao, null);
		}else{
			prepararHTMLParaImpressao(idAreaImpressao, idAreaImpressaoDestino);
		}
		// desabilita o botao
		buttonObj.prop("disabled", true);
		buttonObj.removeClass('fa-print');
		// load spinner
		buttonObj.html('<i class="fa fa-spinner fa-pulse fa-fw me-3"></i> ' + l["carregando"]);

		// checa o tipo de impressao
		// excel
		if(tipo.toLowerCase() == 'excel'){
			download_file(
				nome_arquivo + ".xls",
				(conteudo || $(idAreaImpressaoDestino).html()),
				'application/vnd.ms-excel'
			);

		// pdf
		}else if(tipo.toLowerCase() == 'pdf'){

			if(!is_empty(conteudo, true)){
				// cria a div de destino de forma dinâmica
				const divTempId = idAreaImpressao + '_temp';
				const divTemp = $('<div />').appendTo('body');
				divTemp.attr('id',divTempId.replace("#",""));
				divTemp.addClass('d-none d-print-block');
				divTemp.html(conteudo);
				$(divTempId).printThis({
               		printDelay: printDelay,
					pageTitle: l["itens"],
					footer: 'ManyMinds',
					afterPrint: setTimeout(
						 function() { $(divTempId).remove(); }
						, (printDelay + 5000))
				});
			}else{
				// usa uma div de destino previamente definida no HTML
				$(idAreaImpressaoDestino).printThis({
	               	printDelay: printDelay,
					pageTitle: l["itens"],
					footer: 'ManyMinds'
				});
			}
		} else if (tipo.toLowerCase() == 'pdf_relatorio') {
			// Inicializa o array de dados da tabela
			let tableData = [];
			const rows = $(idAreaImpressao).find('tr'); // Pega todas as linhas da tabela na área de impressão

			// Monta o cabeçalho da tabela
			const header = [];
			$(rows[0]).find('th').each(function() {
				header.push($(this).text().trim());
			});

			tableData.push(header);

			rows.slice(1).each(function() {
				const rowData = [];
				$(this).find('td').each(function() {
					rowData.push($(this).text().trim() || '');
				});

				while (rowData.length < header.length) {
					rowData.push('');
				}
				tableData.push(rowData);
			});

			const baseFontSize = 10;
			const minFontSize = 5;
			const fontSize = Math.max(minFontSize, baseFontSize - Math.floor(header.length / 5));

			// defino larguras proporcionais para as colunas
			const columnWidths = header.length > 6 ? Array(header.length).fill('auto') : Array(header.length).fill('*');

			const docDefinition = {
				pageOrientation: header.length > 4 ? 'landscape' : 'portrait',
				content: [
					{ text: nome_arquivo, style: 'header' },
					{
						table: {
							headerRows: 1,
							widths: columnWidths,
							body: tableData.map(row => row.map(cell => ({ text: cell, fontSize: fontSize })))
						},
						layout: {
							fillColor: function (rowIndex, node, columnIndex) {
								return rowIndex === 0 ? '#eeeeee' : null;
							},
							hLineWidth: function (i, node) {
								return 0.5; // Reduz a espessura da linha
							},
							hLineColor: function (i, node) {
								return '#cccccc'; // Define uma cor mais suave
							}
						}
					}
				],
				footer: function (currentPage, pageCount) {
					return {
						text: l["página"] + ` ${currentPage} ` + l["de"] + ` ${pageCount}`,
						alignment: 'center',
						fontSize: 10
					};
				},
				styles: {
					header: {
						fontSize: 18,
						bold: true,
						alignment: 'center',
						marginBottom: 10
					},
					table: {
						margin: [0, 5, 0, 15]
					},
					footer: {
						fontSize: 10,
						italics: true,
						alignment: 'center'
					}
				}
			};

			// Gerar o PDF com o nome de arquivo e download
			pdfMake.createPdf(docDefinition).download(nome_arquivo + '.pdf');
		}

		// reativa o botao após ser clicado
		setTimeout(function(){
			if (isOldLayout) {
				$(buttonObj).addClass('fa-print')
				$(buttonObj).html($(buttonObj).data('imprimir_novamente'));
			} else {
				const dontDisplayIcon = $(buttonObj).hasClass('not-display-icon');
				const iconType = (
					$(buttonObj).data('tipo') === 'pdf' || $(buttonObj).data('tipo') === 'pdf_relatorio'
						? 'file-type-pdf2'
						: (
							($(buttonObj).data('tipo') === 'excel')
								? 'file-type-excel'
								: 'default-file'
						)
				);

				$(buttonObj).html(
					(!dontDisplayIcon ? `<span data-icon="vscode-icons:${iconType}" class="fs-8 icon-blue2 iconify" style="${iconType !== 'default-file' ? 'transform: translateX(-1rem);' : ''}"></span>` : '') +
					`<span class="${!dontDisplayIcon ? 'mt-3' : ''} fw-bold text-center txt-blue2">${$(buttonObj).data('imprimir_novamente')}</span>`
				);
			}
			buttonObj.prop("disabled", false);
		}, 1000);

	}
}
// FIM DAS FUNCOES DE DOWNLOAD DE DOCUMENTOS PDF, EXCEL ...
// FIM DAS FUNCOES DE DOWNLOAD DE DOCUMENTOS PDF, EXCEL ...

/**
 * Função getTypeDevice que retorna o tipo de dispositivo
 * @returns {string}  (tablet | mobile | desktop)
 */
function getTypeDevice(){
	const ua = navigator.userAgent;
	if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
		return "tablet";
	}
	else if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
		return "mobile";
	}
	return "desktop";
}

/**
 * Função <b>flagModalHandler</b> <br>
 * Implementa o modal de informações de flags do sistema.
 * Pra utilizar é necessário uma div com a classe "explicacao_flags" após o
 * row de mensagens padrão e um elemento "button" com a classe "ico-info-flag"
 * e com os atributos "data-explicacao" e "data-nome_flag".
 *
 * @param generateButtons boolean 	Gera botões em labels para casos como na Conciliação.
 * 									Deve-se seguir o padrão de classes '.form-check.btn-group:not(.no-info-flags)'
 * @param isDev		  	  boolean
 */
function flagModalHandler(generateButtons = false, isDev = false) {
	if (!isDev) {
		// $('.ico-info-flag').each(function () { $(this).remove(); })
		// return;
	}

	if ($('.explicacao_flags').length == 0) {
		console.warn(`
			---------------------------------------- ATENÇÃO! ----------------------------------------
			A 'div.explicacao_flags' precisa ser criada. De preferência, após o '.row .container-msg'!
			Não esquecer de chamar o método flagModalHandler no final do arquivo 'flagModalHandler(false, isDev);'
			------------------------------------------------------------------------------------------
		`);

		return;
	}

	// region Variáveis
	// tempo padrão para fechamento do modal (segundos * 10)
	const TIMER_DISMISS = 5 * 10;

	// Troca a funcionalidade para funcionar com 'click' ao invés de 'mouseover'
	const CLICK_TO_OPEN = true;

	// botão para disparo das funções de exibição dos modais
	const botaoInfoFlagClass = '.ico-info-flag';

	// id do modal a ser criado ao passar o mouse sobre o botão de disparo
	const idExplicacaoFlagModal = "#explicacao-flag-modal";

	// variável de controle para evitar duplicidade de exibição
	let nomeFlagAnterior = '';

	// variável de controle de tempo de exibição
	let contadorDismiss = 0;

	// variável de controle das pausas do contador
	let pausaContador = false;
	// endregion Variáveis


	// region Funções auxiliares
	let __generateButtons = function () {
		$('.form-check.btn-group:not(.no-info-flags)').each(function () {
			let label = $(this).find('label')[0];
			let button = `<button type="button" class="btn btn-outline m-0 p-2 ico-info-flag"
					data-explicacao='${$(label).data('explicacao')}'
					data-nome_flag='${label.innerText}'
					${($(label).data('explicacao') == '' || $(label).data('explicacao') == undefined) ? "style='opacity: 0; pointer-events: none;'" : ''}
				>
					<i class="fa ${isOldLayout ? 'fa-info-circle' : 'fa-info'}"></i>
				</button>`;

			$(this).append(button);
		});
	}

	let __openModal = function () {
		let onType = CLICK_TO_OPEN
			? 'click'
			: 'mouseover';

		let styleFlagComplementar = CLICK_TO_OPEN
			? 'style="background-color:#ca7035"'
			: '';

		$(botaoInfoFlagClass).on(onType, function(){
			// o contador deve ser pausado
			pausaContador = true;

			// se não tiver valor no contador, seta para o padrão
			if (contadorDismiss <= 0) {
				contadorDismiss = TIMER_DISMISS;
			}

			// tratativa para o nome da flag
			let nomeFlag = $(this).data('nome_flag')
				? $(this).data('nome_flag')
				: '<em>Nome da flag</em>';

			// tratativa para a explicação da flag
			let explicacaoFlag = $(this).data('explicacao');

			// montagem do modal que vai ser exibido com nome e explicação da flag
			let flagModal = `<div id='explicacao-flag-modal' class='explicacao-flag-modal-hide'>
					<button id='btn-close-explicacao-flag-modal'>&times;</button>
	
					<h3>${nomeFlag}</h3>
					<p>${explicacaoFlag}</p>
	
					<div id="explicacao-flag-modal-timer-path"><div>
					<div id="explicacao-flag-modal-timer" ${isOldLayout ? styleFlagComplementar : ''}><div>
				</div>`;

			// se o nome da flag anteriormente selecionada for diferente
			// da flag atual, segue com o "if"
			if (nomeFlagAnterior !== nomeFlag) {
				// reseta o contador
				contadorDismiss = TIMER_DISMISS;

				// fecha o modal anterior
				__closeExplicacaoFlagModal();

				// monta e exibe o novo modal
				$('.explicacao_flags').append(flagModal);

				// atribui a flag atual ao nome da flag anterior, para que
				// possamos validar a duplicância de modais abertos
				nomeFlagAnterior = nomeFlag;

				// prepara a função para quando o botão "fechar" do modal for clicado
				__btnCloseExplicacaoFlagModal();
			}

			// torna visível o modal de explicação
			setTimeout(function () {
				$(idExplicacaoFlagModal).addClass('explicacao-flag-modal-unhide');
			}, 50);
		});
	}

	// ao manter o mouse sobre o botão, pausa o contador de remoção do modal
	let __holdModal = function () {
		$(botaoInfoFlagClass).on('mouseover', function() {
			pausaContador = true;
		});
	}

	// ao retirar o mouse do botão, retorna a contagem do contador de remoção do modal
	let __out = function() {
		$(botaoInfoFlagClass).on('mouseout', function() {
			pausaContador = false;
		});
	}

	// ao tirar o mouse, se não tiver mais tempo da contagem, esconde o modal e zera
	// o nome da flag anterior para que a flag fechada possa ser aberta novamente
	let __leaveInfo = function() {
		$(botaoInfoFlagClass).on('mouseleave', function(){
			setInterval(function () {
				if(contadorDismiss <= 0) {
					setTimeout(function () {
						$(idExplicacaoFlagModal).removeClass('explicacao-flag-modal-unhide');
					}, 50);
					nomeFlagAnterior = '';
				}
			}, 10)
		});
	}

	/**
	 * Função para fechar o modal de explicação de flags
	 */
	let __closeExplicacaoFlagModal = function() {
		if($(idExplicacaoFlagModal).length > 0) {
			$(idExplicacaoFlagModal).remove();
		}
	}

	/**
	 * Função para quando o botão de fechar no modal for clicado
	 */
	let __btnCloseExplicacaoFlagModal = function() {
		$('#btn-close-explicacao-flag-modal')
			.off("click")
			.on("click", function (e) {
				e.preventDefault();

				// chama a função para fechar o modal de explicação de flags
				setTimeout(function () {
					$(idExplicacaoFlagModal).removeClass('explicacao-flag-modal-unhide');
				}, 50);

				setTimeout(function () {
					$(idExplicacaoFlagModal).remove();
				}, 300);
				nomeFlagAnterior = '';
			});
	}

	/**
	 * Função para tratamento do tempo de fechamento do modal e da barra de 'carregamento'
	 */
	let __decresceTimeContador = function() {
		setInterval(function() {
			// se não tiver pausado
			if (!pausaContador) {
				contadorDismiss--;

				if (contadorDismiss <= 0) {
					pausaContador = true;
				}

				// seta o tamanho da barra de carregamento
				$('#explicacao-flag-modal-timer')
					.css('width', ((1000 * contadorDismiss) / (TIMER_DISMISS * 10)) + '%');
			}
		}, 100);
	}
	// endregion Funções auxiliares

	// region Chamada das funções
	if (generateButtons) { __generateButtons(); }

	__openModal();
	__holdModal();

	if (!CLICK_TO_OPEN) {
		__out();
		__leaveInfo();

		// chama a função de tratamento do tempo de fechamento do modal
		__decresceTimeContador();
	}
	// endregion Chamada das funções
}


/**
 * Função para controle de flags com dependência
 *	- opostas: desmarca a flag que não é possível usar simultaneamente e alerta na tela
 *	- não pode habilitar se a outra X não estiver habilitada: desmarca a flag que dependende da outra e alerta na tela
 * @param that objeto da flag clicada
 * @param idFlagDependente id do input da flag
 * 							- opostas: que não pode ficar marcada
 * 							- não pode habilitar se a outra X não estiver habilitada: que deve estar habilitada
 * @param tipo tipo de dependêcia: opostas|conjuntas
 * TODO: usar envio de todas as flags dependentes no parametro idFlagDependente, que seria um array assim aplicando as ações para todas
 * TODO: evitar duas chamadas da função quando as flags foram opostas
 */
function checaDependeciaFlags(that=null,idFlagDependente=null,tipo='opostas'){
	let flagDependente= $('input#'+ idFlagDependente);
	if(
		is_empty(that,1) ||
		is_empty(idFlagDependente,1) ||
		is_empty( $(that).length,1) ||
		is_empty(flagDependente.length,1) ||
		(
			! ['opostas','conjuntas'].includes(tipo)
		)
	){return;}
	let labelThatFlag= $(that).parent('label').text().replaceAll('\n','').replaceAll('\t','').trim();
	let label= flagDependente.parent('label').text().replaceAll('\n','').replaceAll('\t','').trim();

	switch(tipo){
		case 'opostas': //desabilita a outra flag oposta quando essa for habilitada
			if(
				that.checked &&
				flagDependente.prop('checked')
			){
				flagDependente.parent('label').removeClass("active");
				flagDependente.prop('checked', false).trigger('change');
				swal(
					l["atenção!"],
					(l["aFlagTxtFlagFoiDesmarcadaPoisNãoÉPossívelUsaLaEmConjuntoComEssa"]).replace("{txtFlag}",'<span style="color: red;">'+label+'</span>'),
					"warning"
				).catch(swal.noop);
			}
		break;
		case 'conjuntas': //quando a flag clicada (main) estiver desabilitada a flag dependente não poderá ser ativa, se já estivesse então desabilita
			if( that.checked ){
				flagDependente.parents("div.form-group")
					          .removeAttr("title");
				flagDependente.removeAttr("disabled").trigger('change');
			}else{
				if(flagDependente.prop('checked')){
					swal(
						l["atenção!"],
						(l["aFlagTxtFlagFoiDesmarcadaPoisElaEDependenteDaAtivacaoDessa"]).replace("{txtFlag}",'<span style="color: red;">'+label+'</span>'),
						"warning"
					).catch(swal.noop);
				}
				flagDependente.attr("disabled","disabled")
							  .prop('checked', false)
							  .trigger('change');
				flagDependente.parents("div.form-group").attr("title", (l["habiliteAFlagTxtFlagAntesParaPoderUsarEssa"]).replace("{txtFlag}",labelThatFlag));
				flagDependente.parent('label').removeClass("active");
			}
		break;
	}
}

/**
 * Renderiza um alerta de acordo com as informações recebidas de um json
 * @param jsonMessage Deve seguir o padrão {"msg": "", "class": ""}
 * @param seletorMsg Seletor para inserção da mensagem
 */
function addJsonMessage(jsonMessage, seletorMsg = '.container-msg') {
	$(seletorMsg).html(
		`<div class="espacamento"></div>
		
		<div id="div-alert" class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX" role="alert">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			${jsonMessage.msg}
		</div>`
	);
}

/**
 * Converte valores em formato de moeda em float para calculos
 * Exemplos:
 * convertCurrencyToFloat("USA", "US$ 19,399.99"); // 19399.99
 * convertCurrencyToFloat("BR", "R$ 12.213,44");  // 12213.44
 * convertCurrencyToFloat("USA", "19399.99");      // 19399.99
 * convertCurrencyToFloat("BR", "12213,44");       // 12213.44
 * @param currencyType Qual é a moeda, BR ou USA
 * @param value Valor que será convertido
 * @returns {number}
 */
function convertCurrencyToFloat(currencyType, value) {
	if (value === "" || value === undefined) {
		value = 0;
	}
	// Remove qualquer símbolo de moeda e espaços
	value = value.toString().replace(/[^0-9.,]/g, '').trim();

	if (currencyType === "BR") {
		// Para valores em reais (BRL), substitua ponto por vazio e vírgula por ponto
		value = value.replace(/\./g, '').replace(/,/g, '.');
	} else if (currencyType === "USA") {
		// Para valores em dólares (USD), substitua vírgula por vazio
		value = value.replace(/,/g, '');
	} else {
		throw new Error("Tipo de moeda não suportado. Use 'BR' para real ou 'USA' para dólar.");
	}

	// Converta o valor para float
	var floatValue = parseFloat(value);
	if (isNaN(floatValue)) {
		throw new Error("Valor inválido. Não foi possível converter para número.");
	}

	return floatValue;
}

/**
 * Converte um float em formato de moeda, baseado no currency type
 * @param currencyType
 * @param value
 * @returns {string}
 */
function formatFloatToCurrency(currencyType, value) {
	if (typeof value !== 'number') {
		//throw new Error("O valor deve ser um número.");
		value = 0;
	}

	let formattedValue;

	if (currencyType === "BR") {
		// Formata para reais (BRL)
		formattedValue = value.toLocaleString('pt-BR', {
			style: 'currency',
			currency: 'BRL',
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	} else if (currencyType === "USA") {
		// Formata para dólares (USD)
		formattedValue = value.toLocaleString('en-US', {
			style: 'currency',
			currency: 'USD',
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
		// Adiciona um espaço entre o cifrão e o valor
		formattedValue = formattedValue.replace('$', '$ ');
	} else {
		throw new Error("Tipo de moeda não suportado. Use 'BR' para real ou 'USA' para dólar.");
	}

	return formattedValue;
}

function printPageInventarioGrupos(pageTitle){
	const printContent = document.getElementById('print-area').innerHTML;
	const printWindow = window.open(window.location.href, '_blank', 'width=900,height=600');

	printWindow.document.open();
	printWindow.document.write(`
            <!DOCTYPE html>
			<html lang="pt-BR">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>${pageTitle}</title>
				<style>
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}
			
			body {
				font-family: Arial, sans-serif;
				line-height: 1.5;
				margin: 15px;
				font-size: 14px;
			}
			
			.header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 20px;
			}
		
			.header .logo {
				max-height: 40px;
				margin-left: 10px;
			}
			
			.new-label {
				display: inline-block;
				margin-right: 5px;
			}
			
			.new-inline {
				display: inline-block;
				font-weight: bold;
				color: #333;
			}
			
			.container {
				max-width: 760px;
				margin: 0 auto;
				padding: 5px;
			}
			
			h1 {
				color: #0380F9;
				margin-bottom: 20px;
				font-size: 18px;
			}
			
			h2 {
				color: #0380F9;
				margin-bottom: 5px;
				font-size: 14px;
			}
			
			.small-text {
				font-size: 8px;
				color: #111;
				margin-bottom: 5px;
			}
			
			.details {
				font-size: 10px;
				color: #3B3B3B;
				margin-bottom: 4px;
			}
			
			.details_alt{
				display: grid;
				grid-template-columns: repeat(2, 1fr);
				width: 100%;
				max-width: 400px;
			}
			
			.highlight {
				font-weight: bold;
				color: #111;
			}
			
			.table {
			  width: 100%;
			  color: #3B3B3B;
			}
			
			.table th, .table td {
			  padding: 8px 10px 8px 0!important;
			  font-size: 10px!important;
			}
			
			.total-right {
				margin-top: 8px;
				text-align: right;
				font-size: 12px;
				font-weight: bold;
				padding-right: 12px;
			}
			
			.footer {
				text-align: left;
				font-size: 10px;
				padding: 10px;
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
				background-color: #f9f9f9;
			}
				</style>
			</head>
			<body>
				${printContent}
				<div class="footer">
					${window.location.href}
				</div>
			</body>
			</html>
        `);
	printWindow.document.close();

	// espero a janela carregar completamente antes de chamar o print
	printWindow.onload = function() {
		printWindow.print();
		printWindow.close();
	};
}