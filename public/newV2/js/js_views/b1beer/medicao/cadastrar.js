function criaSelects() {
	$("#codigo_op").select2Ajax();
	$("#codigo_op").data('init', '');
}

function onEmpresasGestoras() {
	$("#codigo_op").off("select2:unselect");
	$("#codigo_op").on("select2:unselect", function () {
		if ($("#item").hasClass("select2-hidden-accessible")){
			$('#item').select2('destroy');
		}

		$('#item option').remove();
		$('#item').append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
		$('#item').attr("readonly", true);
		$('#item').data("url", "");

		$('#item option').remove();
		$('#item').append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
		$('#item').attr("readonly", true);
		$('#item').data("url", "");

	});

	$("#codigo_op").off("select2:select");
	$("#codigo_op").on("select2:select", function () {
		var idOrdemProducao = $(this).val();
		if ($("#item").hasClass("select2-hidden-accessible")){
			$('#item').select2('destroy');
		}

		$('#item option').remove();
		$('#item').attr("readonly", false);
		$('#item').data("url", $(".datas_views").data("url_search_item") + idOrdemProducao);

		$("#item").select2Ajax();
		$("#item").data('init', '');

	});

	$("#empresas_gestoras").trigger("select2:unselect");
}

criaSelects();
onEmpresasGestoras();