let valuesImportar = [];
let tipoDocsImportar = [];
function acoesBotoes() {
	/**
	 * acao para verificar ou desativar o documento
	 * @param obj
	 * @param objEnvio
	 * @private
	 */
	let __acaoPadrao = function (obj, objEnvio) {
		let id = $(obj).data('id');
		let url = $(obj).data('url');
		let tableDataTable = $($(".dataTables_scrollBody").find('.table-exibe')).DataTable();
		if(is_empty(objEnvio, 1)) {
			objEnvio = {};
		}
		if(is_empty(url, 1)) return;
		if(is_empty(id, 1) && is_empty(objEnvio['id'], 1)) return;
		if(!is_empty(id, 1)) {
			objEnvio['id'] = id;
		}

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

					valuesImportar = [];
					if(ret['bol']) {
						$('.modal-upload').modal('hide');
					}
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
	// funcao exclusiva para enviar o documento para o SAP usando ajaxForm
	let __acaoPadraoEnviar = function (obj) {
		let url = $(obj).data('url');
		let tableDataTable = $($(".dataTables_scrollBody").find('.table-exibe')).DataTable();
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
			// envia o form
			var form = $('#form_reenviar_cte');
			form.ajaxSubmit({
				url: url,
				type: "POST",
				dataType: "json",
				success: function (ret) {
					swal(
						ret.titulo,
						ret.text,
						ret.class
					).catch(swal.noop);

					valuesImportar = [];
					tipoDocsImportar = [];
					if(ret.bol) {
						$('.modal-upload').modal('hide');
					}
					tableDataTable.draw();

					//Atualiza o token utilizado no form
					updateCsrfTokenForms();

					toggleLoading();
				},
				complete: function (dataComp) {
					// it's here if anyone needs to use the complete function :)
				},
				error: function () {
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
	let __acaoPreReenvio = function (obj, listCte, tipoDocsImportar, isVisualizar = false) {
		// cria a parte de anexos
		let __criaAnexos = function () {
			recriar($("div#documentos_anexo"));
			$('div#documentos_anexo').allUpload(
				'upload-anexos_name[]',
				'upload-anexos_blob[]',
				function (obj) {
					if(is_empty($("div.data_views").data("vizualizacao"), 1)) {
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
					"textUpload": $(".data_views").data("text_upload"),
					"textVisualize": $(".data_views").data("text_visualize_upload"),
					"noDocsText": $(".data_views").data("text_no_docs_upload"),
					"obsText": $(".data_views").data("text_obs_upload"),
				},
				function (obj) {
					let idDoc = $(obj).data("id");
					if(is_empty(idDoc, 1)) {
						idDoc = "";
					}
					$(obj).append('<input class="noEffect file-id" style="display: none;" name="upload-anexos_id_interno[]" value="' + idDoc + '" />');

					$(obj).append('<div class="tools-name-doc">' + ($($(obj).find(".file-name")).val()) + '</div>');

					let srcCheck = $($(obj).find(".file-blob")).val().toLowerCase().split(";")[0];
					if (!srcCheck.includes("image")) {
						let fileIcon = "";
						if (srcCheck.includes("text")) {
							fileIcon = isOldLayout ? 'fa fa-file-text-o' : 'fa-regular fa-file-lines';
						} else if (srcCheck.includes("excel")) {
							fileIcon = isOldLayout ? 'fa fa-file-excel-o' : 'fa-regular fa-file-excel';
						} else if (srcCheck.includes("pdf")) {
							fileIcon = isOldLayout ? 'fa fa-file-pdf-o' : 'fa-regular fa-file-pdf';
						} else if (srcCheck.includes("word")) {
							fileIcon = isOldLayout ? 'fa fa-file-word-o' : 'fa-regular fa-file-word';
						} else {
							fileIcon = isOldLayout ? 'fa fa-eye-slash' : 'fa-regular fa-eye-slash';
						}

						$($(obj).find(".text-zone")).html("<i class='" + fileIcon + "' style='font-size: 10em;'></i>");
						fileIcon = null;

						if(is_empty($("div.data_views").data("vizualizacao"), 1)) {
							$($(obj).find(".action-visualize")).remove();
						}
					}
					srcCheck = null;

					if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
						const url = $("div.data_views").data("url_baixar_anexos");
						let id = $(obj).data('id');
						$($(obj).find(".action-visualize")).attr("href", (url + id));
						$($(obj).find(".action-visualize")).attr("target", "_blank");

						$($(obj).find(".action-visualize")).html($(".data_views").data("text_download_upload"));
					}
				}
			);
			if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
				$('div#documentos_anexo .link-adiciona-files').remove();
			}
		};
		let objEnvio = {};
		let url = $(obj).data('url');
		let tipoDocumento = (!is_empty($(obj).data('tipo_doc')) ?  ' - ' + $(obj).data('tipo_doc') : ''); // pode ser 'CTe-OS' ou 'CTe' ou 'grupo'
		let tableDataTable = $($(".dataTables_scrollBody").find('.table-exibe')).DataTable();
		if(is_empty(listCte, 1) || is_empty(url, 1)) return;
		objEnvio['ids'] = listCte;
		if(!is_empty(tipoDocsImportar, 1)) {
			objEnvio['tipos'] = tipoDocsImportar;
		}

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

					// remove as linhas preenchidas da table de parcelas toda vez que abrir a modal
					$("table#upload-parcelas-tabela tbody tr:not(.ocultar)").remove();
					// limpo os totais de parcelas
					$('#upload-parcelas_total_geral').html('');
					// limpo campos de visualizacion
					$('#upload-idDocumento').val('[NOCODE]');
					$('#upload-valor').val('[NOCODE]');
					$('#upload-docNumNotaFiscal').val('[NOCODE]');
					$('#upload-idNotasFiscaisERP').val('[NOCODE]');
					$('#upload-hashKey').val('[NOCODE]');

					let __funRecriaSelect = function (obj, paramsUrl, init) {
						if ($(obj).hasClass("select2-hidden-accessible")){
							$(obj).select2('destroy');
						}

						$($(obj).find("option")).remove();

						$(obj).data("url", ($(obj).data("url_principal") + paramsUrl));
						$(obj).data("init", (!is_empty(init, 1) ? init : ""));

						$(obj).select2Ajax();
						$(obj).data("init", '');
					};

					__funRecriaSelect($("#upload-forma_pagamento"), listCte.join('/'), ret['initFormasPagamento']);
					if(!is_empty(ret['initDepositos'], 1)) {
						__funRecriaSelect($("#upload-deposito"), "", ret['initDepositos']);
					} else {
						$("#upload-deposito option").remove();
						$("#upload-deposito").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
					}
					if(!is_empty(ret['initTipoUtilizacaoProduto'], 1)) {
						__funRecriaSelect($("#upload-utilizacao"), "", ret['initTipoUtilizacaoProduto']);
					} else {
						$("#upload-utilizacao option").remove();
						$("#upload-utilizacao").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
					}
					if(!is_empty(ret['initRegraDistribuicao1'], 1) && $("#upload-centro_renda_1").length > 0) {
						__funRecriaSelect($("#upload-centro_renda_1"), "", ret['initRegraDistribuicao1']);
					} else {
						$("#upload-centro_renda_1 option").remove();
						$("#upload-centro_renda_1").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
					}
					if(!is_empty(ret['initRegraDistribuicao2'], 1) && $("#upload-centro_renda_2").length > 0) {
						__funRecriaSelect($("#upload-centro_renda_2"), "", ret['initRegraDistribuicao2']);
					} else {
						$("#upload-centro_renda_2 option").remove();
						$("#upload-centro_renda_2").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
					}
					if(!is_empty(ret['initRegraDistribuicao3'], 1) && $("#upload-centro_renda_3").length > 0) {
						__funRecriaSelect($("#upload-centro_renda_3"), "", ret['initRegraDistribuicao3']);
					} else {
						$("#upload-centro_renda_3 option").remove();
						$("#upload-centro_renda_3").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
					}
					if(!is_empty(ret['initRegraDistribuicao4'], 1) && $("#upload-centro_renda_4").length > 0) {
						__funRecriaSelect($("#upload-centro_renda_4"), "", ret['initRegraDistribuicao4']);
					} else {
						$("#upload-centro_renda_4 option").remove();
						$("#upload-centro_renda_4").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
					}
					if(!is_empty(ret['initRegraDistribuicao5'], 1) && $("#upload-centro_renda_5").length > 0) {
						__funRecriaSelect($("#upload-centro_renda_5"), "", ret['initRegraDistribuicao5']);
					} else {
						$("#upload-centro_renda_5 option").remove();
						$("#upload-centro_renda_5").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
					}
					$("#upload-observacao").val("");

					let aux = null;
					if(!is_empty(ret['dataLancamento'], 1)) {
						aux = ret['dataLancamento'].split('-');
						$('#upload-data_lancamento').data("DateTimePicker").date(new Date(aux[0], (aux[1] - 1), aux[2]));
					} else {
						$('#upload-data_lancamento').data("DateTimePicker").clear();
					}
					if(!is_empty(ret['dataVencimento'], 1)) {
						aux = ret['dataVencimento'].split('-');
						$('#upload-data_vencimento').data("DateTimePicker").date(new Date(aux[0], (aux[1] - 1), aux[2]));
					} else {
						$('#upload-data_vencimento').data("DateTimePicker").clear();
					}
					aux = null;
					$("#upload-ids_cte").val(JSON.stringify(objEnvio['ids']));

					// monta as parcelas se existirem
					let casasPreco = $("div.data_views").data('casas_preco');
					if(is_empty(casasPreco, 1)) casasPreco = '0';
					casasPreco = parseInt(casasPreco.toString());

					let casasValor = $("div.data_views").data('casas_valor');
					if(is_empty(casasValor, 1)) casasValor = '2';
					casasValor = parseInt(casasValor.toString());

					const cifrao_moeda = $("div.data_views").data('prefixo_moeda');
					const separador_decimal_moeda = $("div.data_views").data('decimal_delimiter_moeda');
					const separador_milhar_moeda = $("div.data_views").data('thousand_delimiter_moeda');
					let iParcela = 1;
					if(!is_empty(ret['parcelas'], 1)) {
						for(parcela of ret['parcelas']) {
							// criar a linha
							$(".add-itens-table-geral").click();
							// preenche os campos da linha
							let trParcelas = $("table#upload-parcelas-tabela tbody tr:not(.ocultar)")
											.find(".linha:contains('"+iParcela+"')").parents('tr');
							trParcelas.find(".upload-parcelas_id_interno").val(parcela.idDFisParcelasCTE);
							trParcelas.find(".upload-parcelas_data_vencimento").val(
								strFormatDate(parcela.dataVencimento, "YYYY-MM-DD","DD/MM/YYYY")
							);
							trParcelas.find(".upload-parcelas_valor").val(
								formataDecimal(
									parcela.valor,
									'.',
									separador_decimal_moeda,
									separador_milhar_moeda,
									cifrao_moeda,
									true,
									casasPreco
								)
							);
							trParcelas.find(".upload-parcelas_valor_desconto").val(
								formataDecimal(
									parcela.valorDesconto,
									'.',
									separador_decimal_moeda,
									separador_milhar_moeda,
									cifrao_moeda,
									true,
									casasPreco
								)
							);
							trParcelas.find(".upload-parcelas_valor_juros").val(
								formataDecimal(
									parcela.valorJuros,
									'.',
									separador_decimal_moeda,
									separador_milhar_moeda,
									cifrao_moeda,
									true,
									casasPreco
								)
							);
							trParcelas.find(".upload-parcelas_codigo_barras").val(parcela.codigoBarras);
							trParcelas.find(".upload-parcelas_linha_digitavel").val(parcela.linhaDigitavel);
							iParcela++;
						}
						somaCamposParcelas();
					}
					// exibicao de dados em tela para confirmacao do documento
					if(!is_empty(ret['doc'], 1)) {
						// exibe o topo
						$('.dados_documento').removeClass('ocultar');
						if(!is_empty(ret['doc']['idDFisCTeImportacoes'], 1)) {
							$('#upload-idDocumento').val(ret['doc']['idDFisCTeImportacoes']);
						}
						if(!is_empty(ret['doc']['valorTotalNotaFiscalFormatado'], 1)) {
							$('#upload-valor').val(ret['doc']['valorTotalNotaFiscalFormatado']);
						}
						if(!is_empty(ret['doc']['docNumNotaFiscal'], 1)) {
							$('#upload-docNumNotaFiscal').val(ret['doc']['docNumNotaFiscal']);
						}
						if(!is_empty(ret['doc']['idNotasFiscaisERP'], 1)) {
							$('#upload-idNotasFiscaisERP').val(ret['doc']['idNotasFiscaisERP']);
						}
						if(!is_empty(ret['doc']['hashKey'], 1)) {
							$('#upload-hashKey').val(ret['doc']['hashKey']);
						}
					}else{
						// oculta o topo com os dados
						$('.dados_documento').addClass('ocultar');
					}

					// checa se teve anexos
					$("div#documentos_anexo").html('<div class="preview-docs-zone"></div>');
					if(!is_empty(ret['anexos'], 1)) {
						for(anexo of ret['anexos']) {
							let img = `
								<img src="${anexo.dataTypeAnexoNFe}${anexo.anexoNFe}"
									data-name="${anexo.nomeAnexoNFe}${anexo.extensaoAnexoNFe}"
									data-id="${anexo.idAnexoNFePortal}" />
							`;
							$(".preview-docs-zone").append(img);
						}
					}

					tableDataTable.draw();

					__criaAnexos();
					// muda o title do modal
					$("#modal-label_upload").html($("div.data_views").data('label_modal') + tipoDocumento);
					$('.modal-upload').modal('toggle');

					// checa se eh apenas visualizacao
					if(isVisualizar) {
						$("div.data_views").data("vizualizacao", 1);
						$('#upload-parcelas-tabela tfoot').addClass('ocultar');
						$('#upload-salvar').addClass('ocultar');
						$('.remove-itens-table-geral').addClass('ocultar');
						$('.allTagsAction').addClass('ocultar');
						$('.doc-cancel').addClass('ocultar');
					}else{
						$("div.data_views").data("vizualizacao", 0);
						$('#upload-parcelas-tabela tfoot').removeClass('ocultar');
						$('#upload-salvar').removeClass('ocultar');
						$('.remove-itens-table-geral').removeClass('ocultar');
						$('.allTagsAction').removeClass('ocultar');
						$('.doc-cancel').removeClass('ocultar');
					}
					toggleLoading();
				}catch(err){
					// console.log(err);
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

	$(".upload").off('click');
	$(".upload").on('click', function () {
		// se clicou em uma linha, desmarca as outras e limpas os ids e remonta o datatable
		valuesImportar = [];
		tipoDocsImportar = [];
		let tableDataTable = $($(".dataTables_scrollBody").find('.table-exibe')).DataTable();
		tableDataTable.draw();
		$(this).toggleClass('selected');

		__acaoPreReenvio($(this), [$(this).data("id")], [$(this).data("tipo_doc")])
	});

	// botao de ver documento
	$(".visualizar").off('click');
	$(".visualizar").on('click', function () {
		__acaoPreReenvio($(this), [$(this).data("id")], [$(this).data("tipo_doc")], true)
	});

	$(".upload_group").off('click');
	$(".upload_group").on('click', function () {
		__acaoPreReenvio($(this), valuesImportar, tipoDocsImportar)
	});

	$(".verificar_doc").off('click');
	$(".verificar_doc").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".desativar").off('click');
	$(".desativar").on('click', function () {
		__acaoPadrao($(this));
	});

	$("#upload-salvar").off("click");
	$("#upload-salvar").on("click", function () {
		__acaoPadraoEnviar($(this));
	});

	$('.show_modal_motivo').off('click');
	$('.show_modal_motivo').on('click', function (e) {
		$('.modal_motivo #label_motivo').html($(this).attr("title"));
		$('.modal_motivo .descricao_motivo').html($(this).parents('td').find('.descricao_rejeicao').html());
		$('.modal_motivo').modal('toggle');
	});
}

function criaCostumizacoes() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');

	$("select#pesquisa-status").select2Simple();
	$("select#pesquisa-configs").select2Simple();
	$("select#pesquisa-tipodoc").select2Simple();
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";

		let select_status = $("select#pesquisa-status");
		let select_configs = $("select#pesquisa-configs");
		let select_tipodoc = $("select#pesquisa-tipodoc");
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
		if(!is_empty($(select_tipodoc).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "tipodoc=" + $(select_tipodoc).val();
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
		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

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

	$("select#pesquisa-tipodoc").off("select2:select");
	$("select#pesquisa-tipodoc").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	$("select#pesquisa-tipodoc").off("select2:unselect");
	$("select#pesquisa-tipodoc").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__acaoAtualizaDataTable();
	});

	$('#importacao-chave').off("keyup");
	$('#importacao-chave').on("keyup",function(e){
		e.preventDefault();
		if(e.keyCode === 13) {
			let obj = $(this);
			let url = $(obj).data("url");
			let dados = {
				chave: $(obj).val(),
				tipoImportacao: 'cte',
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

	// ao clicar no botao deverá fazer a busca
	$('#search-table').off("click");
	$('#search-table').on("click",function(e){
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

function selecaoItens() {
	let __verificaBtnSelecaoReenvio = function () {
		if(is_empty(valuesImportar, 1)) {
			$(".upload_group").prop("disabled", true)
		} else {
			$(".upload_group").prop("disabled", false)
		}
	};

	$('.table-exibe tbody').on( 'click', 'tr', function () {
		if(is_empty($(this).data('id'), 0)) {
			return;
		}
		// checa se tem o botao de reenviar (nao pode selecionar um documento que ja esta esperando integrar no SAP)
		if(is_empty($(this).find('button.upload').data('tipo_doc'), 1)) {
			return;
		}

		$(this).toggleClass('selected');
		let idxRemove = valuesImportar.indexOf($(this).data('id'));
		if (idxRemove > -1) {
			valuesImportar.splice(idxRemove, 1);
		}
		let tipoDocRemove = tipoDocsImportar.indexOf($(this).find('button.upload').data('tipo_doc'));
		if (tipoDocRemove > -1) {
			tipoDocsImportar.splice(tipoDocRemove, 1);
		}

		if($(this).hasClass('selected')) {
			valuesImportar.push($(this).data('id'))
			tipoDocsImportar.push($(this).find('button.upload').data('tipo_doc'))
		}
		__verificaBtnSelecaoReenvio();
	});
	$(".table-exibe").on("datatable:draw:custom", function () {
		$(".table-exibe tbody tr").each(function () {
			if(!is_empty($(this).data('id'))) {
				let idxValor = valuesImportar.indexOf($(this).data('id'));
				if (idxValor > -1) {
					$(this).addClass('selected');
				}
			}
		});
		__verificaBtnSelecaoReenvio();
	});
}

let initByElementReplicancias = {}
function controlaReplicancias_addValores(idElemento, objReplicar) {
	if(
		is_empty(idElemento, 1) || is_empty(objReplicar, 1) || (
			!is_empty($($(objReplicar).parents('table')), 1) && $($(objReplicar).parents('table')).hasClass("ocultar")
		)
	) {
		return;
	}

	$(objReplicar).append(
		$('<option/>').attr('value', initByElementReplicancias[idElemento]['value']).text(initByElementReplicancias[idElemento]['text'])
	).val(initByElementReplicancias[idElemento]['value']).trigger('change');
	if(is_empty(initByElementReplicancias[idElemento]['value'], 1)) {
		$(objReplicar).trigger("select2:unselect");
	} else {
		$(objReplicar).trigger("select2:select");
	}
}

function atualizaLinhasParcelas() {
	let index = 0;
	$("table#upload-parcelas-tabela tbody tr .linha").each(function () {
		$(this).text(index);
		index++;
	});
}

function triggerSomaCampos() {
	if(!is_empty($("div.data_views").data("vizualizacao"), 1)) {
		return;
	}

	$("table#upload-parcelas-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
		somaCamposParcelas();
	});
}

function somaCamposParcelas() {
	const linhas   = $("table#upload-parcelas-tabela tbody tr:not(.ocultar)");
	let casasPreco = $("div.data_views").data('casas_preco');

	if(is_empty(casasPreco, 1)) casasPreco = '0';
	casasPreco = parseInt(casasPreco.toString());

	let casasValor = $("div.data_views").data('casas_valor');

	if(is_empty(casasValor, 1)) casasValor = '2';
	casasValor = parseInt(casasValor.toString());

	const cifrao_moeda = $("div.data_views").data('prefixo_moeda');
	const separador_decimal_moeda = $("div.data_views").data('decimal_delimiter_moeda');
	const separador_milhar_moeda = $("div.data_views").data('thousand_delimiter_moeda');

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		valorTotaisLinhas += stringParaFloat(
			$(linha).find('.upload-parcelas_valor').val(), separador_decimal_moeda, true
		);
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasValor).toString(),
			'.',
			true
		);

	$('#upload-parcelas_total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			separador_decimal_moeda,
			separador_milhar_moeda,
			cifrao_moeda,
			true,
			casasValor
		)
	);
}


// controles dinamicos para a table de parcelas do cte ou cteos
controlaTabelaSuite({
	"ref": "#upload-parcelas-tabela",
	"funAposAddItem": function () {
		atualizaLinhasParcelas();
		triggerSomaCampos();
	},
	"funAposRemoverItem": function () {
		atualizaLinhasParcelas();

		somaCamposParcelas();
		$.each(initByElementReplicancias, function(idElemento, valores) {
			controlaReplicancias_addValores(idElemento, $($($("#upload-parcelas-tabela").find(valores['replicar_para'])).last()));
		});
	}
});

criaCostumizacoes();
pesquisaPersonalizada();
selecaoItens();
acoesBotoes();