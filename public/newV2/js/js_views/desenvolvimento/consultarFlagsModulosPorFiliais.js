function criaSelects(){
	$("select#modulo-flag").select2Simple();
}

function pesquisaPersonalizada() {

	var __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";
		let url_table = $(ref_table_search).data("url_principal");
		let dataTable = null;
		$(ref_table_search).data("url_ajax", __preparaDadosParaBusca(url_table));
		$(ref_table_search).each(function (){
			if($.fn.DataTable.isDataTable(this)) {
				dataTable = $(this).DataTable();
				dataTable.clear();
				dataTable.destroy();
			}
		})
		allTables();
	}
	var __downloadExcell = function () {
		let url = $("#downloadExcel").data("url");
		let search = $('.dataTables_filter input').val();
		let link = __preparaDadosParaBusca(url, search);
		window.location.href = link;
	}

	var __preparaDadosParaBusca = (url_table, search) => {
		let moduloFlag = $("select#modulo-flag").val();
		let gets_url = "";

		if(!is_empty(moduloFlag, 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "moduloFlag=" + moduloFlag;
		}

		if(search !== undefined) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "q="+encodeURIComponent(search);
			gets_url += "&columns="+encodeURIComponent($(".table").data('colunas_ajax'));
		}

		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;
		return url_table;
	}

	$("select#modulo-flag").off("select2:select");
	$("select#modulo-flag").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	$(".acts").on("click","#downloadExcel", function () {
		__downloadExcell();
	})

	$("table.table-exibe").on('click', '.btn-open', function(){
		let flag = $(this).data('flag')
		let empresas = $(this).data('content')
		$("#modal-empresas-content").html(empresas)
		$("#modal-empresas-subtitle").html(`<h6><small class="text-secondary">flag:</small> <span class="badge badge-warning">${flag}</span></h6>`)
		$("#modal-empresas").modal('show', 600)
	})

	__acaoAtualizaDataTable();
}

criaSelects();
pesquisaPersonalizada();
