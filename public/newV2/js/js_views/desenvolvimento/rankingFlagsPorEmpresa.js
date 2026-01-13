function criaSelects(){
	$("select#valor-flag").select2Simple();
	$("select#valor-flag").select2Simple();
}

function pesquisaPersonalizada() {
	var __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";

		let valorFlag = $("select#valor-flag");
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

		if(!is_empty($(valorFlag).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "valorFlag=" + $(valorFlag).val();
		}

		url_table = $(ref_table_search).data("url_principal");
		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	$("select#valor-flag").off("select2:select");
	$("select#valor-flag").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

criaSelects();
pesquisaPersonalizada();
