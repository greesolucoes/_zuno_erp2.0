let __acaoAtualizaUM = function (thisTr, valorProduto) {
	let newUrl = $(".data_views").data("url_principal_um");
	if(!is_empty(valorProduto, 1)) {
		newUrl += valorProduto;
	}

	let select_unidade = $($(thisTr).find(".unidade-medida"));
	if ($(select_unidade).hasClass("select2-hidden-accessible")){
		$(select_unidade).select2('destroy');
	}
	$($(select_unidade).find('option')).remove();
	$(select_unidade).attr("data-url", newUrl);
	$(select_unidade).data("url", newUrl);
	$(select_unidade).attr("data-init", "");
	$(select_unidade).data("init", "");
	$(select_unidade).select2Ajax();
}

function controlaSelectsProdutos() {
	const ref_table = "#layout-tabela";
	let __acaoAtualizaProdutos = function (valorGrupo) {
		let newUrl = $(".data_views").data("url_principal_produtos");
		//faz uma request para buscar todos os produtos do grupo de itens selecionado
		ajaxRequest(true, newUrl + '/' + valorGrupo,null, 'text',{'porGrupoItem': true, 'allItens': true}, function (ret){
		toggleLoading();
			if(!is_empty(ret)){
				ret = JSON.parse(ret);
				$.each(ret, function (index, produto){
					//adiciona a info de produto, nos selects e carrega os campos
					$('table#layout-tabela .add-itens-table-geral').click();
					let addItem = $($('table#layout-tabela tbody tr').last());
					$(addItem).find('.itens-grupo-itens').data("url", newUrl);
					$(addItem).find('.itens-grupo-itens').data("init", JSON.parse(`{"id":"${produto.id}","text":"${produto.text}"}`));
					$(addItem).find('.itens-grupo-itens').select2Ajax();

					__acaoAtualizaUM(addItem, produto.id);
				});
			}
		})
		toggleLoading();
	}

	$(".grupo-itens").off("select2:select");
	$(".grupo-itens").on("select2:select", function () {
		__acaoAtualizaProdutos($(this).val());
	});

	$(".grupo-itens").off("select2:unselect");
	$(".grupo-itens").on("select2:unselect", function () {
		__acaoAtualizaProdutos(null);
	});
}

function controlaSelectsUnidadesMedidas() {
	$(".itens-grupo-itens").off("select2:select");
	$(".itens-grupo-itens").on("select2:select", function () {
		__acaoAtualizaUM($(this).parents("tr"), $(this).val());
	});

	$(".itens-grupo-itens").off("select2:unselect");
	$(".itens-grupo-itens").on("select2:unselect", function () {
		__acaoAtualizaUM($(this).parents("tr"), null);
	});
}

controlaTabelaSuite({
	"ref": "#layout-tabela",
	"funAposAddItem": function () {
		controlaSelectsUnidadesMedidas();
	}
});

controlaSelectsProdutos();
controlaSelectsUnidadesMedidas();