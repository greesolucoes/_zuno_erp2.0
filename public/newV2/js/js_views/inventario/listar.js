function showModals() {
	$('button.tipoCadastro').unbind('click');
	$('button.tipoCadastro').click(function () {
		$('.modalTipoCadastro').modal('toggle');
	});
}

function showCSV() {
	$('button.inventario-csv').unbind('click');
	$('button.inventario-csv').click(function () {
		$('#inventario-csv').modal('toggle');
	});
}

function showModalRejeitarContagem() {
	$('button.show_modal_reject').unbind('click');
	$('button.show_modal_reject').click(function (e) {
		e.preventDefault();
		var obj = $(this);
		$('.descricao_reject textarea#motivo').val('');

		$('.modal_reject').modal('toggle');
		acaoReject(obj);
	});
}

function showModalMotivo() {
	$('button.show_modal_motivo').unbind('click');
	$('button.show_modal_motivo').click(function (e) {
		e.preventDefault();
		var obj = $(this);
		$('.descricao_motivo').html('');

		$('.modal_motivo').modal('toggle');
		showMotivo(obj);
	});
}

function showMotivo(obj) {
	var motivo = $(obj).parents('td').find('.descricaoContagem').text();
	$('.descricao_motivo').text(motivo);
}

function acaoReject(obj) {
	$('.reject').unbind('click');
	$('.reject').on("click", function (e) {
		e.preventDefault();
		var motivo = $(this).parents('.descricao_reject').find('textarea#motivo').val().trim();
		if(motivo !== null && motivo !== '' && motivo !== undefined){
			var url             = $(obj).data('url');
			var id              = $(obj).data('id');
			var tableDataTable  = $(obj).parents('.table-exibe').DataTable();

			swal({
				title: l['rejeitarContagem?'],
				text: l['apósUmaContagemSerNegadaNãoSeráPossívelDesfazerAAçãoAtéOReenvioDoMesmo'],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l['rejeitarContagem!'],
				cancelButtonText: l['desfazer!']
			}).then(function () {
				toggleLoading();
				ajaxRequest(true, url, null, 'text', {'id': id, 'motivo': motivo}, function (ret) {
					if (ret != 0) {
						swal(
							l['contagemNegada'],
							l['aContagemFoiNegadaComSucesso!'],
							"success"
						);
						tableDataTable.draw();
						$('.modal_reject').modal('toggle');
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
				l['oCampoDeMotivoParaARejeiçãoDaContagemNãoPodeSerVazio'],
				'error'
			);
		}
	});
}

function uploadContagem() {
	$('.upload').unbind('click');
	$('.upload').on("click", function (e) {
		e.preventDefault();
		var obj          = $(this);
		var url          = $(obj).data('url');
		var id           = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['reenviarContagem?'],
			text: l['casoConfirmeOReenvio,SuaContagemSeráEnviadaEAnalisadaParaSerAceita,Continuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['reenviar!'],
			cancelButtonText: l['cancelar!']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (ret != 0) {
					swal(
						l['contagemReenviada'],
						l['aContagemFoiReenviadaComSucesso!'],
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

function acaoAprovar() {
	$('.aprovar').unbind('click');
	$('.aprovar').on("click", function (e) {
		e.preventDefault();
		var obj = $(this);
		var url = $(this).data('url');
		var id = $(this).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['aprovarContagem?'],
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['aprovarContagem!'],
			cancelButtonText: l['cancelar!']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {
				if (ret != 0) {
					swal(
						l['contagemAprovada'],
						l['aContagemFoiAprovadaComSucesso!'],
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

function acaoFinalizar() {
	$('.finalizar').unbind('click');
	$('.finalizar').on("click", function (e) {
		e.preventDefault();
		var obj = $(this);
		var url = $(this).data('url');
		var id = $(this).data('id');
		var horaContagem = $(this).data('hora-contagem').substring(0, 5);
		var urlAprovar   = $('.data-views').data('url_aprovar');
		var urlRejeitar  = $('.data-views').data('url_rejeitar');
		var urlExcluir   = $('.data-views').data('url_excluir');
		var autoAprovar  = $('.data-views').data('auto_aprovar_inventarios');
		var permAprovar  = $('.data-views').data('perm_aprovar');
		var tableDataTable = $(this).parents('.table-exibe').DataTable();
		if(autoAprovar == 1){
			swal({
				title: l['fecharContagem?'],
				html: l['desejaFecharAContagemNoHorario'] + `<b style='font-weight: 700;'>${horaContagem}h?</b> <br>` + l['apósAContagemSerFechadaNãoSeráPossívelDesfazerAAção!'],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l['fecharContagem!'],
				cancelButtonText: l['cancelar!']
			}).then(function () {
				toggleLoading();
				ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {
					if (ret != 0) {
						swal(
							l['contagemFechada'],
							l['aContagemFoiFechadaComSucesso!'],
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
		}else{
			swal({
				title: l['enviarContagem?'],
				text: l['apósAContagemSerEnviadaParaAprovação,NãoSeráPossívelDesfazerAAção!'],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l['enviarContagem!'],
				cancelButtonText: l['cancelar!']
			}).then(function () {
				toggleLoading();
				ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {
					if (ret != 0) {
						swal(
							l['contagemEnviada'],
							l['aContagemFoiEnviadaComSucesso!'],
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
		}
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
			title: l['excluirContagem?'],
			text: l['casoConfirmeAExclusão,AContagemSeráExcluídaDoBancoDeDados,Continuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['excluir!'],
			cancelButtonText: l['cancelar!']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (ret != 0) {
					swal(
						l['contagemExcluída'],
						l['aContagemFoiExcluídaComSucesso!'],
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

function uploadReg() {
	$('.uploadERP').unbind('click');
	$('.uploadERP').on("click", function (e) {
		e.preventDefault();
		var obj            = $(this);
		var url            = $(obj).data('url');
		var id             = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['reenviarContagem?'],
			text: l['casoConfirmeOReenvio,AContagemSeráReenviadaParaAAprovaçãoNoErp,Continuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['reenviar!'],
			cancelButtonText: l['cancelar!']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if (ret != 0) {
					swal(
						l['contagemReenviada'],
						l['aContagemFoiReenviadoComSucesso!'],
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

function acionarAcoesListagemInventario(){
	uploadContagem();
	acaoAprovar();
	acaoFinalizar();
	uploadReg();
	excluirReg();
	showModalRejeitarContagem();
	showModalMotivo();
	showModals();
	showCSV();
}

acionarAcoesListagemInventario();

$('input[name="inventario-csv"]').off('change').on('change', function() {
	let nomeArquivo = this.value;

	$('#btn-inventario-csv').addClass('disabled');

	if (nomeArquivo.substr(nomeArquivo.length - 4) === '.csv' ||
		nomeArquivo.substr(nomeArquivo.length - 4) === '.txt'
	) {
		$('#btn-inventario-csv').removeClass('disabled');
	} else {
		swal(
			l['atenção!'],
			l['selecioneUmArquivoValido'] + l['csvOuTxt'],
			'warning'
		).catch(swal.noop);
	}
})