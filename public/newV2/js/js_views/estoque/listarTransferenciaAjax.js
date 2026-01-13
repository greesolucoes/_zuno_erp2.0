function showCSV() {
	$('button.inventario-csv').unbind('click');
	$('button.inventario-csv').click(function () {
		$('#modalTipoCriacaoTransferencia').modal('toggle');
		$('#inventario-csv').modal('toggle');
	});
}

function showModals() {
	$('button.tipoCadastro').unbind('click');
	$('button.tipoCadastro').click(function () {
		$('.modalTipoCadastro').modal('toggle');
	});
}
showModals();

function excluirReg() {
	$('.excluirReg').unbind('click');
	$('.excluirReg').on("click", function (e) {
		e.preventDefault();
		var obj            = $(this);
		var url            = $(obj).data('url');
		var id             = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l["excluirTransferência?"],
			text: l["casoConfirmeAExclusão,ATransferênciaSeráExcluídaDoBancoDeDados,Continuar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['excluir'],
			cancelButtonText: l['cancelar']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (ret != 0) {
					swal(
						l["transferênciaExcluída"],
						l["aTransferênciaFoiExcluídaComSucesso!"],
						"success"
					);
					tableDataTable.row($(obj).parents('tr')).remove().draw();
				} else {
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
				}
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

function uploadReg() {
	$('.uploadERP').unbind('click');
	$('.uploadERP').on("click", function (e) {
		e.preventDefault();
		var obj            = $(this);
		var url            = $(obj).data('url');
		var id             = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l["reenviarTransferência?"],
			text: l["casoConfirmeOReenvio,ATransferênciaSeráReenviadaParaAAprovaçãoNoErp,Continuar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['reenviar'],
			cancelButtonText: l['cancelar']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (ret != 0) {
					swal(
						l["transferênciaReenviada"],
						l["aTransferenciaFoiReenviadoComSucesso!"],
						"success"
					);

					$(obj).parents('tr').find('td.status .ocultar').text(l['transferenciaAguardandoConfirmacaoDoErp']);
					$(obj).parents('tr').find('td.status i').removeClass('circle-status-red');
					$(obj).parents('tr').find('td.status i').addClass('circle-status-black');
					$(obj).parents('tr').find('td.status i').attr('title', l['transferenciaAguardandoConfirmacaoDoErp']);

					$(obj).parents('td').find('button.excluirReg').remove();

					tableDataTable.draw();
					$(obj).remove();
				} else {
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
				}
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}
function allFunctionsSolicitacao(){
	excluirReg();
	uploadReg();
	showCSV();
}
allFunctionsSolicitacao();

$('input[name="inventario-csv"]').off('change').on('change', function() {
	let nomeArquivo = this.value;

	$('#btn-inventario-csv').addClass('disabled');

	if (nomeArquivo.substr(nomeArquivo.length - 4) === '.txt'
	) {
		$('#btn-inventario-csv').removeClass('disabled');
	} else {
		swal(
			l['atenção!'],
			l['selecioneUmArquivoValido'] + ' (*.txt)',
			'warning'
		).catch(swal.noop);
	}
})