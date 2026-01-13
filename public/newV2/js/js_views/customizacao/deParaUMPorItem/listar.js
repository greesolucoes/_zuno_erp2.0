function acoesBotoes() {
	let __acaoPadrao = function (obj, objEnvio, funOnRequest) {
		let id = $(obj).data('id');
		let url = $(obj).data('url');
		let tableDataTable = $('.table-exibe').DataTable();
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

					if(ret['bol']) tableDataTable.draw();
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

	$(".deletar").off("click");
	$(".deletar").on("click", function (e) {
		__acaoPadrao($(this), null, function (id, ret) {});
	});
}

acoesBotoes();