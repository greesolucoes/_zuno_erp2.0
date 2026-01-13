function controlaSelectsProdutos() {
	const ref_table = "#layout-tabela";
	let __acaoAtualizaProdutos = function (valorGrupo) {
		let newUrl = $(".data_views").data("url_principal_produtos");
		if(!is_empty(valorGrupo, 1)) {
			newUrl += valorGrupo;
		}

		let selects_produtos = $($(ref_table).find(".itens-grupo-itens"));
		if ($(selects_produtos).hasClass("select2-hidden-accessible")){
			$(selects_produtos).select2('destroy');
		}
		// $($(selects_produtos).find('option')).remove();
		$(selects_produtos).attr("data-url", newUrl);
		$(selects_produtos).data("url", newUrl);
		$(selects_produtos).attr("data-init", "");
		$(selects_produtos).data("init", "");
		$(selects_produtos).select2Ajax();
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


controlaTabelaSuite({
	"ref": "#layout-tabela",
	"funAposAddItem": function () {
		controlaSelectsProdutos();
	}
});
controlaSelectsProdutos();