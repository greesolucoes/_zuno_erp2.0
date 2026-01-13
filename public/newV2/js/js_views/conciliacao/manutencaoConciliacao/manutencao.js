/**
 * Function guardaDateEPegaInformacoesDia
 * Pega as ações disponíveis para o dia selecionado da conciliação
 */
function getAcoesDoDiaSelecionado() {
    var ultimoAjax = null;

    $("#data_conciliacao[data-picker='calendar']").off('dp.change');
    $("#data_conciliacao[data-picker='calendar']").on('dp.change', function (e) {
        var dataConc = $(this).val();

        if(!is_empty(ultimoAjax)) ultimoAjax.abort();
        ultimoAjax = null;

        var url  = $(".data_views").data('url_get_acoes_dia');

        toggleLoadingOnDivSmall("#acoes-manutencao", true);
        ultimoAjax = ajaxRequest(true, url, null, 'text', {
            'dataConciliacao': dataConc
        }, function(ret){
            $("#acoes-manutencao").html(ret);
        });
    });

    $("#data_conciliacao[data-picker='calendar']").trigger('dp.change');
}

/**
 * Function visualizaDiasEmStatusX
 * Realiza e cancela ações de visualizações de dias e seus status
 */
function visualizaDiasEmStatusX(){
    $("#m-visualizar-dias-para-aprovar").off('click');
    $("#m-visualizar-dias-para-aprovar").on('click', function (e) {
        carregaModalByBtn($(this), "#modal-visualiza-dias-com-status-x");
    });

    $("#m-visualizar-dias-rejeitados-sap").off('click');
    $("#m-visualizar-dias-rejeitados-sap").on('click', function (e) {
        carregaModalByBtn($(this), "#modal-visualiza-dias-com-status-x");
    });
}

getAcoesDoDiaSelecionado();
visualizaDiasEmStatusX();