function showModals() {
	$('button.apontamento-perde').unbind('click');
	$('button.apontamento-perde').click(function () {
		$('.modal-apontamento-perde').modal('toggle');
	});
}

function showCSV() {
	$('button.apontamento-csv').unbind('click');
	$('button.apontamento-csv').click(function () {
		$('.modal-apontamento-csv').modal('toggle');
	});
}

// sistema de aprovacao - cadastro de motivo rejeicao
function showModalRejeitarApontamento() {
	$('button.show_modal_reject').unbind('click');
	$('button.show_modal_reject').click(function (e) {
		e.preventDefault();
		var obj = $(this);
		$('.descricao_delete textarea#motivo').val('');

		$('.modal_delete').modal('toggle');
		acaoDelete(obj);
	});
}
// sistema de aprovacao - cadastro de motivo rejeicao
function showModalMotivoRejeicaoApontamento() {
	$('button.show_modal_motivo').unbind('click');
	$('button.show_modal_motivo').click(function (e) {
		e.preventDefault();
		var obj = $(this);
		$('.descricao_motivo').html('');

		$('.modal_motivo').modal('toggle');
		showMotivo(obj);
	});
}

function reenviar(){
	$('.uploadERP').unbind('click');
	$('.uploadERP').on("click", function (e) {
		e.preventDefault();
		var obj            = $(this);
		var url            = $(obj).data('url');
		var id             = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l["reenviarApontamento"],
			text: l["casoConfirmeOReenvio,OApontamentoSeráReenviadaParaAAprovaçãoNoErp,Continuar?"],
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
						l["apontamentoReenviado"],
						l["oApontamentoFoiReenviadoComSucesso!"],
						"success"
					);

					$(obj).parents('tr').find('td.status .ocultar').text(l['apontamentoAguardandoConfirmacaoDoErp']);
					$(obj).parents('tr').find('td.status i').removeClass('circle-status-red');
					$(obj).parents('tr').find('td.status i').addClass('circle-status-black');
					$(obj).parents('tr').find('td.status i').attr('title', l['apontamentoAguardandoConfirmacaoDoErp']);

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

function excluirReg() {
	$('.excluirReg').unbind('click');
	$('.excluirReg').on("click", function (e) {
		e.preventDefault();
		var obj            = $(this);
		var url            = $(obj).data('url');
		var id             = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l["excluirApontamento"],
			text: l["casoConfirmeAExclusão,OApontamentoSeráExcluídoDoBancoDeDados,Continuar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["excluir!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (ret != 0) {
					swal(
						l["apontamentoExcluído"],
						l["oApontamentoFoiExcluídoComSucesso!"],
						"success"
					);
					tableDataTable.row($(obj).parents('tr')).remove().draw();
				} else {
					swal(
						l["erro"],
						l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"],
						"error"
					);
				}
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

/** Enviar o apontamento para aprovar ou reprovar */
function uploadApontamento() {
	$('.upload').unbind('click');
	$('.upload').on("click", function (e) {
		e.preventDefault();
		var obj          = $(this);
		var url          = $(obj).data('url');
		var id           = $(obj).data('id');
		var autoAprovar  = $('.data-views').data('auto_aprovar_apontamentos');
		var motivo       = $(this).parents('tr').find('.descricaoPed').text().trim();

		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		var replaceSuccess = '';
		var tituloMsg = '';
		var textoMsg = '';

		if (autoAprovar == 1 || motivo == null || motivo == '' || motivo == undefined) {
			replaceSuccess = l['enviado'];
			tituloMsg = l['enviarApontamento?'];
			textoMsg = l['casoConfirmeOEnvio,SeuApontamentoSeraEnviadoParaOErpOuParaAAprovacao,Continuar?'];
		} else {
			replaceSuccess = l['reenviado'];
			tituloMsg = l['reenviarApontamento'];
			textoMsg = l['casoConfirmeOReenvio,SeuApontamentoSeraEnviadoEAnalisadoNovamenteParaSerAceito,Continuar?'];
		}

		swal({
			title: tituloMsg,
			text: textoMsg,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['enviar'],
			cancelButtonText: l['cancelar!']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (ret != 0) {
					swal(
						l['apontamento'] + ' ' + replaceSuccess,
						l['oApontamentoFoi'] + ' ' + replaceSuccess + '' + l['comSucesso!'],
						"success"
					);
					tableDataTable.draw();
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

/**
 * aprovar o apontamento para enviar para o sap
 */
function acaoAprovar() {
	$('.aprovar').unbind('click');
	$('.aprovar').on("click", function (e) {
		e.preventDefault();
		var obj = $(this);
		var url = $(this).data('url');
		var id = $(this).data('id');

		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['aprovarApontamento?'],
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['aprovarApontamento!'],
			cancelButtonText: l['desfazer!']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {
				if (ret != 0) {
					swal(
						l['ApontamentoAprovado'],
						l['oApontamentoFoiAprovadoComSucesso!'],
						"success"
				).catch(swal.noop);
					tableDataTable.draw();
				} else {
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					).catch(swal.noop);
				}
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

/**
 * funcao para rejeitar um apontamento no sistema de aprovacao
 * @param obj
 */
function acaoDelete(obj) {
	$('.delete').unbind('click');
	$('.delete').on("click", function (e) {
		e.preventDefault();
		var motivo = $(this).parents('.descricao_delete').find('textarea#motivo').val().trim();
		if(motivo !== null && motivo !== '' && motivo !== undefined){
			var url             = $(obj).data('url');
			var id              = $(obj).data('id');
			var tableDataTable  = $(obj).parents('.table-exibe').DataTable();

			swal({
				title: l['rejeitarApontamento?'],
				text: l['aposUmApontamentoSerRejeitadoNaoSeraPossivelDesfazerOCancelamentoAteOReenvioDoMesmo'],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l['rejeitar'],
				cancelButtonText: l['cancelar!']
			}).then(function () {
				toggleLoading();
				ajaxRequest(true, url, null, 'text', {'id': id, 'motivo': motivo}, function (ret) {
					if (ret != 0) {
						swal(
							l['apontamentoRejeitado'],
							l['oApontamentoFoiRejeitadoComSucesso!'],
							"success"
						);
						tableDataTable.draw();

						$('.modal_delete').modal('toggle');
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
		}else{
			swal(
				l['motivoNãoDefinido'],
				l['oCampoDeMotivoParaARejeicaoDeApontamentoNaoPodeSerVazio'],
				'error'
			);
		}
	});
}

/**
 * exibe o motivo da reprovacao do apontamento
 * @param obj
 */
function showMotivo(obj) {
	var motivo = $(obj).parents('td').find('.descricaoPed').text();
	$('.descricao_motivo').text(motivo);
}

function allFunctionsSolicitacao(){
	excluirReg();
	reenviar();
	showModals();
	showCSV();
	// sistema de aprovacao edição
	uploadApontamento();
	acaoAprovar();
	showModalRejeitarApontamento();
	showModalMotivoRejeicaoApontamento();
	// /sistema de aprovacao edição
}

allFunctionsSolicitacao();