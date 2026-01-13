const flagsNaoObrigatorio = {
	naoObrigarDadosBancarios: 0,
	naoObrigarParcelas: 0
};

const dataViews = $("div.data_views");
let casasPreco = dataViews.data('casas_preco');
if(is_empty(casasPreco, 1)) casasPreco = '0';
casasPreco = parseInt(casasPreco.toString());

let casasValor = dataViews.data('casas_valor');
if(is_empty(casasValor, 1)) casasValor = '2';
casasValor = parseInt(casasValor.toString());

const cifrao_moeda = dataViews.data('prefixo_moeda');
const separador_decimal_moeda = dataViews.data('decimal_delimiter_moeda');
const separador_milhar_moeda = dataViews.data('thousand_delimiter_moeda');
const separador_decimal_qtd = dataViews.data('decimal_delimiter_qtd');

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
	let __verificarDoc = function (url, idItem) {
		if(is_empty(dataViews.data("vizualizacao"))) {
			return;
		}

		const idNota = $("input#geral-id_nota_fiscal").val();
		if(is_empty(idNota, 1) || is_empty(url, 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				l["erro"]
			).catch(swal.noop);
			return;
		}
		if(is_empty(idItem)) {
			idItem = null;
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
				url,
				null,
				'text',
				{idNota, idItem},
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

	$("button.check_fornecedor").off("click");
	$("button.check_fornecedor").on("click", function () {
		__verificarDoc(dataViews.data("url_verificar_fornecedor"));
	});

	$("button.check_modelo").off("click");
	$("button.check_modelo").on("click", function () {
		__verificarDoc(dataViews.data("url_verificar_modelo"));
	});

	$("button.verificar_de-para_item").off("click");
	$("button.verificar_de-para_item").on("click", function () {
		__verificarDoc(dataViews.data("url_verificar_de_para_produtos"), $($(this).parents("tr").find("input.conteudo-itens_id_interno")).val());
	});

	$("button.verificar_documento").off("click");
	$("button.verificar_documento").on("click", function () {
		__verificarDoc(dataViews.data("url_verificar_doc"));
	});

	$('button.toggle_replicacao').off('click');
	$('button.toggle_replicacao').on('click', function () {
		if(is_empty(dataViews.data("vizualizacao"))) {
			return;
		}

		if($(".grupo_replicacao").hasClass("ocultar")) {
			$(".grupo_replicacao").removeClass("ocultar");
		} else {
			$(".grupo_replicacao").addClass("ocultar");
		}
	});

	let __validacaoCadastrarDePara = function ($tipo = 'itens') {
		if(is_empty(dataViews.data("vizualizacao"))) {
			return false;
		}
		if($tipo === 'itens' && is_empty($("#geral-id_fornecedores_real").val())) {
			return false;
		}

		const idNota = $("input#geral-id_nota_fiscal").val();
		const url = ($tipo === 'itens') ? dataViews.data("url_cadastrar_de_para_produtos") : dataViews.data("url_cadastrar_de_para_modelos");

		if(is_empty(idNota, 1) || is_empty(url, 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				'error'
			).catch(swal.noop);
			return false;
		}

		return {
			"idNota": idNota,
			"url": url,
		}
	};

	$("button.adicionar_de-para_item").off("click");
	$("button.adicionar_de-para_item").on("click", function () {
		let modal = $(".modal-adicionar_de-para_produtos");
		const constantes = __validacaoCadastrarDePara();
		if(is_empty(constantes, 1)) {
			return;
		}
		if(is_empty($($(this).parents('tr').find(".conteudo-itens_has_depara_error")).val())) {
			return false;
		}

		const idOrigem = $($(this).parents("tr").find(".conteudo-itens_id_item")).val();
		const nomeOrigem =
			$($(this).parents("tr").find(".conteudo-itens_nome_item")).text().trim() +
			" - " +
			$($(this).parents("tr").find(".conteudo-itens_nome_unidade")).text().trim();
		if(is_empty(idOrigem, 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				'error'
			).catch(swal.noop);
			return false;
		}

		$($(modal).find("#id_origem_adicionar_de-para_produtos")).data('id', idOrigem);
		$($(modal).find("#id_origem_adicionar_de-para_produtos")).text(nomeOrigem);
		$($(modal).find("#produto_adicionar_de-para_produtos")).val("").trigger('change').trigger('select2:unselect');
		$($(modal).find("#unidade_adicionar_de-para_produtos")).val("").trigger('change').trigger('select2:unselect');
		$($(modal).find("#utilizacao_adicionar_de-para_produtos")).val("").trigger('change').trigger('select2:unselect');
		$($(modal).find("#quantidade_adicionar_de-para_produtos")).val("");

		$(modal).modal('toggle');
		$(modal).modal('toggle');
	});

	$("button.adicionar_de-para_modelos").off("click");
	$("button.adicionar_de-para_modelos").on("click", function () {
		let modal = $(".modal-adicionar_de-para_modelos");
		const constantes = __validacaoCadastrarDePara('modelo');

		if(is_empty(constantes, 1)) {
			return;
		}

		if(is_empty($(modal).find("#id_origem_adicionar_de-para_modelos").val(), 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				'error'
			).catch(swal.noop);
			return false;
		}

		$($(modal).find("#modelo_adicionar_de-para_modelos")).val("").trigger('change').trigger('select2:unselect');

		$(modal).modal('toggle');
	});

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

	// tratativas para vínculo entre itens de pedidos e itens da nota
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

				let tbody = $(".modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela tbody");
				$($(tbody).find("tr:not(.ocultar)")).remove();
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

	// tratativas para vínculo entre itens de pedidos e itens da nota


	// tratativas para visualização do vínculo entre itens de pedidos e itens da nota
	$("button.visualizar_relacao-relacao_item_pedido").off("click");
	$("button.visualizar_relacao-relacao_item_pedido").on("click", function () {
		const obj = $(this);
		const dados = __validaRelacionamento($(obj));
		if(is_empty(dados, 1)) return;

		toggleLoading();
		ajaxRequest(
			true,
			dados.url,
			null,
			'text',
			{'idNota': dados.idNota, 'idItemNota': dados.idItem},
			function (ret) {
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
			}
		);


	});
	// tratativas para vínculo entre itens de pedidos e itens da nota
}

function salvarDados() {
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
		return;
	}

	$("button.salvar").off("click");
	$("button.salvar").on("click", function (e) {
		let url = dataViews.data("url_salvar");
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
				idDFisNotasFiscaisServico: $("#geral-id_nota_fiscal").val(),
				idFornecedoresReal: $("#geral-id_fornecedores_real").val(),
				idFornecedores: $("#geral-id_fornecedores").val(),
				dataLancamento: $("#conteudo-data_lancamento").val(),
				dataDocumento: $("#conteudo-data_documento").val(),
				refNFCancelada: $("#geral-refNFCancelada").val(),
				itens: [],
				parcelas: [],
				anexos: [],
				idFormasPagamento: $("#financeiro-forma_pagamento").val(),
				financeiroProtesto: $("#financeiro-protesto").val(),
				idFornecedoresContatos: $("#financeiro-contato").val(),
				intBankBanco: $("#financeiro-banco").val(),
				intBankAgencia: $("#financeiro-agencia").val(),
				intBankConta: $("#financeiro-conta").val(),
				idModelosNotaFiscal: $("#imposto-modelo").val(),
				serieNotaFiscal: $("#imposto-serie_nota").val(),
				numeroNotaFiscal: $("#imposto-numero_nota").val(),
				observacoes: $.trim($("#geral-observacoes").val()),
				observacoes2: $.trim($("#geral-observacoes2").val()),
				isPrefeitura: $("#geral-is_prefeitura").val(),
				isIntegracao: $("#geral-is_integracao").val(),
				isRefazNotaCancelada: $("#geral-is_refaz_nota_cancelada").val(),
				totalDocumento: $("#conteudo-total_geral").html(),
				tipoIntegracao: $("#geral-tipo_integracao").val(),
				valorTotalImpostosRetidos: $("#conteudo-valor_total_imposto").val(),
			};

			if($("table#conteudo-itens-tabela tbody tr:not(.ocultar)").length > 0) {
				$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").each(function () {
					if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
						return;
					}

					save["itens"].push({
						idDFisItensNotasFiscaisServico: $(this).find(".conteudo-itens_id_interno").val(),
						precoUnitario: $(this).find(".conteudo-itens_preco_unitario").val(),
						quantidade: $(this).find(".conteudo-itens_quantidade").val(),
						idProdutos: $(this).find(".conteudo-itens_id_item").val(),
						idUnidadesMedidas: $(this).find(".conteudo-itens_unidade").val(),
						idDepositos: $(this).find(".conteudo-itens_deposito").val(),
						idProjeto: $(this).find(".conteudo-itens_projeto").val(),
						idTipoUtilizacaoProduto: $(this).find(".conteudo-itens_utilizacao").val(),

						// ids para relacionamento dos itens de pedidos de compra previamente realizados
						idItemPedidoNotaPedido: $(this).find(".conteudo-itens_pedido_item_id").val(),

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

			if($("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)").length > 0) {
				$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)").each(function () {
					if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
						return;
					}

					save["parcelas"].push({
						idDFisParcelasNotasFiscaisServico: $(this).find(".conteudo-parcelas_id_interno").val(),
						valor: $(this).find(".conteudo-parcelas_valor").val(),
						valorDesconto: $(this).find(".conteudo-parcelas_valor_desconto").val(),
						valorJuros: $(this).find(".conteudo-parcelas_valor_juros").val(),
						valorMulta: $(this).find(".conteudo-parcelas_valor_multa").val(),
						valorEncargosBancarios: $(this).find(".conteudo-parcelas_valor_encargos").val(),
						dataVencimento: $(this).find(".conteudo-parcelas_data_vencimento").val(),
						codigoBarras: $(this).find(".conteudo-parcelas_codigo_barras").val(),
						linhaDigitavel: $(this).find(".conteudo-parcelas_linha_digitavel").val(),
					});
				});
			}

			// tratativa para o envio de anexos
			if($(".preview-doc").length > 0) {
				$(".preview-doc").each(function () {
					save["anexos"].push({
						nomeArquivo: $(this).find(".file-name").val(),
						anexo: $(this).find(".file-blob").val(),
						idDFisAnexosNotasFiscaisServico: $(this).find(".file-id").val(),
					});
				});
			}
			// tratativa para o envio de anexos

			ajaxRequest(
				true,
				url,
				null,
				'text',
				{ save },
				function (ret) {
					try{
						ret = JSON.parse(ret);
						if(!is_empty(ret['bol'], 1)) {
							if(is_empty(save['idDFisNotasFiscaisServico'], 1)) {
								$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").remove();
								$("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)").remove();

								$("input:not([type='hidden'])").val("");
								$("input[name='geral-refNFCancelada']").val("");
								$("textarea").val("");

								// reseta os valores inseridos nos select2
								$('select.select_ajax').val('').change();

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

						forceToggleLoading(0);
					}catch(err){
						consoleProduction(err);
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

function somaCamposAll(ignoreSomaGeral) {
	somaCamposItens();
	somaCamposParcelas();
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
		if(is_empty(atualizarLinhaEspecifica, 1) || !is_empty($(linha).data("atualizar"), 1)) {
			valorLinha =
				(
					stringParaFloat($(linha).find('.conteudo-itens_quantidade').val(), separador_decimal_qtd, true) *
					stringParaFloat($(linha).find('.conteudo-itens_preco_unitario').val(), separador_decimal_moeda, true)
				);
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
			casasValor
		)
	);
}

function somaCamposParcelas() {
	const linhas   = $("table#conteudo-parcelas-tabela tbody tr:not(.ocultar)");

	let valorTotaisLinhas = 0;
	let valorTotalSemFinanceiro = 0;

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

function somaCamposGeral() {
	let totalItens        = $("table#conteudo-itens-tabela tfoot tr #conteudo-itens_total_geral");
	let totalImpostosRetidos = $("#conteudo-valor_total_imposto");

	let valorTotaisLinhas = 0;
	let valorTotaisLinhasMenosImpostos = 0;

	valorTotaisLinhas += stringParaFloat($(totalItens).text(), separador_decimal_moeda, true);
	valorTotaisLinhasMenosImpostos = (valorTotaisLinhas - stringParaFloat($(totalImpostosRetidos).val(), separador_decimal_moeda, true));

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

function triggerSomaCampos() {
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
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

	$("#conteudo-valor_total_imposto").off("keyup").on("keyup", function() {
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
	let __validacaoCadastrarDePara = function ($tipo = 'itens') {
		if(is_empty(dataViews.data("vizualizacao"))) {
			return false;
		}

		if($tipo === 'itens' && is_empty($("#geral-id_fornecedores_real").val())) {
			return false;
		}

		const guidNota = $("input#geral-id_nota_fiscal").val();
		const url = ($tipo === 'itens') ? dataViews.data("url_cadastrar_de_para_produtos") : dataViews.data("url_cadastrar_de_para_modelos");

		if(is_empty(guidNota, 1) || is_empty(url, 1)) {
			swal(
				l["erro"],
				l["impossivelVerificarAcao"],
				'error'
			).catch(swal.noop);
			return false;
		}

		return {
			"guidNota": guidNota,
			"url": url,
		}
	};

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

	$("select#produto_adicionar_de-para_produtos").off("select2:unselect");
	$("select#produto_adicionar_de-para_produtos").on("select2:unselect", function () {
		__funControlaUnidadesMedidas($("select#unidade_adicionar_de-para_produtos"), null);
	});

	$("select#produto_adicionar_de-para_produtos").off("select2:select");
	$("select#produto_adicionar_de-para_produtos").on("select2:select", function () {
		__funControlaUnidadesMedidas($("select#unidade_adicionar_de-para_produtos"), $(this).val());
	});

	$("button.salvar_de-para_itens").off("click");
	$("button.salvar_de-para_itens").on("click", function () {
		let modal = $(".modal-adicionar_de-para_produtos");
		const constantes = __validacaoCadastrarDePara();

		if(is_empty(constantes, 1)) { return; }

		const idOrigem = $($(modal).find("#id_origem_adicionar_de-para_produtos")).data('id');
		const idProduto = $($(modal).find("#produto_adicionar_de-para_produtos")).val();
		const idUnidade = $($(modal).find("#unidade_adicionar_de-para_produtos")).val();
		const idUtilizacao = $($(modal).find("#utilizacao_adicionar_de-para_produtos")).val();
		const ibgeCode = $('#geral-id_fornecedor_ibge').val();
		const idMunicipio = ibgeCode === '-' ? 0 : ibgeCode;

		const isIntegracao = $('.data_views').data('is_integracao');

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
				constantes['url'],
				null,
				'text',
				{
					'guidNota': constantes['guidNota'],
					idOrigem,
					idProduto,
					idUnidade,
					idMunicipio,
					idUtilizacao,
					isIntegracao,
				},
				function (ret) {
					try{
						consoleProduction(ret);
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
	});

	$("button.salvar_de-para_modelo").off("click");
	$("button.salvar_de-para_modelo").on("click", function () {
		let modal = $(".modal-adicionar_de-para_modelos");

		const constantes = __validacaoCadastrarDePara('modelo');
		if(is_empty(constantes, 1)) { return; }

		const idOrigem = $($(modal).find("#id_origem_adicionar_de-para_modelos")).val();
		const idModelo = $($(modal).find("#modelo_adicionar_de-para_modelos")).val();

		console.log(idOrigem, idModelo)

		if (idOrigem == null || idModelo == null) {
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
				constantes['url'],
				null,
				'text',
				{
					'guidNota': constantes['guidNota'],
					idOrigem,
					idModelo
				},
				function (ret){
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
	});
}

function controlaUnidadesMedidas(objUnidade, objVal, ObjAux) {
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
			($(data_views).data("url_ajax_formas_pagamento") + (is_empty($(obj).val(), 1) ? "" : $(obj).val()))
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

	// desabilita informacoes_details se não existir um fornecedor
	// se a flag estiver ativa
	if( $('div.data_views').data('isnew') === 1
		&& is_empty($("#geral-id_fornecedores").val())
	) {
		if ( dataViews.data('flag_exibir_somente_itens_servico_por_fornecedor') == '1') {
			$(".informacoes_details").hide();
		}
	} else {
		$(".informacoes_details").show();
	}

	// desabilita geral-id_fornecedores se for edição
	if($('div.data_views').data('isnew') === 0) {
		// $("select#geral-id_fornecedores").select2({disabled:'readonly'})
	}

	// tratativa ao desselecionar um fornecedor
	$("select#geral-id_fornecedores").off("select2:unselect");
	$("select#geral-id_fornecedores").on("select2:unselect", function () {
		// zera o valor do campo de id enviado para validação no backend
		if ( dataViews.data('flag_exibir_somente_itens_servico_por_fornecedor') == '1') {
			$(".informacoes_details").hide();
		}
		$('#geral-id_fornecedores_real').val('');

		// desseleciona o banco, afetando agencia e conta
		$("select#financeiro-banco").trigger('select2:unselect');

		// chama a função de controle das formas de pagamento possíveis
		// de um fornecedor
		__funControlaFormasPagamento($(this));
		__funControlaContato($(this));
		__funControlaBancoPagamento($(this), true);

	});

	// tratativa ao selecionar um  fornecedor
	$("select#geral-id_fornecedores").off("select2:select");
	$("select#geral-id_fornecedores").on("select2:select", function (e) {
		let novoValor = e.params.data.id;
		let jQrObject = $(this);
		controlaOnSelectFornecedor(jQrObject, novoValor);
		/*
		if($("select.conteudo-itens_id_item").length > 1) {
			swal({
				title: l["desejaContinuar?"],
				text: l["aoContinuarTodosOsItensSeraoRemovidosDaNotaFiscal"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["sim!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				$("#conteudo-itens-tabela tr:not(.ocultar) .remove-itens-table-geral").trigger('click');
				controlaOnSelectFornecedor(jQrObject, novoValor);
			}, function () {
				$("select#geral-id_fornecedores").val($('#geral-id_fornecedores_real').val()).select2Ajax();
			}).catch(swal.noop);
		} else {
			controlaOnSelectFornecedor($(this), $('#geral-id_fornecedores').val());
		}
		*/
	});

	controlaOnSelectFornecedor = (jQrObject, novoValor) => {
		// preenche o valor do campo de id enviado para validação no backend
		$('#geral-id_fornecedores_real').val(novoValor);
		jQrObject.val(novoValor).select2Ajax();
		$(".informacoes_details").show();
		// chama a função de controle das formas de pagamento possíveis
		// de um fornecedor
		__funControlaFormasPagamento(jQrObject);
		__funControlaContato(jQrObject);
		__funControlaBancoPagamento(jQrObject);

		// atribui o id do fornecedor a um data_view para checar a flag de somente itens por PN
		$(".data_views").data("fornecedor_selecionado", novoValor);

		// atribui a data-url referente ao fornecedor selecionado nos campos de itens
		controlaItensPorFornecedor();
	}
}

function controlaItensPorFornecedor() {
	let dataUrl = $('div.data_views').data('url_select2ajaxprodutosservico')
	let fornecedor = $('div.data_views').data('fornecedor_selecionado')
	$("select.conteudo-itens_id_item").data('url', dataUrl+fornecedor).select2Ajax()
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

		if ($(select).hasClass("select2-hidden-accessible")) {
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
salvarDados();
somaCamposAll();
triggerSomaCampos();
controlaSelectFornecedor();
controlaSelectInfoPagamento();
buscarFlagsFormaPagamentoNaoObrigatorio();
controlaItensPorFornecedor();

if(is_empty(dataViews.data("vizualizacao"), 1)) {
	contaCaracteres(254, 'geral-observacoes');
	contaCaracteres(254, 'geral-observacoes2');
}

controlaTabelaSuite({
	"ref": "#conteudo-itens-tabela",
	"funAposAddItem": function () {
		triggerSomaCampos();
		$.each(initByElementReplicancias, function(idElemento, valores) {
			controlaReplicancias_addValores(idElemento, $($($("#conteudo-itens-tabela").find(valores['replicar_para'])).last()));
		});
	},
	"funAposRemoverItem": function () {
		somaCamposAll();
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
	}
});

controlaReplicancias();

// tratativa para quando, em notas fiscais manuais, ao alterar o produto
// carregar automaticamente as unidades de medida no select
$('body').on('click', 'button.add-item' , function() {

	$("select.conteudo-itens_id_item").last().change(function () {
		controlaUnidadesMedidas(
			$(this).parents('tr').find('.conteudo-itens_unidade'),
			($(this).val() ?? null)
		);
	})
	controlaItensPorFornecedor()
})

$("select.conteudo-itens_id_item").last().change(function () {
	controlaUnidadesMedidas(
		$(this).parents('tr').find('.conteudo-itens_unidade'),
		($(this).val() ?? null)
	);
})

function limparItensServico(item) {
	$(item).parents('tr').find('.conteudo-itens_quantidade').val("");
	$(item).parents('tr').find('.conteudo-itens_preco_unitario').val("");
	$(item).parents('tr').find('.conteudo-itens_total_linha').text("R$0,00");
	$(item).parents('tr').find('.conteudo-itens_utilizacao').val(null).trigger('change.select2');
	$(item).parents('tr').find('.conteudo-itens_deposito').val(null).trigger('change.select2');
	$(item).parents('tr').find('.conteudo-itens_projeto').val(null).trigger('change.select2');
}

/**
 * Função para tratativa de upload, atualização e remoção de anexos ao criar/editar
 */
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

			if(!is_empty(dataViews.data("vizualizacao"), 1)) {
				const url = dataViews.data("url_baixar_anexos");
				let id = $(obj).data('id');
				$($(obj).find(".action-visualize")).attr("href", (url + id));
				$($(obj).find(".action-visualize")).attr("target", "_blank");

				$($(obj).find(".action-visualize")).html($(".data_views").data("text_download_upload"));
			}
		}
	);
	if(!is_empty(dataViews.data("vizualizacao"), 1)) {
		$('div#documentos_anexo .link-adiciona-files').remove();
	}

	/**
	 * Função com tratativas pra quando as linhas dos modais forem clicadas
	 */
	function controlaModalsRelacionamentosPedidos() {
		$('.modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela').off('click');
		$('.modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela').on('click', 'tbody tr:not(.ocultar)', function() {
			let __hasClass = $(this).hasClass("selected");
			$($(this).parents("tbody").find("tr")).removeClass("selected");

			if(!__hasClass) { $(this).addClass("selected"); }
		});

		// modal de atualização
		$('.modal-atualizar_relacao_pedidos').off('hidden.bs.modal');
		$('.modal-atualizar_relacao_pedidos').on('hidden.bs.modal', function (e) {
			$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-item").html("");
			$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-unidade").html("");
			$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-quantidade").html("");
			$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-preco").html("");
			$(".modal-atualizar_relacao_pedidos .atualizar_relacao_pedidos-total_linha").html("");
			$($(".modal-atualizar_relacao_pedidos table#atualizar_relacao_pedidos-itens_pedidos_tabela tbody").find("tr:not(.ocultar)")).remove();
		});
		// modal de atualização

		// modal de visualização
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
		// modal de visualização
	}

	controlaModalsRelacionamentosPedidos();
}

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
