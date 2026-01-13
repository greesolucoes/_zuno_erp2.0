function criaCostumizacoes() {
	$('.select-tipo_xml').select2Simple();
	$('.select-tipo_xml').data('init', '');

	$('.select-tipo_tabela').select2Simple();
	$('.select-tipo_tabela').data('init', '');
}

criaCostumizacoes();