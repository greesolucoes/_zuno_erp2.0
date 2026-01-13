//CHAMADA DE FUNCAO APOS LOAD COMPLETO DA TELA
$(document).ready(function() {
	allFunctions();
	criaSelects();
});

//CRIACAO DE SELECTS
function criaSelects() {
	$(".select_filial").select2Ajax();
	$(".select_filial").data('init', '');

	$(".select_produtoId").select2AjaxProdutos();
	$(".select_produtoId").data('init', '');

	$(".select_ajax").select2Ajax();
	$(".select_ajax").data('init', '');

	$(".lbl-todas-filiais").off("click");
	$(".lbl-todas-filiais").on("click", function () {
		$(".tabela-filiais-hide").toggle();
	});
}

// BTN REMOVER/ADICIONAR ITENS
function addButtonsRemoveItens() {
	$('table#conteudoTable button.removeItens').unbind('click');
	$('table#conteudoTable button.removeItens').click(function () {
		var rem = $(this).parents('tr');
		rem.fadeOut(270, function () {
			rem.remove();
		});
	});
}
$('table#conteudoTable button.addItens').click(function () {
	var modelo = $('table#conteudoTable tbody tr').first().html();
	var urlAjaxGetProd = $('.data_views').data('url_ajax_get_prod');

	$('div#conteudo table#conteudoTable tbody').append('<tr>' + modelo + '</tr>');
	$($('table#conteudoTable tbody tr').last()).find('button.removeItens').prop('disabled', false);
	$('table#conteudoTable tbody tr .select').select2Reset();

	var limpaCampos = $($('div#conteudo table#conteudoTable tbody tr').last());
	$(limpaCampos).find('select.select_produtoId').find('option').remove();
	$(limpaCampos).find('select.select_produtoId').data('init', '');
	$(limpaCampos).find('select.select_produtoId').data('url', urlAjaxGetProd);
	$(limpaCampos).find('select.select_produtoId option[value=""]').prop('selected', 'selected');
	$('select.select_produtoId').data('travaselecao', 1);

	allFunctions();
	criaSelects();
	addButtonsRemoveItens();
	$('select.select_produtoId').data('travaselecao', 0);
});

//BTN REMOVER / ADICIONAR FILIAIS
function addButtonsRemoveFilial() {
	$('table#conteudoTableFilial button.removeFilial').unbind('click');
	$('table#conteudoTableFilial button.removeFilial').click(function () {
		var rem = $(this).parents('tr');
		rem.fadeOut(270, function () {
			rem.remove();
		});
	});
}
$('table#conteudoTableFilial button.addFilial').click(function () {
	var modelo = $('table#conteudoTableFilial tbody tr').first().html();
	var urlAjaxGetFilial = $('.data_views').data('url_ajax_get_filial');

	$('div#conteudoFilial table#conteudoTableFilial tbody').append('<tr>' + modelo + '</tr>');
	$($('table#conteudoTableFilial tbody tr').last()).find('button.removeFilial').prop('disabled', false);
	$('table#conteudoTableFilial tbody tr .select').select2Reset();

	var limpaCampos = $($('div#conteudoFilial table#conteudoTableFilial tbody tr').last());
	$(limpaCampos).find('select.select_filial').find('option').remove();
	$(limpaCampos).find('select.select_filial').data('init', '');
	$(limpaCampos).find('select.select_filial').data('url', urlAjaxGetFilial);
	$(limpaCampos).find('select.select_filial option[value=""]').prop('selected', 'selected');
	$('select.select_filial').data('travaselecao', 1);

	allFunctions();
	criaSelects();
	addButtonsRemoveFilial();
	$('select.select_filial').data('travaselecao', 0);
});


//CHAMADA DE JS
addButtonsRemoveItens();
addButtonsRemoveFilial();