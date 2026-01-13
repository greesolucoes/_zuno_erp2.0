acaoDeletarCommom("idB1HV2ModeloFormasPagamento");

function botaoItem(elemento){
	let tableDataTable = $(".table-exibe").DataTable();
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	$.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2ModeloFormasPagamentoDePara"]').val(id);
		$('#modalDePara input[name="codigoFormasPagamento"]').val(retorno.codigoFormasPagamento);
		$('#modalDePara input[name="nomeFormasPagamento"]').val(retorno.nomeFormasPagamento);
		$('#modalDePara input[name="codigoSAPFormasPagamento"]').val(retorno.codigoSAPFormasPagamento);
	});
	$('#modalDePara').modal('show');
}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		$('#modalDePara input[name="idB1HV2ModeloFormasPagamentoDePara"]').val('');
		$('#modalDePara input[name="codigoFormasPagamento"]').val('');
		$('#modalDePara input[name="nomeFormasPagamento"]').val('');
		$('#modalDePara input[name="codigoSAPFormasPagamento"]').val('');
		$('.salvar').attr('is-add', 0);
		$('#modalDePara .titulo-modal').html(l['Alterar']);
	});

	$('.abrir-modal-de-para').on('click', function () {
		$('#modalDePara input[name="idB1HV2ModeloFormasPagamentoDePara"]').val('');
		$('#modalDePara input[name="codigoFormasPagamento"]').val('');
		$('#modalDePara input[name="nomeFormasPagamento"]').val('');
		$('#modalDePara input[name="codigoSAPFormasPagamento"]').val('');
		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
		$('.salvar').attr('is-add', 1);
		$('#modalDePara').modal('show');
	});

	$('#modalDePara .salvar').click(function(){
		const action = $(this).data('action');
		let is_add = $('.salvar').attr('is-add');
		toggleLoading();

		$.post(action, {
			guid:$('#modalDePara input[name="idB1HV2ModeloFormasPagamentoDePara"]').val(),
			idB1HV2ModeloFormasPagamento:$('input[name="guid"]').val(),
			codigoFormasPagamento:$('#modalDePara input[name="codigoFormasPagamento"]').val(),
			nomeFormasPagamento:$('#modalDePara input[name="nomeFormasPagamento"]').val(),
			isAdd: is_add,
			codigoSAPFormasPagamento:$('#modalDePara input[name="codigoSAPFormasPagamento"]').val(),
			...tokenCsrf
		}, function(retorno) {
			if (retorno.class == 'success'){
				let tableDataTable = $(".table-exibe").DataTable();
				$('#modalDePara').modal('hide');
				tableDataTable.draw();
			}
			addMessage(retorno);
			toggleLoading();
		});
	});
}

salvar();