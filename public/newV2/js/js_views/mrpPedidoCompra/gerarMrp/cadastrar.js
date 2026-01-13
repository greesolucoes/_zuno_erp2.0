/**
 * Cadastro do MRP
 * @type {*|jQuery|HTMLElement}
 */
let data_views = $(".data_views");
let casasQtd = $(data_views).data("casas_qtd");
let actionChangeValores = "keyup";

function seleciona() {
	$('.table-exibe tbody').on( 'click', 'tr', function () {
		$(this).toggleClass('selected');
	} );
}

function acaoSalvar() {
	$('#btn-salvar').off('click');
	$('.btn-salvar').on("click", function (e) {
		let selecao = $('.table-exibe').DataTable().rows('.selected').data();
		let url     = $("form.cadastro").attr("action");
		let salvar  = {
			nome_mrp: $(".nome_mrp").val(),
			dia_envio: $(".dia_envio").val(),
			de_data: $(".de_data").val(),
			para_data: $(".para_data").val(),
			data_entrega_pedido: $(".data_entrega_pedido").val(),
			parceiro_negocio: $(".parceiro_negocio").val(),
			condicoes_pagamento: $(".condicoes_pagamento").val(),
			//ARRAY DO JSON COM AS INFORMAÇÕES NECESSÁRIAS
			itens: []
		};
		salvar.de_data = moment(salvar.de_data, "DD/MM/YYYY").format('YYYY-MM-DD');
		salvar.para_data = moment(salvar.para_data, "DD/MM/YYYY").format('YYYY-MM-DD');
		salvar.data_entrega_pedido = moment(salvar.data_entrega_pedido, "DD/MM/YYYY").format('YYYY-MM-DD');

		// busca os dados em formato json de cada linha de item
		$.each( $('.table-exibe > tbody > tr.selected > .json-item'), function (index, elem){
			salvar.itens.push(JSON.parse($(this).html()));
		});

		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{
				salvar: salvar
			},
			function (ret) {
				try{
					ret = JSON.parse(ret);
					if(!is_empty(ret['bol'], 1)) {
						swal(
							ret['titulo'],
							ret['text'],
							ret['class'],
							// window.location = ret['url'],
						).catch(swal.noop);

					} else {
						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);
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
			}
		);
	});
}

function criaCostumizacoes() {
	$("select.select-normal").select2Simple();
	$("#de_data").datetimepicker({
		locale: _lang,
		format: 'DD/MM/YYYY',
		useCurrent: false,
		widgetPositioning: {
			vertical: 'top',
			horizontal: 'left',
		}
	}).on('dp.change', function(e){
		if((!is_empty($("#de_data").val()) && !is_empty($("#para_data").val()))){
			validaDate(this);
		}
	});
	$("#para_data").datetimepicker({
		locale: _lang,
		format: 'DD/MM/YYYY',
		useCurrent: false,
		widgetPositioning: {
			vertical: 'top',
			horizontal: 'left',
		}
	}).on('dp.change', function(e){
		if((!is_empty($("#de_data").val()) && !is_empty($("#para_data").val()))){
			validaDate(this);
		}
	});

	var currentDate = moment();
	$("#de_data").data("DateTimePicker").maxDate(currentDate);
	$("#para_data").data("DateTimePicker").maxDate(currentDate);
}

function validaDate(element){
	let result = diffDays(formatDate($("#de_data").val()), formatDate($("#para_data").val()));
	if(result){
		if(!(result % 7 == 0)){
			swal(
				l['listarMrp'],
				l['asDatasNaoSaoMultiplasDe7'],
				"error"
			);
			$(element).val("");
		}
	} else {
		swal(
			l['dataInvalida'],
			l['dataInicialMaiorQueADataFinal'],
			"error"
		);
		$(element).val("");
	}
}

function pesquisaPersonalizada() {
	let __acaoAtualizaDataTable = function (limparTbl) {
		const ref_table_search = ".table-itens";

		let __initFieldsLinha = function () {
			let __initCalculosLinha = function () {
				let __acaoCalculosLinha = function (refTR) {
					let jsonAux = null;
					//Json original que tras sempre quando n ouver conta em tela
					let arrItem = JSON.parse($($(refTR).find(".json-item_original")).text());

					//A porcentagem, esta nula? se estiver, ele usa o json original e volta os valores padroes da query interna
					if(is_empty($($(refTR).find(".porcentagem_adicional-input-item")).val(), 1)){
						$($(refTR).find(".estoque_necessario-item")).text(arrItem.estoqueNecessarioText);
						$($(refTR).find(".necessidade-item")).text(arrItem.necessidadeText);
						$($(refTR).find(".envio_total-item")).text(arrItem.envioTotalText);
					} else { //Se tiver dados, faz os devidos calculos
						arrItem.porcentagemAdicional = formataDecimal($($(refTR).find(".porcentagem_adicional-input-item")).val(), ",", ".", "", "", true, casasQtd) / 100;
						arrItem.seguranca = arrItem.seguranca/100;

						arrItem.estoqueNecessario = (arrItem.mediaSemanas * (1 + arrItem.seguranca) * (1 + arrItem.porcentagemAdicional));
						arrItem.necessidade = (arrItem.estoqueNecessario - arrItem.pesagem);
						//Alteração realizada no dia 01-10-2021
						if(arrItem.necessidade < 0){
							arrItem.necessidade = 0;
						}

						//Checagem de porcao
						arrItem.porcao = !is_empty(arrItem.porcao, 1) ? arrItem.porcao : 1

						//Envio total = arredondaCima(necessidade/porcao) * porcao
						arrItem.envioTotal = (Math.ceil(arrItem.necessidade / arrItem.porcao) * arrItem.porcao);

						// arrItem.segurancaText = $($(refTR).find(".seguranca-input-item")).val();
						arrItem.porcentagemAdicionalText = $($(refTR).find(".porcentagem_adicional-input-item")).val();
						arrItem.estoqueNecessarioText = formataDecimal(arrItem.estoqueNecessario, ".", ",", ".", "", true, casasQtd);
						arrItem.necessidadeText = formataDecimal(arrItem.necessidade, ".", ",", ".", "", true, casasQtd);
						arrItem.envioTotalText = formataDecimal(arrItem.envioTotal, ".", ",", ".", "", true, casasQtd);


						$($(refTR).find(".estoque_necessario-item")).text(arrItem.estoqueNecessarioText);
						$($(refTR).find(".necessidade-item")).text(arrItem.necessidadeText);
						$($(refTR).find(".envio_total-item")).text(arrItem.envioTotalText);
						$($(refTR).find(".json-item")).text(JSON.stringify(arrItem));
					}
				}

				$($(ref_table_search).find(".seguranca-input-item")).off(actionChangeValores);
				$($(ref_table_search).find(".seguranca-input-item")).on(actionChangeValores, function () {
					__acaoCalculosLinha($(this).parents("tr"));
				});

				$($(ref_table_search).find(".porcentagem_adicional-input-item")).off(actionChangeValores);
				$($(ref_table_search).find(".porcentagem_adicional-input-item")).on(actionChangeValores, function () {
					__acaoCalculosLinha($(this).parents("tr"));
				});
			}
			$($(ref_table_search).find("tbody tr").find("input[data-mask='numerov2']")).fnMascaraNumeroV2();
			__initCalculosLinha();
		};
		let diaSemana = $(".dia_envio").val();
		let dataDe = $(".de_data").val();
		let dataPara = $(".para_data").val();
		let url_table = "";
		let gets_url = "";
		let dataTable = null;

		$(ref_table_search).each(function (){
			if($.fn.DataTable.isDataTable(this)) {
				dataTable = $(this).DataTable();
				dataTable.clear();
				dataTable.destroy();
			}
		});
		url_table = $(ref_table_search).data("url_principal");

		if(!is_empty(diaSemana, 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "dia_envio=" + diaSemana;
		}
		if(!is_empty(dataDe, 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "de_data=" + dataDe;
		}if(!is_empty(dataPara, 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "para_data=" + dataPara;
		}
		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}
		gets_url = null;

		if(is_empty(limparTbl, 1)) {
			toggleLoading();
			ajaxRequest(
				true,
				url_table,
				null,
				'text',
				null,
				function (ret) {
					try{
						ret = JSON.parse(ret);
						if (ret['bol'] == 0) {
							// nenhum item encontrado
							swal(
								ret['titulo'],
								ret['text'],
								ret['class']
							).catch(swal.noop);
						}
						$($(ref_table_search).find("tbody")).html(ret['data']);
						__initFieldsLinha();
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
		} else {
			$($(ref_table_search).find("tbody")).html("");
		}
		allTables();
	}

	$("button#buscar").off('click');
	$("button#buscar").on("click", function (){
		__acaoAtualizaDataTable();
	});

	$("#data_final").off("dp.change");
	$("#data_final").on("dp.change", function (e) {
		__acaoAtualizaDataTable(true);
	});

	$("select.dia_envio").off("select2:select");
	$("select.dia_envio").on("select2:select", function () {
		__acaoAtualizaDataTable(true);
	});

	$("select.parceiro_negocio").off("select2:select");
	$("select.parceiro_negocio").on("select2:select", function () {
		__acaoAtualizaDataTable(true);
	});

	$("select.condicoes_pagamento").off("select2:select");
	$("select.condicoes_pagamento").on("select2:select", function () {
		__acaoAtualizaDataTable(true);
	});

	__acaoAtualizaDataTable(true);
}

function criarSelect(){
	$(".dia_envio").select2Ajax();
	$(".dia_envio").data('init', '');
}

criarSelect();
criaCostumizacoes();
pesquisaPersonalizada();
seleciona();
acaoSalvar();