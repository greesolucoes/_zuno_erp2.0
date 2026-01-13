/** INPUTS **/
let inputUnidadeMedida = $(".select_unidade_medida-config-geral");
let inputProduto = $(".select_produto-config-geral")
let inputFornecedor = $('.select_fornecedor-config-geral');
let inputUtilizacao = $('.select_utilizacao-config-geral');
/** INPUTS **/

const initUnidade = $('.select_unidade_medida-config-geral').data('init');

function criaCostumizacoes() {
	inputFornecedor.select2Ajax();
	inputFornecedor.data('init', '');

	inputProduto.select2Ajax();
	inputProduto.data('init', '');

	inputUnidadeMedida.select2Ajax();
	inputUnidadeMedida.data('init', '');

	inputUtilizacao.select2Ajax();
	inputUtilizacao.data('init', '');

}

function onProdutosSelected() {
	function controlaUnidade(idProduto, init) {
		const url = !is_empty(idProduto, 1) ? ($(".data_views").data("url_unidades_medidas") + idProduto) : '';
		if (inputUnidadeMedida.hasClass("select2-hidden-accessible")){
			inputUnidadeMedida.select2('destroy');
		}

		inputUnidadeMedida.find('option').remove();
		inputUnidadeMedida.data("url", url);
		if(is_empty(idProduto, 1)) {
			inputUnidadeMedida.append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
			inputUnidadeMedida.attr("readonly", true);
		} else {
			inputUnidadeMedida.attr("readonly", false);
			inputUnidadeMedida.data("init", init);
			inputUnidadeMedida.select2Ajax();
		}
		inputUnidadeMedida.data("init", '');

	}

	inputProduto.off("select2:unselect");
	inputProduto.on("select2:unselect", function () {
		controlaUnidade(null, null);
	});

	inputProduto.off("select2:select");
	inputProduto.on("select2:select", function () {
		controlaUnidade($(this).val(), null);
	});

	controlaUnidade(inputProduto.val(), initUnidade);
}

criaCostumizacoes();
onProdutosSelected();