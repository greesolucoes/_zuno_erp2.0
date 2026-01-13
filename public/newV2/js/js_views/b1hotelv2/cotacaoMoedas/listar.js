function acaoAtivar() {
	$('.ativar')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url");
			let id = $(obj).data("id");
			let ativar = $(obj).data("type");
			let tableDataTable = $(".table-exibe").DataTable();

			swal({
				title: (ativar == 'ativar') ? l['ativar!'] : l["desativar!"],
				text: l["desejaContinuar?"],
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
					url,
					null,
					'text',
					{
						"idB1HV2MoedasCotacao": id,
						"ativar": (ativar == 'ativar') ? 1 : 0
					},
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if(!is_empty(ret["bol"], 1)) {
							tableDataTable.draw();
						}

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}

acaoAtivar();