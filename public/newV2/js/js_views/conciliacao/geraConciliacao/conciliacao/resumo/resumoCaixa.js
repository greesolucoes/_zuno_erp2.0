function selecionaLinhaFormaPagamentoResumoCaixa() {
    let ultimoAjaxJustificacoes = null;

    $('table#table-formas_pagamento-resumo-caixa tbody').off('click');
    $('table#table-formas_pagamento-resumo-caixa tbody').on( 'click', 'tr', function () {
        const classeLinhaSelecionada = 'linha-selecionada-blue';
        const caixaAjax = $('.r-ajax-resumo-caixa-justificativas');

        if(!is_empty(ultimoAjaxJustificacoes)) ultimoAjaxJustificacoes.abort();
        ultimoAjaxJustificacoes = null;

        const hasClasse = $(this).hasClass(classeLinhaSelecionada);
        const formaPagamento = $(this).data('forma_pagamento');
        let dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
		let idFilial = "";
		if(is_empty(dataConciliacao, 1)) {
			dataConciliacao = $('.controla_modal').data('data_conciliacao');
			idFilial = $('.controla_modal').data('id_filial');
		}
        const url = $('.data_resumo_caixa').data('url_get_table_justificativas_resumo_caixa');

        $(this).parents('tbody').find('tr').removeClass(classeLinhaSelecionada);
        if(hasClasse){
            if(!$(caixaAjax).hasClass('ocultar')) $(caixaAjax).addClass('ocultar');
            $(caixaAjax).html('');

            return null;
        }

        $(this).addClass(classeLinhaSelecionada);
        $(caixaAjax).removeClass('ocultar');
        toggleLoadingOnDiv(caixaAjax, 1);

        ultimoAjaxJustificacoes = ajaxRequest(true, url, null, 'text',
            {
                'formaPagamento': formaPagamento,
                'dataConciliacao': dataConciliacao,
				'idFilial': idFilial
            }, function (ret) {

            $(caixaAjax).html(ret);
        })
    });
}

function somaTotaisFormasPagamento(tabelaFormaPagto){
    const trsTbodyFormaPagto = tabelaFormaPagto + " tbody tr";
    const trsTfootFormaPagto = tabelaFormaPagto + " tfoot tr";
    const casasPreco = $(".data_resumo_caixa").data("casas_preco");
    let valAuxiliarItem = 0;
    let valAuxiliarTotal = 0;

    const cifrao = $(".data_resumo_caixa").data("cifrao");
    const cifrao_is_prefixo = $(".data_resumo_caixa").data("cifrao_is_prefixo");
    const separador_decimal = $(".data_resumo_caixa").data("separador_decimal");
    const separador_milhar = $(".data_resumo_caixa").data("separador_milhar");

    $(trsTfootFormaPagto).find('td.total_pagamento').text("0");
    $(trsTfootFormaPagto).find('td.total_caixa').text("0");
    $(trsTfootFormaPagto).find('td.diferenca_caixa').text("0");
    $(trsTfootFormaPagto).find('td.justificado_caixa').text("0");

    $(trsTbodyFormaPagto).each(function() {
        valAuxiliarItem = stringParaFloat($(this).find('td.total_pagamento').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.total_pagamento').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.total_pagamento').text(valAuxiliarTotal + valAuxiliarItem);

        valAuxiliarItem = stringParaFloat($(this).find('td.total_caixa').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.total_caixa').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.total_caixa').text(valAuxiliarTotal + valAuxiliarItem);

        valAuxiliarItem = stringParaFloat($(this).find('td.diferenca_caixa').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.diferenca_caixa').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.diferenca_caixa').text(valAuxiliarTotal + valAuxiliarItem);

        valAuxiliarItem = stringParaFloat($(this).find('td.justificado_caixa').text().trim(), separador_decimal, true);
        valAuxiliarTotal = parseFloat($(trsTfootFormaPagto).find('td.justificado_caixa').text().trim().toString());
        if(is_empty_numeric(valAuxiliarItem)) valAuxiliarItem = 0;
        if(is_empty_numeric(valAuxiliarTotal)) valAuxiliarTotal = 0;
        $(trsTfootFormaPagto).find('td.justificado_caixa').text(valAuxiliarTotal + valAuxiliarItem);
    });

    $(trsTfootFormaPagto).find('td.total_pagamento').text(formataDecimal($(trsTfootFormaPagto).find('td.total_pagamento').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
    $(trsTfootFormaPagto).find('td.total_caixa').text(formataDecimal($(trsTfootFormaPagto).find('td.total_caixa').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
    $(trsTfootFormaPagto).find('td.diferenca_caixa').text(formataDecimal($(trsTfootFormaPagto).find('td.diferenca_caixa').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
    $(trsTfootFormaPagto).find('td.justificado_caixa').text(formataDecimal($(trsTfootFormaPagto).find('td.justificado_caixa').text().trim(), '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

selecionaLinhaFormaPagamentoResumoCaixa();
somaTotaisFormasPagamento(".itens-tabela-demonstrativa table#table-formas_pagamento-resumo-caixa");
somaTotaisFormasPagamento(".divisoria_impresssao table#conteudo-table-print");