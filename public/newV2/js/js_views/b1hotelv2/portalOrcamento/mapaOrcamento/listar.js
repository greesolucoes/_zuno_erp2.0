/**
 * Função de atualização/carregamento da datatable de acordo com o ano selecionado na view
 * application/views/b1hotelv2/portalBudget/mapaBudgets/listar.php
 */
$('select#anoOrcamento').on('change', function() {
	const table = $('table');
	const newUrl = (table.data('url_original') + ($(this).val() ?? 0 ));
	table.data('url_ajax',)

	table.each(function () {
		if ($.fn.DataTable.isDataTable(this)) {
			const dataTable = $(this).DataTable();
			dataTable.clear();
			dataTable.destroy();
		}
	});

	$(table).data("url_ajax", newUrl);
	allTables();
})

/**
 * Função para download do relatório em Excel
 */
$('.btn-generate-excel').on('click', function() {
	const ref_table_search = ".table-exibe";
	const ref_btn_relatorio = ".btn-generate-excel";

	let dataAno = $("#anoOrcamento");
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
	url_table = $(ref_table_search).data("url_original");
	url_relatorio = $(ref_btn_relatorio).data("url_principal");
	if(!is_empty($(dataAno).val(), 1)) {
		gets_url += $(dataAno).val();
	}

	if(!is_empty(gets_url, 1)) {
		url_table += gets_url;
		url_relatorio += "/" + gets_url;
	}
	gets_url = null;
	$(ref_btn_relatorio).attr("href", url_relatorio);
	$(ref_table_search).data("url_ajax", url_table);
	allTables();
})