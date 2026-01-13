const inicializaSelect2 = function() {
	$('#modalNovaAlteracaoSplit .select2')
		.select2Simple(
			l['selecione'],
			'',
			{
				'allowClear' : true,
				'dropdownParent': "#modalNovaAlteracaoSplit"
			}
		);
}

const verificaPreenchimentoCamposParaSubmit = async function() {
	const ano = $('#anoSplit').val();
	const centroCusto = $('#centroCustoSplit').val();

	if (is_empty(ano, 1) ||  is_empty(centroCusto, 1)) {
		swal(
			l['atenção!'],
			l['preenchaTodosOsCamposParaContinuar'],
			'warning'
		).catch(swal.noop);
	} else {
		toggleLoading();

		const data = new FormData($('#modalForm')[0]);

		Object.entries(tokenCsrf).forEach(([key, value]) => {
			data.append(key, value);
		});
		await $.ajax({
			url: $('.datas_views').data('url_verificar_dados_criacao_split'),
			type: 'POST',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function (retorno) {
				if (retorno.error == 1) {
					addJsonMessage(retorno);
					toggleLoading();
				} else {
					if (!is_empty(retorno.idSplit, 1)) {
						window.location.href = ($('.datas_views').data('url_adicionar_split') + '/' + retorno.idSplit);
					} else {
						toggleLoading();

						swal(
							l['erro!'],
							l['algumProblemaOcorreuDuranteACriacaoDoSplitPorFavorTenteNovamenteOuEntreEmContatoComOSuporteCasoEsteErroPersista'],
							'error'
						).catch(swal.noop)
					}
				}
			},
			complete: function (completeData) {

			}
		}).catch();
	}
}

$('#modalNovoSplit').on('show.bs.modal', inicializaSelect2);
$('.table').on('draw.dt', inicializaSelect2);
$('#btnCriarSplit').off('click').on('click', verificaPreenchimentoCamposParaSubmit);

function handleButtonsSplits() {
	const btnsAjax = [
		{
			'seletor': '.buttonAcaoRemoverSplit',
			'url': 'url_remover_splits',
			'title': l['remover'] + '?',
		},
		{
			'seletor': '.buttonAcaoFinalizarSplit',
			'url': 'url_finalizar_splits',
			'title': l['finalizar'] + '?',
		},
	];

	btnsAjax.forEach(function(btn) {
		$(btn.seletor).off('click').on('click', async function(e) {
			e.preventDefault();
			let url = ($('.datas_views').data(btn.url) + $(this).data('id_splits'));

			swal({
				title: btn.title,
				text: l['essaAcaoSeraIrreversivel'] + '!',
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
							ret["title"],
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
		});
	})
}