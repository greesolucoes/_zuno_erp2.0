function somaTotaisJustificativasPorFormasPagamento(){
    const tabelaFormaPagto = ".itens-tabela-demonstrativa table#table-justificativas-por-formas_pagamento";
    const trsTbodyFormaPagto = tabelaFormaPagto + " tbody tr";
    const trsTfootFormaPagto = tabelaFormaPagto + " tfoot tr";
    const casasPreco = $(".data_resumo_caixa_justificativas_por_forma_pag").data("casas_preco");
    let valAuxiliarItem = 0;
    let valAuxiliarTotal = 0;

    const cifrao = $(".data_resumo_caixa_justificativas_por_forma_pag").data("cifrao");
    const cifrao_is_prefixo = $(".data_resumo_caixa_justificativas_por_forma_pag").data("cifrao_is_prefixo");
    const separador_decimal = $(".data_resumo_caixa_justificativas_por_forma_pag").data("separador_decimal");
    const separador_milhar = $(".data_resumo_caixa_justificativas_por_forma_pag").data("separador_milhar");

    $(trsTfootFormaPagto).find('td.valor_justificativa').val("0");

    $(trsTbodyFormaPagto).each(function() {
        valAuxiliarItem = stringParaFloat($(this).find('td.valor_justificativa').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.valor_justificativa').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.valor_justificativa').text(valAuxiliarTotal + valAuxiliarItem);
    });

    $(trsTfootFormaPagto).find('td.valor_justificativa').text(formataDecimal($(trsTfootFormaPagto).find('td.valor_justificativa').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

somaTotaisJustificativasPorFormasPagamento();