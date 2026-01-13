function reenviarTodasOps(){
	$('.reenviarOps').off();
	$('.reenviarOps').on('click', function (e){
		let url = $('.data-views').data('url_reenviar_todas_ops');
		let tableDataTable = $('.table-exibe').DataTable();

		swal({
			title: l['reenviarOrdemDeProducao'],
			text: l['casoConfirmeOReenvioAOrdemDeProducaoSeraReenviadaParaOERPContinuar'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['enviar'],
			cancelButtonText: l['cancelar']
		}).then(function(){
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {}, function(ret){
				if(ret != 0){
					swal(
						l['ordemReenviada'],
						l['aOrdemDeProducaoFoiReenviada'],
						"success"
					);
					tableDataTable.draw();
				} else{
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
				}
				toggleLoading();
			})
		}).catch(swal.noop);
	});
}

function manageStats(){
	$('.manageStats').off("click");
	$('.manageStats').on("click", function(e){
		e.preventDefault();
		var sttSelected = $('#hiddenStatus').val();
		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');
		var statusErp = $(obj).data('status');
		var entrProdAcab = $(obj).data('epa');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();
		var inputOptions = {};

		//Definimos os status
		if(statusErp == 'p'){
			//Se o item possuir alguma entrada de produto acabado, não permitido voltar para alguns status
			if(entrProdAcab == 0){
				inputOptions.c = l['cancelado'];
				inputOptions.p = l['planejado'];
			}
			inputOptions.l = l['liberado'];
		}
		if(statusErp == 'l'){
			//Se o item possuir alguma entrada de produto acabado, não permitido voltar para alguns status
			if(entrProdAcab == 0){
				inputOptions.c = l['cancelado'];
			}
			inputOptions.f = l['fechado'];
			inputOptions.l = l['liberado'];
		}

		swal({
			title: l['statusDaOrdemDeProducao'],
			input: 'select',
			inputOptions: inputOptions,
			inputValue: sttSelected,
			inputPlaceholder: l['statusDaOrdemDeProducao'],
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['alterar'],
			cancelButtonText: l['cancelar'],
		}).then(function(selected){
			toggleLoading();
			if(is_empty(selected,1)){
				swal(
					l['erro'],
					l['selecioneUmStatus'],
					"error"
				);
				toggleLoading();
				return;
			}
			ajaxRequest(true, url, null, 'text', {'id': id, 'sttSelected': selected}, function(ret){
				if(ret == 0){
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
				} else if(ret == 2){
					swal(
						l['erro'],
						l['dataAtualDoEncerramentoTemQueSerIgualOuAposADataDaOrdemDeProducao'],
						"error"
					);
				} else{
					var txt = '';
					switch(selected){
						case 'c' :
							txt = l['cancelado'];
							break;
						case 'f' :
							txt = l['fechado'];
							break;
						case 'l' :
							txt = l['liberado'];
							break;
						case 'p' :
							txt = l['planejado'];
							break;
					}

					swal(
						l['statusAlterado'],
						l['aOrdemDeProducaoFoiAlterada'],
						"success"
					);

					$(obj).parents('tr').find('td.status .ocultar').text(l['pedidoAguardandoConfirmacaoDoErp']);
					$(obj).parents('tr').find('td.status i').removeClass('circle-status-red');
					$(obj).parents('tr').find('td.status i').addClass('circle-status-black');
					$(obj).parents('tr').find('td.status i').attr('title', l['ordemDeProducaoAguardandoAtualizacaoDeStatusNoERP']);
					$(obj).parents('tr').find('td.statusErp').text(txt);

					$(obj).parents('td').find('button.excluirReg').remove();
					$(obj).parents('td').find('a.lancarProdutoAcabado').remove();

					tableDataTable.draw();
					$(obj).remove();
				}
				toggleLoading();
			})
		}).catch(swal.noop);
	})
};

function reenviarOrdem(){
	$('.reenviarOrdem').off("click");
	$('.reenviarOrdem').on("click", function(e){
		e.preventDefault();
		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['reenviarOrdemDeProducao'],
			text: l['casoConfirmeOReenvioAOrdemDeProducaoSeraReenviadaParaOERPContinuar'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['enviar'],
			cancelButtonText: l['cancelar']
		}).then(function(){
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function(ret){
				if(ret != 0){
					ret = $.parseJSON(ret);
					swal(
						l['ordemReenviada'],
						l['aOrdemDeProducaoFoiReenviada'],
						"success"
					);

					$(obj).parents('td').find('button.show_modal_motivo').remove();
					$(obj).parents('td').find('.descricaoPed').remove();
					$(obj).parents('td').find('button.excluirReg').remove();

					$(obj).parents('tr').find('td.status .ocultar').text(l['ordemProducaoAguardandoRespostaDoErp']);
					$(obj).parents('tr').find('td.status i').removeClass('circle-status-yellow');
					$(obj).parents('tr').find('td.status i').removeClass('circle-status-red');
					$(obj).parents('tr').find('td.status i').addClass('circle-status-black');
					$(obj).parents('tr').find('td.status i').attr('title', l['ordemProducaoAguardandoRespostaDoErp']);
					$(obj).parents('td').find('.alterar').remove();

					$(obj).remove();
					tableDataTable.draw();
				} else{
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
				}
				toggleLoading();
			})
		}).catch(swal.noop);
	})
};

function excluirReg(){
	$('.excluirReg').off("click");
	$('.excluirReg').on("click", function(e){
		e.preventDefault();
		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['excluirOrdemProducao'],
			text: l['casoConfirmeAExclusaoAOrdemDeProducaoSeraExcluidaDoBancoDeDadosContinuar'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['excluir'],
			cancelButtonText: l['cancelar']
		}).then(function(){
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function(ret){
				if(ret != 0){
					swal(
						l['ordemDeProducaoExcluida'],
						l['aOrdemDeProducaoFoiExcluidaComSucesso'],
						"success"
					);
//                    tableDataTable.row($(obj).parents('tr')).remove().draw();
					$(obj).parents('tr').remove();
				} else{
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
				}
				tableDataTable.draw();
				toggleLoading();
			})
		}).catch(swal.noop);
	})
};

function lancamentoProdutoAcabado(){
	$('.lancamentoProdutoAcabado').off("click");
	$('.lancamentoProdutoAcabado').on("click", function(e){
		e.preventDefault();

		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		$('#divLoading' + id).show();
		$('#divLPA' + id).hide();

		ajaxRequest(true, url, null, 'text', {'id': id}, function(ret){
			$('#divLPA' + id).show();
			$('#divLoading' + id).hide();
			$('#modalLPA').modal('toggle');
			$('#divResultadoLPA').html(ret);
		})

	})
};

function saidaInsumos(){
	$('.saidaInsumosView').off("click");
	$('.saidaInsumosView').on("click", function(e){
		e.preventDefault();

		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		$('#divLoadingInsumos' + id).show();
		$('#divSaidaInsumos' + id).hide();

		ajaxRequest(true, url, null, 'text', {'id': id}, function(ret){
			$('#divSaidaInsumos' + id).show();
			$('#divLoadingInsumos' + id).hide();
			$('#modalSaidaInsumos').modal('toggle');
			$('#divResultadoSaidaInsumos').html(ret);
		})

	})
};

function etapaProducao(){
	$('.etapaProducao').off("click");
	$('.etapaProducao').on("click", function(e){
		e.preventDefault();

		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');

		$('.modalEtapasProducao').modal('toggle')

		$('.salvarEtapa').off("click");
		$('.salvarEtapa').on("click", function(e){
			ajaxRequest(true, url, null, 'text', {
				'idOrdemProducao': id,
				'idEtapaProducao': $('.select_etapas_producao').val()
			}, function(ret){
				if(ret){
					swal({
						title: l['sucesso'],
						text: l['etapaSalvaComSucesso'],
						type: "success"
					}).catch(swal.noop);
					$('.modalEtapasProducao').modal('toggle')
				}
			});
		});

	});
}

function acoesBotoes() {
	let __acaoPreImpressao = function (obj, listRegs, imprimirTodos) {
		let tableDataTable = $($(".dataTables_scrollBody").find('.table-exibe')).DataTable();
		if(is_empty(listRegs, 1) && is_empty(imprimirTodos, 1)) return;

		var quantidadeImpressao = 1

		$(".layout_para_impressao").off('change');
		$(".layout_para_impressao").on('change', function () {

			switch (parseInt($(this).val())) {
				case 1:
					quantidadeImpressao = obj.data('quantidadeembalagem');
					break;
				case 2:
					quantidadeImpressao = obj.data('quantidadeplanejada');
					break;
			}

			$("#impressao-quantidade").val(parseInt(quantidadeImpressao));
			$("#impressao-ids").val(listRegs);
		});

		$("#impressao-ids").val(JSON.stringify(listRegs));
		// $("#impressao-impressora option").remove();
		// $("#impressao-impressora").append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');

		tableDataTable.draw();
		$('.modal-impressao').modal('toggle');
	}

	$(".imprimir").off('click');
	$(".imprimir").on('click', function () {
		__acaoPreImpressao($(this), [$(this).data("id")], 0)
	});


	$("#impressao-salvar").off('click');
	$("#impressao-salvar").on('click', function () {
		let objSave = {
			'id': $("#impressao-ids").val(),
			'layout': $("#layout_para_impressao").val(),
			'impressora': $("#impressao-impressora").val(),
			'quantidade': $("#impressao-quantidade").val(),
		};

		let obj = $(this);
		let url = $(obj).data('url');
		if(is_empty(url, 1)) return;

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
			ajaxRequest(true, url, null, 'text', objSave, function (ret) {
				try{
					ret = JSON.parse(ret);

					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);

					if(ret['bol']) {
						$('.modal-impressao').modal('hide');
					}
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

	$(".verErrorErp").off('click');
	$(".verErrorErp").on('click', function () {
		let texto = $(this).data('texto');

		texto = texto.replaceAll('|','<br>');

		swal({
			title: l["atenção!"],
			html: texto,
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		});
	});

}

function criarSelect(){
	$(".select_etapas_producao").select2Ajax();
	$(".select_etapas_producao").data('init', '');
	$(".layout_para_impressao").select2({
		language: _lang,
		allowClear: false
	});
	$("#impressao-impressora").select2Ajax();
	$("#impressao-impressora").data('init', '');
}

function allFunctionsSolicitacao(){
	criarSelect();
	manageStats();
	reenviarOrdem();
	excluirReg();
	lancamentoProdutoAcabado();
	saidaInsumos();
	etapaProducao();
	reenviarTodasOps();
}

allFunctionsSolicitacao();