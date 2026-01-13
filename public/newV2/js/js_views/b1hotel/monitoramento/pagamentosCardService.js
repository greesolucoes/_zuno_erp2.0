function formatDate(date) {
	var dia  = date.split("/")[0];
	var mes  = date.split("/")[1];
	var ano  = date.split("/")[2];
	return ano + '-' + ("0"+mes).slice(-2) + '-' + ("0"+dia).slice(-2);
}

// carrega o datatable
function carregarPagamentosCS() {
	$('#buscar').unbind('click');
	$('#buscar').on("click", function (e) {
		var dataInicial = is_empty($('#dataInicial').val(), 1) ? 0 : formatDate($('#dataInicial').val());
		var dataFinal = is_empty($('#dataFinal').val(), 1) ? 0 : formatDate($('#dataFinal').val());
		var finalUrl = dataInicial + "/" + dataFinal;

		$('#dataTablePagamentos').DataTable().destroy();
		$('#dataTablePagamentos').data("url_ajax", $(".datas_views").data("url_search_carregar") + finalUrl);
		allTables();
	});
	// já efetua a consulta com a data q tiver na busca
	$('#buscar').trigger('click');
}

// altera o status de um grupo de pagamentos para 'as' aguardando envio para o SAP
function reconciliarPagamentoCS(){
	$('.reconciliarPagamentoCS').off("click");
	$(document).on("click",'.reconciliarPagamentoCS', function(e){
		e.preventDefault();
		var obj  = $(this);
		var url  = $(obj).data('url');
		var id   = $(obj).data('id');
		var idFilial   = $(obj).data('id_filial');
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
			ajaxRequest(true, url, null, 'text', {'id': id, 'idFilial': idFilial}, function(ret){
				if(ret != 0){
					swal(
						l['sucesso!'],
						l['pagamentoReprocessadaComSucesso!'],
						"success"
					);
					$(obj).remove();
					tableDataTable.draw();
				}
				else {
					swal(
						l['erro'],
						l['falhaReprocessarPagamento'],
						"error"
					);
				}
				toggleLoading();
			})
		}).catch(swal.noop);
	})
}

reconciliarPagamentoCS();
carregarPagamentosCS();
