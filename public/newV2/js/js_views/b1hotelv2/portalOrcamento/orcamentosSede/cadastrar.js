const datasViews = $(".datas_views");
const cifraoMoeda = datasViews.data('prefixo_moeda');
const separadorDecimalMoeda = datasViews.data('separador_centavos_moeda');
const separadorMilharMoeda = datasViews.data('separador_milhar_moeda');
let casasPreco = datasViews.data('casas_preco');
casasPreco = (is_empty(casasPreco, 1))
	? 2
	: parseInt(casasPreco.toString());

controlaTabelaSuite({
	"ref": "#tabela_contas_orcamento",
	"funAposAddItem": function () {
		$('.select_conta_contabil').select2Ajax();

		// verifica se o ano está preenchido
		if (!!$('#anoOrcamento').val() === false) {
			swal(
				l['atenção!'],
				l['porFavorInformeOAnoDoOrcamentoParaContinuar'],
				'warning'
			).then(function() {
				$('tr:not(.ocultar):last-child .remove-itens-table-geral').trigger('click');
			})
		}

		indexaLinhas();
		somarMeses();
		handleProjectLinha();
		handleJustificativaLinha();
	}
});

function indexaLinhas() {
	const seletorInputs = 'tr:not(.ocultar) .input-index-linha';

	// atribui novos indexadores a linhas criadas
	let targetValue = 0;
	if ($(seletorInputs)[$(seletorInputs).length - 2] != undefined) {
		targetValue = parseInt($(seletorInputs)[$(seletorInputs).length - 2].value);
	}

	targetValue = isNaN(targetValue) ? 0 : targetValue;

	$(seletorInputs).last().val(
		targetValue + 1
	);
}

// inicializa o select de centro de custo
$(document).ready(function() {
	$('#selectCentroCusto').select2Ajax();

	if (!is_empty($('#idOrcamentos').val(), 1) || !is_empty($('#isDuplicacao').val(), 1)) {
		$('.dependenciaCentroCusto').removeClass('ocultar');
		$('tbody tr.ocultar').remove();

		inicializaLinhas();
	}
});

function inicializaLinhas() {
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

	somarMeses();
	handleProjectLinha();
	handleJustificativaLinha();
}

// ação do botão salvar
$('#btnSalvarOrcamentoSede').off('click').on('click', validaInformacoesOrcamentoSede);

// ao preencher o centro de custo, solicita confirmação. Se confirmado, esconde o seletor, mostra o input informativo
// e habilita o preenchimento de linhas
$('#selectCentroCusto').on('select2:select', function() {
	const optionSelecionada = $(this).find('option:selected');

	swal({
		title: l["desejaContinuar?"],
		text: (l['confirmarSelecaoDoCentroDeCusto{{CC}}?EstaSelecaoNaoPodeSerDesfeita']).replace('{{CC}}', optionSelecionada.html()),
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: l["continuar!"],
		cancelButtonText: l["cancelar!"]
	}).then(function () {
		toggleLoading();

		$('#inputCentroCusto').val(optionSelecionada.html());
		$('#selectDiv').addClass('ocultar');
		$('.dependenciaCentroCusto').removeClass('ocultar');
		$('#btn-add-itens').trigger('click');
		$('#anoOrcamento').addClass('readonly');

		setTimeout(function() {
			handleLinhasContasContabeis()
		}, 200);
	}, function(dismiss) {
		$('#selectCentroCusto').val('');
		$('#selectCentroCusto').trigger('change');
	}).catch(swal.noop);
});

function handleLinhasContasContabeis() {
	let template = $('tr.ocultar').html();

	ajaxRequest(
		true,
		$('.datas_views').data("url_get_valores_contas_contabeis") + $('#anoOrcamento').val(),
		null,
		'text',
		null,
		function (ret) {
			ret = JSON.parse(ret);

			Object.keys(ret).forEach(function (index, el)  {
				$('tbody').append('<tr>' + template + '</tr>');

				$('tbody tr:last').find('.input-id-conta-contabil').val(ret[index].idConta);
				$('tbody tr:last').find('.input-nome-conta-contabil').val(ret[index].textConta);
				$('tbody tr:last').find('.realizado-anterior').val(ret[index].realizadoAnterior);
				$('tbody tr:last').find('.realizado-atual').val(ret[index].realizadoAtual);
				$('tbody tr:last').find('.input-index-linha').val(el);
			});

			$('tr.ocultar:not(:first)').removeClass('ocultar');
			$('tbody tr.ocultar').remove();

			somarMeses();
			handleProjectLinha();
			handleJustificativaLinha();

			$("input[data-mask='numerov2']").fnMascaraNumeroV2();

			toggleLoading();
		}
	)
}

/**
 * Função para somar valores dos meses de uma linha para o campo 'ano' da mesma
 */
function somarMeses() {
	$('.somar_campos').off('change keyup').on('change keyup', function() {
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
	})
}

/**
 * Função para seleção e alteração de project nas linhas
 */
function handleProjectLinha() {
	let inputProject;
	let inputInitProject;
	const selectProject = $('select#project');
	const modalProject = $('#modalInformarProject');

	$('.informar_project_linha').off('click').on('click', function() {
		// captura o input para armazenamento dos dados do select
		inputProject = $(this).parents('td').find('.input-project');
		inputInitProject = $(this).parents('td').find('.input-init-project');

		// inicializa o select2 de project
		selectProject.select2Ajax();

		// se tiver valor no input, associa-o ao select, inicializando com o valor do mesmo
		if(!is_empty(inputProject.val(), 1)){
			selectProject.data('init', JSON.parse(inputInitProject.val()));
			selectProject.select2Ajax();
		}

		// mostra o modal
		modalProject.modal('show');
	});

	$('#btnCadastrarProject').off('click').on('click', function() {
		// insere o valor selecionado ao input
		inputProject.val(selectProject.val());
		inputInitProject.val(JSON.stringify({
			'id': selectProject.val(),
			'text': selectProject.find('option:selected').html()
		}))

		// esconde o modal
		modalProject.modal('hide');
	});

	// ao esconder o modal, zera o valor do select2
	modalProject.on('hidden.bs.modal', function () {
		selectProject.val('');
		selectProject.trigger('change');
	});
}

function handleJustificativaLinha() {
	$('.informar_justificativa_linha').off('click').on('click', function() {
		const indexJustificativa = $(this).parents('tr').find('.input-index-linha').val();

		const linhaJustificativa = `
			<tr class="trJustificativa" data-linha_origem="${indexJustificativa}">
				<td></td>
				<td class="pt-0" >
					<input type="hidden" name="indexJustificativa[]" class="index_justificativa" value="${indexJustificativa}">
					<button type="button" title="${l['retirarJustificativa']}"
							class="btn btn-danger fa fa-times remove-justificativa">
					</button>
				</td>
				<td colspan="5" class="pt-0">
					<input type='text' name='justificativaLinha[]'
					   class='form-control justificativaLinha' maxlength="254"
					   value="" placeholder="${l['justificativa']}"
					/>
				</td>
			</tr>
		`;

		if($(this).parents('tbody').find('.index_justificativa[value='+indexJustificativa+']').length > 0) {
			$(linhaJustificativa).insertAfter($(this).parents('tbody').find('.index_justificativa[value='+indexJustificativa+']').last().parents('tr'));
		} else {
			$(linhaJustificativa).insertAfter($(this).closest('tr'));
		}

		__handleRemoveJustificativa();
	});

	const __handleRemoveJustificativa = function() {
		$('.remove-justificativa').off('click').on('click', function() {
			$(this).parents('.trJustificativa').prev().find('.input-has-justificativa').val('');
			$(this).parents('.trJustificativa').remove();
		});

		$('.remove-itens-table-geral').on('click', function() {
			const indexLinha = $(this).parents('tr').find('.input-index-linha').val();

			$('tr[data-linha_origem=' + indexLinha + ']').remove();
			indexaLinhas();
		})
	}

	__handleRemoveJustificativa();
}

function validaInformacoesOrcamentoSede() {
	const inputsValores = $('tr:not(.ocultar) .valores:not(.nao-verificar)');

	const __retornoPadraoSwal = function(text) {
		swal(l['atenção!'], text, 'warning');
	}

	// necessário informar o ano corretamente
	if (!!$('#anoOrcamento').val() === false) {
		__retornoPadraoSwal(l['porFavorInformeOAnoDoOrcamentoParaContinuar']);

	// informar ao menos uma linha com informações
	} else {
		// verifica se os valores devem ir zerados conforme informado
		try {
			inputsValores.each(function(index, elemento) {
				const separadorDecimalMoeda = datasViews.data('separador_centavos_moeda');
				const valorConferencia = stringParaFloat($(elemento).val(), separadorDecimalMoeda, true);

				if (!!valorConferencia === false) {
					throw new Error('error');
				}
			})

			enviarInformacoesOrcamentoSede();
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
				enviarInformacoesOrcamentoSede();
			}).catch(swal.noop);
		}
	}
}

async function enviarInformacoesOrcamentoSede() {
	toggleLoading();

	setTimeout(async function() {
		ajaxRequest(
			true,
			$('.datas_views').data('url_add_orcamentos'),
			null,
			'text',
			{ 'save': formToStringJson('#formCadastroOrcamento', 0) },
			function (ret) {
				ret = JSON.parse(ret);
				toggleLoading();

				swal(
					ret['title'],
					ret['text'],
					ret['class']
				).then(function() {
					if (!!ret['idOrcamentos'] !== false) {
						$('#idOrcamentos').val(ret['idOrcamentos']);

						if (!is_empty($('#isDuplicacao').val(), 1)) {
							toggleLoading();
							window.location.href = $('.datas_views').data('url_orcamentos');
						}
					}

					if (ret['class'] !== 'error') {
						history.back();
					}
				}).catch(swal.noop);
			}
		);

	}, 200)
}
