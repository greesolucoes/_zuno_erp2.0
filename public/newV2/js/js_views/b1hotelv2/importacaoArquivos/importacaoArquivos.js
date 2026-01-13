const btnUpArquivo = $('#buttonImportacao');

/** Chama a url que faz o download do modelo ao clicar no botão */
$('.downloadModelo')
	.off('click')
	.on('click', function() {
		window.location.href = $('.data_views').data('url_download_modelo_xls');
	});

/** Chama a url que faz o download do arquivo salvo ao clicar no botão */
$('.downloadArquivo')
	.off('click')
	.on('click', function() {
		window.location.href = $('.data_views').data('url_download_arquivo') + "/arquivo";
	});

/** Chama a url que faz o download do anexo salvo ao clicar no botão */
$('.downloadAnexo')
	.off('click')
	.on('click', function() {
		window.location.href = $('.data_views').data('url_download_arquivo') + "/anexo";
	});

// ativação do modal de importação de planilha
$('#btnImportarPlanilha').on('click', (e) => {
	e.preventDefault();
	// tira o que estiver presente do input file
	$('#arquivoImportacao').val('');

	$('#modalArquivo').modal('toggle');
})

/**
 * Ao adicionar um arquivo válido, o botão é habilitado.
 * Caso haja a substituição do arquivo válido, o botão é desabilitado.
 */
$('#arquivoImportacao').on('change', function() {
	let nomeArquivo = $('#arquivoImportacao').val();

	// habilita o botão para o click
	btnUpArquivo.addClass('disabled');

	if (nomeArquivo.substr(nomeArquivo.length - 4) === '.xls' ||
		nomeArquivo.substr(nomeArquivo.length - 5) === '.xlsx'
	) {
		btnUpArquivo.removeClass('disabled');
	}
})

/**
 * Ao clicar no botão, pega o arquivo do input:file e o envia para processamento.
 * A rota então retorna um JSON string, apresentando uma mensagem ao usuário.
 */
btnUpArquivo
	.unbind('click')
	.on('click', function(e) {
		e.preventDefault();
		let data = new FormData($('#formImportacaoArquivo')[0]);
		let tableDataTable = $(".table-exibe").DataTable();
		const url = $($(this)).data('url_importacao_arquivos');
		toggleLoading();

		Object.entries(tokenCsrf).forEach(([key, value]) => {
			data.append(key, value);
		});

		$.ajax({
			url: url,
			type: 'POST',
			data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(ret) {
				ret = JSON.parse(ret);
				swal(
					ret['titulo'],
					ret['text'],
					ret['class']
				);

				if (ret['class'] == 'success') {
					// tira o que estiver presente do input file
					$('#arquivoImportacao').val('');

					// esconde o modal
					$('#modalArquivo').modal('toggle');

					// recarrega o datatable
					tableDataTable.draw();
				}
			},
			complete: function(completeData) {
				$('#formImportacaoArquivo')[0].reset();
			}
		}).then(function () {
			toggleLoading();
		}).catch(swal.noop);
	});

// possiveis ações de uma importação feita, enviar ou cancelar (listagem)
function ajaxButtonsImportacao(seletor, updateDataTable = true, preventReload = true) {
	$(seletor)
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url") + $(obj).data("id");
			let title = $(obj).data("titulo");
			let text = $(obj).data("texto");

			swal({
				title,
				text,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				$.get(url, function(ret) {
					ret = JSON.parse(ret);
					toggleLoading();

					let titulo = l["erro"];
					let texto = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
					let tipo = "error";

					if (ret) {
						titulo = ret['titulo'];
						texto = ret['text'];
						tipo = ret['class'];
					}

					if (preventReload) {
						swal(titulo, texto, tipo);
					} else {
						swal(
							titulo,
							texto,
							tipo
						).then(function() {
							window.location.reload();
						});
					}
				})

				toggleLoading();

				if (updateDataTable) {
					$(".table-exibe").DataTable().draw();
				}
			}).catch(swal.noop);
		});
}

ajaxButtonsImportacao('.aprovarImportacaoArquivo', false, false)
ajaxButtonsImportacao('.recusarImportacaoArquivo', false, false)

$('#periodo').mask('99/9999');