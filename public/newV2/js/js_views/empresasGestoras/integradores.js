const proccess = {
	idEmpresasGestoras: null,
	url: null,
	init: (id, url) => {
		proccess.idEmpresasGestoras = id
		proccess.url = url
		proccess.loadData()

		$("#table-response table tbody").on("click", "button.delete", function () {
			proccess.delete($(this).data("empresa"), $(this).data("servico"))
		})

		$("button.add").on("click", function () {
			proccess.add()
		})

		$("button.save").on("click", function () {
			proccess.save()
		})
	},
	add: () => {
		console.log("add")
		$("#modal-setup").modal("toggle")
	},
	save: () => {
		$("#modal-setup").modal("toggle")
		const data = {
			idEmpresasGestoras: proccess.idEmpresasGestoras,
			idEmpresas: $("#sel-empresa").val(),
			idIntegradorservico: $("#sel-servico").val(),
		}

		const url = `${proccess.url}EmpresasGestoras/addintegradorempresa`
		ajaxRequest(true, url, null, "json", data, function (ret) {
			if (ret.status === true) {
				proccess.loadData()
			} else {
				swal("error", ret.message, "error")
				return
			}
		})
	},
	delete: (empresa, servico) => {
		const data = {
			idEmpresasGestoras: proccess.idEmpresasGestoras,
			idEmpresas: empresa,
			idIntegradorservico: servico,
		}

		const url = `${proccess.url}EmpresasGestoras/removeintegradorempresa`
		swal({
			title: l["desejaContinuar?"],
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: l["continuar!"],
			cancelButtonText: l["fechar!"],
		})
			.then(function () {
				toggleLoading()
				ajaxRequest(true, url, null, "json", data, function (ret) {
					if (ret.status === true) {
						proccess.loadData()
						toggleLoading()
					} else {
						swal("error", ret.message, "error")
						toggleLoading()
						return
					}
					forceToggleLoading(0)
				})
			})
			.catch(swal.noop)
	},
	loadData: () => {
		const url = `${proccess.url}EmpresasGestoras/getintegradores/${proccess.idEmpresasGestoras}`
		$("#table-response table tbody").empty()
		ajaxRequest(true, url, null, "json", null, function (ret) {
			if (ret != null && ret.length > 0) {
				proccess.loadTableResponse(ret)
			}
		})
	},
	loadTableResponse: (content) => {
		$("#table-response table tbody").empty()
		toggleLoading()
		content.forEach((el) => {
			let line = `
				<tr>
					<td>
						(${el.idEmpresas}) ${el.nomeEmpresa}
					</td>
					<td>
						<div class="d-flex align-items-center" style="gap: 10px;">
							<span class="badge badge-warning">${el.nomeIntegrador}</span>
							<span class="badge badge-success">${el.servico}</span>
							${el.descricao}
						</div>
					</td>
					<td>
						<div class="${isOldLayout ? 'input-group' : 'd-flex align-items-center justify-content-start col-12 flex-wrap'}">
							<button class='${isOldLayout ? 'btn btn-danger btn-sm' : 'button-form danger-button'} delete' title="Remover" 
									data-empresa='${el.idEmpresas}' data-servico='${el.idIntegradorservico}'
							>
								${isOldLayout ? '<i class="fa fa-trash-o"></i>' : '<span data-icon="ph:trash-simple-bold" class="iconify"></span>'}		
							</button>
						</div>
					</td>
				</tr>
			`
			$("#table-response table tbody").append(line)
		})
		toggleLoading()
		$("#table-response").show()
		forceToggleLoading(0)
	}
}
