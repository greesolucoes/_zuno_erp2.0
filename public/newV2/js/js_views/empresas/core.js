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

	$('table#tabela-config-config_deParaTerminalCardService button.remove-itens-terminal').off('click');
	$('table#tabela-config-config_deParaTerminalCardService button.remove-itens-terminal').on("click", function () {
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
		criaSelects();
		controlaTabelaConfigCartoes();
	});

	$('table#tabela-config-config_deParaTerminalCardService button#add-itens-terminal').off("click");
	$('table#tabela-config-config_deParaTerminalCardService button#add-itens-terminal').on("click", function () {
		var tbody = $('table#tabela-config-config_deParaTerminalCardService tbody');

		var modelo = $(tbody).find('tr').first().html();
		$(tbody).append('<tr>' + modelo + '</tr>');

		allFunctions();
		controlaTabelaConfigCartoes();
	});
}

function criaSelects() {
	$(".select_ajax_cartao").select2Ajax();
	$(".select_ajax_cartao").data('init', '');
}

controlaTabelaConfigCartoes();
criaSelects();