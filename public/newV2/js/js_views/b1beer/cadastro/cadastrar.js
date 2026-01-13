function quantidadePorDepositoEnvase(){
	let __setQuantidade = function () {
		let idProduto = $(".produto_envase").val();
		let idDeposito = $(".deposito_envase").val();
		let url = $(".data_views").data('url_quantidade_envase');

		if(idDeposito != ''){
			$('.quantidadeDisponivel').val("");
			ajaxRequest(true,url,null, 'text',{'idProduto': idProduto, 'idDeposito': idDeposito}, function (ret){
				$('.quantidadeDisponivel').val(ret);
				// melhoria solicitada pelo anytask 184 no dia 09/12/2021
				// $('.quantidadeDisponivel').prop("readonly",false);
				// $('.quantidadeDisponivel').attr("readonly",false);
				// $('.quantidadeDisponivel').removeClass("readonly");
			})
		}
		idProduto = null;
		idDeposito = null;
	}

	$('.deposito_envase').off('select2:select');
	$('.deposito_envase').on('select2:select', function (e) {
		__setQuantidade();
	});

	$(".deposito_envase").off("select2:unselect");
	$(".deposito_envase").on("select2:unselect", function () {
		__setQuantidade();
	});
}

function controlaSelectsProdutos() {
	const ref_table = "#tabela-config-envase";
	let __acaoAtualizaProdutos = function (valorGrupo) {
		let newUrl = $(".data_views").data("url_principal_produto_estrutura");
		if(!is_empty(valorGrupo, 1)) {
			newUrl += valorGrupo;
		}

		let selects_produtos = $($(ref_table).find(".itens-produto-estrutura"));
		$.each(selects_produtos, function () {
			if ($(this).hasClass("select2-hidden-accessible")){
				$(this).select2('destroy');
			}

			$($(this).find('option')).remove();

			$(this).attr("data-url", newUrl);
			$(this).data("url", newUrl);

			$(this).attr("data-init", "");
			$(this).data("init", "");

			$(this).select2Ajax();
		});
	}
	let __acaoRemoveDeposito = function () {
		$('.deposito_envase').prop("readonly",false);
		$('.deposito_envase').attr("readonly",false);
		$('.deposito_envase').removeClass("readonly");

		$($('.deposito_envase').find('option')).prop('selected', false);
		$('.deposito_envase').find('option[value=""]').prop('selected', true);
		$('.deposito_envase').trigger("change.select2");
		$('.deposito_envase').trigger("select2:unselect");
	}

	$(".produto_envase").off("select2:select");
	$(".produto_envase").on("select2:select", function () {
		$(".itens-produto-estrutura").removeClass("readonly");
		__acaoAtualizaProdutos($(this).val());
		__acaoRemoveDeposito();
	});

	$(".produto_envase").off("select2:unselect");
	$(".produto_envase").on("select2:unselect", function () {
		$(".itens-produto-estrutura").addClass("readonly");
		__acaoAtualizaProdutos(null);
		__acaoRemoveDeposito();
	});
}

function getUnidadeMedidaProduto(input){
	var idProduto = $(input).parents("tr").find(".itens-produto-estrutura").val();
	var url = $('.data_views').data('url_unidade_medida');
	var selectUnidadeMedida = $(input).parents("tr").find(".select_medida");
	selectUnidadeMedida.removeClass("readonly");
	selectUnidadeMedida.select2('destroy');
	selectUnidadeMedida.data('url',url + idProduto);
	selectUnidadeMedida.data("init", "");
	selectUnidadeMedida.select2Ajax();
}

controlaTabelaSuite({
	"funAposAddItem": function () {
		controlaSelectsProdutos();
	}
});
contaCaracteres(254, 'observacoes');
controlaSelectsProdutos();
quantidadePorDepositoEnvase();
// $(".config_idProdutoEstrutura-idProdutoEstrutura").trigger("select2:select");