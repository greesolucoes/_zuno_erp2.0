/**
 * Function criaSelectsConfigs
 * Cria os select2 da página e setta o data-init deles para nulo, suprimindo possiveis erros
 */
function criaSelectsConfigs() {
    $(".select_justificativa").select2Ajax();
    $(".select_justificativa").data('init', '');
}

/**
 * Function exibeJustificativaPadrao
 * Define se exibirá o select de justificativa padrão na tela
 */
function exibeJustificativaPadrao() {
    $('input#usar_justificativa_padrao').unbind('change');
    $('input#usar_justificativa_padrao').on("change", function() {
        if(this.checked) {
            $('#justificativa_padrao_form').removeClass('ocultar');
        } else {
            $('#justificativa_padrao_form').addClass('ocultar');
        }
    });

    $('input#usar_justificativa_padrao').trigger('change');
}

/**
 * Function habilitaAgrupamentoTurno
 * Habilita para marcar a opção de agrupamento de turnos
 */
function habilitaAgrupamentoTurno(){
    $('input#conciliar_movimento_por_operador_caixa').unbind('change');
    $('input#conciliar_movimento_por_operador_caixa').on("change", function() {
		checaDependeciaFlags(this,"gerenciar_turno_unico");
    });
	$('input#gerenciar_turno_unico').unbind('change');
	$('input#gerenciar_turno_unico').on("change", function() {
		checaDependeciaFlags(this,"conciliar_movimento_por_operador_caixa");
	});

    $('input#conciliar_movimento_por_operador_caixa').trigger('change');
    $('input#gerenciar_turno_unico').trigger('change');
}


function criaEventosToFlags(){
	$('input#geral-bol-permitir-criacao-de-nfentrada-servico').unbind('change');
	$('input#geral-bol-permitir-criacao-de-nfentrada-servico').on("change", function() {
		checaDependeciaFlags(this,"geral-bol_habilitar_leitura_retroativa_nfs_ws_sp",'conjuntas');
	});
	$('input#geral-bol-permitir-criacao-de-nfentrada-servico').change();
}

criaSelectsConfigs();
exibeJustificativaPadrao();
habilitaAgrupamentoTurno();
criaEventosToFlags();