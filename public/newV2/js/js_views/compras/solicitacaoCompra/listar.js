$('#config-tipo_cadastro').off('click');
$('#config-tipo_cadastro').on('click', function () {
	$('.modal-tipo_cadastro').modal('toggle');
});

function showModalRejeitar() {
	$('button.show_modal_rejeitar').unbind('click');
	$('button.show_modal_rejeitar').click(function (e) {
		e.preventDefault();
		let obj = $(this);
		$('.descricao_rejeitar textarea#motivo').val('');

		$('.modal_rejeitar').modal('toggle');
		acaoRejeitar(obj);
	});
}

function showModalMotivo() {
	$('button.show_modal_motivo').unbind('click');
	$('button.show_modal_motivo').click(function (e) {
		e.preventDefault();
		let obj = $(this);
		$('.descricao_motivo').html('');

		$('.modal_motivo').modal('toggle');
		showMotivo(obj);
	});
}

function showMotivo(obj) {
	let motivo = $(obj).parents('td').find('.descricaoRejeicao').text();
	$('.descricao_motivo').text(motivo);
}

function acaoRejeitar(obj) {
	$('.rejeitar').unbind('click');
	$('.rejeitar').on("click", function (e) {
		e.preventDefault();
		let motivo = $(this).parents('.descricao_rejeitar').find('textarea#motivo').val().trim();
		if(is_empty(motivo, 1)){
			swal(
				'<?= l("motivoNãoDefinido"); ?>',
				'<?= l("oCampoDeMotivoParaaRejeiçãoNãoPodeSerVazio"); ?>',
				'error'
			);
			return;
		}

		let url             = $(obj).data('url');
		let id              = $(obj).data('id');
		let tableDataTable  = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['essaAcaoSeraIrreversivel'],
			text: l['desejaContinuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['sim!'],
			cancelButtonText: l['nao']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id, 'motivo': motivo}, function (ret) {
				if(is_empty(ret, 1)){
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
				);
					toggleLoading();
					return;
				}

				swal(
					l['sucesso!'],
					l['operaçãoEfetuadaComSucesso!'],
					"success"
				);

				tableDataTable.draw();

				$('.modal_rejeitar').modal('toggle');
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

function uploadParaAprovacao() {
	$('.upload').unbind('click');
	$('.upload').on("click", function (e) {
		e.preventDefault();
		let obj          = $(this);
		let url          = $(obj).data('url');
		let id           = $(obj).data('id');
		let tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['essaAcaoSeraIrreversivel'],
			text: l['desejaContinuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['sim!'],
			cancelButtonText: l['nao']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if(is_empty(ret, 1)){
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
				);
					toggleLoading();
					return;
				}

				swal(
					l['sucesso!'],
					l['operaçãoEfetuadaComSucesso!'],
					"success"
				);

				tableDataTable.draw();
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

function acaoAprovar() {
	$('.aprovar').unbind('click');
	$('.aprovar').on("click", function (e) {
		e.preventDefault();
		let obj = $(this);
		let url = $(this).data('url');
		let id = $(this).data('id');

		let tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['essaAcaoSeraIrreversivel'],
			text: l['desejaContinuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['sim!'],
			cancelButtonText: l['nao']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {id: id}, function (ret) {
				if(is_empty(ret, 1)){
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
				);
					toggleLoading();
					return;
				}

				swal(
					l['sucesso!'],
					l['operaçãoEfetuadaComSucesso!'],
					"success"
			).catch(swal.noop);
				tableDataTable.draw();
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

function excluirReg() {
	$('.excluirReg').unbind('click');
	$('.excluirReg').on("click", function (e) {
		e.preventDefault();
		let obj            = $(this);
		let url            = $(obj).data('url');
		let id             = $(obj).data('id');
		let tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['essaAcaoSeraIrreversivel'],
			text: l['desejaContinuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['sim!'],
			cancelButtonText: l['nao']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if(is_empty(ret, 1)){
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
				);
					toggleLoading();
					return;
				}

				swal(
					l['sucesso!'],
					l['operaçãoEfetuadaComSucesso!'],
					"success"
			);
				tableDataTable.draw();
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

function uploadReg() {
	$('.uploadERP').unbind('click');
	$('.uploadERP').on("click", function (e) {
		e.preventDefault();
		let obj            = $(this);
		let url            = $(obj).data('url');
		let id             = $(obj).data('id');
		let tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['essaAcaoSeraIrreversivel'],
			text: l['desejaContinuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['sim!'],
			cancelButtonText: l['nao']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if(is_empty(ret, 1)){
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
				);
					toggleLoading();
					return;
				}

				swal(
					l['sucesso!'],
					l['operaçãoEfetuadaComSucesso!'],
					"success"
				);

				tableDataTable.draw();
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

/**
 * Função para geração e download do relatório
 */
function registrosExcel(){
	// ao clicar, busca e imprime o relatório com as solicitações
	$('.gerarRelatorio').off('click');
	$('.gerarRelatorio').on('click', function (){
		let obj = {
			'dataInicial': $('#dataInicial').val(),
			'dataFinal': $('#dataFinal').val(),
			'statusSolicitacaoCompra': $('#pesquisa-status').val()
		}

		// disponibiliza o arquivo de relatório retornado
		$.redirect($(".data-views").data("url_gerar_relatorio"), obj, "GET", "_blank");
	});

	// ao clicar, busca e imprime o relatório com as solicitações
	$('.gerarRelatorioDetalhado').off('click');
	$('.gerarRelatorioDetalhado').on('click', function (){
		let obj = {
			'dataInicial': $('#dataInicial').val(),
			'dataFinal': $('#dataFinal').val(),
			'statusSolicitacaoCompra': $('#pesquisa-status').val()
		}

		// disponibiliza o arquivo de relatório retornado
		$.redirect($(".data-views").data("url_gerar_relatorio_detalhado"), obj, "GET", "_blank");
	});
}

/**
 * Função para realizar a busca com as datas inicial e final
 */

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";
		const ref_btn_relatorio = "#gerarRelatorio";
		const ref_btn_relatorio_detalhado = "#gerarRelatorioDetalhado";

		let dataInicial = $("#dataInicial");
		let dataFinal = $("#dataFinal");
		let statusSolicitacaoCompra = $("#pesquisa-status");
		let url_table = "";
		let gets_url = "";
		let dataTable = null;

		if(!is_empty($(dataInicial).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "dataInicial=" + $(dataInicial).val();
		}

		if(!is_empty($(dataFinal).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "dataFinal=" + $(dataFinal).val();
		}

		if(!is_empty($(statusSolicitacaoCompra).val(), 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "statusSolicitacaoCompra=" + $(statusSolicitacaoCompra).val();
		}

		// verifica se os campos 'data de' e 'data para'. Se estiver vazio, exibe uma msgm
		if(is_empty($(dataInicial).val(), 1) || is_empty($(dataFinal).val(), 1)) {
			swal(
				l['erro!'],
				l['camposObrigatorios'] + ": " + l['dataCriacaoInicialEDataCriacaoFinal'],
				"error"
			);
			return;
		}

		// verifica se o campo 'data de' é maior que o 'data para'. Se for, exibe uma msgm
		if(strFormatDate($(dataInicial).val(), configLocation.formatDatePicker) > strFormatDate($(dataFinal).val(), configLocation.formatDatePicker)) {
			swal(
				l['erro!'],
				l['dataCriacaoInicialDeveSerMenorQueDataCriacaoFinal'],
				"error"
			);
			return;
		}

		// verifica se a diferença entre as datas é maior que 180 dias. Se for, exibe uma msgm
		const dataInicialcmp = strFormatDate($(dataInicial).val(), configLocation.formatDatePicker);
		const dataFinalcmp = strFormatDate($(dataFinal).val(), configLocation.formatDatePicker);
		const diferencaEmDias = diffDays(dataInicialcmp, dataFinalcmp);
		const maxDiferencaDias = 181; // 181 pois a diferença é entre as 00:00:00 da data inicial até 23:59:59 da data final

		if((!is_empty(diferencaEmDias)) && (diferencaEmDias > maxDiferencaDias)) {
			swal(
				l['erro!'],
				l['diferencaMaxima180Dias'],
				"error"
			);
			return;
		}

		$(ref_table_search).each(function (){
			if($.fn.DataTable.isDataTable(this)) {
				dataTable = $(this).DataTable();
				dataTable.clear();
				dataTable.destroy();
			}
		});
		url_table = $(ref_table_search).data("url_principal");
		url_relatorio = $(".data-views").data("url_gerar_relatorio");
		url_relatorio_detalhado = $(".data-views").data("url_gerar_relatorio_detalhado");

		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
			url_relatorio += "?" + gets_url;
			url_relatorio_detalhado += "?" + gets_url;
		}
		gets_url = null;
		$(ref_btn_relatorio).attr("href", url_relatorio);
		$(ref_btn_relatorio_detalhado).attr("href", url_relatorio_detalhado);
		$(ref_table_search).data("url_ajax", url_table);
		allTables();
	}

	$("select#pesquisa-status").off("select2:select");
	$("select#pesquisa-status").on("select2:select", function () {
		__acaoAtualizaDataTable();
	});

	$("select#pesquisa-status").off("select2:unselect");
	$("select#pesquisa-status").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__acaoAtualizaDataTable();
	});

	// ao clica no botao deverá fazer a busca
	$('#search-table').off("click");
	$('#search-table').on("click",function(e){
		__acaoAtualizaDataTable();
	});

}

function criaCostumizacoes() {
	$("select#pesquisa-status").select2Simple();
}

/**
 * Função para a ação de duplicar registros pelo datatable
 */
function duplicarReg() {
	$('.duplicar-reg').unbind('click');
	$('.duplicar-reg').on("click", function (e) {
		e.preventDefault();
		let obj            = $(this);
		let url            = $(obj).data('url');
		let id             = $(obj).data('id');
		let tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l['essaAcaoSeraIrreversivel'],
			text: l['desejaContinuar?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['sim!'],
			cancelButtonText: l['nao']
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function (ret) {
				if(is_empty(ret, 1)){
					swal(
						l['erro'],
						l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
						"error"
					);
					toggleLoading();
					return;
				}

				swal(
					l['sucesso!'],
					l['operaçãoEfetuadaComSucesso!'],
					"success"
				);
				tableDataTable.draw();
				toggleLoading();
			});
		}).catch(swal.noop);
	});
}

function allFunctionsSolicitacao(){
	showModalRejeitar();
	showModalMotivo();
	uploadParaAprovacao();
	acaoAprovar();
	excluirReg();
	uploadReg();
	duplicarReg();
	registrosExcel();
	pesquisaPersonalizada();
	criaCostumizacoes();
}

allFunctionsSolicitacao();
$("#search-table").trigger("click");