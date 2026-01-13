/**
 * Function guardaDateEPegaInformacoesDia
 * Guarda a Data selecionada no data-data_conciliacao da div#r-ajax-form para se o usuário voltar até essa pagina no ajax, a opção ja seja selecionada
 * Pega informações do dia para exibir ao lado no HTML
 */

function guardaDateEPegaInformacoesDia() {

	var ultimoAjax = null;

    $("#data_conciliacao[data-picker='calendar']").off('dp.change');
    $("#data_conciliacao[data-picker='calendar']").on('dp.change', function (e) {
        $('#r-ajax-form').data('data_conciliacao', $(this).val());

        if(!is_empty(ultimoAjax)) ultimoAjax.abort();
        ultimoAjax = null;

        var dataConciliacao = $(this).val();
        var url  = $(".data_dias_conciliacao").data('part_url_get_info_dates');

        toggleLoadingOnDivSmall("#r-info-dia-conciliacao", true);
        ultimoAjax = ajaxRequest(true, url, null, 'text', {
            'dataConciliacao': dataConciliacao
        }, function(ret){

            $("#r-info-dia-conciliacao").html(ret);
        });
    });

    $("#data_conciliacao[data-picker='calendar']").trigger('dp.change');
}

/**
 * Function geraPrimeiraData
 * Pega a data-data_conciliacao da div#r-ajax-form para o dateTimePicker selecionar o dia por padrão
 */
function geraPrimeiraData() {
    $("#data_conciliacao[data-picker='calendar']").val($('#r-ajax-form').data('data_conciliacao'));
}

/**
 * Function visualizaDiasEmStatusX
 * Realiza e cancela ações de visualizações de dias e seus status
 */
function visualizaDiasEmStatusX(){
    $("#r-visualizar-dias-para-aprovar").off('click');
    $("#r-visualizar-dias-para-aprovar").on('click', function (e) {
        carregaModalByBtn($(this), "#modal-visualiza-dias-com-status-x");
    });

    $("#r-visualizar-dias-rejeitados-sap").off('click');
    $("#r-visualizar-dias-rejeitados-sap").on('click', function (e) {
        carregaModalByBtn($(this), "#modal-visualiza-dias-com-status-x");
    });
}

geraPrimeiraData();
allFunctions();
guardaDateEPegaInformacoesDia();
visualizaDiasEmStatusX();
