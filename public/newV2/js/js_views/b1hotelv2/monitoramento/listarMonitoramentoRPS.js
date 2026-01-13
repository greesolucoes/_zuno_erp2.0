const table = $('.table-exibe');
const dataInicial = $('#dataInicialRPS');
const dataFinal = $('#dataFinalRPS');
const btnCancelarSelecionados = '.botaoCancelarSelecionadas';
const btnReprocessarSelecionados = '.botaoReenviarSelecionadas';
const botaoMarcarTudo = '.botaoMarcarTudo';
const botaoDesmarcarTudo = '.botaoDesmarcarTudo';

function atualizaDataTable() {
	const status = $('#statusRPS').val();
	const urlAjax = $('table.table-exibe').data('url_ajax');

	let get = `${urlAjax}`;
	let hasGet = false;

	if (dataInicial != '') {
		hasGet = true;
		get += `?dataInicial=${strFormatDate($(dataInicial).val(), configLocation.formatDatePicker)}`;
	}

	if (dataFinal != '') {
		if (hasGet) {
			get += '&';
		} else {
			get += '?';
			hasGet = true;
		}

		get += `dataFinal=${strFormatDate($(dataFinal).val(), configLocation.formatDatePicker)}`
	}

	if (status != '') {
		get += (hasGet) ? '&' : '?';
		get += `status=${status.toString()}`
	}

	table.each(function (){
		if($.fn.DataTable.isDataTable(this)) {
			const dataTable = $(this).DataTable();
			dataTable.clear();
			dataTable.destroy();
		}
	});

	$(table).data("url_ajax", get);
	allTables();
}

$('#btnFiltrar')
	.off('click')
	.on('click', function () {
		let dataFinalInt = strFormatDate($(dataFinal).val(), configLocation.formatDatePicker)
		let dataInicialInt = strFormatDate($(dataInicial).val(), configLocation.formatDatePicker)

		if (dataFinalInt < dataInicialInt) {
			swal({
				title: l['porFavorInformeAsDatasDoFiltroCorretamente'],
				text: l['aDataFinalDeveSerMaisRecenteQueADataInicial'],
				type: "info",
				showCancelButton: false,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["continuar!"]
			});

			return;
		}

		atualizaDataTable();
	}
);

	
$('#btnShowErrors')
	.off('click')
	.on('click', function () {
		
		const get = getQryStrErrosNoPeriodo($(this).data('url'))

		if(!get) {
			return
		}

		$("#tbl-erros-no-periodo tbody").empty("");

		toggleLoading();
		ajaxRequest(true, get, null, 'json', null, (response)=>{
			toggleLoading();
			if(response.length > 0) {
				response.forEach((el) => {
					$("#tbl-erros-no-periodo tbody").append(`
						<tr>
							<td class="text-center">${el.status}</td>
							<td class="text-center">${el.dataDocumento}</td>
							<td class="text-center">${el.filial}</td>
							<td class="text-center">${el.cliente}</td>
							<td class="text-center">${el.numRPS}</td>
							<td class="text-left">${el.erro}</td>
						</td>
					`)
				})
				$("#modalErrosNoPeriodo").modal('show')
			}
		}) 
	}
);

$('#btnDownErrors')
	.off('click')
	.on('click', function () {
		
		const get = getQryStrErrosNoPeriodo($(this).data('url'))

		if(!get) {
			return
		}

		window.location.href = get;
	}
);

table.on('draw.dt', function () {
	$('input')
		.parents('tr')
		.click(function(e) {
			$(e.currentTarget).toggleClass('selected')

			let selected = $('.selected').length
			let cancelSelected = $('.selected input[data-selectable="selectable"]').length

			$(btnCancelarSelecionados).removeClass('botaoCancelarAtivo')
			$(btnReprocessarSelecionados).removeClass('botaoReenviarAtivo')
			$(botaoMarcarTudo).removeClass('botaoReenviarAtivo')
			$(botaoDesmarcarTudo).removeClass('botaoReenviarAtivo')

			if (selected > 0 ) {
				$(btnReprocessarSelecionados).addClass('botaoReenviarAtivo')
				$(botaoMarcarTudo).addClass('botaoReenviarAtivo')
				$(botaoDesmarcarTudo).addClass('botaoReenviarAtivo')

				if (cancelSelected > 0 && cancelSelected == selected) {
					$(btnCancelarSelecionados).addClass('botaoCancelarAtivo')
				}
			}
		});
})

// cancelar selecionadas
$('.botaoCancelarSelecionadas')
	.unbind("click")
	.on("click", function (e) {
		e.preventDefault();

		let obj = $(this);

		// propriedades padrão
		let confirmButtonColor = "#3085d6";
		let cancelButtonColor= "#d33";
		let confirmButtonText = l["continuar!"];
		let cancelButtonText = l["cancelar!"];
		let cancelSelected = $('.selected input[data-selectable="selectable"]')
		let texto = $(obj).data("texto").replace("{{x}}", cancelSelected.length)
		// objeto de envio para o ajax
		let hashkeys = [];
		[...$('.selected input[data-selectable="selectable"]')].forEach(function (element) {
			hashkeys.push($(element).data('hash'))
		});

		swal({
			title: $(obj).data("titulo"),
			text: texto,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor,
			cancelButtonColor,
			confirmButtonText,
			cancelButtonText
		}).then(function () {
			ajaxRequest(
				true,
				$(obj).data("url"),
				null,
				'text',
				{ hashkeys },
				function () {
					swal({
						title: $(obj).data("titulo_pos"),
						text: $(obj).data("texto_pos"),
						type: "success",
						showCancelButton: false,
						confirmButtonColor,
						cancelButtonColor,
						confirmButtonText
					}).then(function() {
						toggleLoading();
						$(btnCancelarSelecionados).removeClass('botaoCancelarAtivo')

						// como se trata de uma table simples, a página é
						// recarregada após o sucesso na solicitação
						table.DataTable().draw();
						toggleLoading();
					})
				}
			)
		}).catch(swal.noop);
	});

// reprocessar filtrados
$('.botaoMarcarTudo')
	.unbind("click")
	.on("click", function (e) {
		e.preventDefault();
		$(".table").find("tr:not(.selected)").trigger("click");
	});

$('.botaoDesmarcarTudo')
	.unbind("click")
	.on("click", function (e) {
		e.preventDefault();
		$(".table").find("tr.selected").trigger("click");
	});

// reprocessar selecionadas
$('.botaoReenviarSelecionadas')
	.unbind("click")
	.on("click", function (e) {
		e.preventDefault();

		let obj = $(this);
		// propriedades padrão
		let confirmButtonColor = "#3085d6";
		let cancelButtonColor= "#d33";
		let confirmButtonText = l["continuar!"];
		let cancelButtonText = l["cancelar!"];
		let selected = $('.selected')
		let texto = $(obj).data("texto").replace("{{x}}", selected.length)
		// objeto de envio para o ajax
		let hashkeys = [];
		[...$('.selected')].forEach(function (element) {
			hashkeys.push($(element).find('input').data('hash'))
		});

		swal({
			title: $(obj).data("titulo"),
			text: texto,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor,
			cancelButtonColor,
			confirmButtonText,
			cancelButtonText
		}).then(function () {
			ajaxRequest(
				true,
				$(obj).data("url"),
				null,
				'text',
				{ hashkeys },
				function () {
					swal({
						title: $(obj).data("titulo_pos"),
						text: $(obj).data("texto_pos"),
						type: "success",
						showCancelButton: false,
						confirmButtonColor,
						cancelButtonColor,
						confirmButtonText
					}).then(function() {
						toggleLoading();
						$(btnCancelarSelecionados).removeClass('botaoCancelarAtivo')

						// como se trata de uma table simples, a página é
						// recarregada após o sucesso na solicitação
						table.DataTable().draw();
						toggleLoading();
					})
				}
			)
		}).catch(swal.noop);
	});

// Função para exibição de mensagens de erro ao clicar no botão
// presente no status das importações com erro
function handleAcoesDePara() {
	$('.abrir-modal-obs').off('click');
	$('.abrir-modal-obs').on('click', function (e) {
		const hashkeynota = $(this).data('hashkey');
		const obsObrigatoria = $(this).data('obsobrigatoria');
		$("#hashkey_nf").val(hashkeynota);
		$("#observacoes_obrigatorias").val(obsObrigatoria);
		$('#modalObservacoes').modal('toggle');
	});

	$('.visualizar-erro')
		.unbind('click')
		.on('click', function (e) {
			e.preventDefault();

			let obj = $(this);

			swal(
				l['erro!'],
				$(obj).data('text'),
				'info'
			).catch(swal.noop);

			$(obj.parents('tr')[0]).toggleClass('selected');
		});

	$('.solicitar-reimportacao')
		.unbind('click')
		.on('click', function (e) {
			e.preventDefault();

			let obj = $(this);
			$(obj.parents('tr')[0]).toggleClass('selected');

			swal({
				title: l['desejaContinuar?'],
				text: l['aoContinuarOIntegradorBuscaraOArquivoNovamenteParaReprocessamento'],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();

				ajaxRequest(
					true,
					$('.datas_views').data('url_solicitar_reimportacao') + '/' + obj.data('hash'),
					null,
					'text',
					null,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["title"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if(ret["class"] == 'success') {
							$(".table-exibe").DataTable().draw();
						}

						toggleLoading();
					}
				)
			}).catch(swal.noop)
		});

	$('.solicitar-reprocessar-cancelamento')
		.unbind('click')
		.on('click', function (e) {
			e.preventDefault();

			let obj = $(this);
			$(obj.parents('tr')[0]).toggleClass('selected');

			swal({
				title: l['desejaContinuar?'],
				text: l['aoContinuarOIntegradorBuscaraOArquivoNovamenteParaCancelamento'],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();

				ajaxRequest(
					true,
					$('.datas_views').data('url_solicitar_cancelamento') + '/' + obj.data('hash'),
					null,
					'text',
					null,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["title"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if(ret["class"] == 'success') {
							$(".table-exibe").DataTable().draw();
						}

						toggleLoading();
					}
				)
			}).catch(swal.noop)
		});

	$('.alternar-ativar-registro')
		.unbind('click')
		.on('click', function (e) {
			e.preventDefault();

			const obj = $(this);
			$(obj.parents('tr')[0]).toggleClass('selected');

			const textSwal = obj.data('registro-ativo')
				? l['aoContinuarNaoSeraPossivelExecutarNenhumaOutraAcaoAteQueORegistroSejaReativado']
				: l['aoContinuarORegistroVoltaraAoStatusAnteriorAInativacao'];

			const newStatus = obj.data('registro-ativo') ? 0 : 1;

			swal({
				title: l['desejaContinuar?'],
				text: textSwal,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();

				ajaxRequest(
					true,
					$('.datas_views').data('url_alternar_ativacao_registro') + '/' + obj.data('hash') + '/' + newStatus,
					null,
					'text',
					null,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["title"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						if(ret["class"] == 'success') {
							$(".table-exibe").DataTable().draw();
						}

						toggleLoading();
					}
				)
			}).catch(swal.noop)
		});
}

/**
 * Função para exibir mensagens no modal de observações obrigatórias
 * @param jsonMessage
 * @param selector
 */
function addMessage(jsonMessage, selector = '.container-msg'){
	$(selector).html(
		`<div class="espacamento"></div>
		
		<div class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX" role="alert">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			${jsonMessage.msg}
		</div>`
	);
}

/**
 * Limpa os campos do modal de observação obrigatório
 */
function limpaCamposModal() {
	$('#modalObservacoes input[name="hashkey_nf"]').val('');
	$('#modalObservacoes input[name="observacoes_obrigatorias"]').val('');
}

/**
 * Ações do botão salvar do modal de observações obrigatórias
 */
$('#modalObservacoes .salvar').click(async function(){
	const action = $(this).data('action');
	const hashNota = $('#modalObservacoes input[name="hashkey_nf"]').val();
	const obsObriNota = $('#modalObservacoes textarea[name="observacoes_obrigatorias"]').val();

	await $.post(action, {
		hashkeyNota:	    hashNota,
		obsObrigatoriaNota:	obsObriNota
	}, function(retorno){
		if (retorno.class == 'success'){
			let tableDataTable = $(".table-exibe").DataTable();
			tableDataTable.draw();
			$(".btn-close").trigger("click");
			$('button[data-hashkey="'+hashNota+'"]').attr('data-obsobrigatoria', obsObriNota);
			limpaCamposModal();
		} else {
			addMessage(retorno, '.container-msg-modal');
		}
	});
});

getQryStrErrosNoPeriodo = (url) => {
	let dataFinalInt = strFormatDate($(dataFinal).val(), configLocation.formatDatePicker)
	let dataInicialInt = strFormatDate($(dataInicial).val(), configLocation.formatDatePicker)

	if (dataFinalInt < dataInicialInt) {
		swal({
			title: l['porFavorInformeAsDatasDoFiltroCorretamente'],
			text: l['aDataFinalDeveSerMaisRecenteQueADataInicial'],
			type: "info",
			showCancelButton: false,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["continuar!"]
		});

		return false
	}
	
	let get = `${url}`;
	let hasGet = false;

	if (dataInicial != '') {
		hasGet = true;
		get += `?dataInicial=${strFormatDate($(dataInicial).val(), configLocation.formatDatePicker)}`;
	}

	if (dataFinal != '') {
		if (hasGet) {
			get += '&';
		} else {
			get += '?';
			hasGet = true;
		}

		get += `dataFinal=${strFormatDate($(dataFinal).val(), configLocation.formatDatePicker)}`
	} 

	return get
}