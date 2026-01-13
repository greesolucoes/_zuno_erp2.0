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

        var urlAtivar = $('.datas_views').data('url_ativar');

        swal({
            title: "Desativar Servidor?",
            text: "Deseja desativar este servidor?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["desativar!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {

        	toggleLoading();

            ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {

                if (ret != 0) {

                	swal(
                        "Servidor Desativado!",
                        "Servidor foi desativado e poderá ser reativado a qualquer momento.",
                        "success"
                    );

                    $(obj).parents('tr').find('td.status .ocultar').text("Servidor desativado");
                    $(obj).parents('tr').find('td.status i').attr('title', "Servidor desativado");

                    $(obj).parents('td').find('a.alterar').remove();

                    $(obj).parents('td').append('' + '<button href="#" ' + 'data-id="' + id + '" ' + 'data-url="' + urlAtivar + '" ' + 'class="btn btn-primary btn-sm fa fa-check mudaStatus" ' + 'title="Ativar registro" alt="Ativar registro"></button>');

                    acaoAtivar();

                    tableDataTable.draw();

                    $(obj).remove();

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

function acaoAtivar() {

	$('.mudaStatus').unbind('click');

    $('.mudaStatus').on("click", function (e) {

    	e.preventDefault();

        var obj = $(this);
        var url = $(this).data('url');
        var id = $(this).data('id');
        var tableDataTable = $(this).parents('.table-exibe').DataTable();

        var urlAlterar = $('.datas_views').data('url_alterar') + id;
        var urlDeletar = $('.datas_views').data('url_deletar');

        swal({
            title: "Ativar Servidor?",
            text: "Tem certeza de que deseja ativar este servidor?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["ativar!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {

            toggleLoading();

            ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {

            	if (ret != 0) {

            		swal(
                        "Servidor ativado!",
                        "O servidor foi ativado com sucesso!",
                        "success"
                    );

                    $(obj).parents('tr').find('td.status .ocultar').text("Servidor ativado");
                    $(obj).parents('tr').find('td.status i').attr('title', "Servidor ativado");

                    $(obj).parents('td').append('' +
                        '<a href="' + urlAlterar + '" ' +
                        'class="btn btn-warning btn-sm fa fa-pencil alterar" ' +
                        'title="' + l["alterarRegistro"] + '"></a>' +
                        '<button href="#" ' +
                        'data-id="' + id + '" ' +
                        'data-url="' + urlDeletar + '" ' +
                        'class="btn btn-danger btn-sm fa fa-trash-o delete" ' +
                        'title="' + l["deletarRegistro"] + '"></button>');

                    acaoDelete();

                    tableDataTable.draw();
                    $(obj).remove();

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
acaoAtivar();