function somaCampos() {
	var total = 0;
	$("table#conteudoTable tbody tr").each(function (index) {
		if ($(this).find('input.quantidade').val() !== null && $(this).find('input.quantidade').val() !== '' &&
			$(this).find('input.precoUnitario').val() !== null && $(this).find('input.precoUnitario').val() !== '') {
			var quantidade = 0;
			var precoUnitario = 0;

			// Define o delimitador
			var delimitadorDecimal = configLocation.currencyDecimalPoint;

			if ($(this).find('input.quantidade').val() !== null &&
				$(this).find('input.quantidade').val() !== '' &&
				$(this).find('input.quantidade').val() !== undefined) {

				var valorQuantidade = $(this).find('input.quantidade').val();

				// Verifica se o valor contém o delimitador definido manualmente
				if (valorQuantidade.includes(delimitadorDecimal)) {
					var splitQtd = valorQuantidade.split(delimitadorDecimal);
					quantidade = toFloat(valorQuantidade, splitQtd[1].length);
				} else {
					quantidade = toFloat(valorQuantidade, 0);
				}
			}

			if ($(this).find('input.precoUnitario').val() !== null && $(this).find('input.precoUnitario').val() !== '' && $(this).find('input.precoUnitario').val() !== undefined) {
				if ($(this).find('input.precoUnitario').val().includes(",")) {
					var splitPreco = $(this).find('input.precoUnitario').val().split(",");
					precoUnitario = toFloat($(this).find('input.precoUnitario').val(), splitPreco[1].length);
				} else {
					precoUnitario = toFloat($(this).find('input.precoUnitario').val(), 0);
				}
			}

			var subtotal = quantidade * precoUnitario;
			$(this).find('input.subTotal').val(float2real(subtotal, true));
			$("table#conteudo-table-print tbody tr").each(function (indexPrint) {
				if (index == indexPrint) {
					$(this).find('td.sub-total-print').text(float2real(subtotal, true));
				}
			});

			total += subtotal;
		}
	});
	$("table#conteudoTable input#totalGeral").val(float2real(total, true));
	$("table#conteudo-table-print tfoot td.total-geral-print .total-texto-print").text(float2real(total, true));
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
			x = rows[i].querySelector("TD input") != null ? rows[i].querySelector("TD input").value : null;
			y = rows[i + 1].querySelector("TD input") != null ? rows[i + 1].querySelector("TD input").value : null;

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