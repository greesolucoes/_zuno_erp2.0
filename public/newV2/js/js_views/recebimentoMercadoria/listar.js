/**
 * Created by vitor on 26/08/2017.
 */
function showModals() {
    $('button.tipoCadastro').unbind('click');
    $('button.tipoCadastro').click(function () {
        $('.modalTipoCadastro').modal('toggle');
    });
}

function uploadReg() {
    $('.uploadERP').unbind('click');
    $('.uploadERP').on("click", function (e) {
        e.preventDefault();
        var obj            = $(this);
        var url            = $(obj).data('url');
        var id             = $(obj).data('id');
        var tableDataTable = $(obj).parents('.table-exibe').DataTable();

        swal({
            title: l["reenviarRecebimento?"],
            text: l["casoConfirmeOReenvio,ORecebimentoSeráReenviadoParaAAprovaçãoNoErp,Continuar?"],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["reenviar!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
                if (ret != 0) {
                    swal(
                        l["recebimentoReenviado"],
                        l["oRecebimentoFoiReenviadoComSucesso!"],
                        "success"
                    );

                    tableDataTable.draw();
                } else {
                    swal(
                        l["erro"],
                        l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"],
                        "error"
                    );
                }
                toggleLoading();
            });
        }).catch(swal.noop);
    });
}

function excluirReg() {
    $('.excluirReg').unbind('click');
    $('.excluirReg').on("click", function (e) {
        e.preventDefault();
        var obj            = $(this);
        var url            = $(obj).data('url');
        var id             = $(obj).data('id');
        var tableDataTable = $(obj).parents('.table-exibe').DataTable();

        swal({
            title: l["excluirRecebimento?"],
            text: l["casoConfirmeAExclusão,ORecebimentoSeráExcluídoDoBancoDeDados,Continuar?"],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["excluir!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
                if (ret != 0) {
                    swal(
                        l["recebimentoExcluído"],
                        l["oRecebimentoFoiExcluídoComSucesso!"],
                        "success"
                    );

                    tableDataTable.draw();
                } else {
                    swal(
                        l["erro"],
                        l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"],
                        "error"
                    );
                }
                toggleLoading();
            });
        }).catch(swal.noop);
    });
}

function acionarAcoesListagemRecebimentosMercadorias() {
    showModals();
    uploadReg();
    excluirReg();
}

acionarAcoesListagemRecebimentosMercadorias();