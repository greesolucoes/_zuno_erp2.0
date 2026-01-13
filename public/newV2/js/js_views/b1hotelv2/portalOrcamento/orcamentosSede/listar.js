$('#modalDownloadPremissas').on('hidden.bs.modal', function () {
	$('#div-alert>a.close').first().trigger('click');
	$('#ano-premissas').val("").trigger("change");
	$('#premissas-container').html('');
});

$('#ano-premissas').on('change', async function() {
	const anoSelecionado = $(this).val();

	if (!!anoSelecionado != false) {
		toggleLoading();

		ajaxRequest(
			true,
			$('.datas_views').data("url_get_premissas") + '/' + anoSelecionado,
			null,
			'text',
			null,
			function (ret) {
				ret = JSON.parse(ret);
				if((ret.semPremissasDisponiveis == 1) || (!!ret == false)) {
					swal(
						l['nenhumaPremissaEncontrada'],
						l['naoHaPremissasDisponiveisParaOAnoEscolhido'],
						'warning'
					)
				} else {
					renderPremissasDownload(ret);
				}

				toggleLoading();
			}
		);
	}
})

function renderPremissasDownload(ret) {
	let containerPremissasHtml = `<hr> 
		<div class="informacoes_add">
			<table class="table table-hover table-responsive-sm">
				<thead>
				<tr>
					<th>${l['acao']}</th>
					<th>${l['ano']}</th>
					<th>${l['versao']}</th>
				</tr>
				</thead>
				<tbody>
	`;

	ret.forEach(function (premissa) {
		containerPremissasHtml += `<tr>
			<td>
				<a class="btn btn-warning" 
					href="${$('.datas_views').data('url_download_premissa') + '/' + premissa.idPremissas}" 
					target="_blank"
				><i class="fa fa-download"></i></a>
			</td>
			<td>${premissa.ano}</td>
			<td>${premissa.versao}</td>
		</tr>`;
	});

	containerPremissasHtml += `</tbody> </table></div>`;
	$('#premissas-container').html(containerPremissasHtml);
}

$('.baixarPremissa')
	.unbind("click")
	.on("click", async function (e) {
		e.preventDefault();
		const url_download_premissa = $('.datas_views').data('url_download_premissa') + $(this).data('id_premissa');
		window.open( url_download_premissa, "_blank");
	});

function handleAcoesOrcamentos() {
	$('.btnToggleOrcamentos').off('click').on('click', async function(e) {
		e.preventDefault();
		let url = ($('.datas_views').data("url_toggle_orcamentos") + $(this).data('id_orcamentos'));
		let statusAtual = $(this).data('status_orcamentos');

		swal({
			title: (statusAtual === 'a') ? l["fecharOrcamento"] : l['abrirOrcamento'],
			text: (statusAtual === 'a') ? l["aoFecharOOrcamentoNaoSeraMaisPossivelEditarOuRemoverOMesmo"] : '',
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

	$('.btnDownloadOrcamentos').off('click').on('click', async function(e) {
		e.preventDefault();
		window.location.href = $('.datas_views').data('url_download_orcamentos') + $(this).data('id_orcamentos');
	});

	const btnsAjax = [
		{
			'seletor': '.btnRemoverOrcamentos',
			'url': 'url_remover_orcamentos',
			'title': l['removerOrcamento'],
			'text': l['essaAcaoSeraIrreversivel']
		},
		{
			'seletor': '.btnAprovarOrcamentos',
			'url': 'url_aprovacao_orcamentos',
			'title': l['confirmarAprovacao?'],
			'text': l['estaAcaoEIrreversivelDependendoDaQuantidadeDeAprovacoesConfiguradasNoWorkflowDesteCentroDeCusto']
		},
		{
			'seletor': '.btnReprovarOrcamentos',
			'url': 'url_reprovacao_orcamentos',
			'title': l['confirmarRejeicao?'],
			'text': l['estaAcaoEIrreversivelDependendoDaQuantidadeDeReprovacoesConfiguradasNoWorkflowDesteCentroDeCusto']
		},
	]

	btnsAjax.forEach(function(btn) {
		$(btn.seletor).off('click').on('click', async function(e) {
			e.preventDefault();
			let url = ($('.datas_views').data(btn.url) + $(this).data('id_orcamentos'));

			swal({
				title: btn.title,
				text: btn.text,
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


