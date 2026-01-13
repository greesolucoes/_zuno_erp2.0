function somaCampos() {
	var total = 0;
	$("table#conteudoTable tbody tr").each(function (index) {
		if ($(this).find('input.quantidade').val() !== null && $(this).find('input.quantidade').val() !== '' &&
			$(this).find('input.precoUnitario').val() !== null && $(this).find('input.precoUnitario').val() !== '') {
			var quantidade = 0;
			var precoUnitario = 0;

			if ($(this).find('input.quantidade').val() !== null && $(this).find('input.quantidade').val() !== '' && $(this).find('input.quantidade').val() !== undefined) {
				// if ($(this).find('input.quantidade').val().includes($('.data-views').data('sep_decimal'))) {
				if ($(this).find('input.quantidade').val().includes(',')) {
					// var splitQtd = $(this).find('input.quantidade').val().split($('.data-views').data('sep_decimal'));
					var splitQtd = $(this).find('input.quantidade').val().split(',');
					// quantidade = formataFloat($(this).find('input.quantidade').val(), $('.data-views').data('sep_decimal'), splitQtd[1].length);
					quantidade = formataFloat($(this).find('input.quantidade').val(), ',', splitQtd[1].length);
				} else {
					// quantidade = formataFloat($(this).find('input.quantidade').val(), $('.data-views').data('sep_decimal'));
					quantidade = formataFloat($(this).find('input.quantidade').val(), ',');
				}
			}

			if ($(this).find('input.precoUnitario').val() !== null && $(this).find('input.precoUnitario').val() !== '' && $(this).find('input.precoUnitario').val() !== undefined) {
				if ($(this).find('input.precoUnitario').val().includes($('.data-views').data('sep_decimal'))) {
					var splitPreco = $(this).find('input.precoUnitario').val().split($('.data-views').data('sep_decimal'));
					precoUnitario = formataFloat($(this).find('input.precoUnitario').val(), $('.data-views').data('sep_decimal'), splitPreco[1].length);
				} else {
					precoUnitario = formataFloat($(this).find('input.precoUnitario').val(), $('.data-views').data('sep_decimal'));
				}
			}

			var subtotal = quantidade * precoUnitario;
			$(this).find('input.subTotal').val(number_format(subtotal, 6, $('.data-views').data('sep_decimal'), $('.data-views').data('sep_milhar'), $('.data-views').data('cifrao')));
			$("table#conteudo-table-print tbody tr").each(function (indexPrint) {
				if (index == indexPrint) {
					$(this).find('td.sub-total-print').text(number_format(subtotal, 6, $('.data-views').data('sep_decimal'), $('.data-views').data('sep_milhar'), $('.data-views').data('cifrao')));
				}
			});

			total += subtotal;
		}
	});
	$("table#conteudoTable input#totalGeral").val(number_format(total, 6, $('.data-views').data('sep_decimal'), $('.data-views').data('sep_milhar'), $('.data-views').data('cifrao')));
	$("table#conteudo-table-print tfoot td.total-geral-print .total-texto-print").text(number_format(total, 6, $('.data-views').data('sep_decimal'), $('.data-views').data('sep_milhar'), $('data-views').data('cifrao')));
}

somaCampos();

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