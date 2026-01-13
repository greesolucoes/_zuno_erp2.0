async function ajaxEditarConversao(elemento){
	toggleLoading();
	const idConversao = $(elemento).data('id_conversao');

	await $.post(
		$(elemento).data('url'),
		{
			idConversao,
			...tokenCsrf
		},
		function(retorno){
			$('#modalCadastroConversaoMoedas input[name="idConversaoMoeda"]').val(idConversao);
			$('#modalCadastroConversaoMoedas input[name="moedaHotel"]').val(retorno.moedaHotel);
			$('#modalCadastroConversaoMoedas input[name="moedaSede"]').val(retorno.moedaSede);
			$('#modalCadastroConversaoMoedas input[name="taxaConversao"]').val(retorno.taxaConversao);
		}
	);

	$('#modalCadastroConversaoMoedas input[name="moedaHotel"]').attr('readonly', 'readonly');
	$('#modalCadastroConversaoMoedas input[name="moedaSede"]').attr('readonly', 'readonly');
	$('#modalCadastroConversaoMoedas').modal('show');
	toggleLoading();
}

/**
 * Função para formatar a Sigla da Moeda em maíusculo e apenas letras
 */
$(".fmtSiglaMoeda").on("keyup keydown focusout", function () {
	$(this).val(
		$(this).val()
			.toUpperCase()
			.substring(0,3)
	);
});

/**
 * Função para fechar a modal e fechar o aviso de sucesso/erro
 */
$("#btnModalCadastroConversaoMoedas").on("click", function () {
	$('#modalCadastroConversaoMoedas').modal('hide');
	$(".container-msg .close").trigger("click");
	$("#idConversaoMoeda").val("");
});

/**
 * Função para remover o readonly dos campos de conversão de moeda
 */
$("#btnOpenModal").on("click", function () {
	$('#modalCadastroConversaoMoedas input[name="moedaHotel"]').removeAttr('readonly');
	$('#modalCadastroConversaoMoedas input[name="moedaSede"]').removeAttr('readonly');
	$('#formConversaoMoedas')[0].reset();
	$("#idConversaoMoeda").val("");
});

function toggleStatusConversaoMoedas() {
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

function validarPreenchimentoConversaoMoeda () {
	let moedaHotel = $('#modalCadastroConversaoMoedas input[name="moedaHotel"]').val();
	let moedaSede = $('#modalCadastroConversaoMoedas input[name="moedaSede"]').val();
	let taxaConversao = $('#modalCadastroConversaoMoedas input[name="taxaConversao"]').val();

	// valida os campos vazios
	if ((moedaHotel == '') || (moedaSede == '') || (taxaConversao == '')){
		swal({
			 title: l["atenção!"],
			 text: l['preenchaTodosOsCamposParaContinuar'],
			 type: "warning",
			 showCancelButton: false,
			 confirmButtonColor: '#3085d6'
		 });

		return false;
	}

	// começa com letras ou numeros, tem letras, números e . no meio e deve terminar com números ou letras
	if ((/[A-Z]{3}/.test(moedaHotel) === false) || (/[A-Z]{3}/.test(moedaSede) === false)) {
		swal({
			title: l["atenção!"],
			text: l['oCampoMoedaDeveConterApenasLetras'],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		});

		return false;
	}

	return true;
}

$('#btnSalvarConversaoMoeda').off('click').on('click', async function() {
	if (validarPreenchimentoConversaoMoeda()) {
		toggleLoading();
		const data = new FormData($("#formConversaoMoedas")[0]);
		let canEdit = 0;

		Object.entries(tokenCsrf).forEach(([key, value]) => {
			data.append(key, value);
		});
		await $.ajax({
			url: $(this).data('action'),
			type: 'POST',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(retorno) {
				addJsonMessage(retorno, '.container-msg');
				canEdit = retorno.edit;

				if (canEdit === 0) {
					$('#formConversaoMoedas')[0].reset();
					$("#idConversaoMoeda").val("");
				}
				$(".table-exibe").DataTable().draw();
			}
		}).then(function (completeData) {
			if(canEdit === 0) {
				$('#formConversaoMoedas')[0].reset();
				$("#idConversaoMoeda").val("");
			}
			$(".table-exibe").DataTable().draw();
			toggleLoading();
		}).catch(swal.noop);
	}
});


$('#tableConversao').on('click', '.buttonAcaoToggleConversao', async function (e) {
	e.preventDefault();
	let url = ($(this).data("url") + $(this).data('id_conversao'));

	swal({
		title: l["removerConversaoMoeda"],
		text: l["confirmarRemocaoConversaoMoeda"],
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: l["continuar!"],
		cancelButtonText: l["fechar!"]
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

				if (ret["class"] != "error") {
					$(".table-exibe").DataTable().draw();
				}

				toggleLoading();
			}
		);
	}).catch(swal.noop);
})
