var control = false
function controlaLote() {
	let trAddLote = null;

	$('button.lote').off('click');
	$('button.lote').on("click", function () {
		trAddLote = $($(this).parents("tr"));

		let lotes = JSON.parse($($(trAddLote).find(".lotes_json")).val());
		$($($('#cadastro_lote-tabela tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");

		if($('#cadastro_lote-tabela tbody tr .idlote').hasClass("select2-hidden-accessible")) {
			$('#cadastro_lote-tabela tbody tr .idlote').select2('destroy');
		}

		$('#cadastro_lote-tabela tbody tr .idlote option').remove();
		$('#cadastro_lote-tabela tbody tr .idlote').data("url", $('#cadastro_lote-tabela tbody tr .idlote').data("url_principal") + (!is_empty($($(trAddLote).find(".idproduto")).val(), 1) ? $($(trAddLote).find(".idproduto")).val() : ""));
		$('#cadastro_lote-tabela tbody tr .idlote').attr("data-url", $('#cadastro_lote-tabela tbody tr .idlote').data("url_principal") + (!is_empty($($(trAddLote).find(".idproduto")).val(), 1) ? $($(trAddLote).find(".idproduto")).val() : ""));
		$('#cadastro_lote-tabela tbody tr .idlote').data("init", '');
		$('#cadastro_lote-tabela tbody tr .idlote').attr("data-init", '');
		$('#cadastro_lote-tabela tbody tr .idlote').select2Ajax();

		if(!control) {
			if(typeof($($(trAddLote).find(".id_item_saida_insumo")).val()) != "undefined" && trAddLote !== null){
				let idItemSaidaInsumo = JSON.parse($($(trAddLote).find(".id_item_saida_insumo")).val());
				$.each(JSON.parse($('.lotes').text()), function (idx, loteArray) {
					$.each(this, function (key, lote) {
						if(lote.idItensSaidaInsumos == idItemSaidaInsumo){
							$('#cadastro_lote-tabela tfoot .add-itens-table-geral').trigger("click");
							$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).select2('destroy');
							$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).data('init', JSON.parse('{"id":"' + lote.idLote + '","text":"' + lote.lote + '"}'));
							$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).select2Ajax();
							$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).data("init", "");


							$($('#cadastro_lote-tabela tbody tr:last').find(".lotequantidade")).val(formataDecimal(lote.quantidadeLote, ".", ",", ".", "", true, $('#casasDecimais').val())).trigger("keyup");
							$($('#cadastro_lote-tabela tbody tr:last').find(".lotedata")).val(lote.dataVencimento);
							$($('#cadastro_lote-tabela tbody tr:last').find(".lotedatafabricacao")).val(lote.dataFabricacao);
						}
					});
				});
			}
		}

		$.each(lotes, function (idx, lote) {
			$('#cadastro_lote-tabela tfoot .add-itens-table-geral').trigger("click");
			$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).select2('destroy');
			$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).data("init", lote.lote);
			console.log(lote.lote);
			$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).select2Ajax();
			$($($('#cadastro_lote-tabela tbody tr:last').find(".idlote"))).data("init", "");

			$($('#cadastro_lote-tabela tbody tr:last').find(".lotequantidade")).val(lote.quantidade);
			$($('#cadastro_lote-tabela tbody tr:last').find(".lotedata")).val(lote.vencimento);
			$($('#cadastro_lote-tabela tbody tr:last').find(".lotedatafabricacao")).val(lote.fabricacao);
		});
		lotes = null;

		$('.modal-lote').modal('toggle');
	});

	$('.modal-lote button.btn-salvar-lote_itens').off('click');
	$('.modal-lote button.btn-salvar-lote_itens').on("click", function () {
		let lotes = [];
		$("#cadastro_lote-tabela tbody tr").each(function () {
			let obj = $(this);
			if ($(obj).find(".idlote").val() != '' && $(obj).find(".idlote").val() != null) {
				lotes.push({
					"lote": {
						'id': $(obj).find(".idlote").val(),
						'text': $($(obj).find(".idlote")).find("option:selected").text(),
					},
					"quantidade": $(obj).find(".lotequantidade").val(),
					"vencimento": $(obj).find(".lotedata").val(),
					"fabricacao": $(obj).find(".lotedatafabricacao").val(),
				});
			}
		});

		if(trAddLote !== null && trAddLote.length === 1) {
			$($(trAddLote).find(".lotes_json")).val(JSON.stringify(lotes));
		}
		control = true;
		trAddLote = null;
		$('.modal-lote').modal('toggle');
	});
}

function converteFloatMoeda(valor){
	var inteiro = null, c = null, j = null;
	casas = $('#casasDecimais').val();
	decimal = $('#casasDecimais').val();
	var aux = new Array();
	valor = ""+valor;
	c = valor.indexOf(".",0);
	//encontrou o ponto na string
	if(c > 0){
		//separa as partes em inteiro e decimal
		inteiro = valor.substring(0,c);
		decimal = valor.substring(c+1,valor.length);
		decimal = decimal.padEnd(casas.length, '0');
	}else{
		inteiro = valor;
	}

	//pega a parte inteiro de 3 em 3 partes
	for (j = inteiro.length, c = 0; j > 0; j-=3, c++){
		aux[c]=inteiro.substring(j-3,j);
	}

	//percorre a string acrescentando os pontos
	inteiro = "";
	for(c = aux.length-1; c >= 0; c--){
		inteiro += aux[c]+'.';
	}
	//retirando o ultimo ponto e finalizando a parte inteiro

	inteiro = inteiro.substring(0,inteiro.length-1);

	//decimal = parseInt(decimal);
	if (isNaN(decimal)) {
		decimal = "00";
	} else {
		decimal = ""+decimal;
		if(decimal.length === 1){
			decimal = decimal+"0";
		}
	}

	valor = inteiro + "," + decimal;
	return valor;
}

function converteMoedaFloat(valor){
	if (valor === "") {
		valor =  1;
	} else {
		if (valor.search(/\./) >= 1) {
			valor = valor.replace(".","");
		}
		if (valor.search(/\,/) >= 1) {
			valor = valor.replace(",",".");
		}
		valor = parseFloat(valor);
	}
	if (isNaN(valor)) {
		valor = 1
	}
	return valor;
}

function setaLote(element) {
	let loteSelecionado = $('option:selected', element).val();
	let thisTr = $(element).parents('tr');
	let url = $(element).data("url");

	forceToggleLoading(1);
	ajaxRequest(true, url, null, 'text', {'lote': loteSelecionado}, function(ret){
		ret = $.parseJSON(ret);
		if(!is_empty(ret, 1) && !is_empty(ret[0], 1)) {
			$(thisTr).find('input.lotedatafabricacao').val(ret[0].dataFabricacao);
			$(thisTr).find('input.lotedata').val(ret[0].dataVencimento);
		}
	});

	forceToggleLoading(0);
	$(this).data('travaselecao', 0);
}

controlaTabelaSuite({
	"ref": "#cadastro_lote-tabela",
	"funAposAddItem": function () {
	}
});

function criaSelects() {
	$(".select_depositoIdOrigem").select2Simple();
	$(".select_depositoIdOrigem").data('init', '');

	$(".select_depositoIdOrigemModel").select2Simple();
	$(".select_depositoIdOrigemModel").data('init', '');

	$(".select_depositoProdutoEstrutura").select2Simple();
	$(".select_depositoProdutoEstrutura").data('init', '');
}

// funcao para trocar automaticamente as origens da perda nos itens
$(".select_depositoIdOrigemModel").on('change', function () {

	let itemVal = $("option:selected", this).val();
	let item = $("option:selected", this).html();

	if(is_empty($(this).val())){
		swal({
			title: "Você irá remover todos os Depósitos das linhas abaixo!",
			text: l["desejaContinuar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["sim!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			// limpa todos as origens da perda
			let selectLinha = $('.select_depositoIdOrigem').select2();
			selectLinha.val(null).trigger("change");
		}).catch(swal.noop);
	}else{
		swal({
			title: "Você irá atribuir " + item + " para todas as linhas abaixo!",
			text: l["desejaContinuar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["sim!"],
			cancelButtonText: l["cancelar!"]
		}).then(function () {
			// atribui o mesmo para todos os itens das linhas
			let selectLinha = $('.select_depositoIdOrigem').select2();
			selectLinha.val(itemVal).trigger("change");
		}).catch(swal.noop);
	}
});

function addQuantidadeBipagem(){
	let codigoBarras = $('#codigoBarras') ?? null;

	if(is_empty(codigoBarras.val(), 1)){
		swal(
			"Codigo de barras",
			"Código de barras vazio",
			"error"
		).catch(swal.noop);
	} else {
		codigoBarras.attr('readonly', true);
		ajaxRequest(
			true,
			$('.data_views').data('url_codigo_barra_produto'),
			null,
			'text',
			{
				'codigoBarras': codigoBarras.val(),
			},
			function (ret) {
				try{
					consoleProduction(ret);
					ret = JSON.parse(ret);

					$('.produto_modal').val('');
					$('.valor_modal').val('');
					$('.quantidade_modal').val('');

					if(ret['class'] == 'error'){
						swal(
							ret['titulo'],
							ret['text'],
							ret['class']
						).catch(swal.noop);
					}else{
						let selectorQuantidade = $('select[name="produtos[]"] option[value="'+ ret['idProdutos'] +'"]').parents('tr').find('input.quantidadePlanejada:not([readonly])');
						if(typeof selectorQuantidade.val() != 'undefined'){
							codigoBarras.val("");

							$('.produto_modal').val(ret['codigoProdutos'] + ' - ' + ret['nomeProdutos']);
							$('.valor_modal').val(ret['valor']);
							$('.quantidade_modal').val(formataDecimal(ret['quantidade'], ".", ",", ".", "", true, $('#casasDecimais').val()));

							let quantidadeOriginal = (!is_empty(selectorQuantidade.val(), 1) ? selectorQuantidade.val() : 0)
							selectorQuantidade.val(formataDecimal(parseFloat(quantidadeOriginal) + parseFloat(ret['quantidade']), ".", ",", ".", "", true, $('#casasDecimais').val()));
						}else{
							swal(
								l["erro!"],
								l['itemNaoEncontradoNoLancamentoDeProdutoAcabado'],
								"error"
							);
						}
					}
				}catch(err){
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
				codigoBarras.attr('readonly', false);
			}
		);
	}
}

criaSelects();
controlaLote();
allFunctions();

controlaTabelaSuite({
	"ref": "#conteudoTable",
	"funAposAddItem": function () {
	}
});