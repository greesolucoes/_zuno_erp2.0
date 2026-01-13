function criaSelects() {
	$("#documentos").select2Simple();
	$("#documentos").data('init', '');
}

function formatDate(date) {
	var dia  = date.split("/")[0];
	var mes  = date.split("/")[1];
	var ano  = date.split("/")[2];
	return ano + '-' + ("0"+mes).slice(-2) + '-' + ("0"+dia).slice(-2);
}

function reprocessarXml(){
	$('.reprocessarXml').off("click");
	$('.reprocessarXml').on("click", function(e){
		e.preventDefault();
		var documentos = $('#documentos').val();
		var customerCode = $('#customerCode').val();
		var dataXml = is_empty($('#dataXml').val(), 1) ? 0 : formatDate($('#dataXml').val());

		// Captura o arquivo do input
		const xmlInputElement = $('#xml');
		let file = '';
		if(xmlInputElement.length > 0) {
			const fileInput = xmlInputElement[0];
			file = fileInput.files[0];
		}

		// Cria um FormData para enviar o arquivo
		const formData = new FormData();
		formData.append('xml', file);
		formData.append('dataXml', dataXml);
		formData.append('documentos', documentos);
		formData.append('customerCode', customerCode);

		swal({
			title: l["atenção!"],
			text: l["temCertezaQueDesejaReprocessar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['reprocessar'],
			cancelButtonText: l["cancelar!"]
		}).then(function(){
			toggleLoading();

			$.ajax({
				async: true,
				url: $(".datas_views").data("url"),
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (ret) {
					if (ret == 2) {
						swal(
							l['erro'],
							l['aDataOuODocumentoEstãoSãoInválidos!'],
							"error"
						);
					}
					else if(ret == 0) {
						swal(
							l['erro'],
							l['falhaReprocessarXML!'],
							"error"
						);
					}
					else {
						swal(
							l['sucesso!'],
							l['XMLReprocessadoComSucesso!'],
							"success"
						);
					}
					toggleLoading();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					swal(
						l['erro'],
						'Erro ao tentar processar XML! Favor falar com o suporte: Cod PS1301 '
							+ textStatus + ' | ' + errorThrown,
						"error"
					);
					toggleLoading();
				},
			});
		}).catch(swal.noop);
	})
};

criaSelects();
reprocessarXml();


ajaxRequest()