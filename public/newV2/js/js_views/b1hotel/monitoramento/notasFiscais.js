function criaSelects() {
	$("#status").select2Simple();
	$("#status").data('init', '');

	$("#statusContasReceber").select2Simple();
	$("#statusContasReceber").data('init', '');

	$("#filialSelecionada").select2Simple();
	$("#filialSelecionada").data('init', '');

	$("#documentos").select2Simple();
	$("#documentos").data('init', '');
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
		var dataInicial = is_empty($('#dataInicial').val(), 1) ? 0 : formatDate($('#dataInicial').val());
		var dataFinal = is_empty($('#dataFinal').val(), 1) ? 0 : formatDate($('#dataFinal').val());
		var documentos = is_empty($('#documentos').val(), 1) ? 0 : $('#documentos').val();
		var filial = is_empty($('#filialSelecionada').val(), 1) ? 0 : $('#filialSelecionada').val();
		var status = is_empty($('#status').val(), 1) ? 0 : $('#status').val();
		var statusContasReceber = is_empty($('#statusContasReceber').val(), 1) ? 0 : $('#statusContasReceber').val();
		var finalUrl = dataInicial + "/" + dataFinal + "/" + documentos + "/" + filial + "/" + status + "/" + statusContasReceber;

		$('#dataTableDocumentos').DataTable().destroy();
		$('#dataTableDocumentos').data("url_ajax", $(".datas_views").data("url_search_carregar") + finalUrl);
		allTables();
	});
}

function reprocessarNota(){
	$('.reprocessarNota').off("click");
	$('.reprocessarNota').on("click", function(e){
		e.preventDefault();
		var obj  = $(this);
		var url  = $(obj).data('url');
		var id   = $(obj).data('id');
		var tipo = $(obj).data('tipo');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l["reprocessar"],
			text: l["temCertezaQueDesejaReprocessar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['reprocessar'],
			cancelButtonText: l["cancelar!"]
		}).then(function(){
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id, 'tipo': tipo}, function(ret){
				if(ret != 0){
					swal(
						l['sucesso!'],
						l['notaReprocessada'],
						"success"
					);
					$(obj).remove();
					tableDataTable.draw();
				}
				else {
					swal(
						l['erro'],
						l['falhaReprocessarNota'],
						"error"
					);
				}
				toggleLoading();
			})
		}).catch(swal.noop);
	})
};

function allFunctionsNotasFiscais(){
	reprocessarNota();
}

function docCancelado(){
	$('.select_documentos').on('change', function (e){
		$("#status option[value='c']").remove();
		if($(this).val() != 'pn') {
			$('#status').append($("<option></option>").attr("value", "c").text(l['documentoCancelado']));
		}
	});
}

allFunctionsNotasFiscais();
carregar();
criaSelects();
docCancelado();
