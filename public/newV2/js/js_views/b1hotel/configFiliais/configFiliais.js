/**
 * Function criaSelectsFiliaisConfig
 * Cria o Select2 de todos os selects da página e altera o data-init deles para nada
 * (pode ocasionar problemas se não for feito isso após a 1 chamada do select 2)
 */
function criaSelectsFiliaisConfig() {
	$('.select_pms').select2Ajax();
	$('.select_pms').data('init', '');
}

criaSelectsFiliaisConfig();
controlaTabelaSuite();