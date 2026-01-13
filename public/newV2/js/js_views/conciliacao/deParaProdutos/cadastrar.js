function criaCostumizacoes() {
	$('input.input_files').buttonFile();
}

function replicarParaEmpresas(){
	$('.cadastro').attr('action', $('.data_views').data('form_replicar_empresas')).submit();
}

function disableReplicar(){
	$('.de-para-produtos-is_filial').change(function (){
		if(this.checked){
			$('.replicarDePara').addClass("disabled");
		} else {
			$('.replicarDePara').removeClass("disabled");
		}
	})
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

disableReplicar();
criaCostumizacoes();
controlaDownloadsCSV();