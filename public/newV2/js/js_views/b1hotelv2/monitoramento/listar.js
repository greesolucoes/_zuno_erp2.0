function pararAtualizaMonitoramentoB1HV2() {
    if(!is_empty(auxiliaresMonitoramento['ajax'], 1)) auxiliaresMonitoramento['ajax'].abort();
    auxiliaresMonitoramento['ajax'] = null;

    if(!is_empty(auxiliaresMonitoramento['timeout'], 1)) clearTimeout(auxiliaresMonitoramento['timeout']);
    auxiliaresMonitoramento['timeout'] = null;
}

function atualizaMonitoramentoB1HV2(naoPararAtualiza) {
    if(is_empty(naoPararAtualiza, 1)) pararAtualizaMonitoramentoB1HV2();
	let select_filiais = $("select#geral-filiais");
    auxiliaresMonitoramento['ajax'] = ajaxRequest(true, $('.data_monitoramento').data('url_monitoramento_ajax'), null, 'text',
		{
			'competencia': $('#dataCompetencia').val(),
			'filiais': toBase64($(select_filiais).val().join(","))
		}, function (ret) {
        if(!auxiliaresMonitoramento['travar']) $('div.diagnostico div.indexAjax').html(ret);

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
        auxiliaresMonitoramento['timeout'] = setTimeout(function () { atualizaMonitoramentoB1HV2(true); }, 30000);
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
    atualizaMonitoramentoB1HV2();
});

$('#geral-filiais').on('select2:select', function (e) {
	atualizaMonitoramentoB1HV2();
});

function criaComponentes() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');

	atualizaMonitoramentoB1HV2();
}

$(document).on('click', '#geral-filiais-div .select2-selection__clear', function() {
	setTimeout(function(){
		atualizaMonitoramentoB1HV2();
	},200);
});

$("#geral-filiais").on("select2:unselecting", function(e) {
	setTimeout(function(){
		atualizaMonitoramentoB1HV2();
	},200);
});

criaComponentes();
atualizaMonitoramentoB1HV2();