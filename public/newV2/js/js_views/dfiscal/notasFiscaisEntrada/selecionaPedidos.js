function acoesTela() {
	$('.table-exibe tbody').on('click', 'tr', function () {
		$(this).toggleClass('selected');
	});

    $('#add-por_pedidos').unbind('click');
    $('#add-por_pedidos').on("click", function (e) {
		toggleLoading();
		let url     = $(this).data('url');
        let selecao = $('.table-exibe').DataTable().rows('.selected').data();

        let itens   = {};
        $.each(selecao, function (idArraySelecao, valueSelecao) {
			itens[selecao[idArraySelecao][0]] = selecao[idArraySelecao][0];
        });

		$.redirect(url, {'pedidos': itens, ...tokenCsrf});
    });
}

acoesTela();