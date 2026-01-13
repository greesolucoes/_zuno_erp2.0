function criaCostumizacoes() {
	$('.select_ajax').select2Ajax();
	$('.select_ajax').data('init', '');
}

function controlaCheckIsImpostoItem() {
	$("input.despesas-is_imposto_item").off("change");
	$("input.despesas-is_imposto_item").on("change", function () {
		$($(this).parents("tr").find(".despesas-is_imposto_item-hidden")).val(this.checked ? "1" : "0");
		let inputPrioridade= $(this).parents("tr").find("input.despesas-ordem_prioridade");
		if(this.checked){
			inputPrioridade.removeAttr('placeholder');
			inputPrioridade.removeAttr('readonly');
		}else{
			inputPrioridade.val('');
			inputPrioridade.attr('placeholder', inputPrioridade.data('placeholder'));
			inputPrioridade.attr('readonly','readonly');
		}
	});
}
function controlaCheckRemoverCodigoImposto() {
	$("input.despesas-remover_codigo_imposto").off("change");
	$("input.despesas-remover_codigo_imposto").on("change", function () {
		$($(this).parents("tr").find(".despesas-remover_codigo_imposto-hidden")).val(this.checked ? "1" : "0");
	});
}
function controlaInputPrioridadeDespesasAdd(){
	$("input.despesas-ordem_prioridade").off('keyup').on('keyup',function(){
		let el= $(this);
		let iRepete=0;
		$("input.despesas-ordem_prioridade").not('[readonly="readonly"]').each(function(){
			if(el.val() == $(this).val()) iRepete++;
		});
		if(iRepete>1){
			el.val(el.val().substr(0, el.val().length-1));
			swal({
				title: l["atenção!"],
				text: $('div.data_views').data('traducao_erro_prioridade_despesas_add'),
				type: "warning",
				showCancelButton: false,
				confirmButtonColor: '#3085d6'
			});
		}
	});
}

function controlaCheckFormaPagamentoNaoObrigatorio() {
	// Verifico se a flag esta ativa para mostrar os campos
	if ($("#isBolNaoObrigarDadosBancariosEParcelasActive").val() == 1) {
		$('.hideNaoObrigarDadosBancarios').removeClass('ocultar');
	} else {
		$('.hideNaoObrigarDadosBancarios').addClass('ocultar');
	}

	// Não obrigar dados bancarios
	$("input.formas_pagamento-is_nao_obrigar_dados_bancarios").off("change");
	$("#formas_pagamento_table").on("change", "input.formas_pagamento-is_nao_obrigar_dados_bancarios", function () {
		$($(this).parents("tr").find(".formas_pagamento-is_nao_obrigar_dados_bancarios-hidden")).val(this.checked ? "1" : "0");
	});

	// Não obrigar parcelas
	$("input.formas_pagamento-is_nao_obrigar_parcelas").off("change");
	$("#formas_pagamento_table").on("change", "input.formas_pagamento-is_nao_obrigar_parcelas", function () {
		$($(this).parents("tr").find(".formas_pagamento-is_nao_obrigar_parcelas-hidden")).val(this.checked ? "1" : "0");
	});
}

function exibeCampos() {
	$('input#geral-bol_ignorar_variacao_quantidade_item').off('change');
	$('input#geral-bol_ignorar_variacao_quantidade_item').on("change", function() {
		if(this.checked) {
			$('#variacoes-variacao_quantidade_maxima_item').prop('readonly', true);
		} else {
			$('#variacoes-variacao_quantidade_maxima_item').prop('readonly', false);
		}
	});
	$('input#geral-bol_ignorar_variacao_preco_unitario_item').off('change');
	$('input#geral-bol_ignorar_variacao_preco_unitario_item').on("change", function() {
		if(this.checked) {
			$('#variacoes-variacao_preco_unitario_maximo_item').prop('readonly', true);
		} else {
			$('#variacoes-variacao_preco_unitario_maximo_item').prop('readonly', false);
		}
	});
	$('input#geral-bol_habilitar_importacao_cte').off('change');
	$('input#geral-bol_habilitar_importacao_cte').on("change", function() {
		if(this.checked) {
			$('.hide-configs_cte').removeClass('ocultar');
		} else {
			$('.hide-configs_cte').addClass('ocultar');
		}
	});
	$('input#geral-bol_habilitar_importacao_cteos').off('change');
	$('input#geral-bol_habilitar_importacao_cteos').on("change", function() {
		if(this.checked) {
			$('.hide-configs_cteos').removeClass('ocultar');
		} else {
			$('.hide-configs_cteos').addClass('ocultar');
		}
	});

	// tratativa para quando a flag de integração de campos de pagamento integration bank for ativa
	$('input#geral-bol_integrar_campos_pagamento_integration_bank_nf_entrada').off('change');
	$('input#geral-bol_integrar_campos_pagamento_integration_bank_nf_entrada').on("change", function() {
		if(this.checked) {
			$('.hide-configs_pgto_integration_bank').removeClass('ocultar');
		} else {
			$('.hide-configs_pgto_integration_bank').addClass('ocultar');
		}
	});

	$('input#geral-bol_ignorar_variacao_quantidade_item').trigger('change');
	$('input#geral-bol_ignorar_variacao_preco_unitario_item').trigger('change');
	$('input#geral-bol_habilitar_importacao_cte').trigger('change');
	$('input#geral-bol_habilitar_importacao_cteos').trigger('change');
	$('input#geral-bol_integrar_campos_pagamento_integration_bank_nf_entrada').trigger('change');

	$('input#geral-bol-permitir-criacao-de-nfentrada-servico').off('change');
	$('input#geral-bol-permitir-criacao-de-nfentrada-servico').on("change", function() {
		checaDependeciaFlags(this,"geral-bol_habilitar_leitura_retroativa_nfs_ws_sp",'conjuntas');
	});
	$('input#geral-bol-permitir-criacao-de-nfentrada-servico').change();

	// tratativa para quando a flag de não obrigar dados bancarios e parcelas para as formas de pagamento integration bank for ativa
	$('input#geral-flag_nao_obrigar_dados_bancarios').off('change');
	$('input#geral-flag_nao_obrigar_dados_bancarios').on("change", function() {
		if(this.checked) {
			$('.hideNaoObrigarDadosBancarios').removeClass('ocultar');
		} else {
			$('.hideNaoObrigarDadosBancarios').addClass('ocultar');
		}
	});

	$('input#bol_habilitar_integracao_v360').off('change');
	$('input#bol_habilitar_integracao_v360').on("change", function() {
		if(this.checked) {
			$('.hide-configs_integracao_v360').removeClass('ocultar');
		} else {
			$('.hide-configs_integracao_v360').addClass('ocultar');
		}
	});
	$('input#bol_habilitar_integracao_v360').change();

	$('input#bol_integracao_provider').off('change');
	$('input#bol_integracao_provider').on("change", function() {
		if(this.checked) {
			$('.hide-configs_integracao_provider').removeClass('ocultar');
		} else {
			$('.hide-configs_integracao_provider').addClass('ocultar');
		}
	});
	$('input#bol_integracao_provider').change();
}

/**
 * Function utilizada para desativar certas flags para não conseguir utilizá-las simultaneamente
 */
function desativarFlagsConcorrente(){
	/**
	 * Seção documentos ficais aprovação
	 * Seção que pode ativar a aprovação em primeira instância ou remove o processo de aprovação dos documentos fiscais.
 	*/
	let desativarAprocacao = $('input#geral-bol_desabilitar_aprovacao_documentos_fiscais');
	let desativarAprovacaoSefaz = $('input#geral-bol_habilitar_aprovacao_nf_entrada_sefaz_primeira_instancia');
	let desativarAprovacaoServico = $('input#geral-bol_habilitar_aprovacao_nf_entrada_servico_primeira_instancia');


	desativarAprocacao.off('change');
	desativarAprocacao.on("change", function() {
		if(this.checked) {
			$('label[for="geral-bol_habilitar_aprovacao_nf_entrada_sefaz_primeira_instancia"]').addClass('disabled');
			$('label[for="geral-bol_habilitar_aprovacao_nf_entrada_servico_primeira_instancia"]').addClass('disabled');
		} else {
			$('label[for="geral-bol_habilitar_aprovacao_nf_entrada_sefaz_primeira_instancia"]').removeClass('disabled');
			$('label[for="geral-bol_habilitar_aprovacao_nf_entrada_servico_primeira_instancia"]').removeClass('disabled');
		}
	});

	desativarAprovacaoSefaz.off('change');
	desativarAprovacaoSefaz.on("change", function() {
		if(this.checked) {
			$('label[for="geral-bol_desabilitar_aprovacao_documentos_fiscais"]').addClass('disabled');
		} else {
			if ($('#geral-bol_habilitar_aprovacao_nf_entrada_servico_primeira_instancia:checkbox:checked').length <= 0){
				$('label[for="geral-bol_desabilitar_aprovacao_documentos_fiscais"]').removeClass('disabled');
			}
		}
	});

	desativarAprovacaoServico.off('change');
	desativarAprovacaoServico.on("change", function() {
		if(this.checked) {
			$('label[for="geral-bol_desabilitar_aprovacao_documentos_fiscais"]').addClass('disabled');
		} else {
			if ($('#geral-bol_habilitar_aprovacao_nf_entrada_sefaz_primeira_instancia:checkbox:checked').length <= 0 ){
				$('label[for="geral-bol_desabilitar_aprovacao_documentos_fiscais"]').removeClass('disabled');
			}
		}
	});

	/**
	 * Fim da seção documentos ficais aprovação
	 */
}

/**
 * Função para manipular filiais a desconsiderar na integração v360
 */
function integracaov360_filiais() {

	// cria tabela de filiais desabilitadas se existir dados
	const filiaisDesabilitadas = $("#integracao_v360-table-filiais").data('init');
	if(filiaisDesabilitadas != "" || filiaisDesabilitadas != null) {
		const table = $("#integracao_v360-table-filiais tbody");
		filiaisDesabilitadas.forEach((el, idx) => {
			const text = `${el.codigoFiliaisERP} - ${el.razaoSocial}`
			addRowOnv360DisabledFiliais(el.idFiliais, text)
		})
	}

	$("#integracao_v360-geral-filiais").change(function() {
		const filialId = $(this).val()
		const filialName = $(this).find('option:selected').text()
		const isFilial = $('#integracao_v360-table-filiais tbody tr [data-filial-id="' + filialId + '"]').length > 0

		// se não existe filial, adiciona
		if (!isFilial && filialId != null && filialId != '' && filialId != undefined) {
			addRowOnv360DisabledFiliais(filialId, filialName)
		}
	})

	// aguarda botão remover ser pressionado.
	$("#integracao_v360-table-filiais").on("click", ".btn-v360-remove-filial", function() {
		// remove linha da tabela
		var filialId = $(this).data('filial-id')
		$('table tr[data-filial-id="' + filialId + '"]').remove();
	})
}

/**
 * 
 * @param {*} id 
 * @param {*} text 
 */
function addRowOnv360DisabledFiliais(id, text) {
	const table = $("#integracao_v360-table-filiais tbody");
	const clearText = text.replace(/^\s+|\s+$/g, '')
	table.append(`
		<tr data-filial-id="${id}">
			<th ${isOldLayout ? 'scope="col" class="w-auto"' : 'style="min-width: 30rem;"'}>
				${clearText}
				<input type="hidden" name="integracao_v360-disable-filial[]" value="${id}"/>
			</th>
			<th ${isOldLayout ? 'scope="col" class="w-3"' : 'style="width: 7rem"'}>
				<button type="button" data-filial-id="${id}" class="${isOldLayout ? 'btn btn-warning btn-sm' : 'button-form warning-button'} btn-v360-remove-filial">
					<i class="fa fa-trash ${isOldLayout ? '' : 'txt-black-absolute'}"></i>
				</button>
			</th>
		</tr>
	`)

	// atualiza a página, forçando um scroll down
	$('html, body').animate({
		scrollTop: $('#integracao_v360-table-filiais tbody tr:last').offset().top
	}, 500);
}

desativarFlagsConcorrente()
criaCostumizacoes();
controlaCheckIsImpostoItem();
controlaCheckRemoverCodigoImposto();
controlaInputPrioridadeDespesasAdd();
controlaCheckFormaPagamentoNaoObrigatorio();
exibeCampos();
integracaov360_filiais();
controlaTabelaSuite({
	"ref": "#despesas-tipos_despesas-tabela",
	"funAposAddItem": function () {
		controlaCheckIsImpostoItem();
		controlaCheckRemoverCodigoImposto();
		controlaInputPrioridadeDespesasAdd();
	}
});

// controle de adição de linhas para formas de pagamento (Integration Bank)
controlaTabelaSuite({ "ref": "#formas_pagamento_table" });