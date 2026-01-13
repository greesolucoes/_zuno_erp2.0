const initUnidade = $('.select_unidade_medida-config-geral').data('init');

function criaCostumizacoes() {
	$('.select_fornecedor-config-geral').select2Ajax();
	$('.select_fornecedor-config-geral').data('init', '');

	$('.select_produto-config-geral').select2Ajax();
	$('.select_produto-config-geral').data('init', '');

	$('.select_unidade_medida-config-geral').select2Ajax();
	$('.select_unidade_medida-config-geral').data('init', '');

	$('.select_utilizacao-config-geral').select2Ajax();
	$('.select_utilizacao-config-geral').data('init', '');

	$('.select_municipio-config-geral').select2Ajax();
	$('.select_municipio-config-geral').data('init', '');

	$('input.input_files').buttonFile();
}

function onProdutosSelected() {
	function controlaUnidade(idProduto, init) {
		const url = !is_empty(idProduto, 1) ? ($(".data_views").data("url_unidades_medidas") + idProduto) : '';
		if ($(".select_unidade_medida-config-geral").hasClass("select2-hidden-accessible")){
			$('.select_unidade_medida-config-geral').select2('destroy');
		}

		$('.select_unidade_medida-config-geral option').remove();
		$('.select_unidade_medida-config-geral').data("url", url);
		if(is_empty(idProduto, 1)) {
			$('.select_unidade_medida-config-geral').append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
			$('.select_unidade_medida-config-geral').attr("readonly", true);
		} else {
			$('.select_unidade_medida-config-geral').attr("readonly", false);
			$('.select_unidade_medida-config-geral').data("init", init);
			$('.select_unidade_medida-config-geral').select2Ajax();
		}
		$('.select_unidade_medida-config-geral').data("init", '');
	}

	$(".select_produto-config-geral").off("select2:unselect");
	$(".select_produto-config-geral").on("select2:unselect", function () {
		controlaUnidade(null, null);
	});

	$(".select_produto-config-geral").off("select2:select");
	$(".select_produto-config-geral").on("select2:select", function () {
		controlaUnidade($(this).val(), null);
	});

	controlaUnidade($('.select_produto-config-geral').val(), initUnidade);
}

function controlaDownloadsCSV() {
	$("button.baixar_csv_erro").off('click');
	$("button.baixar_csv_erro").on('click', function () {
		let url = $(this).data('url');

		if (!is_empty(url, 1)) {
			let params = {'errors': $("#csv_json").text(), ...tokenCsrf};
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
			let params = {'errors': $("#string_erro_csv").html(), ...tokenCsrf};
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

// Adiciona uma limitação no campo de Código de serviço para aceitar apenas letras e numeros
$(".form-control.de-para-produtos-codigo_servico").on("input", function() {
	const inputValue = $(this).val();
	const sanitizedValue = inputValue.replace(/[^a-zA-Z0-9]/g, ""); // Remove caracteres não alfanuméricos
	$(this).val(sanitizedValue);
});

criaCostumizacoes();
onProdutosSelected();
controlaDownloadsCSV();