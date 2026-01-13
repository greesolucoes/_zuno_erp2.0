/**
 * Created by vitor on 26/08/2017.
 */
function seleciona() {
    $('.table-exibe tbody').on( 'click', 'tr', function () {
        $(this).toggleClass('selected');
    } );
}

function acaoRecebePedidos() {
    $('.recebePedidos').unbind('click');
    $('.recebePedidos').on("click", function (e) {
        e.preventDefault();
        $('.loading').css("display", "block");

        var selecao = $('.table-exibe').DataTable().rows('.selected').data();
        var url     = $(this).data('url');
        var itens   = [];

        $.each(selecao, function (idArraySelecao, valueSelecao) {
            itens.push({
                idPedido: selecao[idArraySelecao][0]
            });
        });

		$.redirect(url, {
			'pedidos': itens,
			...tokenCsrf
		});
    });
}

acaoRecebePedidos();
seleciona();