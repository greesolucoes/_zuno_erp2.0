function criaSelects() {
	$("#empresas_gestoras").select2Ajax();
	$("#empresas_gestoras").data('init', '');
}

function onEmpresasGestoras() {
	$("#empresas_gestoras").off("select2:unselect");
	$("#empresas_gestoras").on("select2:unselect", function () {
		if ($("#empresas").hasClass("select2-hidden-accessible")){
			$('#empresas').select2('destroy');
		}
		if ($("#filiais").hasClass("select2-hidden-accessible")){
			$('#filiais').select2('destroy');
		}

		$('#empresas option').remove();
		$('#empresas').append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
		$('#empresas').attr("readonly", true);
		$('#empresas').data("url", "");

		$('#filiais option').remove();
		$('#filiais').append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
		$('#filiais').attr("readonly", true);
		$('#filiais').data("url", "");

	});

	$("#empresas_gestoras").off("select2:select");
	$("#empresas_gestoras").on("select2:select", function () {
		var idEmpresasGestoras = $(this).val();
		if ($("#empresas").hasClass("select2-hidden-accessible")){
			$('#empresas').select2('destroy');
		}
		if ($("#filiais").hasClass("select2-hidden-accessible")){
			$('#filiais').select2('destroy');
		}

		$('#empresas option').remove();
		$('#empresas').attr("readonly", false);
		$('#empresas').data("url", $(".datas_views").data("url_search_empresas") + idEmpresasGestoras);

		$('#filiais option').remove();
		$('#filiais').append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
		$('#filiais').attr("readonly", true);
		$('#filiais').data("url", "");

		$("#empresas").select2Ajax();
		$("#empresas").data('init', '');

	});

	$("#empresas_gestoras").trigger("select2:unselect");
}

function onEmpresas() {
	$("#empresas").off("select2:unselect");
	$("#empresas").on("select2:unselect", function () {
		if ($("#filiais").hasClass("select2-hidden-accessible")){
			$('#filiais').select2('destroy');
		}

		$('#filiais option').remove();
		$('#filiais').append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
		$('#filiais').attr("readonly", true);
		$('#filiais').data("url", "");
	});

	$("#empresas").off("select2:select");
	$("#empresas").on("select2:select", function () {
		var idEmpresas = $(this).val();
		if ($("#filiais").hasClass("select2-hidden-accessible")){
			$('#filiais').select2('destroy');
		}

		$('#filiais option').remove();
		$('#filiais').attr("readonly", false);
		$('#filiais').data("url", $(".datas_views").data("url_search_filiais") + idEmpresas);

		$("#filiais").select2Ajax();
		$("#filiais").data('init', '');
	});
}


function carregar() {
	$('#buscar').unbind('click');
	$('#buscar').on("click", function (e) {
		var empresasGestoras = $('#empresas_gestoras').val();
		var empresas = $('#empresas').val();
		var filiais = $('#filiais').val();
		var printMaster = $('#mostrarMaster').prop("checked");
		var finalUrl = "";

		if (printMaster == true) {
			finalUrl += 1 + "/";
		}else{
			finalUrl += 0 + "/";
		}

		if (!is_empty(empresasGestoras, 1)) {
			finalUrl += empresasGestoras + "/";
		}
		if (!is_empty(empresas, 1)) {
			finalUrl += empresas + "/";
		}
		if (!is_empty(filiais, 1)) {
			finalUrl += filiais + "/";
		}

		$('#dataTableEmpresasGestoras').DataTable().destroy();
		$('#dataTableEmpresasGestoras').data("url_ajax", $(".datas_views").data("url_search_carregar") + finalUrl);
		allTables();
	});
}

function mudarTittle() {
	$("#mostrarMaster").change(function() {
		var printMaster = $('#mostrarMaster').prop("checked");
		if(printMaster == false){
			$('#tittleMaster').attr('title', l["mostrarUsuariosMaster"]);
		}else{
			$('#tittleMaster').attr('title', l["ocultarUsuariosMaster"]);
		}
	});
}

mudarTittle();
carregar();
criaSelects();
onEmpresasGestoras();
onEmpresas();