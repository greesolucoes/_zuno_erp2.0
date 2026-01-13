function acaoDeletar() {
	$('.deletar')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url");
			let id = $(obj).data("id");
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
					{"idB1HV2ConfigMonitoramentoNFSaidaSede": id},
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

function modalViewErroSapMonitoramento(id){
	const url = $('.table').data('url_ajax_view_erro_sap');
	$.ajax({
		url : url,
		type : 'post',
		dataType: 'json',
		data : {
			id : id,
			...tokenCsrf
		},
		beforeSend : function(){
			$('#modalVisualizarErros').modal('hide');
		}
	}).done(function(data){
		if (data){
			$('#modalVisualizarErros .modal-body').html(data.ultimoErroSAP)
			$('#modalVisualizarErros #modalVisualizarErrosTitle').html(data.tituloModal)
			$('#modalVisualizarErros a[data-modal-button="save"]').attr('href',data.buttonReenviar)
			$('#modalVisualizarErros').modal('show');
		}

	});
}