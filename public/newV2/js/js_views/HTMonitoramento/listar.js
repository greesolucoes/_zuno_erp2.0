function pararAtualizaHTMonitoramento() {
    if(!is_empty(auxiliaresMonitoramento['ajax'], 1)) auxiliaresMonitoramento['ajax'].abort();
    auxiliaresMonitoramento['ajax'] = null;

    if(!is_empty(auxiliaresMonitoramento['timeout'], 1)) clearTimeout(auxiliaresMonitoramento['timeout']);
    auxiliaresMonitoramento['timeout'] = null;
}

function atualizaHTMonitoramento(naoPararAtualiza) {
    if(is_empty(naoPararAtualiza, 1)) pararAtualizaHTMonitoramento();

    auxiliaresMonitoramento['ajax'] = ajaxRequest(true, $('.data_monitoramento').data('url_monitoramento_ajax'), null, 'text', {'competencia': $('#dataCompetencia').val()}, function (ret) {
        if(!auxiliaresMonitoramento['travar']) $('div.diagnostico div.indexAjax').html(ret);

        auxiliaresMonitoramento['timeout'] = setTimeout(function () { atualizaHTMonitoramento(true); }, 5000);
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
    atualizaHTMonitoramento();
});

atualizaHTMonitoramento();