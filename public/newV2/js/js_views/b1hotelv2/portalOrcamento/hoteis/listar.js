async function ajaxEditarHotel(elemento){
	toggleLoading();
	const idHotel = $(elemento).data('id_hotel');

	await $.post(
		$(elemento).data('url'),
		{
			idHotel,
			...tokenCsrf
		},
		function(retorno){
			$('#modalCadastroHotel input[name="idB1HV2PortalBudgetHoteis"]').val(idHotel);
			$('#modalCadastroHotel input[name="megacode"]').val(retorno.megacode);
			$('#modalCadastroHotel input[name="nome"]').val(retorno.nome);

			$('#modalCadastroHotel select[name="tipo_contrato"]').select2Reset();
			$('#modalCadastroHotel select[name="tipo_contrato"]').data('init', JSON.parse('{"id":"'+ retorno.tipoContrato +'","text":"'+ retorno.tipoContrato +'"}'));
			$('#modalCadastroHotel select[name="tipo_contrato"]').select2Ajax();

			$('#modalCadastroHotel select[name="marca"]').select2Reset();
			$('#modalCadastroHotel select[name="marca"]').data('init', JSON.parse('{"id":"'+ retorno.marca +'","text":"'+ retorno.marca +'"}'));
			$('#modalCadastroHotel select[name="marca"]').select2Ajax();
		}
	);

	$('#modalCadastroHotel').modal('show');
	toggleLoading();
}

function salvar(){
	$('#modalCadastroHotel').on('hidden.bs.modal', function () {
		$('#div-alert>a.close').first().trigger('click');
		$('#modalCadastroHotel input[name="idB1HV2PortalBudgetHoteis"]').val('');
		$('#modalCadastroHotel input[name="megacode"]').val('');
		$('#modalCadastroHotel input[name="nome"]').val('');

		$('#modalCadastroHotel select[name="tipo_contrato"]').val("");
		$('#modalCadastroHotel select[name="tipo_contrato"]').trigger('change');

		$('#modalCadastroHotel select[name="marca"]').val("");
		$('#modalCadastroHotel select[name="marca"]').trigger('change');
	});

	$('#modalCadastroHotel .salvar').click(async function() {
		if (!validarPreenchimentoCampos()) {
			toggleLoading();

			await $.post(
				$(this).data('action'),
				{
					idHotel: $('#modalCadastroHotel input[name="idB1HV2PortalBudgetHoteis"]').val(),
					megacode: $('#modalCadastroHotel input[name="megacode"]').val(),
					nome: $('#modalCadastroHotel input[name="nome"]').val(),
					tipoContrato: $('#modalCadastroHotel select[name="tipo_contrato"]').val(),
					marca: $('#modalCadastroHotel select[name="marca"]').val(),
					...tokenCsrf
				},
				function (retorno) {
					$(".table-exibe").DataTable().draw();

					addJsonMessage(retorno);

					if (retorno.edit == 0) {
						$('#modalCadastroHotel input[name="idB1HV2PortalBudgetHoteis"]').val('');
						$('#modalCadastroHotel input[name="megacode"]').val('');
						$('#modalCadastroHotel input[name="nome"]').val('');

						$('#modalCadastroHotel select[name="tipo_contrato"]').val("");
						$('#modalCadastroHotel select[name="tipo_contrato"]').trigger('change');

						$('#modalCadastroHotel select[name="marca"]').val("");
						$('#modalCadastroHotel select[name="marca"]').trigger('change');
					}
					toggleLoading();
					$('.nomeArquivo').trigger('change');

				});
		}
	});
}

function validarPreenchimentoCampos() {
	// começa com letras ou numeros, tem letras, números e . no meio e deve terminar com números ou letras
	if (/^[A-Za-z0-9]+[A-Za-z0-9.]*[a-zA-Z0-9]*$/.test($('#modalCadastroHotel input[name="megacode"]').val()) === false) {
		swal({
			title: l["atenção!"],
			text: l['oCampoMegacodeDeveConterApenasNumeroLetraEPonto'],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		})

		return true;
	}

	// começa com letras ou numeros, tem letras, números, ., espaço e traço no meio e deve terminar com números ou letras
	if ((/^[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ]+[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ. -]*[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ]*$/.test($('#modalCadastroHotel input[name="nome"]').val()) === false)) {
		swal({
			title: l["atenção!"],
			text: l['oCampoNomeDeveConterApenasNumeroLetraPontoEspacoETraco'],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		})

		return true;
	}

	return false
}

function toggleStatusHotel() {
	$('.buttonAcaoToggleHotel')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);

			swal({
				title: $(obj).prop('title'),
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
					$(obj).data("url") + $(obj).data("id_hotel"),
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

						$(".table-exibe").DataTable().draw();

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}

function criarSelects(){
	$(".select_tipo_contrato").select2Ajax();
	$(".select_tipo_contrato").data("init", "");
	$(".select_marca").select2Ajax();
	$(".select_marca").data("init", "");
}

salvar();
criarSelects();
