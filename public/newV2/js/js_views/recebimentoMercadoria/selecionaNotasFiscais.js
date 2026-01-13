function seleciona() {
    $('.table-exibe tbody').on( 'click', 'tr', function () {
        $(this).toggleClass('selected');
    } );
}

function acaoNotasFiscais() {
    $('.recebeNotasFiscais').unbind('click');
    $('.recebeNotasFiscais').on("click", function (e) {
        e.preventDefault();
        $('.loading').css("display", "block");

		var selecao = $('.table-exibe').DataTable().rows('.selected').data();
		var url     = $(this).data('url');
		var itens   = [];

		$.each(selecao, function (idArraySelecao, valueSelecao) {
			itens.push({
				idNotaFiscal: selecao[idArraySelecao][0]
			});
		});

		$.redirect(url, {
			'notasfiscaissaidas': itens,
			...tokenCsrf
		});
    });
}

acaoNotasFiscais();
seleciona();