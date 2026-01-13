function somaTotaisFormasPagamentoPorCaixa(){
    const tabelaFormaPagto = ".itens-tabela-demonstrativa-resumo-caixa table#tabela-resumo_pagto_por_caixa";
    const trsTbodyFormaPagto = tabelaFormaPagto + " tbody tr";
    const trsTfootFormaPagto = tabelaFormaPagto + " tfoot tr";
    const casasPreco = $(".data_resumo_pagto_por_caixa").data("casas_preco");
    let valAuxiliarItem = 0;
    let valAuxiliarTotal = 0;

    const cifrao = $(".data_resumo_pagto_por_caixa").data("cifrao");
    const cifrao_is_prefixo = $(".data_resumo_pagto_por_caixa").data("cifrao_is_prefixo");
    const separador_decimal = $(".data_resumo_pagto_por_caixa").data("separador_decimal");
    const separador_milhar = $(".data_resumo_pagto_por_caixa").data("separador_milhar");

    $(trsTfootFormaPagto).find('td.total_caixa').text("0");
    $(trsTfootFormaPagto).find('td.total_parcial_forma_pagto').text("0");
    $(trsTfootFormaPagto).find('td.total_forma_pagto').text("0");
	$(trsTfootFormaPagto).find('td.total_vendas_card_service').text("0");

    $(trsTbodyFormaPagto).each(function() {
        valAuxiliarItem = stringParaFloat($(this).find('td.total_caixa').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.total_caixa').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.total_caixa').text(valAuxiliarTotal + valAuxiliarItem);

        valAuxiliarItem = stringParaFloat($(this).find('td.total_parcial_forma_pagto').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.total_parcial_forma_pagto').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.total_parcial_forma_pagto').text(valAuxiliarTotal + valAuxiliarItem);

        valAuxiliarItem = stringParaFloat($(this).find('td.total_forma_pagto').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.total_forma_pagto').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.total_forma_pagto').text(valAuxiliarTotal + valAuxiliarItem);

		valAuxiliarItem = stringParaFloat($(this).find('td.total_vendas_card_service').text().trim(), separador_decimal, true);
		valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.total_vendas_card_service').text().trim().toString());
		if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
		if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
		$(trsTfootFormaPagto).find('td.total_vendas_card_service').text(valAuxiliarTotal + valAuxiliarItem);
    });

    $(trsTfootFormaPagto).find('td.total_caixa').text(formataDecimal($(trsTfootFormaPagto).find('td.total_caixa').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
    $(trsTfootFormaPagto).find('td.total_parcial_forma_pagto').text(formataDecimal($(trsTfootFormaPagto).find('td.total_parcial_forma_pagto').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
    $(trsTfootFormaPagto).find('td.total_forma_pagto').text(formataDecimal($(trsTfootFormaPagto).find('td.total_forma_pagto').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
	$(trsTfootFormaPagto).find('td.total_vendas_card_service').text(formataDecimal($(trsTfootFormaPagto).find('td.total_vendas_card_service').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

somaTotaisFormasPagamentoPorCaixa();