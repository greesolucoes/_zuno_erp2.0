function acaoDeletar() {
	$('.deletar')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url");
			let tableDataTable = $(".table-exibe").DataTable();

			swal({
				title: l["deletarRegistro"],
				text: l["desejaContinuar?"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();
				ajaxRequest(
					true,
					url,
					null,
					'text',
					null,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if(!is_empty(ret["bol"], 1)) {
							tableDataTable.draw();
						}

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}

function botaoModelo(){
	$('.updateModelo').off('click').on('click', function(){
		if ($(this).hasClass('fa-pencil')){
			$('#nomeModelo').removeAttr('readonly');
			$(this).removeClass('btn-primary').removeClass('fa-pencil').addClass('btn-success').addClass('fa-check');
		}else{
			$.post($('.updateModelo').data('action'), {
				nomeModelo:$('#nomeModelo').val(),
				guid:$('#guid').val(),
				...tokenCsrf
			}, function(retorno){
				$('#nomeModelo').attr('readonly','readonly');
				addMessage(retorno, '.container-msg-modal')
			});
			$(this).removeClass('btn-success').removeClass('fa-check').addClass('btn-primary').addClass('fa-pencil');
		}
	});
}
acaoDeletarCommom("idb1hv2modelodeparalcm");

async function botaoItem(elemento){
	toggleLoading();

	let tableDataTable = $(".table-exibe").DataTable();
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	await $.post(url, {guid:id, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2DeParaReceitasPms"]').val(id);
		$('#modalDePara select[name="importarNoSap"] option[value="'+retorno.importarNoSap+'"]').prop("selected", true).trigger('change');
		$('#modalDePara input[name="codigoDoServico"]').val(retorno.codigoDoServico);
		$('#modalDePara input[name="descricao"]').val(retorno.descricao);
		$('#modalDePara input[name="contaDeDebito"]').val(retorno.contaDeDebito);
		$('#modalDePara input[name="contaDeCredito"]').val(retorno.contaDeCredito);
		$('#modalDePara input[name="contaRedutoraIss"]').val(retorno.contaRedutoraIss);
		$('#modalDePara input[name="contaRedutoraCofins"]').val(retorno.contaRedutoraCofins);
		$('#modalDePara input[name="contaRedutoraPis"]').val(retorno.contaRedutoraPis);
		$('#modalDePara select[name="tipoContaPassivo"] option[value="'+retorno.tipoContaPassivo+'"]').prop("selected", true).trigger('change');


		if (retorno.codigoDoServico=='HOSPEDEEMCURSO'){
			$('#modalDePara input[name="codigoDoServico"]').attr('readonly','readonly');
			$('#modalDePara select[name="importarNoSap"]').attr('disabled', true);
			$('#modalDePara input[name="naoDiferenciarSegmentacao"]').attr('readonly','readonly');
			$('#modalDePara input[name="segmentacao"]').attr('readonly','readonly');
			$('#modalDePara input[name="descricao"]').attr('readonly','readonly');
			$('#modalDePara input[name="contaRedutoraIss"]').attr('readonly','readonly');
			$('#modalDePara input[name="contaRedutoraCofins"]').attr('readonly','readonly');
			$('#modalDePara input[name="contaRedutoraPis"]').attr('readonly','readonly');
			$('#modalDePara select[name="tipoContaPassivo"]').attr('disabled', true);
		}else{
			$('#modalDePara input[name="codigoDoServico"]').removeAttr('readonly');
			$('#modalDePara select[name="importarNoSap"]').removeAttr('disabled');
			$('#modalDePara input[name="naoDiferenciarSegmentacao"]').removeAttr('readonly');
			$('#modalDePara input[name="segmentacao"]').removeAttr('readonly');
			$('#modalDePara input[name="descricao"]').removeAttr('readonly');
			$('#modalDePara input[name="contaRedutoraIss"]').removeAttr('readonly');
			$('#modalDePara input[name="contaRedutoraCofins"]').removeAttr('readonly');
			$('#modalDePara input[name="contaRedutoraPis"]').removeAttr('readonly');
			$('#modalDePara select[name="tipoContaPassivo"]').removeAttr('disabled');
		}
		if (retorno.naoDiferenciarSegmentacao=="1") $('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', true);
		$('#modalDePara input[name="segmentacao"]').val(retorno.segmentacao);
	});

	$('#modalDePara').modal('show');
	toggleLoading();
}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		removeMessage();
		limpaCamposModal();
		$('#modalDePara .titulo-modal').html(l['Alterar']);
		$('#modalDePara .titulo-modal').attr('is-add', 0);
	});

	$('.abrir-modal-de-para').on('click', function () {
		removeMessage();
		limpaCamposModal();
		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
		$('#modalDePara .titulo-modal').attr('is-add', 1);
		$('#modalDePara').modal('show');
	});

	$('#modalDePara .salvar').click(async function(){
		toggleLoading();

		const action = $(this).data('action');
		let segmentacao = ``;
		let is_add = $('#modalDePara .titulo-modal').attr('is-add');
		let naoDiferenciarSegmentacao = $('#modalDePara input[name="naoDiferenciarSegmentacao"]').is(":checked");
		if (naoDiferenciarSegmentacao == false){
			naoDiferenciarSegmentacao = 0;
			segmentacao = $('#modalDePara input[name="segmentacao"]').val();
		}else{
			naoDiferenciarSegmentacao = 1;
		}

		if (hasErroPreenchimentoCampos(
			'#modalDePara',
			{
				inputsTexto: ['codigoDoServico', 'descricao'],
				inputsContas: [
					'contaDeDebito',
					'contaDeCredito',
					'contaRedutoraIss',
					'contaRedutoraCofins',
					'contaRedutoraPis'
				]
			},
			$('#modalDePara input[name="codigoDoServico"]').val()
		)) {
			toggleLoading();
		} else {
			await $.post(action, {
				guid:$('#modalDePara input[name="idB1HV2DeParaReceitasPms"]').val(),
				idb1hv2modelodeparalcm:$('input[name="guid"]').val(),
				importarNoSap:$('#modalDePara select[name="importarNoSap"]').val(),
				codigoDoServico:$('#modalDePara input[name="codigoDoServico"]').val(),
				descricao:$('#modalDePara input[name="descricao"]').val(),
				contaDeDebito:$('#modalDePara input[name="contaDeDebito"]').val(),
				contaDeCredito:$('#modalDePara input[name="contaDeCredito"]').val(),
				naoDiferenciarSegmentacao,
				segmentacao,
				contaRedutoraIss:$('#modalDePara input[name="contaRedutoraIss"]').val(),
				contaRedutoraCofins:$('#modalDePara input[name="contaRedutoraCofins"]').val(),
				contaRedutoraPis:$('#modalDePara input[name="contaRedutoraPis"]').val(),
				tipoContaPassivo:$('#modalDePara select[name="tipoContaPassivo"]').val(),
				isAdd: is_add,
				...tokenCsrf
			}, function(retorno){
				if (retorno.class == 'success'){
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
	$('#modalDePara input[name="idB1HV2DeParaReceitasPms"]').val('');
	$('#modalDePara select[name="importarNoSap"] option').prop("selected", false).trigger('change');
	$('#modalDePara input[name="codigoDoServico"]').val('');
	$('#modalDePara input[name="contaDeDebito"]').val('');
	$('#modalDePara input[name="contaDeCredito"]').val('');
	$('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', false);
	$('#modalDePara input[name="segmentacao"]').val('');
	$('#modalDePara input[name="descricao"]').val('');
	$('#modalDePara input[name="contaRedutoraIss"]').val('');
	$('#modalDePara input[name="contaRedutoraCofins"]').val('');
	$('#modalDePara input[name="contaRedutoraPis"]').val('');
	$('#modalDePara select[name="tipoContaPassivo"] option').prop("selected", false).trigger('change');

	$('#modalDePara input[name="codigoDoServico"]').removeAttr('readonly');
	$('#modalDePara select[name="importarNoSap"]').removeAttr('readonly');
	$('#modalDePara input[name="naoDiferenciarSegmentacao"]').removeAttr('readonly');
	$('#modalDePara input[name="segmentacao"]').removeAttr('readonly');
	$('#modalDePara input[name="descricao"]').removeAttr('readonly');
	$('#modalDePara input[name="contaRedutoraIss"]').removeAttr('readonly');
	$('#modalDePara input[name="contaRedutoraCofins"]').removeAttr('readonly');
	$('#modalDePara input[name="contaRedutoraPis"]').removeAttr('readonly');
	$('#modalDePara select[name="tipoContaPassivo"]').removeAttr('disabled');
}

acaoDeletar();
botaoModelo();
salvar();