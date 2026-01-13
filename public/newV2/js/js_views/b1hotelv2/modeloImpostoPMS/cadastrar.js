acaoDeletarCommom("idb1hv2modeloimpostopms");

function botaoItem(elemento){
	let tableDataTable = $(".table-exibe").DataTable();
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	$.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2DeParaImpostoPms"]').val(id);
		$('#modalDePara input[name="codigoDoServico"]').val(retorno.codigoDoServico);
		$('#modalDePara select[name="imposto"] option[value="'+retorno.imposto+'"]').prop("selected", true).trigger('change');
		$('#modalDePara input[name="aliquotaIss"]').val(retorno.aliquotaIss);
		$('#modalDePara input[name="aliquotaCofins"]').val(retorno.aliquotaCofins);
		$('#modalDePara input[name="aliquotaPis"]').val(retorno.aliquotaPis);

	});
	$('#modalDePara').modal('show');
}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		$('#modalDePara input[name="idB1HV2DeParaImpostoPms"]').val('');
		$('#modalDePara input[name="codigoDoServico"]').val('');
		$('#modalDePara select[name="imposto"] option').prop("selected", false).trigger('change');
		$('#modalDePara input[name="aliquotaIss"]').val('');
		$('#modalDePara input[name="aliquotaCofins"]').val('');
		$('#modalDePara input[name="aliquotaPis"]').val('');
		$('.salvar').attr('is-add', 0);
		$('#modalDePara .titulo-modal').html(l['Alterar']);
	});

	$('.abrir-modal-de-para').on('click', function () {
		$('#modalDePara input[name="idB1HV2DeParaImpostoPms"]').val('');
		$('#modalDePara input[name="codigoDoServico"]').val('');
		$('#modalDePara select[name="imposto"] option').prop("selected", false).trigger('change');
		$('#modalDePara input[name="aliquotaIss"]').val('');
		$('#modalDePara input[name="aliquotaCofins"]').val('');
		$('#modalDePara input[name="aliquotaPis"]').val('');
		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
		$('.salvar').attr('is-add', 1);
		$('#modalDePara').modal('show');
	});

	$('#modalDePara .salvar').click(function(){
		const action = $(this).data('action');
		let is_add = $('.salvar').attr('is-add');
		toggleLoading();
		$.post(action, {
			guid:$('#modalDePara input[name="idB1HV2DeParaImpostoPms"]').val(),
			idb1hv2modeloimpostopms:$('input[name="guid"]').val(),
			codigoDoServico:$('#modalDePara input[name="codigoDoServico"]').val(),
			imposto:$('#modalDePara select[name="imposto"]').val(),
			aliquotaIss:$('#modalDePara input[name="aliquotaIss"]').val(),
			aliquotaCofins:$('#modalDePara input[name="aliquotaCofins"]').val(),
			isAdd: is_add,
			aliquotaPis:$('#modalDePara input[name="aliquotaPis"]').val(),
			...tokenCsrf
		}, function(retorno){
			if (retorno.class=='success'){
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