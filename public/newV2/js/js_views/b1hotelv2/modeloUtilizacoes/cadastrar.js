acaoDeletarCommom("idB1HV2ModeloUtilizacoes");

function botaoItem(elemento){
	let tableDataTable = $(".table-exibe").DataTable();
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	$.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2ModeloUtilizacoesDePara"]').val(id);
		$('#modalDePara input[name="codigoUtilizacoes"]').val(retorno.codigoUtilizacoes);
		$('#modalDePara input[name="nomeUtilizacoes"]').val(retorno.nomeUtilizacoes);
		$('#modalDePara input[name="codigoSAPUtilizacoes"]').val(retorno.codigoSAPUtilizacoes);
	});
	$('#modalDePara').modal('show');
}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		$('#modalDePara input[name="idB1HV2ModeloUtilizacoesDePara"]').val('');
		$('#modalDePara input[name="codigoUtilizacoes"]').val('');
		$('#modalDePara input[name="nomeUtilizacoes"]').val('');
		$('#modalDePara input[name="codigoSAPUtilizacoes"]').val('');
	});

	$('#modalDePara .salvar').click(function(){
		const action = $(this).data('action');
		toggleLoading();

		$.post(action, {
			guid:$('#modalDePara input[name="idB1HV2ModeloUtilizacoesDePara"]').val(),
			idB1HV2ModeloUtilizacoes:$('input[name="guid"]').val(),
			codigoUtilizacoes:$('#modalDePara input[name="codigoUtilizacoes"]').val(),
			nomeUtilizacoes:$('#modalDePara input[name="nomeUtilizacoes"]').val(),
			codigoSAPUtilizacoes:$('#modalDePara input[name="codigoSAPUtilizacoes"]').val(),
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