function pararAtualizaMonitoramentoConciliacao() {
    if(!is_empty(auxiliaresMonitoramento['ajax'], 1)) auxiliaresMonitoramento['ajax'].abort();
    auxiliaresMonitoramento['ajax'] = null;

    if(!is_empty(auxiliaresMonitoramento['timeout'], 1)) clearTimeout(auxiliaresMonitoramento['timeout']);
    auxiliaresMonitoramento['timeout'] = null;
}

function atualizaMonitoramentoConciliacao(naoPararAtualiza) {
    if(is_empty(naoPararAtualiza, 1)) pararAtualizaMonitoramentoConciliacao();

    auxiliaresMonitoramento['ajax'] = ajaxRequest(true, $('.data_monitoramento').data('url_monitoramento_ajax'), null, 'text', {'competencia': $('#dataCompetencia').val()}, function (ret) {
        if(!auxiliaresMonitoramento['travar']) {
			$('div.diagnostico div.indexAjax').html(ret);
		}

		//A cada 5m atualiza a tabela automaticamente
        auxiliaresMonitoramento['timeout'] = setTimeout(function () { atualizaMonitoramentoConciliacao(true); }, 300000);
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
    atualizaMonitoramentoConciliacao();
});

atualizaMonitoramentoConciliacao();

$(document).on('click', '.tabela-monitoramento > tbody > tr', function() {
	$(this).toggleClass('linha-destacada');
});

$(document).on('click', '.tabela-monitoramento > thead > tr > th', function() {
	const coluna = $(this).data('y');

	$(this).toggleClass('coluna-destacada');
	$('tbody > tr > td').each(function () {
		if ($(this).data('y') == coluna) {
			$(this).toggleClass('coluna-destacada')
		}
	});
});

