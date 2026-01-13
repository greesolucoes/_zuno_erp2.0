/**
 * Reprocessa um agrupamento de pagamento para reenviar para o SAP
 */
function reconciliarPagamentoCS(){
	$('.reconciliar-pagamento-cs').off("click");
	$('.reconciliar-pagamento-cs').on("click", function(e){
		e.preventDefault();
		var obj  = $(this);
		var url  = $(obj).data('url');
		var id   = $(obj).data('id');
		var idFilial   = $(obj).data('id_filial');

		swal({
			title: l["reprocessar"],
			text: l["continuarComOProcesso?"],
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
				} else {
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
};
reconciliarPagamentoCS();