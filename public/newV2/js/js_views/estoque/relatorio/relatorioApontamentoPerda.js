/**
 * cria select filiais e esconde a div de busca de filial quando checar todas as filias
 * cria select status e esconde a div de busca de status quando checar todos os status
 */
function criaObjetos() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');
	$('label.label-custom').on("click",function(){
		$("label.label-custom-selected").removeClass("label-custom-selected")
		$(this).addClass("label-custom-selected");
	});

	$("select#status").select2Simple();

	$(".lbl-todas-filiais").off("click");
	$(".lbl-todas-filiais").on("click", function () {
		$("#filiais-div").toggle();
	});

	$(".lbl-todos-status").off("click");
	$(".lbl-todos-status").on("click", function () {
		$("#status-div").toggle();
	});
}

function acoesBotoes(){
	// botao que busca uma listagem dos erros pra criar um botao pra gerar o arquivo XLSX
	$("#btnListarErros").off("click");
	$('#btnListarErros').on('click', function (){

		let dt_Inicio = $('#dt_inicio').val();
		dt_Inicio = new Date(dt_Inicio.split('/').reverse().join('/'));

		let dt_Fim = $('#dt_fim').val();
		dt_Fim = new Date(dt_Fim.split('/').reverse().join('/'));

		if(dt_Inicio > dt_Fim) {
			swal({
				title: l["atenção!"],
				text: l["dataDeDeveSerMenorQueDataPara"],
				type: "warning",
				showCancelButton: false,
				confirmButtonColor: '#3085d6'
			});
		} else {
			// checa se não marcar TODAS AS FILIAIS deve ao menos escolher 1 na caixa de selecao
			if($('#todas-filiais').prop('checked') == false && $('#filiais').val().length <= 0){
				swal({
					title: l["atenção!"],
					text: l["umaFilialDeveSerSelecionada!"],
					type: "warning",
					showCancelButton: false,
					confirmButtonColor: '#3085d6'
				});
			}else {
				if($('#todos-status').prop('checked') == false && $('#status').val().length <= 0){
					swal({
						title: l["atenção!"],
						text: l["umStatusDeveSerSelecionado!"],
						type: "warning",
						showCancelButton: false,
						confirmButtonColor: '#3085d6'
					});
				}else{
					toggleLoading();

					ajaxRequest(
						true,
						$('.data_views').data('url_erros_apontamento_ajax'),
						null,
						'text',
						{
							'dt_inicio': $('#dt_inicio').val(),
							'dt_fim': $('#dt_fim').val(),
							'filiais': $('#filiais').val(),
							'todas-filiais': $('#todas-filiais').prop('checked'),
							'status': $('#status').val(),
							'todos-status': $('#todos-status').prop('checked')
						},
						function (ret) {
							if (!is_empty(ret, true)) {
								$('div.errosApontamento div.retornoAjax').html(ret);

								// abrir o modal
								$("#modalErrors").modal('toggle');

							} else {
								swal({
									title: l["atenção!"],
									text: l["nenhumRegistroEncontrado"],
									type: "warning",
									showCancelButton: false,
									confirmButtonColor: '#3085d6'
								});
							}
							toggleLoading();
						});
				}
			}
		}

	});
}

criaObjetos();
acoesBotoes();