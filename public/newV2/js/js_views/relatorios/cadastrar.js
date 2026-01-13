let textArea = null;

function destroyFieldQuery(idTextArea, limpar) {
	if(is_empty(idTextArea, 1)) {
		return;
	}

	if(textArea !== null) {
		textArea.toTextArea();
		textArea = null;
	}
	if(limpar) {
		$("#" + idTextArea).val("");
	}
}

function createFieldQuery(limpar) {
	destroyFieldQuery("geral-query", limpar);
	textArea = CodeMirror.fromTextArea(document.getElementById('geral-query'), {
		mode: 'text/x-mssql',
		indentWithTabs: true,
		smartIndent: true,
		lineNumbers: true,
		matchBrackets : true,
		autofocus: true,
		extraKeys: {
			"Ctrl-Space": "autocomplete",
			//https://github.com/jupyter/notebook/issues/1816
			"Ctrl-D": function(cm) {
				// get a position of a current cursor in a current cell
				let current_cursor = cm.doc.getCursor();
				// read a content from a line where is the current cursor
				let line_content = cm.doc.getLine(current_cursor.line);

				// go to the end the current line
				CodeMirror.commands.goLineEnd(cm);
				// make a break for a new line
				CodeMirror.commands.newlineAndIndent(cm);

				// filled a content of the new line content from line above it
				cm.doc.replaceSelection(line_content);
				// restore position cursor on the new line
				cm.doc.setCursor(current_cursor.line + 1, current_cursor.ch);
			},
			"Ctrl-Enter": function (cm) {
				$('button#executar_query').trigger('click');
			},
		}
	});
}

function controlaChecksCabecalho() {
	$("input.geral-exibir_menu_relatorio").off("change");
	$("input.geral-exibir_menu_relatorio").on("change", function () {
		$($(this).parents("div").find(".geral-exibir_menu_relatorio-hidden")).val(this.checked ? "1" : "0");
	});
}

function controlaChecksCamposLinhas(isInit) {
	let __funIsRefID = function (obj, isChecked) {
		$($(obj).parents("tr").find(".is_ref_id-campos_query-hidden")).val(isChecked ? "1" : "0");
		if(isChecked) {
			if($($(obj).parents("tr").find(".tipo_campo-campos_query")).val().startsWith("TEXT")) {
				$($(obj).parents("tr").find(".tipo_campo-campos_query")).val("").trigger("change");
			} else if($($(obj).parents("tr").find(".tipo_campo-campos_query")).val().endsWith("TEXT")) {
				$($(obj).parents("tr").find(".tipo_campo-campos_query")).val("").trigger("change");
			}
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value^='TEXT']")).addClass("ocultar");
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value$='TEXT']")).addClass("ocultar");
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value='']")).removeClass("ocultar");
		} else {
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value^='TEXT']")).removeClass("ocultar");
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value$='TEXT']")).removeClass("ocultar");
		}
	}
	let __funIsOcultarCampo = function (obj, isChecked) {
		$($(obj).parents("tr").find(".is_ocultar_campo-campos_query-hidden")).val(isChecked ? "1" : "0");
	}
	let __funIsRefDataCorte = function (obj, isChecked) {
		$($(obj).parents("tr").find(".is_ref_dt_corte-campos_query-hidden")).val(isChecked ? "1" : "0");
		if(isChecked) {
			if(!$($(obj).parents("tr").find(".tipo_campo-campos_query")).val().startsWith("DATE_")) {
				$($(obj).parents("tr").find(".tipo_campo-campos_query")).val("").trigger("change");
			}
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option:not([value^='DATE_'])")).addClass("ocultar");
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value='']")).removeClass("ocultar");
			$($(obj).parents("tr").find("input.is_ref_filial-campos_query")).prop("disabled", true);
			$($(obj).parents("tr").find("input.is_ref_cnpj_usuarios-campos_query")).prop("disabled", true);
		} else {
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option:not([value^='DATE_'])")).removeClass("ocultar");
			$($(obj).parents("tr").find("input.is_ref_filial-campos_query")).prop("disabled", false);
			$($(obj).parents("tr").find("input.is_ref_cnpj_usuarios-campos_query")).prop("disabled", false);
		}
	}
	let __funIsRefFilial = function (obj, isChecked) {
		$($(obj).parents("tr").find(".is_ref_filial-campos_query-hidden")).val(isChecked ? "1" : "0");
		if(isChecked) {
			if(!$($(obj).parents("tr").find(".tipo_campo-campos_query")).val().startsWith("INTEIRO_")) {
				$($(obj).parents("tr").find(".tipo_campo-campos_query")).val("").trigger("change");
			}
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option:not([value^='INTEIRO_'])")).addClass("ocultar");
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value='']")).removeClass("ocultar");
			$($(obj).parents("tr").find("input.is_ref_dt_corte-campos_query")).prop("disabled", true);
			$($(obj).parents("tr").find("input.is_ref_cnpj_usuarios-campos_query")).prop("disabled", true);
		} else {
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option:not([value^='INTEIRO_'])")).removeClass("ocultar");
			$($(obj).parents("tr").find("input.is_ref_dt_corte-campos_query")).prop("disabled", false);
			$($(obj).parents("tr").find("input.is_ref_cnpj_usuarios-campos_query")).prop("disabled", false);
		}
	}
	let __funIsRefCNPJUsuarios = function (obj, isChecked) {
		$($(obj).parents("tr").find(".is_ref_cnpj_usuarios-campos_query-hidden")).val(isChecked ? "1" : "0");
		if(isChecked) {
			if(!$($(obj).parents("tr").find(".tipo_campo-campos_query")).val().startsWith("CPF_")) {
				$($(obj).parents("tr").find(".tipo_campo-campos_query")).val("").trigger("change");
			}
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option:not([value^='CPF_'])")).addClass("ocultar");
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option[value='']")).removeClass("ocultar");
			$($(obj).parents("tr").find("input.is_ref_dt_corte-campos_query")).prop("disabled", true);
			$($(obj).parents("tr").find("input.is_ref_filial-campos_query")).prop("disabled", true);
		} else {
			$($(obj).parents("tr").find(".tipo_campo-campos_query").find("option:not([value^='CPF_'])")).removeClass("ocultar");
			$($(obj).parents("tr").find("input.is_ref_dt_corte-campos_query")).prop("disabled", false);
			$($(obj).parents("tr").find("input.is_ref_filial-campos_query")).prop("disabled", false);
		}
	}
	$("input.is_ref_id-campos_query").off("change");
	$("input.is_ref_id-campos_query").on("change", function () {
		__funIsRefID($(this), this.checked);
	});
	$("input.is_ocultar_campo-campos_query").off("change");
	$("input.is_ocultar_campo-campos_query").on("change", function () {
		__funIsOcultarCampo($(this), this.checked);
	});
	$("input.is_ref_dt_corte-campos_query").off("change");
	$("input.is_ref_dt_corte-campos_query").on("change", function () {
		__funIsRefDataCorte($(this), this.checked);
	});
	$("input.is_ref_filial-campos_query").off("change");
	$("input.is_ref_filial-campos_query").on("change", function () {
		__funIsRefFilial($(this), this.checked);
	});
	$("input.is_ref_cnpj_usuarios-campos_query").off("change");
	$("input.is_ref_cnpj_usuarios-campos_query").on("change", function () {
		__funIsRefCNPJUsuarios($(this), this.checked);
	});

	if(isInit) {
		$("input.is_ref_id-campos_query").each(function (idxField, fieldObj) {
			if(fieldObj.checked) {
				__funIsRefID($(fieldObj), fieldObj.checked);
			}
		});
		$("input.is_ref_dt_corte-campos_query").each(function (idxField, fieldObj) {
			if(fieldObj.checked) {
				__funIsRefDataCorte($(fieldObj), fieldObj.checked);
			}
		});
		$("input.is_ref_filial-campos_query").each(function (idxField, fieldObj) {
			if(fieldObj.checked) {
				__funIsRefFilial($(fieldObj), fieldObj.checked);
			}
		});
		$("input.is_ref_cnpj_usuarios-campos_query").each(function (idxField, fieldObj) {
			if(fieldObj.checked) {
				__funIsRefCNPJUsuarios($(fieldObj), fieldObj.checked);
			}
		});
	}
}

function btnFunctions() {
	$('button#salvar').off('click');
	$('button#salvar').on('click', function () {
		let dateAux = null;
		let url = $('.data_views').data('url_salvar');
		if(is_empty(url, 1)) {
			return;
		}

		let save = {
			id: $("#geral-id_registro").val(),
			nome: $("#geral-nome_relatorio").val(),
			fornecedorPermitido: $("#geral-fornecedor_permitido").val(),
			tempoConsumoSap: $("#geral-tempo_consumo_sap").val(),
			dataInicialCorte: "",
			exibirMenu: $(".geral-exibir_menu_relatorio-hidden").val(),
			queryText: "",
			campos: [],
			order: [],
			departamento: [],
		};
		dateAux = $('#geral-dt_inicial').data("DateTimePicker").date();
		if(!is_empty(dateAux, 1)) {
			dateAux = dateAux._d;
			save.dataInicialCorte = dateAux.getFullYear() + "-" + (dateAux.getMonth() + 1).toString().padStart(2, '0') + "-" + dateAux.getDate().toString().padStart(2, '0');
		}
		dateAux = null;

		if(textArea !== null) {
			save.queryText = toBase64(textArea.getValue());
		}
		if($("table#tabela-campos_query tbody tr:not(.ocultar)").length > 0) {
			$("table#tabela-campos_query tbody tr:not(.ocultar)").each(function () {
				if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
					return;
				}

				save.campos.push({
					nomeCampo: $(this).find(".nome_campo-campos_query").val(),
					aliasCampo: $(this).find(".alias_relatorio-campos_query").val(),
					tipoCampo: $(this).find(".tipo_campo-campos_query").val(),
					isRefFiliais: $(this).find(".is_ref_filial-campos_query-hidden").val(),
					isRefCNPJUsuario: $(this).find(".is_ref_cnpj_usuarios-campos_query-hidden").val(),
					isRefDtCorte: $(this).find(".is_ref_dt_corte-campos_query-hidden").val(),
					isRefId: $(this).find(".is_ref_id-campos_query-hidden").val(),
					isOcultarCampo: $(this).find(".is_ocultar_campo-campos_query-hidden").val(),
				});
			});
		}
		if($("table#tabela-order_query tbody tr:not(.ocultar)").length > 0) {
			$("table#tabela-order_query tbody tr:not(.ocultar)").each(function () {
				if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
					return;
				}

				save.order.push({
					nomeCampo: $(this).find(".nome_campo-order_query").val(),
					orderCampo: $(this).find(".order_campo-order_query").val(),
				});
			});
		}

		if($("table#tabela-departamento tbody tr:not(.ocultar)").length > 0) {
			$("table#tabela-departamento tbody tr:not(.ocultar)").each(function () {
				if(!is_empty($($(this).find(".is_fake-no_post")).val(), 1)) {
					return;
				}
				save.departamento.push({
					nomeDepartamento: $(this).find(".nome-departamento").val()
				});
			});
		}

		toggleLoading();
		ajaxRequest(true, url, null, 'text', {'save': save}, function (ret) {
			try{
				ret = JSON.parse(ret);
				if(!is_empty(ret['bol'], 1)) {
					if(is_empty(save.id, 1)) {
						$("table#tabela-campos_query tbody tr:not(.ocultar)").remove();
						$("table#tabela-order_query tbody tr:not(.ocultar)").remove();
						$("table#tabela-departamento tbody tr:not(.ocultar)").remove();

						$("input:not([type='hidden'])").val("");
						$(".select_simple").val("").trigger('change');
						$('.grupo_datas').data("DateTimePicker").clear();
						createFieldQuery(true);
					} else {
						$("#tempo_consumo_estatistica").text($("#geral-tempo_consumo_sap").select2("data")[0].text);
					}
				}

				swal(
					ret['titulo'],
					ret['text'],
					ret['class']
				).catch(swal.noop);

				toggleLoading();
			}catch(err){
				consoleProduction(err);
				swal(
					l["erro!"],
					l["tempoDeRespostaDoServidorEsgotado!"],
					"error"
				).catch(swal.noop);
				forceToggleLoading(0);
			}
		});
	});
}

function criaSelectsCabecalho() {
	$("select.select_simple").select2Simple();
}

function criaSelectsItens(ref) {
	if(!is_empty(ref, 1)) {
		ref += " ";
	} else {
		ref = "";
	}
	ref += "select.select_simple_itens";
	$(ref).select2Simple();
}

function criaSelects(){
	$(".select_fornecedor_permitido").select2Ajax();
	$(".select_fornecedor_permitido").data('init', '');
}

function criaCostumizacoes() {
	criaSelectsCabecalho();
	criaSelectsItens();
	criaSelects();
	createFieldQuery(false);
	btnFunctions();
	controlaChecksCabecalho();
	controlaChecksCamposLinhas(true);
}

criaCostumizacoes();
controlaTabelaSuite({
	"ref": "#tabela-campos_query",
	"funAposAddItem": function () {
		controlaChecksCamposLinhas(false);
		criaSelectsItens("#tabela-campos_query");
	}
});
controlaTabelaSuite({
	"ref": "#tabela-order_query",
	"funAposAddItem": function () {
		criaSelectsItens("#tabela-order_query");
	}
});
controlaTabelaSuite({
	"ref": "#tabela-departamento",
	"funAposAddItem": function () {
		criaSelectsItens("#tabela-departamento");
	}
});