var isJustificativa = $(".data_diferencas_encontradas").data('is_justificativa');
var ultimoAjaxModalJustificativas = null;

/**
 * Function ativaTriggerBtnPagamentoClickConciliar
 * Concilia uma forma de pagamento
 * @param pagamentoId
 * @param dataConciliacao
 * @param tr
 */
function ativaTriggerBtnPagamentoClickConciliar(pagamentoId, dataConciliacao, tr){
    var url = $(".data_diferencas_encontradas").data('part_url_conciliar_pagamento');

    swal({
        title: l["conciliar"],
        text: l["continuarComOProcesso?"],
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: l['conciliar!'],
        cancelButtonText: l["cancelar!"]
    }).then(function () {
        toggleLoading();
        ajaxRequest(true, url, null, 'text', {
            'pagamentoId': pagamentoId,
            'dataConciliacao': dataConciliacao
        }, function (ret) {
            try{
                ret = JSON.parse(ret);
                if(!is_empty(ret['bol'], 1)) {
                    $(tr).find('td.total-justificado').text($(tr).find('td.diferenca').text());
                    ativaCorEmLinhasDasDiferencas();
                }

                swal(
                    ret['titulo'],
                    ret['text'],
                    ret['class']
                ).catch(swal.noop);

                toggleLoading();
            }catch(err){
                swal(
                    l["erro!"],
                    l["tempoDeRespostaDoServidorEsgotado!"],
                    "error"
                ).catch(swal.noop);
                forceToggleLoading(0);
                // consoleSystem(err, 'error');
            }
        });
    }).catch(swal.noop);
}

/**
 * Function ativaTriggerBtnPagamentoClickJustificativa
 * Ativa triggers para ao clicar uma vez no botão do pagamento, abrir pop-up
 * @param pagamentoId
 * @param dataConciliacao
 */
function ativaTriggerBtnPagamentoClickJustificativa(pagamentoId, dataConciliacao) {
    var url = $(".data_diferencas_encontradas").data('part_url_get_justificativas');

    if(!is_empty(ultimoAjaxModalJustificativas)) ultimoAjaxModalJustificativas.abort();
    ultimoAjaxModalJustificativas = null;

    $('#r-modal-justificativa-diferenca .data_modal_justificativa_diferenca').data('pagamento_id', pagamentoId);
    $('#r-modal-justificativa-diferenca').modal('show');

    toggleLoadingOnDivSmall($('#r-modal-justificativa-diferenca #r-ajax-modal-body'), true);
    ultimoAjaxModalJustificativas = ajaxRequest(true, url, null, 'text', {
        'dataConciliacao': dataConciliacao,
        'pagamentoId': pagamentoId
    }, function(ret){
        $("#r-modal-justificativa-diferenca #r-ajax-modal-body").html(ret);
    });
}

/**
 * Function ativaTriggerBtnPagamentoClickJustificarOuConciliar
 * Ativa triggers para justificar ou conciliar pagamentos
 */
function ativaTriggerBtnPagamentoClickJustificarOuConciliar() {
    $(".r-justificar-diferenca-pagamento").off('click');
    $(".r-justificar-diferenca-pagamento").on('click', function (e) {
        var dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
        var pagamentoId     = $(this).parents('tr').data('pagamento_id');

        if(!is_empty(isJustificativa, 1)) {
            ativaTriggerBtnPagamentoClickJustificativa(pagamentoId, dataConciliacao);
            onDismissModalJustificativas();
            saveModalJustificativas($(this).parents('tr'));
            return null;
        }

        ativaTriggerBtnPagamentoClickConciliar(pagamentoId, dataConciliacao, $(this).parents('tr'));
    });
}

/**
 * Function onDismissModalJustificativas
 * Ao fechar modal, excluir informações do mesmo
 */
function onDismissModalJustificativas() {
    if(is_empty(isJustificativa, 1)) return null;

    $('#r-modal-justificativa-diferenca').unbind('hidden.bs.modal');
    $('#r-modal-justificativa-diferenca').on('hidden.bs.modal', function () {
        if(!is_empty(ultimoAjaxModalJustificativas)) ultimoAjaxModalJustificativas.abort();
        ultimoAjaxModalJustificativas = null;

        $(this).find('.data_modal_justificativa_diferenca').data('pagamento_id', '');

        $(this).find('#r-ajax-modal-body').html('');
    })
}

/**
 * Function saveModalJustificativas
 * Salva as alterações do modal - Não completo
 * @param tr
 */
function saveModalJustificativas(tr){
    if(is_empty(isJustificativa, 1)) return null;

    $("#r-modal-justificativa-diferenca .modal-footer button#r-salvar-justificativas").off('click');
    $("#r-modal-justificativa-diferenca .modal-footer button#r-salvar-justificativas").on('click', function (e) {
        e.preventDefault();

        var objModal         = $(this).parents('#r-modal-justificativa-diferenca');
        var linhas           = $(objModal).find("table#r-tabela-justificativas-diferencas tbody tr:not(.ocultar)");
        var pagamentoId      = $(objModal).find('.data_modal_justificativa_diferenca').data('pagamento_id');
        var justificativas   = [];
        var casasPreco       = $(".data_diferencas_encontradas").data('casas_preco');
        var valorTotalLinhas = 0;

        var cifrao = $(".data_diferencas_encontradas").data("cifrao");
        var cifrao_is_prefixo = $(".data_diferencas_encontradas").data("cifrao_is_prefixo");
        var separador_decimal = $(".data_diferencas_encontradas").data("separador_decimal");
        var separador_milhar = $(".data_diferencas_encontradas").data("separador_milhar");

        if(!is_empty(linhas, 1)){
            $.each(linhas, function (idLinha, linha) {
                var valorLinha    = stringParaFloat($(linha).find('.r-valor-justificativas').val().trim(), separador_decimal, true);
                if(is_empty_numeric(valorLinha)) valorLinha = 0;

                var justificativa      = $(linha).find('.r-select_justificativa-diferencas').val();
                if(!is_empty(justificativa, 1)) justificativa.trim();
                var textoJustificativa = $(linha).find('.r-descricao-justificativas').val();
                if(!is_empty(textoJustificativa, 1)) textoJustificativa.trim();
                var clienteJustificativa = $(linha).find('.r-ref-cliente-justificativas').val();
                if(!is_empty(clienteJustificativa, 1)) clienteJustificativa.trim();
                var contaDebito = $(linha).find('.r-ref-conta-debito-justificativas').val();
                if(!is_empty(contaDebito, 1)) contaDebito.trim();
                var contaCredito = $(linha).find('.r-ref-conta-credito-justificativas').val();
                if(!is_empty(contaCredito, 1)) contaCredito.trim();

                if(!is_empty_numeric(valorLinha)){
                    valorTotalLinhas += valorLinha;

                    justificativas.push({
                        'valorLinha': valorLinha,
                        'justificativa': justificativa,
                        'textoJustificativa': textoJustificativa,
                        'clienteJustificativa': clienteJustificativa,
                        'contaDebito': contaDebito,
                        'contaCredito': contaCredito
                    });
                }
            });
        }

        var dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
        var url             = $(".data_diferencas_encontradas").data('part_url_save_justificativas');

        swal({
            title: l["salvarJustificativas?"],
            text: l["temCertezaDeQueDesejaSalvarAsJustificativas?"],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l['salvar!'],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {
                'pagamentoId': pagamentoId,
                'justificativas': justificativas,
                'dataConciliacao': dataConciliacao
            }, function (ret) {
                try{
                    ret = JSON.parse(ret);
                    if(!is_empty(ret['bol'], 1)) {
                        $(objModal).modal('hide');
                        $(tr).find('td.total-justificado').text(formataDecimal(valorTotalLinhas, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
                        ativaCorEmLinhasDasDiferencas();
                    }

                    swal(
                        ret['titulo'],
                        ret['text'],
                        ret['class']
                    ).catch(swal.noop);

                    toggleLoading();
                }catch(err){
                    swal(
                        l["erro!"],
                        l["tempoDeRespostaDoServidorEsgotado!"],
                        "error"
                    ).catch(swal.noop);
                    forceToggleLoading(0);
                    // consoleSystem(err, 'error');
                }
            });
        }).catch(swal.noop);
    });
}

function aplicarJustificativaPadraoDiferencasEncontradas(){
    if(is_empty(isJustificativa, 1)) return null;

    $("button.r-justificar-padrao-diferencas_encontradas").off('click');
    $("button.r-justificar-padrao-diferencas_encontradas").on('click', function (e) {
        e.preventDefault();

        var dataConciliacao         = $('#r-ajax-form').data('data_conciliacao');
        var urlUsarJustificativaPad = $(".data_diferencas_encontradas").data('url_usar_justificativa_padrao');

        swal({
            title: l["usarJustificativaPadrão?"],
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l['sim!'],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, urlUsarJustificativaPad, null, 'text', {
                'dataConciliacao': dataConciliacao
            }, function (ret) {
                try{
                    ret = JSON.parse(ret);
                    if(!is_empty(ret['bol'], 1)) recarregaEtapaConciliacao(4);

                    swal(
                        ret['titulo'],
                        ret['text'],
                        ret['class']
                    ).catch(swal.noop);

                    toggleLoading();
                }catch(err){
                    swal(
                        l["erro!"],
                        l["tempoDeRespostaDoServidorEsgotado!"],
                        "error"
                    ).catch(swal.noop);
                    forceToggleLoading(0);
                }
            });
        }).catch(swal.noop);
    });
}

function ativaCorEmLinhasDasDiferencas(){
    var lines = $('table tbody tr.linha_diferencas_encontradas');
    var separador_decimal = $(".data_diferencas_encontradas").data("separador_decimal");

    $.each(lines, function (indexTr, linha) {
        $(this).removeClass('linha-vermelha');
        $(this).removeClass('linha-verde');

        var valorJustificado = stringParaFloat($(this).find('.total-justificado').text().trim(), separador_decimal, true);
        var valorDiff        = stringParaFloat($(this).find('.diferenca').text().trim(), separador_decimal, true);
        if(is_empty_numeric(valorJustificado)) valorJustificado = 0;
        if(is_empty_numeric(valorDiff)) valorDiff = 0;

        if(valorJustificado !== valorDiff) $(this).addClass('linha-vermelha');
        else                               $(this).addClass('linha-verde');
    });
}

ativaTriggerBtnPagamentoClickJustificarOuConciliar();
aplicarJustificativaPadraoDiferencasEncontradas();
ativaCorEmLinhasDasDiferencas();
