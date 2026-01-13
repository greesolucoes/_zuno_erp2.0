

const formUtils = {
	removeTr: function (elemento) {
		$(elemento).parents('tr').fadeOut(270, function () {
			$(elemento).parents('tr').remove();
		});
	},

	addItemDePara: function (button, aba) {
		$('button[data-add="' + button + '"]').click(function(e) {
			e.preventDefault();
			let template = $(aba + ' template').html();
			let index = parseInt($(aba + ' tfoot').attr('data-count')) + 1;
			let html = template.replaceAll("{{n}}", index);

			$(aba + ' tbody').fadeIn(270, function() {
				$(aba + ' tbody').append(html);
			})
			$(`select[name="dePara[${index}][idRegraDistribuicaoOrigem]"]`).select2Simple();
			$(`select[name="dePara[${index}][idRegraDistribuicaoDestino]"]`).select2Simple();

			$(aba + ' tfoot').attr('data-count', index);
		})
	},
}

// de-paras disponíveis
// adicionar aqui caso haja outro
const btnsDePara = [
	{ 'btn': 'dePara', 'aba': '#deParas-aba' },
];

// para cada de-para, cria-se a funcionalidade
btnsDePara.forEach(function(dePara) { formUtils.addItemDePara(dePara.btn, dePara.aba) });



function buscarRegraDistribuicao(index,valor){
	const url = $('#deParasTable').attr("data-url-regras-distribuicao");

	$.ajax({
		url : url,
		type : 'post',
		dataType: 'json',
		data : {
			dimensao : valor,
			...tokenCsrf
		},
		beforeSend : function(){
			$(`select[name="dePara[${index}][idDimensoes]"]`).attr('readonly','readonly');
			$(`select[name="dePara[${index}][idRegraDistribuicaoOrigem]"]`).attr('readonly','readonly').empty();
			$(`select[name="dePara[${index}][idRegraDistribuicaoDestino]"]`).attr('readonly','readonly').empty();
		}
	}).done(function(data){
		$(`select[name="dePara[${index}][idRegraDistribuicaoOrigem]"]`).append($('<option>', {
			value: '',
			text : ''
		}));
		$(`select[name="dePara[${index}][idRegraDistribuicaoDestino]"]`).append($('<option>', {
			value: '',
			text : ''
		}))
		$.each(data, function (i, item) {
			$(`select[name="dePara[${index}][idRegraDistribuicaoOrigem]"]`).append($('<option>', {
				value: item.idregra,
				text : item.nomeregra
			}));
			$(`select[name="dePara[${index}][idRegraDistribuicaoDestino]"]`).append($('<option>', {
				value: item.idregra,
				text : item.nomeregra
			}));
		});

		$(`select[name="dePara[${index}][idDimensoes]"]`).removeAttr('readonly');
		$(`select[name="dePara[${index}][idRegraDistribuicaoOrigem]"]`).removeAttr('readonly');
		$(`select[name="dePara[${index}][idRegraDistribuicaoDestino]"]`).removeAttr('readonly');
	});
}