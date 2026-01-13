function acoesBotoes() {
	let __acaoPadrao = function (obj, objEnvio) {
		let id = $(obj).data('id');
		let url = $(obj).data('url');
		let tableDataTable = $(obj).parents('.table-exibe').DataTable();
		if(is_empty(id, 1) || is_empty(url, 1)) return;
		if(is_empty(objEnvio, 1)) {
			objEnvio = {};
		}
		objEnvio['id'] = id;

		swal({
			title: l["desejaContinuar?"],
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["continuar!"],
			cancelButtonText: l["fechar!"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', objEnvio, function (ret) {
				try{
					ret = JSON.parse(ret);

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);

					tableDataTable.draw();
					toggleLoading();
				}catch(err){
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			});
		}).catch(swal.noop);
	}

	$(".deletar").off('click');
	$(".deletar").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".upload").off('click');
	$(".upload").on('click', function () {
		__acaoPadrao($(this));
	});

	$('.show_modal_motivo').off('click');
	$('.show_modal_motivo').on('click', function (e) {
		$('.modal_motivo #label_motivo').html($(this).attr("title"));
		$('.modal_motivo .descricao_motivo').html($(this).parents('td').find('.descricao_rejeicao').html());
		$('.modal_motivo').modal('toggle');
	});
}