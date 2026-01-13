// para de atualizar o monitoramento automaticamente apos abrir modal
$('#modal_especificacoes_dia').unbind('shown.bs.modal');
$(document).on('shown.bs.modal', '#modal_especificacoes_dia', function () {
	auxiliaresMonitoramento['travar'] = true;
    pararAtualiza();
});

/**
 * Function funcoesModalEspecificacoesDia
 * Define todas as funções do modal de especificações do dia
 */
function funcoesModalEspecificacoesDia() {
    var ultimoAjaxModalEspecificacoesDia = null;

    /**
     * Function ativaTriggerClickDia
     * Ativa triggers para ao clicar no dia do monitoramento, abrir pop-up
     */
    function ativaTriggerClickDia() {
        $(".td-data-monitoramento").off('click');
        $(".td-data-monitoramento").on('click', function (e) {

            if(!is_empty(ultimoAjaxModalEspecificacoesDia, 1)) ultimoAjaxModalEspecificacoesDia.abort();
            ultimoAjaxModalEspecificacoesDia = null;

            var dataMonitoramento = $(this).data('mon_date');
            var idFilialMonitoramento = $(this).parents('tr').data('id_filial');
            var url = $(this).parents('table').data('url');

            $('#modal_especificacoes_dia .data_modal_especificacoes_dia').data('mon_date', dataMonitoramento);
            $('#modal_especificacoes_dia .data_modal_especificacoes_dia').data('id_filial', idFilialMonitoramento);

            if(!(($('#modal_especificacoes_dia').data('bs.modal') || {})._isShown || ($('#modal_especificacoes_dia').data('bs.modal') || {}).isShown))
                $('#modal_especificacoes_dia').modal('show');

            toggleLoadingOnDivSmall($('#modal_especificacoes_dia .modal-body'), true);
            ultimoAjaxModalEspecificacoesDia = ajaxRequest(true, url, null, 'text', {
                'dataMonitoramento': dataMonitoramento,
                'idFilialMonitoramento': idFilialMonitoramento
            }, function(ret){
                ret = $.parseJSON(ret);

                $('#modal_especificacoes_dia .modal-header .titulo_especificacoes').html(ret['titulo']);
                $("#modal_especificacoes_dia .modal-body").html(ret['corpo']);
            });
        });

        $(".td-data-monitoramento").off('taphold');
        $(".td-data-monitoramento").on('taphold', function (e) {
            $(this).trigger('click');
        })
    }

    /**
     * Function onDismissModalEspecificacoesDia
     * Ao fechar modal, excluir informações do mesmo
     */
    function onDismissModalEspecificacoesDia() {
        $('#modal_especificacoes_dia').unbind('hidden.bs.modal');
        $('#modal_especificacoes_dia').on('hidden.bs.modal', function () {
            if(!is_empty(ultimoAjaxModalEspecificacoesDia)) ultimoAjaxModalEspecificacoesDia.abort();
            ultimoAjaxModalEspecificacoesDia = null;

            $(this).find('.data_modal_especificacoes_dia').data('mon_date', '');
            $(this).find('.data_modal_especificacoes_dia').data('id_filial', '');
            $(this).find('.modal-body').html('');
            $(this).find('.modal-header .titulo_especificacoes').html('');

            auxiliaresMonitoramento['travar'] = false;
            atualiza();
        })
    }

    ativaTriggerClickDia();
    onDismissModalEspecificacoesDia();
}

funcoesModalEspecificacoesDia();