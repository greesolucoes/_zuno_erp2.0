acaoDeletarCommom("idB1HV2ModeloCondicoesPagamento");

function botaoItem(elemento){
	let tableDataTable = $(".table-exibe").DataTable();
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	$.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2ModeloCondicoesPagamentoDePara"]').val(id);
		$('#modalDePara input[name="codigoCondicoesPagamento"]').val(retorno.codigoCondicoesPagamento);
		$('#modalDePara input[name="nomeCondicoesPagamento"]').val(retorno.nomeCondicoesPagamento);
		$('#modalDePara input[name="codigoSAPCondicoesPagamento"]').val(retorno.codigoSAPCondicoesPagamento);
	});
	$('#modalDePara').modal('show');
}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		$('#modalDePara input[name="idB1HV2ModeloCondicoesPagamentoDePara"]').val('');
		$('#modalDePara input[name="codigoCondicoesPagamento"]').val('');
		$('#modalDePara input[name="nomeCondicoesPagamento"]').val('');
		$('#modalDePara input[name="codigoSAPCondicoesPagamento"]').val('');
		$('.salvar').attr('is-add', 0);
		$('#modalDePara .titulo-modal').html(l['Alterar']);
	});

	$('.abrir-modal-de-para').on('click', function () {
		$('#modalDePara input[name="idB1HV2ModeloCondicoesPagamentoDePara"]').val('');
		$('#modalDePara input[name="codigoCondicoesPagamento"]').val('');
		$('#modalDePara input[name="nomeCondicoesPagamento"]').val('');
		$('#modalDePara input[name="codigoSAPCondicoesPagamento"]').val('');
		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
		$('.salvar').attr('is-add', 1);
		$('#modalDePara').modal('show');
	});

	$('#modalDePara .salvar').click(function(){
		const action = $(this).data('action');
		let is_add = $('.salvar').attr('is-add');
		toggleLoading();

		$.post(action, {
			guid:$('#modalDePara input[name="idB1HV2ModeloCondicoesPagamentoDePara"]').val(),
			idB1HV2ModeloCondicoesPagamento:$('input[name="guid"]').val(),
			codigoCondicoesPagamento:$('#modalDePara input[name="codigoCondicoesPagamento"]').val(),
			nomeCondicoesPagamento:$('#modalDePara input[name="nomeCondicoesPagamento"]').val(),
			isAdd: is_add,
			codigoSAPCondicoesPagamento:$('#modalDePara input[name="codigoSAPCondicoesPagamento"]').val(),
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