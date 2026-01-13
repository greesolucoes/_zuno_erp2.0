/** INPUTS **/
let inputUnidadeMedida = $(".select_unidade_medida-config-geral");
let inputUnidadeMedidaTrib = $(".select_unidade_medida_trib-config-geral");
let inputProduto = $(".select_produto-config-geral")
let inputSubItem = $(".select_subItem-config-geral");
let inputFornecedor = $('.select_fornecedor-config-geral');
let inputUtilizacao = $('.select_utilizacao-config-geral');
/** INPUTS **/

const initUnidade = $('.select_unidade_medida-config-geral').data('init');
const initUnidadeTrib = $('.select_unidade_medida_trib-config-geral').data('init');
/** Só ira funcionar com a flag ativa para os sub-items(Marca) */
const flagSubItemPortalFornecedor = $('.data_views').attr('data-flagSubItemPortalFornecedor');
const initSubItem = $('.select_subItem-config-geral').data('init');
/** Só ira funcionar com a flag ativa para os sub-items(Marca) */

function criaCostumizacoes() {
	inputFornecedor.select2Ajax();
	inputFornecedor.data('init', '');

	inputProduto.select2Ajax();
	inputProduto.data('init', '');

	inputUnidadeMedida.select2Ajax();
	inputUnidadeMedida.data('init', '');

	inputUnidadeMedidaTrib.select2Ajax();
	inputUnidadeMedidaTrib.data('init', '');

	/** Só ira funcionar com a flag ativa para os sub-items(Marca) */
	inputSubItem.select2Ajax();
	inputSubItem.data('init', '');
	/** Só ira funcionar com a flag ativa para os sub-items(Marca) */


	inputUtilizacao.select2Ajax();
	inputUtilizacao.data('init', '');

	$('input.input_files').buttonFile();
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
	function controlaUnidadeTrib(idProduto, init) {
		const url = !is_empty(idProduto, 1) ? ($(".data_views").data("url_unidades_medidas") + idProduto) : '';
		if (inputUnidadeMedidaTrib.hasClass("select2-hidden-accessible")){
			inputUnidadeMedidaTrib.select2('destroy');
		}

		inputUnidadeMedidaTrib.find('option').remove();
		inputUnidadeMedidaTrib.data("url", url);
		if(is_empty(idProduto, 1)) {
			inputUnidadeMedidaTrib.append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
			inputUnidadeMedidaTrib.attr("readonly", true);
		} else {
			inputUnidadeMedidaTrib.attr("readonly", false);
			inputUnidadeMedidaTrib.data("init", init);
			inputUnidadeMedidaTrib.select2Ajax();
		}
		inputUnidadeMedidaTrib.data("init", '');
	}

	if (flagSubItemPortalFornecedor==1){
		function controlaSubItem(idProduto, init) {
			const url = !is_empty(idProduto, 1) ? ($(".data_views").data("url_sub_items") + idProduto) : '';
			if (inputSubItem.hasClass("select2-hidden-accessible")){
				inputSubItem.select2('destroy');
			}

			inputSubItem.find('option').remove();
			inputSubItem.data("url", url);
			if(is_empty(idProduto, 1)) {
				inputSubItem.append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
				inputSubItem.attr("readonly", true);
			} else {
				inputSubItem.attr("readonly", false);
				inputSubItem.data("init", init);
				inputSubItem.select2Ajax();
			}
			inputSubItem.data("init", '');
		}
	}

	inputProduto.off("select2:unselect");
	inputProduto.on("select2:unselect", function () {
		controlaUnidade(null, null);
		controlaUnidadeTrib(null, null);
		if (flagSubItemPortalFornecedor==1){
			controlaSubItem(null, null);
		}
	});

	inputProduto.off("select2:select");
	inputProduto.on("select2:select", function () {
		controlaUnidade($(this).val(), null);
		controlaUnidadeTrib($(this).val(), null);
		if (flagSubItemPortalFornecedor==1){
			controlaSubItem($(this).val(), null);
		}
	});

	controlaUnidade(inputProduto.val(), initUnidade);
	controlaUnidadeTrib(inputProduto.val(), initUnidadeTrib);
}



function controlaDownloadsCSV() {
	$("button.baixar_csv_erro").off('click');
	$("button.baixar_csv_erro").on('click', function () {
		let url = $(this).data('url');

		if (!is_empty(url, 1)) {
			let params = {
				'errors': $("#csv_json").text(),
				...tokenCsrf
			};
			let form = document.createElement("form");
			form.setAttribute("method", "post");
			form.setAttribute("action", url);
			form.setAttribute("target", '_blank');
			for (let i in params) {
				if (!params.hasOwnProperty(i)) continue;

				let input = document.createElement('input');
				input.type = 'hidden';
				input.name = i;
				input.value = params[i];
				form.appendChild(input);
			}
			document.body.appendChild(form);
			form.submit();
			document.body.removeChild(form);
		}
	});

	$("button.baixar_string_erro_csv").off('click');
	$("button.baixar_string_erro_csv").on('click', function () {
		let url = $(this).data('url');

		if (!is_empty(url, 1)) {
			let params = {
				'errors': $("#string_erro_csv").html(),
				...tokenCsrf
			};
			let form = document.createElement("form");
			form.setAttribute("method", "post");
			form.setAttribute("action", url);
			form.setAttribute("target", '_blank');
			for (let i in params) {
				if (!params.hasOwnProperty(i)) continue;

				let textarea = document.createElement('textarea');
				textarea.name = i;
				textarea.innerText = params[i];
				form.appendChild(textarea);
			}
			document.body.appendChild(form);
			form.submit();
			document.body.removeChild(form);
		}
	});
}

criaCostumizacoes();
onProdutosSelected();
if (flagSubItemPortalFornecedor==1){
	onSubItemSelected();
}
controlaDownloadsCSV();