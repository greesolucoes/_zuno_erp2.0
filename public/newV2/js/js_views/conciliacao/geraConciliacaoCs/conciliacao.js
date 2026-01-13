var bolIsLiberado = true;

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
	var faseMaxima   = 2;

	var ajaxForm         = $("#r-ajax-form");
	var btnAnteriorEtapa = $("button#r-etapa-anterior-conciliacao");
	var btnProximaEtapa  = $("button#r-proxima-etapa-conciliacao");

	if(is_empty(bolIsLiberado, 1)) return null;
	bolIsLiberado = false;

	if(is_empty_numeric(faseAgora)) faseAgora = 0;

	var faseFuturo = 0;
	if(is_empty(bolProximaFase, 1)) faseFuturo = faseAgora - 1;
	else                                       faseFuturo = faseAgora + 1;

	if(faseFuturo < faseMinima) {
		bolIsLiberado = true;
		return null;
	}

	$(btnProximaEtapa).prop('disabled', true);
	$(btnAnteriorEtapa).prop('disabled', true);

	toggleLoadingOnDiv($(ajaxForm).find('.loading-form'));
	$(ajaxForm).find('.conteudo-form').addClass('ocultar');
	$(ajaxForm).removeClass('informacoes_add');

	ajaxRequest(true, url, null, 'text', {
		'dataConciliacao': dataConciliacao,
		'fase': faseFuturo,
		'bolProximaFase': bolProximaFase ? 1 : 0
	}, function(ret){
		// console.log(ret);
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
			$(btnProximaEtapa).addClass('ocultar');
			if(!is_empty(ret['desativarProximo'], 1)) $(btnProximaEtapa).prop('disabled', true);
			else                                                 $(btnProximaEtapa).prop('disabled', false);
		} else {
			$(btnProximaEtapa).removeClass('ocultar');
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
		case 2: //Resumo
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