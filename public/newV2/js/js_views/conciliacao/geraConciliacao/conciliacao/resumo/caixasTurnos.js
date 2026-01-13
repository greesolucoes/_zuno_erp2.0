var ultimoAjaxResumoCaixaTurnos = null;
var modalPagCaixaTurnos         = $('#r-modal-resumo-caixasTurnos');
var pagamentosAjaxResumo        = $(modalPagCaixaTurnos).find('#r-ajax-pagamento_por_caixa-resumo-caixas-turnos');

/**
 * Function selecionaCaixaFormaPagamentoResumoCaixasTurnos.
 * Abre modal de pagamentos especificos de uma caixa de um turno ou colaborador
 */
function selecionaCaixaFormaPagamentoResumoCaixasTurnos() {
    const divCaixas = $('.itens-tabela-demonstrativa-resumo-caixa');
    const btnDivCaixas = $(divCaixas).find('button.btn-caixa');

    $(btnDivCaixas).unbind('click');
    $(btnDivCaixas).on( 'click', function () {
        $(modalPagCaixaTurnos).modal('show');

        if(!is_empty(ultimoAjaxResumoCaixaTurnos)) ultimoAjaxResumoCaixaTurnos.abort();
        ultimoAjaxResumoCaixaTurnos = null;

        const caixa = $(this).data('id_caixa');
        const colaboradorPeriodo = $(this).data('colaborador_periodo');
        let dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
        let idFilial = "";
        if(is_empty(dataConciliacao, 1)) {
			dataConciliacao = $('.controla_modal').data('data_conciliacao');
			idFilial = $('.controla_modal').data('id_filial');
		}
        const url = $('.data_resumo_caixas_turnos').data('url_get_pagamentos_por_caixa');

        toggleLoadingOnDiv(pagamentosAjaxResumo, 1);
        ultimoAjaxResumoCaixaTurnos = ajaxRequest(true, url, null, 'text',
            {
                'caixa': caixa,
                'colaborador_periodo': colaboradorPeriodo,
                'dataConciliacao': dataConciliacao,
                'idFilial': idFilial
            }, function (ret) {
                $(pagamentosAjaxResumo).html(ret);
            })
    });
}

/**
 * Function onDismissModalResumoCaixasTurnos
 * Ao fechar modal, excluir informações do mesmo
 */
function onDismissModalResumoCaixasTurnos() {
    $('#r-modal-pagamentos-conciliacao').unbind('hidden.bs.modal');
    $('#r-modal-pagamentos-conciliacao').on('hidden.bs.modal', function () {
        if(!is_empty(ultimoAjaxResumoCaixaTurnos)) ultimoAjaxResumoCaixaTurnos.abort();
        ultimoAjaxResumoCaixaTurnos = null;

        $(pagamentosAjaxResumo).html('');
    })
}

selecionaCaixaFormaPagamentoResumoCaixasTurnos();
onDismissModalResumoCaixasTurnos();