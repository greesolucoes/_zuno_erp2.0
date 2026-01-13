acaoDeletarCommom();

function botaoItem(elemento){
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	$.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1Hv2ModeloCentroDeCustoDePara"]').val(id);
		$('#modalDePara input[name="codigoServico"]').val(retorno.codigoServico);
		if (retorno.naoDiferenciarSegmentacao=="1") $('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', true);
		$('#modalDePara input[name="segmentacao"]').val(retorno.segmentacao);
		$('#modalDePara select[name="idregra"] option[value="'+retorno.idregra+'"]').prop("selected", true).trigger('change');
	});
	$('#modalDePara').modal('show');

}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		$('#modalDePara input[name="idB1Hv2ModeloCentroDeCustoDePara"]').val('');
		$('#modalDePara input[name="codigoServico"]').val('');
		$('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', false);
		$('#modalDePara input[name="segmentacao"]').val('');
		$('#modalDePara select[name="idregra"] option').prop("selected", false).trigger('change');
		$('.salvar').attr('is-add', 0);
		$('#modalDePara .titulo-modal').html(l['Alterar']);
	});

	$('.abrir-modal-de-para').on('click', function () {
		$('#modalDePara input[name="idB1Hv2ModeloCentroDeCustoDePara"]').val('');
		$('#modalDePara input[name="codigoServico"]').val('');
		$('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', false);
		$('#modalDePara input[name="segmentacao"]').val('');
		$('#modalDePara select[name="idregra"] option').prop("selected", false).trigger('change');
		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
		$('.salvar').attr('is-add', 1);
		$('#modalDePara').modal('show');
	});

	$('#modalDePara .salvar').click(function(){
		const action = $(this).data('action');
		toggleLoading();
		let segmentacao = ``;
		let is_add = $('.salvar').attr('is-add');
		let naoDiferenciarSegmentacao = $('#modalDePara input[name="naoDiferenciarSegmentacao"]').is(":checked");
		if (naoDiferenciarSegmentacao==false){
			naoDiferenciarSegmentacao = 0;
			segmentacao = $('#modalDePara input[name="segmentacao"]').val();
		}else{
			naoDiferenciarSegmentacao = 1;
		}

		$.post(action, {
			guid:$('#modalDePara input[name="idB1Hv2ModeloCentroDeCustoDePara"]').val(),
			idB1HV2ModeloCentroDeCusto:$('input[name="guid"]').val(),
			codigoServico:$('#modalDePara input[name="codigoServico"]').val(),
			naoDiferenciarSegmentacao:naoDiferenciarSegmentacao,
			segmentacao:segmentacao,
			isAdd: is_add,
			idregra:$('#modalDePara select[name="idregra"]').val(),
			...tokenCsrf
		},
		function(retorno){
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