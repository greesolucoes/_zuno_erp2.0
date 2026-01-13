var ultimoAjax = null;

/**
 * Function getCaixasEColaboradores
 * Pega as informações das caixas e colaboradores em tabela
 */
function getCaixasEColaboradores(bolOcultarOperadoresSemMov, bolMarcarCaixasComMov, bolMarcarCaixasConciliados) {
    if(!is_empty(ultimoAjax)) ultimoAjax.abort();
    ultimoAjax = null;

    if(!is_empty(bolOcultarOperadoresSemMov, 1)) {
    	bolOcultarOperadoresSemMov = 1;
	} else {
    	bolOcultarOperadoresSemMov = 0;
	}
    if(!is_empty(bolMarcarCaixasComMov, 1)) {
    	bolMarcarCaixasComMov = 1;
	} else {
    	bolMarcarCaixasComMov = 0;
	}
    if(!is_empty(bolMarcarCaixasConciliados, 1)) {
		bolMarcarCaixasConciliados = 1;
	} else {
		bolMarcarCaixasConciliados = 0;
	}

    var dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
    var url  = $(".data_turno_caixa").data('part_url_get_caixas_e_colaboradores');

    toggleLoadingOnDivSmall("#r-ajax-get-caixas-e-colaboradores", true);
    ultimoAjax = ajaxRequest(true, url, null, 'text', {
        'dataConciliacao': dataConciliacao,
        'bolOcultarOperadoresSemMov': bolOcultarOperadoresSemMov,
        'bolMarcarCaixasComMov': bolMarcarCaixasComMov,
        'bolMarcarCaixasConciliados': bolMarcarCaixasConciliados
    }, function(ret){
        $("#r-ajax-get-caixas-e-colaboradores").html(ret);
		if (!isOldLayout) {
			allTables();
		}
    });
}

function ativaTriggerGetCaixasEColaboradores(){
    let bolOcultarOperadoresSemMov = 0;
	let bolMarcarCaixasComMov = 0;
	let bolMarcarCaixasConciliados = 0;

    $('input#r-marcar-caixas-com-movimento').off('change');
    $('input#r-marcar-caixas-com-movimento').on("change", function() {
        bolOcultarOperadoresSemMov = 0;
        bolMarcarCaixasComMov = 0;
		bolMarcarCaixasConciliados = 0;
        if($(this).is(':checked')) bolMarcarCaixasComMov = 1;
        if($('input#r-ocultar-operadores-sem-movimento').is(':checked')) bolOcultarOperadoresSemMov = 1;
		if($('input#r-marcar-caixas-conciliados').is(':checked')) bolMarcarCaixasConciliados = 1;

        getCaixasEColaboradores(bolOcultarOperadoresSemMov, bolMarcarCaixasComMov, bolMarcarCaixasConciliados);
    });

    $('input#r-ocultar-operadores-sem-movimento').off('change');
    $('input#r-ocultar-operadores-sem-movimento').on("change", function() {
        bolOcultarOperadoresSemMov = 0;
        bolMarcarCaixasComMov = 0;
		bolMarcarCaixasConciliados = 0;
        if($(this).is(':checked')) bolOcultarOperadoresSemMov = 1;
        if($('input#r-marcar-caixas-com-movimento').is(':checked')) bolMarcarCaixasComMov = 1;
		if($('input#r-marcar-caixas-conciliados').is(':checked')) bolMarcarCaixasConciliados = 1;

        getCaixasEColaboradores(bolOcultarOperadoresSemMov, bolMarcarCaixasComMov, bolMarcarCaixasConciliados);
    });

    $('input#r-marcar-caixas-conciliados').off('change');
    $('input#r-marcar-caixas-conciliados').on("change", function() {
        bolOcultarOperadoresSemMov = 0;
        bolMarcarCaixasComMov = 0;
		bolMarcarCaixasConciliados = 0;
        if($(this).is(':checked')) bolMarcarCaixasConciliados = 1;
		if($('input#r-ocultar-operadores-sem-movimento').is(':checked')) bolOcultarOperadoresSemMov = 1;
        if($('input#r-marcar-caixas-com-movimento').is(':checked')) bolMarcarCaixasComMov = 1;

        getCaixasEColaboradores(bolOcultarOperadoresSemMov, bolMarcarCaixasComMov, bolMarcarCaixasConciliados);
    });

    bolOcultarOperadoresSemMov = 0;
    bolMarcarCaixasComMov = 0;
	bolMarcarCaixasConciliados = 0;
    if($('input#r-ocultar-operadores-sem-movimento').is(':checked')) bolOcultarOperadoresSemMov = 1;
    if($('input#r-marcar-caixas-com-movimento').is(':checked')) bolMarcarCaixasComMov = 1;
    if($('input#r-marcar-caixas-conciliados').is(':checked')) bolMarcarCaixasConciliados = 1;
    getCaixasEColaboradores(bolOcultarOperadoresSemMov, bolMarcarCaixasComMov, bolMarcarCaixasConciliados);
}

ativaTriggerGetCaixasEColaboradores();
