function acoesBotoes() {
	let __acaoPadrao = function (obj, objEnvio) {
		let id = $(obj).data('id');
		let url = $(obj).data('url');
		let tableDataTable = $(obj).parents('.table-exibe').DataTable();
		if(is_empty(id, 1) || is_empty(url, 1)) return;
		if(is_empty(objEnvio, 1)) {
			objEnvio = {};
		}
		objEnvio['id'] = id;

		swal({
			title: l["desejaContinuar?"],
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["continuar!"],
			cancelButtonText: l["fechar!"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', objEnvio, function (ret) {
				try{
					ret = JSON.parse(ret);

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);

					tableDataTable.draw();
					toggleLoading();
				}catch(err){
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			});
		}).catch(swal.noop);
	}

	let __acaoWithDescription = function (obj, objEnvio) {
		$('.modal_reject #label_reject').html($(obj).attr("title"));
		$('.modal_reject .descricao_reject textarea#motivo_text').val('');
		$(".modal_reject button.reject").attr("title", $(obj).attr("title"));

		$('.modal_reject').modal('toggle');
		$('.modal_reject button.reject').off('click');
		$('.modal_reject button.reject').on("click", function (e) {
			let motivo         = $(this).parents('.descricao_reject').find('textarea#motivo_text').val().trim();
			let url            = $(obj).data('url');
			let id             = $(obj).data('id');
			let tableDataTable = $(obj).parents('.table-exibe').DataTable();
			if(is_empty(motivo, 1)) motivo = "";
			if(is_empty(url, 1) || is_empty(id, 1)) return;
			if(is_empty(objEnvio, 1)) {
				objEnvio = {};
			}
			objEnvio['id'] = id;
			objEnvio['motivo'] = motivo;

			swal({
				title: l["desejaContinuar?"],
				text: "",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["fechar!"]
			}).then(function () {
				toggleLoading();
				ajaxRequest(true, url, null, 'text', objEnvio, function (ret) {
					try{
						ret = JSON.parse(ret);

						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);

						tableDataTable.draw();
						$('.modal_reject').modal('toggle');
						toggleLoading();
					}catch(err){
						swal(
							l["erro!"],
							l["tempoDeRespostaDoServidorEsgotado!"],
							"error"
						).catch(swal.noop);
						forceToggleLoading(0);
					}
				});
			}).catch(swal.noop);
		});
	}

	$(".aprovar").off('click');
	$(".aprovar").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".upload").off('click');
	$(".upload").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".desativar").off('click');
	$(".desativar").on('click', function () {
		__acaoPadrao($(this));
	});

	$(".show_modal_reject").off('click');
	$(".show_modal_reject").on('click', function () {
		__acaoWithDescription($(this));
	});

	$('.show_modal_motivo').off('click');
	$('.show_modal_motivo').on('click', function (e) {
		$('.modal_motivo #label_motivo').html($(this).attr("title"));
		$('.modal_motivo .descricao_motivo').html($(this).parents('td').find('.descricao_rejeicao').html());
		$('.modal_motivo').modal('toggle');
	});
}

function criaCostumizacoes() {
	$("select#pesquisa-status").select2Simple();
}

criaCostumizacoes();
pesquisaPersonalizada();
acoesBotoes();
