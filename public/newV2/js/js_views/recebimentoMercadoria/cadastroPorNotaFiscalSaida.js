function criaSelects(){
    $(".select_produtoId").select2AjaxProdutos();
    $(".select_medida").select2({
        placeholder: l["unidadeDeMedida"],
        language: "pt-BR",
        allowClear: true
    });
    $(".select_utilizacao").select2({
        placeholder: l["utilização"],
        language: "pt-BR",
        allowClear: true
    });
    $(".select_despesa").select2({
        placeholder: l["tipoDaDespesa"],
        language: "pt-BR",
        allowClear: true
    });

    $(".select_condicoesPagamento, .select_modeloNotaFiscal").select2({
        placeholder: l["selecione"],
        language: "pt-BR",
        allowClear: true
    });

    $(".select_moeda").select2({
        placeholder: l["selecione"],
        language: "pt-BR",
        allowClear: false
    });

    $(".select_observacoesId").select2Ajax();
    $(".select_observacoesId").data('init', '');
}

function somaCamposLotes() {
	let casasQtd = $("div.data_views").data('casas_qtd');
	const linhas   = $("table#conteudo-lotes-tabela tbody tr:not(.ocultar)");

	if(is_empty(casasQtd, 1)) casasQtd = '0';
	casasQtd = parseInt(casasQtd.toString());

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		valorTotaisLinhas += stringParaFloat(
			$(linha).find('.conteudo-lote_quantidade').val(), configLocation.currencyDecimalPoint, true
		);
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasQtd).toString(),
			'.',
			true
		);


	$('#conteudo-lotes_total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			configLocation.currencyDecimalPoint,
			"",
			"",
			true,
			casasQtd
		)
	);
}

function atualizaLinhasLotes() {
	let index = -1;
	$("table#conteudo-lotes-tabela tbody tr .conteudo-lotes_id_interno").each(function () {
		$(this).val(index);
		index++;
	});
}

function ativaMudancaMoeda() {
    var isFirstTime = true;

    $(".select_moeda").unbind('select2:select');
    $(".select_moeda").on('select2:select', function () {
        var iMoeda = 0;
        $($('table#conteudoTable tbody').find('tr')).each(function () {
            if(iMoeda === 0 || isFirstTime) {
                if(!isFirstTime){
                    $(this).find('input[type="text"]').prop('value', '');

                    $(this).find('select.select_produtoId').find('option').remove();
                    $(this).find('select.select_produtoId').data('init', '');

                    $(this).find('select.select_medida').find('option').remove();
                    $(this).find('select.select_medida').append('<option value=""></option>');

                    $(this).find('select').find('option:selected').prop('selected', false);
                    $(this).find('select.select_medida option[value=""]').prop('selected', 'selected');
                    $(this).find('select.select_produtoId option[value=""]').prop('selected', 'selected');

                    $(this).find('select.select_medida').removeClass('readonly');
                    $(this).find('input.precoUnitario').prop('readonly', false);

                    $(this).find('select.select_produtoId').data('travaselecao', 1);
                }

                trocaParametroCamposMaskNumerosV2($(this).find('input.precoUnitario'));
                trocaParametroCamposMaskNumerosV2($(this).find('input.subTotal'));

                iMoeda++;
                return;
            }

            iMoeda++;
            if(!isFirstTime) $(this).remove();
        });

        iMoeda = 0;
        $("table#despesasTable tbody tr").each(function () {
            if(iMoeda === 0 || isFirstTime) {
                if(!isFirstTime){
                    $(this).find('input[type="text"]').prop('value', '');

                    $(this).find('select').find('option:selected').prop('selected', false);
                    $(this).find('select option[value=""]').prop('selected', 'selected');
                }

                trocaParametroCamposMaskNumerosV2($(this).find('input.valorDespesa'));

                iMoeda++;
                return;
            }

            iMoeda++;
            if(!isFirstTime) $(this).remove();
        });

		$('select.select_produtoId').data('travaselecao', 1);
        trocaParametroCamposMaskNumerosV2($('input#totalGeral'));
        trocaParametroCamposMaskNumerosV2($('input#valorTotalFinal'));
        allFunctions();
        criaSelects();
        addSomaCampos();
        somaCampos();
        $('select.select_produtoId').data('travaselecao', 0);
    });
    $(".select_moeda").trigger('select2:select');

    isFirstTime = false;
}

function trocaParametroCamposMaskNumerosV2(__this) {
    var v = $(__this).clone();
    $(__this).parent().append(v);
    $(__this).remove();

    $(v).attr('data-mask', 'numerov2');
    $(v).attr('data-prefixo', $($(".select_moeda").find('option:selected')).data('texto_impressao'));
    $(v).attr('data-sufixo', '');
    $(v).attr('data-thousand_delimiter', $($(".select_moeda").find('option:selected')).data('sep_milhar'));
    $(v).attr('data-decimal_delimiter', $($(".select_moeda").find('option:selected')).data('sep_decimal'));
    $(v).attr('data-bol_negative', 'false');
    $(v).attr('data-maxdecimal', $('.data_views').data('casas_preco'));

    $(v).fnMascaraNumeroV2();
}

function contaCharObservacoes() {
    var controleObs = $('.data_views').data('controle_obs');
    if(is_empty(controleObs, 1)) {
        var maxLenObs = 254;
        var char = 0;
        $('#observacoes').keyup(function () {
            var len = $(this).val().length;
            if (len > maxLenObs) {
                char = 0;
                $(this).val($(this).val().substring(0, maxLenObs));
            } else {
                char = maxLenObs - len;
            }
            $('p#numChars').text(char + ' ' + l["caracteresRestantes"]);
        });
        char = maxLenObs - $('#observacoes').val().length;
        $('p#numChars').text(char + ' ' + l["caracteresRestantes"]);
    }
}

function somaCampos(){
    var total = 0;
    $("table#conteudoTable tbody tr").each(function () {
        var subtotal =
            formataFloat(
                $(this).find('input.quantidade').val(),
				configLocation.currencyDecimalPoint,
                $('.data_views').data('casas_qtd')
            )  *
            formataFloat(
                $(this).find('input.precoUnitario').val(),
                $($(".select_moeda").find('option:selected')).data('sep_decimal'),
                $('.data_views').data('casas_preco')
            );
        $(this).find('input.subTotal').val(number_format(
            subtotal,
            $('.data_views').data('casas_preco'),
            $($(".select_moeda").find('option:selected')).data('sep_decimal'),
            $($(".select_moeda").find('option:selected')).data('sep_milhar'),
            $($(".select_moeda").find('option:selected')).data('texto_impressao')
        ));
        total += subtotal;
    });
    $("table#conteudoTable input#totalGeral").val(number_format(
        total,
        $('.data_views').data('casas_preco'),
        $($(".select_moeda").find('option:selected')).data('sep_decimal'),
        $($(".select_moeda").find('option:selected')).data('sep_milhar'),
        $($(".select_moeda").find('option:selected')).data('texto_impressao')
    ));

    $("table#despesasTable tbody tr input.valorDespesa").each(function () {
        total +=
            formataFloat(
                $(this).val(),
                $($(".select_moeda").find('option:selected')).data('sep_decimal'),
                $('.data_views').data('casas_preco')
            );
    });
    $("div#conteudo input#valorTotalFinal").val(number_format(
        total,
        $('.data_views').data('casas_preco'),
        $($(".select_moeda").find('option:selected')).data('sep_decimal'),
        $($(".select_moeda").find('option:selected')).data('sep_milhar'),
        $($(".select_moeda").find('option:selected')).data('texto_impressao')
    ));
}

function addSomaCampos(){
	$("input.quantidade, " +
		"input.precoUnitario, " +
		"input.valorDespesa").unbind('keyup').keyup(somaCampos);
}

function controlaLote() {
	if(is_empty($(".data_views").data("is_controlar_lote"), 1)) {
		return;
	}
	let trAddLote = null;

	$('button.controlar_lote').off('click');
	$('button.controlar_lote').on("click", function () {
		trAddLote = $($(this).parents("tr"));
		let lotes = JSON.parse($($(trAddLote).find(".conteudo-itens_lotes_json")).val());
		$($($('#conteudo-lotes-tabela tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");

		let casasQtd = $("div.data_views").data('casas_qtd');
		if(is_empty(casasQtd, 1)) casasQtd = '0';
		casasQtd = parseInt(casasQtd.toString());

		$.each(lotes, function (idx, lote) {
			$('#conteudo-lotes-tabela tfoot .add-itens-table-geral').trigger("click");
			$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lotes_id_interno")).val(lote.uidLinhaLote);
			$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_nome")).val(lote.nome);
			$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_quantidade")).val(
				formataDecimal(
					lote.quantidade,
					".",
					configLocation.currencyDecimalPoint,
					"",
					"",
					true,
					casasQtd
				)
			);
			if(!is_empty(lote.dataFabricacao, 1)) {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_data_fabricacao")).val(moment(lote.dataFabricacao, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
			}
			if(!is_empty(lote.dataVencimento, 1)) {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_data_vencimento")).val(moment(lote.dataVencimento, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
			}
		});
		$("table#conteudo-lotes-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
			somaCamposLotes();
		});
		somaCamposLotes();
		lotes = null;

		$('.modal-lote').modal('toggle');
	});

	$('.modal-lote button.btn-salvar-lote_itens').off('click');
	$('.modal-lote button.btn-salvar-lote_itens').on("click", function () {
		let lotes = [];
		let casasQtd = $("div.data_views").data('casas_qtd');

		if(is_empty(casasQtd, 1)) casasQtd = '0';
		casasQtd = parseInt(casasQtd.toString());

		$($("#conteudo-lotes-tabela tbody tr").not(':first')).each(function () {
			let obj = $(this);
			let push = {
				"uidLinhaLote": $(obj).find(".conteudo-lotes_id_interno").val(),
				"nome": $(obj).find(".conteudo-lote_nome").val(),
				"quantidade": formataDecimal(
					$(obj).find(".conteudo-lote_quantidade").val(),
					configLocation.currencyDecimalPoint,
					".",
					"",
					"",
					true,
					casasQtd
				),
				"dataFabricacao": $(obj).find(".conteudo-lote_data_fabricacao").val(),
				"dataVencimento": $(obj).find(".conteudo-lote_data_vencimento").val(),
			};
			if(!is_empty(push['dataFabricacao'], 1)) {
				push['dataFabricacao'] = moment(push['dataFabricacao'], $('div.data_views').data('format_date')).format('YYYY-MM-DD');
			} else {
				push['dataFabricacao'] = null;
			}
			if(!is_empty(push['dataVencimento'], 1)) {
				push['dataVencimento'] = moment(push['dataVencimento'], $('div.data_views').data('format_date')).format('YYYY-MM-DD');
			} else {
				push['dataVencimento'] = null;
			}

			lotes.push(push);
		});

		if(trAddLote !== null && trAddLote.length === 1) {
			$($(trAddLote).find(".conteudo-itens_lotes_json")).val(JSON.stringify(lotes));
		}

		trAddLote = null;
		$('.modal-lote').modal('toggle');
	});
}

$('select.select_produtoId').data('travaselecao', 1);
criaSelects();
addSomaCampos();
somaCampos();
contaCharObservacoes();
controlaLote();
controlaTabelaSuite({
	"ref": "#conteudo-lotes-tabela",
	"funAposAddItem": function () {
		atualizaLinhasLotes();
		$("table#conteudo-lotes-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
			somaCamposLotes();
		});
		somaCamposLotes();
	},
	"funAposRemoverItem": function () {
		atualizaLinhasLotes();
		somaCamposLotes();
	}
});
$('select.select_produtoId').data('travaselecao', 0);
ativaMudancaMoeda();