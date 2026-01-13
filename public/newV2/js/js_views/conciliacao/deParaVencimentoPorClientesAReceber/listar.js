function acaoDeletar() {
    $(".deletar").unbind("click");
    $(".deletar").on("click", function (e) {
        e.preventDefault();
        var obj = $(this);
        var url = $(obj).data("url");
        var linha = $(obj).data("linha");
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
            ajaxRequest(true, url, null, 'text', {"linha": linha}, function (ret) {
                ret = JSON.parse(ret);

                swal(
                    ret["titulo"],
                    ret["text"],
                    ret["class"]
                ).catch(swal.noop);
                if(!is_empty(ret["bol"], 1)) {
                    tableDataTable.draw();
                    $('table#relatorio-de-para_table tbody tr[data-linha="' + linha + '"]').remove();
                }

                toggleLoading();
            });
        }).catch(swal.noop);
    });
}

function downloadDePara(){
    $('button.printar-valores').unbind('click');
    $('button.printar-valores').on('click', function() {
        var titulo = $(this).attr('title');

        save2excel($('table#relatorio-de-para_table'), {
            not: null,
            name: titulo,
            filename: (titulo + '.xls')
        });
    });
}

downloadDePara();
acaoDeletar();