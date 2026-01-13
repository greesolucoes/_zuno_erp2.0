/**
 * Function somaCamposPagamentosCaixa.
 * Soma os campos da tabela
 */
function somaCamposPagamentosCaixa() {
    var cifrao = $(".data_pagamentos").data("cifrao");
    var cifrao_is_prefixo = $(".data_pagamentos").data("cifrao_is_prefixo");
    var separador_decimal = $(".data_pagamentos").data("separador_decimal");
    var separador_milhar = $(".data_pagamentos").data("separador_milhar");

    /**
     * Function somaCamposEspecificos.
     * Soma os campos especificos dos pagamentos (Totais e Parciais)
     */
    function somaCamposEspecificos() {
        var linhas            = $("table#r-tabela-pagamentos-caixas-colaboradores tbody tr");
        var casasPreco        = $('.data_pagamentos').data('casas_preco');
        var valorTotalTotal   = 0;
        var valorTotalParcial = 0;

        $.each(linhas, function (idLinha, linha) {
            var valorParcial = stringParaFloat($(linha).find('td.parcial').text().trim(), separador_decimal, true);
            var valorTotal   = stringParaFloat($(linha).find('td.total').text().trim(), separador_decimal, true);
            if(is_empty_numeric(valorParcial)) valorParcial = 0;
            if(is_empty_numeric(valorTotal))   valorTotal = 0;

            valorTotalTotal   += valorTotal;
            valorTotalParcial += valorParcial;
        });

        $('table#r-tabela-pagamentos-caixas-colaboradores tfoot tr td.parcial').text(formataDecimal(valorTotalParcial, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
        $('table#r-tabela-pagamentos-caixas-colaboradores tfoot tr td.total').text(formataDecimal(valorTotalTotal, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
    }

    /**
     * Function somaCamposDigitaveis.
     * Soma os campos digitaveis dos pagamentos (Caixas)
     */
    function somaCamposDigitaveis() {
        var linhas          = $("table#r-tabela-pagamentos-caixas-colaboradores tbody tr");
        var casasPreco      = $('.data_pagamentos').data('casas_preco');
        var valorTotalCaixa = 0;

        $.each(linhas, function (idLinha, linha) {
            var valorCaixa = stringParaFloat($(linha).find('td.caixa input.r-caixa-input').val().trim(), separador_decimal, true);
            if(is_empty_numeric(valorCaixa)) valorCaixa = 0;

            valorTotalCaixa   += valorCaixa;
        });

		$("#valor_caixa_conciliacao_caixas").val(valorTotalCaixa);
        $('table#r-tabela-pagamentos-caixas-colaboradores tfoot tr td.caixa').text(formataDecimal(valorTotalCaixa, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
    }

    /**
     * Function triggerSomaCamposDigitaveis.
     * Ativa as triggers dos campos para somar os valores digitaveis
     */
    function triggerSomaCamposDigitaveis() {
        $("table#r-tabela-pagamentos-caixas-colaboradores tbody tr td.caixa input.r-caixa-input").off("keyup").on("keyup", function() {
            somaCamposDigitaveis();
        });
    }

    somaCamposEspecificos();
    somaCamposDigitaveis();
    triggerSomaCamposDigitaveis();
}

/**
 * Function enableBtnSalvarModalPagamentos.
 * Habilita o botão de salvar do modal
 */
function enableBtnSalvarModalPagamentos(){
    $("#r-modal-pagamentos-conciliacao .modal-footer button#r-salvar-pagamentos").prop("disabled", false);
}

allFunctions();
somaCamposPagamentosCaixa();
enableBtnSalvarModalPagamentos();