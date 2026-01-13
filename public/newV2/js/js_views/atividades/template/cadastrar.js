// Função para adicionar mensagens no modal
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

function limpaCamposModal() {
	$('#modalAtividades input[name="id"]').val('');
	$('#modalAtividades input[name="nomeTemplate"]').val('');
	$('#modalAtividades input[name="descricao"]').val('');
	$('#modalAtividades select[name="tipo"]').empty().trigger('change');
	$('#modalAtividades select[name="assunto"]').empty().trigger('change');
	$('#modalAtividades select[name="lista"]').empty().trigger('change');
	$('#modalAtividades input[name="prazoFinal"]').val('');
	$('#modalAtividades input[name="horaInicio"]').val('');
	$('#modalAtividades input[name="horaFinal"]').val('');
	$('#modalAtividades select[name="recorrencia"] option').prop("selected", false).trigger('change');
	$('#modalAtividades input[name="repetir"]').val('');
	$(".btn-success.salvar").show();
}

function hasErroPreenchimentoCampos(modalID, { inputsTexto = [] }) {
	let hasError = false;
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

	return hasError;
}

$(document).ready(function() {
	const labels = {
		"N": l['nenhum'],
		"D": l['dias(S)'],
		"W": l['semana(S)'],
		"M": l['mês/Meses'],
		"A": l['ano(S)']
	};

	$('#select-recorrencia').change(function() {
		let valor = $(this).val();
		if (valor === 'N') {
			$('#campo-repetir').hide();
		} else {
			$('#campo-repetir').show();
			$('#label-repetir').text(l['repetirACada-'] + ' ' + labels[valor]);
		}
	});
});

$(document).ready(function() {
	$("#horaInicio").attr('onkeydown', 'return false');
	$("#horaFinal").attr('onkeydown', 'return false');
});

function criaSelect() {
	$('.select_atividade').select2Ajax();
	$('.select_atividade').data('init', '');

	$('#select-assunto').select2({
		placeholder: l['assunto'],
		language: "pt-BR",
		allowClear: true
	});
	$('#select-recorrencia').select2({
		placeholder: l['recorrência?'],
		language: "pt-BR",
		allowClear: true
	});

	$('.select_FI').select2({
		placeholder: l['selecione'],
		language: "pt-BR",
		allowClear: true
	});
}

function salvar(){
	$('#modalAtividades').on('hidden.bs.modal', function () {
		removeMessage();
		limpaCamposModal();
		$('#modalAtividades .titulo-modal').html(l['Alterar']);
		$('#modalAtividades .titulo-modal').attr('is-add', 0);
	});

	$('.abrir-modal-atividades').on('click', function () {
		removeMessage();
		limpaCamposModal();
		$('#modalAtividades .titulo-modal').html(l['Cadastrar']);
		$('#modalAtividades .titulo-modal').attr('is-add', 1);
		$('#modalAtividades').modal('show');
	});

	$('#modalAtividades .salvar').click(async function(){
		toggleLoading();

		const action = $(this).data('action');
		let is_add = $('#modalAtividades .titulo-modal').attr('is-add');

		if (hasErroPreenchimentoCampos(
			'#modalAtividades',
			{
				inputsTexto: [
					'nomeTemplate',
					'descricao'
				],
			}
		)) {
			toggleLoading();
		} else {
			await $.post(action, {
				id:	$('#modalAtividades input[name="id"]').val(),
				nomeTemplate:	$('#modalAtividades input[name="nomeTemplate"]').val(),
				descricao:		$('#modalAtividades input[name="descricao"]').val(),
				tipo:			$('#modalAtividades select[name="tipo"]').val(),
				assunto:		$('#modalAtividades select[name="assunto"]').val(),
				lista:			$('#modalAtividades select[name="lista"]').val(),
				prazoFinal:		$('#modalAtividades input[name="prazoFinal"]').val(),
				horaInicio:		$('#modalAtividades input[name="horaInicio"]').val(),
				horaFinal:		$('#modalAtividades input[name="horaFinal"]').val(),
				recorrencia:	$('#modalAtividades select[name="recorrencia"]').val(),
				repetir:		$('#modalAtividades input[name="repetir"]').val(),
				isAdd: 			is_add,
				...tokenCsrf
			}, function(retorno){
				if (retorno.class == 'success'){
					let tableDataTable = $(".table-exibe").DataTable();
					tableDataTable.draw();

					limpaCamposModal();
				}

				addMessage(retorno, '.container-msg-modal');

				toggleLoading();
			});
		}
	});
}

$('#select-tipo').on('select2:select', function (e) {
	const ref_table_search = ".table-exibe";
	let select = $('#select-assunto');
	let idTipoErp = $('#select-tipo').val();
	let urlSelectAssunto = $(ref_table_search).data("url_select_assunto_atividade");
	$(select).find('option').remove();
	ajaxRequest(
		true,
		urlSelectAssunto,
		null,
		'text',
		{'idTipoErp': idTipoErp},
		function(ret){
			ret = $.parseJSON(ret);
			select.append('<option value=""></option>');
			$.each(ret, function (index, value) {
				select.append('<option value="' + value.id + '">' + value.text + '</option>');
			});
			select.trigger('change');
		});
});


$(document).on("click", ".deletar", function (e) {
	e.preventDefault();

	let obj = $(this);
	let url = $(obj).data("url");
	let id = $(obj).data("id");
	let tableDataTable = $(".table-exibe").DataTable();

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
			url,
			null,
			'text',
			{id: id},
			function (ret) {
				ret = JSON.parse(ret);

				swal(
					ret["titulo"],
					ret["text"],
					ret["class"]
				).catch(swal.noop);

				if(!is_empty(ret["bol"], 1)) {
					tableDataTable.draw();
				}

				toggleLoading();
			}
		);
	}).catch(swal.noop);
});

async function botaoItem(elemento){
	toggleLoading();

	const id = $(elemento).data('id');
	const url = $(elemento).data('url');
	const notification = $(elemento).data('notification');

	if ($(elemento).data('idnota')){
		$("#modalAtividades .modal-footer").prepend('<div>' + l['*osArquivosDanfe/DacteVinculadosÀNotaFiscalSerãoEnviadosAoSap'] + '</div>');
	}

	await $.post(url, {id:id, ...tokenCsrf}, function(retorno){

		// remove o ícone da notificação
		$(".atividades-notification-icon.notification-icon-" + id).remove();

		if ($("#notificacoes").length && notification === true) {
			let valorAtual = parseInt($("#notificacoes").text());
			let novoValor = valorAtual - 1;
			$("#notificacoes").text(novoValor);

			if (novoValor === 0) {
				$("#notificacoes").remove();
				$("#atualizacoes").remove();
			} else {
				let textoAtualizacoes = novoValor + (novoValor === 1 ? " " + l['novaAtualização'] : " " + l['novasAtualizações']);
				$("#atualizacoes").text(textoAtualizacoes);
			}
		}

		if (is_empty(retorno, true)){
			swal({
				title: l["atenção!"],
				text: l["nenhumRegistroEncontrado"],
				type: "warning",
				showCancelButton: false,
				confirmButtonColor: '#3085d6'
			});

			return;
		}

		$('#modalAtividades input[name="id"]').val(id);
		$('#modalAtividades input[name="nomeTemplate"]').val(retorno.nomeTemplate);
		$('#modalAtividades input[name="descricao"]').val(retorno.descricao);

		$('#modalAtividades select[name="tipo"]').select2Reset();
		$('#modalAtividades select[name="tipo"]').data('init', JSON.parse('{"id":"'+ retorno.idTipoErp +'","text":"'+ retorno.nomeTipo +'"}'));
		$('#modalAtividades select[name="tipo"]').select2Ajax();

		$('#modalAtividades select[name="assunto"]').append('<option value="' + retorno.idAssuntoErp + '"> ' + retorno.nomeAssunto + ' </option>');

		$('#modalAtividades select[name="lista"]').select2Reset();
		$('#modalAtividades select[name="lista"]').data('init', JSON.parse('{"id":"'+ retorno.idListaDestinatarioErp +'","text":"'+ retorno.nomeLista +'"}'));
		$('#modalAtividades select[name="lista"]').select2Ajax();

		$('#modalAtividades input[name="horaInicio"]').val(retorno.horaInicio);
		$('#modalAtividades input[name="horaFinal"]').val(retorno.horaFinal);

		$('#modalAtividades input[name="prazoFinal"]').val(retorno.prazoFinal);

		$('#select-recorrencia').val(retorno.recorrencia).trigger('change');

		$('#modalAtividades input[name="repetir"]').val(retorno.repetir);

		if (retorno.userTemplate !== '1') {
			$(".btn-success.salvar").hide();
		}

		$('#modalAtividades').modal('show');
	});

	toggleLoading();
}

function pesquisaPersonalizada() {
	if ($("#pesquisa-usuario").length > 0) {
		let __acaoAtualizaDataTable = function () {
			const ref_table_search = ".table-exibe";

			let usuario = $("select#pesquisa-usuario");
			let url_table = "";
			let gets_url = "";
			let dataTable = null;

			$(ref_table_search).each(function () {
				if ($.fn.DataTable.isDataTable(this)) {
					dataTable = $(this).DataTable();
					dataTable.clear();
					dataTable.destroy();
				}
			});
			url_table = $(ref_table_search).data("url_principal");

			// Adiciona o valor do select de usuario
			if (!is_empty($(usuario).val(), 1)) {
				if (!is_empty(gets_url, 1)) {
					gets_url += "&";
				}
				gets_url += "usuario=" + $(usuario).val();
			}

			if (!is_empty(gets_url, 1)) {
				url_table += "?" + gets_url;
			}
			gets_url = null;

			$(ref_table_search).data("url_ajax", url_table);
			allTables();
		}

		$("select#pesquisa-usuario").off("select2:select");
		$("select#pesquisa-usuario").on("select2:select", function () {
			__acaoAtualizaDataTable();
		});

		$("select#pesquisa-situacao").off("select2:select");
		$("select#pesquisa-situacao").on("select2:select", function () {
			__acaoAtualizaDataTable();
		});

		__acaoAtualizaDataTable();
	}
}

criaSelect();
salvar();
pesquisaPersonalizada();