const table = $('.table-exibe');
const dataInicial = $('#dataInicial');
const dataFinal = $('#dataFinal');
const btnCancelarSelecionados = '.botaoCancelarSelecionadas';
const btnReenviarSelecionados = '.botaoReenviarSelecionadas';

function atualizaDataTable() {
	const status = $('#status').val();
	const urlAjax = $('table.table-exibe').data('url_ajax');

	let get = `${urlAjax}`;
	let hasGet = false;

	if (dataInicial != '') {
		hasGet = true;
		get += `?dataInicial=${dataInicial.val().split('/').reverse().join('-')}`;
	}

	if (dataFinal != '') {
		if (hasGet) {
			get += '&';
		} else {
			get += '?';
			hasGet = true;
		}

		get += `dataFinal=${dataFinal.val().split('/').reverse().join('-')}`
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
		let dataFinalInt = Math.round(new Date(dataFinal.val().split('/').reverse().join('-')).getTime())
		let dataInicialInt = Math.round(new Date(dataInicial.val().split('/').reverse().join('-')).getTime())

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
	});

table.on('draw.dt', function () {
	$('input[data-selectable="selectable"]')
		.parents('tr')
		.click(function(e) {
			$(e.currentTarget).toggleClass('selected')

			$(btnCancelarSelecionados).removeClass('botaoCancelarAtivo')
			$(btnReenviarSelecionados).removeClass('botaoReenviarAtivo')

			// só ativamos o botão para exclusão em lote caso haja mais de uma seleção
			if ($('.selected').length > 0) {
				$(btnCancelarSelecionados).addClass('botaoCancelarAtivo')
				$(btnReenviarSelecionados).addClass('botaoReenviarAtivo')
			}
		});
})

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

		// objeto de envio para o ajax
		let guids = [];
		[...$('.selected')].forEach(function (element) {
			guids.push($(element).find('input').data('guid'))
		});

		swal({
			title: $(obj).data("titulo"),
			text: $(obj).data("texto"),
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
				{ guids },
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
						$(btnCancelarSelecionados).removeClass('botaoCancelarAtivo');
						$(btnReenviarSelecionados).removeClass('botaoReenviarAtivo');

						// como se trata de uma table simples, a página é
						// recarregada após o sucesso na solicitação
						table.DataTable().draw();
						toggleLoading();
					})
				}
			)
		}).catch(swal.noop);
	});

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

		// objeto de envio para o ajax
		let guids = [];
		[...$('.selected')].forEach(function (element) {
			guids.push($(element).find('input').data('guid'))
		});

		swal({
			title: $(obj).data("titulo"),
			text: $(obj).data("texto"),
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
				{ guids },
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
						$(btnReenviarSelecionados).removeClass('botaoReenviarAtivo');
						$(btnCancelarSelecionados).removeClass('botaoCancelarAtivo');

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
}