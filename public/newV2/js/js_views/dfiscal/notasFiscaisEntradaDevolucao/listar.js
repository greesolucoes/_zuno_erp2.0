function showModals() {
}

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

	$('#config-tipo_cadastro').off('click');
	$('#config-tipo_cadastro').on('click', function () {
		$('.modal-tipo_cadastro').modal('toggle');
	});

	$('.gerarRelatorio').off('click');
	$('.gerarRelatorio').on('click', function (){
		let obj = {
			'dataDe': $('#dataDeGerarRelatorio').val(),
			'dataPara': $('#dataParaGerarRelatorio').val(),
			'idFilial': $('#filiaisRelatorio').val(),
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
		} else if(dateBrToDate(obj.dataDe) > dateBrToDate(obj.dataPara)) {
			swal(
				l['erro!'],
				l['dataDeDeveSerMenorQueDataPara'],
				"error"
			);
		} else {
			let url = $(".datas_views").data("url_gerar_relatorio");

			window.location.href = `${url}?dataDe=${dateBrToDate(obj.dataDe)}&dataPara=${dateBrToDate(obj.dataPara)}&idFilial=${obj.idFilial}`;
			$('.modal_relatorio_filiais').modal('toggle');
		}
	});

	// desativa a Dev. NF Entrada
	$(".desativar").off('click');
	$(".desativar").on('click', function () {
		__acaoPadrao($(this));
	});

	// envia para o SAP
	$(".upload").off('click');
	$(".upload").on('click', function () {
		__acaoPadrao($(this));
	});
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";

		let dataInicial = $("#dataInicial");
		let dataFinal = $("#dataFinal");
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

		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	// ao clica no botao deverá fazer a busca
	$('#search-table').off("click");
	$('#search-table').on("click",function(e){
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

pesquisaPersonalizada();
acoesBotoes();
showModals();