function initFields() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');

	const countRegrasDistribuicao = $('.data_views').data('count_regras_distribuicao');
	if (countRegrasDistribuicao > 0) {
		for (let idx = 1; idx <= countRegrasDistribuicao; idx++) {
			let select = ".select_regra_distribuicao_" + idx;
			$(select).select2Ajax();
			$(select).data('init', '');
		}
	}
}
initFields();

function controlaSelectProduto() {

	let selectItensIdItemConteudo = $("select.conteudo-itens_id_produtos");

	let __funControlaUnidadesMedidas = function (selectUM, objVal) {
		$(selectUM).find('option').remove();
		$(selectUM).append('<option value=""></option>');
		if (objVal !== null && objVal !== '') {
			ajaxRequest(true, $('.data_views').data('url_unidade_medida'), null, 'text', {'produto': objVal}, function (ret) {
				ret = $.parseJSON(ret);
				$.each(ret['medidas'], function (id, value) {
					$(selectUM).append('<option value="' + value.idUnidadesMedidas + '">' + value.nomeUnidadesMedidas + '</option>');
				});
				$(selectUM).find('option[value="' + ret['UMBase'] + '"]').attr("selected", "selected");
				$(selectUM).trigger('change');
			});
		}
	};

	//Função Old de unidade de medida do pedido de transferencia, caso de algum erro no novo, só voltar esse :)
	let __funControlaUnidadesMedidasOld = function (objUnidade, objVal) {
		if(is_empty(objUnidade, 1) || $(objUnidade).length === 0) {
			return;
		}

		let data_views = $("div.data_views");
		if ($(objUnidade).hasClass("select2-hidden-accessible")){
			$(objUnidade).select2('destroy');
		}

		$($(objUnidade).find("option")).remove();
		$(objUnidade).data(
			"url",
			($(data_views).data("url_ajax_unidades") + objVal)
		);

		$(objUnidade).select2Ajax();
	};

	/** ITEM DO CONTEUDO */
	selectItensIdItemConteudo.off("select2:unselect");
	selectItensIdItemConteudo.on("select2:unselect", function () {
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), null);
	});

	selectItensIdItemConteudo.off("select2:select");
	selectItensIdItemConteudo.on("select2:select", function () {
		__funControlaUnidadesMedidas($($(this).parents("tr").find("select.conteudo-itens_unidade")), $(this).val());
	});
	/** FIM ITEM DO CONTEUDO */

}

const formUtils = {
	removeTr: function (elemento) {
		$(elemento).parents('tr').fadeOut(270, function () {
			$(elemento).parents('tr').remove();
		});
	},addItem: function (button, aba) {
		$('button[data-add="' + button + '"]').click(function(e) {
			e.preventDefault();
			let template = $(aba + ' template').html();
			let index = parseInt($(aba + ' tfoot').attr('data-count'));
			let html = template.replaceAll("{{n}}", index);

			$(aba + ' tbody').fadeIn(270, function() {
				$(aba + ' tbody').append(html);
			})
			$(`select[name="itens[${index}][idProdutos]"]`).select2Ajax();
			$(`select[name="itens[${index}][idUnidadesMedidas]"]`).select2Ajax();

			const countRegrasDistribuicao = $('.data_views').data('count_regras_distribuicao');
			if (countRegrasDistribuicao > 0) {
				for (let idx = 1; idx <= countRegrasDistribuicao; idx++) {
					$(`select[name="itens[${index}][idRegraDistribuicao${idx}]"]`).select2Ajax();
				}
			}

			$(aba + ' tfoot').attr('data-count', index + 1);
			controlaSelectProduto();
		})
	},

}

// de-paras disponíveis
// adicionar aqui caso haja outro
const btnsDePara = [
	{ 'btn': 'itens', 'aba': '#itens-aba' },
];

// para cada de-para, cria-se a funcionalidade
btnsDePara.forEach(function(itens) { formUtils.addItem(itens.btn, itens.aba) });


function setEstoqueAndLastSync(produto){
	let idProduto 	= $(produto).val();
	var thisTr 		= $(produto).parents('tr');

	if(!is_empty([idProduto], 1) && !is_empty($('#idDepositoOrigem').val(), 1)){
		ajaxRequest(true, $('.data_views').data('url_ajax_get_estoque'), null, 'text', {
			'produtos': [idProduto],
			'depositoSelecionado': $('#idDepositoOrigem').val()
		}, function (ret) {
			ret = $.parseJSON(ret);
			$.each(ret, function (indRet, valorRet) {
				if (!is_empty(ret, 1)) {
					$(thisTr).find(".estoque_item").val(valorRet['qtdEstoque']);
					$(thisTr).find(".ultima_sync_item").val(valorRet['ultimaSincronizacao']);
				} else {
					$(thisTr).find(".estoque_item").val('0');
					$(thisTr).find(".ultima_sync_item").val('-');
				}
			});
		});
	} else {
		$(thisTr).find(".estoque_item").val('0');
		$(thisTr).find(".ultima_sync_item").val('-');
	}
}

//funcao para ordernar campos
function sortTable() {
	var table, rows, switching, i, x, y, shouldSwitch;
	table = document.getElementById("itensTable");
	switching = true;
	while (switching) {
		switching = false;
		rows = table.rows;
		for (i = 1; i < (rows.length - 1); i++) {
			shouldSwitch = false;

			//ordena por ordem alfabetica o nome do item
			x = rows[i].getElementsByTagName("TD")[0];
			y = rows[i + 1].getElementsByTagName("TD")[0];

			const selectX = x.querySelector(".conteudo-itens_id_produtos");
			const selectY = y.querySelector(".conteudo-itens_id_produtos");

			x = selectY != null && selectX.options[selectX.selectedIndex] != null
				? selectX.options[selectX.selectedIndex].text.split(' - ')[1] !== undefined
					? selectX.options[selectX.selectedIndex].text.split(' - ')[1].toLowerCase()
					: 'zzzzzz' //foi colocado zzzzzz para quando ordernar ordem alfabetica e n tiver texto, ele jogar para ultima posição
				: null;

			y = selectY != null && selectY.options[selectY.selectedIndex] != null
				? selectY.options[selectY.selectedIndex].text.split(' - ')[1] !== undefined
					? selectY.options[selectY.selectedIndex].text.split(' - ')[1].toLowerCase()
					: 'zzzzzz' //foi colocado zzzzzz para quando ordernar ordem alfabetica e n tiver texto, ele jogar para ultima posição
				: null;

			if ((x > y) && (!is_empty(x) || !is_empty(y))) {
				shouldSwitch = true;
				break;
			}
		}
		if (shouldSwitch) {
			rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
			switching = true;
		}
	}
	$('#itensTable tbody tr td .removeItens').attr('disabled', false);
	$('#itensTable tbody tr td .removeItens').first().attr('disabled', true);
}

controlaSelectProduto();