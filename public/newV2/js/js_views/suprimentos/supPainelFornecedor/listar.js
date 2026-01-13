/**
 * Reutilizado por Denis Amaral
 * Ação de exclusão de usuário do painel de fornecedores - Code Base: js_views/empresas/listar.js
 * Criado por Vitor on 26/08/2017.
 */
function acaoDelete() {

    $('.delete').unbind('click');

    $('.delete').on("click", function (e) {

        e.preventDefault();

        var obj = $(this);
        var url = $(this).data('url');
        var id 	= $(this).data('id');

        swal({
            title: l["deletarUsuário?"],
            text: l["estaAçãoÉIrreversívelDesejaProsseguir?"],
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
						l["usuárioDesativado"],
						l["usuárioExcluídoComSucesso!"],
						"success"
                    );

                    $(obj).parents('tr').remove();

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
