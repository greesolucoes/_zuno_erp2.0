
controlaTabelaSuite({
	"ref": "#tabela-departamento",
	"funAposAddItem": function () {
		criaSelectsItens("#tabela-departamento");
	}
});

function criaSelectsItens(ref) {
	if(!is_empty(ref, 1)) {
		ref += " ";
	} else {
		ref = "";
	}
	ref += "select.select_simple_itens";
	$(ref).select2Simple();
}

function btnFunctions() {
	$('button#salvar').off('click');
	$('button#salvar').on('click', function () {

		let url = $('#urlSalvarModalDepartamento').val();
		let save = {
			idRelTabelasRelatorios: $('#idRelTabelasRelatorios').val(),
			departamento: [],
		};

		if($("table#tabela-departamento tbody tr:not(.ocultar)").length > 0) {
			$("table#tabela-departamento tbody tr:not(.ocultar)").each(function () {
				save.departamento.push({
					nomeDepartamento: $(this).find(".nome-departamento").val()
				});
			});
		}

		toggleLoading();
		ajaxRequest(true, url, null, 'text', {'save': save}, function (ret) {
			try{
				ret = JSON.parse(ret);
				$('.modal_departamento').modal('toggle');
				swal(
					ret['titulo'],
					ret['text'],
					ret['class']
				).catch(swal.noop);
				toggleLoading();
			}catch(err){
				consoleProduction(err);
				swal(
					l["erro!"],
					l["tempoDeRespostaDoServidorEsgotado!"],
					"error"
				).catch(swal.noop);
				forceToggleLoading(0);
			}
		});
	});
}

function criaCostumizacoes() {
	criaSelectsItens();
	btnFunctions();
}

criaCostumizacoes();