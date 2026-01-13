let data_views = $(".data_views");
let casasQtd = $(data_views).data("casas_qtd");
let actionChangeValores = "keyup";

function initFields(tipo, objLinha) {
	if(is_empty(tipo, 1)) {
		tipo = "all";
	}

	let __initContadorCharsObservacao = function () {
		if (!is_empty($(data_views).data('controle_obs'), 1)) {
			return;
		}

		contaCaracteres(254, 'observacoes');
	};

	let __initAutomatizacaoDatas = function () {
		if(is_empty($(data_views).data("automatizar_datas"), 1)) {
			return;
		}

		$("#dataPedido").off("dp.change");
		$("#dataPedido").on("dp.change", function (e) {
			$("#dataInicio").val($(this).val());
			$("#dataVencimento").val($(this).val());
		});
	};

	let __initFieldsCabecalho = function () {
		let __initCalculosCabecalho = function () {
			$("#quantidadePlanejadaCab").off(actionChangeValores);
			$("#quantidadePlanejadaCab").on(actionChangeValores, function () {
				if($("#bol_controlar_calculos_auto").is(':checked')) {
					return;
				}

				let qtdPlanejadaCabecalho = formataDecimal($(this).val(), ",", ".", "", "", true, casasQtd) * 1;
				let qtdBaseItem = null;
				let qtdPlanejadaItem = null;
				$("#tabela-itens tbody tr:not(.ocultar)").each(function (indexTR, thisTR) {
					qtdBaseItem = formataDecimal($($(thisTR).find(".quantidadeBase")).val(), ",", ".", "", "", true, casasQtd) * 1;
					if(is_empty(qtdBaseItem, 1)) {
						qtdBaseItem = $($(thisTR).find(".item-qtd_base")).val() * 1;
					}
					qtdPlanejadaItem = qtdBaseItem * qtdPlanejadaCabecalho;
					$($(thisTR).find(".item-ultima_qtd_planejada")).val(qtdPlanejadaItem);
					$($(thisTR).find(".quantidadePlanejada")).val(formataDecimal(qtdPlanejadaItem, ".", ",", ".", "", true, casasQtd));
				});
				qtdBaseItem = null;
				qtdPlanejadaItem = null;
				qtdPlanejadaCabecalho = null;
			});
		}

		let __initProdutoEstrutura = function () {
			let __acaoProdutoEstrutura = function (val) {
				toggleLoading();
				let url = $(data_views).data("url_get_info_estrutura");
				let travaCampo = $(data_views).data("flag_trava_campos");
				ajaxRequest(true, url, null, 'text', {
					'produtoEstrutura': val,
				}, function (ret) {
					try{
						ret = JSON.parse(ret);
						$($($('#tabela-itens tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");
						if(!is_empty(ret['bol'], 1)) {
							$("#isEstruturaEspecial").val(ret['dados']['tipoEstruturaCabecalho']);
							if(ret['dados']['tipoEstruturaCabecalho'] === "p") {
								$("#tabela-itens .porcao-line").removeClass("ocultar");
								$("#tabela-itens .quantidade_porcao-line").removeClass("ocultar");
								$("#tabela-itens tbody tr .quantidadeBase").attr("readonly", true);
								$("#tabela-itens tbody tr .quantidadeBase").prop("readonly", true);
							} else {
								$("#tabela-itens .porcao-line").addClass("ocultar");
								$("#tabela-itens .quantidade_porcao-line").addClass("ocultar");
								$("#tabela-itens tbody tr .quantidadeBase").attr("readonly", false);
								$("#tabela-itens tbody tr .quantidadeBase").prop("readonly", false);
							}
							$('#xQuantidade').val(formataDecimal(ret['dados']['quantidadeBaseCabecalho'], ".", ",", ".", "", true, casasQtd));
							$('#quantidadePlanejadaCab').val(formataDecimal(ret['dados']['producaoMediaPlanejadaCabecalho'], ".", ",", ".", "", true, casasQtd));
							$(".select_depositoIdOrigem").val(ret['dados']['idDepositoCabecalho']).trigger('change');

							let lastTR = null;
							$.each(ret['dados']['itens'], function (indexItem, valoresItem) {
								$('#tabela-itens tfoot .add-itens-table-geral').trigger("click");
								lastTR = $($("#tabela-itens tbody tr").last());
								//Não é necessário chamar o initFields, uma vez que após adicionar o item automaticamente a função é chamada

								$($(lastTR).find(".item-permitir_alteracao")).val(0);
								$($(lastTR).find(".item-qtd_base")).val(formataDecimal(valoresItem['quantidadeBaseItem'], ".", ".", "", "", true, null));
								$($(lastTR).find(".quantidadeBase")).val(formataDecimal(valoresItem['quantidadeBaseItem'], ".", ",", ".", "", true, casasQtd));
								$($(lastTR).find(".select_produtoId")).append($('<option/>').attr('value', valoresItem['idProdutoItem']).text(valoresItem['nomeProdutoItem'])).val(valoresItem['idProdutoItem']).trigger('change');
								$($(lastTR).find(".select_produtoId")).addClass("readonly");
								$($(lastTR).find(".select_produtoId")).attr("readonly", true);
								$($(lastTR).find(".select_produtoId")).prop("readonly", true);
								// $($(lastTR).find(".select_depositoOrigem")).val(valoresItem['idDepositoItem']).trigger('change');
								$($(lastTR).find(".quantidadeDisponivel")).val(formataDecimal(valoresItem['emEstoqueItem'], ".", ",", ".", "", true, casasQtd));
								$($(lastTR).find(".tipoProduto")).val(valoresItem['tipoProduto']);
								$($(lastTR).find(".select_itinerario")).append($('<option/>').attr('value', valoresItem['idEtapaProducaoItem']).text(valoresItem['nomeEtapaProducaoItem'])).val(valoresItem['idEtapaProducaoItem']).trigger('change');
								$($(lastTR).find(".select_itinerario")).addClass("readonly");
								$($(lastTR).find(".select_itinerario")).attr("readonly", true);
								$($(lastTR).find(".select_itinerario")).prop("readonly", true);
								if(travaCampo == 1){
									$($(lastTR).find(".quantidadeBase")).addClass("readonly");
									$($(lastTR).find(".quantidadeBase")).attr("readonly", true);
									$($(lastTR).find(".quantidadeBase")).prop("readonly", true);
									$($(lastTR).find(".select_depositoOrigem")).addClass("readonly");
									$($(lastTR).find(".select_depositoOrigem")).attr("readonly", true);
									$($(lastTR).find(".select_depositoOrigem")).prop("readonly", true);
								}
							});
							lastTR = null;

							$('#quantidadePlanejadaCab').trigger(actionChangeValores);
						}
						toggleLoading();
					}catch(err){
						forceToggleLoading(0);
					}
				});
			};

			$(".select_ProdutoEstrutura").off("select2:unselect");
			$(".select_ProdutoEstrutura").on("select2:unselect", function () {
				__acaoProdutoEstrutura(null);
			});

			$(".select_ProdutoEstrutura").off("select2:select");
			$(".select_ProdutoEstrutura").on("select2:select", function () {
				__acaoProdutoEstrutura($(this).val());
			});
		};

		$("[data-picker='date-top-left-op']").datetimepicker({
			locale: _lang,
			format: 'DD/MM/YYYY',
			widgetPositioning: {
				vertical: 'top',
				horizontal: 'left'
			}
		});

		//Se tiver a flag, ele desabilita as datas posteriores, se n, continua normal
		if(!is_empty($('.data_views').data('desabilitar_data_posterior_data_pedido'), true)){
			$("#dataPedido").data('DateTimePicker').maxDate(new Date())
		}

		$(".select_ProdutoEstrutura").select2Ajax();
		$(".select_ProdutoEstrutura").data('init', '');
		$(".select_ProdutoEstrutura").attr('data-init', '');

		$(".select_observacoesId").select2Ajax();
		$(".select_observacoesId").data('init', '');
		$(".select_observacoesId").attr('data-init', '');

		$(".select_Tipo").select2Simple();
		$(".select_Status").select2Simple();
		$(".select_depositoIdOrigem").select2Simple();
		__initContadorCharsObservacao();
		__initAutomatizacaoDatas();
		__initCalculosCabecalho();
		__initProdutoEstrutura();
	};

	let __initFieldsLinhas = function (objLinha) {
		if(is_empty(objLinha, 1)) {
			objLinha = $("body");
		}
		let __initCalculosLinha = function () {
			$($("#tabela-itens").find(".quantidadeBase")).off(actionChangeValores);
			$($("#tabela-itens").find(".quantidadeBase")).on(actionChangeValores, function () {
				if($("#bol_controlar_calculos_auto").is(':checked')) {
					return;
				}

				let qtdBaseItemOld = $($($(this).parents("tr")).find(".item-qtd_base")).val() * 1;
				let qtdBaseItemNew = formataDecimal($($($(this).parents("tr")).find(".quantidadeBase")).val(), ",", ".", "", "", true, casasQtd) * 1;
				let qtdPlanejadaItemOld = $($($(this).parents("tr")).find(".item-ultima_qtd_planejada")).val() * 1;
				let qtdPlanejadaItemNew = (qtdBaseItemNew / qtdBaseItemOld) * qtdPlanejadaItemOld;

				$($($(this).parents("tr")).find(".quantidadePlanejada")).val(formataDecimal(qtdPlanejadaItemNew, ".", ",", ".", "", true, casasQtd));

				qtdBaseItemNew = null;
				qtdBaseItemOld = null;
				qtdPlanejadaItemOld = null;
				qtdPlanejadaItemNew = null;
			});
			$($("#tabela-itens").find(".quantidadePlanejada")).off(actionChangeValores);
			$($("#tabela-itens").find(".quantidadePlanejada")).on(actionChangeValores, function () {
				let qtdPlanejadaItem = formataDecimal($(this).val(), ",", ".", "", "", true, casasQtd) * 1;
				$($($(this).parents("tr")).find(".item-ultima_qtd_planejada")).val(qtdPlanejadaItem);
				if($("#bol_controlar_calculos_auto").is(':checked')) {
					return;
				}

				let qtdPlanejadaCabecalho = formataDecimal($("#quantidadePlanejadaCab").val(), ",", ".", "", "", true, casasQtd) * 1;
				let qtdBaseItem = qtdPlanejadaItem / qtdPlanejadaCabecalho;

				$($($(this).parents("tr")).find(".quantidadeBase")).val(formataDecimal(qtdBaseItem, ".", ",", ".", "", true, casasQtd));

				qtdBaseItem = null;
				qtdPlanejadaItem = null;
				qtdPlanejadaCabecalho = null;
			});
		}

		let __initProdutoDeposito = function () {
			let __acaoProdutoDeposito = function (valDeposito, objTR, valProdutos) {
				toggleLoading();
				//TRATATIVA PRIMEIRO LOAD DE PRODUTO COM DEPOSITO VAZIO.
				if(valDeposito == null) valDeposito = $('.select_depositoIdOrigem').val();

				let url = $(data_views).data("url_get_info_deposito");
				ajaxRequest(true, url, null, 'text', {
					'idsProdutos': valProdutos,
					'idDeposito': valDeposito,
				}, function (ret) {
					try{
						ret = JSON.parse(ret);
						$(objTR).each(function (indexTR, thisTR) {
							$($(thisTR).find(".select_depositoOrigem")).val(valDeposito).trigger('change');
							$($(thisTR).find(".quantidadeDisponivel")).val("");
							if(!is_empty(ret['bol'], 1)) {
								$($(thisTR).find(".quantidadeDisponivel")).val(
									formataDecimal(
										ret['dados'][indexTR]['emEstoque'],
										".",
										",",
										".",
										"",
										true,
										casasQtd
									)
								);
							}
						});
						toggleLoading();
					}catch(err){
						forceToggleLoading(0);
					}
				});
			};

			$(".select_produtoId").off("select2:unselect");
			$(".select_produtoId").on("select2:unselect", function () {
				__acaoProdutoDeposito($($($(this).parents("tr")).find(".select_depositoOrigem")).val(), $($(this).parents("tr")), null);
			});

			$(".select_produtoId").off("select2:select");
			$(".select_produtoId").on("select2:select", function () {
				__acaoProdutoDeposito($($($(this).parents("tr")).find(".select_depositoOrigem")).val(), $($(this).parents("tr")), $(this).val());
			});

			$(".select_depositoOrigem").off("select2:unselect");
			$(".select_depositoOrigem").on("select2:unselect", function () {
				__acaoProdutoDeposito(null, $($(this).parents("tr")), $($($(this).parents("tr")).find(".select_produtoId")).val());
			});

			$(".select_depositoOrigem").off("select2:select");
			$(".select_depositoOrigem").on("select2:select", function () {
				__acaoProdutoDeposito($(this).val(), $($(this).parents("tr")), $($($(this).parents("tr")).find(".select_produtoId")).val());
			});

			$(".select_depositoIdOrigem").off("select2:unselect");
			$(".select_depositoIdOrigem").on("select2:unselect", function () {
				let objTRs = $($('#tabela-itens tbody tr').not(':first'));
				let valProdutos = [];
				$($(objTRs).find(".select_produtoId")).each(function (indexTR, thisTR) {
					valProdutos.push($(this).val());
				});
				__acaoProdutoDeposito(null, objTRs, valProdutos);
			});

			$(".select_depositoIdOrigem").off("select2:select");
			$(".select_depositoIdOrigem").on("select2:select", function () {
				var depositoIdOrigem = $('.select_depositoIdOrigem').val();
				var produtoEstrutura = $('#produtoEstrutura').val();

				if(produtoEstrutura != null){
					if (window.confirm(l["alterarDepositoOrdemProducao"])) {
						let objTRs = $($('#tabela-itens tbody tr').not(':first'));
						let valProdutos = [];
						$($(objTRs).find(".select_produtoId")).each(function (indexTR, thisTR) {
							valProdutos.push($(this).val());
						});

						__acaoProdutoDeposito($(this).val(), objTRs, valProdutos);
					}
				}
			});
		};

		$($(objLinha).find(".select_produtoId")).select2Ajax();
		$($(objLinha).find(".select_produtoId")).data('init', '');
		$($(objLinha).find(".select_produtoId")).attr('data-init', '');

		$($(objLinha).find(".select_itinerario")).select2Ajax();
		$($(objLinha).find(".select_itinerario")).data('init', '');
		$($(objLinha).find(".select_itinerario")).attr('data-init', '');

		$($(objLinha).find(".select_depositoOrigem")).select2Simple();


		if($(data_views).data("flag_trava_campos")){
			$($(objLinha).find(".select_depositoOrigem")).addClass('readonly');

			$($(objLinha).find(".quantidadeBase")).attr('readonly', true);
			$($(objLinha).find('input.quantidadeBase').val('1,0000'));
		}
		__initCalculosLinha();
		__initProdutoDeposito();
	};

	switch (tipo) {
		case "all":
			__initFieldsCabecalho();
			__initFieldsLinhas(objLinha);
			break;
		case "cabecalho":
			__initFieldsCabecalho();
			break;
		case "linhas":
		case "linha":
			__initFieldsLinhas(objLinha);
			break;
	}
}

initFields();
controlaTabelaSuite({
	"ref": "#tabela-itens",
	"funAposAddItem": function () {
		initFields("linha", $($("#tabela-itens tbody tr").last()));
	},
	"funAposRemoverItem": function () {
	}
});