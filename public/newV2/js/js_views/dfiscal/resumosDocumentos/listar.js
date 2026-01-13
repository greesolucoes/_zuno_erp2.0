function atualizaURLsDTSearch() {
	const ref_table_search = ".table-exibe";
	const ref_btn_relatorio = ".btn-generate-excel";

	let select_tipoDoc = $("select#pesquisa-tipo_doc");
	let select_filiais = $("select#geral-filiais");
	let data_inicio = $(".de-para-inicio");
	let data_fim = $(".de-para-final");
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
	if(!is_empty($(select_tipoDoc).val(), 1)) {
		if(!is_empty(gets_url, 1)) {
			gets_url += "&";
		}
		gets_url += "tipo_doc=" + $(select_tipoDoc).val();
	}
	if(!is_empty($(select_filiais).val(), 1)) {
		if(!is_empty(gets_url, 1)) {
			gets_url += "&";
		}
		gets_url += "filiais=" + toBase64($(select_filiais).val().join(","));
	}
	if(!is_empty($(data_inicio).val(), 1)) {
		if(!is_empty(gets_url, 1)) {
			gets_url += "&";
		}
		gets_url += "data_inicio=" + moment($(data_inicio).val(), $('.data_views').data('format_date')).format('YYYY-MM-DD');
	}
	if(!is_empty($(data_fim).val(), 1)) {
		if(!is_empty(gets_url, 1)) {
			gets_url += "&";
		}
		gets_url += "data_fim=" + moment($(data_fim).val(), $('.data_views').data('format_date')).format('YYYY-MM-DD');
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

function acoesBotoes() {
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

	$("#search-table").off("click");
	$("#search-table").on("click", function () {
		atualizaURLsDTSearch();
	});
}

function criaComponentes() {
	$("select#pesquisa-tipo_doc").select2Simple();

	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');

	$(".de-para-inicio").datetimepicker({
		locale: _lang,
		useCurrent: false,
		format: $('.data_views').data('format_date'),
		widgetPositioning: {
			vertical: 'top',
			horizontal: 'left'
		}
	});


	$(".de-para-final").datetimepicker({
		locale: _lang,
		useCurrent: false,
		format: $('.data_views').data('format_date'),
		widgetPositioning: {
			vertical: 'top',
			horizontal: 'left'
		}
	});


	atualizaURLsDTSearch();
}

criaComponentes();
acoesBotoes();