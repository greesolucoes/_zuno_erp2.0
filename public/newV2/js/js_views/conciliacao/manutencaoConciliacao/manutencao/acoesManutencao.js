var disabledDatesManutencao = [];

/**
 * Function fazManutencaoConciliacao
 * Realiza a manutenção da conciliação de acordo com o tipo da manutenção
 */
function fazManutencaoConciliacao(textoAcao, tipoManutencao){
    var obj = $("#data_conciliacao[data-picker='calendar']");
    var dataConciliacao = $(obj).val();
    var url             = $(".data_views").data('url_requisicao_manutencao');
    if(is_empty(url, 1) || is_empty(dataConciliacao, 1)) return null;
    swal({
        title: textoAcao + l["oDia?"],
        text: l["temCertezaDeQueDeseja"] + textoAcao + l["oDia?"],
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: textoAcao,
        cancelButtonText: l["fechar!"]
    }).then(function () {
        toggleLoading();
        ajaxRequest(true, url, null, 'text', {
            'dataConciliacao': dataConciliacao,
            'tipoManutencao': tipoManutencao
        }, function (ret) {
            try{
                ret = JSON.parse(ret);

                if(!is_empty(ret['bol'], 1) && tipoManutencao != 2) {
                    var enabledDatesManutencao = $(obj).data('permitteds');
                    if(is_empty(enabledDatesManutencao, 1)) enabledDatesManutencao = [];
                    else enabledDatesManutencao = enabledDatesManutencao.split(",");

                    dataConciliacao = dateBrToDate(dataConciliacao);
                    disabledDatesManutencao.push(dataConciliacao);

                    var novoArrayManutencao = arrayMenosArray(enabledDatesManutencao, disabledDatesManutencao);
                    var textoNovasDatasPermitidas = '';
                    $.each( novoArrayManutencao, function( indexNovoA, itemA ){
                        textoNovasDatasPermitidas += itemA + ',';
                    });
                    if(novoArrayManutencao.length > 0) textoNovasDatasPermitidas = textoNovasDatasPermitidas.substr(0, textoNovasDatasPermitidas.length - 1);

                    $(obj).data('permitteds', textoNovasDatasPermitidas);

                    $(obj).data("DateTimePicker").destroy();
                    allFunctions();
                }

                swal(
                    ret['titulo'],
                    ret['text'],
                    ret['class']
                ).catch(swal.noop);

                $("#data_conciliacao[data-picker='calendar']").trigger('dp.change');
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
}

/**
 * Function cancelaDiaConciliacao
 * Cancela a conciliação do dia selecionado
 */
function cancelaDiaConciliacao() {
    $(".cancelar_conciliacao").off('click');
    $(".cancelar_conciliacao").on('click', function (e) {
        fazManutencaoConciliacao('Cancelar', 1); //TIPO 1 = CANCELAR
    });
}

/**
 * Function reabreDiaConciliacao
 * Reabre a conciliação do dia selecionado
 */
function reabreDiaConciliacao() {
    $(".reabrir_conciliacao").off('click');
    $(".reabrir_conciliacao").on('click', function (e) {
        fazManutencaoConciliacao('Reabrir', 2); //TIPO 2 = REABRIR
    });
}

cancelaDiaConciliacao();
reabreDiaConciliacao();