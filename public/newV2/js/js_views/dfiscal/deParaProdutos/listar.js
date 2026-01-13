function acoesBotoes() {
	let __acaoPadrao = function (obj, objEnvio, funOnRequest) {
		let id = $(obj).data('id');
		let url = $(obj).data('url');
		let tableDataTable = $('.table-exibe').DataTable();
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
					if(funOnRequest != null) {
						funOnRequest(id, ret);
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
	}

    $(".deletar").off("click");
    $(".deletar").on("click", function (e) {
		disselecionaLinha(this);
		__acaoPadrao($(this), null, function (id, ret) {
			if(!is_empty(ret["bol"], 1)) {
				deParaSelecionados.splice($.inArray(id, deParaSelecionados) ,1 );
				$('table#relatorio-de-para_table tbody tr[data-linha="' + id + '"]').remove();
			}
		});
    });

    $(".upload").off("click");
    $(".upload").on("click", function (e) {
		__acaoPadrao($(this));
    });

    $(".upload_all").off("click");
    $(".upload_all").on("click", function (e) {
		__acaoPadrao($(this));
    });

	$('.show_modal_motivo').off('click');
	$('.show_modal_motivo').on('click', function (e) {
		$('.modal_motivo #label_motivo').html($(this).attr("title"));
		$('.modal_motivo .descricao_motivo').html($(this).parents('td').find('.descricao_rejeicao').html());
		$('.modal_motivo').modal('toggle');
	});
}

function disselecionaLinha(value){
	$('input[data-id="' + $(value).data('id') + '"]').parents('tr').trigger('click');
}

function downloadDePara(){
    $('button.printar-valores').unbind('click');
    $('button.printar-valores').on('click', function() {
        var titulo = $(this).attr('title');

        save2excel($('table#relatorio-de-para_table'), {
            not: null,
            name: titulo,
            filename: (titulo + '.xls')
        }, {permiteZerosEsquerda: true});
    });
}

let deParaSelecionados = [];
const table = 'table.table-exibe';

$(table).on('draw.dt', function () {
	$(deParaSelecionados).each(function (idx, value){
		$('input[data-id="' + value + '"]').parents('tr').toggleClass('selected');
	});

	$(table + ' tbody').off('click');
	$(table + ' tbody').on('click', 'tr', function () {
		if(!$(this).hasClass('selected')){
			deParaSelecionados.push($(this).find('input').data('id'));
		} else {
			deParaSelecionados.splice($.inArray($(this).find('input').data('id'), deParaSelecionados) ,1 );
		}
		$(this).toggleClass('selected');

		if(deParaSelecionados.length > 0){
			$('#excluirDeParaLote').addClass('botaoGerarAtivo');
		}else{
			$('#excluirDeParaLote').removeClass('botaoGerarAtivo');
		}
	});
});

$('#excluirDeParaLote').off('click');
$('#excluirDeParaLote').on('click', function (){
	let url = $('.datas_views').data('url_exclusao_lote');
	let tableDataTable = $('.table-exibe').DataTable();

	swal({
		title: l["desejaContinuar?"],
		text: "",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: l["sim!"],
		cancelButtonText: l["cancelar!"]
	}).then(function () {
		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{
				'limit': deParaSelecionados.length,
				'id': deParaSelecionados.join("','"),
			},
			function (ret) {
				try{
					ret = JSON.parse(ret);
					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
					tableDataTable.draw();
					deParaSelecionados = [];
					$('#excluirDeParaLote').removeClass('botaoGerarAtivo');
					toggleLoading();
				}catch(err){
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			}
		);
	}).catch(swal.noop);
});

$('.removerTodos').off('click');
$('.removerTodos').on('click', function (){
	let url = $('.datas_views').data('url_exclusao_lote');
	let tableDataTable = $('.table-exibe').DataTable();

	swal({
		title: l["desejaContinuar?"],
		text: l['temCertezaQueDesejaExcluirOsRegistrosOsDadosSeraoPerdidos'],
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: l["sim!"],
		cancelButtonText: l["cancelar!"]
	}).then(function () {
		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{
				'removerTodos': 1,
			},
			function (ret) {
				try{
					ret = JSON.parse(ret);
					swal(
						ret['titulo'],
						ret['text'],
						ret['class']
					).catch(swal.noop);
					tableDataTable.draw();
					deParaSelecionados = [];
					$('#excluirDeParaLote').removeClass('botaoGerarAtivo');
					toggleLoading();
				}catch(err){
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			}
		);
	}).catch(swal.noop);
});

downloadDePara();
acoesBotoes();