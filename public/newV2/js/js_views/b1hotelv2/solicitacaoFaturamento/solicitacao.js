const btnUpArquivo = $('#arquivoSolicitacaoFaturamentoGroup button');

/** Chama a url que faz o download do modelo ao clicar no botão */
$('.downloadModelo')
	.off('click')
	.on('click', function() {
		window.location.href = $('.data_views').data('url_download_modelo_xls');
	});

/**
 * Ao adicionar um arquivo válido, o botão é habilitado.
 * Caso haja a substituição do arquivo válido, o botão é desabilitado.
 */
$('#arquivoSolicitacaoFaturamento').on('change', function() {
	let nomeArquivo = $('#arquivoSolicitacaoFaturamento').val();

	btnUpArquivo.addClass('disabled');

	if (nomeArquivo.substr(nomeArquivo.length - 4) === '.xls') {
		btnUpArquivo.removeClass('disabled');
	} else {
		swal(
			l['atenção!'],
			l['selecioneUmArquivoValido'] + ' (*.xls)',
			'warning'
		).catch(swal.noop);
	}
})

/**
 * Ao clicar no botão, pega o arquivo do input:file e o envia para processamento.
 * A rota então retorna um JSON array ou um JSON string. Caso retorne um JSON
 * string válido, no caso de erro, apresenta uma mensagem ao usuário. Caso retorne
 * um JSON array, há uma iteração sobre ele que carrega cada linha da solicitação
 * com os dados do arquivo tratado.
 */
btnUpArquivo
	.unbind('click')
	.on('click', function(e) {
		e.preventDefault();
		let dataArquivo = new FormData($('.cadastroSolicitacaoFaturamento')[0]);
		const url = $($(this)).data('url_solicitacao_faturamento');
		toggleLoading();

		Object.entries(tokenCsrf).forEach(([key, value]) => {
			dataArquivo.append(key, value);
		});

		$.ajax({
			url: url,
			type: 'POST',
			data: dataArquivo,
			cache: false,
			contentType: false,
			processData: false,
			success: function(ret) {
				try{
					ret = JSON.parse(ret)
					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					);
				} catch {
					for (let retArray in ret) {
						let retObj = ret[retArray][0];
						addItemArquivo(retObj);
					}

					$('.valor').maskMoney({
						allowNegative: false,
						thousands: '.',
						decimal: ',',
						precision: $('.data_views').data('casas_preco')
					});
				}
			}
		}).then(function () {
			toggleLoading();
		}).catch(swal.noop);
	});

/** Adiciona linhas de pedidos de venda na solicitação, baseadas em um template */
$('button[data-add="itensSolicitacao"]')
	.click(function(e) {
		e.preventDefault();

		let aba = '.retorno_add';
		let tbody = $('tbody');
		let template = $(`${aba} template`).html();
		let index = (tbody.children().length > 0)
			? (parseInt(tbody.children()[tbody.children().length - 1].children[2].children[0].value) + 1)
			: 1;

		let html = template
			.replaceAll("{{n}}", index)
			.replaceAll("{{idPedido}}", index)
			.replaceAll("{{megaCode}}", '')
			.replaceAll("{{valor}}", '0,00')
			.replaceAll("{{costCenter}}", '')
			.replaceAll("{{project}}", '')
			.replaceAll("{{flow}}", '')
			.replaceAll("{{observacoes}}", '');

		$(`${aba} tbody`).fadeIn(270, function() {
			$(`${aba} tbody`).append(html);
		})


		$(`${aba} tfoot`).attr('data-count', index);
		$('.valor').maskMoney({
			allowNegative: false,
			thousands: '.',
			decimal: ',',
			precision: $('.data_views').data('casas_preco')
		});
	});

/**
 * Função para remover linhas de pedidos de venda na solicitação
 *
 * @param elemento
 * @type {(elemento: HTMLElement) => void}
 */
function removeTrSolicitacao (elemento) {
	$(elemento).parents('tr').fadeOut(270, function () {
		$(elemento).parents('tr').remove();
	});
}

/**
 * Adiciona uma linha à tabela de retorno com as informações do item
 *
 * @param dadosItem
 * @returns void
 * @type {(dadosItem: array) => void}
 */
function addItemArquivo(dadosItem) {
	let aba = '.retorno_add';
	let tbody = $('tbody');
	let template = $(`${aba} template`).html();
	let valor = dadosItem['valor'].toString();
	let index = (tbody.children().length > 0)
		? (parseInt(tbody.children()[tbody.children().length - 1].children[2].children[0].value) + 1)
		: 1;

	if ([',', '.'].indexOf(valor.substring((valor.length - 3), (valor.length - 2))) < 0) {
		if ([',', '.'].indexOf(valor.substring((valor.length - 2), (valor.length - 1))) < 0) {
			valor = `${valor}.0`;
		}

		valor = `${valor}0`;
	}

	let html = template
		.replaceAll("{{n}}", index)
		.replaceAll("{{idPedido}}", index)
		.replaceAll("{{megaCode}}", dadosItem['megaCode'])
		.replaceAll("{{valor}}", valor)
		.replaceAll("{{costCenter}}", (dadosItem['costCenter'] == null) ? '-' : dadosItem['costCenter'])
		.replaceAll("{{project}}", (dadosItem['project'] == null) ? '-' : dadosItem['project'])
		.replaceAll("{{flow}}", (dadosItem['flow'] == null) ? '-' : dadosItem['flow'])
		.replaceAll("{{observacoes}}", (dadosItem['observacoes'] == null) ? '-' : dadosItem['observacoes']);

	$(`${aba} tbody`).fadeIn(270, function() {
		$(`${aba} tbody`).append(html);
	})

	$(`${aba} tfoot`).attr('data-count', index);
}

// possiveis ações de uma solicitação, enviar ou  cancelar (listagem)
function ajaxButtonsSolicitacao(seletor) {
	$(seletor)
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url") + $(obj).data("id");
			let title = $(obj).data("titulo");
			let text = $(obj).data("texto");
			let tableDataTable = $(".table-exibe").DataTable();

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

					swal(titulo, texto, tipo);
				})

				toggleLoading();
				tableDataTable.draw();
			}).catch(swal.noop);
		});
}

// seleciona linhas na tabela de exibição dos pedidos de venda
function selecionaLinha() {
	$('table tbody').on('click', 'tr', function () {
		if ($(this).data('selectable') === 'selectable') {
			$(this).toggleClass('selected');

			// só ativamos o botão para exclusão em lote caso haja mais de uma seleção
			if ($('.selected').length > 1) {
				$('.botaoCancelarSelecionadas').addClass('botaoCancelarAtivo')
			} else {
				$('.botaoCancelarSelecionadas').removeClass('botaoCancelarAtivo')
			}
		}
	});
}

/**
 * Função ajaxButtonCancelarPedidoVenda
 * Função para ações de exclusão simples ou em lote de pedidos de venda
 *
 * @param seletor 		Seletor para captura da ação
 * @param titleSwalPre 	Título do alerta antes da ação
 * @param textSwalPre 	Texto do alerta antes da ação
 * @param titleSwalPos 	Título do alerta depois da ação
 * @param textSwalPos	Texto do alerta depois da ação
 * @param many 			Opção para que sejam processados vários itens selecionados
 */
const ajaxButtonCancelarPedidoVenda = function(
	seletor,
	titleSwalPre = "",
	textSwalPre = "",
	titleSwalPos = "",
	textSwalPos = "",
	many = false
) {
	$(seletor)
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);

			// propriedades padrão
			let confirmButtonColor = "#3085d6";
			let cancelButtonColor= "#d33";
			let confirmButtonText = l["continuar!"];
			let cancelButtonText = l["cancelar!"];

			// objeto de envio para o ajax
			let sendObject;

			// no caso de vários itens selecionados
			if (many) {
				let hashkeys = [];
				[...$('.selected')].forEach(function (element) {
					hashkeys.push($(element).data('hash'))
				});

				sendObject = { hashkeys };

			// no caso de um item a excluir
			} else {
				sendObject = {
					"idPedido": $(obj).data("id_pedido"),
					"idSolicitacao": $(obj).data("id_solicitacao")
				}
			}

			swal({
				title: $(obj).data("titulo") ?? titleSwalPre,
				text: $(obj).data("texto") ?? textSwalPre,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor,
				cancelButtonColor,
				confirmButtonText,
				cancelButtonText
			}).then(function () {
				ajaxRequest(
					true,
					$(obj).data("url"),
					null,
					'text',
					sendObject,
					function () {
						swal({
							title: titleSwalPos,
							text: textSwalPos,
							type: "success",
							showCancelButton: false,
							confirmButtonColor,
							cancelButtonColor,
							confirmButtonText
						}).then(function() {
							toggleLoading();

							// como se trata de uma table simples, a página é
							// recarregada após o sucesso na solicitação
							window.location.reload();
						})
					}
				)
			}).catch(swal.noop);
		});
}

$('.valor').maskMoney({
	allowNegative: false,
	thousands: '.',
	decimal: ',',
	precision: $('.data_views').data('casas_preco')
});

$( document ).ready(function() {
	$(".valor").maskMoney('mask');
});

selecionaLinha();
ajaxButtonCancelarPedidoVenda(
	'.botaoCancelarSelecionadas',
	l['cancelarPedidosDeVendaSelecionados'],
	l['osPedidosDevendaSelecionadosSeraoIncluidosNaFilaDeCancelamentoDoProximoCicloDeIntegracaoSeEstesPedidosJaForamVinculadosAAlgumDocumentoNoSAPOPedidoApresentaraMensagensDeErroAoSerProcessado'],
	l['oPedidoDeVendaSelecionadoEntrouNaFilaDeCancelamento'],
	l['oPedidoDeVendaSeraCanceladoCorretamenteCasoNaoHajaVinculoComOutroDocumentoSeHouverVinculoOcorreraoErrosDuranteOProximoCicloDeIntegracao'],
	true
)

ajaxButtonCancelarPedidoVenda(
	'.buttonCancelarPedidoVenda',
	null,
	null,
	l['oPedidoDeVendaSelecionadoEntrouNaFilaDeCancelamento'],
	l['oPedidoDeVendaSeraCanceladoCorretamenteCasoNaoHajaVinculoComOutroDocumentoSeHouverVinculoOcorreraoErrosDuranteOProximoCicloDeIntegracao']
)
