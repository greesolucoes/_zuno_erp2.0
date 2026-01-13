
function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";
		const ref_btn_relatorio = ".btn-generate-excel";

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

		console.log(url_table);

		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	let inputSearch = $('#search-table');

	// ao clica no botao deverá fazer a busca
	inputSearch.off("click");
	inputSearch.on("click",function(e){
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}
pesquisaPersonalizada();