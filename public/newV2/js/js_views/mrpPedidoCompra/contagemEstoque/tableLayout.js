$('#import-invent').off('click').on('click', function () {
	const idLayoutCompraCabecalho = $(this).data('id-layout-compra');
	const importadoInventario = $(this).data('importado');
	const urlImportadoInventario = $(this).data('url');

	swal({
		title: l["desejaContinuar?"],
		text: "",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: l["sim!"],
		cancelButtonText: l["cancelar!"]
	}).then((result) => {
		if (result) {
			$.ajax({
				type: "POST",
				url: urlImportadoInventario,
				data: {
					idLayoutContagem: idLayoutCompraCabecalho,
					...tokenCsrf
				},
				beforeSend: function () {
					if (importadoInventario == 1 || importadoInventario.length == 0) {
						swal({
							icon: 'error',
							type: "error",
							title: l["erro!"],
							text: l["atencaoErroImportarContagemMrpInventario"],
						});
						return false; // Impede o envio da requisição AJAX
					}
				},
				success: function (response) {
					if (response.bol) {
						window.location.href = response.url;
					} else {
						swal({
							icon: 'error',
							type: "error",
							title: l["erro!"],
							text: l["erroAoProcessarDado"],
						});
					}
				},
				error: function (error) {
					swal({
						icon: 'error',
						type: "error",
						title: l["erro!"],
						text: l["erroAoProcessarDado"],
					});
				}
			});
		}
	});
});

function controlarAcoes() {
	let __initSalvarRequisicao = function () {
		let __acaoSalvarRequisicao = function () {
			toggleLoading();
		};

		$("button#requisicao-salvar").off('click');
		$("button#requisicao-salvar").on("click", function (){
			__acaoSalvarRequisicao();
		});
	}
	__initSalvarRequisicao();
}
controlarAcoes();

function allFunctions() {
	$("input[data-mask='numerov2']").fnMascaraNumeroV2();
}
allFunctions();