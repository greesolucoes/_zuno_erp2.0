function criaObjetos() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');

	$(".div-ativa_todas_filiais").off("click");
	$(".div-ativa_todas_filiais").on("click", function () {
		if (
			(isOldLayout && is_empty($("#todas-filiais").is(':checked'), 1))
			|| (!isOldLayout && $("#todas-filiais").is(':checked'))
		) {
			$("#geral-filiais-div").addClass("ocultar");
		} else {
			$("#geral-filiais-div").removeClass("ocultar");
		}
	});
}

function controlaSearch() {
	$("#geral-search").off("click");
	$("#geral-search").on("click", function () {
		var url = $($(this).parents('form#geral-form_search')).attr('action');
		let formSerializable = formToStringJson('form#geral-form_search');
		toggleLoading();
		ajaxRequest(true, url, null, 'text', {
			content: formSerializable
		}, function (ret) {
			let divBtnsImprimir = $(`${ isOldLayout ? '#imprimir-relatorio' : '#imprimir-relatorio-btn'}`);
			let divBtnsImprimirItens = $("#imprimir-relatorio-itens");
			try {
				ret = JSON.parse(ret);

				if (!is_empty(ret['bol'], 1)) {
					let divTbl  = $("#relatorio-informacoes");
					let tbl     = $(divTbl).find("table#relatorio-tabela_informacoes");
					let tbody   = $(tbl).find("tbody");
					let tfoot   = $(tbl).find("tfoot");
					let modelo  = $(tbody).find('tr').first().html();
					let newTr   = null;

					$($(tbody).find("tr:not(.ocultar)")).remove();
					$.each(ret['dados'], function (__index, __valores) {
						let idConciliacao = __valores['idConciliacao'];
						if(is_empty(idConciliacao, 1)) {
							idConciliacao = "";
						}

						$(tbody).append('<tr>' + modelo + '</tr>');
						newTr = $($(tbody).find('tr').last());
						$(newTr).data("id_conciliacao", idConciliacao);
						$(newTr).data("id_filial", __valores['filial']);
						$(newTr).data("data_conciliacao", __valores['data']);
						$($(newTr).find("td.relatorio_registro-filial")).text(__valores['filialText']);
						$($(newTr).find("td.relatorio_registro-dia")).text(__valores['data']);
						$($(newTr).find("td.relatorio_registro-status_conciliacao")).text(__valores['statusConciliacao']);
					});
					gerenciaControleLinhasTabelaPesquisa();

					divTbl = $("#print-range_area-relatorio .print-ext-conteudo");
					$(divTbl).html("");

					if(!is_empty(ret['pagamentosTC'], 1)) {
						$(divTbl).append($(".template-table-print-resumo_caixa_agrupado-relatorio").html());

						tbl    = $(divTbl).find("table.conteudo-table-print-resumo_caixa_agrupado-relatorio");
						tbody  = $(tbl).find("tbody");
						tfoot  = $(tbl).find("tfoot");
						modelo =
							"<td class=\"filial\"></td>" +
							"<td class=\"descricao\"></td>" +
							"<td class=\"total_pagamento\"></td>" +
							"<td class=\"total_caixa\"></td>" +
							"<td class=\"diferenca_caixa\"></td>" +
							"<td class=\"justificado_caixa\"></td>";
						newTr = null;

						$.each(ret['pagamentosTC'], function (__index, __valores) {
							$(tbody).append('<tr>' + modelo + '</tr>');
							newTr = $($(tbody).find('tr').last());

							$($(newTr).find("td.filial")).text(__valores['filial']);
							$($(newTr).find("td.descricao")).text(__valores['descricaoFormaPgto']);
							$($(newTr).find("td.total_pagamento")).text(__valores['valoresFormatados']['valorTotalPagamento']);
							$($(newTr).find("td.total_caixa")).text(__valores['valoresFormatados']['valorCaixa']);
							$($(newTr).find("td.diferenca_caixa")).text(__valores['valoresFormatados']['diferencaCaixa']);
							$($(newTr).find("td.justificado_caixa")).text(__valores['valoresFormatados']['valorJustificado']);
						});

						$.each(ret['totaisPagamentosTC'], function (__index, __valores) {
							$(tfoot).append('<tr>' + modelo + '</tr>');
							newTr = $($(tfoot).find('tr').last());

							$($(newTr).find("td.filial")).text(__valores['filial']);
							$($(newTr).find("td.descricao")).text($(tfoot).data("text_total"));
							$($(newTr).find("td.total_pagamento")).text(__valores['valoresFormatados']['valorTotalPagamento']);
							$($(newTr).find("td.total_caixa")).text(__valores['valoresFormatados']['valorCaixa']);
							$($(newTr).find("td.diferenca_caixa")).text(__valores['valoresFormatados']['diferencaCaixa']);
							$($(newTr).find("td.justificado_caixa")).text(__valores['valoresFormatados']['valorJustificado']);
						});
					}

					if(!is_empty(ret['resumosDias'], 1)) {
						$(divTbl).append($(".template-table-print-saldo_agrupado-relatorio").html());

						tbl    = $(divTbl).find("table.conteudo-table-print-saldo_agrupado-relatorio");
						tbody  = $(tbl).find("tbody");
						tfoot  = $(tbl).find("tfoot");
						modelo =
							"<td class=\"filial\"></td>" +
							"<td class=\"data\"></td>" +
							"<td class=\"informacao\"></td>" +
							"<td class=\"valor\"></td>";
						newTr = null;

						$.each(ret['resumosDias'], function (__dataAux, __filiais) {
							$.each(__filiais, function (__filialAux, __valores) {
								//Adicionando saldo anterior
								$(tbody).append('<tr>' + modelo + '</tr>');
								newTr = $($(tbody).find('tr').last());

								$($(newTr).find("td.filial")).text(__valores['saldoAnterior']['filial']);
								$($(newTr).find("td.data")).text(__valores['saldoAnterior']['valoresFormatados']['data']);
								$($(newTr).find("td.informacao")).text(__valores['saldoAnterior']['informacao']);
								$($(newTr).find("td.valor")).text(__valores['saldoAnterior']['valoresFormatados']['valor']);

								//Adicionando saldo caixa
								$(tbody).append('<tr>' + modelo + '</tr>');
								newTr = $($(tbody).find('tr').last());

								$($(newTr).find("td.filial")).text(__valores['saldoCaixa']['filial']);
								$($(newTr).find("td.data")).text(__valores['saldoCaixa']['valoresFormatados']['data']);
								$($(newTr).find("td.informacao")).text(__valores['saldoCaixa']['informacao']);
								$($(newTr).find("td.valor")).text(__valores['saldoCaixa']['valoresFormatados']['valor']);

								//Adicionando mov. caixas
								$.each(__valores['movCaixa'], function (__indexMov, __valoresMov) {
									$(tbody).append('<tr>' + modelo + '</tr>');
									newTr = $($(tbody).find('tr').last());

									$($(newTr).find("td.filial")).text(__valoresMov['filial']);
									$($(newTr).find("td.data")).text(__valoresMov['valoresFormatados']['data']);
									$($(newTr).find("td.informacao")).text(__valoresMov['informacao']);
									$($(newTr).find("td.valor")).text(__valoresMov['valoresFormatados']['valor']);
								});

								//Adicionando saldo final
								$(tbody).append('<tr>' + modelo + '</tr>');
								newTr = $($(tbody).find('tr').last());

								$($(newTr).find("td.filial")).text(__valores['saldoFinal']['filial']);
								$($(newTr).find("td.data")).text(__valores['saldoFinal']['valoresFormatados']['data']);
								$($(newTr).find("td.informacao")).text(__valores['saldoFinal']['informacao']);
								$($(newTr).find("td.valor")).text(__valores['saldoFinal']['valoresFormatados']['valor']);
							});
						});
					}

					if(!is_empty(ret['isRelatorioResumoCaixa'], 1) || !is_empty(ret['isRelatorioSaldos'], 1)) {
						$(divBtnsImprimir).removeClass("ocultar");
					} else {
						$(divBtnsImprimir).addClass("ocultar");
					}
				}

				swal(
					ret['titulo'],
					ret['text'],
					ret['class']
				).catch(swal.noop);

				toggleLoading();
			} catch (err) {
				swal(
					l["erro!"],
					l["tempoDeRespostaDoServidorEsgotado!"],
					"error"
				).catch(swal.noop);

				$("#relatorio-informacoes table#relatorio-tabela_informacoes tbody tr:not(.ocultar)").remove();
				$("#print-range_area-relatorio .print-ext-conteudo").html("");
				$(divBtnsImprimir).addClass("ocultar");
				gerenciaControleLinhasTabelaPesquisa();

				forceToggleLoading(0);
			}
		});
	});
}

function gerenciaControleLinhasTabelaPesquisa() {
	$('#relatorio-informacoes table#relatorio-tabela_informacoes').off('click');
	$('#relatorio-informacoes table#relatorio-tabela_informacoes').on('click', 'tbody tr:not(.ocultar)', function() {
		let obj = $(this);
		const idConciliacao = $(obj).data("id_conciliacao");
		const url = $(".data_views").data("url_get_informacoes_conciliacao");

		toggleLoading();
		ajaxRequest(true, url, null, 'text', {
			idConciliacao: idConciliacao
		}, function (ret) {
			try {
				ret = JSON.parse(ret);
				if (!is_empty(ret['bol'], 1)) {
					$(obj).addClass("controla_modal");
					$("#modal-visualizar_conciliacao .modal-body").html(ret['text']);
					$('#modal-visualizar_conciliacao').modal('toggle');
					//exibe o botao da primeira aba: resumo
					$("div.buttons_resumo").css("display", "block");
				} else {
					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
				}

				toggleLoading();
			} catch (err) {
				swal(
					l["erro!"],
					l["tempoDeRespostaDoServidorEsgotado!"],
					"error"
				).catch(swal.noop);
				forceToggleLoading(0);
			}
		});
	});
}

function printResumoPDF(){
	$('#imprimir-relatorio button.print-preview').off('click');
	$('#imprimir-relatorio button.print-preview').on('click', function() {
		$(this).prop("disabled", true);
		$(this).removeClass('fa-print');

		$(this).html('<i class="fa fa-spinner fa-pulse fa-fw"></i> ' + l["carregando"]);
		$('#print-range_area-relatorio').printThis({
//                printDelay: 1000,
			pageTitle: l["itens"],
			footer: 'ManyMinds'
		});

		const obj = $(this);
		setTimeout(function(){
			if (isOldLayout) {
				$(obj).addClass('fa-print');
				$(obj).html(" " + $(obj).data('imprimir_novamente'));
			} else {
				$(obj).html(
					`<span data-icon="vscode-icons:file-type-pdf2" class="iconify fs-8" style="transform: translateX(-1rem);"></span>` +
					`<span class="mt-3 fw-bold text-center txt-blue2">${$(obj).data('imprimir_novamente')}</span>`
				);
			}
			$(obj).prop("disabled", false);
		}, 1000);
	});
}

function printResumoExcel() {
	$('#imprimir-relatorio button.imprimir_excel').off('click');
	$('#imprimir-relatorio button.imprimir_excel').on('click', function() {
		$(this).prop("disabled", true);
		$(this).removeClass('fa-print');

		$(this).html('<i class="fa fa-spinner fa-pulse fa-fw"></i> ' + l["carregando"]);
		download_file(
			$('.data_views').data('nome_impressao_excel') + ".xls",
			$('#print-range_area-relatorio').html(),
			'application/vnd.ms-excel'
		);

		const obj = $(this);
		setTimeout(function(){
			if (isOldLayout) {
				$(obj).addClass('fa-print')
				$(obj).html($(obj).data('imprimir_novamente'));
			} else {
				$(obj).html(
					`<span data-icon="vscode-icons:file-type-excel" class="iconify fs-8" style="transform: translateX(-1rem);"></span>` +
					`<span class="mt-3 fw-bold text-center txt-blue2">${$(obj).data('imprimir_novamente')}</span>`
				);
			}
			$(obj).prop("disabled", false);
		}, 1000);
	});
}

criaObjetos();
controlaSearch();
gerenciaControleLinhasTabelaPesquisa();
printResumoPDF();
printResumoExcel();

//https://stackoverflow.com/questions/19305821/multiple-modals-overlay
$(document).on('show.bs.modal', '.modal', function () {
	let zIndex = 1040 + (10 * $('.modal:visible').length);
	$(this).css('z-index', zIndex);
	setTimeout(function() {
		$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
	}, 0);
});
$(document).on('hidden.bs.modal', '.modal', function () {
	if($('.modal:visible').length > 0) {
		$(document.body).addClass('modal-open');
	} else {
		$("#relatorio-informacoes table#relatorio-tabela_informacoes tbody tr").removeClass("controla_modal");
		$("#modal-visualizar_conciliacao .modal-body").html("");
	}
});