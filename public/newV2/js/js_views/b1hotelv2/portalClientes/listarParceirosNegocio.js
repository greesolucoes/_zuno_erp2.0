function acaoDeletar() {
	$('.deletar')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url");
			let idClienteEmpresa = $(obj).data("idClienteEmpresa");
			let tableDataTable = $(".table-exibe").DataTable();

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
				ajaxRequest(
					true,
					url,
					null,
					'text',
					{"idClienteEmpresa": idClienteEmpresa},
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

acaoDeletar();