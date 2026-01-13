function botaoItem(elemento){
	let tableDataTable = $(".table-exibe").DataTable();
	const guidModelo = $('input[name="guid"]').val();
	const idFilial = $(elemento).data('id_filial');
	const id = $(elemento).data('id');
	const url = $(elemento).data('url');

	$.post(url, {id:id, guid:guidModelo, idFilial:idFilial, ...tokenCsrf}, function(retorno){
		$('#modalDePara input[name="idB1HV2DeParaNFServico"]').val(id); // id do de/para
		$('#modalDePara input[name="idFiliais"]').val(idFilial); // idFiliais do de/para
		$('#modalDePara input[name="naoIntegrarItemNotaFiscal"]').prop('checked', !!Number(retorno.naoIntegrarItemNotaFiscal));
		$('#modalDePara input[name="servico"]').val(retorno.servico);
		$('#modalDePara input[name="contaCredito"]').val(retorno.contaCredito);
		$('#modalDePara input[name="contaDebito"]').val(retorno.contaDebito);
		$('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', !!Number(retorno.naoDiferenciarSegmentacao));
		$('#modalDePara input[name="considerarNaComissao"]').prop('checked', !!Number(retorno.considerarNaComissao));
		$('#modalDePara input[name="segmentacao"]').val(retorno.segmentacao);
		$('#modalDePara select[name="itemSAP"] option[value="'+retorno.idProdutosServico+'"]').prop("selected", true).trigger('change');

		for (let i = 1; i <= 5; i++) {
			let dimensao = retorno['idDimensao' + i];
			if (dimensao !== null) {
				$('#modalDePara select[name="dimensao' + i + '"] option[value="' + dimensao + '"]').prop("selected", true).trigger('change');
			}
		}
	});
	$('#modalDePara').modal('show');
}

function salvar(){
	$('#modalDePara').on('hidden.bs.modal', function () {
		$('#modalDePara input[name="idB1HV2DeParaNFServico"]').val(''); // id do de/para
		$('#modalDePara input[name="idFiliais"]').val(''); // idFiliais do de/para
		$('#modalDePara input[name="naoIntegrarItemNotaFiscal"]').prop('checked', false);
		$('#modalDePara input[name="servico"]').val('');
		$('#modalDePara input[name="contaCredito"]').val('');
		$('#modalDePara input[name="contaDebito"]').val('');
		$('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', false);
		$('#modalDePara input[name="considerarNaComissao"]').prop('checked', false);
		$('#modalDePara input[name="segmentacao"]').val('');
		$('#modalDePara select[name="itemSAP"] option[value=""]').prop("selected", true).trigger('change');

		for (let i = 1; i <= 5; i++) {
			$('#modalDePara select[name="dimensao' + i + '"] option[value=""]').prop("selected", true).trigger('change');
		}

		$('.salvar').attr('is-add', 0);
		$('#modalDePara .titulo-modal').html(l['Alterar']);
	});

	$('.abrir-modal-de-para').on('click', function () {

		$('#modalDePara input[name="idB1HV2DeParaNFServico"]').val(''); // id do de/para
		$('#modalDePara input[name="idFiliais"]').val(''); // idFiliais do de/para
		$('#modalDePara input[name="naoIntegrarItemNotaFiscal"]').prop('checked', false);
		$('#modalDePara input[name="servico"]').val('');
		$('#modalDePara input[name="contaCredito"]').val('');
		$('#modalDePara input[name="contaDebito"]').val('');
		$('#modalDePara input[name="naoDiferenciarSegmentacao"]').prop('checked', false);
		$('#modalDePara input[name="considerarNaComissao"]').prop('checked', false);
		$('#modalDePara input[name="segmentacao"]').val('');
		$('#modalDePara select[name="itemSAP"] option[value=""]').prop("selected", true).trigger('change');

		for (let i = 1; i <= 5; i++) {
			$('#modalDePara select[name="dimensao' + i + '"] option[value=""]').prop("selected", true).trigger('change');
		}

		$('#modalDePara .titulo-modal').html(l['Cadastrar']);
		$('.salvar').attr('is-add', 1);
		$('#modalDePara').modal('show');
	});

	$('#modalDePara .salvar').click(function(){
		const action = $(this).data('action');
		let is_add = $('.salvar').attr('is-add');
		toggleLoading();

		let id = $('#modalDePara input[name="idB1HV2DeParaNFServico"]').val();
		let idFilial = $('#modalDePara input[name="idFiliais"]').val();
		let naoIntegrarItemNotaFiscal = $('#modalDePara input[name="naoIntegrarItemNotaFiscal"]').is(':checked') ? 1 : 0;
		let servico = $('#modalDePara input[name="servico"]').val();
		let contaCredito = $('#modalDePara input[name="contaCredito"]').val();
		let contaDebito = $('#modalDePara input[name="contaDebito"]').val();
		let naoDiferenciarSegmentacao = $('#modalDePara input[name="naoDiferenciarSegmentacao"]').is(':checked') ? 1 : 0;
		let considerarNaComissao = $('#modalDePara input[name="considerarNaComissao"]').is(':checked') ? 1 : 0;
		let segmentacao = $('#modalDePara input[name="segmentacao"]').val();
		let itemSAP = $('#modalDePara select[name="itemSAP"] option:selected').val();

		let dimensoes = [];
		for (let i = 1; i <= 5; i++) {
			let dimensao = $('#modalDePara select[name="dimensao' + i + '"] option:selected').val();
			if (dimensao !== null) {
				dimensoes.push(dimensao);
			}
		}

		$.post(action, {
			id: id,
			idFiliais: idFilial,
			guid: $('input[name="guid"]').val(),
			naoIntegrarItemNotaFiscal: naoIntegrarItemNotaFiscal,
			servico: servico,
			contaCredito: contaCredito,
			contaDebito: contaDebito,
			naoDiferenciarSegmentacao: naoDiferenciarSegmentacao,
			considerarNaComissao: considerarNaComissao,
			segmentacao: segmentacao,
			itemSAP: itemSAP,
			dimensao1: dimensoes[0] || null,
			dimensao2: dimensoes[1] || null,
			dimensao3: dimensoes[2] || null,
			dimensao4: dimensoes[3] || null,
			dimensao5: dimensoes[4] || null,
			isAdd: is_add,
			aliquotaPis: $('#modalDePara input[name="aliquotaPis"]').val(),
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

function acaoDeletarDeParaItens(id = null, idFilial = null) {
	$('.deletar')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			const guidModelo = $('input[name="guid"]').val();

			let obj = $(this);
			let tableDataTable = $(".table-exibe").DataTable();
			let paramsAjax = {
				'guidModelo': (guidModelo == null) ? null : guidModelo,
				'idFilial' : (idFilial == null) ? null : $(obj).data("id_filial"),
				'id': (id == null) ? null : $(obj).data("id")
			}

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
					$(obj).data("url"),
					null,
					'text',
					paramsAjax,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if (!is_empty(ret["bol"], 1)) {
							tableDataTable.draw();
						}

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}

acaoDeletarDeParaItens('idB1HV2ModeloItensNF', 'idFilial');