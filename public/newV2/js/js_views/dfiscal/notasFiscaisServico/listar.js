function acoesBotoes() {
	let __acaoPadrao = function (obj, objEnvio) {
		let id = $(obj).data('id');
		let url = $(obj).data('url');
		let tableDataTable = $(obj).parents('.table-exibe').DataTable();
		if(is_empty(id, 1) || is_empty(url, 1)) return;
		if(is_empty(objEnvio, 1)) {
			objEnvio = {};
		}
		objEnvio['id'] = id;

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
			let tableDataTable = $(obj).parents('.table-exibe').DataTable();
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
		__acaoPadrao($(this),{acao: 'desativar'});
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

		$('#modalAtividades input[name="idnotafiscaisservico"]').val(idNota);

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

	$('#config-tipo_cadastro').off('click');
	$('#config-tipo_cadastro').on('click', function () {
		$('.modal-tipo_cadastro').modal('toggle');
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

	$("select.select_filiais").select2();
	$("select.select_filiais").data('init', '');

	// $("select#filiaisRelatorio").select2Ajax();
	// $("select#filiaisRelatorio").data('init', '');


}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";
		let select_status = $("select#pesquisa-status");
		let select_configs = $("select#pesquisa-configs");
		const ref_btn_relatorio = ".btn-generate-excel";
		let select_filiais = $("select#select_filiais");
		let dataInicial = $("#dataInicial");
		let dataFinal = $("#dataFinal");
		let pesquisaStatus = $("#pesquisa-status");
		let pesquisaConfigs = $("#pesquisa-configs");
		let filiaisFiltro = $("#select_filiais");

		let url_table = "";
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
		let url_relatorio = $(ref_btn_relatorio).data("url_principal");
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
			gets_url += "dataInicial=" + $(dataInicial).val();
		}
		if(!is_empty($(dataFinal).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "dataFinal=" + $(dataFinal).val();
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

		$(ref_table_search).data("url_ajax", url_table);
		$(ref_btn_relatorio).attr("href", url_relatorio);
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
acoesBotoes();