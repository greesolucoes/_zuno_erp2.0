function pararAtualizaMonitoramentoConciliacaoNotas() {
    if(!is_empty(auxiliaresMonitoramentoNotas['ajax'], 1)) auxiliaresMonitoramentoNotas['ajax'].abort();
	auxiliaresMonitoramentoNotas['ajax'] = null;

    if(!is_empty(auxiliaresMonitoramentoNotas['timeout'], 1)) clearTimeout(auxiliaresMonitoramentoNotas['timeout']);
	auxiliaresMonitoramentoNotas['timeout'] = null;
}

function atualizaMonitoramentoConciliacaoNotas(naoPararAtualiza) {
    if(is_empty(naoPararAtualiza, 1)) pararAtualizaMonitoramentoConciliacaoNotas();

	//começa a carregar, para não conseguir clicar em nenhum dia, para não explodir o modal
	toggleLoading();

	auxiliaresMonitoramentoNotas['ajax'] = ajaxRequest(true, $('.data_monitoramento').data('url_monitoramento_ajax'), null, 'text', {'competencia': $('#dataCompetencia').val()}, function (ret) {
        if (!auxiliaresMonitoramentoNotas['travar']) $('div.diagnostico div.indexAjax').html(ret);

		let txtDia = "";
        $('div.diagnostico div.indexAjax table.tabela-monitoramento thead tr th.dia').each(function (indexTableTR) {
			txtDia = "";
			if (!isOldLayout) {
				txtDia += '<div class="dia-content">';
					txtDia += "<span class='bar-status-dia'></span> ";
					txtDia += "<span class='dia-calendario'>";
						txtDia += (indexTableTR + 1).toString().padStart(2, '0');
					txtDia += "</span>";
					txtDia += "<div class='icons-dia'>";
					txtDia += "</div>";
				txtDia += "</div>";
			}

			const diaElement = $('div.diagnostico div.indexAjax table.tabela-monitoramento tbody tr td.dia-' + (indexTableTR + 1));
			!isOldLayout ? diaElement.html(txtDia) : diaElement.text((indexTableTR + 1).toString().padStart(2, '0'));

			txtDia = null;
        });
		toggleLoading();
		auxiliaresMonitoramentoNotas['timeout'] = setTimeout(function () { atualizaMonitoramentoConciliacaoNotas(true); }, 30000);
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
    atualizaMonitoramentoConciliacaoNotas();
});

atualizaMonitoramentoConciliacaoNotas();