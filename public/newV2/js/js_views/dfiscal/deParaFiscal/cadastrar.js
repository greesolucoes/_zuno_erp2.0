$(document).ready(function() {
	$('.cfop').mask('9999');
	$('.cstIcms').mask('9999');
});

$('input.input_files').buttonFile();

function changeSelect(){
	$('#fornecedor').on('change', function (){
		$('.nomeFornecedor').val($('#fornecedor').text());
	});
	$('#grupo_item').on('change', function (){
		$('.nomeGrupoItens').val($('#grupo_item').text());
	});
	$('#item').on('change', function (){
		$('.nomeProdutos').val($('#item').text());
	});
	$('#utilizacao').on('change', function (){
		$('.nomeTipoUtilizacaoProduto').val($('#utilizacao').text());
	});
}

function disableSelect(){
	$("#grupo_item").on("change", function (e) {
		if($(this).val()){
			$('#item').attr('disabled', true);
		} else {
			$('#item').attr('disabled', false);
		}
	});

	$("#item").on("change", function (e) {
		if($(this).val()){
			$('#grupo_item').attr('disabled', true);
		} else {
			$('#grupo_item').attr('disabled', false);
		}
	});
}

function criarSelect(){
	$('select.select_ajax').select2Ajax();
	$('select.select_ajax').data('init', '');
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

criarSelect();
disableSelect();
changeSelect();
controlaDownloadsCSV();