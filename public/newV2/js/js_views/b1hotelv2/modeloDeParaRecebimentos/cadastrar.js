acaoDeletarCommom();

function botaoItem(elemento){
	let tableDataTable = $(".table-exibe").DataTable();
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	$.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2DeParaRecebimentos"]').val(id);
		$('#modalDePara select[name="importarNoSap"] option[value="'+retorno.importarNoSap+'"]').prop("selected", true).trigger('change');
		$('#modalDePara input[name="codigoDoServico"]').val(retorno.codigoDoServico);
		$('#modalDePara input[name="descricao"]').val(retorno.descricao);
		$('#modalDePara input[name="contaDeDebito"]').val(retorno.contaDeDebito);
		$('#modalDePara input[name="contaDeCredito"]').val(retorno.contaDeCredito);
		if (retorno.codigoDoServico=='HOSPEDEEMCURSO'){
			$('#modalDePara input[name="codigoDoServico"]').attr('readonly','readonly');
			$('#modalDePara input[name="descricao"]').attr('readonly','readonly');
			$('#modalDePara select[name="importarNoSap"]').attr('readonly','readonly');
		}else{
			$('#modalDePara input[name="codigoDoServico"]').removeAttr('readonly');
			$('#modalDePara input[name="descricao"]').removeAttr('readonly','readonly');
			$('#modalDePara select[name="importarNoSap"]').removeAttr('readonly');
		}
	});

	removeMessage();

	$('#modalDePara .titulo-modal').html(l["Alterar"]);
	$('#modalDePara').modal('show');
}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		removeMessage();
		limpaCamposModal();
		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
	});

	$('#modalDePara .salvar').click(function(){
		toggleLoading();
		const action = $(this).data('action');

		if (hasErroPreenchimentoCampos(
			'#modalDePara',
			{
				inputsTexto: ['codigoDoServico', 'descricao'],
				inputsContas: ['contaDeDebito', 'contaDeCredito']
			},
			$('#modalDePara input[name="codigoDoServico"]').val()
		)) {
			toggleLoading();
		} else {
			$.post(action, {
				guid:$('#modalDePara input[name="idB1HV2DeParaRecebimentos"]').val(),
				idB1HV2ModeloRecebimentos:$('input[name="guid"]').val(),
				importarNoSap:$('#modalDePara select[name="importarNoSap"]').val(),
				codigoDoServico:$('#modalDePara input[name="codigoDoServico"]').val(),
				descricao:$('#modalDePara input[name="descricao"]').val(),
				contaDeDebito:$('#modalDePara input[name="contaDeDebito"]').val(),
				contaDeCredito:$('#modalDePara input[name="contaDeCredito"]').val(),
				...tokenCsrf
			}, function(retorno){
				if (retorno.class === 'success'){
					let tableDataTable = $(".table-exibe").DataTable();
					tableDataTable.draw();

					if (!$('#modalDePara input[name="idB1HV2DeParaRecebimentos"]').val()) {
						limpaCamposModal();
					}
				}

				addMessage(retorno, '.container-msg-modal');

				toggleLoading();
			});
		}
	});
}

function limpaCamposModal() {
	$('#modalDePara input[name="idB1HV2DeParaRecebimentos"]').val('');
	$('#modalDePara select[name="importarNoSap"] option').prop("selected", false).trigger('change');
	$('#modalDePara input[name="codigoDoServico"]').val('');
	$('#modalDePara input[name="descricao"]').val('');
	$('#modalDePara input[name="contaDeDebito"]').val('');
	$('#modalDePara input[name="contaDeCredito"]').val('');
	$('#modalDePara input[name="codigoDoServico"]').removeAttr('readonly');
	$('#modalDePara input[name="descricao"]').removeAttr('readonly');
	$('#modalDePara select[name="importarNoSap"]').removeAttr('readonly');
}

salvar();