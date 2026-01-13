function criarSelects () {
	$("select.select").select2Ajax();
	$("select.select").data("init", "");
}

criarSelects();

/**
 * Função para fechar a modal e fechar o aviso de sucesso/erro
 */
$("#btnModalDeParaAlteracoesSplit").on("click", function () {
	$('#modalDeParaAlteracoesSplit').modal('hide');
	$(".container-msg .close").trigger("click");
	$("select.select").val("").trigger("change");
	$("#idAlteracaoSplit").val("");
});

$("#btnOpenModal").on("click", function () {
	$("select.select").val("").trigger("change");
	$("#idAlteracaoSplit").val("");
});

function validarPreenchimento () {
	let tipoContrado = $('.select_tipo_contrato').val();
	let marca 		 = $('.select_marca').val();
	let contaGrupo   = $('.select_conta_grupo').val();
	let contaSede    = $('.select_conta_sede').val();
	let ccSede 		 = $('.select_centro_custo_sede').val();

	// valida os campos vazios
	if ((tipoContrado === null) || (marca === null) || (contaGrupo === null) || (contaSede === null) || (ccSede === null)){
		swal({
			 title: l["atenção!"],
			 text: l['preenchaTodosOsCamposParaContinuar'],
			 type: "warning",
			 showCancelButton: false,
			 confirmButtonColor: '#3085d6'
		 });

		return false;
	}

	return true;
}

$('#btnSalvarDeParaDespesReceita').off('click').on('click', async function() {
	if (validarPreenchimento()) {
		toggleLoading();
		const data = new FormData($("#formDeParaAlteracoesSplit")[0]);
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

				// Limpo todos os campos do add
				if (canEdit === 0) {
					$("select.select").val("").trigger("change");
					$("#idAlteracaoSplit").val("");
				}

				$(".table-exibe").DataTable().draw();
			}
		}).then(function (completeData) {
			if(canEdit === 0) {
				$("select.select").val("").trigger("change");
				$("#idAlteracaoSplit").val("");
			}

			$(".table-exibe").DataTable().draw();
			toggleLoading();
		}).catch(swal.noop);
	}
});

/**
 * Função para excluir um de/para
 */
$('#tableDePara').on('click', '.buttonAcaoToggleDeParaSplit', async function (e) {
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
			$(obj).data("url") + $(obj).data("iddeparasplit"),
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

/**
 * Função para editar um de/para
 */

async function ajaxEditarDePara(elemento){
	toggleLoading();
	const idDeParaAlteracaoSplit = $(elemento).data('iddeparasplit');

	$.post(
		$(elemento).data('url'),
		{
			idDeParaAlteracaoSplit,
			...tokenCsrf
		},
		function(retorno){
			$('#idAlteracaoSplit').val(retorno.idAlteracaoSplit);
			$('.select_tipo_contrato').data("init", {id:retorno.tipoContrato, text:retorno.tipoContrato});
			$('.select_tipo_contrato').select2Ajax();
			$('.select_marca').data("init", {id:retorno.marca, text:retorno.marca});
			$('.select_marca').select2Ajax();
			$('.select_conta_grupo').data("init", {id:retorno.idHotelContasContabeis, text:retorno.contaHotel});
			$('.select_conta_grupo').select2Ajax();
			$('.select_conta_sede').data("init", {id:retorno.idContasSede, text:retorno.contaSede});
			$('.select_conta_sede').select2Ajax();
			$('.select_centro_custo_sede').data("init", {id:retorno.idregra, text:retorno.nomeregra});
			$('.select_centro_custo_sede').select2Ajax();

			$('#modalDeParaAlteracoesSplit').modal('show');
			toggleLoading();
		}
	);
}
