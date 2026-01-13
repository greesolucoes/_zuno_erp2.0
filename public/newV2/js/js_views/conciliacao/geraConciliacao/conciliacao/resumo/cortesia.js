function somaCortesias(tabelaFormaPagto){
	const trsTbodyFormaPagto = tabelaFormaPagto + " tbody tr";
	const trsTfootFormaPagto = tabelaFormaPagto + " tfoot tr";
	const casasPreco = $(".data_cortesia").data("casas_preco");

	const cifrao = $(".data_cortesia").data("cifrao");
	const cifrao_is_prefixo = $(".data_cortesia").data("cifrao_is_prefixo");
	const separador_decimal = $(".data_cortesia").data("separador_decimal");
	const separador_milhar = $(".data_cortesia").data("separador_milhar");

	$(trsTfootFormaPagto).find('td.total_pagamento_cortesia').text("0");
	$(trsTfootFormaPagto).find('td.total_cortesia_centavos').text("0");

	$(trsTbodyFormaPagto).each(function() {
		valAuxiliarItem = stringParaFloat($(this).find('td.total_pagamento_cortesia').text().trim(), separador_decimal, true);
		valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.total_pagamento_cortesia').text().trim().toString());
		if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
		if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
		$(trsTfootFormaPagto).find('td.total_pagamento_cortesia').text(valAuxiliarTotal + valAuxiliarItem);

		valAuxiliarItemCentavos = stringParaFloat($(this).find('td.total_cortesia_centavos').text().trim(), separador_decimal, true);
		valAuxiliarTotalCentavos = parseFloat($(trsTfootFormaPagto).find('td.total_cortesia_centavos').text().trim().toString());
		if(is_empty_numeric(valAuxiliarItemCentavos)) valAuxiliarItemCentavos = 0;
		if(is_empty_numeric(valAuxiliarTotalCentavos)) valAuxiliarTotalCentavos = 0;
		$(trsTfootFormaPagto).find('td.total_cortesia_centavos').text(valAuxiliarTotalCentavos + valAuxiliarItemCentavos);
	});

	$(trsTfootFormaPagto).find('td.total_pagamento_cortesia').text(formataDecimal($(trsTfootFormaPagto).find('td.total_pagamento_cortesia').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
	$(trsTfootFormaPagto).find('td.total_cortesia_centavos').text(formataDecimal($(trsTfootFormaPagto).find('td.total_cortesia_centavos').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

somaCortesias(".itens-tabela-demonstrativa table#table-cortesias");