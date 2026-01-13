/**
 * Created by vitor on 26/08/2017.
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
            title: l["desativarEmpresaGestora?"],
            text: l["casoAEmpresaGestoraSejaDesativada,TodasAsEmpresasEFiliaisCujasQuaisElaTeriaVinculoSerãoDesativadas,DesejaContinuar?"],
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
                        l["empresaGestoraDesativada"],
                        l["aEmpresaGestoraFoiDesativadaEPoderáSerRecuperadaMaisTarde!"],
                        "success"
                    );

                    $(obj).parents('tr').find('td.status .ocultar').text(l["gestoraDesativada"]);
                    $(obj).parents('tr').find('td.status i').addClass('circle-status-red');
                    $(obj).parents('tr').find('td.status i').removeClass('circle-status-white');
                    $(obj).parents('tr').find('td.status i').attr('title', l["gestoraDesativada"]);

                    $(obj).parents('td').find('a.alterar').remove();
                    $(obj).parents('td').append('' +
                        '<button href="#" ' +
                        'data-id="' + id + '" ' +
                        'data-url="' + urlAtivar + '" ' +
                        'class="btn btn-primary btn-sm mudaStatus" ' +
                        'title="' + l["ativarRegistro"] + '">' +
							`${isOldLayout ? '<i class="fa fa-check"></i>' : '<span data-icon="ic:outline-check" class="iconify"></span>'}` +
						'</button>');
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
            title: l["ativarEmpresaGestora?"],
            text: l["temCertezaDeQueDesejaAtivarEssaEmpresaGestora!"],
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
                        l["empresaAtivada"],
                        l["aEmpresaGestoraFoiAtivadaComSucesso!"],
                        "success"
                    );

                    $(obj).parents('tr').find('td.status .ocultar').text(l["gestoraAtivada"]);
                    $(obj).parents('tr').find('td.status i').removeClass('circle-status-red');
                    $(obj).parents('tr').find('td.status i').addClass('circle-status-white');
                    $(obj).parents('tr').find('td.status i').attr('title', l["gestoraAtivada"]);

                    $(obj).parents('td').append('' +
                        '<a href="' + urlAlterar + '" ' +
                        'class="btn btn-warning btn-sm alterar" ' +
                        'title="' + l["alterarRegistro"] + '">' +
							`${isOldLayout ? '<i class="fa fa-pencil"></i>' : '<span data-icon="mingcute:edit-line" class="iconify"></span>'}` +
						'</a>' +
                        '<button href="#" ' +
                        'data-id="' + id + '" ' +
                        'data-url="' + urlDeletar + '" ' +
                        'class="btn btn-danger btn-sm delete" ' +
                        'title="' + l["deletarRegistro"] + '">' +
							`${isOldLayout ? '<i class="fa fa-trash-o"></i>' : '<span data-icon="ph:trash-simple-bold" class="iconify"></span>'}` +
						'</button>');
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