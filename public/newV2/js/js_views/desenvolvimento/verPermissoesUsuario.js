function criaSelects(){
	$("select#usuario").select2Simple();
	$("select#usuario").select2Simple();
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";

		let usuario = $("select#usuario");
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
		if(!is_empty($(usuario).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "usuario=" + $(usuario).val();
		}
		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	$("select#usuario").off("select2:select");
	$("select#usuario").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

criaSelects();
pesquisaPersonalizada();
