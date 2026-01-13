function reenviarPgtosCardServices() {

    $('button.btn-reimportar-pgto').off('click');

    $('button.btn-reimportar-pgto').on("click", function (e) {

    	e.preventDefault();
        const idConc = $(this).data('id_conciliacao');
        const url = $(this).data('url');

        swal({
            title: l["desejaContinuar?"],
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["sim!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {'id_conciliacao': idConc}, function (ret) {
                try{
                    ret = JSON.parse(ret);
                    swal(
                        ret['titulo'],
                        ret['text'],
                        ret['class']
                    ).catch(swal.noop);

                    toggleLoading();
                }catch(err){
                    swal(
                        l["erro!"],
                        l["tempoDeRespostaDoServidorEsgotado!"],
                        "error"
                    ).catch(swal.noop);
                    forceToggleLoading(0);
                }
            });
        }, function () {
            //SE DER ERRO
        }).catch(swal.noop);
    });

}

reenviarPgtosCardServices();
