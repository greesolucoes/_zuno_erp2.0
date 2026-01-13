const inicializaSelect2 = function () {
	$('.select2')
		.select2Simple(
			l['selecione'],
			'',
			{
				'allowClear': true,
				'dropdownParent': "#modalArquivo"
			}
		);
}
$('#modalArquivo').on('show.bs.modal', inicializaSelect2);
$('.table').on('draw.dt', inicializaSelect2);

$('#modalArquivo').on('hidden.bs.modal', function () {
	$('#div-alert>a.close').first().trigger('click');
	$('#arquivoImportacao').val('');
	$('#anoImportacao').val('').trigger('change');
	$('#versaoImportacao').val("").trigger("change");
});

$('#arquivoImportacao').off('click').on('click', function() {
	$('#arquivoImportacao').val('');
});

function handleButtonsPremissas() {
	$('.removerPremissa')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);

			swal({
				title: l['excluirRegistro'] + '?',
				text: l["essaAcaoSeraIrreversivel"],
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
					$('.datas_views').data("url_excluir_premissa") + '/' + $(obj).data("id_premissa"),
					null,
					'text',
					null,
					function (ret) {
						ret = JSON.parse(ret);
						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						$(".table-exibe").DataTable().draw();

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});

	$('.baixarPremissa')
		.unbind("click")
		.on("click", async function (e) {
			e.preventDefault();
			url_download_premissa = $('.datas_views').data('url_download_premissa') + $(this).data('id_premissa');
			window.open(url_download_premissa, "_blank");
		});
}

$('#btnImportarArquivo').off('click').on('click', async function() {
	if (!validarPreenchimentoCampos()) {
		toggleLoading();
		const data = new FormData($('#modalForm')[0]);
		let canEdit = 0;

		Object.entries(tokenCsrf).forEach(([key, value]) => {
			data.append(key, value);
		});

		await $.ajax({
			url: $('.datas_views').data('url_importacao_arquivos_budget'),
			type: 'POST',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(retorno) {
				addJsonMessage(retorno, '#container-retorno-msg');
				canEdit = retorno.edit;

				if (canEdit === 0) {
					$('#modalForm')[0].reset();
					$(".table-exibe").DataTable().draw();
				}
			},
			complete: function(completeData) {
				if(canEdit === 0) {
					$('#modalForm')[0].reset();
					$(".table-exibe").DataTable().draw();
				}
			}
		}).then(function () {
			toggleLoading();
		}).catch(swal.noop);
	}
});

function validarPreenchimentoCampos() {
	// valida os campos vazios
	if (($('#arquivoImportacao').val() == '') || ($('#anoImportacao').val() == '') || $('#versaoImportacao').val() == ''){
		swal({
			 title: l["atenção!"],
			 text: l['preenchaTodosOsCamposParaContinuar'],
			 type: "warning",
			 showCancelButton: false,
			 confirmButtonColor: '#3085d6'
		 });

		return true;
	}

	// começa com letras ou numeros, tem letras, números e . no meio e deve terminar com números ou letras
	if (/[0-9]{4}/.test($('#anoImportacao').val()) === false) {
		swal({
			title: l["atenção!"],
			text: l['oCampoAnoDeveConterApenasNumeros'],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		});

		return true;
	}

	return false
}

function acaoCancelarImportacao() {
	$('.buttonAcaoCancelarImportacao').off('click').on('click', async function(e) {
		e.preventDefault();
		let url = ($('.datas_views').data("url_cancelar_importacao") + $(this).data('id_importacao'));

		swal({
			title: l["cancelarImportacao"],
			text: l["aoCancelarTodosOsRegistrosInternosTambemSeraoCancelados"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: l["continuar!"],
			cancelButtonText: l["fechar!"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(
				true,
				url,
				null,
				'text',
				null,
				function (ret) {
					ret = JSON.parse(ret);

					swal(
						ret["titulo"],
						ret["text"],
						ret["class"]
					).catch(swal.noop);

					if (!is_empty(ret["bol"], 1)) {
						$(".table-exibe").DataTable().draw();
					}

					toggleLoading();
				}
			);
		}).catch(swal.noop);
	})
}