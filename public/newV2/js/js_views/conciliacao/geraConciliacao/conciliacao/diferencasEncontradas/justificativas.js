/**
 * Function criaSelectsJustificativas
 * @param {boolean} isLastTR Define se é para realizar triggers na ultima TR apenas
 *
 * Cria o Select2 de todos os selects da página e altera o data-init deles para nada
 * (pode ocasionar problemas se não for feito isso após a 1 chamada do select 2)
 */
function criaSelectsJustificativas(isLastTR){
	let __triggerJustificativa = function (obj, vlObj) {
		let __addTravas = function (objTR) {
			$($(objTR).find(".r-ref-conta-debito-justificativas")).attr('readonly', true);
			$($(objTR).find(".r-ref-conta-credito-justificativas")).attr('readonly', true);
			$($(objTR).find(".r-ref-conta-debito-justificativas")).prop('readonly', true);
			$($(objTR).find(".r-ref-conta-credito-justificativas")).prop('readonly', true);
			$($(objTR).find(".r-ref-conta-debito-justificativas")).val("");
			$($(objTR).find(".r-ref-conta-credito-justificativas")).val("");
		}
		let __removeTravas = function (objTR) {
			$($(objTR).find(".r-ref-conta-debito-justificativas")).attr('readonly', false);
			$($(objTR).find(".r-ref-conta-credito-justificativas")).attr('readonly', false);
			$($(objTR).find(".r-ref-conta-debito-justificativas")).prop('readonly', false);
			$($(objTR).find(".r-ref-conta-credito-justificativas")).prop('readonly', false);
		}

		let url = $(".data_justificativas").data('url_get_bol_justificativas');
		if(is_empty(url, 1) || is_empty(obj, 1)) {
			return;
		}

		if(is_empty(vlObj, 1)) {
			__addTravas($(obj).parents("tr"));
			return;
		}

		ajaxRequest(true, url, null, 'text', {
			'idJustificativa': vlObj
		}, function (ret) {
			ret = JSON.parse(ret);
			if(!is_empty(ret, 1) && !is_empty(ret.bolContaManual, 1)){
				__removeTravas($(obj).parents("tr"));
				return;
			}

			__addTravas($(obj).parents("tr"));
		});
	}
	let objTriggers = $("table#r-tabela-justificativas-diferencas tbody tr");

	isLastTR = !is_empty(isLastTR, 1);
	if(isLastTR) {
		objTriggers = $(objTriggers).last();
	}

	$(".r-select_justificativa-diferencas").select2Ajax();
	$(".r-select_justificativa-diferencas").data('init', '');

	$(".r-select_justificativa-diferencas").off("select2:select");
	$(".r-select_justificativa-diferencas").on("select2:select", function () {
		__triggerJustificativa($(this), $(this).val());
	});
	$(".r-select_justificativa-diferencas").off("select2:unselect");
	$(".r-select_justificativa-diferencas").on("select2:unselect", function () {
		__triggerJustificativa($(this), null);
	});
	$(".r-select_justificativa-diferencas").off("select2:clear");
	$(".r-select_justificativa-diferencas").on("select2:clear", function () {
		__triggerJustificativa($(this), null);
	});

	$($(objTriggers).find(".r-select_justificativa-diferencas")).trigger("select2:select");
}

/**
 * Function removeItensContaJustificativas
 * Remove itens na tabela de justificativas
 */
function removeItensContaJustificativas() {
    $('table#r-tabela-justificativas-diferencas button.r-remove-itens-justificativas').off('click');
    $('table#r-tabela-justificativas-diferencas button.r-remove-itens-justificativas').on("click", function () {
        let rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCamposJustificativas();
            triggerSomaCamposJustificativas();
        });
    });
}

/**
 * Function addItensContasJustificativas
 * Adiciona itens na tabela de justificativas
 */
function addItensContasJustificativas() {
    $('table#r-tabela-justificativas-diferencas button#r-add-itens-justificativas').off("click");
    $('table#r-tabela-justificativas-diferencas button#r-add-itens-justificativas').on("click", function () {
        const tbody  = $('table#r-tabela-justificativas-diferencas tbody');
        const modelo = $(tbody).find('tr').first().html();

        $(tbody).append('<tr>' + modelo + '</tr>');
        $(tbody).find('select').select2Reset();

        allFunctions();
        criaSelectsJustificativas(true);
        removeItensContaJustificativas();

        somaCamposJustificativas();
        triggerSomaCamposJustificativas();
    });
}

/**
 * Function somaCamposJustificativas
 * Soma o total da diferença de acordo com as justificativas
 */
function somaCamposJustificativas() {
    // console.log('========================================');
    // console.log('================ Inicio ================');
    // console.log('========================================');

    const linhas                = $("table#r-tabela-justificativas-diferencas tbody tr");
    let casasPreco            = $('.data_justificativas').data('casas_preco');
    if(is_empty(casasPreco, 1)) casasPreco = '0';
    casasPreco = parseInt(casasPreco.toString());
    // console.log('Casas Preço: ' + casasPreco);

    const cifrao = $(".data_justificativas").data("cifrao");
    const cifrao_is_prefixo = $(".data_justificativas").data("cifrao_is_prefixo");
    const separador_decimal = $(".data_justificativas").data("separador_decimal");
    const separador_milhar = $(".data_justificativas").data("separador_milhar");
    // console.log('Cifrão: ' + cifrao);
    // console.log('Cifrão is prefixo: ' + cifrao_is_prefixo);
    // console.log('Separador Decimal: ' + separador_decimal);
    // console.log('Separador Milhar: ' + separador_milhar);

    let valorTotalCaixa       = stringParaFloat($(".r-caixa-total-justificativas").data('valor').toString(), '.', true);
    let valorTotalFrenteCaixa = stringParaFloat($(".r-frente-caixa-total-justificativas").data('valor').toString(), '.', true);
    if(is_empty_numeric(valorTotalCaixa))       valorTotalCaixa = 0;
    if(is_empty_numeric(valorTotalFrenteCaixa)) valorTotalFrenteCaixa = 0;
    // console.log('Valor Total Caixa (texto): ' + $(".r-caixa-total-justificativas").data('valor').toString());
    // console.log('Valor Total Frente de Caixa (texto): ' + $(".r-frente-caixa-total-justificativas").data('valor').toString());
    // console.log('Valor Total Caixa: ' + valorTotalCaixa);
    // console.log('Valor Total Frente de Caixa: ' + valorTotalFrenteCaixa);

    let valorTotalDiferenca = valorTotalCaixa - valorTotalFrenteCaixa;
    // console.log('Valor Total Caixa - Valor Total Frente de Caixa: ' + valorTotalDiferenca);

    let valorTotaisLinhas = 0;
    if(is_empty_numeric(valorTotalDiferenca)) {
        valorTotalDiferenca = 0;
    }
    valorTotalDiferenca = stringParaFloat(valorTotalDiferenca.toFixed(casasPreco).toString(), '.', true);
    // console.log('Valor Total Diferença inicial: ' + valorTotalDiferenca);

    $.each(linhas, function (idLinha, linha) {
        let valorLinha = stringParaFloat($(linha).find('.r-valor-justificativas').val(), separador_decimal, true);
        if(is_empty_numeric(valorLinha)) valorLinha = 0;
        // console.log('Valor linha ' + idLinha + ": " + valorLinha);

        valorTotaisLinhas = stringParaFloat(valorTotaisLinhas.toString(), '.', true) + valorLinha;
        // console.log('Valor Total Linha Agora: ' + valorTotaisLinhas);
    });
    valorTotaisLinhas = stringParaFloat(valorTotaisLinhas.toFixed(casasPreco).toString(), '.', true);
    valorTotalDiferenca -= valorTotaisLinhas;
    // console.log('Valor Total Linha Final: ' + valorTotaisLinhas);
    // console.log('Valor Total Diferença Final: ' + valorTotalDiferenca);

    $('.r-diferenca-total-justificativas').text(
        formataDecimal(
            valorTotalDiferenca,
            '.',
            separador_decimal,
            separador_milhar,
            cifrao,
            cifrao_is_prefixo,
            casasPreco
        )
    );
    // console.log(
    //     'Valor Total Diferença Final Formatada: ' +
    //     formataDecimal(
    //         valorTotalDiferenca,
    //         '.',
    //         separador_decimal,
    //         separador_milhar,
    //         cifrao,
    //         cifrao_is_prefixo,
    //         casasPreco
    //     )
    // );
    //
    // console.log('========================================');
    // console.log('================ Final =================');
    // console.log('========================================');
}

/**
 * Function triggerSomaCamposJustificativas
 * Prepara gatilhos que ativam a soma da diferença do caixa e da frente de caixa
 */
function triggerSomaCamposJustificativas() {
    $("table#r-tabela-justificativas-diferencas tbody tr .r-valor-justificativas").off("keyup").on("keyup", function() {
        somaCamposJustificativas();
    });
}

addItensContasJustificativas();
criaSelectsJustificativas(false);
removeItensContaJustificativas();

somaCamposJustificativas();
triggerSomaCamposJustificativas();

allFunctions();