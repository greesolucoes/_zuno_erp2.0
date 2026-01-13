function criaSelects() {

	$("#filialSelecionada").select2Simple();
	$("#filialSelecionada").data('init', '');

}

function formatDate(valDate) {
	if(is_empty(valDate, 1)) {
		return null;
	}

	return moment(valDate, "DD/MM/YYYY").format('YYYY-MM-DD');
}

// marca as linhas de notas selecionadas
function seleciona() {
	$('.table-exibe tbody').on( 'click', 'tr', function () {
		$(this).toggleClass('selected');
	} );
}

// altera as notas
function acaoAlteraNotas() {
	$('.alteraNotas').unbind('click');
	$('.alteraNotas').on("click", function (e) {
		e.preventDefault();

		var selecao = $('.table-exibe').DataTable().rows('.selected').data();
		var url     = $(this).data('url');
		var dataDoc = formatDate($('#novaDataDoc').val());
		var itens   = [];

		$.each(selecao, function (idArraySelecao, valueSelecao) {
			itens.push({
				idNotaFiscalSaida: selecao[idArraySelecao][11], 	// posicao do td onde tem o id da nota
				idFilial: selecao[idArraySelecao][12]   			// posicao do td onde tem o id da filial
			});
		});

		// obtem a filial da pesquisa
		var filialSelecionada = $("#filialSelecionada").val();

		if(itens.length <= 0){
			swal(
				'Atenção',
				'Selecione alguma nota para alterar',
				'info'
			);
		}else{
			$.redirect(url, {
				'filialSelecionada': filialSelecionada,
				'notas': itens,
				'novaDataDoc': dataDoc,
				'dataInicial': $('#dataInicial').val(),
				'dataFinal': $('#dataFinal').val(),
				...tokenCsrf
			}, "POST", "_self");
		}
	});
}

acaoAlteraNotas();
criaSelects();
seleciona();


