$("select.select_ajax").select2Ajax();
$("select.select_ajax").data('init', '');

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";
		let select_workflows = $('select#relatorio-cadWorkflow');
		const ref_btn_relatorio = "a.btn-baixar-csv";

		let select_filiais = $('select#filiais');
		let dataInicial = $('input#dataInicial');
		let dataFinal = $('input#dataFinal');

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

		if(!is_empty($(select_filiais).val(), 1)) {
			gets_url += is_empty(gets_url, 1) ? "" : "&"
			gets_url += "filiais=" + $(select_filiais).val();
		}

		if(!is_empty($(dataInicial).val(), 1)) {
			gets_url += is_empty(gets_url, 1) ? "" : "&"
			gets_url += "dataInicial=" + $(dataInicial).val();
		}
		if(!is_empty($(dataFinal).val(), 1)) {
			gets_url += is_empty(gets_url, 1) ? "" : "&"
			gets_url += "dataFinal=" + $(dataFinal).val();
		}

		if(!is_empty($(select_workflows).val(), 1)) {
			gets_url += is_empty(gets_url, 1) ? "" : "&"
			gets_url += "workflows=" + $(select_workflows).val();
		}

		gets_url += is_empty(gets_url, 1) ? "" : "&"
		gets_url += "onlyPendentes=" + ($('input#relatorio-orcamentoPendente').prop('checked') ? 1 : 0);

		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
			url_relatorio += "?" + gets_url;
		}
		gets_url = null;

		$(ref_table_search).data("url_ajax", url_table);
		$(ref_btn_relatorio).attr("href", url_relatorio);
		allTables();
	}
	// ao clica no botao deverá fazer a busca
	$('.btn-search').off("click");
	$('.btn-search').on("click",function(e){
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}
pesquisaPersonalizada();