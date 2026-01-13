const inicializaSelect2 = function () {
	$('.select2')
		.select2Simple(
			l['selecione'],
			'',
			{
				'allowClear': true,
				'dropdownParent': "#modalNovoStatusCentrosCusto"
			}
		);
}
$('#modalNovoForecast').on('show.bs.modal', inicializaSelect2);
$('.table').on('draw.dt', inicializaSelect2);

$('#modalNovoStatusCentrosCusto').on('hidden.bs.modal', function () {
	$('#anoStatusCentrosCusto').val('-1').trigger('change');
	$('#div-alert>a.close').first().trigger('click');
});

$('#btnCriarStatusCentrosCusto').off('click').on('click', async function() {
	if (!validarPreenchimentoCampos()) {
		toggleLoading();
		const data = new FormData($('#modalForm')[0]);
		let canEdit = 0;
		let hasGuid = '';

		Object.entries(tokenCsrf).forEach(([key, value]) => {
			data.append(key, value);
		});

		await $.ajax({
			url: $('.datas_views').data('url_adicionar_cabecalho_status_cc'),
			type: 'POST',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(retorno) {
				canEdit = retorno.edit;
				hasGuid = retorno.guidStatus;

				if (canEdit === 0) {
					$('#div-alert>a.close').first().trigger('click');
					$('#anoStatusCentrosCusto').val('');
					$(".table-exibe").DataTable().draw();
				} else {
					addJsonMessage(retorno, '#container-retorno-msg');
				}
			},
			complete: function(completeData) {
				if (!!hasGuid != false) {
					window.location.href = ($('.datas_views').data('url_adicionar_status_cc') + '/' + hasGuid);
				}
			}
		}).then(function () {
			if (!!hasGuid == false) {
				toggleLoading();
			}
		}).catch(swal.noop);
	}
});

function validarPreenchimentoCampos() {
	// regex para 4 números
	if (/[0-9]{4}/.test($('#anoStatusCentrosCusto').val()) === false) {
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

function removeStatusCentrosCusto() {
	$('.buttonAcaoRemoveStatusCentrosCusto').off('click').on('click', async function(e) {
		e.preventDefault();
		let url = ($('.datas_views').data("url_cancelar_status_cc") + $(this).data('id_status_cc'));
		swal({
			title: l["excluirRegistro"],
			text: l["aoConfirmarORegistroSeraExcluidoJuntoATodosOsSeusRegistrosInternos"],
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
