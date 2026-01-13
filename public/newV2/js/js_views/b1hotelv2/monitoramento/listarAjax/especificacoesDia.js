/**
 * Function realizaManutencaoMonitoramento
 * Realiza a manutenção da conciliação de acordo com o tipo da manutenção
 */
function realizaManutencaoMonitoramento(){
    var obj = $("#modal_especificacoes_dia .modal-content");
    var dtMon    = $(obj).find('.data_modal_especificacoes_dia').data('mon_date');
    var idFilial = $(obj).find('.data_modal_especificacoes_dia').data('id_filial');
    var url      = $(obj).find('.data_especificacoes_dia').data('url_acoes');
    
    if(is_empty(url, 1) || is_empty(idFilial, 1) || is_empty(dtMon, 1)) {
		return null;
	}
    $($(obj).find('.btn-acao')).off("click");
    $($(obj).find('.btn-acao')).on("click", function (e) {
        e.preventDefault();
        var tipoAcao = $(this).data('tipo_acao');

        swal({
            title: $($(this).parent()).attr("title") ?? '',
            text: l["desejaContinuar?"],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["continuar!"],
            cancelButtonText: l["fechar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {
                'dtMon': dtMon,
                'idFilial': idFilial,
                'tipoAcao': tipoAcao
            }, function (ret) {
            	// console.log(ret);
                try{
                    ret = JSON.parse(ret);

                    swal(
                        ret['titulo'],
                        ret['text'],
                        ret['class']
                    ).catch(swal.noop);

                    if(
						!is_empty(ret['bol'], 1) &&
						!is_empty(dtMon, 1) &&
						!is_empty(idFilial, 1)
					) {
                        $($('tr[data-id_filial="' + idFilial + '"]').find('td.td-data-monitoramento[data-mon_date="' + dtMon + '"]')).trigger('click');
                    }
                    toggleLoading();
                }catch(err){
                    swal(
                        l["erro!"],
                        l["tempoDeRespostaDoServidorEsgotado!"],
                        "error"
                    ).catch(swal.noop);
                    forceToggleLoading(0);
                    // console.log(err);
                    // consoleSystem(err, 'error');
                }
            });
        }).catch(swal.noop);
    });
}

realizaManutencaoMonitoramento();