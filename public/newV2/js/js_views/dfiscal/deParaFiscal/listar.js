function reqExcluir(){
	$('.delete').on('click', function (e) {
		e.preventDefault();
		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l["deletarRegistro"],
			text: l["desejaContinuar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: l["continuar!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {id}, function (ret) {
				if (ret) {
					swal({
						title: l["sucesso!"],
						text: "Sucesso ao excluir registro!",
						type: "success",
					});
					$(obj).remove();
					tableDataTable.draw();
				} else {
					swal({
						title: l["erro!"],
						text: "Erro ao excluir registro!",
						type: "error",
					});
				}
				toggleLoading();
			});
		});
	});
}

$(".gerar-xlsx-da-view").click(function(){
	let url = $(".table-exibe").data("url_ajax");
	ajaxRequest(
		true,
		url,
		"div-table-relatorio",
		'html',
		{
			relatorio: true
		},
		function(ret) {
			if (ret) {
				save2excel($('table#table-relatorio'), {
					not: null,
					name: `${l['DEPARAFiscalUtilizacao']}`,
					filename: (`${l['DEPARAFiscalUtilizacao']}.xls`)
				});
			} else {
				swal(
					'Erro na requisição',
					'Houve um erro na requisição, tente novamente!',
					'error'
				).catch(swal.noop);
			}
		}
	);
});