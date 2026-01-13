function habilitaMovimentosCardService() {

	$('button.btn-habilita-movint-cs').off('click');

	$('button.btn-habilita-movint-cs').on("click", function (e) {

		e.preventDefault();
		var obj = $(this);

		var idConc = $(obj).data('id_conciliacao');
		var url = $(obj).data('url');

		swal({
			title: l["desejaHabilitarIntegracaoDeMovimentosDoCardService"],
			text: l["umaVezHabilitadaTodosOsMovimentosAdvindosDaProvedoraDosValoresDeCartaoSeraoIntegradosVerifiqueOsValoresAntesDeHabilitarEstaIntegracao"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["sim!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id_conciliacao': idConc}, function (ret) {
				try{
					ret = JSON.parse(ret);
					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
					toggleLoading();
					obj.remove();
				}catch(err){
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			});
		}, function () {
			//SE DER ERRO
		}).catch(swal.noop);
	});

}

habilitaMovimentosCardService();
