function pararAtualizaMonitoramentoFranquia() {
    if(!is_empty(auxiliaresModulos['ajax_monitoramento_franquia'], 1)) auxiliaresModulos['ajax_monitoramento_franquia'].abort();
    auxiliaresModulos['ajax_monitoramento_franquia'] = null;

    if(!is_empty(auxiliaresModulos['timeout_monitoramento_franquia'], 1)) clearTimeout(auxiliaresModulos['timeout_monitoramento_franquia']);
    auxiliaresModulos['timeout_monitoramento_franquia'] = null;
}

function atualizaMonitoramentoFranquia(naoPararAtualiza) {
    if(is_empty(naoPararAtualiza, 1)) pararAtualizaMonitoramentoFranquia();

    auxiliaresModulos['ajax_monitoramento_franquia'] = ajaxRequest(true, $('.data_monitoramento').data('url_monitoramento_ajax'), null, 'text', {'competencia': $('#dataCompetencia').val()}, function (ret) {
        if(is_empty(auxiliaresModulos['travar_monitoramento_franquia'], 1)) $('div.diagnostico div.indexAjax').html(ret);

        $('div.diagnostico div.indexAjax table.tabela-monitoramento thead tr th.dia').each(function (indexTableTR) {
            $('div.diagnostico div.indexAjax table.tabela-monitoramento tbody tr td.dia-' + (indexTableTR + 1)).text(
                (indexTableTR + 1).toString().padStart(2, '0')
            );
        });
        auxiliaresModulos['timeout_monitoramento_franquia'] = setTimeout(function () { atualizaMonitoramentoFranquia(true); }, 30000);
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
    atualizaMonitoramentoFranquia();
});

atualizaMonitoramentoFranquia();