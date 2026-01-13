function acaoDesativar() {
    $('.desativar').unbind('click');
    $('.desativar').on("click", function (e) {
        e.preventDefault();
        var obj = $(this);
        var url = $(obj).data('url');
        var id = $(obj).data('id');
        var tableDataTable = $(this).parents('.table-exibe').DataTable();

        var urlAtivar = $('.datas_views').data('url_ativar');

        swal({
            title: l["desativarJustificativa"],
            text: l["desejaContinuar?"],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l['desativar!'],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
                if (ret != 0) {
                    swal(
                        l["justificativaDesativada"],
                        l["aJustificativaFoiDesativada!"],
                        "success"
                    );

                    $(obj).parents('tr').find('td.status .ocultar').text(l['justificativaDesativada']);
                    $(obj).parents('tr').find('td.status i').addClass('circle-status-red');
                    $(obj).parents('tr').find('td.status i').removeClass('circle-status-white');
                    $(obj).parents('tr').find('td.status i').attr('title', l['justificativaDesativada']);

                    $(obj).parents('td').find('a.alterar').remove();
                    $(obj).parents('td').append(
                        '<button href="#" data-id="'+ id +'" data-url="'+ urlAtivar +'" class="btn btn-primary btn-sm ativar" title="'+ l["ativarRegistro"] + '">' +
							(isOldLayout ? '<i class="fa fa-check"></i>' : '<span data-icon="material-symbols:check" class="iconify"></span>') +
						'</button>'
					);
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
    $('.ativar').unbind('click');
    $('.ativar').on("click", function (e) {
        e.preventDefault();
        var obj = $(this);
        var url = $(obj).data('url');
        var id = $(obj).data('id');
        var tableDataTable = $(this).parents('.table-exibe').DataTable();

        var urlAlterar = $('.datas_views').data('url_alterar') + id;
        var urlDeletar = $('.datas_views').data('url_desativar');
        swal({
            title: l["ativarJustificativa"],
            text: l["desejaContinuar?"],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l['ativar!'],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
                if (ret != 0) {
                    swal(
                        l["justificativaAtivada"],
                        l["aJustificativaFoiAtivadaComSucesso!"],
                        "success"
                    );

                    $(obj).parents('tr').find('td.status .ocultar').text(l['justificativaAtivada']);
                    $(obj).parents('tr').find('td.status i').removeClass('circle-status-red');
                    $(obj).parents('tr').find('td.status i').addClass('circle-status-white');
                    $(obj).parents('tr').find('td.status i').attr('title', l['justificativaAtivada']);

                    $(obj).parents('td').append(
                        '<a href="'+ urlAlterar +'" class="btn btn-warning btn-sm alterar" title="'+ l["alterarRegistro"] +'">' +
							(isOldLayout ? '<i class="fa fa-pencil"></i>' : '<span data-icon="mingcute:edit-line" class="iconify"></span>') +
						'</a>' +
                        '' +
                        '<button href="#" data-id="'+ id +'" data-url="'+ urlDeletar +'" class="btn btn-danger btn-sm desativar" title="'+ l['desativarRegistro'] +'">' +
							(isOldLayout ? '<i class="fa fa-times"></i>' : '<span data-icon="ph:trash-simple-bold" class="iconify"></span>') +
						'</button>'
					);
                    acaoDesativar();

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

acaoDesativar();
acaoAtivar();