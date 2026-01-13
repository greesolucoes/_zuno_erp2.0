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
		var finalUrl = dataSelecionada + "/" + dataParaSelecionada;

		$('#dataTableLogImportacao').DataTable().destroy();
		$('#dataTableLogImportacao').data("url_ajax", $(".datas_views").data("url_search_carregar") + finalUrl);
		allTables();
	});
}
carregar();