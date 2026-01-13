function criaSelects() {
	// $("#tipo_documento").select2Ajax();
	// $("#tipo_documento").data('init', '');
	$("#tipo_documento").select2Simple('Tipo de Documento');
	$("#tipo_documento").data('init', '');
	$("#severidade").select2Simple(l['Severity']);
	$("#severidade").data('init', '');
}

function formatDate(date) {
	var dia  = date.split("/")[0];
	var mes  = date.split("/")[1];
	var ano  = date.split("/")[2];

	return ano + '-' + ("0"+mes).slice(-2) + '-' + ("0"+dia).slice(-2);
}

function carregar() {
	$('#buscar').unbind('click');
	$('#buscar').on("click", function (e) {
		var dataSelecionada = is_empty($('#dataSelecionada').val(), 1) ? 0 : formatDate($('#dataSelecionada').val());
		var dataParaSelecionada = is_empty($('#dataParaSelecionada').val(), 1) ? 0 : formatDate($('#dataParaSelecionada').val());
		var tipoDocumento = is_empty($('#tipo_documento').val(), 1) ? 0 : $('#tipo_documento').val();
		var severidade = is_empty($('#severidade').val(), 1) ? 0 : $('#severidade').val();
		var finalUrl = dataSelecionada + "/" + dataParaSelecionada + "/" + tipoDocumento + "/" + severidade;

		$('#dataTableLogImportacao').DataTable().destroy();
		$('#dataTableLogImportacao').data("url_ajax", $(".datas_views").data("url_search_carregar") + finalUrl);
		allTables();
	});
}

carregar();
criaSelects();