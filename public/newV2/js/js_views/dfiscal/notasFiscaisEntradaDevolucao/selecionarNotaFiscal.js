function acoesTela() {

	$('.table-exibe tbody').on('click', 'tr', function () {
		$('.table-exibe tbody tr').removeClass("selected");
		$(this).toggleClass('selected');
	});

    $('#add-por_nota_entrada').unbind('click');
    $('#add-por_nota_entrada').on("click", function (e) {
		toggleLoading();
		let url     = $(this).data('url');
		// captura os dados da linha selecionada
		// checa se selecionou a nota
		if(is_empty($('.table-exibe').DataTable().rows().data().length, true)){
			toggleLoading();
			swal(
				l["atenção!"],
				l["nenhumRegistroEncontrado"],
				"warning"
			).catch(swal.noop);
		}else{
			if(is_empty($('.table-exibe').DataTable().row('.selected').data(), true)){
				toggleLoading();
				swal(
					l["atenção!"],
					l["selecioneUmaNotaFiscalDeEntradaParaVincular"],
					"warning"
				).catch(swal.noop);
			}else{
				let id = $('.table-exibe').DataTable().row('.selected').data()['idDFisNotasFiscais'];
				$.redirect(url, {'idDFisNotasFiscais': id, ...tokenCsrf});
			}
		}
    });
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";

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
			gets_url += "dataDe=" + $(dataInicial).val();
		}
		if(!is_empty($(dataFinal).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "dataAte=" + $(dataFinal).val();
		}

		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	// ao clica no botao deverá fazer a busca
	$('#search-table').off("click");
	$('#search-table').on("click",function(e){
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

pesquisaPersonalizada();
acoesTela();