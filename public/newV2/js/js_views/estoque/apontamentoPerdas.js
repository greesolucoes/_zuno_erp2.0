controlaTabelaSuite();
$("#select_observacoesId").select2Ajax();

function controlaLote() {
	let trAddLote = null;

	$('button.lote').off('click');
	$('button.lote').on("click", function () {
		trAddLote = $($(this).parents("tr"));

		let lotes = JSON.parse($($(trAddLote).find(".lotes_json")).val());
		$($($('#cadastro_lote-tabela tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");
		$.each(lotes, function (idx, lote) {
			$('#cadastro_lote-tabela tfoot .add-itens-table-geral').trigger("click");
			$($('#cadastro_lote-tabela tbody tr:last').find(".lotenome")).val(lote.nome);
			$($('#cadastro_lote-tabela tbody tr:last').find(".lotequantidade")).val(lote.quantidade);
			$($('#cadastro_lote-tabela tbody tr:last').find(".lotedata")).val(lote.vencimento);
		});
		lotes = null;

		$('.modal-lote').modal('toggle');
	});

	$('.modal-lote button.btn-salvar-lote_itens').off('click');
	$('.modal-lote button.btn-salvar-lote_itens').on("click", function () {
		let lotes = [];
		$("#cadastro_lote-tabela tbody tr").each(function () {
			let obj = $(this);
			if($(obj).find(".is_fake-no_post").length === 0 || !is_empty($(obj).find(".is_fake-no_post").val(), 1)) {
				return;
			}

			lotes.push({
				"nome": $(obj).find(".lotenome").val(),
				"quantidade": $(obj).find(".lotequantidade").val(),
				"vencimento": $(obj).find(".lotedata").val(),
			});
		});

		if(trAddLote !== null && trAddLote.length === 1) {
			$($(trAddLote).find(".lotes_json")).val(JSON.stringify(lotes));
		}

		trAddLote = null;
		$('.modal-lote').modal('toggle');
	});
}

controlaTabelaSuite({
	"ref": "#cadastro_lote-tabela",
	"funAposAddItem": function () {
	}
});
controlaLote();
allFunctions();