function moeda(a, e, r, t) {
	let n = ""
		, h = j = 0
		, u = tamanho2 = 0
		, l = ajd2 = ""
		, o = window.Event ? t.which : t.keyCode;
	if (13 == o || 8 == o)
		return !0;
	if (n = String.fromCharCode(o),
	-1 == "0123456789".indexOf(n))
		return !1;
	for (u = a.value.length,
			 h = 0; h < u && ("0" == a.value.charAt(h) || a.value.charAt(h) == r); h++)
		;
	for (l = ""; h < u; h++)
		-1 != "0123456789".indexOf(a.value.charAt(h)) && (l += a.value.charAt(h));
	if (l += n,
	0 == (u = l.length) && (a.value = ""),
	1 == u && (a.value = "0" + r + "0" + l),
	2 == u && (a.value = "0" + r + l),
	u > 2) {
		for (ajd2 = "",
				 j = 0,
				 h = u - 3; h >= 0; h--)
			3 == j && (ajd2 += e,
				j = 0),
				ajd2 += l.charAt(h),
				j++;
		for (a.value = "",
				 tamanho2 = ajd2.length,
				 h = tamanho2 - 1; h >= 0; h--)
			a.value += ajd2.charAt(h);
		a.value += r + l.substr(u - 2, u)
	}
	return !1
}

function criaSelects() {
	$(".select_observacoesId").select2Ajax();
	$(".select_observacoesId").data('init', '');

	$(".select_Tipo").select2Simple();
	$(".select_Tipo").data('init', '');

	$(".select_depositoIdOrigem").select2Simple();
	$(".select_depositoIdOrigem").data('init', '');

	$(".select_depositoIdOrigemModel").select2Simple();
	$(".select_depositoIdOrigemModel").data('init', '');

	$(".select_depositoIdOrigemItem").select2Simple();
	$(".select_depositoIdOrigemItem").data('init', '');
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
			let selectLinha = $('.select_depositoIdOrigemItem').select2();
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
			let selectLinha = $('.select_depositoIdOrigemItem').select2();
			selectLinha.val(itemVal).trigger("change");
		}).catch(swal.noop);
	}
});

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

controlaTabelaSuite({
	"ref": "#cadastro_lote-tabela",
	"funAposAddItem": function () {
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

							let quantidadeOriginal = (!is_empty(selectorQuantidade.val(), 1) ? formataDecimal(selectorQuantidade.val(), ",", ".", "", "", true, $('#casasDecimais').val()) : 0)
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