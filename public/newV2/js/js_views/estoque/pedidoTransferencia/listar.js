function acaoDeletar() {
	reqAjaxCommon(
		'.deletar',
		l['desativarRegistro'],
		l['desejaContinuar?']
	);
}

function acaoEnviar() {
	reqAjaxCommon(
		'.enviar-pedido',
		l['enviarPedido'],
		l['desejaContinuar?']
	);
}
function acaoAprovar() {
	reqAjaxCommon(
		'.aprovar-pedido',
		l['aprovarPedido'],
		l['desejaContinuar?'],
		{ 'aprovar_portal' : 1 }
	);
}

function reqAjaxCommon(seletorBtn, title, text,dados=null) {
	$(seletorBtn)
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);

			swal({
				title,
				text,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();
				ajaxRequest(
					true,
					$(obj).data("url"),
					null,
					'text',
					{ 'id' : $(obj).data("id"), 'dados': dados},
					function (ret) {
						ret = JSON.parse(ret);
						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if(!is_empty(ret["bol"], 1)) {
							$(".table-exibe").DataTable().draw();
						}
						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}