function acaoDeletar() {
    $(".deletar").unbind("click");
    $(".deletar").on("click", function (e) {
		disselecionaLinha(this);
        e.preventDefault();
        var obj = $(this);
        var url = $(obj).data("url");
        var id = $(obj).data("id");
        var tableDataTable = $(this).parents(".table-exibe").DataTable();

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
            ajaxRequest(true, url, null, 'text', {"idRegistro": id}, function (ret) {
                ret = JSON.parse(ret);

                swal(
                    ret["titulo"],
                    ret["text"],
                    ret["class"]
                ).catch(swal.noop);
                if(!is_empty(ret["bol"], 1)) {
                    tableDataTable.draw();
					deParaSelecionados.splice($.inArray(id, deParaSelecionados) ,1 );
                    $('table#relatorio-de-para_table tbody tr[data-id_registro="' + id + '"]').remove();
                }

                toggleLoading();
            });
        }).catch(swal.noop);
    });
}

function downloadDePara(){
    $('button.printar-valores').unbind('click');
    $('button.printar-valores').on('click', function() {
        var titulo = $(this).attr('title');

        save2excel(
			$('table#relatorio-de-para_table'),
			{
				not: null,
				name: titulo,
				filename: (titulo + '.xls')
			},
			{
				permiteZerosEsquerda: true
			}
		);
    });
}

function disselecionaLinha(value){
	$('input[data-id="' + $(value).data('id') + '"]').parents('tr').trigger('click');
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
				'idRegistro': deParaSelecionados,
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
acaoDeletar();