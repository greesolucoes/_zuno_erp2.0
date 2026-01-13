function controlarAcoes() {
	let __initFiltrarContagem = function () {
		let __acaoFiltrarReqistros = function () {
			// executa o filtro na tela de listagem de contagem de estoque por ajax
			// passando a filtragem por dia da semana e/ou data
			// toggleLoading();
			const ref_table_search = ".table-exibe";

			let select_diaSemana = $("select#pesquisa-dia_semana");
			let input_dataContagem = strFormatDate($("input#pesquisa-data_contagem").val());
			let url_table = "";
			let gets_url = "";
			let dataTable = null;

			//$(ref_table_search).each(function (){
				if($.fn.DataTable.isDataTable(this)) {
					dataTable = $(this).DataTable();
					dataTable.clear();
					dataTable.destroy();
				}
			//});

			url_table = $(ref_table_search).data("url_ajax");
			if(!is_empty($(select_diaSemana).val(), 1)) {
				if(!is_empty(gets_url, 1)) {
					gets_url += "&";
				}
				gets_url += "diasemanafiltro=" + $(select_diaSemana).val();
			}
			if(!is_empty(input_dataContagem, 1)) {
				if(!is_empty(gets_url, 1)) {
					gets_url += "&";
				}
				gets_url += "datacontagemfiltro=" + input_dataContagem;
			}
			if(!is_empty(gets_url, 1)) {
				url_table += "?" + gets_url;
			}
			gets_url = null;

			$(ref_table_search).data("url_ajax", url_table);
			allTables();
		};

		$("button#filtrar_contagem").off('click');
		$("button#filtrar_contagem").on("click", function (){
			__acaoFiltrarReqistros();
		});

		$("select#pesquisa-dia_semana").off("select2:unselect");
		$("select#pesquisa-dia_semana").on("select2:unselect", function () {
			if(!is_empty($(this).val(), 1)) {
				$(this).val("").trigger('change').trigger('select2:unselect');
				// return;
			}
			__acaoFiltrarReqistros();
			return;
		});
	}
	__initFiltrarContagem();
}
controlarAcoes();
