const flagSubItemPortalFornecedor = $('div.data_views').data("flag_sub_item_portal_fornecedor");
let inputUtilizacao = $('select.select_utilizacao_de-para_itens');
const flagsNaoObrigatorio = {
	naoObrigarDadosBancarios: 0,
	naoObrigarParcelas: 0
};

const dataViews = $("div.data_views");
const cifrao_moeda = dataViews.data('prefixo_moeda');
const separador_decimal_moeda = dataViews.data('decimal_delimiter_moeda');
const separador_milhar_moeda = dataViews.data('thousand_delimiter_moeda');
const separador_decimal_qtd = dataViews.data('decimal_delimiter_qtd');

let casasPreco = dataViews.data('casas_preco');
let casasValor = dataViews.data('casas_valor');
if(is_empty(casasPreco, 1)) casasPreco = '0';
casasPreco = parseInt(casasPreco.toString());

if(is_empty(casasValor, 1)) casasValor = '2';
casasValor = parseInt(casasValor.toString());

function createFieldAnexos() {
	recriar($("div#documentos_anexo"));
	$('div#documentos_anexo').allUpload(
		'conteudo-anexos_name[]',
		'conteudo-anexos_blob[]',
		function (obj) {
			if(is_empty(dataViews.data("vizualizacao"), 1)) {
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

				if(is_empty(dataViews.data("vizualizacao"), 1)) {
					$($(obj).find(".action-visualize")).remove();
				}
			}
			srcCheck = null;

			if (!is_empty(dataViews.data("vizualizacao"), 1)) {
				const url = dataViews.data("url_baixar_anexos");
				const urlArquivoSefaz = dataViews.data("url_baixar_anexos_sefaz");
				let id = $(obj).data('id');
				let idnf = $(obj).data('idnf');
				let tipo = $(obj).data('extensao');

				if (id) {
					$($(obj).find(".action-visualize")).attr("href", (url + id));
				} else {
					if (tipo == '.pdf'){
						$($(obj).find(".action-visualize")).attr("href", (urlArquivoSefaz + idnf + '?option=pdf'));
					}else{
						$($(obj).find(".action-visualize")).attr("href", (urlArquivoSefaz + idnf + '?option=xml'));
					}
				}

				$($(obj).find(".action-visualize")).attr("target", "_blank");
				$($(obj).find(".action-visualize")).html($(".data_views").data("text_download_upload"));
			}
		}
	);
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
		$('div#documentos_anexo .link-adiciona-files').remove();
	}
}

function initFields() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');
	$("select.select_simples").select2Simple();

	createFieldAnexos();
	$('.modal-visualiza_anexo').off('hidden.bs.modal');
	$('.modal-visualiza_anexo').on('hidden.bs.modal', function (e) {
		$($(this).find(".modal-content .modal-title")).text("");
		$($(this).find(".modal-content .modal-body")).text("");
	});

	inputUtilizacao.data('init', '');
	inputUtilizacao.select2Ajax();

	if( typeof $('select#tipoCadastroDePara') == 'object') {
		$('select#tipoCadastroDePara').select2Simple(null,null,{
			allowClear: false,
		});

		$("select#tipoCadastroDePara").on("change",function(){
			const isDeParaAtivoFixo= ($('select#tipoCadastroDePara').val() == 'ativoFixo') ? true : false;
			const url = dataViews.data("url_ajax_produtos");
			if(isDeParaAtivoFixo){
				$("#produto_adicionar_de-para_itens").data("url", (url + 'F') );
				$(".div-unidade_medida_fornecedor").addClass("ocultar");
				$(".div-multiplicador_qtd_sefaz").addClass("ocultar");
				$(".div-replicar-de-para").addClass("ocultar");
			}else{
				$("#produto_adicionar_de-para_itens").data("url",url);
				$(".div-unidade_medida_fornecedor").removeClass("ocultar");
				$(".div-multiplicador_qtd_sefaz").removeClass("ocultar");
				$(".div-replicar-de-para").removeClass("ocultar");
			}
			$("#produto_adicionar_de-para_itens").select2Ajax();
			$("#produto_adicionar_de-para_itens").val("").trigger('change').trigger('select2:unselect');
		});
	}

	$('button.btn-add-obs2').off('click').on('click', function(){
		let icon = isOldLayout === 0 ? $(this).find('svg') : $(this);
		let isPlus = icon.hasClass('fa-plus');

		$('div.div-observacao2').toggleClass('ocultar', !isPlus);
		icon.toggleClass('fa-plus fa-minus');
		$(this).attr('title', isPlus ? $(this).data('title-remove') : $(this).data('title-add'));

		if (!isPlus) {
			$('textarea#geral-observacoes2').val('');
		}
	});

}

function initButtons() {
	let __validaRelacionamento = function(obj) {
		if(is_empty(obj, 1)) {
			return null;
		}

		const url = $(obj).data("url");
		const idNota = $("input#geral-id_nota_fiscal").val();
		const idItem = $($(obj).parents("tr").find("input.conteudo-itens_id_interno")).val();
		if(is_empty(idNota, 1) || is_empty(idItem, 1) || is_empty(url, 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				l["erro"]
			).catch(swal.noop);
			return null;
		}

		return {
			url: url,
			idNota: idNota,
			idItem: idItem,
		};
	};
	let __verificarDoc = function (url, idItem) {
		if(is_empty(dataViews.data("vizualizacao"))) { return; }

		const idNota = $("input#geral-id_nota_fiscal").val();
		if(is_empty(idNota, 1) || is_empty(url, 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				l["erro"]
			).catch(swal.noop);
			return;
		}

		if(is_empty(idItem)) { idItem = null; }

		// pega o ID da filial pois o documento pode nao ser da filial logada
		const idFilial = dataViews.data("id_filial");

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
			ajaxRequest(
				true,
				url,
				null,
				'text',
				{ idNota, idItem, idFilial },
				function (ret) {
					try{
						ret = JSON.parse(ret);
						if(!is_empty(ret['bol'], 1)) {
							location.reload();
						} else {
							swal(
								ret['titulo'],
								ret['text'],
								ret['class']
							).catch(swal.noop);
							toggleLoading();
						}
					}catch(err){
						swal(
							l["erro!"],
							l["tempoDeRespostaDoServidorEsgotado!"],
							"error"
						).catch(swal.noop);

						forceToggleLoading(0);
					}
				}
			);
		}, function () {
			//SE DER ERRO
		}).catch(swal.noop);
	};
	let __validacaoCadastrarDePara = function () {
		if(is_empty(dataViews.data("vizualizacao"))) {
			return false;
		}
		if(is_empty($("#geral-id_fornecedores_real").val())) {
			return false;
		}

		const idNota = $("input#geral-id_nota_fiscal").val();
		const url = dataViews.data("url_cadastrar_de_para_produtos");
		const urlAtivoFixo = dataViews.data("url_cadastrar_de_para_ativos_fixos");

		if(is_empty(idNota, 1) || is_empty(url, 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				l["erro"]
			).catch(swal.noop);
			return false;
		}

		return {
			"idNota": idNota,
			"url": url,
			"urlAtivoFixo": urlAtivoFixo,
		}
	};

	$("button.check_fornecedor").off("click");
	$("button.check_fornecedor").on("click", function () {
		__verificarDoc(dataViews.data("url_verificar_fornecedor"));
	});

	$("button.verificar_despesas").off("click");
	$("button.verificar_despesas").on("click", function () {
		__verificarDoc(dataViews.data("url_verificar_despesas"));
	});

	$("button.verificar_de-para_item").off("click");
	$("button.verificar_de-para_item").on("click", function () {
		__verificarDoc(
			dataViews.data("url_verificar_de_para_produtos"),
			$($(this).parents("tr").find("input.conteudo-itens_id_interno")).val()
		);
	});

	$("button.verificar_documento").off("click");
	$("button.verificar_documento").on("click", function () {
		__verificarDoc(dataViews.data("url_verificar_doc"));
	});

	$('button.toggle_replicacao').off('click');
	$('button.toggle_replicacao').on('click', function () {
		if($(".grupo_replicacao").hasClass("ocultar")) {
			$(".grupo_replicacao").removeClass("ocultar");
		} else {
			$(".grupo_replicacao").addClass("ocultar");
		}
	});

	$("button.adicionar_de-para_item").off("click");
	$("button.adicionar_de-para_item").on("click", function () {
		let modal = $(".modal-adicionar_de-para_itens");
		const constantes = __validacaoCadastrarDePara();

		if(is_empty(constantes, 1)) { return; }
		if(is_empty($($(this).parents('tr').find(".conteudo-itens_has_depara_error")).val())) { return false; }

		const idOrigem = $($(this).parents("tr").find(".conteudo-itens_id_item")).val();
		const unidadeFornecedor = $($(this).parents("tr").find(".conteudo-itens_unidade_fornecedor_item")).val();
		const nomeOrigem =
			$($(this).parents("tr").find(".conteudo-itens_nome_item")).text().trim() +
			" - " +
			$($(this).parents("tr").find(".conteudo-itens_nome_unidade")).text().trim();
		if(is_empty(idOrigem, 0)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				l["erro"]
			).catch(swal.noop);
			return false;
		}

		$($(modal).find("#id_origem_adicionar_de-para_itens")).data('id', idOrigem);
		$($(modal).find("#id_origem_adicionar_de-para_itens")).text(nomeOrigem);
		$($(modal).find("#id_unidade_fornecedor_adicionar_de-para_itens")).data('id', unidadeFornecedor);
		$($(modal).find("#id_unidade_fornecedor_adicionar_de-para_itens")).text(unidadeFornecedor);
		$($(modal).find("#produto_adicionar_de-para_itens")).val("").trigger('change').trigger('select2:unselect');
		$($(modal).find("#unidade_adicionar_de-para_itens")).val("").trigger('change').trigger('select2:unselect');
		$($(modal).find("#quantidade_adicionar_de-para_itens")).val("");

		$("select#tipoCadastroDePara").val("produto").trigger('change');
		$(modal).modal('toggle');
	});

	$("button.atualizar_manual-relacao_item_pedido").off("click");
	$("button.atualizar_manual-relacao_item_pedido").on("click", function () {
		const obj = $(this);
		const dados = __validaRelacionamento($(obj));
		if(is_empty(dados, 1)) return;
		if(is_empty(dataViews.data("vizualizacao"))) {
			return;
		}

		toggleLoading();
		ajaxRequest(true, dados.url, null, 'text', {'idNota': dados.idNota, 'idItemNota': dados.idItem}, function (ret) {
			try{
				consoleProduction(ret);
				ret = JSON.parse(ret);
				if(is_empty(ret['bol'], 1)) {
					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
					toggleLoading();
					return;
				}

				$(".modal-atualizar_relacao_pedidos .modal-body").data("id_nota", dados.idNota);
				$(".modal-atualizar_relacao_pedidos .modal-body").data("id_item_nota", dados.idItem);
				$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-item").html(encodeHTMLEntities($($(obj).parents("tr").find(".conteudo-itens_nome_item")).text().trim()));
				$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-unidade").html(encodeHTMLEntities($($(obj).parents("tr").find(".conteudo-itens_nome_unidade")).text().trim()));
				$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-quantidade").html(encodeHTMLEntities($($(obj).parents("tr").find("input.conteudo-itens_quantidade")).val()));
				$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-preco").html(encodeHTMLEntities($($(obj).parents("tr").find("input.conteudo-itens_preco_unitario")).val()));
				$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-total_linha").html(encodeHTMLEntities($($(obj).parents("tr").find(".conteudo-itens_total_linha")).text().trim()));

				let table = $(".modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela");
				let tbody = $($(table).find("tbody"));
				let divPedidosInexistentes = $("#msg-pedidos-inexistentes");

				$($(tbody).find("tr:not(.ocultar)")).remove();
				if(!is_empty(ret['itens'], true)) {
					$(divPedidosInexistentes).addClass("ocultar");
					$.each(ret['itens'], function (idPedido, valoresPedido) {
						$.each(valoresPedido['itens'], function (indexItemPedido, valoresItemPedido) {
							let modelo = $(tbody).find("tr").first().html();
							let refTr = null;

							$(tbody).append('<tr>' + modelo + '</tr>');
							refTr = $($(tbody).find('tr').last());

							$(refTr).find(".atualizar_relacao_pedidos-table-id_pedido_interno").html(valoresPedido['idPedidosCompra']);
							$(refTr).find(".atualizar_relacao_pedidos-table-id_pedido").html(encodeHTMLEntities(valoresPedido['idPedidosCompra']));
							$(refTr).find(".atualizar_relacao_pedidos-table-id_sap_pedido").html(encodeHTMLEntities(valoresPedido['idPedidoERP']));
							$(refTr).find(".atualizar_relacao_pedidos-table-doc_num_pedido").html(encodeHTMLEntities(valoresPedido['docNum']));
							$(refTr).find(".atualizar_relacao_pedidos-table-dt_documento_pedido").html(encodeHTMLEntities(valoresPedido['dataDocumentoText']));
							$(refTr).find(".atualizar_relacao_pedidos-table-dt_entrega_pedido").html(encodeHTMLEntities(valoresPedido['dataEntregaText']));
							$(refTr).find(".atualizar_relacao_pedidos-table-condicao_pagamento_pedido").html(encodeHTMLEntities(valoresPedido['nomeCondicoesPagamento']));
							$(refTr).find(".atualizar_relacao_pedidos-table-total_geral").html(encodeHTMLEntities(valoresPedido['valorTotalItensText']));

							$(refTr).find(".atualizar_relacao_pedidos-table-id_item_pedido_interno").html(valoresItemPedido['idItensPedidosCompra']);
							$(refTr).find(".atualizar_relacao_pedidos-table-produto_item").html(encodeHTMLEntities(valoresItemPedido['produtoText']));
							$(refTr).find(".atualizar_relacao_pedidos-table-quantidade_item").html(encodeHTMLEntities(valoresItemPedido['quantidadeText']));
							$(refTr).find(".atualizar_relacao_pedidos-table-quantidade_restante_item").html(encodeHTMLEntities(valoresItemPedido['quantidadeRestanteText']));
							$(refTr).find(".atualizar_relacao_pedidos-table-status_entrega").html(encodeHTMLEntities(valoresItemPedido['statusIntegracaoPedido']));
							$(refTr).find(".atualizar_relacao_pedidos-table-unidade_item").html(encodeHTMLEntities(valoresItemPedido['nomeUnidadesMedidas']));
							$(refTr).find(".atualizar_relacao_pedidos-table-preco_unitario_item").html(encodeHTMLEntities(valoresItemPedido['precoUnitarioText']));
						});
					});
				} else {
					$(divPedidosInexistentes).removeClass("ocultar");
				}

				$('.modal-atualizar_relacao_pedidos').modal('show');
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
	});

	$("button.atualizar_relacao_pedidos-salvar").off("click");
	$("button.atualizar_relacao_pedidos-salvar").on("click", function () {
		let modal = $(".modal-atualizar_relacao_pedidos");
		let lineSelected = $(modal).find("table#atualizar_relacao_pedidos-itens_pedidos_tabela tbody tr.selected");

		const idNota = $(modal).find(".modal-body").data("id_nota");
		const idItemNota = $(modal).find(".modal-body").data("id_item_nota");
		const idPedido = $(lineSelected).find(".atualizar_relacao_pedidos-table-id_pedido_interno").text();
		const idItemPedido = $(lineSelected).find(".atualizar_relacao_pedidos-table-id_item_pedido_interno").text();
		const url = $(this).data("url");

		if(
			is_empty(idNota, 1) ||
			is_empty(idItemNota, 1) ||
			is_empty(url, 1)
		) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				l["erro"]
			).catch(swal.noop);
			return null;
		}

		toggleLoading();
		ajaxRequest(true, url, null, 'text', {'idNota': idNota, 'idItemNota': idItemNota, 'idPedido': idPedido, 'idItemPedido': idItemPedido}, function (ret) {
			try{
				consoleProduction(ret);
				ret = JSON.parse(ret);
				if(!is_empty(ret['bol'], 1)) {
					if(is_empty(idPedido, 1) && is_empty(idItemPedido, 1)) {
						$(
							$(
								$("table#conteudo-itens-tabela tbody tr:not(.ocultar) .conteudo-itens_id_interno").filter(
									function(){
										return this.value.toString() === idItemNota.toString();
									}
								)
							).parents("tr").find(".visualizar_relacao-relacao_item_pedido")
						).addClass("ocultar");
					} else {
						$(
							$(
								$("table#conteudo-itens-tabela tbody tr:not(.ocultar) .conteudo-itens_id_interno").filter(
									function(){
										return this.value.toString() === idItemNota.toString();
									}
								)
							).parents("tr").find(".visualizar_relacao-relacao_item_pedido")
						).removeClass("ocultar");
					}
					$('.modal-atualizar_relacao_pedidos').modal('hide');
				}

				swal(
					ret['titulo'],
					ret['text'],
					ret['class']
				).catch(swal.noop);
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
	});

	$("button.visualizar_relacao-relacao_item_pedido").off("click");
	$("button.visualizar_relacao-relacao_item_pedido").on("click", function () {
		const obj = $(this);
		const dados = __validaRelacionamento($(obj));
		if(is_empty(dados, 1)) return;

		toggleLoading();
		ajaxRequest(true, dados.url, null, 'text', {'idNota': dados.idNota, 'idItemNota': dados.idItem}, function (ret) {
			try{
				consoleProduction(ret);
				ret = JSON.parse(ret);
				if(is_empty(ret['bol'], 1)) {
					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
					toggleLoading();
					return;
				}

				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-item").html(encodeHTMLEntities($($(obj).parents("tr").find(".conteudo-itens_nome_item")).text().trim()));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-unidade").html(encodeHTMLEntities($($(obj).parents("tr").find(".conteudo-itens_nome_unidade")).text().trim()));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-quantidade").html(encodeHTMLEntities($($(obj).parents("tr").find("input.conteudo-itens_quantidade")).val()));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-preco").html(encodeHTMLEntities($($(obj).parents("tr").find("input.conteudo-itens_preco_unitario")).val()));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-total_linha").html(encodeHTMLEntities($($(obj).parents("tr").find(".conteudo-itens_total_linha")).text().trim()));

				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_botoes_principal .visualizar_pedido").attr(
					"href",
					(
						$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_botoes_principal .visualizar_pedido").data("url") +
						ret['dados']['idPedidosCompra']
					)
				);
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_id").html(encodeHTMLEntities(ret['dados']['idPedidosCompra']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_id_erp").html(encodeHTMLEntities(ret['dados']['idPedidoERP']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_doc_num").html(encodeHTMLEntities(ret['dados']['docNum']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_data_criacao").html(encodeHTMLEntities(ret['dados']['dataDocumentoText']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_data_entrega").html(encodeHTMLEntities(ret['dados']['dataEntregaText']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_condicao_pagamento").html(encodeHTMLEntities(ret['dados']['nomeCondicoesPagamento']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_total_geral").html(encodeHTMLEntities(ret['dados']['valorTotalItensText']));

				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_produto").html(encodeHTMLEntities(ret['dados']['produtoText']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_unidade").html(encodeHTMLEntities(ret['dados']['nomeUnidadesMedidas']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_quantidade").html(encodeHTMLEntities(ret['dados']['quantidadeText']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_quantidade_restante").html(encodeHTMLEntities(ret['dados']['quantidadeRestanteText']));
				$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_preco_unitario").html(encodeHTMLEntities(ret['dados']['precoUnitarioText']));

				$('.modal-visualizar_relacao_pedidos').modal('show');
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


	});

	$("button.salvar_de-para_itens").off("click");
	$("button.salvar_de-para_itens").on("click", function () {
		let modal = $(".modal-adicionar_de-para_itens");
		const constantes = __validacaoCadastrarDePara();
		if(is_empty(constantes, 1)) {
			return;
		}

		const utilizarUnidadeFornecedor = $($(modal).find("#is_utilizar_unidade_fornecedor_de-para_produtos")).is(":checked");
		const idOrigem = $($(modal).find("#id_origem_adicionar_de-para_itens")).data('id');
		const unidadeFornecedor = (utilizarUnidadeFornecedor ? $($(modal).find("#id_unidade_fornecedor_adicionar_de-para_itens")).data('id') : null);
		const idProduto = $($(modal).find("#produto_adicionar_de-para_itens")).val();
		const idUnidade = $($(modal).find("#unidade_adicionar_de-para_itens")).val();
		const quantidade = $($(modal).find("#quantidade_adicionar_de-para_itens")).val();
		const idTipoUtilizacaoProduto = $($(modal).find("#id_utilizacao_de-para_itens")).val();

		const isDeParaAtivoFixo = ($($(modal).find("#tipoCadastroDePara")).val() == 'ativoFixo') ? true : false;
		const isReplicarParaFornecedoresMesmaBaseCNPJ = $($(modal).find("#de-para-produtos-replicar_para_fornecedor_mesma_base_cnpj")).is(":checked");
		const isReplicarParaEmpresasEmpresaGestora = $($(modal).find("#de-para-produtos-replicar_para_empresas_gestora")).is(":checked");

		// pega o ID da filial pois o documento pode nao ser da filial logada
		const idFilial = dataViews.data("id_filial");

		if (idUnidade == null || idProduto == null) {
			swal(
				l["erro!"],
				l["todosOsCamposDeRegistrosAdicionadosDevemSerPreenchidos!"],
				"error"
			).catch(swal.noop);
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
			ajaxRequest(
				true,
				isDeParaAtivoFixo ? constantes['urlAtivoFixo'] : constantes['url'],
				null,
				'text',
				{
					'idNota': constantes['idNota'],
					'idOrigem': idOrigem,
					'nomeUnidadeFornecedor': unidadeFornecedor,
					'idProduto': idProduto,
					'idUnidade': idUnidade,
					'quantidade': quantidade,
					'idTipoUtilizacaoProduto': idTipoUtilizacaoProduto,
					'isReplicarParaFornecedoresMesmaBaseCNPJ': isReplicarParaFornecedoresMesmaBaseCNPJ,
					'isReplicarParaEmpresasEmpresaGestora': isReplicarParaEmpresasEmpresaGestora,
					'idFilial': idFilial
				},
				function (ret) {
					try{
						consoleProduction(ret);
						ret = JSON.parse(ret);
						if(!is_empty(ret['bol'], 1)) {
							toggleLoading();
							swal(
								ret['titulo'],
								ret['text'],
								ret['class']
							).then(function () {
								location.reload();
							});
						} else {
							swal(
								ret['titulo'],
								ret['text'],
								ret['class']
							).catch(swal.noop);
							toggleLoading();
						}
					}catch(err){
						swal(
							l["erro!"],
							l["tempoDeRespostaDoServidorEsgotado!"],
							"error"
						).catch(swal.noop);
						forceToggleLoading(0);
					}
				}
			);
		}, function () {
			//SE DER ERRO
		}).catch(swal.noop);
	});
}

/**
 * Função controlaSelectFornecedor
 * Usada para controle da funcionalidade ao selecionar e desselecionar um fornecedor
 */
function controlaSelectFornecedor() {
	// se for uma página de visualização, retorna
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
		return;
	}
	// função auxiliar para carregar as formas de pagamento possíveis de um fornecedor
	let __funControlaFormasPagamento = function (obj) {
		let select = $("select#financeiro-forma_pagamento");
		let data_views = dataViews;
		if ($(select).hasClass("select2-hidden-accessible")){
			$(select).select2('destroy');
		}

		// remove as options previamente carregadas
		$($(select).find("option")).remove();

		// preenche com novas options, baseada na url e no id do fornecedor
		$(select).data(
			"url",
			($(data_views).data("url_ajax_formas_pagamento") +
				(is_empty($(obj).val(), 1) ? "" : $(obj).val()))
		);

		// monta o select2
		$(select).select2Ajax();
	};

	// função auxiliar para carregar os contatos possíveis de um fornecedor
	let __funControlaContato = function (obj) {
		let select = $("select#financeiro-contato");
		let data_views = dataViews;
		if ($(select).hasClass("select2-hidden-accessible")){
			$(select).select2('destroy');
		}

		// remove as options previamente carregadas
		$($(select).find("option")).remove();

		// preenche com novas options, baseada na url e no id do fornecedor
		$(select).data(
			"url",
			($(data_views).data("url_ajax_contato_fornecedor") +
				(is_empty($(obj).val(), 1) ? "" : $(obj).val()))
		);

		// monta o select2
		$(select).select2Ajax();
	};
	// função auxiliar para carregar os contatos possíveis de um fornecedor

	// função auxiliar para controle do retorno dos bancos ao selecionar fornecedor,
	// na aba 'Financeiro'
	let __funControlaBancoPagamento = function (obj, resetURL = false) {
		let select = $("select#financeiro-banco");
		let data_views = dataViews;
		let url = $(data_views).data("url_ajax_info_contas_bancarias");

		if ($(select).hasClass("select2-hidden-accessible")){
			$(select).select2('destroy');
		}

		$($(select).find("option")).remove();

		if (!resetURL) {
			url += (is_empty($(obj).val(), 1) ? "" : $(obj).val());
		}

		$(select).data('url', url);
		$(select).select2Ajax();
	};

	$("select#geral-id_fornecedores").off("select2:unselect");
	$("select#geral-id_fornecedores").on("select2:unselect", function () {
		// desseleciona o banco, afetando agencia e conta
		$("select#financeiro-banco").trigger('select2:unselect');

		__funControlaFormasPagamento($(this));
		__funControlaContato($(this));
		__funControlaBancoPagamento($(this), true);
	});

	$("select#geral-id_fornecedores").off("select2:select");
	$("select#geral-id_fornecedores").on("select2:select", function () {
		__funControlaFormasPagamento($(this));
		__funControlaContato($(this));
		__funControlaBancoPagamento($(this));
	});
}

/**
 * Função para controle dos campos 'Agência' e 'Conta' da aba 'Financeiro'
 * ao selecionar um banco, e posteriormente, a própria agência
 */
function controlaSelectInfoPagamento() {
	// função auxiliar para retorno de agências de acordo com o fornecedor e
	// banco selecionados
	let __funControlaAgenciasInfoPagamento = function (obj, resetURL = false) {
		let select = $("select#financeiro-agencia");
		let data_views = dataViews;
		let url = $(data_views).data("url_ajax_info_contas_bancarias");

		if ($(select).hasClass("select2-hidden-accessible")){
			$(select).select2('destroy');
		}

		let idFornecedores = $("input#geral-id_fornecedores_real").val() ?? $("select#geral-id_fornecedores").val();

		$($(select).find("option")).remove();
		if (!resetURL) {
			url += idFornecedores +
				(is_empty($(obj).val(), 1) ? "" : '/' + $(obj).val());
		}

		$(select).data('url', url);
		$(select).select2Ajax();
	};

	// função auxiliar para retorno de contas de acordo com o fornecedor,
	// banco e agência selecionados
	let __funControlaContasInfoPagamento = function (obj, resetURL = false) {
		let select = $("select#financeiro-conta");
		let data_views = dataViews;
		let url = $(data_views).data("url_ajax_info_contas_bancarias");

		if ($(select).hasClass("select2-hidden-accessible")){
			$(select).select2('destroy');
		}

		let idFornecedores = $("input#geral-id_fornecedores_real").val() ?? $("select#geral-id_fornecedores").val();

		$($(select).find("option")).remove();
		if (!resetURL) {
			url += idFornecedores +
				'/' + $("select#financeiro-banco").val() +
				'/' + (is_empty($(obj).val(), 1) ? "" : $(obj).val());
		}

		$(select).data("url", url);
		$(select).select2Ajax();
	};

	// desseleção do campo Banco
	$("select#financeiro-banco").off("select2:unselect");
	$("select#financeiro-banco").on("select2:unselect", function () {
		__funControlaAgenciasInfoPagamento($(this), true);
		__funControlaContasInfoPagamento($(this), true);
	});

	// seleção do campo Banco
	$("select#financeiro-banco").off("select2:select");
	$("select#financeiro-banco").on("select2:select", function () {
		__funControlaAgenciasInfoPagamento($(this));
		__funControlaContasInfoPagamento($(this));
	});

	// desseleção do campo Agência
	$("select#financeiro-agencia").off("select2:unselect");
	$("select#financeiro-agencia").on("select2:unselect", function () {
		__funControlaContasInfoPagamento($(this), true);
	});

	// seleção do campo Agência
	$("select#financeiro-agencia").off("select2:select");
	$("select#financeiro-agencia").on("select2:select", function () {
		__funControlaContasInfoPagamento($(this));
	});
}

function controlaSelectMoeda() {
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
		return;
	}

	let prefixoAnteriorMoeda = dataViews.data('prefixo_moeda');
	let sepMilharAnteriorMoeda = dataViews.data('thousand_delimiter_moeda');
	let sepDecimalAnteriorMoeda = dataViews.data('decimal_delimiter_moeda');

	let prefixoAgoraMoeda = prefixoAnteriorMoeda;
	let sepMilharAgoraMoeda = sepMilharAnteriorMoeda;
	let sepDecimalAgoraMoeda = sepDecimalAnteriorMoeda;

	let urlMoeda = dataViews.data('url_ajax_moedas');

	let __funObterValoresMoeda = function (obj, isUnselect) {
		let __funFormatarValores = function () {
			if(prefixoAgoraMoeda === prefixoAnteriorMoeda && sepMilharAgoraMoeda === sepMilharAnteriorMoeda && sepDecimalAgoraMoeda === sepDecimalAnteriorMoeda) {
				return;
			}

			recriar($("input.valores"));
			$("input.valores").each(function () {
				if($(this).data("mask") === "numerov2") {
					if (configLocation.codigo === "USA") {
						$(this).data('prefixo', configLocation.currencySymbol);
						$(this).data('thousand_delimiter', configLocation.currencyThousandsSeparator);
						$(this).data('decimal_delimiter', configLocation.currencyDecimal);
					} else {
						$(this).data('prefixo', prefixoAgoraMoeda);
						$(this).data('thousand_delimiter', sepMilharAgoraMoeda);
						$(this).data('decimal_delimiter', sepDecimalAgoraMoeda);
					}
				}
				if (configLocation.codigo === "USA") {
					$(this).val(formatFloatToCurrency(configLocation.codigo, $(this).val()));
				} else {
					$(this).val(formataDecimal($(this).val(), sepDecimalAnteriorMoeda, sepDecimalAgoraMoeda, sepMilharAgoraMoeda, prefixoAgoraMoeda, true, casasPreco));
				}
			});
			$("input.valores[data-mask='numerov2']").fnMascaraNumeroV2();

			if (configLocation.codigo === "USA") {
				$("div.conteudo-itens_total_linha").each(function () {
					$(this).text(formatFloatToCurrency(configLocation.codigo, $.trim($(this).text())));
				});
				$("#conteudo-itens_total_geral").text(formatFloatToCurrency(configLocation.codigo, $.trim($("#conteudo-itens_total_geral").text())));
			} else {
				$("div.conteudo-itens_total_linha").each(function () {
					$(this).text(formataDecimal($.trim($(this).text()), sepDecimalAnteriorMoeda, sepDecimalAgoraMoeda, sepMilharAgoraMoeda, prefixoAgoraMoeda, true, casasValor));
				});
				$("#conteudo-itens_total_geral").text(formataDecimal($.trim($("#conteudo-itens_total_geral").text()), sepDecimalAnteriorMoeda, sepDecimalAgoraMoeda, sepMilharAgoraMoeda, prefixoAgoraMoeda, true, casasValor));
			}

			prefixoAnteriorMoeda = prefixoAgoraMoeda;
			sepMilharAnteriorMoeda = sepMilharAgoraMoeda;
			sepDecimalAnteriorMoeda = sepDecimalAgoraMoeda;

			dataViews.data('prefixo_moeda', prefixoAgoraMoeda);
			dataViews.data('thousand_delimiter_moeda', sepMilharAgoraMoeda);
			dataViews.data('decimal_delimiter_moeda', sepDecimalAgoraMoeda);
		};

		toggleLoading();
		if(!is_empty(isUnselect, true)) {
			prefixoAgoraMoeda = dataViews.data('prefixo_padrao_moeda');
			sepMilharAgoraMoeda = dataViews.data('thousand_delimiter_padrao_moeda');
			sepDecimalAgoraMoeda = dataViews.data('decimal_delimiter_padrao_moeda');

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
					prefixoAgoraMoeda = dataViews.data('prefixo_padrao_moeda');
					sepMilharAgoraMoeda = dataViews.data('thousand_delimiter_padrao_moeda');
					sepDecimalAgoraMoeda = dataViews.data('decimal_delimiter_padrao_moeda');
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
	let selectItensIdSubitemConteudo = $("select.conteudo-itens_id_subitem");

	/** FUNÇÃO DE CONTROLE DE SUBITEM SÓ IRA FUNCIONAR ATIVANDO A FLAG */
	let __funControlaSubItemsMe = function (objSubItem, objVal){
		if(is_empty(objSubItem, 1) || $(objSubItem).length === 0) {
			return;
		}

		let data_views = dataViews;
		if ($(objSubItem).hasClass("select2-hidden-accessible")){
			$(objSubItem).select2('destroy');
		}

		$($(objSubItem).find("option")).remove();
		$(objSubItem).data(
			"url",
			($(data_views).data("url_ajax_subitems") + (is_empty(objVal, 1) ? "" : objVal))
		);

		$(objSubItem).select2Ajax();
	}
	/** FIM FUNÇÃO DE CONTROLE DE SUBITEM SÓ IRA FUNCIONAR ATIVANDO A FLAG */

	let __funControlaUnidadesMedidas = function (objUnidade, objVal, ObjAux) {
		if(is_empty(objUnidade, 1) || $(objUnidade).length === 0) {
			return;
		}

		let data_views = dataViews;
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
		let isControlarLotes = dataViews.data("is_controlar_lote");

		$(objTextareaLotes).val("[]");
		if(is_empty(objVal, 1) || is_empty(isControlarLotes, 1)) {
			$(objBtnLote).addClass("ocultar");
			return;
		}

		let url = dataViews.data("url_verifica_produto_adm_lote");
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
		if (flagSubItemPortalFornecedor){
			__funControlaSubItemsMe($($(this).parents("tr").find("select.conteudo-itens_id_subitem")), null);
		}
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), null, null);
		__funControlaLote($($(this).parents("tr").find(".controlar_lote")), $($(this).parents("tr").find(".conteudo-itens_lotes_json")), null);
	});

	selectItensIdItemConteudo.off("select2:select");
	selectItensIdItemConteudo.on("select2:select", function () {
		if (flagSubItemPortalFornecedor){
			__funControlaSubItemsMe($($(this).parents("tr").find("select.conteudo-itens_id_subitem")), $(this).val());
		}
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), $(this).val(), null);
		__funControlaLote($($(this).parents("tr").find(".controlar_lote")), $($(this).parents("tr").find(".conteudo-itens_lotes_json")), $(this).val());
	});
	/** FIM ITEM DO CONTEUDO */

	$("select#produto_adicionar_de-para_itens").off("select2:unselect");
	$("select#produto_adicionar_de-para_itens").on("select2:unselect", function () {
		__funControlaUnidadesMedidas($("select#unidade_adicionar_de-para_itens"), null);
	});

	$("select#produto_adicionar_de-para_itens").off("select2:select");
	$("select#produto_adicionar_de-para_itens").on("select2:select", function () {
		__funControlaUnidadesMedidas($("select#unidade_adicionar_de-para_itens"), $(this).val());
	});
}

function salvarDados() {
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
		return;
	}

	$("button.salvar").off("click");
	$("button.salvar").on("click", function (e) {
		let urlThis = dataViews.data("url_add");
		let url = $($(this).parents('form#form-to-serialize')).attr('action');
		let idNota = null;
		let save = null;
		let text = "";
		if(is_empty(url, 1)) {
			return;
		}

		if(
			is_empty(dataViews.data("trava_campos"), 1) &&
			is_empty(dataViews.data("is_nota_pedido"), 1) &&
			!is_empty($('#geral-id_nota_fiscal').val(), 1)
		) {
			text = l["aoSalvarNotaPerderaTotalmenteLinkItensPedidosCompra"];
		}

		/**
		 *
		 * VALIDAÇÃO DO FRONT FOI DESATIVADA
		 * TODO: REMOVER APÓS 3 MESES CASO NÃO SOLICITADO ATIVAÇÃO PELO CONSULTOR
		 *
		 */

		// [FLAG] Nao obrigar dados bancários e parcelas NFE
		// Validação se é obrigatório o preenchimento de dados bancários
		// let isNaoObrigatorioDadosBancariosParcelas = dataViews.data("flag_nao_obrigatorio_dados_bancarios_parcelas");
		// let isAtivoFlagIntegrarCamposPagamentoIntegrationBankNFEntrada = dataViews.data("flag_integrar_campos_pagamento_integration_bank");
		// if (isAtivoFlagIntegrarCamposPagamentoIntegrationBankNFEntrada == 1) {
		// 	if ((isNaoObrigatorioDadosBancariosParcelas == 1) && (flagsNaoObrigatorio.naoObrigarDadosBancarios == 0)) {
		// 		// Verifico se o banco está preenchido
		// 		if (($("#financeiro-banco").val() == null) || ($("#financeiro-banco").val() == "")) {
		// 			swal(
		// 				l["erro!"],
		// 				l["financeiroBancoObrigatorio"],
		// 				"error"
		// 			).catch(swal.noop);
		// 			forceToggleLoading(0);
		// 			return false;
		// 		}
		//
		// 		// Verifico se a agencia está preenchida
		// 		if (($("#financeiro-agencia").val() == null) || ($("#financeiro-agencia").val() == "")) {
		// 			swal(
		// 				l["erro!"],
		// 				l["financeiroAgenciaObrigatorio"],
		// 				"error"
		// 			).catch(swal.noop);
		// 			forceToggleLoading(0);
		// 			return false;
		// 		}
		//
		// 		// Verifico se a conta está preenchida
		// 		if (($("#financeiro-conta").val() == null) || ($("#financeiro-conta").val() == "")) {
		// 			swal(
		// 				l["erro!"],
		// 				l["financeiroContaObrigatorio"],
		// 				"error"
		// 			).catch(swal.noop);
		// 			forceToggleLoading(0);
		// 			return false;
		// 		}
		// 	}
		// }

		// [FLAG] Nao obrigar dados bancários e parcelas NFE
		// Validação se é obrigatório o preenchimento de parcelas
		// if ((isNaoObrigatorioDadosBancariosParcelas == 1) && (flagsNaoObrigatorio.naoObrigarParcelas == 0)) {
		// 	// Verifico se existe pelo menos uma parcela
		// 	if ($(".conteudo-parcelas_data_vencimento").length <= 1) {
		// 		swal(
		// 			l["erro!"],
		// 			l["preenchimentoUmaParcelaObrigatorio"],
		// 			"error"
		// 		).catch(swal.noop);
		// 		forceToggleLoading(0);
		// 		return false;
		// 	}
		// }

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
			idNota = !is_empty($("#geral-id_nota_fiscal").val(), true) ? $("#geral-id_nota_fiscal").val() : null;
			ajaxRequest(true, url, null, 'text', {'save': save}, function (ret) {
				try{
					ret = JSON.parse(ret);
					if(!is_empty(ret['bol'], 1)) {
						if(is_empty(idNota, 1)) {
							$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").remove();
							$("table#conteudo-despesas-tabela tbody tr:not(.ocultar)").remove();
							$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)").remove();

							$("input:not([type='hidden'])").val("");
							$("input[name='geral-refNFCancelada']").val("");
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

					if(!is_empty(ret['bol'], 1) && is_empty(idNota, 1) && !is_empty(dataViews.data("is_nota_pedido"), 1)) {
						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);
						setTimeout(function() {
							forceToggleLoading(0);
							$.redirect(urlThis + ret['notaFiscal']['idDFisNotasFiscais'], {...tokenCsrf});
						}, 2000);
					} else {
						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);

						forceToggleLoading(0);
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
	somaCamposDespesasErro();
	if(is_empty(ignoreSomaGeral, 1)) {
		somaCamposGeral();
	}
}

function somaCamposItens(atualizarLinhaEspecifica) {
	const linhas   = $("table#conteudo-itens-tabela tbody tr:not(.ocultar)");

	let valorTotaisLinhas = 0;
	let valorLinha = 0;
	$.each(linhas, function (idLinha, linha) {
		valorLinha = 0;
		// parametros usados para definir qual campo trará o preco para o total da linha
		// se for normal, traz o preco padrão que vem formatado com 2 casas decimais
		// senao, se for visualizacao e flag ativa de desconto na linha então pega o preco com 6 casas decimais
		let flagDescontoLinhaItem = $('.data_views').data('flag_desconto_linha_item');
		let flagCorrecaoValoresLinhaItem = $('.data_views').data('flag_ativar_correcao_valores_linha_item');
		let travaCampos = $('.data_views').data('trava_campos');
		if (configLocation.codigo === "USA") {
			if(is_empty(atualizarLinhaEspecifica, 1) || !is_empty($(linha).data("atualizar"), 1)) {
				valorLinha =
					(
						stringParaFloat($(linha).find('.conteudo-itens_quantidade').val(), separador_decimal_qtd, true) *
						// se a nota for sefaz e usar melhoria do desconto na linha do item para evitar erros de centavos
						((!is_empty(flagDescontoLinhaItem, true) || !is_empty(flagCorrecaoValoresLinhaItem, true)) && !is_empty(travaCampos, true)
								? convertCurrencyToFloat(configLocation.codigo, $(linha).find('.conteudo-itens_preco_unitario').data("preco"))
								: convertCurrencyToFloat(configLocation.codigo, $(linha).find('.conteudo-itens_preco_unitario').val())
						)
					) +
					convertCurrencyToFloat(configLocation.codigo, $(linha).find('.conteudo-itens_valor_despesa1').val()) +
					convertCurrencyToFloat(configLocation.codigo, $(linha).find('.conteudo-itens_valor_despesa2').val()) +
					convertCurrencyToFloat(configLocation.codigo, $(linha).find('.conteudo-itens_valor_despesa3').val());
				if (is_empty_numeric(valorLinha)) {
					valorLinha = 0;
				}

				$(linha).find('.conteudo-itens_total_linha').text(
					formataDecimal(
						valorLinha,
						".",
						configLocation.currencyDecimalPoint,
						configLocation.currencyThousandsSeparator,
						configLocation.currencySymbol,
						true,
						casasPreco
					)
				);

				$(linha).data("atualizar", 0);
			}

			valorTotaisLinhas += Math.round(valorLinha * 100) / 100;
		} else {
				if (is_empty(atualizarLinhaEspecifica, 1) || !is_empty($(linha).data("atualizar"), 1)) {
					valorLinha =
						(
							stringParaFloat($(linha).find('.conteudo-itens_quantidade').val(), 6, true) *
							// se a nota for sefaz e usar melhoria do desconto na linha do item para evitar erros de centavos
							((!is_empty(flagDescontoLinhaItem, true) || !is_empty(flagCorrecaoValoresLinhaItem, true)) && !is_empty(travaCampos, true)
									? stringParaFloat($(linha).find('.conteudo-itens_preco_unitario').data("preco"), 6, true)
									: stringParaFloat($(linha).find('.conteudo-itens_preco_unitario').val(), separador_decimal_moeda, true)
							)
						) +
						stringParaFloat($(linha).find('.conteudo-itens_valor_despesa1').val(), separador_decimal_moeda, true) +
						stringParaFloat($(linha).find('.conteudo-itens_valor_despesa2').val(), separador_decimal_moeda, true) +
						stringParaFloat($(linha).find('.conteudo-itens_valor_despesa3').val(), separador_decimal_moeda, true);
					if (is_empty_numeric(valorLinha)) {
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
		}
	});
	if (configLocation.codigo === "USA") {
		$('#conteudo-itens_total_geral').text(
			formatFloatToCurrency(configLocation.codigo, valorTotaisLinhas)
		);
	} else {
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
				casasValor
			)
		);
	}
}

function somaCamposLotes() {
	let casasQtd = dataViews.data('casas_qtd');
	const thousand_delimiter_qtd = dataViews.data('thousand_delimiter_qtd');
	const separador_decimal_qtd = dataViews.data('decimal_delimiter_qtd');
	const linhas   = $("table#conteudo-lotes-tabela tbody tr:not(.ocultar)");

	if(is_empty(casasQtd, 1)) casasQtd = '0';
	casasQtd = parseInt(casasQtd.toString());

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		if(!is_empty(dataViews.data("vizualizacao"), 1)) {
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

	let valorTotaisLinhas = 0;
	let valorTotalSemFinanceiro = 0;

	if (configLocation.codigo === "USA") {
		$.each(linhas, function (idLinha, linha) {
			valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo,$(linha).find('.conteudo-parcelas_valor').val())

			valorTotalSemFinanceiro += convertCurrencyToFloat(configLocation.codigo, $(linha).find('.conteudo-parcelas_valor').val()
			);

			valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo,
				$(linha).find('.conteudo-parcelas_valor_multa').val()
			);

			valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo,
				$(linha).find('.conteudo-parcelas_valor_encargos').val()
			);

			valorTotaisLinhas -= convertCurrencyToFloat(configLocation.codigo,
				$(linha).find('.conteudo-parcelas_valor_desconto').val()
			);

			valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo,
				$(linha).find('.conteudo-parcelas_valor_juros').val()
			);
		});

		valorTotaisLinhas =
			convertCurrencyToFloat(
				configLocation.codigo,
				valorTotaisLinhas.toFixed(casasPreco).toString()
			);

		valorTotalSemFinanceiro =
			convertCurrencyToFloat(
				configLocation.codigo,
				valorTotalSemFinanceiro.toFixed(casasPreco).toString()
			);

		$('#conteudo-parcelas_total_geral_financeiro').text(
			formatFloatToCurrency(
				configLocation.codigo,
				valorTotaisLinhas
			)
		);

		$('#conteudo-parcelas_total_geral').text(
			formatFloatToCurrency(
				configLocation.codigo,
				valorTotalSemFinanceiro
			)
		);
	} else {
		$.each(linhas, function (idLinha, linha) {
			valorTotaisLinhas += stringParaFloat(
				$(linha).find('.conteudo-parcelas_valor').val(), separador_decimal_moeda, true
			);

			valorTotalSemFinanceiro += stringParaFloat(
				$(linha).find('.conteudo-parcelas_valor').val(), separador_decimal_moeda, true
			);

			valorTotaisLinhas += stringParaFloat(
				$(linha).find('.conteudo-parcelas_valor_multa').val(), separador_decimal_moeda, true
			);

			valorTotaisLinhas += stringParaFloat(
				$(linha).find('.conteudo-parcelas_valor_encargos').val(), separador_decimal_moeda, true
			);

			valorTotaisLinhas -= stringParaFloat(
				$(linha).find('.conteudo-parcelas_valor_desconto').val(), separador_decimal_moeda, true
			);

			valorTotaisLinhas += stringParaFloat(
				$(linha).find('.conteudo-parcelas_valor_juros').val(), separador_decimal_moeda, true
			);
		});

		valorTotaisLinhas =
			stringParaFloat(
				valorTotaisLinhas.toFixed(casasPreco).toString(),
				'.',
				true
			);

		valorTotalSemFinanceiro =
			stringParaFloat(
				valorTotalSemFinanceiro.toFixed(casasPreco).toString(),
				'.',
				true
			);

		$('#conteudo-parcelas_total_geral_financeiro').text(
			formataDecimal(
				valorTotaisLinhas,
				'.',
				separador_decimal_moeda,
				separador_milhar_moeda,
				cifrao_moeda,
				true,
				casasValor
			)
		);

		$('#conteudo-parcelas_total_geral').text(
			formataDecimal(
				valorTotalSemFinanceiro,
				'.',
				separador_decimal_moeda,
				separador_milhar_moeda,
				cifrao_moeda,
				true,
				casasValor
			)
		);
	}

}

function somaCamposDespesas() {
	const linhas   = $("table#conteudo-despesas-tabela tbody tr:not(.ocultar)");

	let valorTotaisLinhas = 0;
	if (configLocation.codigo === "USA") {
		$.each(linhas, function (idLinha, linha) {
			valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo, $(linha).find('.conteudo-despesas_valor_despesa').val());
		});
		valorTotaisLinhas = convertCurrencyToFloat(configLocation.codigo, valorTotaisLinhas);

		$('#conteudo-despesas_total_geral').text(
			formatFloatToCurrency(configLocation.codigo, valorTotaisLinhas)
		);
	} else {
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
				casasValor
			)
		);
	}
}

function somaCamposDespesasErro() {
	const linhas   = $("table#conteudo-despesas_erro-tabela tbody tr:not(.ocultar)");

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		valorTotaisLinhas += stringParaFloat(
			$(linha).find('.conteudo-despesas_erro_valor').val(), separador_decimal_moeda, true
		);
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasPreco).toString(),
			'.',
			true
		);

	$('#conteudo-despesas_erro_total_geral').text(
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
	let totalImpostosRetidos = $("#conteudo-valor_total_imposto");

	let valorTotaisLinhas = 0;
	let valorTotaisLinhasMenosImpostos = 0;
	if (configLocation.codigo === "USA") {
		valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo, $(totalItens).text());
		valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo, $(totalDespesas).text());
		valorTotaisLinhas += convertCurrencyToFloat(configLocation.codigo, $(totalDespesasErro).text());

		valorTotaisLinhasMenosImpostos = (valorTotaisLinhas - convertCurrencyToFloat(configLocation.codigo, $(totalImpostosRetidos).val()));

		valorTotaisLinhas = convertCurrencyToFloat(
			configLocation.codigo,
			valorTotaisLinhas.toFixed(casasPreco).toString()
		);

		$('#conteudo-total_geral').text(
			formatFloatToCurrency(configLocation.codigo, valorTotaisLinhas)
		);

		valorTotaisLinhasMenosImpostos = convertCurrencyToFloat(
			configLocation.codigo,
			valorTotaisLinhasMenosImpostos.toFixed(casasPreco).toString()
		);

		if (valorTotaisLinhasMenosImpostos < 0) {
			valorTotaisLinhasMenosImpostos = 0;
		}

		$('#conteudo-total_geral_menos_impostos').text(
			formatFloatToCurrency(
				configLocation.codigo,
				valorTotaisLinhasMenosImpostos
			)
		);
	} else {
		valorTotaisLinhas += stringParaFloat($(totalItens).text(), separador_decimal_moeda, true);
		valorTotaisLinhas += stringParaFloat($(totalDespesas).text(), separador_decimal_moeda, true);
		valorTotaisLinhas += stringParaFloat($(totalDespesasErro).text(), separador_decimal_moeda, true);

		valorTotaisLinhasMenosImpostos = (valorTotaisLinhas - stringParaFloat($(totalImpostosRetidos).val(), separador_decimal_moeda, true));

		valorTotaisLinhas = stringParaFloat(
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
				casasValor
			)
		);

		valorTotaisLinhasMenosImpostos = stringParaFloat(
			valorTotaisLinhasMenosImpostos.toFixed(casasPreco).toString(),
			'.',
			true
		);

		if (valorTotaisLinhasMenosImpostos < 0) {
			valorTotaisLinhasMenosImpostos = 0;
		}

		$('#conteudo-total_geral_menos_impostos').text(
			formataDecimal(
				valorTotaisLinhasMenosImpostos,
				'.',
				separador_decimal_moeda,
				separador_milhar_moeda,
				cifrao_moeda,
				true,
				casasValor
			)
		);
	}
}

function triggerSomaCampos() {
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
		return;
	}

	$("table#conteudo-itens-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		$($(this).parents("tr")).data("atualizar", 1);
		if (configLocation.codigo === "USA") {
			somaCamposItens(false);
		} else {
			somaCamposItens(true);
		}
		somaCamposGeral();
	});

	$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposParcelas();
	});

	$("table#conteudo-despesas-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposDespesas();
		somaCamposGeral();
	});

	$("#conteudo-valor_total_imposto").off("keyup").on("keyup", function() {
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

function controlaModalsRelacionamentosPedidos() {
	$('.modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela').off('click');
	$('.modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela').on('click', 'tbody tr:not(.ocultar)', function() {
		let __hasClass = $(this).hasClass("selected");
		$($(this).parents("tbody").find("tr")).removeClass("selected");

		if(!__hasClass) {
			$(this).addClass("selected");
		}
	});

	$('.modal-atualizar_relacao_pedidos').off('hidden.bs.modal');
	$('.modal-atualizar_relacao_pedidos').on('hidden.bs.modal', function (e) {
		$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-item").html("");
		$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-unidade").html("");
		$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-quantidade").html("");
		$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-preco").html("");
		$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-total_linha").html("");
		$($(".modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela tbody").find("tr:not(.ocultar)")).remove();
	});

	$('.modal-visualizar_relacao_pedidos').off('hidden.bs.modal');
	$('.modal-visualizar_relacao_pedidos').on('hidden.bs.modal', function (e) {
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-item").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-unidade").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-quantidade").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-preco").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-total_linha").html("");

		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_id").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_id_erp").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_doc_num").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_data_criacao").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_data_entrega").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_condicao_pagamento").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_total_geral").html("");

		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_produto").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_unidade").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_quantidade").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_quantidade_restante").html("");
		$(".modal-visualizar_relacao_pedidos .visualizar_relacao_pedidos-pedido_item_preco_unitario").html("");
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
		if(is_empty(dataViews.data("vizualizacao"))) {
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
			if(!is_empty(dataViews.data("vizualizacao"), 1)) {
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
		let casasQtd = dataViews.data('casas_qtd');
		const separador_decimal_qtd = dataViews.data('decimal_delimiter_qtd');

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

/**
 *
 * Função para buscar as flags de 'nao obrigatorio dados bancarios e parcelas' da forma de pagamento selecionada
 *
 */

function buscarFlagsFormaPagamentoNaoObrigatorio() {
	$("select#financeiro-forma_pagamento").on("change", function () {
		let idFormaPagamento = $(this).val();
		let url = dataViews.data("url_ajax_flags_nao_obrigatorias_formas_pgto");
		toggleLoading();
			ajaxRequest(
				true,
				url,
				null,
				'text',
				{ idFormaPagamento },
				function (ret) {
					toggleLoading();
					try{
						ret = JSON.parse(ret);
						flagsNaoObrigatorio.naoObrigarDadosBancarios = ret.naoObrigarDadosBancarios;
						flagsNaoObrigatorio.naoObrigarParcelas       = ret.naoObrigarParcelas;
					}catch(err){
						flagsNaoObrigatorio.naoObrigarDadosBancarios = 0
						flagsNaoObrigatorio.naoObrigarParcelas       = 0
						forceToggleLoading(0);
					}
				}
			);
	});
}

initFields();
initButtons();
controlaSelectFornecedor();
controlaSelectInfoPagamento();
controlaSelectMoeda();
controlaSelectProduto();
salvarDados();
controlaLote();
somaCamposAll();
triggerSomaCampos();

if(is_empty(dataViews.data("vizualizacao"), 1)) {
	contaCaracteres(254, 'geral-observacoes');
	contaCaracteres(254, 'geral-observacoes2');
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

		somaCamposParcelas();
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

			if (x > y) {
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

controlaModalsRelacionamentosPedidos();
controlaReplicancias();
buscarFlagsFormaPagamentoNaoObrigatorio();
