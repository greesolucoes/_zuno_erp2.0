function excluirReg() {
	$('.excluirReg').off('click');
	$('.excluirReg').on("click", function (e) {
		e.preventDefault();
		var obj            = $(this);
		var url            = $(obj).data('url');
		var id             = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['essaAcaoSeraIrreversivel'],
			text: l['continuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['sim'],
			cancelButtonText: l['nao']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (is_empty(ret, 1)) {
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
					toggleLoading();
					return;
				}

				swal(
					l['sucesso'],
					l['layoutDoPedidoDeCompraExcluidoComSucesso'],
					"success"
				);

				tableDataTable.draw();
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

function acoesBotoes(){
	excluirReg();
}

acoesBotoes();