let optsCriaTabelaItens = {
	"ref": "#conteudo-itens-tabela"
}

function criaCostumizacoes() {
	let __functionSearchCards = function (){
		if(!is_empty($('.data_views').data('travar_campos'), 1) || !is_empty($('.data_views').data('visualizar'), 1)) {
			return;
		}

		let url = $('.data_views').data('url_search')
		let data_deposito = $("#cabecalho-data_recebimento").val();
		let busca_inicial = $("#busca-data_inicial").val();
		let busca_final = $("#busca-data_final").val();
		let conta_banco = $("#cabecalho-conta_banco").val();
		let filial = $("#cabecalho-filial").val();
		if(
			is_empty(busca_inicial, 1) || is_empty(conta_banco, 1) ||
			is_empty(filial, 1) || is_empty(data_deposito, 1)
		) {
			$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").remove();
			return;
		}

		data_deposito = moment(data_deposito, $('.data_views').data('format_date')).format('YYYY-MM-DD');
		busca_inicial = moment(busca_inicial, $('.data_views').data('format_date')).format('YYYY-MM-DD');
		if(!is_empty(busca_final, 1)) {
			busca_final = moment(busca_final, $('.data_views').data('format_date')).format('YYYY-MM-DD');
		} else {
			busca_final = null;
		}

		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{
				'data_deposito': data_deposito,
				'busca_inicial': busca_inicial,
				'busca_final': busca_final,
				'conta_banco': conta_banco,
				'filial': filial,
			}, function (ret) {
				$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").remove();

				try{
					ret = JSON.parse(ret);

					if(!is_empty(ret['bol'], 1)){
						let tbody = $("table#conteudo-itens-tabela tbody");

						$.each(ret['cards'], function (indexCartao, valoresCartoes) {
							let modelo = $(tbody).find("tr").first().html();
							let refTr = null;

							$(tbody).append('<tr>' + modelo + '</tr>');
							refTr = $($(tbody).find('tr').last());

							let idItens = "";
							$.each(valoresCartoes['infos'], function (indexInfoCartao, valoresInfoCartoes) {
								if(!is_empty(idItens, 1)) {
									idItens += ";";
								}
								idItens += valoresInfoCartoes['idItem'];
							});

							$(refTr).find(".table-id_itens").val(idItens);
							$(refTr).find(".table-cartao").text(valoresCartoes['idSapCartao'] + " - " + valoresCartoes['descricaoCartao']);
							$(refTr).find(".table-dt_vencimento").text(valoresCartoes['dataPagamentoExtratoItemText']);
							$(refTr).find(".table-valor_bruto").text(valoresCartoes['valorBrutoItemFormatado']);
							$(refTr).find(".table-valor_taxa").text(valoresCartoes['valorTaxaItemFormatado']);
							$(refTr).find(".table-valor_liquido").text(valoresCartoes['valorLiquidoItemFormatado']);
							$(refTr).find(".table-valor_conciliado").text(valoresCartoes['valorConciliadoItemFormatado']);
							$(refTr).find(".table-valor_conciliado").data("valor", valoresCartoes['valorConciliadoItem']);
							$(refTr).find(".table-divergencia").text(valoresCartoes['valorDivergenciaItemFormatado']);
						});
						controlaTabelaSuite(optsCriaTabelaItens);
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
			});
	};

	$('.select_ajax').select2Ajax();
	$('.select_ajax').data('init', '');

	$("select#cabecalho-conta_banco").off("select2:unselect");
	$("select#cabecalho-conta_banco").on("select2:unselect", function () {
		if(!is_empty($(this).val(), 1)) {
			$(this).append($('<option/>').attr('value', "").text("")).val("").trigger('change').trigger('select2:unselect');
			return;
		}
		__functionSearchCards();
	});

	$("select#cabecalho-conta_banco").off("select2:select");
	$("select#cabecalho-conta_banco").on("select2:select", function () {
		__functionSearchCards();
	});

	$(".busca-datas").datetimepicker({
		locale: _lang,
		useCurrent: false,
		format: $('.data_views').data('format_date'),
		widgetPositioning: {
			vertical: 'top',
			horizontal: 'left'
		}
	}).on('dp.change', function(e){
		__functionSearchCards();
	});
}

function saveForm() {
	$(".salvar_ajax").off("click");
	$(".salvar_ajax").on("click", function () {
		if(!is_empty($('.data_views').data('visualizar'), 1)) {
			return;
		}

		let save = {};
		let url = $("form.cadastro").attr("action");
		if(is_empty(url, 1)) {
			return;
		}

		save['idCabecalho'] = !is_empty($("#cabecalho-id_registro").val(), 1) ? $("#cabecalho-id_registro").val() : "";
		save['dataRecebimento'] = moment($("#cabecalho-data_recebimento").val(), $('.data_views').data('format_date')).format('YYYY-MM-DD');
		save['contaBanco'] = $("#cabecalho-conta_banco").val();
		save['filial'] = $("#cabecalho-filial").val();
		save['dataInicialSearch'] = moment($("#busca-data_inicial").val(), $('.data_views').data('format_date')).format('YYYY-MM-DD');
		save['dataFinalSearch'] = moment($("#busca-data_final").val(), $('.data_views').data('format_date')).format('YYYY-MM-DD');

		save['itens'] = [];
		$("table#conteudo-itens-tabela tbody tr:not(.ocultar)").each(function () {
			let item = {};
			item['idItens'] = $(this).find(".table-id_itens").val();
			item['valorConciliado'] = $(this).find(".table-valor_conciliado").data("valor");

			save['itens'].push(item);
		});

		toggleLoading();
		ajaxRequest(
			true,
			url,
			null,
			'text',
			{'save': save},
			function (ret) {
				try{
					ret = JSON.parse(ret);

					if(!is_empty(ret['bol'], 1)){
						location.reload();
					} else {
						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);
						toggleLoading();
					}
				}catch(err){
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			});
	});
}

criaCostumizacoes();
controlaTabelaSuite(optsCriaTabelaItens);
saveForm();