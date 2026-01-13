// acao ao esconder modal
$('#modalNovoCentroCusto').on('hidden.bs.modal', function () {
	resetSelectCC();
	$(".table-exibe").DataTable().draw();
});

// ação ao exibir modal
$('#modalNovoCentroCusto').on('show.bs.modal', function () {
	$('#centroCusto').select2Ajax();
	$('#centroCusto').data('init', '');
});

$('.table').on('draw.dt', function() {
	$('#centroCusto').select2Ajax();
	$('#centroCusto').data('init', '');
});


$('#btnSalvarStatusOrcamento').off('click').on('click', function() {
	// retira a mensagem do usuário ao tentar sair da página sem salvar, visto
	// que o botão SALVA
	window.onbeforeunload = null;

	const status = $('#status').val();
	let text = l["aoConfirmarTodosOsValoresInformadosNesteRegistroSeraoEfetivados"];

	if (status === 'f') {
		text = l['oStatusDesteRegistroFoiAlteradoParaFechadoAoConfirmarTodosOsValoresInformadosSeraoEfetivadosMasNaoMaisPoderaoSerAlterados'];
	}

	swal({
		title: l["salvarAlteracoes"],
		text,
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
			$('.datas_views').data("url_salvar_status_cc"),
			null,
			'text',
			{
				'guidStatus': $('#guidStatus').val(),
				'ano': $('#anoStatus').val(),
				status
			},
			async function (retorno) {
				let ret = JSON.parse(retorno);
				toggleLoading();

				let title = l["erro"];
				let text = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
				let type = "error";

				if (ret) {
					title = ret['title'];
					text = ret['msg'];
					type = ret['class'];
				}

				swal({
					title,
					text,
					type
				}).then(function() {
					if (type == 'success') {
						toggleLoading();

						window.location.href = $('.datas_views').data('url_listar_status');
					} else {
						// apresenta uma mensagem ao usuário ao tentar sair da página sem salvar
						window.onbeforeunload = function() {
							forceToggleLoading(0);
							return "";
						}
						forceToggleLoading(0);
					}
				});
			}
		);
	}).catch(swal.noop);
})

$('#btnCadastrarCentroCusto').off('click').on('click', function() {
	let canEdit = 0;

	// apresenta uma mensagem ao usuário ao tentar sair da página sem salvar
	window.onbeforeunload = function() {
		forceToggleLoading(0);
		return "";
	}

	toggleLoading();

	ajaxRequest(
		true,
		$('.datas_views').data("url_criar_cc"),
		null,
		'text',
		{
			'guidStatus': $('#guidStatus').val(),
			'idCC': $('#centroCusto').val()
		},
		async function (retorno) {
			retorno = JSON.parse(retorno);
			addJsonMessage(retorno, '#container-retorno-msg');
			canEdit = retorno.edit;

			if (canEdit === 0) {
				resetSelectCC();
			}

			toggleLoading();
		}
	);
});

/**
 * Função para reset do select2 do modal de inserção de centros de custo
 */
function resetSelectCC() {
	$('#centroCusto').select2Ajax();
	$("#centroCusto").val("").trigger('change').trigger('select2:unselect');
	$('#centroCusto').data('init', '');
}

/**
 * Função para ação dos botões da listagem de centro de custo
 */
function handleButtonsCentrosCusto() {
	// trocar status
	$('.buttonToggleCentrosCusto').off('click').on('click', function() {
		let guidCC = $(this).data('id_centro_custo');
		swal({
			title: ($(this).attr('title') + '?'),
			text: null,
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
				$('.datas_views').data("url_toggle_status_cc") + '/' + guidCC,
				null,
				'text',
				null,
				function (retorno) {
					let ret = JSON.parse(retorno);
					toggleLoading();

					let title = l["erro"];
					let text = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
					let type = "error";

					if (ret) {
						title = ret['title'];
						text = ret['msg'];
						type = ret['class'];
					}

					swal({
						title,
						text,
						type
					}).then(function() {
						if (type == 'success') {
							$(".table-exibe").DataTable().draw();
						}
					});
				}
			);
		}).catch(swal.noop);
	});

	// remover centro de custo
	$('.buttonRemoveCentrosCusto').off('click').on('click', function() {
		let guidCC = $(this).data('id_centro_custo');

		swal({
			title: ($(this).attr('title') + '?'),
			text: null,
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
				$('.datas_views').data("url_remover_cc") + '/' + guidCC,
				null,
				'text',
				null,
				async function (retorno) {
					let ret = JSON.parse(retorno);
					toggleLoading();

					let title = l["erro"];
					let text = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
					let type = "error";

					if (ret) {
						title = ret['title'];
						text = ret['msg'];
						type = ret['class'];
					}

					swal({
						title,
						text,
						type
					}).then(function() {
						if (type == 'success') {
							$(".table-exibe").DataTable().draw();
						}
					});
				}
			);
		}).catch(swal.noop);
	})
}

