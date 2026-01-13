// Função para adicionar mensagens no modal
function addMessage(jsonMessage, selector = '.container-msg'){
	const htmlContent = isOldLayout
		? `	<div class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX" role="alert">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				${jsonMessage.msg}
			</div>`
		: `	<div class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX d-flex flex-wrap pb-5" role="alert">
				<a class="close text-decoration-none pe-4 col-12 d-flex justify-content-end mb-3" data-bs-dismiss="alert" aria-label="close" style="cursor: pointer;">
					<i class="fa-solid fa-xmark"></i>
				</a>
				<p class="col-12 text-center">
					${jsonMessage.msg}
				</p>
			</div>
		</div>`;
	$(selector).html(htmlContent);
}

function removeMessage() {
	$('.container-msg').html('');
	$('.container-msg-modal').html('');
}

function limpaCamposModal() {
	$('#modalAtividades input[name="idAtividade"]').val('');
	$('#modalAtividades select[name="idTemplate"]').empty().trigger('change');
	$('#modalAtividades input[name="idnotafiscais"]').val('');
	$('#modalAtividades input[name="idnotafiscaisservico"]').val('');
	$('#modalAtividades input[name="observacoes"]').val('');
	$('#modalAtividades textarea[name="conteudo"]').val('');
	$("div#documentos_anexo").empty();
	$(".btn-success.salvar").show();
}

function criaSelect() {
	$('.select_atividade').select2Ajax();
	$('.select_atividade').data('init', '');

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
		createFieldAnexos();
		$('#modalAtividades .titulo-modal').html(l['Cadastrar']);
		$('#modalAtividades .titulo-modal').attr('is-add', 1);
		$("#control-template").show();
		$('#modalAtividades').modal('show');
	});


	$('#modalAtividades .salvar').click(async function(){
		toggleLoading();

		// tratativa para o envio de anexos
		let saveAttachment = [];

		if($(".preview-doc").length > 0) {
			$(".preview-doc").each(function () {
				saveAttachment.push({
					nomeArquivo: $(this).find(".file-name").val(),
					anexo: $(this).find(".file-blob").val(),
					id: $(this).find(".file-id").val(),
				});
			});
		}
		// tratativa para o envio de anexos

		const action = $(this).data('action');
		let is_add = $('#modalAtividades .titulo-modal').attr('is-add');

			await $.post(action, {
				idAtividade:	$('#modalAtividades input[name="idAtividade"]').val(),
				idTemplate:		$('#modalAtividades select[name="idTemplate"]').val(),
				idnotafiscais:	$('#modalAtividades input[name="idnotafiscais"]').val(),
				idnotafiscaisservico: $('#modalAtividades input[name="idnotafiscaisservico"]').val(),
				observacoes:	$('#modalAtividades input[name="observacoes"]').val(),
				conteudo:		$('#modalAtividades textarea[name="conteudo"]').val(),
				isAdd: 			is_add,
				anexos:			saveAttachment,
				...tokenCsrf
			}, function(retorno){
				if (retorno.class == 'success'){
					let tableDataTable = $(".table-exibe").DataTable();
					tableDataTable.draw();

					$("#documentos_anexo").html("");
					createFieldAnexos();

					limpaCamposModal();
				}

				addMessage(retorno, '.container-msg-modal');

				toggleLoading();
			});

	});
}

$(document).on("click", ".deletar", function (e) {
	e.preventDefault();

	let obj = $(this);
	let url = $(obj).data("url");
	let idAtv = $(obj).data("id");
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
			{idAtividade: idAtv},
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
	const status = $(elemento).data('status');
	const notification = $(elemento).data('notification');

	$("#control-template").hide();
	if (status == 'a'){
		$("#modalAtividades .btn-success.salvar").hide();
		$('#modalAtividades .titulo-modal').html(l['visualizando']);
	}

	if ($(elemento).data('idnota')){
		$("#modalAtividades .modal-footer").prepend('<div>' + l['*osArquivosDanfe/DacteVinculadosÀNotaFiscalSerãoEnviadosAoSap'] + '</div>');
	}

	await $.post(url, {idAtividade:id, ...tokenCsrf}, function(retorno){

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

		if (is_empty(retorno['dados'], true)){
			swal({
				title: l["atenção!"],
				text: l["nenhumRegistroEncontrado"],
				type: "warning",
				showCancelButton: false,
				confirmButtonColor: '#3085d6'
			});

			return;
		}

		$('#modalAtividades input[name="idAtividade"]').val(id);
		$('#modalAtividades input[name="observacoes"]').val(retorno['dados'].observacoes);
		$('#modalAtividades textarea[name="conteudo"]').val(retorno['dados'].conteudo);

		// start - checa se tem anexos
		$("div#documentos_anexo").html('<div class="preview-docs-zone"></div>');
		if(!is_empty(retorno['anexos'], 1)) {
			for(anexo of retorno['anexos']) {
				let img = `
					<img src="${anexo.dataTypeAnexoAtv}${anexo.anexoAtv}"
						data-name="${anexo.nomeAnexoAtv}${anexo.extensaoAnexoAtv}"
						data-id="${anexo.idAnexoPortal}" />
					`;
				$(".preview-docs-zone").append(img);
			}
		}
		// end - checa se tem anexos
		createFieldAnexos(1);

		if (retorno['dados'].solicitanteAtv !== '1') {
			$(".btn-success.salvar").hide();
		}

		$('#modalAtividades').modal('show');
	});

	toggleLoading();
}

function pesquisaPersonalizada() {
	if ($("#pesquisa-situacao").length > 0) {

		// função para validar formato de data dd/mm/yyyy
		function isValidDate(dateString) {
			const regex = /^\d{2}\/\d{2}\/\d{4}$/;
			if (!regex.test(dateString)) return false;

			const parts = dateString.split('/');
			const day = parseInt(parts[0], 10);
			const month = parseInt(parts[1], 10);
			const year = parseInt(parts[2], 10);

			if (month < 1 || month > 12) return false;
			if (day < 1 || day > 31) return false;
			if (year < 1000 || year > 9999) return false;

			// verifica os dias de fevereiro
			if (month === 2) {
				const isLeap = (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
				if (day > (isLeap ? 29 : 28)) return false;
			}

			// verifica os dias de abril, junho, setembro e novembro
			if ([4, 6, 9, 11].includes(month) && day > 30) return false;

			return true;
		}

		// não permite que o usuário insira valores manualmente no campo, apenas apagar
		$(document).ready(function () {
			$('#geral-dt_inicio, #geral-dt_fim').on('keyup', function (event) {
				if (event.which == 8) $(this).val(''); // 8 é o código da tecla "backspace"
			}).on('keypress', function (event) {
				if (event.which != 8) event.preventDefault();
			});
		});

		let __acaoAtualizaDataTable = function () {
			const ref_table_search = ".table-exibe";

			let usuario = $("select#pesquisa-usuario");
			let situacao = $("select#pesquisa-situacao");
			let dataInicio = $("#geral-dt_inicio").val();
			let dataFinal = $("#geral-dt_fim").val();
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

			// Adiciona o valor do select de situacao
			if (!is_empty($(situacao).val(), 1)) {
				if (!is_empty(gets_url, 1)) {
					gets_url += "&";
				}
				gets_url += "situacao=" + $(situacao).val();
			}

			// Verifica se ambos os campos de data estão preenchidos e no formato correto
			if (dataInicio && dataFinal && isValidDate(dataInicio) && isValidDate(dataFinal)) {
				if (!is_empty(gets_url, 1)) {
					gets_url += "&";
				}
				gets_url += "dataInicio=" + dataInicio + "&dataFinal=" + dataFinal;
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

		// ----- Campos data inicio -----
		$(function () {
			$("#geral-dt_inicio, #geral-dt_fim").datepicker({
				dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
				dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
				dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
				monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
				monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: 'dd/mm/yy',
				onSelect: function () {
					let dataInicio = $("#geral-dt_inicio").val();
					let dataFinal = $("#geral-dt_fim").val();

					if (dataInicio && dataFinal) {
						__acaoAtualizaDataTable();
					}
				}
			});
		});

		// ----- Campos data fim -----

		__acaoAtualizaDataTable();

	}
}

/**
 * Função para tratativa de upload, atualização e remoção de anexos ao criar/editar
 */
const dataViews = $("div#modalAtividades");
function createFieldAnexos(isView) {
	recriar($("div#documentos_anexo"));
	$('div#documentos_anexo').allUpload(
		'conteudo-anexos_name[]',
		'conteudo-anexos_blob[]',
		function (obj) {
			if(is_empty(isView, 1)) {
				let name = $(obj).parents('.preview-doc').find(".file-name").val();
				let src = $(obj).parents('.preview-doc').find('.doc-zone img').prop('src');

				$(".modal-visualiza_anexo .modal-content .modal-title").text(name);
				$(".modal-visualiza_anexo .modal-content .modal-body").html('<img src="' + src + '" style="max-width:100%; margin:0 auto; display: block;" />');
				$('.modal-visualiza_anexo').modal('show');
			}
			return false;
		},
		'.preview-docs-zone',
		{
			"textUpload": $("div#modalAtividades").data("text_upload"),
			"textVisualize": $("div#modalAtividades").data("text_visualize_upload"),
			"noDocsText": $("div#modalAtividades").data("text_no_docs_upload"),
			"obsText": $("div#modalAtividades").data("text_obs_upload"),
		},
		function (obj) {
			let idDoc = $(obj).data("id");
			if(is_empty(idDoc, 1)) {
				idDoc = "";
			}
			$(obj).append('<input class="noEffect file-id" style="display: none;" name="conteudo-anexos_id_interno[]" value="' + idDoc + '" />');

			$(obj).append('<div class="tools-name-doc">' + ($($(obj).find(".file-name")).val()) + '</div>');

			let srcCheck = $($(obj).find(".file-blob")).val().toLowerCase().split(";")[0];
			if (!srcCheck.includes("image")) {
				let fileIcon = "";
				if (srcCheck.includes("text")) {
					fileIcon = isOldLayout ? 'fa fa-file-text-o' : 'fa-regular fa-file-lines';
				} else if(srcCheck.includes("excel")) {
					fileIcon = isOldLayout ? 'fa fa-file-excel-o' : 'fa-regular fa-file-excel';
				} else if(srcCheck.includes("pdf")) {
					fileIcon = isOldLayout ? 'fa fa-file-pdf-o' : 'fa-regular fa-file-pdf';
				} else if(srcCheck.includes("word")) {
					fileIcon = isOldLayout ? 'fa fa-file-word-o' : 'fa-regular fa-file-word';
				} else {
					fileIcon = isOldLayout ? 'fa fa-eye-slash' : 'fa-regular fa-eye-slash';
				}

				$($(obj).find(".text-zone")).html("<i class='" + fileIcon + "' style='font-size: 10em;'></i>");
				fileIcon = null;

				if(is_empty(isView, 1)) {
					$($(obj).find(".action-visualize")).remove();
				}
			}
			srcCheck = null;

			if(!is_empty(isView, 1)) {
				const url = dataViews.data("url_baixar_anexos");
				let id = $(obj).data('id');
				$($(obj).find(".action-visualize")).attr("href", (url + id));
				$($(obj).find(".action-visualize")).attr("target", "_blank");

				$($(obj).find(".action-visualize")).html($(".data_views").data("text_download_upload"));
			}
		}
	);
	if(!is_empty(isView, 1)) {
		$('div#documentos_anexo .link-adiciona-files').remove();
	}
}

criaSelect();
salvar();
pesquisaPersonalizada();