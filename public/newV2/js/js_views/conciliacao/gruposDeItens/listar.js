function criaSelects(){
	$("select#status-search").select2Simple();
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";

		let statusSearch = $("select#status-search");

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
		if(!is_empty($(statusSearch).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "statusSearch=" + $(statusSearch).val();
		}
		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	$("select#status-search").off("select2:select");
	$("select#status-search").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

function downloadGruposDeItens(){
	$('button.printar-valores').unbind('click');
	$('button.printar-valores').on('click', function() {
		const titulo = $('.page-header, .page-title').text();
		save2excel($('table#relatorio-grupos-de-itens_table'), {
			not: null,
			name: titulo,
			filename: (titulo + '.xls')
		});
	});
}

criaSelects();
pesquisaPersonalizada();
downloadGruposDeItens();