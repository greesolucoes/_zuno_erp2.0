// Ativa ou desativa configurações de integrações junto ao SAP
$('input[type="checkbox"]').on('change', function() {
	handleIntegracoes();
});

// função para esconder as configurações de integrações não ativas
function handleIntegracoes() {
	document
		.querySelectorAll("#integracoes .row.item input[type='checkbox']")
		.forEach(function(e) {
			let tabIntegracao = $("[data-integracao_tab='" + e.value + "']");

			if (e.checked) {
				tabIntegracao.removeClass('disabled');
				// show elements
				$("[data-flag-view='" + e.value + "']").show();
			} else {
				tabIntegracao.addClass('disabled');
				tabIntegracao.removeClass('active');
				$(`#${tabIntegracao.attr('aria-controls')}`).removeClass('active');
				// hide elements
				$("[data-flag-view='" + e.value + "']").hide();
			}
		});
};

/**
 * Função genérica para quando um botão for acionado
 * @param btn			Seletor do botão
 * @param dataText		Texto da mensagem a ser exibida
 * @param title			Título da mensagem a ser exibida
 * @param textSuccess	Texto da mensagem de sucesso
 */
function ajaxBtnAction (btn, dataText, title, textSuccess) {
	const actionbtn = $(btn);

	actionbtn
		.unbind('click')
		.on("click", function (e) {
			e.preventDefault();

			let url = actionbtn.data('url');
			let textReset = actionbtn.data(dataText);

			swal({
				title: title,
				text: textReset,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(() => {
				$.get(url, function(ret) {
					toggleLoading();

					let titulo = l["erro"];
					let texto = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
					let tipo = "error";

					if (ret) {
						titulo = l['sucesso!'];
						texto = textSuccess;
						tipo = "success";
					}

					swal(titulo, texto, tipo);
				});

				toggleLoading();
			}).catch(swal.noop);
		})
}


/**
 * Toda integração que permita que cada filial da empresa
 * tenha uma ativação independente de flag deve ser incluida
 * aqui. (Mateus Neri)
 *
 * Observacao Importante: o dataText deve ser em snake_case para
 * que a função seja executada da maneira correta. Isso acontece
 * porque ao ler um atributo do tipo data, o javascript converte
 * tudo para caixa baixa
 */
const ajaxButtons = [
	{
		'btn': 'button.habilitar_BARBOC_filiais',
		'dataText': 'text_habilitar_barboc',
		'title': l["integracaoBarboc"]
	},
	{
		'btn': 'button.habilitar_RH_filiais',
		'dataText': 'text_habilitar_rh',
		'title': l["integracaoRH"]
	},
	{
		'btn': 'button.habilitar_LCM_filiais',
		'dataText': 'text_habilitar_lcm',
		'title': l["integracaoLCM"]
	},
	{
		'btn': 'button.habilitar_XMLNFS_filiais',
		'dataText': 'text_habilitar_xmlnfs',
		'title': l["integracaoXmlNfs"]
	}
];

const formUtils = {
	removeTr: function (elemento) {
		if ($(elemento).attr("data-url")){
			$.get($(elemento).attr("data-url"), function(ret) {
				if(ret=="false"){
					$(elemento).parents('tr').fadeOut(270, function () {
						$(elemento).parents('tr').remove();
					});
				}else{
					swal(l["erro"], l["oCartãoNãoPodeSerRemovidoPoisHáParametrizaçõesAtivasComOMesmo!"], 'error');
				}
			});
		}else{
			$(elemento).parents('tr').fadeOut(270, function () {
				$(elemento).parents('tr').remove();
			});
		}
	},

	addItemDePara: function (button, aba) {
		$('button[data-add="' + button + '"]').click(function(e) {
			e.preventDefault();
			let template = $(aba + ' template').html();
			let index = parseInt($(aba + ' tfoot').attr('data-count')) + 1;
			let html = template.replaceAll("{{n}}", index);

			$(aba + ' tbody').fadeIn(270, function() {
				$(aba + ' tbody').append(html);
			})

			$(aba + ' tfoot').attr('data-count', index);
			$('select.select2').select2();
		})
	}
}

// de-paras disponíveis
// adicionar aqui caso haja outro
const btnsDePara = [
	{ 'btn': 'equals-tipo-lancamento', 'aba': '#equalsTiposLacamento_add' },
	{ 'btn': 'cartoes', 'aba': '#cartoes_add' },
	{ 'btn': 'regra', 'aba': '#regra-aba'  }
];

// para cada de-para, cria-se a funcionalidade
btnsDePara.forEach(function(dePara) { formUtils.addItemDePara(dePara.btn, dePara.aba) });

// função que se executa ao carregar a view
(function() {
	// cria a função que habilita as integrações para todas as filiais
	ajaxButtons.forEach(function(button) {
		ajaxBtnAction(
			button.btn,
			button.dataText,
			button.title,
			button.textSuccess ?? l["todasAsFiliaisDestaEmpresaEstaoHabilitadasParaRealizarEstaIntegracao"]
		);
	});

	handleIntegracoes();
})();

$('button.addEmail').click(function () {
	let seletorTableTbodyTr = 'table#emailsTable tbody tr';

	$('table#emailsTable')
		.append('<tr>' + $(seletorTableTbodyTr).first().html() + '</tr>');

	$($(seletorTableTbodyTr).last())
		.find('button.removerEmail')
		.prop('disabled', false);

	$($(seletorTableTbodyTr).last())
		.find('input.form-control')
		.val("");
});

$(document).on("click", ".removerEmail", function() {
	let rem = $(this).parents('tr');

	rem.fadeOut(270, function () {
		rem.remove();
	});
});

$('button.addEmpresas').click(function () {
	let seletorTableTbodyTr = 'table#filiaisTable tbody tr';

	$('table#filiaisTable')
		.append('<tr>' + $(seletorTableTbodyTr).first().html() + '</tr>');

	$($(seletorTableTbodyTr).last())
		.find('button.removeEmpresas')
		.prop('disabled', false);

	$('table#filiaisTable .select').select2Reset();

	$($($(seletorTableTbodyTr).last()).find('select.select_empresa'))
		.find('option:selected')
		.prop('selected', false);

	$($($(seletorTableTbodyTr).last()).find('select.select_filial'))
		.find('option')
		.remove()
		.append('<option value=""></option>');

	criaSelect();
});

let removeEmpresaEmissora = function(){
	$('table#empresasEmissorasTable button.removeEmpresasEmissoras')
		.unbind('click')
		.click(function () {
			let rem = $(this).parents('tr');

			rem.fadeOut(270, function () {
				rem.remove();
			});
		});
}

$('button.addEmpresasEmissoras').click(function () {
	let seletorTableTbodyTr = 'table#empresasEmissorasTable tbody template';
	$('table#empresasEmissorasTable tbody')
		.append($(seletorTableTbodyTr).first().html());
	removeEmpresaEmissora();
});
removeEmpresaEmissora();



function hideAndShowOptHabilitarFiliais(){
	$('input#habilitarTodasFiliais')
		.unbind('change')
		.on('change', function() {
			if(this.checked) {
				$('#filiaisTable').hide();
				$('#select_empresa_habilita_filiais').show();
			}else{
				$('#filiaisTable').show();
				$('#select_empresa_habilita_filiais').hide();
			}
		})
		.trigger('change');
}

function criaSelect() {
	ajaxSelect();

	$(".select_empresa").select2({
		placeholder: l["empresas"],
		language: "pt-BR",
		allowClear: true
	});
	$(".select_filial").select2({
		placeholder: l["filiais"],
		language: "pt-BR",
		allowClear: true
	});

	let empresaAllFilial = $('.datas_views').data('empresa_all_filial');

	if(!is_empty(empresaAllFilial,1)) {
		$("#empresas_habilitar_filiais").data('init',
			JSON.parse(`{"id":"${empresaAllFilial.id}","text":"${empresaAllFilial.text}"}`)
		);
	}else{
		$("#empresas_habilitar_filiais").data('init', '');
	}
	$("#empresas_habilitar_filiais").select2Ajax();

	$('table#filiaisTable button.removeEmpresas')
		.unbind('click')
		.click(function () {
			let rem = $(this).parents('tr');

			rem.fadeOut(270, function () {
				rem.remove();
			});
		});

	$("select.select_status_reimportacao").select2();
	$("select.select_status_reimportacao").data('init', '');
}
function ajaxSelect(){
	$(".select_empresa")
		.unbind('change')
		.change(function (){
			let empresa = $('option:selected', this).val();
			let selectFilial = $(this).parents('tr').find('select.select_filial');
			let url = $('.datas_views').data('url_ajax_filiais');

			$(selectFilial).find('option').remove();
			$(selectFilial).append('<option value=""></option>');

			if(empresa != null && empresa != '') {
				ajaxRequest(
					true,
					url,
					null,
					'text',
					{ 'empresa': empresa },
					function(ret){
						ret = $.parseJSON(ret);
						$.each(ret, function (id, value) {
							$(selectFilial).append(`<option value="${value.idFiliais}">${value.razaoSocial}</option>`);
						});
					}
				)
			}
		});
}

criaSelect();
hideAndShowOptHabilitarFiliais();

$('select#idFiliaisPortalOrcamento').select2Ajax();
$('select#idFiliaisPortalOrcamento').init();

function buscarRegraDistribuicao(index,valor){
	const url = $('#equalsTiposLacamento_add').attr("data-url-regras-distribuicao");
	$.ajax({
		url : url,
		type : 'post',
		dataType: 'json',
		data : {
			dimensao : valor,
			...tokenCsrf
		},
		beforeSend : function(){
			$(`select[name="equals-tipo-lancamento[dePara][${index}][dimensao]"]`).attr('readonly','readonly');
			$(`select[name="equals-tipo-lancamento[dePara][${index}][idregra]"]`).attr('readonly','readonly').empty();
		}
	}).done(function(data){
		$.each(data, function (i, item) {
			$(`select[name="equals-tipo-lancamento[dePara][${index}][idregra]"]`).append($('<option>', {
				value: item.idregra,
				text : item.nomeregra
			}));
		});

		$(`select[name="equals-tipo-lancamento[dePara][${index}][dimensao]"]`).removeAttr('readonly');
		$(`select[name="equals-tipo-lancamento[dePara][${index}][idregra]"]`).removeAttr('readonly');
	});
}

/**
 * Função que ira deixar os campos de cancelamento com apenas readonly caso a flag cancelamento esteja marcado como 1
 * @param index
 */
function naoEditarCamposCancelamento(index){
	if ($(`select[name="equals-tipo-lancamento[dePara][${index}][cancelamento]"]`).val()==1){
		$(`input[name="equals-tipo-lancamento[dePara][${index}][contaDebitoCancelamento]"]`).removeAttr('readonly');
		$(`input[name="equals-tipo-lancamento[dePara][${index}][contaCreditoCancelamento]"]`).removeAttr('readonly');
		$(`select[name="equals-tipo-lancamento[dePara][${index}][dimensao1Cancelamento]"]`).removeAttr('readonly');
		$(`select[name="equals-tipo-lancamento[dePara][${index}][dimensao2Cancelamento]"]`).removeAttr('readonly');
	}else{
		$(`input[name="equals-tipo-lancamento[dePara][${index}][contaDebitoCancelamento]"]`).attr('readonly','readonly');
		$(`input[name="equals-tipo-lancamento[dePara][${index}][contaCreditoCancelamento]"]`).attr('readonly','readonly');
		$(`select[name="equals-tipo-lancamento[dePara][${index}][dimensao1Cancelamento]"]`).attr('readonly','readonly');
		$(`select[name="equals-tipo-lancamento[dePara][${index}][dimensao2Cancelamento]"]`).attr('readonly','readonly');
	}
}

/**
 * Função que ira deixar os campos de credita banco com apenas readonly caso a flag cancelamento esteja marcado como 1
 * @param index
 */
function naoEditarCamposCreditaBanco(index){
	if ($(`select[name="equals-tipo-lancamento[dePara][${index}][creditaBanco]"]`).val()==1){
		$(`input[name="equals-tipo-lancamento[dePara][${index}][contaContabilCreditoDesagendamento]"]`).attr('readonly','readonly');
	}else{
		$(`input[name="equals-tipo-lancamento[dePara][${index}][contaContabilCreditoDesagendamento]"]`).removeAttr('readonly');
	}
}

/**
 * Adiciona e remove as chaves do idErpCs em recebimentos abertos
 * @param index
 */
var contadorRea = $('.section-rea').length;
$('#add-field-rea').click(function() {
	contadorRea++;
	var novaSecao = $('.section-rea').last().clone();
	novaSecao.attr('id', 'section-rea-' + contadorRea);
	novaSecao.find('label').attr('for', 'chaveRecebimentosAbertos' + contadorRea);
	novaSecao.find('label').html(function() {
		return $(this).html().replace(/\d+$/, contadorRea);
	});
	novaSecao.find('span').attr('id', 'spanRecebimentosAbertos' + contadorRea).text(contadorRea);
	novaSecao.find('input').attr('id', 'chaveRecebimentosAbertos' + contadorRea).val('');
	novaSecao.find('button').attr('id', 'remove-field-rea-' + contadorRea).unbind('click').click(function() {
		$(this).closest('.section-rea').remove();
		contador = $('.section-rea').length;
	});
	novaSecao.insertBefore('#add-field-rea');
});
$('.remove-btn-rea').click(function() {
	var val_id = $(this).attr('val_id');
	if( val_id != 1 ){
		$('#remove-field-rea-'+val_id).closest('.section-rea').remove();
	}
});

$('#add-field-chave-rps').click(function() {
	var novaSecao = $('.linha-chave-rps').last().clone().removeClass("ocultar");
	novaSecao.insertAfter($('.linha-chave-rps').last());
	$('.selectchavecancelamento').select2Simple();
});

$(document).on("click",".remove-field-chave-rps",function() {
	$(this).closest('.linha-chave-rps').remove();
	$('.selectchavecancelamento').select2Simple();
});

// Verifica se houve algum check em alguma flag
$('input[name="integracoes[]"]').on('change', function () {
	// Flag mudar data do documento no periodo contábil
	if ($(this).val() === 'mpc') {
		if ($(this).is(':checked')) {
			$('.div-mensagem-erro-periodo-contabil').show();
		} else {
			$('.div-mensagem-erro-periodo-contabil').hide();
		}
	}
});

function exibirDivCardService() {
	var origin = $('#originCardService').val();
	if (origin !== 'eql') {
		$('#CSEquals').hide();
	} else if (origin === 'eql') {
		$('#CSEquals').show();
	}
}

$('#originCardService').on('change', function() {
	exibirDivCardService();
});

exibirDivCardService();