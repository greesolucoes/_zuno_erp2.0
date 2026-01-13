
let __acaoPadrao = function (obj, objEnvio) {
	let id = $(obj).data('id');
	let url = $(obj).data('url');
	let tableDataTable = $(obj).parents('.table-exibe').DataTable();
	if (is_empty(id, 1) || is_empty(url, 1)) return;
	if (is_empty(objEnvio, 1)) {
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
			try {
				ret = JSON.parse(ret);

				swal(
					ret['titulo'],
					ret['text'],
					ret['class']
				).then(function () {
					// usado pelos botÃµes superiores de aÃ§Ã£o nas telas de visualizaÃ§Ã£o e cadastro
					if (!is_empty($('.data_views').data('atualizar_pagina_apos_envio'), 1) === true) {
						window.location.reload();
					}
				}).catch(swal.noop);

				tableDataTable.draw();
				toggleLoading();
			} catch (err) {
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
function acoesBotoes() {

	$("button.deletar").off('click');
	$("button.deletar").on('click', function () {
		__acaoPadrao($(this));
	});

}