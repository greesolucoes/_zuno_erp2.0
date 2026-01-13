function excluirReg() {
    $('.excluirReg').unbind('click');
    $('.excluirReg').on("click", function (e) {
        e.preventDefault();
        var obj            = $(this);
        var url            = $(obj).data('url');
        var id             = $(obj).data('id');
        var tableDataTable = $(obj).parents('.table-exibe').DataTable();

        swal({
            title: l["excluirObservação?"],
            text: l["casoConfirmeAExclusão,AObservaçãoSeráExcluídaDoBancoDeDados,Continuar?"],
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
                        l["observaçãoExcluída"],
                        l["aObservaçãoFoiExcluídaComSucesso!"],
                        "success"
                    );
                    tableDataTable.row($(obj).parents('tr')).remove().draw();
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

excluirReg();