/**
 * Created by vitor on 26/08/2017
 */
function acaoDelete() {

    $('.delete').unbind('click');

    $('.delete').on("click", function (e) {

    	e.preventDefault();

    	var obj = $(this);
        var url = $(this).data('url');
        var id = $(this).data('id');
        var tableDataTable = $(this).parents('.table-exibe').DataTable();

        swal({
            title: "Deletar Arquivo?",
            text: "Deseja deletar este arquivo teste?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["desativar!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {

        	toggleLoading();

            ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {

            	console.log(ret);

                if (ret != 0) {

                	swal(
                        "Arquivo deletado!",
                        "Arquivo foi removido do servidor com sucesso!",
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

acaoDelete();