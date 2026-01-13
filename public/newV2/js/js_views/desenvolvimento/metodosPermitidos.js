function criaSelects(){
	$("select#permissao").select2Simple();
	$("select#permissao").select2Simple();
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";

		let permissao = $("select#permissao");
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
		if(!is_empty($(permissao).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "permissao=" + $(permissao).val();
		}
		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	$("select#permissao").off("select2:select");
	$("select#permissao").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	__acaoAtualizaDataTable();
}

function acaoDeletar() {
	$(document).on("click",'.deletar', function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url");
			let id = $(obj).data("id");
			let tableDataTable = $(".table-exibe").DataTable();

			swal({
				title: l["deletarRegistro"],
				text: l["desejaContinuar?"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();
				ajaxRequest(
					true,
					url,
					null,
					'text',
					{},
					function (ret) {
						console.log(ret);
						ret = JSON.parse(ret);

						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if(!is_empty(ret["bol"], 1)) {
							tableDataTable.draw();
						}

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}

acaoDeletar();
criaSelects();
pesquisaPersonalizada();