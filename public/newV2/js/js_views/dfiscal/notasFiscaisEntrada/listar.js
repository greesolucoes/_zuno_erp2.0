function init() {
	//$('[data-toggle="popover"]').popover()
}
function showModals() {
}

function acoesBotoes() {
	let __acaoPadrao = function (obj, objEnvio, msgAux=null) {
		let id = $(obj).data('id');
		let url = $(obj).data('url');
		let tableDataTable = $('.table-exibe').DataTable();
		if(is_empty(id, 1) || is_empty(url, 1)) return;
		if(is_empty(objEnvio, 1)) {
			objEnvio = {};
		}
		objEnvio['id'] = id;

		swal({
			title: l["desejaContinuar?"],
			html: is_empty(msgAux,0) ? '' : msgAux,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["continuar!"],
			cancelButtonText: l["fechar!"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', objEnvio, function (ret) {
				try{
					ret = JSON.parse(ret);

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).then(function(){
						// usado pelos botões superiores de ação nas telas de visualização e cadastro
						if(!is_empty($('.data_views').data('atualizar_pagina_apos_envio'), 1) === true) {
							window.location.reload();
						}
					}).catch(swal.noop);

					tableDataTable.draw();
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
		}).catch(swal.noop);
	}

	let __acaoWithDescription = function (obj, objEnvio) {
		$('.modal_reject #label_reject').html($(obj).attr("title"));
		$('.modal_reject .descricao_reject textarea#motivo_text').val('');
		$(".modal_reject button.reject").attr("title", $(obj).attr("title"));
		$(`.modal_reject button.reject ${isOldLayout ? '' : '.reject-button-text'}`).text($(obj).attr("title"));

		$('.modal_reject').modal('toggle');
		$('.modal_reject button.reject').off('click');
		$('.modal_reject button.reject').on("click", function (e) {
			let motivo         = $(this).parents('.descricao_reject').find('textarea#motivo_text').val().trim();
			let url            = $(obj).data('url');
			let id             = $(obj).data('id');
			let tableDataTable = $('.table-exibe').DataTable();
			if(is_empty(motivo, 1)) motivo = "";
			if(is_empty(url, 1) || is_empty(id, 1)) return;
			if(is_empty(objEnvio, 1)) {
				objEnvio = {};
			}
			objEnvio['id'] = id;
			objEnvio['motivo'] = motivo;

			swal({
				title: l["desejaContinuar?"],
				text: "",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["fechar!"]
			}).then(function () {
				toggleLoading();
				ajaxRequest(true, url, null, 'text', objEnvio, function (ret) {
					try{
						ret = JSON.parse(ret);

						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);
						if(!is_empty($('.data_views').data('atualizar_pagina_apos_envio'), 1) === true) {
							window.location.reload();
						}
						tableDataTable.draw();
						$('.modal_reject').modal('toggle');
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
			}).catch(swal.noop);
		});
	}

	$(".aprovar").off('click');
	$(".aprovar").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".upload").off('click');
	$(".upload").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".refaz_depara").off('click');
	$(".refaz_depara").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".desativar").off('click');
	$(".desativar").on('click', function () {
		let msg= null;
		if(!is_empty($(this).data('is_sefaz'),1)) msg= '<span style="color: tomato;">' + l["essaNotaNãoPoderáSerReativadaPoisÉDeOrigemSefaz"] + '</span>';
		__acaoPadrao($(this),{acao: 'desativar'}, msg);
	});
	$(".ativar").off('click');
	$(".ativar").on('click', function () {
		__acaoPadrao($(this),{acao: 'ativar'});
	});

	$(".show_modal_reject").off('click');
	$(".show_modal_reject").on('click', function () {
		__acaoWithDescription($(this));
	});

	$('.show_modal_motivo').off('click');
	$('.show_modal_motivo').on('click', function (e) {
		$('.modal_motivo #label_motivo').html($(this).attr("title"));
		$('.modal_motivo .descricao_motivo').html($(this).parents('td').find('.descricao_rejeicao').html());
		$('.modal_motivo').modal('toggle');
	});

	$('.show_modal_atividade').off('click');
	$('.show_modal_atividade').on('click', function (e) {

		const idNota = $(this).data('idnota');
		const numNota = $(this).data('numnota');
		const chave = $(this).data('chave');

		$('#modalAtividades input[name="idnotafiscais"]').val(idNota);

		let observacoes = '';
		if (numNota && chave) {
			observacoes = 'N.º NFe - ' + numNota + ' / ' + l['chave'] + ' - ' + chave;
		} else if (numNota) {
			observacoes = 'N.º NFe - ' + numNota;
		} else if (chave) {
			observacoes = l['chave'] + ' - ' + chave;
		}
		$('#modalAtividades input[name="observacoes"]').val(observacoes);

		$('#modalAtividades .titulo-modal').html($(this).attr("title"));
		$('#modalAtividades .titulo-modal').attr('is-add', 0);
		$('#modalAtividades').modal('toggle');
	});

	$('#importar_danfes').off('click');
	$('#importar_danfes').on("click", function () {
		$('.modal-download_danfes').modal('toggle');
	});

	$('#importar_xmls').off('click');
	$('#importar_xmls').on("click", function () {
		$('.modal-download_xmls').modal('toggle');
	});

	$('#gerarRelatorioFilial').off('click');
	$('#gerarRelatorioFilial').on("click", function (e) {
		$('.modal_relatorio_filiais').modal('toggle');
	});

	$('#download_danfes').off('click');
	$('#download_danfes').on("click", function () {
		let url = $(".datas_views").data("url_download_danfes");
		$.redirect(url, {
			'deData': $($(".modal-download_danfes").find(".de-para-inicio")).val(),
			'paraData': $($(".modal-download_danfes").find(".de-para-final")).val(),
			'filiais': $($(".modal-download_danfes").find(".select_filiais")).val(),
			...tokenCsrf
		}, "POST", "_blank");
	});

	$('#download_xmls').off('click');
	$('#download_xmls').on("click", function () {
		let url = $(".datas_views").data("url_download_xmls");
		$.redirect(url, {
			'deData': $($(".modal-download_xmls").find(".de-para-inicio")).val(),
			'paraData': $($(".modal-download_xmls").find(".de-para-final")).val(),
			'filiais': $($(".modal-download_xmls").find(".select_filiais")).val(),
			...tokenCsrf
		}, "POST", "_blank");
	});

	$('#config-tipo_cadastro').off('click');
	$('#config-tipo_cadastro').on('click', function () {
		$('.modal-tipo_cadastro').modal('toggle');
	});

	$('#importacao-chave').off("keyup");
	$('#importacao-chave').on("keyup",function(e){
		e.preventDefault();
		if(e.keyCode === 13) {
			let obj = $(this);
			let url = $(obj).data("url");
			let dados = {
				chave: $(obj).val(),
				tipoImportacao: 'nfe',
			};

			toggleLoading();
			ajaxRequest(true, url, null, 'text', dados, function (ret) {
				try {
					ret = JSON.parse(ret);
					if (!is_empty(ret['bol'], 1)) {
						$(obj).val("");
					}

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);

					toggleLoading();
				} catch (err) {
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			});
		}
	});

	$('.gerarRelatorio').off('click');
	$('.gerarRelatorio').on('click', function (){
		let url = $(".datas_views").data("url_gerar_relatorio");
		let obj = {
			'dataDe': $('#data_de-gerar_relatorio').val(),
			'dataPara': $('#data_para-gerar_relatorio').val(),
			'idFilial': $('#filiaisRelatorio').val(),
			...tokenCsrf
		}
		if(!is_empty(obj.dataDe, 1)) {
			obj.dataDe = moment(obj.dataDe, $('.datas_views').data('format_date')).format('YYYY-MM-DD');
		} else {
			obj.dataDe = null;
		}
		if(!is_empty(obj.dataPara, 1)) {
			obj.dataPara = moment(obj.dataPara, $('.datas_views').data('format_date')).format('YYYY-MM-DD');
		} else {
			obj.dataPara = null;
		}

		if(
			is_empty(obj.dataDe, 0) &&
			is_empty(obj.dataPara, 0)
		) {
			swal(
				l['erro!'],
				l['camposObrigatorios'] + l[":'DataDe'e'DataPara'"],
				"error"
			);
			return;
		}
		if(obj.dataDe > obj.dataPara) {
			swal(
				l['erro!'],
				l['dataDeDeveSerMenorQueDataPara'],
				"error"
			);
			return;
		}


		$.redirect(url, obj, "GET", "_blank");

		// window.location.href = `${url}?dataDe=${obj.dataDe}&dataPara=${obj.dataPara}&idFilial=${obj.idFilial}`;
		$('.modal_relatorio_filiais').modal('toggle');
	});

	$('select#select_acao_sefaz').off('select2:select');
	$('select#select_acao_sefaz').on('select2:select', function (){
		if ($(this).val() === 'nao_realizada') {
			$(".div_justificativa").removeClass("ocultar");
		} else {
			$(".div_justificativa").addClass("ocultar");
		}
	});

	$('select#select_acao_sefaz').off('select2:unselect');
	$('select#select_acao_sefaz').on('select2:unselect', function (){
		$("#justificativas").val("");
	});

	$('.realizar_acao_manifesto').off('click');
	$('.realizar_acao_manifesto').on('click', function (){
		let id = $(this).data('id');
		$('.enviar_acao_manifesto').data('id', id);
		$('.enviar_acao_manifesto').data('url', $(this).data('url'));

		toggleLoading();
		$(".conteudo_logs_manifesto").html("");
		ajaxRequest(true, $(this).data('url_logs'), null, 'text', {
			id_nfe: id
		}, function (ret) {
			try {
				ret = JSON.parse(ret);
				$(".conteudo_logs_manifesto").html(ret);
				$('.modal_opcoes_manifesto').modal('toggle');
				toggleLoading();
			} catch (err) {
				$(".conteudo_logs_manifesto").html("");
				$('.modal_opcoes_manifesto').modal('toggle');
				forceToggleLoading(0);
			}
		});
	});

	$('.enviar_acao_manifesto').off('click');
	$('.enviar_acao_manifesto').on('click', function (){
		__acaoPadrao($(this), {"tipoManifesto": $("#select_acao_sefaz").val(), "motivo": $("#justificativas").val()});
		$("#select_acao_sefaz").val("0").trigger('change').trigger('select2:unselect');
		$("#justificativas").val("");
	});

	$('.realizar_ciencia').off('click');
	$('.realizar_ciencia').on('click', function (){
		__acaoPadrao($(this), {"tipoManifesto": "ciencia", "motivo": null});
	});

	$('.desconhecer_operacao').off('click');
	$('.desconhecer_operacao').on('click', function (){
		__acaoWithDescription($(this), {"tipoManifesto": "desconhecimento"});
	});

	$('.fechar_modal_logs').off('click');
	$('.fechar_modal_logs').on('click', function (){
		$('.modal_logs_manifesto').modal('toggle');
	});

	let __acaoRequest = function (obj, objEnvio) {
		let url = $(obj).data('url');
		if(is_empty(url, 1)) {
			return;
		}
		if(is_empty(objEnvio, 1)) {
			objEnvio = {};
		}

		Object.assign(objEnvio, tokenCsrf);
		$.redirect(url, objEnvio, "POST", "_blank");
	}
	let __acaoRequestDoc = function (obj) {
		let objEnvio = {};
		objEnvio['id'] = $(obj).data('id');
		objEnvio['tipo'] = $(obj).data('tipo');
		if(is_empty(objEnvio['id'], 1) || is_empty(objEnvio['tipo'], 1)) {
			return;
		}

		__acaoRequest($(obj), objEnvio);
	}

	$(".imprimir_da").off('click');
	$(".imprimir_da").on('click', function () {
		__acaoRequestDoc($(this));
	});

	$(".imprimir_xml").off('click');
	$(".imprimir_xml").on('click', function () {
		__acaoRequestDoc($(this));
	});

}

function criaCostumizacoes() {
	$("select#pesquisa-status").select2Simple();
	$("select#pesquisa-configs").select2Simple();

	$("select#select_acao_sefaz").select2Simple();

	$("select#filiaisRelatorio").select2Ajax();
	$("select#filiaisRelatorio").data('init', '');

	$("select.select_filiais").select2();
	$("select.select_filiais").data('init', '');
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";
		const ref_btn_relatorio = ".btn-generate-excel";

		let select_status = $("select#pesquisa-status");
		let select_configs = $("select#pesquisa-configs");
		let select_filiais = $("select#select_filiais");
		let dataInicial = $("#dataInicial");
		let dataFinal = $("#dataFinal");
		let pesquisaStatus = $("#pesquisa-status");
		let pesquisaConfigs = $("#pesquisa-configs");
		let filiaisFiltro = $("#select_filiais");
		let url_table = "";
		let url_relatorio = "";
		let gets_url = "";
		let dataTable = null;

		$(ref_table_search).each(function (){
			if($.fn.DataTable.isDataTable(this)) {
				dataTable = $(this).DataTable();
				dataTable.clear();
				dataTable.destroy();
			}
		});
		url_table = $(ref_table_search).data("url_principal");
		url_relatorio = $(ref_btn_relatorio).data("url_principal");
		if(!is_empty($(select_status).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "status=" + $(select_status).val();
		}
		if(!is_empty($(select_configs).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "configs=" + $(select_configs).val();
		}
		if(!is_empty($(select_filiais).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "filiais=" + $(select_filiais).val();
		}
		if(!is_empty($(dataInicial).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "dataInicial=" + strFormatDate($(dataInicial).val(), configLocation.formatDatePicker,"DD/MM/YYYY");
		}
		if(!is_empty($(dataFinal).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "dataFinal=" + strFormatDate($(dataFinal).val(), configLocation.formatDatePicker,"DD/MM/YYYY");
		}

		if(!is_empty($(pesquisaStatus).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "pesquisaStatus=" + $(pesquisaStatus).val();
		}

		if(!is_empty($(pesquisaConfigs).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "pesquisaConfigs=" + $(pesquisaConfigs).val();
		}

		if(!is_empty($(filiaisFiltro).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "filiaisFiltro=" + $(filiaisFiltro).val();
		}

		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
			url_relatorio += "?" + gets_url;
		}
		gets_url = null;

		$(ref_btn_relatorio).attr("href", url_relatorio);
		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	$("select#pesquisa-status").off("select2:select");
	$("select#pesquisa-status").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	$("select#pesquisa-status").off("select2:unselect");
	$("select#pesquisa-status").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__acaoAtualizaDataTable();
	});

	$("select#pesquisa-configs").off("select2:select");
	$("select#pesquisa-configs").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	$("select#pesquisa-configs").off("select2:unselect");
	$("select#pesquisa-configs").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__acaoAtualizaDataTable();
	});

	// ao clica no botao deverá fazer a busca
	$('#search-table').off("click");
	$('#search-table').on("click",function(e){
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

function contaCharJustificativa() {
	var maxLenObs = 254;
	var char = 254;
	$('#justificativas').keyup(function () {
		var len = $(this).val().length;
		if (len > maxLenObs) {
			char = 0;
			$(this).val($(this).val().substring(0, maxLenObs));
		} else {
			char = maxLenObs - len;
		}
		$('p#numChars').text(char + ' ' + l['caracteresRestantes']);
	});

	if (!is_empty($('#justificativas').val())) {
		char = maxLenObs - $('#justificativas').val().length;
	}

	$('p#numChars').text(char + ' ' + l['caracteresRestantes']);
}

$(document).ready(function(){
	if (!is_empty($(".data_views").data("pesquisa_status"), 1)) {
		$("#pesquisa-status").val($(".data_views").data("pesquisa_status")).trigger('change').trigger('select2:select');
	}

	if (!is_empty($(".data_views").data("pesquisa_configs"), 1)) {
		$("#pesquisa-configs").val($(".data_views").data("pesquisa_configs")).trigger('change').trigger('select2:select');
	}

	if (!is_empty($(".data_views").data("pesquisa_filiais"), 1)) {
		let arrFil;
		if ($(".data_views").data("pesquisa_filiais").toString().includes(',')) {
			arrFil = $(".data_views").data("pesquisa_filiais").split(',');
		} else {
			arrFil = [$(".data_views").data("pesquisa_filiais")];
		}
		$("#select_filiais").val(arrFil).trigger('change').trigger('select2:select');
		$("#search-table").trigger("click");
	}
});

criaCostumizacoes();
pesquisaPersonalizada();
contaCharJustificativa();
acoesBotoes();
showModals();
init();