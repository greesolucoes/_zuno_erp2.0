function pararAtualizaMonitoramento() {
    if(!is_empty(auxiliaresMonitoramento['ajax'], 1)) auxiliaresMonitoramento['ajax'].abort();
    auxiliaresMonitoramento['ajax'] = null;

    if(!is_empty(auxiliaresMonitoramento['timeout'], 1)) clearTimeout(auxiliaresMonitoramento['timeout']);
    auxiliaresMonitoramento['timeout'] = null;
}

function exibirImportacaoPN() {
	$('#integracao').off('change').on('change', function() {
		$("#boccusTable").addClass("d-none");
		$("#openFolioTable").addClass("d-none");
		$("#legenda-monitoramento").hide();
		$("#monitoramento-dia").hide();

		let integracao= $('#integracao').val();

		// TODO VALIDAR
		if(['importacao_coleta_pn','integracao_open_folio'].includes(integracao)){
			$($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
			pesquisaPersonalizada();
		}

		switch(integracao){
			case 'importacao_coleta_pn':
				$("#boccusTable").removeClass("d-none");
				break;
			case 'integracao_open_folio':
				$("#openFolioTable").removeClass("d-none");
				break;
			default:
				$("#legenda-monitoramento").show();
				$("#monitoramento-dia").show();
		}

	});

	$("[data-picker='date-month-right-now']").datetimepicker({
		locale: _lang,
		viewMode: 'months',
		format: $('.data_monitoramento').data('formato_date_time_picker'),
		useCurrent: true,
		widgetPositioning: {
			vertical: 'bottom',
			horizontal: 'right'
		}
	}).on('dp.change', function(e){
		pesquisaPersonalizada();
	});

	$('#geral-filiais').on('select2:select', function (e) {
		pesquisaPersonalizada();
	});
}

function pesquisaPersonalizada() {
	['#boccusTable .table-exibe','#openFolioTable .table-exibe'].forEach(function(el){
		let __acaoAtualizaDataTable = function () {
			// const ref_table_search = "#boccusTable .table-exibe";
			const ref_table_search = $(el);
			let periodo = $("#dataCompetencia");
			let select_filiais = $("select#geral-filiais");
			let gets_url = "";
			let dataTable = null;

			let url_table = ref_table_search.data("url_principal");

			let params = [];
			if(!is_empty($(periodo).val(), 1)) {
				params.push("periodo=" + $(periodo).val());
			}
			if (select_filiais && select_filiais.length > 0) {
				params.push("filiais=" + toBase64($(select_filiais).val().join(",")));
			}

			if (params.length > 0) {
				url_table += "?" + params.join("&");
			}

			ref_table_search.each(function (){
				if($.fn.DataTable.isDataTable(this)) {
					dataTable = $(this).DataTable();
					dataTable.clear();
					dataTable.destroy();
				}
			});

			// TODO ajustar para parar de chamar todas as datatables
			//console.log(url_table);

			ref_table_search.data("url_ajax", url_table);
			allTables();
		}

		__acaoAtualizaDataTable();
	});
}

function atualizaMonitoramento(naoPararAtualiza) {
    if(is_empty(naoPararAtualiza, 1)) pararAtualizaMonitoramento();
	let select_filiais = $("select#geral-filiais");
    auxiliaresMonitoramento['ajax'] =
		ajaxRequest(
			true,
			$('.data_monitoramento').data('url_monitoramento_ajax'),
			null,
			'text',
			{
				'competencia': $('#dataCompetencia').val(),
				'filiais': toBase64($(select_filiais).val().join(",")),
				'integracao': $('#integracao').val(),
			},
			function (ret) {
				if(!auxiliaresMonitoramento['travar']) {
					$('div.diagnostico div.indexAjax').html(ret);
				}

				let txtDia = "";
				$('div.diagnostico div.indexAjax table.tabela-monitoramento thead tr th.dia').each(function (indexTableTR) {
					//Classe dia_has_nota se o dia tiver nota inserida sem a finalização da conciliação
					$('div.diagnostico div.indexAjax table.tabela-monitoramento tbody tr td.dia-' + (indexTableTR + 1)).each(function () {
						txtDia = "<div class='centraliza'>";
						txtDia += (indexTableTR + 1).toString().padStart(2, '0');
						txtDia += "</div>";

						$(this).html(txtDia);
						txtDia = null;
					});
				});
				auxiliaresMonitoramento['timeout'] = setTimeout(function () { atualizaMonitoramento(true); }, 30000);
			});
}

$("[data-picker='date-month-right-now']").datetimepicker({
    locale: _lang,
    viewMode: 'months',
    format: $('.data_monitoramento').data('formato_date_time_picker'),
    useCurrent: true,
    widgetPositioning: {
        vertical: 'bottom',
        horizontal: 'right'
    }
}).on('dp.change', function(e){
	atualizaMonitoramento();
});

$("#integracao").off("select2:select");
$("#integracao").on("select2:select", function () {
	atualizaMonitoramento();
});

$('#geral-filiais').on('select2:select', function (e) {
	atualizaMonitoramento();
});

function acoesBtnTableOpenFolio(){
	$('#openFolioTable button.btn-reenviar').off('click').on('click',function(){

		let tableDataTable = $("#table-open-folio").DataTable();
		let url= $(this).data('url');

		swal({
			title: l["atenção!"],
			text: l["reenviar!"].replace("!","?"),
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
					ret = JSON.parse(ret);
					swal(
						l["atenção!"],
						ret["text"],
						ret["icon"]
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

$("select.select_ajax").select2Ajax();
$("select.select_ajax").data('init', '');

$(document).on('click', '#geral-filiais-div .select2-selection__clear', function() {
	setTimeout(function(){
		atualizaMonitoramento();
	},200);
});

$("#geral-filiais").on("select2:unselecting", function(e) {
	setTimeout(function(){
		atualizaMonitoramento();
	},200);
});

atualizaMonitoramento();
exibirImportacaoPN();
pesquisaPersonalizada();