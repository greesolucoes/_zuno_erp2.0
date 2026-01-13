$("#tabela").DataTable({
	"paging": false,
	"ordering": false,
	"info": false,
	"searching": false,
	"oLanguage": oLanguage
});

function reprocessarProdutoAcabado() {
	$('.reprocessarLancamentoProdutoAcabado').off('click');
	$(".reprocessarLancamentoProdutoAcabado").on('click', function () {

		var url = $('.data-views').data('url_reprocessar_produto_acabado');
		var idProdutoAcabado = $(this).data('id');

		swal({
			title: l["reprocessar"],
			text: l["desejaReprocessarProdutoAcabado"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["sim!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			ajaxRequest(
				true,
				url,
				null,
				'text',
				{'idProdutoAcabado': idProdutoAcabado},
				function (ret) {
					if(ret) {
						swal({
							title: l["sucesso!"],
							text: l["produtoAcabadoReprocessadoComSucesso"],
							type: "success",
							showCancelButton: false,
							confirmButtonColor: '#3085d6',
							confirmButtonText: l["sim!"]
						});
						$('#modalLPA').modal('toggle');
					}else{
						swal({
							title: l["erro!"],
							text: l["erroAoReprocessarProdutoAcabado"],
							type: "error",
							showConfirmButton: false,
							confirmButtonColor: '#3085d6',
							confirmButtonText: l["sim!"]
						});
					}
				});
		});
	});
}

reprocessarProdutoAcabado();