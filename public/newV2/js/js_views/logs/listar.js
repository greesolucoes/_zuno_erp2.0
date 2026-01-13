$('#buscar').unbind('click');
$('#buscar').on("click", function (e) {
	var dataInicial 	= is_empty($('#dataInicial').val(), 1) ? 0 : dateBrToDate($('#dataInicial').val());
	var dataFinal 		= is_empty($('#dataFinal').val(), 1) ? 0 : dateBrToDate($('#dataFinal').val());
	var usuario 		= is_empty($('#usuario').val(), 1) ? 0 : $('#usuario').val();
	var modulo 			= is_empty($('#modulo').val(), 1) ? 0 : $('#modulo').val();
	var tela 			= is_empty($('#tela').val(), 1) ? 0 : $('#tela').val();
	var palavraChave 	= is_empty($('#palavraChave').val(), 1) ? 0 : $('#palavraChave').val();
	var idPortal 		= is_empty($('#idPortal').val(), 1) ? 0 : $('#idPortal').val();

	if(is_empty(dataInicial, 1) || is_empty(dataFinal, 1)){
		swal(
			l["erro!"],
			l["informeADataInicialEADataFinal"],
			"error"
		);
		$('.table-exibe').data("url_ajax", "");
	} else {
		var finalUrl 		=
			dataInicial 						+ "/" +
			dataFinal							+ "/" +
			encodeURIComponent(usuario) 		+ "/" +
			encodeURIComponent(modulo) 			+ "/" +
			encodeURIComponent(tela) 			+ "/" +
			encodeURIComponent(palavraChave) 	+ "/" +
			encodeURIComponent(idPortal);


		$('.table-exibe').DataTable().destroy();
		$('.table-exibe').data("url_ajax", $(".data_views").data("url_search") + finalUrl);
		allTables();
	}

});

$('.gerarRelatorio').off('click');
$('.gerarRelatorio').on('click', function (){
	let url = $(".data_views").data("url_gerar_relatorio");
	let obj = {
		'dataInicial': 		is_empty($('#dataInicial').val(), 1) ? 0 : dateBrToDate($('#dataInicial').val()),
		'dataFinal': 		is_empty($('#dataFinal').val(), 1) ? 0 : dateBrToDate($('#dataFinal').val()),
		'usuario': 			is_empty($('#usuario').val(), 1) ? 0 : $('#usuario').val(),
		'modulo': 			is_empty($('#modulo').val(), 1) ? 0 : $('#modulo').val(),
		'tela': 			is_empty($('#tela').val(), 1) ? 0 : $('#tela').val(),
		'palavraChave': 	is_empty($('#palavraChave').val(), 1) ? 0 : $('#palavraChave').val(),
		'idPortal': 		is_empty($('#idPortal').val(), 1) ? 0 : $('#idPortal').val()
	}

	if(is_empty(obj.dataInicial, 1) || is_empty(obj.dataFinal, 1)){
		swal(
			l["erro!"],
			l["informeADataInicialEADataFinal"],
			"error"
		);
	} else {
		$.redirect(url, obj, "GET", "_blank");
	}

});