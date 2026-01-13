// cria o dropbox do anexo
function createFieldAnexos() {
	recriar($("div#documentos_anexo"));
	$('div#documentos_anexo').allUpload(
		'conteudo-anexos_name[]',
		'conteudo-anexos_blob[]',
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
			$(obj).append('<input class="noEffect file-id" style="display: none;" name="conteudo-anexos_id_interno[]" value="' + idDoc + '" />');

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
		if($(".grupo_replicacao").hasClass("ocultar")) {
			$(".grupo_replicacao").removeClass("ocultar");
		} else {
			$(".grupo_replicacao").addClass("ocultar");
		}
	});
}

function controlaSelectFornecedor() {
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

	$("select#geral-id_fornecedores").off("select2:unselect");
	$("select#geral-id_fornecedores").on("select2:unselect", function () {
		__funControlaFormasPagamento($(this));
	});

	$("select#geral-id_fornecedores").off("select2:select");
	$("select#geral-id_fornecedores").on("select2:select", function () {
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

	let selectItensIdItemConteudo = $("select.conteudo-itens_id_item");

	let __funControlaUnidadesMedidas = function (objUnidade, objVal, ObjAux) {
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
			($(data_views).data("url_ajax_unidades") + (is_empty(objVal, 1) ? "" : objVal) + (is_empty(objVal, 1) ? "" : `/${ObjAux}`))
		);

		$(objUnidade).select2Ajax();
	};
	let __funControlaLote = function (objBtnLote, objTextareaLotes, objVal) {
		if(is_empty(objBtnLote, 1) || $(objBtnLote).length === 0) {
			return;
		}
		if(is_empty(objTextareaLotes, 1) || $(objTextareaLotes).length === 0) {
			return;
		}
		let isControlarLotes = $("div.data_views").data("is_controlar_lote");

		$(objTextareaLotes).val("[]");
		if(is_empty(objVal, 1) || is_empty(isControlarLotes, 1)) {
			$(objBtnLote).addClass("ocultar");
			return;
		}

		let url = $("div.data_views").data("url_verifica_produto_adm_lote");
		toggleLoading();
		ajaxRequest(true, url, null, 'text', {'idProduto': objVal}, function (ret) {
			try{
				if(!is_empty(ret, 1)) {
					$(objBtnLote).removeClass("ocultar");
				} else {
					$(objBtnLote).addClass("ocultar");
				}

				toggleLoading();
			}catch(err){
				swal(
					l["erro!"],
					l["tempoDeRespostaDoServidorEsgotado!"],
					"error"
				).catch(swal.noop);
				forceToggleLoading(0);
			}
		});
	};

	/** ITEM DO CONTEUDO */
	selectItensIdItemConteudo.off("select2:unselect");
	selectItensIdItemConteudo.on("select2:unselect", function () {
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), null, null);
		__funControlaLote($($(this).parents("tr").find(".controlar_lote")), $($(this).parents("tr").find(".conteudo-itens_lotes_json")), null);
	});

	selectItensIdItemConteudo.off("select2:select");
	selectItensIdItemConteudo.on("select2:select", function () {
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), $(this).val(), null);
		__funControlaLote($($(this).parents("tr").find(".controlar_lote")), $($(this).parents("tr").find(".conteudo-itens_lotes_json")), $(this).val());
	});
	/** FIM ITEM DO CONTEUDO */

}

function salvarDados() {
	if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
		return;
	}

	$("button.salvar").off("click");
	$("button.salvar").on("click", function (e) {
		let urlThis = $("div.data_views").data("url_add");
		let url = $($(this).parents('form#form-to-serialize')).attr('action');
		let idNota = null;
		let save = null;
		let text = "";
		if(is_empty(url, 1)) {
			return;
		}

		swal({
			title: l["desejaContinuar?"],
			text: text,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["sim!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			toggleLoading();

			save = formToStringJson('form#form-to-serialize');
			idNota = !is_empty($("#geral-id_devolucao_nota_fiscal").val(), true) ? $("#geral-id_devolucao_nota_fiscal").val() : null;

			ajaxRequest(true, url, null, 'text', {'save': save}, function (ret) {
				try{
					ret = JSON.parse(ret);
					if(!is_empty(ret['bol'], 1)) {
						if(is_empty(idNota, 1)) {
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

					// Volta pra mesma página
					if(!is_empty(ret['bol'], 1) && is_empty(idNota, 1)
						&& !is_empty($("div.data_views").data("is_nota_vinculada"), 1)
					) {
						$.redirect(urlThis + ret['notaFiscal']['idDFisDevolucaoNotasFiscaisEntrada'], {...tokenCsrf});
					} else {
						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);

						toggleLoading();
					}
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
		text = null;
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

function somaCamposLotes() {
	let casasQtd = $("div.data_views").data('casas_qtd');
	const thousand_delimiter_qtd = $("div.data_views").data('thousand_delimiter_qtd');
	const separador_decimal_qtd = $("div.data_views").data('decimal_delimiter_qtd');
	const linhas   = $("table#conteudo-lotes-tabela tbody tr:not(.ocultar)");

	if(is_empty(casasQtd, 1)) casasQtd = '0';
	casasQtd = parseInt(casasQtd.toString());

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
			valorTotaisLinhas += stringParaFloat(
				$(linha).find('.div-conteudo-lote_quantidade').text(), separador_decimal_qtd, true
			);
		} else {
			valorTotaisLinhas += stringParaFloat(
				$(linha).find('.conteudo-lote_quantidade').val(), separador_decimal_qtd, true
			);
		}
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasQtd).toString(),
			'.',
			true
		);

	$('#conteudo-lotes_total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			separador_decimal_qtd,
			thousand_delimiter_qtd,
			"",
			true,
			casasQtd
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
	let totalDespesasErro = $("table#conteudo-despesas_erro-tabela tfoot tr #conteudo-despesas_erro_total_geral");
	let casasPreco        = $("div.data_views").data('casas_preco');

	if(is_empty(casasPreco, 1)) casasPreco = '0';
	casasPreco = parseInt(casasPreco.toString());

	const cifrao_moeda = $("div.data_views").data('prefixo_moeda');
	const separador_decimal_moeda = $("div.data_views").data('decimal_delimiter_moeda');
	const separador_milhar_moeda = $("div.data_views").data('thousand_delimiter_moeda');

	let valorTotaisLinhas = 0;
	valorTotaisLinhas += stringParaFloat($(totalItens).text(), separador_decimal_moeda, true);
	valorTotaisLinhas += stringParaFloat($(totalDespesas).text(), separador_decimal_moeda, true);
	valorTotaisLinhas += stringParaFloat($(totalDespesasErro).text(), separador_decimal_moeda, true);
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
		ratearDespesaAdicionalOrigemNFEnosItens();
	});

	$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposParcelas();
	});

	$("table#conteudo-despesas-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposDespesas();
		somaCamposGeral();
	});

	$("table#conteudo-lotes-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposLotes();
	});
}

function atualizaLinhasParcelas() {
	let index = 0;
	$("table#conteudo-parcelas-tabela tbody tr .linha").each(function () {
		$(this).text(index);
		index++;
	});
}

function atualizaLinhasLotes() {
	let index = -1;
	$("table#conteudo-lotes-tabela tbody tr .conteudo-lotes_id_interno").each(function () {
		$(this).val(index);
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

		if($("table#conteudo-itens-tabela tbody tr:not(.ocultar)").length <= 0) {
			initByElementReplicancias[$(obj).attr('id')]['value'] = $(obj).val();
			initByElementReplicancias[$(obj).attr('id')]['text'] = $(obj).find("option:selected").text();
			if(is_empty($(obj).val(), 1)) {
				initByElementReplicancias[$(obj).attr('id')]['value'] = "";
				initByElementReplicancias[$(obj).attr('id')]['text'] = "";
			}

			$(initByElementReplicancias[$(obj).attr('id')]['replicar_para']).each(function () {
				controlaReplicancias_addValores($(obj).attr('id'), $(this));
			});
		} else {
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

function controlaLote() {
	let trAddLote = null;

	$('button.controlar_lote').off('click');
	$('button.controlar_lote').on("click", function () {
		trAddLote = $($(this).parents("tr"));

		let lotes = JSON.parse($($(trAddLote).find(".conteudo-itens_lotes_json")).val());
		$($($('#conteudo-lotes-tabela tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");

		$.each(lotes, function (idx, lote) {
			$('#conteudo-lotes-tabela tfoot .add-itens-table-geral').trigger("click");

			$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lotes_id_interno")).val(lote.linhaLoteItemNFePortal);
			if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_nome")).text(lote.nomeLoteItemNFe);
				$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_quantidade")).text(lote.quantidadeFormatadaLoteItemNFe);
				if(!is_empty(lote.dataFabricacaoLoteItemNFe, 1)) {
					$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_data_fabricacao")).text(moment(lote.dataFabricacaoLoteItemNFe, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
				}
				if(!is_empty(lote.dataVencimentoLoteItemNFe, 1)) {
					$($('#conteudo-lotes-tabela tbody tr:last').find(".div-conteudo-lote_data_vencimento")).text(moment(lote.dataVencimentoLoteItemNFe, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
				}
			} else {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_nome")).val(lote.nomeLoteItemNFe);
				$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_quantidade")).val(lote.quantidadeFormatadaLoteItemNFe);
				if(!is_empty(lote.dataFabricacaoLoteItemNFe, 1)) {
					$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_data_fabricacao")).val(moment(lote.dataFabricacaoLoteItemNFe, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
				}
				if(!is_empty(lote.dataVencimentoLoteItemNFe, 1)) {
					$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_data_vencimento")).val(moment(lote.dataVencimentoLoteItemNFe, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
				}
			}
		});
		triggerSomaCampos();
		somaCamposLotes();
		lotes = null;

		$('.modal-lote').modal('toggle');
	});

	$('.modal-lote button.btn-salvar-lote_itens').off('click');
	$('.modal-lote button.btn-salvar-lote_itens').on("click", function () {
		let lotes = [];
		let casasQtd = $("div.data_views").data('casas_qtd');
		const separador_decimal_qtd = $("div.data_views").data('decimal_delimiter_qtd');

		if(is_empty(casasQtd, 1)) casasQtd = '0';
		casasQtd = parseInt(casasQtd.toString());

		$($("#conteudo-lotes-tabela tbody tr").not(':first')).each(function () {
			let obj = $(this);
			let push = {
				"linhaLoteItemNFePortal": $(obj).find(".conteudo-lotes_id_interno").val(),
				"nomeLoteItemNFe": $(obj).find(".conteudo-lote_nome").val(),
				"quantidadeLoteItemNFe": formataDecimal(
					$(obj).find(".conteudo-lote_quantidade").val(),
					separador_decimal_qtd,
					".",
					"",
					"",
					true,
					casasQtd
				),
				"quantidadeFormatadaLoteItemNFe": $(obj).find(".conteudo-lote_quantidade").val(),
				"dataFabricacaoLoteItemNFe": $(obj).find(".conteudo-lote_data_fabricacao").val(),
				"dataVencimentoLoteItemNFe": $(obj).find(".conteudo-lote_data_vencimento").val(),
			};
			if(!is_empty(push['dataFabricacaoLoteItemNFe'], 1)) {
				push['dataFabricacaoLoteItemNFe'] = moment(push['dataFabricacaoLoteItemNFe'], $('div.data_views').data('format_date')).format('YYYY-MM-DD');
			} else {
				push['dataFabricacaoLoteItemNFe'] = null;
			}
			if(!is_empty(push['dataVencimentoLoteItemNFe'], 1)) {
				push['dataVencimentoLoteItemNFe'] = moment(push['dataVencimentoLoteItemNFe'], $('div.data_views').data('format_date')).format('YYYY-MM-DD');
			} else {
				push['dataVencimentoLoteItemNFe'] = null;
			}

			lotes.push(push);
		});

		if(trAddLote !== null && trAddLote.length === 1) {
			$($(trAddLote).find(".conteudo-itens_lotes_json")).val(JSON.stringify(lotes));
		}

		trAddLote = null;
		$('.modal-lote').modal('toggle');
	});
}

function controlaSelectSequencia() {

	$("select#imposto-sequencia").off("select2:select");
	$("select#imposto-sequencia").on("select2:select", function () {
		// desabilita os outros INPUTS E SELECTS
		$("select#imposto-modelo").empty();
		$("input#imposto-numero_nota").val('');
		$("input#imposto-serie_nota").val('');
		$("select#imposto-modelo").attr("disabled",true);
		$("input#imposto-numero_nota").attr("disabled",true);
		$("input#imposto-serie_nota").attr("disabled",true);
	});

	$("select#imposto-sequencia").off("select2:unselect");
	$("select#imposto-sequencia").on("select2:unselect", function () {
		// habilita os outros INPUTS E SELECTS
		$("select#imposto-modelo").attr("disabled",false);
		$("input#imposto-numero_nota").attr("disabled",false);
		$("input#imposto-serie_nota").attr("disabled",false);
	});
}

initFields();
initButtons();
controlaSelectFornecedor();
controlaSelectMoeda();
controlaSelectProduto();
controlaSelectSequencia();
salvarDados();
controlaLote();
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
		controlaLote();
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

controlaTabelaSuite({
	"ref": "#conteudo-lotes-tabela",
	"funAposAddItem": function () {
		atualizaLinhasLotes();
		triggerSomaCampos();
	},
	"funAposRemoverItem": function () {
		atualizaLinhasLotes();
		triggerSomaCampos();
	}
});

/**
 * Função ratearDespesaAdicionalOrigemNFEnosItens
 * Rateia o valor da despesa adicional entre os itens que serão devolvidos de acordo seu valor em relação ao valor total original da NFE
 * Funcionamento: apenas soma os valores de cada linha que refere a uma despesa e inclui no valor da despesa (table dfisdespesasadicionaisporitemnfe)
 */
function ratearDespesaAdicionalOrigemNFEnosItens(){
	if( !is_empty($("div.data_views").data("ratear_despesa_adicional_origem_nfe_nos_itens"), 1) ){
		//pego dados da empresa sobre Moeda
		let casasPreco = $("div.data_views").data('casas_preco');
		let sepDecimalMoeda = $("div.data_views").data('decimal_delimiter_moeda');
		let separadorMilharMoeda = $("div.data_views").data('thousand_delimiter_moeda');
		let cifraoMoeda = $("div.data_views").data('prefixo_moeda');

		const objItens = $("#conteudo-itens-tabela tbody tr").not(":first");
		const objDespesas = $("#conteudo-despesas-tabela tbody tr").not(":first");

		//se tem alguma despesa na tela
		if (objDespesas.length > 0) {
			let totalDepesas= [];
			$.each(objItens, function (index) {
				let despesasItem= JSON.parse($(this).find('input.conteudo-itens_rateio_despesas').val());
				for(let i=0;i< despesasItem.length ;i++){
					let idDespesa= despesasItem[i].idDespesasAdicionais;
					if( typeof totalDepesas[idDespesa] =='undefined' ) totalDepesas[idDespesa] = 0;
					totalDepesas[idDespesa] += parseFloat(despesasItem[i].lineTotal);
				}
			});

			$.each(objDespesas, function (index) {
				//se for uma despesa originada da NFE
				if (!is_empty($(this).find('td input.conteudo-despesas_valor_despesa').data('is_despesa_origem_nfe'), 1)) {
					let novoValor= 0;
					let idDespesa= $(this).find('td select.conteudo-despesas_despesa').val();
					if( !is_empty(idDespesa,0) ){
						if( typeof totalDepesas[idDespesa] != 'undefined' ){
							novoValor= totalDepesas[idDespesa];
						}
					}
					$(this).find('td input.conteudo-despesas_valor_despesa').val(
						formataDecimal(
							novoValor,
							'.',
							sepDecimalMoeda,
							separadorMilharMoeda,
							cifraoMoeda,
							true,
							casasPreco
						)
					)
				}
			});

			//chamo funções para recalcular os totais exibidos no corpo do documento
			somaCamposItens();
			somaCamposLotes();
			somaCamposDespesas();
			somaCamposGeral();
		}
	}
}

controlaReplicancias();
ratearDespesaAdicionalOrigemNFEnosItens();

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