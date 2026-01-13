let textArea = null;

function destroyFieldQuery() {
	if(textArea === null) {
		return;
	}

	textArea.toTextArea();
	textArea = null;
}

function createFieldQuery(hints) {
	if(is_empty(hints, 1)) {
		hints = {};
	}

	destroyFieldQuery();
	textArea = CodeMirror.fromTextArea(document.getElementById('cabecalho-query'), {
		mode: 'text/x-mariadb',
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
		},
		hintOptions: hints
		/*{
			tables: {
				users: ["name", "score", "birthDate"],
				countries: ["name", "population", "size"]
			}
		}*/
	});
}

function createFieldFiles() {
	recriar($("div#cabecalho-zip_dbfs"));
	$('div#cabecalho-zip_dbfs').allUpload(
		'cabecalho-zip_dbfs-names[]',
		'cabecalho-zip_dbfs-blobs[]',
		null,
		'.preview-docs-zone',
		{
			"textUpload": $(".data_views").data("text_upload"),
			"textVisualize": $(".data_views").data("text_visualize_upload"),
			"noDocsText": $(".data_views").data("text_no_docs_upload"),
			"obsText": $(".data_views").data("text_obs_upload"),
		},
		function (obj) {
			$(obj).append('<div class="tools-name-doc">' + ($($(obj).find(".file-name")).val()) + '</div>');

			let srcCheck = $($(obj).find(".file-blob")).val().toLowerCase().split(";")[0];
			let fontAwesomeIcon = "fa ";
			if(srcCheck.includes("application/octet-stream")) {
				fontAwesomeIcon += 'fa-file-text-o';
			} else if(srcCheck.includes("application/x-zip-compressed")) {
				fontAwesomeIcon += 'fa-eye-slash';
			} else {
				fontAwesomeIcon += 'fa-eye-slash';
			}

			$($(obj).find(".text-zone")).html("<i class='" + fontAwesomeIcon + "' style='font-size: 10em;'></i>");
			fontAwesomeIcon = null;
			srcCheck = null;
		},
		null,
		false,
		".zip,.dbf"
	);
}

function formControl() {
	$('button#executar_query').off('click');
	$('button#executar_query').on('click', function () {
		let url = $($(this).parents("form.cadastro")).attr("action");
		if(is_empty(url, 1)) {
			return;
		}

		let regexRemoveComments = /(--[\w\W\s\t].*)|(\/\/[\w\W\s\t].*)|((\/\*(?:(?!\*\/)[\s\S\r])*?)\*\/)/g;
		let token = $("#cabecalho-token").val();
		let querys = null;
		if(textArea !== null) {
			querys = textArea.getValue();
			if(!is_empty(textArea.getSelection(), 1)) {
				querys = textArea.getSelection();
			}
			querys = querys.replace(regexRemoveComments, "");
		}
		regexRemoveComments = null;

		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{
				"token": token,
				"querys": querys,
			},
			function (ret) {
				try{
					ret = JSON.parse(ret);
					let htmlResults = "";

					if(!is_empty(ret['bol'], 1)) {
						$.each(ret['results'], function (indexTable, table) {
							htmlResults += "<div id='query-resultado-" + (indexTable + 1) + "' style='width: 100%; min-height: 50px; max-height: 500px;'>";
							if(!is_empty(table)) {
								$.each(table, function (row, fields) {
									row = parseInt(row);
									if(row === 0) {
										htmlResults += '<table class="table table-bordered table-hover table-exibe display" data-table="table-no-fixed" data-tipos_col="';
										$.each(fields, function (field, valField) {
											htmlResults += 'normal,';
										});
										htmlResults = htmlResults.substring(0, (htmlResults.length - 1));
										htmlResults += '">';
										htmlResults += '<thead>';
										$.each(fields, function (field, valField) {
											htmlResults += '<th class="nosort">' + field + '</th>';
										});
										htmlResults += '</thead>';
										htmlResults += '<tbody>';
									}
									htmlResults += '<tr>';
									$.each(fields, function (field, valField) {
										htmlResults += '<td>' + valField + '</td>';
									});
									htmlResults += '</tr>';
									if(row === (table.length - 1)) {
										htmlResults += '</tbody>';
										htmlResults += '<tfoot>';
										htmlResults += '<td class="ocultar" colspan="' + fields.length + '"></td>';
										htmlResults += '</tfoot>';
										htmlResults += '</table>';
									}
								});
							} else {
								htmlResults += "<div class='centraliza'>";
								htmlResults += l["registrosNaoEncontrados"];
								htmlResults += "</div>";
							}
							htmlResults += "</div>";
						});

						$("#querys-resultados").html(htmlResults);
						htmlResults = null;
						allTables();
					} else {
						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);
					}

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
	});
	$('button#criar_db').off('click');
	$('button#criar_db').on('click', function () {
		let url = $($(this).parents("form.cadastro")).data("url_create_db");
		if(is_empty(url, 1)) {
			return;
		}
		let data = formToStringJson('form#form-db_create');

		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{
				"data": data,
			},
			function (ret) {
				try{
					ret = JSON.parse(ret);
					$("#form-query_db").addClass("ocultar");

					if(!is_empty(ret['bol'], 1)) {
						createFieldQuery(ret['hints']);
						$("#form-query_db").removeClass("ocultar");
						$("#cabecalho-token").prop("readonly", true);
						$('button#remove_db').prop("disabled", false);
						$("#cabecalho-token_antigo").val($("#cabecalho-token").val());
					}

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
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
	});
	$('button#remove_db').off('click');
	$('button#remove_db').on('click', function () {
		let url = $($(this).parents("form.cadastro")).data("url_remove_db");
		let token = $("#cabecalho-token").val();
		if(is_empty(url, 1) || is_empty(token, 1)) {
			return;
		}

		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{
				"token": token,
			},
			function (ret) {
				try{
					ret = JSON.parse(ret);
					$("#form-query_db").addClass("ocultar");

					if(!is_empty(ret['bol'], 1)) {
						destroyFieldQuery();
						$('button#remove_db').prop("disabled", true);
					}

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
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
	});
}

createFieldFiles();
formControl();