/**
 * Created by vitor on 26/08/2017.
 */
function somaCampos(){
    var total = 0;
    var totalFinal = 0;
    $("table#conteudoTable tbody tr").each(function () {
        if($(this).find('input.quantidade').val() !== null && $(this).find('input.quantidade').val() !== '' &&
            $(this).find('input.precoUnitario').val() !== null && $(this).find('input.precoUnitario').val() !== ''){
            var quantidade    = 0;
            var precoUnitario = 0;

            if($(this).find('input.quantidade').val() !== null && $(this).find('input.quantidade').val() !== '' && $(this).find('input.quantidade').val() !== undefined) {
                if ($(this).find('input.quantidade').val().includes($('.data-views').data('sep_decimal'))) {
                    var splitQtd = $(this).find('input.quantidade').val().split($('.data-views').data('sep_decimal'));
                    quantidade = formataFloat($(this).find('input.quantidade').val(), $('.data-views').data('sep_decimal'), splitQtd[1].length);
                } else {
                    quantidade = formataFloat($(this).find('input.quantidade').val(), $('.data-views').data('sep_decimal'));
                }
            }

            if($(this).find('input.precoUnitario').val() !== null && $(this).find('input.precoUnitario').val() !== '' && $(this).find('input.precoUnitario').val() !== undefined) {
                if ($(this).find('input.precoUnitario').val().includes($('.data-views').data('sep_decimal'))) {
                    var splitPreco = $(this).find('input.precoUnitario').val().split($('.data-views').data('sep_decimal'));
                    precoUnitario = formataFloat($(this).find('input.precoUnitario').val(), $('.data-views').data('sep_decimal'), splitPreco[1].length);
                } else {
                    precoUnitario = formataFloat($(this).find('input.precoUnitario').val(), $('.data-views').data('sep_decimal'));
                }
            }

            var subtotal = quantidade * precoUnitario;
            $(this).find('input.subTotal').val(number_format(
                subtotal,
                6,
                $('.data-views').data('sep_decimal'),
                $('.data-views').data('sep_milhar'),
                $('.data-views').data('cifrao')
            ));

            total += subtotal;
        }
    });
    totalFinal = total;
    $("table#despesasTable tbody tr").each(function () {
        if($(this).find('input.valorDespesa').val() !== null && $(this).find('input.valorDespesa').val() !== ''){
            var valorAdicional = 0;
            if($(this).find('input.valorDespesa').val() !== null && $(this).find('input.valorDespesa').val() !== '' && $(this).find('input.valorDespesa').val() !== undefined) {
                if ($(this).find('input.valorDespesa').val().includes($('.data-views').data('sep_decimal'))) {
                    var splitValor = $(this).find('input.valorDespesa').val().split($('.data-views').data('sep_decimal'));
                    valorAdicional = formataFloat($(this).find('input.valorDespesa').val(), $('.data-views').data('sep_decimal'), splitValor[1].length);
                } else {
                    valorAdicional = formataFloat($(this).find('input.valorDespesa').val(), $('.data-views').data('sep_decimal'));
                }
            }

            totalFinal += valorAdicional;
        }
    });

    $("table#conteudoTable input#totalGeral").val(number_format(
        total,
        6,
        $('.data-views').data('sep_decimal'),
        $('.data-views').data('sep_milhar'),
        $('.data-views').data('cifrao')
    ));
    $("div#conteudo input#valorTotalFinal").val(number_format(
        totalFinal,
        6,
        $('.data-views').data('sep_decimal'),
        $('.data-views').data('sep_milhar'),
        $('.data-views').data('cifrao')
    ));
}

function somaCamposLotes() {
	let casasQtd = $("div.data-views").data('casas_qtd');
	const linhas = $("table#conteudo-lotes-tabela tbody tr:not(.ocultar)");

	if(is_empty(casasQtd, 1)) casasQtd = '0';
	casasQtd = parseInt(casasQtd.toString());

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		valorTotaisLinhas += stringParaFloat(
			$(linha).find('.div-conteudo-lote_quantidade').text(), $('.data-views').data('sep_decimal'), true
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
			$('.data-views').data('sep_decimal'),
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

function controlaLote() {
	let trAddLote = null;

	$('button.controlar_lote').off('click');
	$('button.controlar_lote').on("click", function () {
		trAddLote = $($(this).parents("tr"));
		let lotes = JSON.parse($($(trAddLote).find(".conteudo-itens_lotes_json")).val());
		$($($('#conteudo-lotes-tabela tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");

		let casasQtd = $("div.data-views").data('casas_qtd');
		if(is_empty(casasQtd, 1)) casasQtd = '0';
		casasQtd = parseInt(casasQtd.toString());

		$.each(lotes, function (idx, lote) {
			$('#conteudo-lotes-tabela tfoot .add-itens-table-geral').trigger("click");
			$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lotes_id_interno")).text(lote.uidLinhaLote);
			$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_nome")).text(lote.nome);
			$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_quantidade")).text(
				formataDecimal(
					lote.quantidade,
					".",
					$('.data-views').data('sep_decimal'),
					"",
					"",
					true,
					casasQtd
				)
			);
			if(!is_empty(lote.dataFabricacao, 1)) {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_data_fabricacao")).text(moment(lote.dataFabricacao, "YYYY-MM-DD").format($('div.data-views').data('format_date')));
			}
			if(!is_empty(lote.dataVencimento, 1)) {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_data_vencimento")).text(moment(lote.dataVencimento, "YYYY-MM-DD").format($('div.data-views').data('format_date')));
			}
		});
		somaCamposLotes();
		lotes = null;

		$('.modal-lote').modal('toggle');
	});
}

//funcao para ordernar campos
function sortTable() {
	var table, rows, switching, i, x, y, shouldSwitch;
	table = document.getElementById("conteudoTable");
	switching = true;
	while (switching) {
		switching = false;
		rows = table.rows;
		for (i = 1; i < (rows.length - 1); i++) {
			shouldSwitch = false;

			//ordena por ordem alfabetica o nome do item
			x = rows[i].querySelector("TD input").value;
			y = rows[i + 1].querySelector("TD input").value;

			x = !is_empty(x.split(' - ')[1]) ? x.split(' - ')[1].toLowerCase() : 'zzzzzz'; //foi colocado zzzzzz para quando ordernar ordem alfabetica e n tiver texto, ele jogar para ultima posição
			y = !is_empty(y.split(' - ')[1]) ? y.split(' - ')[1].toLowerCase() : 'zzzzzz'; //foi colocado zzzzzz para quando ordernar ordem alfabetica e n tiver texto, ele jogar para ultima posição

			if ((x > y) && (!is_empty(x) || !is_empty(y))) {
				shouldSwitch = true;
				break;
			}
		}
		if (shouldSwitch) {
			rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
			switching = true;
		}
	}
}

somaCampos();
controlaLote();
controlaTabelaSuite({
	"ref": "#conteudo-lotes-tabela",
	"funAposAddItem": function () {
		atualizaLinhasLotes();
		somaCamposLotes();
	},
	"funAposRemoverItem": function () {
		atualizaLinhasLotes();
		somaCamposLotes();
	}
});