function createFieldAnexos() {
	recriar($("div#documentos_anexo"));
	$('div#documentos_anexo').allUpload(
		'documentos_anexo-names[]',
		'documentos_anexo-blobs[]',
		function (obj) {
			if(is_empty($("div.data_views").data("vizualizacao"), 1)) {
				let name = $(obj).parents('.preview-doc').find(".file-name").val();
				let src = $(obj).parents('.preview-doc').find('.doc-zone img').prop('src');

				$(".modal-visualiza_anexo .modal-content .modal-title").text(name);
				$(".modal-visualiza_anexo .modal-content .modal-body").html('<img src="' + src + '" style="max-width:100%; margin:0 auto; display: block;" />');
				$('.modal-visualiza_anexo').modal('show');
			}

			return false;
		},
		'.preview-docs-zone',
		{
			"textUpload": $(".data_views").data("text_upload"),
			"textVisualize": $(".data_views").data("text_visualize_upload"),
			"noDocsText": $(".data_views").data("text_no_docs_upload"),
			"obsText": $(".data_views").data("text_obs_upload"),
		},
		function (obj) {
			let idDoc = $(obj).data("id");
			if(is_empty(idDoc, 1)) {
				idDoc = "";
			}
			$(obj).append('<input class="noEffect file-id" style="display: none;" name="documentos_anexo-ids[]" value="' + idDoc + '" />');

			$(obj).append('<div class="tools-name-doc">' + ($($(obj).find(".file-name")).val()) + '</div>');

			let srcCheck = $($(obj).find(".file-blob")).val().toLowerCase().split(";")[0];
			if (!srcCheck.includes("image")) {
				let fileIcon = "";
				if (srcCheck.includes("text")) {
					fileIcon = isOldLayout ? 'fa fa-file-text-o' : 'fa-regular fa-file-lines';
				} else if (srcCheck.includes("excel")) {
					fileIcon = isOldLayout ? 'fa fa-file-excel-o' : 'fa-regular fa-file-excel';
				} else if (srcCheck.includes("pdf")) {
					fileIcon = isOldLayout ? 'fa fa-file-pdf-o' : 'fa-regular fa-file-pdf';
				} else if (srcCheck.includes("word")) {
					fileIcon = isOldLayout ? 'fa fa-file-word-o' : 'fa-regular fa-file-word';
				} else {
					fileIcon = isOldLayout ? 'fa fa-eye-slash' : 'fa-regular fa-eye-slash';
				}

				$($(obj).find(".text-zone")).html("<i class='" + fileIcon + "' style='font-size: 10em;'></i>");
				fileIcon = null;

				if(is_empty($("div.data_views").data("vizualizacao"), 1)) {
					$($(obj).find(".action-visualize")).remove();
				}
			}
			srcCheck = null;

			if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
				const url = $("div.data_views").data("url_baixar_anexos");
				let id = $(obj).data('id');
				$($(obj).find(".action-visualize")).attr("href", (url + id));
				$($(obj).find(".action-visualize")).attr("target", "_blank");

				$($(obj).find(".action-visualize")).html($(".data_views").data("text_download_upload"));
			}
		}
	);
	if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
		$('div#documentos_anexo .link-adiciona-files').remove();
	}
}

function initFields() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');

	createFieldAnexos();
	$('.modal-visualiza_anexo').off('hidden.bs.modal');
	$('.modal-visualiza_anexo').on('hidden.bs.modal', function (e) {
		$($(this).find(".modal-content .modal-title")).text("");
		$($(this).find(".modal-content .modal-body")).text("");
	});
}

function initButtons() {
	$('button.toggle_replicacao').off('click');
	$('button.toggle_replicacao').on('click', function () {
		if(is_empty($("div.data_views").data("vizualizacao"))) {
			return;
		}

		if($(".grupo_replicacao").hasClass("ocultar")) {
			$(".grupo_replicacao").removeClass("ocultar");
		} else {
			$(".grupo_replicacao").addClass("ocultar");
		}
	});
}

function controlaSelectCliente() {
	if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
		return;
	}

	let __funControlaFormasPagamento = function (obj) {
		let select = $("select#financeiro-forma_pagamento");
		let data_views = $("div.data_views");
		if ($(select).hasClass("select2-hidden-accessible")){
			$(select).select2('destroy');
		}

		$($(select).find("option")).remove();
		$(select).data(
			"url",
			($(data_views).data("url_ajax_formas_pagamento") + (is_empty($(obj).val(), 1) ? "" : $(obj).val()))
		);

		$(select).select2Ajax();
	};

	$("select#geral-id_clientes").off("select2:unselect");
	$("select#geral-id_clientes").on("select2:unselect", function () {
		__funControlaFormasPagamento($(this));
	});

	$("select#geral-id_clientes").off("select2:select");
	$("select#geral-id_clientes").on("select2:select", function () {
		__funControlaFormasPagamento($(this));
	});
}

function controlaSelectMoeda() {
	if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
		return;
	}

	let prefixoAnteriorMoeda = $("div.data_views").data('prefixo_moeda');
	let sepMilharAnteriorMoeda = $("div.data_views").data('thousand_delimiter_moeda');
	let sepDecimalAnteriorMoeda = $("div.data_views").data('decimal_delimiter_moeda');

	let prefixoAgoraMoeda = prefixoAnteriorMoeda;
	let sepMilharAgoraMoeda = sepMilharAnteriorMoeda;
	let sepDecimalAgoraMoeda = sepDecimalAnteriorMoeda;

	let casasPreco = $("div.data_views").data('casas_preco');
	let urlMoeda = $("div.data_views").data('url_ajax_moedas');

	let __funObterValoresMoeda = function (obj, isUnselect) {
		let __funFormatarValores = function () {
			if(prefixoAgoraMoeda === prefixoAnteriorMoeda && sepMilharAgoraMoeda === sepMilharAnteriorMoeda && sepDecimalAgoraMoeda === sepDecimalAnteriorMoeda) {
				return;
			}

			recriar($("input.valores"));
			$("input.valores").each(function () {
				if($(this).data("mask") === "numerov2") {
					$(this).data('prefixo', prefixoAgoraMoeda);
					$(this).data('thousand_delimiter', sepMilharAgoraMoeda);
					$(this).data('decimal_delimiter', sepDecimalAgoraMoeda);
				}
				$(this).val(formataDecimal($(this).val(), sepDecimalAnteriorMoeda, sepDecimalAgoraMoeda, sepMilharAgoraMoeda, prefixoAgoraMoeda, true, casasPreco));
			});
			$("input.valores[data-mask='numerov2']").fnMascaraNumeroV2();

			$("div.conteudo-itens_total_linha").each(function () {
				$(this).text(formataDecimal($.trim($(this).text()), sepDecimalAnteriorMoeda, sepDecimalAgoraMoeda, sepMilharAgoraMoeda, prefixoAgoraMoeda, true, casasPreco));
			});

			$("#conteudo-itens_total_geral").text(formataDecimal($.trim($("#conteudo-itens_total_geral").text()), sepDecimalAnteriorMoeda, sepDecimalAgoraMoeda, sepMilharAgoraMoeda, prefixoAgoraMoeda, true, casasPreco));

			prefixoAnteriorMoeda = prefixoAgoraMoeda;
			sepMilharAnteriorMoeda = sepMilharAgoraMoeda;
			sepDecimalAnteriorMoeda = sepDecimalAgoraMoeda;

			$("div.data_views").data('prefixo_moeda', prefixoAgoraMoeda);
			$("div.data_views").data('thousand_delimiter_moeda', sepMilharAgoraMoeda);
			$("div.data_views").data('decimal_delimiter_moeda', sepDecimalAgoraMoeda);
		};

		toggleLoading();
		if(!is_empty(isUnselect, true)) {
			prefixoAgoraMoeda = $("div.data_views").data('prefixo_padrao_moeda');
			sepMilharAgoraMoeda = $("div.data_views").data('thousand_delimiter_padrao_moeda');
			sepDecimalAgoraMoeda = $("div.data_views").data('decimal_delimiter_padrao_moeda');

			__funFormatarValores();
			toggleLoading();
		} else {
			ajaxRequest(true, (urlMoeda + $(obj).val()), null, 'text', null, function (ret) {
				try{
					ret = JSON.parse(ret);
				} catch(err) {
					ret = [];
				}

				if(!is_empty(ret, 1)) {
					prefixoAgoraMoeda = ret['textoImpressao'];
					sepMilharAgoraMoeda = ret['separadorMilhar'];
					sepDecimalAgoraMoeda = ret['separadorDecimal'];
				} else {
					prefixoAgoraMoeda = $("div.data_views").data('prefixo_padrao_moeda');
					sepMilharAgoraMoeda = $("div.data_views").data('thousand_delimiter_padrao_moeda');
					sepDecimalAgoraMoeda = $("div.data_views").data('decimal_delimiter_padrao_moeda');
				}

				__funFormatarValores();
				toggleLoading();
			});
		}
	};

	$("select#geral-id_compras_moedas").off("select2:unselect");
	$("select#geral-id_compras_moedas").on("select2:unselect", function () {
		__funObterValoresMoeda($(this), true);
	});

	$("select#geral-id_compras_moedas").off("select2:select");
	$("select#geral-id_compras_moedas").on("select2:select", function () {
		__funObterValoresMoeda($(this), false);
	});
}

function controlaSelectProduto() {
	let __funControlaUnidadesMedidas = function (objUnidade, objVal) {
		if(is_empty(objUnidade, 1) || $(objUnidade).length === 0) {
			return;
		}

		let data_views = $("div.data_views");
		if ($(objUnidade).hasClass("select2-hidden-accessible")){
			$(objUnidade).select2('destroy');
		}

		$($(objUnidade).find("option")).remove();
		$(objUnidade).data(
			"url",
			($(data_views).data("url_ajax_unidades") + (is_empty(objVal, 1) ? "" : objVal))
		);

		$(objUnidade).select2Ajax();
	};

	$("select.conteudo-itens_id_item").off("select2:unselect");
	$("select.conteudo-itens_id_item").on("select2:unselect", function () {
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), null);
	});

	$("select.conteudo-itens_id_item").off("select2:select");
	$("select.conteudo-itens_id_item").on("select2:select", function () {
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), $(this).val());
	});
}

function salvarDados() {
	if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
		return;
	}

	$("button.salvar").off("click");
	$("button.salvar").on("click", function (e) {
		let url = $("div.data_views").data("url_salvar");
		let save = null;
		if(is_empty(url, 1)) {
			return;
		}

		swal({
			title: l["desejaContinuar?"],
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["sim!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			toggleLoading();

			save = {
				idDFisNotasFiscaisSaida: $("#geral-id_nota_fiscal").val(),
				idClientes: $("#geral-id_clientes").val(),
				idComprasMoedas: (
					$("#geral-id_compras_moedas").length > 0
						? $("#geral-id_compras_moedas").val()
						: null
				),
				dataLancamento: $("#conteudo-data_lancamento").val(),
				dataDocumento: $("#conteudo-data_documento").val(),
				itens: [],
				despesas: [],
				parcelas: [],
				anexos: [],
				idFormasPagamento: $("#financeiro-forma_pagamento").val(),
				idSequencias: $("#imposto-sequencia").val(),
				observacoes: $.trim($("#geral-observacoes").val()),
			};

			if($("table#conteudo-itens-tabela tbody tr:not(.ocultar)").length > 0) {
				$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").each(function () {
					if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
						return;
					}

					save["itens"].push({
						idDFisItensNotasFiscaisSaida: $(this).find(".conteudo-itens_id_interno").val(),
						precoUnitario: $(this).find(".conteudo-itens_preco_unitario").val(),
						quantidade: $(this).find(".conteudo-itens_quantidade").val(),
						idProdutos: $(this).find(".conteudo-itens_id_item").val(),
						idUnidadesMedidas: $(this).find(".conteudo-itens_unidade").val(),
						idDespesasAdicionais1: $(this).find(".conteudo-itens_despesa1").val(),
						valorDespesa1: $(this).find(".conteudo-itens_valor_despesa1").val(),
						idDespesasAdicionais2: $(this).find(".conteudo-itens_despesa2").val(),
						valorDespesa2: $(this).find(".conteudo-itens_valor_despesa2").val(),
						idDespesasAdicionais3: $(this).find(".conteudo-itens_despesa3").val(),
						valorDespesa3: $(this).find(".conteudo-itens_valor_despesa3").val(),
						idDepositos: $(this).find(".conteudo-itens_deposito").val(),
						idTipoUtilizacaoProduto: $(this).find(".conteudo-itens_utilizacao").val(),
						idRegraDistribuicao1: (
							$(this).find(".conteudo-itens_regra1").length > 0
								? $(this).find(".conteudo-itens_regra1").val()
								: null
						),
						idRegraDistribuicao2: (
							$(this).find(".conteudo-itens_regra2").length > 0
								? $(this).find(".conteudo-itens_regra2").val()
								: null
						),
						idRegraDistribuicao3: (
							$(this).find(".conteudo-itens_regra3").length > 0
								? $(this).find(".conteudo-itens_regra3").val()
								: null
						),
						idRegraDistribuicao4: (
							$(this).find(".conteudo-itens_regra4").length > 0
								? $(this).find(".conteudo-itens_regra4").val()
								: null
						),
						idRegraDistribuicao5: (
							$(this).find(".conteudo-itens_regra5").length > 0
								? $(this).find(".conteudo-itens_regra5").val()
								: null
						),
					});
				});
			}

			if($("table#conteudo-despesas-tabela tbody tr:not(.ocultar)").length > 0) {
				$("table#conteudo-despesas-tabela tbody tr:not(.ocultar)").each(function () {
					if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
						return;
					}

					save["despesas"].push({
						idDFisDespesasNotasFiscaisSaida: $(this).find(".conteudo-despesas_id_interno").val(),
						idDespesasAdicionais: $(this).find(".conteudo-despesas_despesa").val(),
						valor: $(this).find(".conteudo-despesas_valor_despesa").val(),
					});
				});
			}

			if($("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)").length > 0) {
				$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)").each(function () {
					if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
						return;
					}

					save["parcelas"].push({
						idDFisParcelasNotasFiscaisSaida: $(this).find(".conteudo-parcelas_id_interno").val(),
						valor: $(this).find(".conteudo-parcelas_valor").val(),
						dataVencimento: $(this).find(".conteudo-parcelas_data_vencimento").val(),
					});
				});
			}

			if($("#documentos_anexo .preview-doc").length > 0) {
				$("#documentos_anexo .preview-doc").each(function () {
					let objAnexo = {};
					objAnexo['idDFisAnexosNotasFiscaisSaida'] = is_empty($(this).find(".file-id").val(), 1) ? null : $(this).find(".file-id").val();
					if(objAnexo['idDFisAnexosNotasFiscaisSaida'] === null) {
						objAnexo['nomeArquivo'] = $(this).find(".file-name").val();
						objAnexo['anexo'] = $(this).find(".file-blob").val();
					}

					save["anexos"].push(objAnexo);
				});
			}

			ajaxRequest(true, url, null, 'text', {'save': save}, function (ret) {
				try{
					ret = JSON.parse(ret);
					if(!is_empty(ret['bol'], 1)) {
						if(is_empty(save['idDFisNotasFiscaisSaida'], 1)) {
							$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").remove();
							$("table#conteudo-despesas-tabela tbody tr:not(.ocultar)").remove();
							$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)").remove();

							$("input:not([type='hidden'])").val("");
							$("textarea").val("");
							$("select.select_ajax:not(#geral-id_compras_moedas)").val("").trigger('change').trigger('select2:unselect');

							$("#documentos_anexo").html("");
							createFieldAnexos();

							recriar($("input[data-mask='numerov2']"));
							$("input[data-mask='numerov2']").val("");
							$("input[data-mask='numerov2']").fnMascaraNumeroV2();

							triggerSomaCampos();
							somaCamposAll();
						}

						$("#geral-doc_status").text(ret['notaFiscal']['statusText']);
					}

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);

					toggleLoading();
				}catch(err){
					consoleProduction(err);
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			});
		}, function () {
			//SE DER ERRO
		}).catch(swal.noop);
	});
}

function somaCamposAll(ignoreSomaGeral) {
	somaCamposItens();
	somaCamposParcelas();
	somaCamposDespesas();
	if(is_empty(ignoreSomaGeral, 1)) {
		somaCamposGeral();
	}
}

function somaCamposItens(atualizarLinhaEspecifica) {
	const linhas   = $("table#conteudo-itens-tabela tbody tr:not(.ocultar)");

	let casasPreco = $("div.data_views").data('casas_preco');
	if(is_empty(casasPreco, 1)) casasPreco = '0';
	casasPreco = parseInt(casasPreco.toString());

	const cifrao_moeda = $("div.data_views").data('prefixo_moeda');
	const separador_decimal_moeda = $("div.data_views").data('decimal_delimiter_moeda');
	const separador_milhar_moeda = $("div.data_views").data('thousand_delimiter_moeda');

	const separador_decimal_qtd = $("div.data_views").data('decimal_delimiter_qtd');

	let valorTotaisLinhas = 0;
	let valorLinha = 0;
	$.each(linhas, function (idLinha, linha) {
		valorLinha = 0;
		if(is_empty(atualizarLinhaEspecifica, 1) || !is_empty($(linha).data("atualizar"), 1)) {
			valorLinha =
				(
					stringParaFloat($(linha).find('.conteudo-itens_quantidade').val(), separador_decimal_qtd, true) *
					stringParaFloat($(linha).find('.conteudo-itens_preco_unitario').val(), separador_decimal_moeda, true)
				) +
				stringParaFloat($(linha).find('.conteudo-itens_valor_despesa1').val(), separador_decimal_moeda, true) +
				stringParaFloat($(linha).find('.conteudo-itens_valor_despesa2').val(), separador_decimal_moeda, true) +
				stringParaFloat($(linha).find('.conteudo-itens_valor_despesa3').val(), separador_decimal_moeda, true);
			if(is_empty_numeric(valorLinha)) {
				valorLinha = 0;
			}
			$(linha).find('.conteudo-itens_total_linha').text(
				formataDecimal(
					valorLinha,
					".",
					separador_decimal_moeda,
					separador_milhar_moeda,
					cifrao_moeda,
					true,
					casasPreco
				)
			);

			$(linha).data("atualizar", 0);
		}

		valorTotaisLinhas += stringParaFloat($(linha).find('.conteudo-itens_total_linha').text(), separador_decimal_moeda, true);
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasPreco).toString(),
			'.',
			true
		);

	$('#conteudo-itens_total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			separador_decimal_moeda,
			separador_milhar_moeda,
			cifrao_moeda,
			true,
			casasPreco
		)
	);
}

function somaCamposParcelas() {
	const linhas   = $("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)");
	let casasPreco = $("div.data_views").data('casas_preco');

	if(is_empty(casasPreco, 1)) casasPreco = '0';
	casasPreco = parseInt(casasPreco.toString());

	const cifrao_moeda = $("div.data_views").data('prefixo_moeda');
	const separador_decimal_moeda = $("div.data_views").data('decimal_delimiter_moeda');
	const separador_milhar_moeda = $("div.data_views").data('thousand_delimiter_moeda');

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		valorTotaisLinhas += stringParaFloat(
			$(linha).find('.conteudo-parcelas_valor').val(), separador_decimal_moeda, true
		);
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasPreco).toString(),
			'.',
			true
		);

	$('#conteudo-parcelas_total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			separador_decimal_moeda,
			separador_milhar_moeda,
			cifrao_moeda,
			true,
			casasPreco
		)
	);
}

function somaCamposDespesas() {
	const linhas   = $("table#conteudo-despesas-tabela tbody tr:not(.ocultar)");
	let casasPreco = $("div.data_views").data('casas_preco');

	if(is_empty(casasPreco, 1)) casasPreco = '0';
	casasPreco = parseInt(casasPreco.toString());

	const cifrao_moeda = $("div.data_views").data('prefixo_moeda');
	const separador_decimal_moeda = $("div.data_views").data('decimal_delimiter_moeda');
	const separador_milhar_moeda = $("div.data_views").data('thousand_delimiter_moeda');

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		valorTotaisLinhas += stringParaFloat(
			$(linha).find('.conteudo-despesas_valor_despesa').val(), separador_decimal_moeda, true
		);
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasPreco).toString(),
			'.',
			true
		);

	$('#conteudo-despesas_total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			separador_decimal_moeda,
			separador_milhar_moeda,
			cifrao_moeda,
			true,
			casasPreco
		)
	);
}

function somaCamposGeral() {
	let totalItens        = $("table#conteudo-itens-tabela tfoot tr #conteudo-itens_total_geral");
	let totalDespesas     = $("table#conteudo-despesas-tabela tfoot tr #conteudo-despesas_total_geral");
	let casasPreco        = $("div.data_views").data('casas_preco');

	if(is_empty(casasPreco, 1)) casasPreco = '0';
	casasPreco = parseInt(casasPreco.toString());

	const cifrao_moeda = $("div.data_views").data('prefixo_moeda');
	const separador_decimal_moeda = $("div.data_views").data('decimal_delimiter_moeda');
	const separador_milhar_moeda = $("div.data_views").data('thousand_delimiter_moeda');

	let valorTotaisLinhas = 0;
	valorTotaisLinhas += stringParaFloat($(totalItens).text(), separador_decimal_moeda, true);
	valorTotaisLinhas += stringParaFloat($(totalDespesas).text(), separador_decimal_moeda, true);
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasPreco).toString(),
			'.',
			true
		);

	$('#conteudo-total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			separador_decimal_moeda,
			separador_milhar_moeda,
			cifrao_moeda,
			true,
			casasPreco
		)
	);
}

function triggerSomaCampos() {
	if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
		return;
	}

	$("table#conteudo-itens-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		$($(this).parents("tr")).data("atualizar", 1);
		somaCamposItens(true);
		somaCamposGeral();
	});

	$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposParcelas();
	});

	$("table#conteudo-despesas-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposDespesas();
		somaCamposGeral();
	});
}

function atualizaLinhasParcelas() {
	let index = 0;
	$("table#conteudo-parcelas-tabela tbody tr .linha").each(function () {
		$(this).text(index);
		index++;
	});
}

let initByElementReplicancias = {}
function controlaReplicancias_addValores(idElemento, objReplicar) {
	if(
		is_empty(idElemento, 1) || is_empty(objReplicar, 1) || (
			!is_empty($($(objReplicar).parents('table')), 1) && $($(objReplicar).parents('table')).hasClass("ocultar")
		)
	) {
		return;
	}

	$(objReplicar).append(
		$('<option/>').attr('value', initByElementReplicancias[idElemento]['value']).text(initByElementReplicancias[idElemento]['text'])
	).val(initByElementReplicancias[idElemento]['value']).trigger('change');
	if(is_empty(initByElementReplicancias[idElemento]['value'], 1)) {
		$(objReplicar).trigger("select2:unselect");
	} else {
		$(objReplicar).trigger("select2:select");
	}
}

function controlaReplicancias() {
	let __replicarDadosCabecalho = function (obj) {
		if(is_empty($("div.data_views").data("vizualizacao"))) {
			return;
		}
		if(
			is_empty($(obj).data('replicar_para'), 1) ||
			is_empty($(obj).attr('id'), 1)
		) {
			return;
		}

		if(initByElementReplicancias[$(obj).attr('id')] === undefined) {
			initByElementReplicancias[$(obj).attr('id')] = {
				replicar_para: $(obj).data('replicar_para'),
				text: "",
				value: "",
			}
		}

		swal({
			title: l["desejaContinuar?"],
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["sim!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			initByElementReplicancias[$(obj).attr('id')]['value'] = $(obj).val();
			initByElementReplicancias[$(obj).attr('id')]['text'] = $(obj).find("option:selected").text();
			if(is_empty($(obj).val(), 1)) {
				initByElementReplicancias[$(obj).attr('id')]['value'] = "";
				initByElementReplicancias[$(obj).attr('id')]['text'] = "";
			}

			$(initByElementReplicancias[$(obj).attr('id')]['replicar_para']).each(function () {
				controlaReplicancias_addValores($(obj).attr('id'), $(this));
			});
		}, function () {
			$(obj).append(
				$('<option/>').attr('value', initByElementReplicancias[$(obj).attr('id')]['value']).text(initByElementReplicancias[$(obj).attr('id')]['text'])
			).val(initByElementReplicancias[$(obj).attr('id')]['value']).trigger('change');
		}).catch(swal.noop);
	}

	$("select#replicancia-utilizacao").off("select2:select");
	$("select#replicancia-utilizacao").on("select2:select", function () {
		__replicarDadosCabecalho($(this));
	});

	$("select#replicancia-utilizacao").off("select2:unselect");
	$("select#replicancia-utilizacao").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__replicarDadosCabecalho($(this));
	});

	$("select#replicancia-deposito").off("select2:select");
	$("select#replicancia-deposito").on("select2:select", function () {
		__replicarDadosCabecalho($(this));
	});

	$("select#replicancia-deposito").off("select2:unselect");
	$("select#replicancia-deposito").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__replicarDadosCabecalho($(this));
	});

	$("select.replicancia-regras").off("select2:select");
	$("select.replicancia-regras").on("select2:select", function () {
		__replicarDadosCabecalho($(this));
	});

	$("select.replicancia-regras").off("select2:unselect");
	$("select.replicancia-regras").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__replicarDadosCabecalho($(this));
	});
}

initFields();
initButtons();
controlaSelectCliente();
controlaSelectMoeda();
controlaSelectProduto();
salvarDados();
somaCamposAll(true);
triggerSomaCampos();
if(is_empty($("div.data_views").data("vizualizacao"), 1)) {
	contaCaracteres(254, 'geral-observacoes');
}
controlaTabelaSuite({
	"ref": "#conteudo-itens-tabela",
	"funAposAddItem": function () {
		controlaSelectProduto();
		triggerSomaCampos();
		$.each(initByElementReplicancias, function(idElemento, valores) {
			controlaReplicancias_addValores(idElemento, $($($("#conteudo-itens-tabela").find(valores['replicar_para'])).last()));
		});
	},
	"funAposRemoverItem": function () {
		somaCamposItens();
		$.each(initByElementReplicancias, function(idElemento, valores) {
			controlaReplicancias_addValores(idElemento, $($($("#conteudo-itens-tabela").find(valores['replicar_para'])).last()));
		});
		somaCamposGeral();
	}
});
controlaTabelaSuite({
	"ref": "#conteudo-despesas-tabela",
	"funAposAddItem": function () {
		triggerSomaCampos();
		$.each(initByElementReplicancias, function(idElemento, valores) {
			controlaReplicancias_addValores(idElemento, $($($("#conteudo-despesas-tabela").find(valores['replicar_para'])).last()));
		});
	},
	"funAposRemoverItem": function () {
		somaCamposDespesas();
		somaCamposGeral();
		$.each(initByElementReplicancias, function(idElemento, valores) {
			controlaReplicancias_addValores(idElemento, $($($("#conteudo-parcelas-tabela").find(valores['replicar_para'])).last()));
		});
	}
});
controlaTabelaSuite({
	"ref": "#conteudo-parcelas-tabela",
	"funAposAddItem": function () {
		atualizaLinhasParcelas();
		triggerSomaCampos();
	},
	"funAposRemoverItem": function () {
		atualizaLinhasParcelas();
		$.each(initByElementReplicancias, function(idElemento, valores) {
			controlaReplicancias_addValores(idElemento, $($($("#conteudo-parcelas-tabela").find(valores['replicar_para'])).last()));
		});
		somaCamposGeral();
	}
});
controlaReplicancias();

//funcao para ordernar campos
function sortTable() {
	var table, rows, switching, i, x, y, shouldSwitch;
	table = document.getElementById("conteudo-itens-tabela");
	switching = true;
	while (switching) {
		switching = false;
		rows = table.rows;
		for (i = 1; i < (rows.length - 1); i++) {
			shouldSwitch = false;

			//Visualização
			x = rows[i].querySelector(".conteudo-itens_nome_item") != null ? rows[i].querySelector(".conteudo-itens_nome_item").innerHTML : null;
			y = rows[i + 1].querySelector(".conteudo-itens_nome_item") != null ? rows[i + 1].querySelector(".conteudo-itens_nome_item").innerHTML : null;

			x = !is_empty(x) ? x.split(' - ')[1].toLowerCase() : 'zzzzzz'; //foi colocado zzzzzz para quando ordernar ordem alfabetica e n tiver texto, ele jogar para ultima posição
			y = !is_empty(y) ? y.split(' - ')[1].toLowerCase() : 'zzzzzz'; //foi colocado zzzzzz para quando ordernar ordem alfabetica e n tiver texto, ele jogar para ultima posição

			if (x > y && !is_empty(x) && !is_empty(y)) {
				shouldSwitch = true;
				break;
			}
		}
		if (shouldSwitch) {
			rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
			switching = true;
		}
	}
	$('#conteudo-itens-tabela tbody tr td .removeItens').attr('disabled', false);
	$('#conteudo-itens-tabela tbody tr td .removeItens').first().attr('disabled', true);
}