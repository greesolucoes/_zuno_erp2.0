$(document).ready(function () {
    // Extrai o token CSRF da meta tag
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    console.log("CSRF Token:", csrfToken);

    // Função auxiliar para enviar requisições PATCH usando $.ajax com log
    function ajaxPatch(url, data, callback) {
        console.log("Enviando PATCH para URL:", url, "com dados:", data, "e CSRF:", csrfToken);
        $.ajax({
            url: url,
            type: 'PATCH',
            data: data,
            dataType: 'text',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(ret) {
                console.log("PATCH sucesso:", ret);
                callback(ret);
            },
            error: function(xhr, status, error) {
                console.error("Erro PATCH:", status, error);
            }
        });
    }

    function acaoDelete() {
        console.log("Ação DELETE: Binding eventos...");
        $('.delete').unbind('click');
        $('.delete').on("click", function (e) {
            e.preventDefault();
            console.log("Delete button clicado");

            var obj = $(this);
            var url = $(this).data('url'); // URL individual do botão (já contém o ID)
            var idUsr = $(this).data('idusr');
            var usr = $(this).data('usr');
            console.log("Dados do botão DELETE:", { url: url, idUsr: idUsr, usr: usr });

            var tableDataTable = $(this).parents('.table-exibe').DataTable();

            var textDeletar = $('.datas_views').data('lang_deletar');
            // Para recriar o botão de ativação, usamos a base da URL e concatenamos: base + idUsr + "/status"
            var urlAtivar = $('.datas_views').data('url_ativar') + idUsr + "/status";
            var master = $('.datas_views').data('master');
            console.log("Dados do container .datas_views:", { textDeletar: textDeletar, urlAtivar: urlAtivar, master: master });

            swal({
                title: l["deletarUsuário?"],
                text: textDeletar,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: l["deletar!"],
                cancelButtonText: l["cancelar!"]
            }).then(function () {
                console.log("Usuário confirmou deleção, chamando toggleLoading e ajaxPatch...");
                toggleLoading();
                ajaxPatch(url, { idUsr: idUsr, status: 'inactive' }, function (ret) {
                    console.log("Resposta ajaxPatch para deleção:", ret);
                    if (ret != 0) {
                        swal(l["usuárioDeletado"], l["oUsuárioDeletadoPoderáSerRecuperadoComSuporteTécnico"], "success");
                        if (master == 0 || master == null || master === '') {
                            tableDataTable.row($(obj).parents('tr')).remove().draw();
                            console.log("Linha removida da tabela.");
                        } else {
                            $(obj).parents('tr').find('td.status .ocultar').text(l["usuárioDesativado"]);
                            $(obj).parents('tr').find('td.status i')
                                .addClass('circle-status-red')
                                .removeClass('circle-status-white')
                                .attr('title', l["usuárioDesativado"]);

                            $(obj).parents('td').find('a.alterar').remove();
                            var novoBotao = '<button data-usr="' + ret + '" ' +
                                             'data-idusr="' + idUsr + '" ' +
                                             'data-url="' + $('.datas_views').data('url_ativar') + idUsr + "/status" + '" ' +
                                             'class="btn btn-primary btn-sm mudaStatus" ' +
                                             'title="Ativar registro">' +
                                                (isOldLayout ? '<i class="fa fa-check"></i>' : '<span data-icon="ic:outline-check" class="iconify"></span>') +
                                             '</button>';
                            $(obj).parents('td').append(novoBotao);
                            console.log("Botão para ativação adicionado:", novoBotao);
                            acaoAtivar();
                            tableDataTable.draw();
                            $(obj).remove();
                        }
                    } else {
                        swal(l["erro"], l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"], "error");
                    }
                    toggleLoading();
                });
            }).catch(swal.noop);
        });
    }

    function acaoAtivar() {
        console.log("Ação ATIVAR: Binding eventos...");
        $('.mudaStatus').unbind('click');
        $('.mudaStatus').on("click", function (e) {
            e.preventDefault();
            console.log("Botão de ativação clicado.");

            var obj = $(this);
            var url = $(this).data('url'); // URL individual do botão (contém o ID)
            var idUsr = $(this).data('idusr');
            var usr = $(this).data('usr');
            console.log("Dados do botão ATIVAR:", { url: url, idUsr: idUsr, usr: usr });

            var tableDataTable = $(this).parents('.table-exibe').DataTable();

            var urlAlterar = $('.datas_views').data('url_alterar') + idUsr;
            var urlDeletar = $('.datas_views').data('url_deletar') + idUsr + "/status";
            console.log("URLs do container .datas_views para ativar:", { urlAlterar: urlAlterar, urlDeletar: urlDeletar });

            swal({
                title: l["ativarUsuário?"],
                text: l["temCertezaDeQueDesejaAtivarEsseUsuário!"],
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: l["ativar!"],
                cancelButtonText: l["cancelar!"]
            }).then(function () {
                console.log("Usuário confirmou ativação, chamando toggleLoading e ajaxPatch...");
                toggleLoading();
                ajaxPatch(url, { idUsr: idUsr, status: 'active' }, function (ret) {
                    console.log("Resposta ajaxPatch para ativação:", ret);
                    if (ret != 0) {
                        swal(l["usuárioAtivado"], l["oUsuárioFoiAtivadoComSucesso!"], "success");

                        $(obj).parents('tr').find('td.status .ocultar').text(l["usuárioAtivado"]);
                        $(obj).parents('tr').find('td.status i')
                            .removeClass('circle-status-red')
                            .addClass('circle-status-white')
                            .attr('title', l["usuárioAtivado"]);

                        var novosBotoes = '<a href="' + urlAlterar + '" ' +
                                               'class="btn btn-warning btn-sm alterar" ' +
                                               'title="' + l["alterarRegistro"] + '">' +
                                                   (isOldLayout ? '<i class="fa fa-pencil"></i>' : '<span data-icon="mingcute:edit-line" class="iconify"></span>') +
                                           '</a>' +
                                           '<button data-usr="' + ret + '" ' +
                                               'data-idusr="' + idUsr + '" ' +
                                               'data-url="' + urlDeletar + '" ' +
                                               'class="btn btn-danger btn-sm delete" ' +
                                               'title="' + l["deletarRegistro"] + '">' +
                                                   (isOldLayout ? '<i class="fa fa-trash-o"></i>' : '<span data-icon="ph:trash-simple-bold" class="iconify"></span>') +
                                           '</button>';
                        $(obj).parents('td').append(novosBotoes);
                        console.log("Botões para editar e deletar adicionados após ativação:", novosBotoes);
                        acaoDelete();
                        tableDataTable.draw();
                        $(obj).remove();
                    } else {
                        swal(l["erro"], l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"], "error");
                    }
                    toggleLoading();
                });
            }).catch(swal.noop);
        });
    }

    // Inicializa as ações
    acaoAtivar();
    acaoDelete();
});
