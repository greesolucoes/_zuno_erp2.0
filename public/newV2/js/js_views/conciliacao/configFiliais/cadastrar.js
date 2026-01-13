/**
 * Desocultar e ocultar campos
 */
function desocultarCampos(){
	if (!$('#bol_permitir_taxa_valor').is(':checked')) {
		$(".config_cartoes_valor").addClass("ocultar");
	} else {
		$(".config_cartoes_valor").removeClass("ocultar");
	}

	$('#bol_permitir_taxa_valor').off('change');
	$('#bol_permitir_taxa_valor').on('change', function (e) {
		if (!this.checked) {
			$(".config_cartoes_valor").addClass("ocultar");
		} else {
			$(".config_cartoes_valor").removeClass("ocultar");
		}
	});

	$('#bol_conciliar_dia_automatico_pos_integracao').off('change');
	$('#bol_conciliar_dia_automatico_pos_integracao').on('change', function (e) {
		if (!this.checked) {
			$(".flag-trava-valores-conciliacao").removeClass("ocultar");
		} else {
			$(".campo-valor-trava-conciliacao").addClass("ocultar");
			$(".flag-trava-valores-conciliacao").addClass("ocultar");
			$('input#bol_habilitar_trava_valor_conciliacao').prop('checked', false);
			$('label[for="bol_habilitar_trava_valor_conciliacao"] i.fa.fa-check-square.icone-check')
				.removeClass('fa-check-square')
				.addClass('fa-square-o');
		}
	});

	$('#bol_habilitar_trava_valor_conciliacao').off('change');
	$('#bol_habilitar_trava_valor_conciliacao').on('change', function (e) {
		if (!this.checked) {
			$(".campo-valor-trava-conciliacao").addClass("ocultar");
		} else {
			$(".campo-valor-trava-conciliacao").removeClass("ocultar");
		}
	});

	$('#bol_habilitar_devolucao_pix_contas_receber').off('change');
	$('#bol_habilitar_devolucao_pix_contas_receber').on('change', function (e) {
		if (!this.checked) {
			$(".reducao_forma_pagamento_pix").addClass("ocultar");
			$("#pix-config-forma_pagamento").val("");
		} else {
			$(".reducao_forma_pagamento_pix").removeClass("ocultar");
		}
	});
}

/**
 * Function criaSelectsConcFiliaisConfig
 * Cria o Select2 de todos os selects da página e altera o data-init deles para nada
 * (pode ocasionar problemas se não for feito isso após a 1 chamada do select 2)
 */
function criaSelectsConcFiliaisConfig() {
	$(".cifrao_is_prefixo-config_geral").select2({
		language: _lang,
		allowClear: false
	});
	$(".separador_decimal-config_geral").select2({
		language: _lang,
		allowClear: false
	});
	$(".separador_milhar-config_geral").select2({
		placeholder: $('.data_config').data('selecione_lang'),
		language: _lang,
		allowClear: true
	});
	$(".formato_date_time-config_geral").select2({
		language: _lang,
		allowClear: false
	});

	// pegar essas duas linhas para fazer o select2 via ajax
	$('.select_filial-config-geral').select2Ajax();
	$('.select_filial-config-geral').data('init', '');

	$('.select_origem-config-geral').select2Ajax();
	$('.select_origem-config-geral').data('init', '');

	// ao alterar o select, se for do tipo esperado, ativa os campos relacionados
	$('#origem-config-geral').change(function() {
		const idOrigensConc = $(this).val();

		// post na rota para retorno do tipo de origem da conciliação
		$.post(
			$('.data_views').data('url_verificar_origem_conciliacao'),
			{
				idOrigensConc: idOrigensConc,
				...tokenCsrf
			},
			function(retorno){
				retorno = JSON.parse(retorno);

				// itens para exibir/não exibir
				const auxBol = $('.bol_importar_itens_consumo_interno_manycore_aux');

				// botão de ativação da funcionalidade
				const btnBol = $('#label_bol_importar_itens_consumo_interno_manycore');

				// se o identificador for do tipo XML ManyCore, exibe os itens relacionados
				if (retorno.identificador === 'xmm') {
					auxBol.each(function() {
						$(this).removeClass('ocultar');
					})

				// senão, esconde os itens relacionados
				} else {
					auxBol.each(function() {
						$(this).addClass('ocultar');
						$('#centro-custo-padrao-consumo-interno').val('');
					})

					// desliga o botão de ativação de funcionalidade se estiver ativo
					if (btnBol.hasClass('active')) {
						btnBol.trigger('click');
					}
				}
			}
		);

	// dispara a seleção. Funcional em casos de retorno
	}).trigger('change');
}

function controlaCheckIsImpedirEnvioSAPCS() {
	$("input.enviar_sap-config_deParaCSCC").off("change");
	$("input.enviar_sap-config_deParaCSCC").on("change", function () {
		$($(this).parents("tr").find(".enviar_sap-config_deParaCSCC-hidden")).val(this.checked ? "1" : "0");
	});
}
function controlaCheckUtilizarDepositoPadrao() {
	$("input.bol_ignorar_domicilio_bancario-config_deParaCSCC").off("change");
	$("input.bol_ignorar_domicilio_bancario-config_deParaCSCC").on("change", function () {
		$($(this).parents("tr").find(".bol_ignorar_domicilio_bancario-config_deParaCSCC-hidden")).val(this.checked ? "1" : "0");
	});
}

/**
 * Function controlaTabelaConfigCartoes
 * Controla a tabela de configurações de cartões e suas funções
 */
function controlaTabelaConfigCartoes() {
	$(".id_cartao-config_cartoes").select2Ajax();
	$(".id_cartao-config_cartoes").data('init', '');

	$('table#tabela-config-cartoes button.remove-itens-config_cartoes').off('click');
	$('table#tabela-config-cartoes button.remove-itens-config_cartoes').on("click", function () {
		var rem = $(this).parents('tr');

		rem.fadeOut(270, function () {
			rem.remove();
		});
	});

	$('table#tabela-config-cartoes button#add-itens-config_cartoes').off("click");
	$('table#tabela-config-cartoes button#add-itens-config_cartoes').on("click", function () {
		var tbody = $('table#tabela-config-cartoes tbody');
		var modelo = $(tbody).find('tr').first().html();

		$(tbody).append('<tr>' + modelo + '</tr>');

		allFunctions();
		$('table#tabela-config-cartoes tbody tr .select').select2Reset();
		controlaTabelaConfigCartoes();
	});

}

/**
 * Function exibeCodigoEstoque
 * Define se exibirá o input para o código do estoque
 */
function exibeCodigoEstoque() {
	$('input#importador-bol_nao_controlar_estoque').unbind('change');
	$('input#importador-bol_nao_controlar_estoque').on("change", function () {
		if (this.checked) {
			$('#importador-nao_controlar_estoque-form').removeClass('ocultar');
			toggleStatusFlag('importador-definir_deposito_para_conciliacao', 'inactive', true);
		} else {
			$('#importador-nao_controlar_estoque-form').addClass('ocultar');
			$('input#importador-definir_deposito_para_conciliacao').prop('disabled', false);
		}

		if (this.checked) {
			// $('#div_gerenciar_turno_unico').addClass('ocultar');
			$('input#bol_controlar_deposito_por_grupo').prop('checked', false);
			$('label#bol_controlar_deposito_por_grupo').removeClass('active');
			$('label#bol_controlar_deposito_por_grupo i.fa.fa-check-square.icone-check')
			.removeClass('fa-check-square')
			.addClass('fa-square-o');
			$('input#bol_controlar_deposito_por_grupo').prop('disabled', true);
		} else {
			// $('#div_gerenciar_turno_unico').removeClass('ocultar');
			$('input#bol_controlar_deposito_por_grupo').prop('disabled', false);
		}

		if (this.checked) {
			// $('#div_gerenciar_turno_unico').addClass('ocultar');
			$('input#bol_controlar_deposito_por_deposito_padrao_item').prop('checked', false);
			$('label#bol_controlar_deposito_por_deposito_padrao_item').removeClass('active');
			$('label#bol_controlar_deposito_por_deposito_padrao_item i.fa.fa-check-square.icone-check')
			.removeClass('fa-check-square')
			.addClass('fa-square-o');
			$('input#bol_controlar_deposito_por_deposito_padrao_item').prop('disabled', true);
		} else {
			// $('#div_gerenciar_turno_unico').removeClass('ocultar');
			$('input#bol_controlar_deposito_por_deposito_padrao_item').prop('disabled', false);
		}
	});

	$('input#importador-bol_nao_controlar_estoque').trigger('change');
}

function handleFlagDefinirDepositoConciliacao(){
	$('input#importador-definir_deposito_para_conciliacao').unbind('change');
	$('input#importador-definir_deposito_para_conciliacao').on("change", function () {
		let divDeposito = $('#importador-definir_deposito_para_conciliacao-form');
		if(this.checked){
			toggleStatusFlag('importador-bol_nao_controlar_estoque', 'inactive', true);
			divDeposito.removeClass('ocultar');
		}else{
			divDeposito.addClass('ocultar');
			$('input#importador-bol_nao_controlar_estoque').prop('disabled', false);
		}
	});
}
/**
 * Function exibeControleTerminalCS
 * Define se exibirá a tabela de controle de terminal por CS
 */
function exibeControleTerminalCS() {
	$('input#bol_habilitar_verificacao_terminal_cs').off('change');
	$('input#bol_habilitar_verificacao_terminal_cs').on("change", function () {
		if (this.checked) {
			$('#importador-verificacao_terminal_cs-form').removeClass('ocultar');
			$('.importador-verificacao_terminal_cs-form').removeClass('ocultar');
		} else {
			$('#importador-verificacao_terminal_cs-form').addClass('ocultar');
			$('.importador-verificacao_terminal_cs-form').addClass('ocultar');
		}
	});

	$('input#bol_habilitar_verificacao_terminal_cs').trigger('change');
}

/**
 * Function exibeOrdensPedidoImportacaoGorjeta
 * Define se exibirá a aba de configuração de ordens de pedido para a importação de gorjetas
 */
function exibeOrdensPedidoImportacaoGorjeta() {
	$('input#bol_importar_gorjeta_por_ordem_pedido').off('change');
	$('input#bol_importar_gorjeta_por_ordem_pedido').on("change", function () {
		if (this.checked) {
			$('.habilita_ordem_pedido').removeClass('ocultar');
		} else {
			$('.habilita_ordem_pedido').addClass('ocultar');
		}
	});

	$('input#bol_importar_gorjeta_por_ordem_pedido').trigger('change');
}

// /**
//  * Function habilitaAgrupamentoTurno
//  * Habilita para marcar opções dependentes
//  */
// function habilitaOpcoesConfigFiliais(){
//     $('input#soma_modificadores').unbind('change');
//     $('input#soma_modificadores').on("change", function() {
//         if(this.checked) {
//             $('input#agrupar_movimento_por_item').prop('checked', true);
//             if(!$('label#label_agrupar_movimento_por_item').hasClass('active')) $('label#label_agrupar_movimento_por_item').addClass('active');
//
//             $('label#label_agrupar_movimento_por_item i.fa.fa-square-o.icone-check')
//                 .removeClass('fa-square-o')
//                 .addClass('fa-check-square');
//             $('input#agrupar_movimento_por_item').prop('disabled', true);
//         } else {
//             $('input#agrupar_movimento_por_item').prop('checked', false);
//             $('label#label_agrupar_movimento_por_item').removeClass('active');
//
//             $('label#label_agrupar_movimento_por_item i.fa.fa-check-square.icone-check')
//                 .removeClass('fa-check-square')
//                 .addClass('fa-square-o');
//             $('input#agrupar_movimento_por_item').prop('disabled', false);
//         }
//     });
//
//     $('input#soma_modificadores').trigger('change');
// }

/**
 * Function populaCamposHiddenDuplicacao.
 * Irá popular os campos hidden de acordo com a tabela do modal (No post, não envia campos do modal)
 */
function populaCamposHiddenDuplicacao() {
	var tabelaModal = "#modal-duplicar_dados_filial table#table-modal_filiais_duplicacao tbody tr";
	var campoVariaveis = ".configs_hidden";

	$(campoVariaveis).html("");
	$(tabelaModal).each(function () {
		$(campoVariaveis).html($(campoVariaveis).html() + '<input type="hidden" name="filial-duplicacao_dados[]" value="' + $(this).find(".filial-duplicacao_dados").val() + '" />');
		$(campoVariaveis).html($(campoVariaveis).html() + '<input type="hidden" name="origem-duplicacao_dados[]" value="' + $(this).find(".origem-duplicacao_dados").val() + '" />');
		$(campoVariaveis).html($(campoVariaveis).html() + '<input type="hidden" name="duplicar-duplicacao_dados[]" value="' + $(this).find(".duplicar-duplicacao_dados").val() + '" />');
	});
}

/**
 * Function importarDiasManuaisEModal.
 * Realiza ações para importação de dias manuais.
 */
function controlaModalDuplicacaoDados() {
	var btnAbreModal = "button.duplicar_dados-btn";
	var modalRef = "#modal-duplicar_dados_filial";
	var btnsDuplicar = modalRef + " input[type='checkbox'].duplicar-duplicacao_dados";


	$(btnAbreModal).off('click');
	$(btnAbreModal).on("click", function (e) {
		e.preventDefault();

		$(modalRef).modal('show');
	});

	$(modalRef).unbind('hidden.bs.modal');
	$(modalRef).on('hidden.bs.modal', function () {
		populaCamposHiddenDuplicacao();
	});

	$('.filial-duplicacao_dados').select2Ajax();
	$('.filial-duplicacao_dados').data('init', '');
	$('.origem-duplicacao_dados').select2Ajax();
	$('.origem-duplicacao_dados').data('init', '');

	$(btnsDuplicar).off('change');
	$(btnsDuplicar).on('change', function (e) {
		if ($(this).prop('checked')) {
			$(this).parents('label.form-check-label').removeClass('btn-danger');
			$(this).parents('label.form-check-label').addClass('btn-success');
			$(this).parents('label.form-check-label').find('i').remove();
			$(this).parents('label.form-check-label').append('<i class="fa fa-check" aria-hidden="true"></i>');
			$(this).val('1');
		} else {
			$(this).parents('label.form-check-label').removeClass('btn-success');
			$(this).parents('label.form-check-label').addClass('btn-danger');
			$(this).parents('label.form-check-label').find('i').remove();
			$(this).parents('label.form-check-label').append('<i class="fa fa-times" aria-hidden="true"></i>');
			$(this).val('0');
		}
	});
}

// /**
//  * Function exibeCodigoEstoque
//  * Define se exibirá o input para o código do estoque
//  **/
// function defineImportacaoManualOuExcel() {
//     $('input[name="config-cartoes-type"]').unbind('change');
//     $('input[name="config-cartoes-type"]').on("change", function() {
//         if (this.value === 'manual') {
//             if(!$('.config-cartoes-importacao-excel').hasClass('ocultar')) $('.config-cartoes-importacao-excel').addClass('ocultar');
//             $('.config-cartoes-importacao-manual').removeClass('ocultar');;
//         } else if (this.value === 'excel') {
//             if(!$('.config-cartoes-importacao-manual').hasClass('ocultar')) $('.config-cartoes-importacao-manual').addClass('ocultar');
//             $('.config-cartoes-importacao-excel').removeClass('ocultar')
//         }
//     });
//
//     $('input[name="config-cartoes-type"][checked="checked"]').trigger('change');
// }

function ocultarCnpjCpf() {
	if (!$('#bol_validar_cnpj_cpf_na_conciliacao').is(':checked')) {
		$(".cnpj_cpf-ocultar").addClass("ocultar");
	} else {
		$(".cnpj_cpf-ocultar").removeClass("ocultar");
	}

	$('#bol_validar_cnpj_cpf_na_conciliacao').off('change');
	$('#bol_validar_cnpj_cpf_na_conciliacao').on('change', function (e) {
		if (!this.checked) {
			$(".cnpj_cpf-ocultar").addClass("ocultar");
		} else {
			$(".cnpj_cpf-ocultar").removeClass("ocultar");
		}
	})
}

function ocultarDataCorteTravaConciliacoes() {
	if (!$('#bol_bloquear_conciliacao_quando_houver_pendentes').is(':checked')) {
		$(".data_corte_trava-ocultar").addClass("ocultar");
	} else {
		$(".data_corte_trava-ocultar").removeClass("ocultar");
	}

	$('#bol_bloquear_conciliacao_quando_houver_pendentes').off('change');
	$('#bol_bloquear_conciliacao_quando_houver_pendentes').on('change', function (e) {
		if (!this.checked) {
			$(".data_corte_trava-ocultar").addClass("ocultar");
		} else {
			$(".data_corte_trava-ocultar").removeClass("ocultar");
		}
	})
}

function replicarAbasEntreFiliais() {
	let btnAbreModal = "button.duplicar-aba-config";
	let modalRef = "#modal-duplicar_abas_filiais";
	let selectFiliaisRef = modalRef + " table tbody tr:not(.ocultar) td select.select_duplicar_filial";
	let btnSalvar = modalRef + " button#salvar-duplicar_abas_entre_filiais";
	let url = $(".data_views").data('url_duplicar_abas');
	let tipoRequest = "";
	let dados = [];
	let filiaisDuplicar = [];

	$(btnAbreModal).off('click');
	$(btnAbreModal).on("click", function (e) {
		e.preventDefault();

		tipoRequest = $(this).data('tipo_requisicao');
		$(modalRef).modal('show');
	});

	$(modalRef).unbind('hidden.bs.modal');
	$(modalRef).on('hidden.bs.modal', function () {
		tipoRequest = "";
		dados = [];
		selectFiliaisRef = [];
		$($(selectFiliaisRef).parents('tr')).remove();
	});

	$(selectFiliaisRef).select2Ajax();
	$(selectFiliaisRef).data('init', '');

	$(btnSalvar).off("click");
	$(btnSalvar).on("click", function () {
		if (!is_empty(url, 1)) {
			swal({
				title: l["desejaContinuar?"],
				text: "",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085D6',
				cancelButtonColor: '#DD3333',
				confirmButtonText: l["sim!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();

				$(selectFiliaisRef).each(function () {
					filiaisDuplicar.push($(this).val());
				});

				dados = [];
				switch (tipoRequest) {
					case "config_outside_tabs":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"contaCaixa": $("#importador-conta-caixa").val(),
							"contaCaixaDiff": $("#importador-conta-caixa-diff").val(),
							"seqNf": $("#importador-seq-nf").val(),
							"horaIntegrarDiaAutomatico": $("#horaIntegrarDiaAutomatico").val(),
							"codClientePadrao": $("#importador-cod-cliente-padrao").val(),
							"codClientePadraoPedidosDevolucao": $("#importador-cod-cliente-padrao-pedidos-devolucao").val(),
							"contaContabilFilial": $("#importador-conta-contabil-filial").val(),
							"contaContabilAdiantamento": $("#conta_contabil_adiantamento-config_geral").val(),
							"contaTaxaAntecipacao": $("#conta_taxa_antecipacao-config_geral").val(),
							"contaContabilContaAReceberCSIndependente": $("#conta_contabil_conta_areceber_cs_independente-config_geral").val(),
							"bolAgruparMovPorItem": document.getElementById("agrupar_movimento_por_item").checked ? 1 : 0,
							"bolControlarDepositoPorSetor": document.getElementById("controlar_deposito_por_setor").checked ? 1 : 0,
							"bolHabilitarBaixaDeLotesFIFO": document.getElementById("bol_habilitar_baixa_de_lotes_fifo").checked ? 1 : 0,
							"bolHabilitarVerificacaoTerminalCS": document.getElementById("bol_habilitar_verificacao_terminal_cs").checked ? 1 : 0,
							"bolPermitirEstoqueNegativo": document.getElementById("bol_permitir_estoque_negativo").checked ? 1 : 0,
							"ativarImportacaoPedidosDevolucao": document.getElementById("ativar_importacao_pedidos_devolucao").checked ? 1 : 0,
							"bolPermitirItemSemCusto": document.getElementById("bol_permitir_item_sem_custo").checked ? 1 : 0,
							"bolImportarMod": document.getElementById("importar_modificadores").checked ? 1 : 0,
							"bolSomaMod": document.getElementById("soma_modificadores").checked ? 1 : 0,
							"isNaoControlarDeposito": document.getElementById("importador-bol_nao_controlar_estoque").checked ? 1 : 0,
							"codigoDeposito": $("#importador-condigo_estoque").val(),
							"codigoSerie": $("#pdv-parametrizacao_id_serie").val(),
							"porcentagemDivergenciaAceitaImportacao": $("#porcentagem_divergencia_valores-config_importacao").val(),
							"codigoGenericoPnValidacaoCnpjCpf": $("#codigo_generico_pn_validacao_cnpj_cpf").val(),
							"bolControlarDepositoPorGrupo": document.getElementById("bol_controlar_deposito_por_grupo").checked ? 1 : 0,
							"bolInativarCardService": document.getElementById("bol_inativar_card_service").checked ? 1 : 0,
							"bolControlarDepositoPorDepositoPadraoItem": document.getElementById("bol_controlar_deposito_por_deposito_padrao_item").checked ? 1 : 0,
							"bolUtilizarCFOPCodePorTaxCode": document.getElementById("bol_utilizar_cfop_code_por_tax_code").checked ? 1 : 0,
							"bolConsiderarDescontoAvaria": document.getElementById("bol_considerar_desconto_avaria").checked ? 1 : 0,
							"bolConciliarDiaAutomaticoPosIntegracao": document.getElementById("bol_conciliar_dia_automatico_pos_integracao").checked ? 1 : 0,
							// "bolNaoPermitirPrecoZerado": document.getElementById("bol_nao_permitir_preco_zerado").checked ? 1 : 0,
							"bolUtilizarMultiplicadorUnidadeVendaItem": document.getElementById("bol_utilizar_multiplicador_unidade_venda_item").checked ? 1 : 0,
							"bolSomarSaldoAnteriorNaoIntegradosSap": document.getElementById("bol_somar_saldo_anterior_nao_integrados_sap").checked ? 1 : 0,
							"bolSomarSaldoAnteriorErroSap": document.getElementById("bol_somar_saldo_anterior_erro_sap").checked ? 1 : 0,
							"bolInserirDescontoComoFormaPagamento": document.getElementById("bol_inserir_desconto_como_forma_pagamento").checked ? 1 : 0,
							"bolCalcularDescontoPeloErp": document.getElementById("bol_calcular_desconto_pelo_erp").checked ? 1 : 0,
							"bolValidarCnpjCpfConciliacao": document.getElementById("bol_validar_cnpj_cpf_na_conciliacao").checked ? 1 : 0,
							"bolImportarCloudImportacao": document.getElementById("importar_cloud-config_importacao").checked ? 1 : 0,
							"bolImportacaoPorFechamento": document.getElementById("bol_importacao_por_fechamento-config_importacao").checked ? 1 : 0,
							"bolImportarGorjetaPorOrdemPedido": document.getElementById("bol_importar_gorjeta_por_ordem_pedido").checked ? 1 : 0,
							"bolBloquearConciliacaoQuandoHouverPendencias": document.getElementById("bol_bloquear_conciliacao_quando_houver_pendentes").checked ? 1 : 0,
							"dataCorteBloquearConciliacoes": $("#data_corte_bloquear_conciliacoes").val(),
						};
						break;
					case "geral_conciliacao":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"contaUtilizacao": $("#conta-utilizacao").val(),
							"contaCodCfop": $("#conta-cod-cfop").val(),
							"saldoInicial": $("#saldo-config-geral").val(),
							"bolImportarItensConsumoInternoManycore": $("#bolImportarItensConsumoInternoManycore").val(),
							"centroCustoPadraoConsumoInterno": $("#centro-custo-padrao-consumo-interno").val(),
						};
						break;
					case "metricas_conciliacao":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"casasPreco": $("#casas_preco").val(),
							"casasQuantidade": $("#casas_quantidades").val(),
							"cifrao": $("#cifrao-config_geral").val(),
							"cifraoIsPrefixo": $("#cifrao_is_prefixo-config_geral").val(),
							"separadorDecimal": $("#separador_decimal-config_geral").val(),
							"separadorMilhar": $("#separador_milhar-config_geral").val(),
							"formatoDateTime": $("#formato_date_time-config_geral").val(),
						};
						break;
					case "formas_pagamento_conciliacao":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"formaPagamentoCaixa": $("#caixa-config-forma_pagamento").val(),
							"formaPagamentoConvite": $("#convite-config-forma_pagamento").val(),
							"formaPagamentoPermuta": $("#permuta-config-forma_pagamento").val(),
							"formaPagamentoCheque": $("#cheque-config-forma_pagamento").val(),
							"formaPagamentoAReceber": $("#contas-receber-config-forma_pagamento").val(),
						};
						break;
					case "pedidos_devolucao_conciliacao":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"utilizacaoPedidosDevolucao": $("#utilizacao-config_pedidos_devolucao").val(),
							"modeloPedidosDevolucao": $("#modelo_fiscal-config_pedidos_devolucao").val(),
							"observacaoPedidosDevolucao": $("#observacao_padrao-config_pedidos_devolucao").val(),
						};
						break;
					case "pedidos_devolucao_como_nota_fiscal_saida":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"bolIntegrarEsbocoDevolucaoNotaFiscalSaida": $("#bol_nf_como_esboco-config_devolucao_nota_fiscal_saida").val(),
							"bolIntegrarDevolucaoComDataDoSistema": $("#bol_devolucao_com_data_sistema-config_devolucao_nota_fiscal_saida").val(),
							"utilizacaoDevolucaoNotaFiscalSaida": 		 $("#utilizacao-config_devolucao_nota_fiscal_saida").val(),
							"sequenciaNFDevolucaoNotaFiscalSaida": 		 $("#sequencia-nf-config_devolucao_nota_fiscal_saida").val(),
						};
						break;
					case "config_determinacao_conta_razao":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"bolHabilitarAlteracaoDeterminacaoContaRazao": ($("#bolHabilitarAlteracaoDeterminacaoContaRazao").is(':checked')) ? 1 : 0,
							"contaDeterminacaoContaRazao": $("#contaDeterminacaoContaRazao").val(),
						};
						break;
					case "cartoes_conciliacao":
						$("table#tabela-config-cartoes tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"formaDePagamento": $($(this).find(".codigo_forma_pagamento-config_cartoes")).val(),
									"idConcCartoes": $($(this).find(".id_cartao-config_cartoes")).val(),
									"taxa": $($(this).find(".taxa-config_cartoes")).val(),
									"taxaValor": $($(this).find(".taxa-config_cartoes_valor")).val(),
									"quantidadeDiasRecebimento": $($(this).find(".quantidade_dias_recebimento-config_cartoes")).val(),
								}
							);
						});
						break;
					case "ordens_pedido_permitir_gorjetas":
						$("table#tabela-config-ordens_pedido_permit_gorjeta tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"idOrigemOrdemPedido": $($(this).find(".codigo_ordem-config_ordens_pedido_permit_gorjeta")).val(),
								}
							);
						});
						break;
					case "cartoes_cardservices":
						$("table#tabela-config-config_deParaCSCC tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"cartaoConciliadora": $($(this).find(".codigo_forma_pagamento-config_deParaCSCC")).val(),
									"codigoAdquirente": $($(this).find(".codigo_adquirente-config_deParaCSCC")).val(),
									"cartaoSap": $($(this).find(".id_cartao-config_deParaCSCC")).val(),
									"contaAjuste": !is_empty($(this).find(".conta_ajuste-config_deParaCSCC"), 1) ? $($(this).find(".conta_ajuste-config_deParaCSCC")).val() : null,
									"contaPadrao": !is_empty($(this).find(".conta_deposito-config_deParaCSCC"), 1) ? $($(this).find(".conta_deposito-config_deParaCSCC")).val() : null,
									"contaTaxaAntecipacao": !is_empty($(this).find(".conta_taxa_antecipacao-config_deParaCSCC"), 1) ? $($(this).find(".conta_taxa_antecipacao-config_deParaCSCC")).val() : null,
									"qtdDiasRecebimento": $($(this).find(".quantidade_dias_recebimento-config_deParaCSCC")).val(),
									"bolImpedirEnvioSap": !is_empty($(this).find(".enviar_sap-config_deParaCSCC-hidden"), 1) ? $($(this).find(".enviar_sap-config_deParaCSCC-hidden")).val() : 0,
									"bolIgnorarDomicilioBancario": !is_empty($(this).find(".bol_ignorar_domicilio_bancario-config_deParaCSCC-hidden"), 1) ? $($(this).find(".bol_ignorar_domicilio_bancario-config_deParaCSCC-hidden")).val() : 0,
								}
							);
						});
						break;
					case "ajustes_cardservices":
						$("table#tabela-config-config_deParaCA tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"codigoAjuste": $($(this).find(".codigo_ajuste-config_deParaCA")).val(),
									"contaAjuste": $($(this).find(".conta_ajuste-config_deParaCA")).val(),
								}
							);
						});
						break;
					case "modificadores_exclusao":
						$("table#tabela-config-modificadores_exclusao tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"codigoItemFrenteCaixa": $($(this).find(".modificadores_exclusao-produto_frente_caixa")).val(),
									"codigoItemSAP": $($(this).find(".modificadores_exclusao-produto_sap")).val(),
								}
							);
						});
						break;
					case "emails_erro":
						$("table#tabela-config-emails_resposta_erro tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"email": $($(this).find(".emails_resposta-email")).val(),
								}
							);
						});
						break;
					case "emails_conciliacao_aberta":
						$("table#tabela-config-emails_conciliacao_aberta tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 					0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"email": 						$($(this).find(".emails_conciliacao_aberta-email")).val(),
									"quantDias":					$($(this).find(".emails_conciliacao_aberta-dias")).val(),
									"bolAlertaConciliacaoAberta":	document.getElementById("bol_alerta_conciliacao_aberta").checked ? 1 : 0,
								}
							);
						});
						break;
					case "movimentos_caixa":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"contaDebitoMovCaixaSupri": $("#mov_caixa_supri-conta_debito").val(),
							"contaCreditoMovCaixaSupri": $("#mov_caixa_supri-conta_credito").val(),
							"contaDebitoMovCaixaSaida": $("#mov_caixa_saida-conta_debito").val(),
							"contaCreditoMovCaixaSaida": $("#mov_caixa_saida-conta_credito").val(),
						};
						break;
					case "configs_ws_conciliacao":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"url": $("#url_acesso-config_ws").val(),
							"url": $("#url_acesso_force_ssl-config_ws").val(),
							"urlAux": $("#url_aux_acesso-config_ws").val(),
							"idRede": $("#id_rede-config_ws").val(),
							"token": $("#token_cliente-config_ws").val(),
							"usuario": $("#usuario-config_ws").val(),
							"senha": $("#senha-config_ws").val(),
						};
						break;
					case "configs_ws_cardservice":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"urlCardService": !is_empty($("#url_acesso-config_cardservice_ws"), 1) ? $("#url_acesso-config_cardservice_ws").val() : null,
							"diasParaBusca": $("#diasParaBusca-config_cardservice_ws").val(),
							"authorizationCardService": $("#token_cliente-config_cardservice_ws").val(),
							"senhaCardService": !is_empty($("#senha-config_cardservice_ws"), 1) ? $("#senha-config_cardservice_ws").val() : null,
						};
						break;
					case "config_terminal_centrorenda":
						$("table#tabela-config_contas_contabeis_lcm_centro_renda_terminal tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"idTerminalOrigem": $($(this).find(".cr_descontos-id_terminal_origem")).val(),
									"contaContabilReceita": $($(this).find(".cr_descontos-conta_contabil_receita")).val(),
									"contaContabilDesconto": $($(this).find(".cr_descontos-conta_contabil_desconto")).val(),
									"contaContabilCancelamento": $($(this).find(".cr_descontos-conta_contabil_cancelamento")).val(),
								}
							);
						});
						break;
					case "terminal_cardservices":
						$("table#tabela-config-config_deParaTerminalCardService tbody tr:not(.ocultar)").each(function () {
							dados.push(
								{
									"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
									"idOrigemTerminal": $($(this).find(".id_origem_terminal-config_deParaTCS")).val(),
								}
							);
						});
						break;
					case "config_nfsaida_cupons_invent":
						dados = {
							"idFiliais": 0, //ALTERADO POSTERIORMENTE VIA PROGRAMAÇÃO
							"bolPermitirGeracaoNFSaidaCuponsInvent": 1,
							"codigoSequenciasNFCuponsInvent": $("#sequencia-nf-nfsaida-cupons-invent").val(),
							"codigoFormasPagamentoNFCuponsInvent": $("#forma-pagamento-nfsaida-cupons-invent").val(),
							"codigoDepositosNFCuponsInvent": $("#deposito-nfsaida-cupons-invent").val(),
						};
						break;
				}

				ajaxRequest(
					true, url, null, 'text',
					{
						'tipoRequest': tipoRequest,
						'filiaisDuplicar': filiaisDuplicar,
						'dados': dados,
					}, function (ret) {
						try {
							ret = JSON.parse(ret);
							if (!is_empty(ret['bol'], 1)) {
								$(modalRef).trigger('hidden.bs.modal');
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
							forceToggleLoading(0);
						}
					}
				);
			}, function (ret) {
				forceToggleLoading(0);
			}).catch(swal.noop);
		}
	});
}

/**
 * Function exibeCampoDeSerie
 * Define se exibirá o input para o código da série
 */
function exibeCampoDeSerie() {
	$('input#bol_habilita_grupos_itens').unbind('change');
	$('input#bol_habilita_grupos_itens').on("change", function () {
		if (this.checked) {
			$('#id-serie-parametrizacao-itens').removeClass('ocultar');
		} else {
			$('#id-serie-parametrizacao-itens').addClass('ocultar');
		}
	});
	$('input#bol_habilita_grupos_itens').trigger('change');
}

function ocultarFormaPagamentoEquals() {
	if (!$('#bol_habilita_codigo_forma_pagamento_equals').is(':checked')) {
		$(".depara-forma-pagamento-equals").addClass("ocultar");
	} else {
		$(".depara-forma-pagamento-equals").removeClass("ocultar");
	}

	$('#bol_habilita_codigo_forma_pagamento_equals').off('change');
	$('#bol_habilita_codigo_forma_pagamento_equals').on('change', function () {
		if (!this.checked) {
			$(".depara-forma-pagamento-equals").addClass("ocultar");
		} else {
			$(".depara-forma-pagamento-equals").removeClass("ocultar");
		}
	})
}

function exibeSomenteConfigCartoes() {
	if($('#permissoesAtribuidasScc').val()==1 && $('#userMaster').val() != 1 ){
		$('#geral').removeClass('show active').addClass('fade');
		$('#config_cartoes').removeClass('fade').addClass('show active');
		$('.nav-item').hide();
		// Mostra apenas o item com link para '#config_cartoes' e adiciona a classe 'active'
		$('a[href="#config_cartoes"]').closest('.nav-item').show().find('a').addClass('active');
	}
}


/**
 * Inverte ou força o status de uma flag
 * @param identificador id do input da flag
 * @param forceStatus [active | inactive] Força o status, se enviado
 * @param callTrigger se verdadeiro irá disparar a trigger de change do input#identificador
 */
function toggleStatusFlag(identificador= null, forceStatus= null, callTrigger= true){
	if(!identificador) return;
	let currentStatus = $(`input#${identificador}`).prop('checked');
	let newStatus = forceStatus ? forceStatus : (currentStatus ? 'inactive' : 'active');
	if(newStatus=='active'){
		$(`input#${identificador}`).prop('checked', true);
		$(`label#${identificador}`).addClass('active');
		$(`label#${identificador} i.fa.fa-square-o.icone-check`).removeClass('fa-square-o').addClass('fa-check-square');
		$(`input#${identificador}`).prop('disabled', false);
	}else{
		$(`input#${identificador}`).prop('checked', false);
		$(`label#${identificador}`).removeClass('active');
		$(`label#${identificador} i.fa.fa-check-square.icone-check`).removeClass('fa-check-square').addClass('fa-square-o');
		$(`input#${identificador}`).prop('disabled', true);
	}
	callTrigger && $(`input#${identificador}`).trigger("change");
}

exibeSomenteConfigCartoes();
ocultarFormaPagamentoEquals();
ocultarCnpjCpf();
ocultarDataCorteTravaConciliacoes();
criaSelectsConcFiliaisConfig();
controlaTabelaConfigCartoes();
exibeCodigoEstoque();
handleFlagDefinirDepositoConciliacao();
exibeControleTerminalCS();
exibeOrdensPedidoImportacaoGorjeta();
// habilitaOpcoesConfigFiliais();
// defineImportacaoManualOuExcel();
controlaModalDuplicacaoDados();
populaCamposHiddenDuplicacao();
controlaTabelaSuite();
controlaCheckIsImpedirEnvioSAPCS();
controlaCheckUtilizarDepositoPadrao();
desocultarCampos();
exibeCampoDeSerie();

controlaTabelaSuite({
	"ref": "#tabela-config-config_deParaTerminalCardService",
	"ref": "#tabela-config-config_deParaCSCC",
	"funAposAddItem": function () {
		controlaCheckIsImpedirEnvioSAPCS();
		controlaCheckUtilizarDepositoPadrao();
	}
});

replicarAbasEntreFiliais();

