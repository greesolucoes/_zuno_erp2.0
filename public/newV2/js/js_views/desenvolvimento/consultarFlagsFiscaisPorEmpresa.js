init = ()=>{
	$("div#filterTipoFlag").hide();
	$("div#filterTipoInt").hide();
}
function criaSelects(){
	$("select#tipo-flag").select2Simple();
	$("select#valor-flag").select2Simple();
}

function pesquisaPersonalizada() {

	const __selecionaFilters = () => {
		let tipoFlag = $("select#tipo-flag").val();
		$("div#filterTipoFlag").hide();
		$("div#filterTipoInt").hide();
		if(tipoFlag === 'flag') {
			$("div#filterTipoFlag").show();
		}
		if(tipoFlag === 'valor') {
			$("div#filterTipoInt").show();
		}
	}
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
		let tipoFlag = $("select#tipo-flag").val();
		let valorFlag = $("select#valor-flag").val();
		let valorInicial = $("input#valorInicial").val();
		let valorFinal = $("input#valorFinal").val();
		let gets_url = "";

		if(!is_empty(tipoFlag, 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "tipoFlag=" + tipoFlag;

			if(tipoFlag === 'flag' && !is_empty(valorFlag, 0)) {
				gets_url += `&valorFlag=${valorFlag}`
			}

			if(tipoFlag === 'integer') {
				if(!is_empty(valorInicial, 0)){
					gets_url += `&valorInicial=${valorInicial}`
				}
				if(!is_empty(valorFinal, 0)){
					gets_url += `&valorFinal=${valorFinal}`
				}
			}
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

	$("select#tipo-flag").off("select2:select");
	$("select#tipo-flag").on("select2:select", function () {
		__selecionaFilters();
		__acaoAtualizaDataTable();
	})

	$("select#valor-flag").off("select2:select");
	$("select#valor-flag").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	$("#filterTipoInt").on("keyup",".filterInteger", function () {
		__acaoAtualizaDataTable();
	})

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

init();
criaSelects();
pesquisaPersonalizada();
