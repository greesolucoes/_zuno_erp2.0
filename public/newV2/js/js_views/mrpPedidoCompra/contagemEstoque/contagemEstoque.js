function criaCostumizacoes() {
	$(".layout-compra").select2Simple();
}

function controlarAcoes() {
	let __initPesquisaLayout = function () {
		let __acaoPesquisaLayout = function () {
			let selectLayout = $(".layout-compra");
			let part = $("div#layout-part");
			let url = $(part).data("url_principal");
			$(part).html("");
			if(!is_empty($(selectLayout).val(), 1)) {
				toggleLoading();
				ajaxRequest(true, url, null, 'text', {
					"layout":            $(selectLayout).val(),
					"idContagemEstoque": $(".idContagemEstoque").val(),
					"data-pesquisa":     $("#data-pesquisa").val()
				}, function (ret) {
					try{
						$(part).html(ret);
						toggleLoading();
					}catch(err){
						swal(
							l["erro!"],
							l["tempoDeRespostaDoServidorEsgotado!"],
							"error"
						).catch(swal.noop);
						forceToggleLoading(0);
					}
				});
			}
		};

		$("button#adicionar-layout").off('click');
		$("button#adicionar-layout").on("click", function (){
			__acaoPesquisaLayout();
		});

		__acaoPesquisaLayout();
	}

	__initPesquisaLayout();
}

criaCostumizacoes();
controlarAcoes();

