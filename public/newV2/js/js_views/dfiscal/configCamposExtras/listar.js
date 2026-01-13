function acaoDeletar() {
    $(".deletar").off("click");
    $(".deletar").on("click", function (e) {
        e.preventDefault();
        var obj = $(this);
        var url = $(obj).data("url");
        var id = $(obj).data("id");
        var tableDataTable = $(this).parents(".table-exibe").DataTable();

        swal({
            title: l["deletarRegistro"],
            text: l["desejaContinuar?"],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: l["continuar!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {"linha": id}, function (ret) {
                ret = JSON.parse(ret);

                swal(
                    ret["titulo"],
                    ret["text"],
                    ret["class"]
                ).catch(swal.noop);
                if(!is_empty(ret["bol"], 1)) {
                    tableDataTable.draw();
                }

                toggleLoading();
            });
        }).catch(swal.noop);
    });
}

acaoDeletar();