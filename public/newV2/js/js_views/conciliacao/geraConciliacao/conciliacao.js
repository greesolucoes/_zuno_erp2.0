var bolIsLiberado = true;
var updateUltimoUso = {
    timeout: null,
    ajax: null
};

/**
 * Function updateUltimoUsoEUsuarioDia.
 * A cada 30 seg. pergunto ao server se o usuário ainda está na página
 */
function updateUltimoUsoEUsuarioDia() {
    var urlUp   = $('.data_views').data('url_update_dia_conc');
    var urlThis = $('.data_views').data('url_this');

    var data      = $("#r-ajax-form").data('data_conciliacao');
    var faseAgora = $("#r-ajax-form").data('fase');

    updateUltimoUso['ajax'] = ajaxRequest(true, urlUp, null, 'text', {
        'dataConciliacao': data,
        'faseAgora': faseAgora
    }, function(ret){
		if(is_empty(ret, 1)) $.redirect(urlThis, {...tokenCsrf});

		updateUltimoUso['timeout'] = setTimeout(function () {
			updateUltimoUsoEUsuarioDia();
		}, 20000);
    });
}

/**
 * Function padraoEtapaConciliacao
 * Função de padrões para as etapas de conciliação
 * @param faseAgora       Fase do dia (À Receber, Mov. Caixa e etc..)
 * @param bolProximaFase  Verdadeiro se for a próxima fase, falso se for a fase anterior
 * @returns {null}
 */
function padraoEtapaConciliacao(faseAgora, bolProximaFase) {
    var dataConciliacao = $("#r-ajax-form").data('data_conciliacao');
    var url             = $(".data_views").data('part_url_util');
    var dados           = getDadosFaseConc(faseAgora);

    var faseMinima   = 1;
    var faseMaxima   = 10;

    var ajaxForm         = $("#r-ajax-form");
    var btnAnteriorEtapa = $("button#r-etapa-anterior-conciliacao");
    var btnProximaEtapa  = $("button#r-proxima-etapa-conciliacao");

    if(is_empty(bolIsLiberado, 1)) return null;
    bolIsLiberado = false;

    if(is_empty_numeric(faseAgora)) faseAgora = 0;
	
    var faseFuturo = 0;
    if(is_empty(bolProximaFase, 1)) faseFuturo = faseAgora - 1;
    else                            faseFuturo = faseAgora + 1;

    if(faseFuturo < faseMinima) {
        bolIsLiberado = true;
        return null;
    }

    $(btnProximaEtapa).prop('disabled', true);
    $(btnAnteriorEtapa).prop('disabled', true);

    if(faseFuturo > faseMaxima){
        //ATIVA O LOADING
        toggleLoading();
        //ATIVA O LOADING

        //LIMPA O TIMEOUT E O AJAX DO UPDATE DO ULTIMO USO
        if(!is_empty(updateUltimoUso['timeout'])){
            clearTimeout(updateUltimoUso['timeout']);
        }
        if(!is_empty(updateUltimoUso['ajax'], 1)) {
            updateUltimoUso['ajax'].abort();
            updateUltimoUso['ajax'] = null;
        }
        //LIMPA O TIMEOUT E O AJAX DO UPDATE DO ULTIMO USO

        //FINALIZA VIA AJAX E FAZ O REDIRECT PARA A MESMA PÁGINA
        var urlThis = $('.data_views').data('url_this');
        var urlFinalizar = $('.data_views').data('url_finalizar_dia');
        ajaxRequest(true, urlFinalizar, null, 'text', {
            'dataConciliacao': dataConciliacao
        }, function(ret){
            ret = $.parseJSON(ret);
            if(!is_empty(ret['bol'], 1)){
				$.redirect(urlThis, {...tokenCsrf});
            } else {
                toggleLoading();

                swal(
                    l["erro!"],
                    ret['error'],
                    "error"
                ).catch(swal.noop);

                $(btnProximaEtapa).prop('disabled', false);
                $(btnAnteriorEtapa).prop('disabled', false);

                updateUltimoUsoEUsuarioDia();
                bolIsLiberado = true;
            }
        });
        //FINALIZA VIA AJAX E FAZ O REDIRECT PARA A MESMA PÁGINA

        //PARA A EXECUÇÃO DO SCRIPT
        return null;
        //PARA A EXECUÇÃO DO SCRIPT
    }

    toggleLoadingOnDiv($(ajaxForm).find('.loading-form'));
    $(ajaxForm).find('.conteudo-form').addClass('ocultar');
    $(ajaxForm).removeClass('informacoes_add');

    ajaxRequest(true, url, null, 'text', {
        'dataConciliacao': dataConciliacao,
        'fase': faseFuturo,
        'faseSalvar': faseAgora,
        'dados': dados,
        'bolProximaFase': bolProximaFase ? 1 : 0
    }, function(ret){
        ret = $.parseJSON(ret);

        if(!is_empty(ret['bol'], 1)){
            faseFuturo = parseInt(ret['faseFuturo']);
            $(ajaxForm).data('fase', faseFuturo);
            $(ajaxForm).find('.conteudo-form').html(ret['html']);
        }else{
            faseFuturo = faseAgora;
            swal(
                l["erroDaRequisição"],
                ret['html'],
                "error"
            ).catch(swal.noop);
        }

        if(faseFuturo >= faseMaxima) {
            $(btnProximaEtapa).html(l['finalizar']);
            if(!is_empty(ret['desativarProximo'], 1)) $(btnProximaEtapa).prop('disabled', true);
            else                                      $(btnProximaEtapa).prop('disabled', false);
        } else {
            $(btnProximaEtapa).html(`${isOldLayout ? '<i class="fa fa-arrow-right" aria-hidden="true"></i>' : ''}` + l['próximo']);
            $(btnProximaEtapa).prop('disabled', false);
        }

        $(ajaxForm).find('.conteudo-form').removeClass('ocultar');
        $(ajaxForm).find('.loading-form').html('');

        if(faseFuturo <= 1) $(btnAnteriorEtapa).prop('disabled', true);
        else                $(btnAnteriorEtapa).prop('disabled', false);

        bolIsLiberado = true;
        $(ajaxForm).addClass('informacoes_add');
    });
}

/**
 * Function voltaEtapaConciliacao
 * Função para trazer o HTML anterior das etapas da conciliação
 */
function voltaEtapaConciliacao() {
    $("button#r-etapa-anterior-conciliacao").off('click');
    $("button#r-etapa-anterior-conciliacao").on("click", function (){
        var faseAgora       = $("#r-ajax-form").data('fase');
        padraoEtapaConciliacao(faseAgora, false);
    });
}

/**
 * Function proximaEtapaConciliacao
 * Função para trazer o próximo HTML das etapas da conciliação
 */
function proximaEtapaConciliacao() {
    $("button#r-proxima-etapa-conciliacao").off('click');
    $("button#r-proxima-etapa-conciliacao").on("click", function (){
        var faseAgora       = $("#r-ajax-form").data('fase');
        padraoEtapaConciliacao(faseAgora, true);
    });
}

function getDadosFaseConc(faseAgora) {
    var dados = [];

    switch(faseAgora){
        case 1://Escolha da data
            break;
        case 2: //Mov. Caixa
            //GRAVA TUDO, POIS IREI RETORNAR UM ERRO NA VALIDAÇÃO SE ALGO DER ERRADO
            $("table#r-tabela-movimento-caixa tbody tr:not(.ocultar)").each(function () {
                dados.push({
                    'tipo':      $(this).find('td select.r-select_tipo-movimento-caixa').val(),
                    'descricao': $(this).find('td input.r-descricao-movimento-caixa').val(),
                    'valor':     $(this).find('td input.r-valor-movimento-caixa').val()
                });
            });

            break;
        case 3: //Turno/Operador/Caixa
            break;
        case 4: //Diferenças encontradas
            break;
        case 5: //Permutas
            //GRAVA TUDO, POIS IREI RETORNAR UM ERRO NA VALIDAÇÃO SE ALGO DER ERRADO
            $("table#r-tabela-permuta tbody tr:not(.ocultar)").each(function () {
                dados.push({
                    'parceiro':  $(this).find('td input.r-text_parceiro-permuta').val(),
                    'origem': $(this).find('td input.r-descricao-permuta').val(),
                    'valor':     $(this).find('td input.r-valor-permuta').val()
                });
            });

            break;
        case 6: //Convites
            //GRAVA TUDO, POIS IREI RETORNAR UM ERRO NA VALIDAÇÃO SE ALGO DER ERRADO
            $("table#r-tabela-convite tbody tr:not(.ocultar)").each(function () {
                dados.push({
                    'numero': $(this).find('td input.r-numero-convite').val(),
                    'origem': $(this).find('td input.r-descricao-convite').val(),
                    'valor':  $(this).find('td input.r-valor-convite').val()
                });
            });

            break;
        case 7: //À Receber
            //GRAVA TUDO, POIS IREI RETORNAR UM ERRO NA VALIDAÇÃO SE ALGO DER ERRADO
            $("table#r-tabela-a-receber tbody tr:not(.ocultar)").each(function () {
                dados.push({
                    'parceiro':  $(this).find('td input.r-text_parceiro-a-receber').val(),
                    'descricao': $(this).find('td input.r-descricao-a-receber').val(),
                    'valor':     $(this).find('td input.r-valor-a-receber').val()
                });
            });

            break;
        case 8: //Cheque
            //GRAVA TUDO, POIS IREI RETORNAR UM ERRO NA VALIDAÇÃO SE ALGO DER ERRADO
            $("table#r-tabela-cheque tbody tr:not(.ocultar)").each(function () {
                dados.push({
                    'pais':         $(this).find('td select.r-select_pais-cheque').val(),
                    'banco':        $(this).find('td select.r-select_banco-cheque').val(),
                    'filial':       $(this).find('td input.r-filial-cheque').val(),
                    'conta':        $(this).find('td input.r-conta-cheque').val(),
                    'numeroCheque': $(this).find('td input.r-num_cheque-cheque').val(),
                    'vencimento':   $(this).find('td input.r-vencimento-cheque').val(),
                    'valor':        $(this).find('td input.r-valor-cheque').val(),
                    'endossado':    !is_empty($(this).find('td input.r-endossado-cheque').is(':checked'), 1) ? 1 : 0
                });
            });

            break;
		case 9: //Anexos
			dados.push(
				formToStringJson('#form-anexos')
			);
			break;
        case 10: //Resumo
            break;
    }

    return dados;
}

/**
 * Function recarregaEtapaConciliacao
 * Função para trazer o HTML de uma etapa especifica da conciliação
 * @param etapa Etapa para recarregar
 */
function recarregaEtapaConciliacao(etapa) {
    if(is_empty_numeric(etapa)) etapa = 0;
    padraoEtapaConciliacao((etapa - 1), true);
}

voltaEtapaConciliacao();
proximaEtapaConciliacao();
recarregaEtapaConciliacao(1);
updateUltimoUsoEUsuarioDia();