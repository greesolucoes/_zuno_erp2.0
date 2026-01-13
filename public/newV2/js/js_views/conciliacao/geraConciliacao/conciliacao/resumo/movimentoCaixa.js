function somaTotaisResumoMovimentoCaixa(){
    const tabelaFormaPagto = ".itens-tabela-demonstrativa table#tabela-resumo_movimento_caixa";
    const trsTbodyFormaPagto = tabelaFormaPagto + " tbody tr";
    const trsTfootFormaPagto = tabelaFormaPagto + " tfoot tr";
    const casasPreco = $(".resumo_data_movimento_caixa").data("casas_preco");
    let valAuxiliarItem = 0;
    let valAuxiliarTotal = 0;

    const cifrao = $(".resumo_data_movimento_caixa").data("cifrao");
    const cifrao_is_prefixo = $(".resumo_data_movimento_caixa").data("cifrao_is_prefixo");
    const separador_decimal = $(".resumo_data_movimento_caixa").data("separador_decimal");
    const separador_milhar = $(".resumo_data_movimento_caixa").data("separador_milhar");

    $(trsTfootFormaPagto).find('td.valor').val("0");

    $(trsTbodyFormaPagto).each(function() {
        valAuxiliarItem = stringParaFloat($(this).find('td.valor').text().trim(), separador_decimal, true);
        if($(this).find('td.tipo_valor').data('tipo_valor').toString() === "2"){
            valAuxiliarItem *= -1;
        }

        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.valor').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.valor').text(valAuxiliarTotal + valAuxiliarItem);
    });

    $(trsTfootFormaPagto).find('td.valor').text(formataDecimal($(trsTfootFormaPagto).find('td.valor').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

somaTotaisResumoMovimentoCaixa();