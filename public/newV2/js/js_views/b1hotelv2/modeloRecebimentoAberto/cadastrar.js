acaoDeletarCommom("idb1hv2modelorecebimentodetalhado");

function criarSelects() {
	$('select.select_tipo').select2Simple(l['selecione'],'',{'allowClear' : true, 'dropdownParent': "#modalDePara"});
	$('select.select_tipo').data('init', '');

	$('select.select_is_agrupado').select2Simple(l['selecione'],'',{'allowClear' : false, 'dropdownParent': "#modalDePara"});
	$('select.select_is_agrupado').data('init', '');
}

function criarSelectCliente() {
	$('select.select_cliente').select2Simple(l['selecione'],'',{'allowClear' : true, 'dropdownParent': "#modalDePara"});
	$('select.select_cliente').data('init', '');
}
function removerSelectCliente() {
	$('select.select_cliente').select2Reset();
}

function botaoItem(elemento){

	const id = $(elemento).data('id');
	const url = $(elemento).data('url');
	toggleLoading();

	$.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2DeParaRecebimentoCliente"]').val(id);
		$('#modalDePara input[name="codigo"]').val(retorno.codigo);
		$('#modalDePara select[name="tipo"] option[value="'+retorno.tipo+'"]').prop("selected", true).trigger('change');
		$('#modalDePara select[name="idClientes"] option[value="'+retorno.idClientes+'"]').prop("selected", true).trigger('change');
		$('#modalDePara input[name="conta"]').val(retorno.conta);
		$('#modalDePara input[name="contaTransitoria"]').val(retorno.contaTransitoria);
		$('#modalDePara input[name="descricao"]').val(retorno.descricao);
		$('#modalDePara select[name="isAgrupado"] option[value="'+retorno.isAgrupado+'"]').prop("selected", true).trigger('change');

		if(!is_empty(retorno.tipo, true), retorno.tipo == 'cli') {
			criarSelectCliente();
		}
	}).done(function (data) {
		toggleLoading();
	});
	$('#modalDePara').modal('show');
}

function salvar(){
	$('#modalDePara').on('show.bs.modal', function () {
		criarSelects();
	});
	$('#modalDePara').on('hidden.bs.modal', function () {
		$('#modalDePara input[name="idB1HV2DeParaRecebimentoCliente"]').val('');
		$('#modalDePara input[name="codigo"]').val('');
		$('#modalDePara select[name="tipo"] option').prop("selected", false).trigger('change');
		$('#modalDePara select[name="idClientes"] option').prop("selected", false).trigger('change');
		$('#modalDePara input[name="conta"]').val('');
		$('#modalDePara input[name="contaTransitoria"]').val('');
		$('#modalDePara input[name="descricao"]').val('');
		$('#modalDePara select[name="isAgrupado"] option').prop("selected", false).trigger('change');
		$('.salvar').attr('is-add', 0);
		$('#modalDePara .titulo-modal').html(l['Alterar']);
	});

	$('.abrir-modal-de-para').on('click', function () {
		$('#modalDePara input[name="idB1HV2DeParaRecebimentoCliente"]').val('');
		$('#modalDePara input[name="codigo"]').val('');
		$('#modalDePara select[name="tipo"] option').prop("selected", false).trigger('change');
		$('#modalDePara select[name="idClientes"] option').prop("selected", false).trigger('change');
		$('#modalDePara input[name="conta"]').val('');
		$('#modalDePara input[name="contaTransitoria"]').val('');
		$('#modalDePara input[name="descricao"]').val('');
		$('#modalDePara select[name="isAgrupado"] option').prop("selected", false).trigger('change');
		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
		$('.salvar').attr('is-add', 1);
		$('#modalDePara').modal('show');
	});

	$('#modalDePara .salvar').click(function(){
		const action = $(this).data('action');
		toggleLoading();
		let idClientes = ``;
		let conta = ``;
		let is_add = $('.salvar').attr('is-add');
		let tipo = $('#modalDePara select[name="tipo"]').val();
		if (tipo=='cli'){
			idClientes = $('#modalDePara select[name="idClientes"]').val();
		}else{
			conta = $('#modalDePara input[name="conta"]').val();
		}
		let descricao = $('#modalDePara input[name="descricao"]').val();
		let isAgrupado = $('#modalDePara select[name="isAgrupado"]').val();

		$.post(action, {
			guid:$('#modalDePara input[name="idB1HV2DeParaRecebimentoCliente"]').val(),
			idb1hv2modelorecebimentodetalhado:$('input[name="guid"]').val(),
			codigo:$('#modalDePara input[name="codigo"]').val(),
			tipo:tipo,
			idClientes:idClientes,
			conta:conta,
			descricao:descricao,
			isAgrupado:isAgrupado,
			isAdd: is_add,
			contaTransitoria:$('#modalDePara input[name="contaTransitoria"]').val(),
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

function trocarCampo(){
	if($(`select[name="tipo"]`).val()=='cli'){
		$('.linhaCliente').removeClass('d-none');
		$('.linhaConta').addClass('d-none');
		criarSelectCliente();
	}else{
		$('.linhaCliente').addClass('d-none');
		$('.linhaConta').removeClass('d-none');
		removerSelectCliente();
	}
}
