function addMessage(jsonMessage, selector = '.container-msg'){
	$(selector).html(
		`<div class="espacamento"></div>
		
		<div class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX" role="alert">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			${jsonMessage.msg}
		</div>`
	);
}

function removeMessage() {
	$('.container-msg').html('');
	$('.container-msg-modal').html('');
}

function botaoModelo(){
	$('.updateModelo').click(function(){
		if ($(this).hasClass('fa-pencil')){
			$('#nomeModelo').removeAttr('readonly');
			$(this).removeClass('btn-primary').removeClass('fa-pencil').addClass('btn-success').addClass('fa-check');
		}else{
			$.post($('.updateModelo').data('action'), {
				nomeModelo: $('#nomeModelo').val(),
				guid:$('#guid').val(),
				...tokenCsrf
			}, function(retorno){
				$('#nomeModelo').attr('readonly','readonly');
				addMessage(retorno)
			});
			$(this).removeClass('btn-success').removeClass('fa-check').addClass('btn-primary').addClass('fa-pencil');
		}
	});
}

$('#modalModelo').on('hidden.bs.modal', function () {
	$('#modalModelo input[name="nomeModelo"]').val('');
	$(".table-exibe").DataTable().draw();
});

function acaoDeletarCommom(postParamName = null) {
	$('.deletar')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let tableDataTable = $(".table-exibe").DataTable();
			let paramsAjax = (postParamName == null)
				? null
				: { [postParamName]: $(obj).data("id") }

			swal({
				title: l["deletarRegistro"],
				text: l["desejaContinuar?"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();
				ajaxRequest(
					true,
					$(obj).data("url"),
					null,
					'text',
					paramsAjax,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if (!is_empty(ret["bol"], 1)) {
							tableDataTable.draw();
						}

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}

function salvarCommom(action, params){
	let tableDataTable = $(".table-exibe").DataTable();

	toggleLoading();

	params = params ?? {};
	Object.assign(params, tokenCsrf);
	$.post(
		action,
		params,
		function(retorno){
			if (retorno.class == 'success'){
				$('#modalModelo').modal('hide');
				$('#modalModelo input').val('');
				tableDataTable.draw();
			}
			toggleLoading();
			addMessage(retorno);
		}
	);
}

function hasErroPreenchimentoCampos(modalID, { inputsTexto = [], inputsContas = [] }, codigoDoServico) {
	let hasError = false;
	if (codigoDoServico != 'HOSPEDEEMCURSO') {
		// começa com letras, tem letras, números e undeline no meio e deve terminar com números ou letras
		inputsTexto.forEach((inputTexto) => {
			let seletor = `${modalID} input[name="${inputTexto}"]`
			if ($(seletor).val() == '') {
				return;
			}

			if (/^[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ]+[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ_ ]*[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ]*$/.test($(seletor).val()) === false) {
				swal({
					title: l["atenção!"],
					text: l["verifiqueOsValoresEnviadosAsInformacoesDevemConterApenasLetrasNumerosOuUnderlinesNaoPodendoIniciarComUnderlines"],
					type: "warning",
					showCancelButton: false,
					confirmButtonColor: '#3085d6'
				})

				hasError = true;
			}
		});
	}

	// começa com números, tem números e pontos no meio e deve terminar com números
	inputsContas.forEach((inputConta) => {
		let seletor = `${modalID} input[name="${inputConta}"]`;
		if ($(seletor).val() == '') {
			return;
		}

		if (/^[0-9]+[0-9.]*[0-9]*$/.test($(seletor).val()) === false) {
			swal({
				title: l["atenção!"],
				text: l["verifiqueOsCamposDeContasAsInformacoesDevemConterApenasNumerosOuPontosDevendoIniciarComUmNumero"],
				type: "warning",
				showCancelButton: false,
				confirmButtonColor: '#3085d6'
			})

			hasError = true;
		}
	})

	return hasError;
}


function salvarLayout() {
	$('#baixar-layout').click(function () {
		toggleLoading();
		let searchValue = $("input[type='search']").val();
		let url = $(this).data('save_csv');
		if (!is_empty(url, 1)) {
			url += '?searchValue=' + encodeURIComponent(searchValue);
			let xhr = new XMLHttpRequest();
			xhr.open('GET', url, true);
			xhr.responseType = 'blob';
			xhr.onloadend = function () {
				toggleLoading();
				if (this.status === 200) {
					let blob = new Blob([this.response], {type: 'text/csv'});
					let filename = '';
					let disposition = xhr.getResponseHeader('Content-Disposition');
					if (disposition && disposition.indexOf('attachment') !== -1) {
						var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
						var matches = filenameRegex.exec(disposition);
						if (matches != null && matches[1]) {
							filename = matches[1].replace(/['"]/g, '');
						}
					}
					var link = document.createElement('a');
					link.href = window.URL.createObjectURL(blob);
					link.download = filename;
					link.click();
				}
			};
			xhr.send();
		}
	});
}

$('#carregar-layout').click(function() {
	swal({
		title: l["desejaContinuar?"],
		text: l["aoFazerOUploadDeUmNovoLayout,OsRegistrosExistentesDesseModeloSelecionadoSeraoExcluidosEOsNovosSeraoInseridos"],
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: l["continuar!"],
		cancelButtonText: l["cancelar!"]
	}).then((willDelete) => {
		if (willDelete) {
			$('#file-input').click();
		}
	});
});

$('#file-input').change(function() {
	let file = this.files[0];
	let formData = new FormData();
	formData.append('file', file);
	formData.append('guidModelo', $('#guid').val());
	carregarLayout($('#carregar-layout').data('import_csv'), formData);
	this.value = null;
});

function carregarLayout(action, params){
	let tableDataTable = $(".table-exibe").DataTable();

	toggleLoading();
	params = params ?? {};

	Object.entries(tokenCsrf).forEach(([key, value]) => {
		params.append(key, value);
	});

	$.ajax({
		url: action,
		type: 'POST',
		data: params,
		processData: false,
		contentType: false,
		success: function(retorno){
			if (retorno.class == 'success'){
				tableDataTable.draw();
			}
			toggleLoading();
			addMessage(retorno);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			let errorMsg = textStatus + ' | ' + errorThrown;
			addMessage({msg: errorMsg, class: 'danger'});
		}
	});
}

botaoModelo();
salvarLayout();
