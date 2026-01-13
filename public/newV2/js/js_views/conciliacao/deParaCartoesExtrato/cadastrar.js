function criaCostumizacoes() {
	$("select.select2_ajax").select2Ajax();
	$("select.select2_ajax").data('init', '');

	criaTodosSelects();
}

criaCostumizacoes();