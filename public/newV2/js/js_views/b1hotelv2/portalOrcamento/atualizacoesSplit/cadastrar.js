const datasViews = $(".datas_views");
const cifraoMoeda = datasViews.data('prefixo_moeda');
const separadorDecimalMoeda = datasViews.data('separador_centavos_moeda');
const separadorMilharMoeda = datasViews.data('separador_milhar_moeda');
let casasPreco = datasViews.data('casas_preco');
casasPreco = (is_empty(casasPreco, 1))
	? 2
	: parseInt(casasPreco.toString());

$('.valores').each(function (index, elemento) {
	$(elemento).val(
		formataDecimal(
			$(elemento).val(),
			'.',
			separadorDecimalMoeda,
			separadorMilharMoeda,
			cifraoMoeda,
			true,
			casasPreco
		)
	);
});

$('.somar_campos').off('change keyup').on('change keyup', somarCampos);
function somarCampos() {
	let valorAnoLinha = 0;
	$(this).parents('tr').find('.somar_campos').each(function(index, elemento) {
		valorAnoLinha += stringParaFloat(
			$(elemento).val(),
			separadorDecimalMoeda,
			true
		);
	})

	$(this).parents('tr').find('.valor_ano').val(
		formataDecimal(
			valorAnoLinha,
			'.',
			separadorDecimalMoeda,
			separadorMilharMoeda,
			cifraoMoeda,
			true,
			casasPreco
		)
	);
}

$('.somar_campos').trigger('change');

// ação do botão salvar
$('#btnSalvarSplits').off('click').on('click', validaInformacoesSplits);

function validaInformacoesSplits() {
	const inputsValores = $('tr:not(.ocultar) .valores:not(.nao-verificar)');

	// verifica se os valores devem ir zerados conforme informado
	try {
		inputsValores.each(function(index, elemento) {
			const separadorDecimalMoeda = datasViews.data('separador_centavos_moeda');
			const valorConferencia = stringParaFloat($(elemento).val(), separadorDecimalMoeda, true);

			if (!!valorConferencia === false) {
				throw new Error('error');
			}
		})

		enviarInformacoesSplits();
	} catch (error) {
		swal({
			title: l["desejaContinuar?"],
			text: l['haValoresZeradosNoOrcamentoContinuarMesmoAssim'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["continuar!"],
			cancelButtonText: l["cancelar!"],
		}).then(function () {
			enviarInformacoesSplits();
		}).catch(swal.noop);
	}
}

async function enviarInformacoesSplits() {
	toggleLoading();

	setTimeout(async function() {
		ajaxRequest(
			true,
			$('.datas_views').data('url_add_split'),
			null,
			'text',
			{ 'save': formToStringJson('#formCadastroSplit') },
			function (ret) {
				ret = JSON.parse(ret);

				toggleLoading();
				swal(
					ret['title'],
					ret['text'],
					ret['class']
				).then(function() {
					if (ret['class'] !== 'error') {
						history.back();
					}
				}).catch(swal.noop);
			}
		);
	}, 200)
}
