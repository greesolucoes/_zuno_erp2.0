// constants
const table = $('table.table-exibe');
const cuponsSelecionados = [];
const btnGerarSelecionados = '#botaoGerarSelecionados';
const btnGerarSelecionadosUnificado = '#botaoGerarSelecionados-unificado';
const btnEnviarCadastroNFSaida = '#enviarCadastroNFSaida';
const dataInicial = $('#dataInicialCupom');
const dataFinal = $('#dataFinalCupom');
const dataViews = $('.datas_views');
const isModuloComprasAtivo = !is_empty(dataViews.data('is_modulo_compras_ativo_empresa'), true);
const isNFUnificada = $('#controleNFSaidaUnificada');
const modalCadastro = '#modalCadastroNFSaida';
// constants

// variáveis úteis
let cupomEditado = [];
// variáveis úteis

// inicializações
$('select#filialPesquisaCupons').init();
$('select#filialPesquisaCupons').select2Ajax();
dataInicial.mask('99/99/9999');
dataFinal.mask('99/99/9999');
// inicializações

// eventos
$("#toggleSelecaoCupom").off('click').on('click', toggleSelecaoCupom);
$(btnGerarSelecionados).off('click').on('click', gerarNFSaidaCuponsSelecionados);
$(btnGerarSelecionadosUnificado).off('click').on('click', gerarNFSaidaCuponsSelecionadosUnificado);
$(btnEnviarCadastroNFSaida).off('click').on('click', enviarCadastroNFSaida);
$('#cancelarEnvioNFSaida').off('click').on('click', function () {
	cupomEditado = []
});
$('#botaoPesquisar').off('click').on('click', atualizaDataTable);

const classNewV2Labels = isOldLayout ? 'font-normal' : 'fw-normal txt-blue2 mb-2';
const classNewV2FormGroup = isOldLayout ? 'col-md-4' : 'col-md-6';

table.on('draw.dt', function () {
	$('.visualizarInfoCupons').off('click').on('click', function () {
		visualizarInfoCupons($(this).data('id'))
	});

	const inputSelectable = 'input[data-selectable="selectable"]';

	// Tratativa para quando as linhas forem selecionadas e o usuário trocar de página
	// Com essa tratativa, ao voltar pra alguma página, as opções que ele selecionou vão
	// continuar selecionadas
	$(inputSelectable)
		.parents('tr')
		.each(function () {
			let tr = $(this);

			cuponsSelecionados.map(function (cupomSelecionado) {
				if (cupomSelecionado.idCupom == tr.find('input').data('id')) {
					tr.toggleClass('selected');
				}
			})
		});

	// tratativa para novos elementos selecionados
	$(inputSelectable)
		.parents('tr')
		.click(function (e) {

			// ativa/inativa a classe de seleção
			$(e.currentTarget).toggleClass('selected')

			// verifica se o item atual está selecionado ou não. Se estiver, adiciona
			// o mesmo ao array de cupons selecionados, se não o remove
			if ($(e.currentTarget).hasClass('selected')) {
				cuponsSelecionados[$(e.currentTarget).find('input').data('id')] = {
					'idCupom': $(e.currentTarget).find('input').data('id'),
					'numeroCupom': $('input[data-id=' + $(e.currentTarget).find('input').data('id') + ']').data('numero_cupom')
				};
			} else {
				// cria um novo array sem o TR desselecionado
				let newCuponsSeleciodados = cuponsSelecionados.filter(function (cuponsSelecionados) {
					return cuponsSelecionados.idCupom != $(e.currentTarget).find('input').data('id')
				});

				// remove todos os cupons do array de selecionados
				cuponsSelecionados.splice(0, cuponsSelecionados.length);

				// preenche o array de selecionados com o novo array filtrado
				newCuponsSeleciodados.forEach(function (newCupom) {
					cuponsSelecionados[newCupom.idCupom] = (newCupom);
				})
			}

			// cada vez que essa função for executada, a classe a seguir é removida
			// para que sua ativação seja verificada nas próximas linhas
			$(btnGerarSelecionados).removeClass('botaoGerarAtivo');
			$(btnGerarSelecionadosUnificado).removeClass('botaoGerarAtivoUnificado');

			// só ativamos o botão para exclusão em lote caso haja mais de uma seleção
			if (Object.keys(cuponsSelecionados).length) {
				$(btnGerarSelecionados).addClass('botaoGerarAtivo')

				if (Object.keys(cuponsSelecionados).length >= 2) {
					$(btnGerarSelecionadosUnificado).addClass('botaoGerarAtivoUnificado');
				}
			}
		});
})

// / eventos

/**
 * Função para retorno de itens de um cupom selecionado
 */
async function visualizarInfoCupons(idCupom) {
	toggleLoading();

	// variáveis úteis
	const btnToggleSelecaoCupom = $('#toggleSelecaoCupom');

	btnToggleSelecaoCupom.show();

	// quando o botão for clickado, simula o unclick do mesmo (não consegui usar stopPropagation)
	$('input[data-id="' + idCupom + '"]').trigger('click');

	// requisição ao backend para retorno de informações sobre os itens de um cupom
	await $.post(
		dataViews.data('url_get_info_cupons'),  // url
		{
			id: idCupom,
			...tokenCsrf
		},								// POST data
		function (retorno) {
			const modal = "#modalVisualizacaoItens";
			const tableBody = modal + " .modal-body table tbody";
			const infoCupomBody = modal + " #infoCupom";

			retorno = JSON.parse(retorno);

			// seta o número do cupom
			$(modal + " .modal-body div #numeroCupom").html(retorno.infoCupom.numeroCupom);
			$('#cuponsRelacionados').html('');

			// prepara o informativo de cupons agrupados
			if (retorno.infoCupom.cuponsAgrupamento) {
				let cuponsAgrupamento = '';

				retorno.infoCupom.cuponsAgrupamento.forEach(function(item, index) {
					if (index == (retorno.infoCupom.cuponsAgrupamento.length - 1)) {
						cuponsAgrupamento += (' ' + l['e'] + ' ');
					} else if (index > 0) {
						cuponsAgrupamento += ', ';
					}

					cuponsAgrupamento += `
						${isOldLayout ? '<strong>' : '<span class="fw-medium">'}
							<a 	class="cupom-relacionado-visualizacao ${!isOldLayout ? 'txt-blue-absolute' : ''}" 
								style="cursor: pointer" data-id_cupom="${item.idCupom}"
							>
								${item.numeroCupom}
							</a>
						${isOldLayout ? '</strong>' : '</span>'}
					`;
				})

				$('#cuponsRelacionados').append(`
					<p class="${isOldLayout ? 'mr-2 mt-0 text-right' : 'fw-normal txt-blue2'}">
						(${l['oCupomFazParteDaUnificacaoEmNFDeSaidaAPartirDosCupons']} ${cuponsAgrupamento})
					</p>
				`);

				handleVisualizacaoCuponsRelacionados();
			}
			// prepara o informativo de cupons agrupados

			// remove informações anteriores da exibição de itens
			$(infoCupomBody).html('');
			$(tableBody).html('');

			if (retorno.infoCupom.hashkey) {
				btnToggleSelecaoCupom.hide();
			}

			$(infoCupomBody).append(getTemplateInfoCupons(retorno));

			// pra cada novo item, criamos uma nova linha com as informações
			retorno.itens.forEach(function (item) {
				$(tableBody).append(`
					<tr>
						<td>${item.descricao}</td>
						<td>${item.quantidade}</td>
						<td>${item.valor}</td>
					</t
				`);
			});

		}
	);

	// atribui o id do cupom ao botão de seleção/desseleção
	btnToggleSelecaoCupom.data('id_cupom', idCupom);

	// verifica se o cupom em questão está selecionado
	let isSelecionado = false;
	cuponsSelecionados.map(function (cupomSelecionado) {
		if (cupomSelecionado.idCupom == idCupom) {
			isSelecionado = true;
		}
	})

	// padrao do botao
	btnToggleSelecaoCupom
		.removeClass(isOldLayout ? 'btn-danger' : 'danger-button')
		.addClass(isOldLayout ? 'btn-success' : 'confirm-button');
	btnToggleSelecaoCupom.html(l['selecionarCupom']);

	// se o cupom ESTIVER selecionado, troca a a cor do botão e o texto para 'desselecionar'
	if (isSelecionado) {
		btnToggleSelecaoCupom
			.removeClass(isOldLayout ? 'btn-success' : 'confirm-button')
			.addClass(isOldLayout ? 'btn-danger' : 'danger-button');

		btnToggleSelecaoCupom.html(l['desselecionarCupom']);
	}

	// mostra o modal
	$('#modalVisualizacaoItens').modal('show');
	toggleLoading();
}

/**
 * Função para quando algum cupom relacionado for clickado da tela de visualização
 */
function handleVisualizacaoCuponsRelacionados() {
	$('.cupom-relacionado-visualizacao').off('click').on('click', function() {
		const idCupom = $(this).data('id_cupom');

		$('#modalVisualizacaoItens').modal('hide');
		visualizarInfoCupons(idCupom);
	})
}

/**
 * Função para quando o botão '#toggleSelecaoCupom' for acionado. Faz com que
 * haja a seleção/desseleção de uma linha na tabela.
 */
function toggleSelecaoCupom() {
	$('input[data-id="' + $(this).data('id_cupom') + '"]')
		.trigger('click');

	// mostra o modal
	$('#modalVisualizacaoItens').modal('hide');
}

/**
 * Função para preenchimento do modal de criaçao de novas NF de Saída
 */
async function gerarNFSaidaCuponsSelecionados() {
	toggleLoading();

	const modal = $(modalCadastro + ' .modal-body #novosCupons');
	$(modalCadastro + ' .modal-header :header').html(l['cadastrarNovasNFdeSaida']);
	$(btnEnviarCadastroNFSaida).html(l['gerarNFdeSaida']);

	// remove informaçoes posteriores do modal
	$(modal).html("");

	let needHr = false;

	// mapeia o array de cupons selecionados de modo a tratarmos um a um em uma variável independente
	// obs.: Por que 'map' e não 'foreach'? Porque é uma constante.
	cuponsSelecionados.map(function (idCupom, index) {
		if (needHr) {
			modal.append('<hr>');
		}

		modal.append(getTemplateGerarSelecionados(idCupom, index));

		$('#cliente_cupom' + index).select2Ajax();
		$('#cliente_cupom' + index).data('init', '');

		needHr = true;
	});

	isNFUnificada.val(0);

	$(modalCadastro).modal('show');
	toggleLoading();
}

async function gerarNFSaidaCuponsSelecionadosUnificado() {
	toggleLoading();

	const modal = $(modalCadastro + ' .modal-body #novosCupons');
	$(modalCadastro + ' .modal-header :header').html(l['cadastrarNovaNFdeSaidaUnificada']);
	$(btnEnviarCadastroNFSaida).html(l['gerarNFdeSaida']);

	// remove informaçoes posteriores do modal
	$(modal).html("");

	// mapeia o array de cupons selecionados de modo a tratarmos um a um em uma variável independente
	// obs.: Por que 'map' e não 'foreach'? Porque é uma constante.
	let idsCupons = '';
	let numerosCupons = '';
	let contador = 0;
	cuponsSelecionados.map(function (idCupom, index) {
		idsCupons += (contador > 0) ? `_${idCupom.idCupom}` : `${idCupom.idCupom}`;
		numerosCupons += (contador > 0) ? `_${idCupom.numeroCupom}` : `${idCupom.numeroCupom}`;

		contador++;
	});

	modal.append(getTemplateGerarSelecionados({
		'idCupom': idsCupons,
		'numeroCupom': numerosCupons
	}, idsCupons, true));

	$('#cliente_cupom' + idsCupons).select2Ajax();
	$('#cliente_cupom' + idsCupons).data('init', '');

	isNFUnificada.val(1);

	$(modalCadastro).modal('show');
	toggleLoading();
}

/**
 * Prepara os dados para a substituição de cupons
 */
async function enviarCadastroNFSaida() {
	if (validaCamposNFSaida()) {
		// variáveis úteis
		const dadosCadastroNFSaida = [];
		let hasErroIntegracao = false;

		// funcão para preparar os registros dos cupons selecionados para substituição
		let pushToDadosCadastroNFSaida = function (cupom) {
			dadosCadastroNFSaida.push({
				idCupons: cupom.idCupom,
				isUnificado: $('input#controleNFSaidaUnificada').val(),
				clienteCupom: (isNFUnificada.val() == 1)
					? ($('.campo_cliente').first().val() ?? '').trim()
					: ($('#cliente_cupom' + cupom.idCupom).val() ?? '').trim(),
				observacoesCupom: (isNFUnificada.val() == 1)
					? ($('.observacoes_cupons').first().val() ?? '').trim()
					: ($('input#observacoes_cupom' + cupom.idCupom).val() ?? '').trim()
			})
		};

		// se o cupom estiver sendo editado, somente ele é preparado;
		// senão, os cupons selecionados é que são preparados para envio
		if (cupomEditado.length > 0) {
			pushToDadosCadastroNFSaida(cupomEditado[cupomEditado.length - 1]);
		} else {
			cuponsSelecionados.map(function (cupom) {
				pushToDadosCadastroNFSaida(cupom);
			});
		}

		// se houverem cupons para substituição
		if (dadosCadastroNFSaida.length > 0) {
			await swal({
				title: l["substituirCuponsDeVendasPorNFdeSaida"],
				text: l["estaAcaoEIrreversivelAoConfirmarOsCuponsEscolhidosSeraoEnviadosAoSAPParaGeracaoDeNFdeSaida"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();

				ajaxRequest(
					true,
					dataViews.data("url_gerar_nfsaida_cupons_selecionados"),
					null,
					'text',
					{dataNFs: dadosCadastroNFSaida},
					async function (ret) {
						toggleLoading();

						ret = await JSON.parse(ret);

						if (ret['hasErroIntegracao']) {
							hasErroIntegracao = true;
						}

						swal(
							ret["title"],
							ret["text"],
							ret["class"]
						).then(function () {
							if (!hasErroIntegracao) {
								// se tiver sido uma edição, zera a variável de controle para que
								// não seja usado novamente; senão, remove todos os cupons selecionados
								// e esconde o botão
								if (cupomEditado.length > 0) {
									cupomEditado = [];
								} else {
									cuponsSelecionados.splice(0, cuponsSelecionados.length);
									$(btnGerarSelecionados).removeClass('botaoGerarAtivo')
								}

								// esconde o modal
								$(modalCadastro).modal('hide');

								atualizaDataTable();
							}
						}).catch(swal.noop);
					}
				);
			}).catch(swal.noop);
		}
	}
}

/**
 * Reenvia o cupom com erro
 * @param btn
 */
async function reenviarCupom(btn) {
	await swal({
		title: l["confirmarReenvio?"],
		text: l["estaAcaoEIrreversivelAoConfirmarOsCuponsEscolhidosSeraoEnviadosAoSAPParaGeracaoDeNFdeSaida"],
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: l["continuar!"],
		cancelButtonText: l["cancelar!"]
	}).then(function () {
		toggleLoading();

		ajaxRequest(
			true,
			dataViews.data("url_reenviar_cupom"),
			null,
			'text',
			{id: $(btn).data('id')},
			async function (ret) {
				toggleLoading();

				ret = await JSON.parse(ret);

				swal(
					ret["title"],
					ret["text"],
					ret["class"]
				).then(function () {
					if (!ret['hasErroIntegracao']) {
						atualizaDataTable();
					}
				}).catch(swal.noop);
			}
		);
	}).catch(swal.noop);
}

/**
 * Edita as informações de uma substituição com erro
 * @param btn
 */
async function editarInfoSubstituicao(btn) {
	toggleLoading();

	// variáveis úteis
	const idCupom = $(btn).data('id');
	const modal = modalCadastro + ' .modal-body #novosCupons';

	// altera as informações de exibição do modal
	$(modalCadastro + ' .modal-header :header').html(l['editarNFdeSaida']);
	$(btnEnviarCadastroNFSaida).html(l['salvarEReenviar']);

	// remove informaçoes posteriores do modal
	$(modal).html("")

	// requisição ao backend para retorno de informações sobre os itens de um cupom
	await $.post(
		dataViews.data('url_get_info_cupons'),  // url
		{
			id: idCupom,
			...tokenCsrf
		},								// POST data
		function (retorno) {
			retorno = JSON.parse(retorno);

			let idCupomInit = (retorno.infoCupom.idCuponsAgrupamentoExibicao ?? idCupom);
			let isUnificado = (retorno.infoCupom.idCuponsAgrupamentoExibicao != undefined);
			isNFUnificada.val(isUnificado ? 1 : 0);

			$(modal).append(getTemplateGerarSelecionados({
				'idCupom': idCupomInit,
				'numeroCupom': retorno.infoCupom.cuponsAgrupamentoExibicao ?? $('input[data-id=' + idCupom + ']').data('numero_cupom')
			}, idCupomInit, isUnificado));

			// inicialização dos campos criados para edição
			if (isModuloComprasAtivo) {
				$('#cliente_cupom' + idCupomInit).data('init', {
					id: retorno.infoCupom.idClientes,
					text: retorno.infoCupom.nomeCliente
				});
				$('#cliente_cupom' + idCupomInit).select2Ajax();
			} else {
				$('#cliente_cupom' + idCupomInit).val(retorno.infoCupom.codigoClientes);
			}

			$('#observacoes_cupom' + idCupomInit).val(retorno.infoCupom.observacoes);
			// inicialização dos campos criados para edição

			// seta a variável de controle com as informações do cupom editado
			cupomEditado[1] = {
				'idCupom': idCupomInit,
				'numeroCupom': retorno.infoCupom.cuponsAgrupamentoExibicao ?? $('input[data-id=' + idCupomInit + ']').data('numero_cupom')
			};
		}
	);
	// mostra o modal
	$(modalCadastro).modal('show');
	toggleLoading();
}

/**
 * Função para validação dos campos preenchidos pelo usuário ao substituir/editar um cupom
 * @returns {boolean}
 */
function validaCamposNFSaida() {
	const tituloMsg = l['verifiqueTodasAsInformacoesParaContinuar'];
	let msgRetornoObservacoes = (l['asInformacoesNaoDevemConterAspasOuBarrasVerifiqueOsSeguintesCupons'] + ': ');
	let hasErrorObservacoes = false;
	let needVirgulaObservacoes = false;

	let msgRetornoPreenchimento = (l['algumCampoNaoEstaPreenchidoCorretamenteParaOsCupons'] + ': ');
	let hasErrorPreenchimento = false;
	let needVirgulaPreenchimento = false;

	let msgRetorno = '';

	let executaValidacoes = function (cupom) {
		const unificado = (isNFUnificada.val() == 1);

		let numeroCupom = cupom.numeroCupom;
		let clienteCupom = ($('#cliente_cupom' + cupom.idCupom).val() ?? '').trim();
		let observacoesCupom = ($('input#observacoes_cupom' + cupom.idCupom).val() ?? '').trim();

		if (unificado) {
			clienteCupom = ($('.campo_cliente').first().val() ?? '').trim();
			observacoesCupom = ($('.observacoes_cupons').first().val() ?? '').trim();
		}

		if (clienteCupom == '' || observacoesCupom == '') {
			if (needVirgulaPreenchimento) {
				msgRetornoPreenchimento += ', ';
			}
			msgRetornoPreenchimento += numeroCupom;

			hasErrorPreenchimento = true;
			needVirgulaPreenchimento = true;
		}

		// valida o correto preenchimento do campo 'Observações'
		if (
			(observacoesCupom.indexOf("'") !== -1) ||
			(observacoesCupom.indexOf('"') !== -1) ||
			(observacoesCupom.indexOf("\\") !== -1) ||
			(observacoesCupom.indexOf("/") !== -1) ||
			(clienteCupom.indexOf("'") !== -1) ||
			(clienteCupom.indexOf('"') !== -1) ||
			(clienteCupom.indexOf("\\") !== -1) ||
			(clienteCupom.indexOf("/") !== -1)
		) {
			if (needVirgulaObservacoes) {
				msgRetornoObservacoes += ', ';
			}
			msgRetornoObservacoes += numeroCupom;

			hasErrorObservacoes = true;
			needVirgulaObservacoes = true;
		}
	}
	// verifica se há um cupom editado. Se houver, executa as validações nele; senão, executa as validações
	// nos cupons selecionados
	if (cupomEditado.length > 0) {
		executaValidacoes(cupomEditado[cupomEditado.length - 1]);
	} else {
		// valida o preenchimento de todos os campos
		cuponsSelecionados.map(function (cupom) {
			executaValidacoes(cupom)
		});
	}

	// retorna com a msg de acordo com o tipo de erro
	if (hasErrorPreenchimento) {
		msgRetorno = msgRetornoPreenchimento;
	} else if (hasErrorObservacoes) {
		msgRetorno = msgRetornoObservacoes;
	}

	// se houver msgm, retorna com o erro
	if (msgRetorno !== '') {
		swal({
			title: tituloMsg,
			text: msgRetorno,
			type: 'warning'
		})

		return false;
	}

	return true;
}

/**
 * Função de atualização/carregamento da datatable de acordo com as informações da barra de pesquisa
 */
function atualizaDataTable() {
	cuponsSelecionados.splice(0, cuponsSelecionados.length);
	$(btnGerarSelecionados).removeClass('botaoGerarAtivo');
	$(btnGerarSelecionadosUnificado).removeClass('botaoGerarAtivoUnificado');

	let dataFinalInt = Math.round(new Date(dataFinal.val().split('/').reverse().join('-')).getTime())
	let dataInicialInt = Math.round(new Date(dataInicial.val().split('/').reverse().join('-')).getTime())
	let dataInicialPesquisa = dataInicial.val();
	let dataFinalPesquisa = dataFinal.val();


	if (dataFinalInt < dataInicialInt) {
		let dataTemp = dataFinalPesquisa;
		dataFinalPesquisa = dataInicialPesquisa;
		dataInicialPesquisa = dataTemp;

		let dataIntTemp = dataFinalInt;
		dataFinalInt = dataInicialInt;
		dataInicialInt = dataIntTemp;
	}

	if ((dataFinal == '') || (dataInicial == '')) {
		swal({
			title: l['asDatasDevemSerInformadasCorretamenteParaAPesquisa'],
			text: l['informeADataInicialEADataFinal'],
			type: 'warning'
		})
	} else if (Math.ceil((dataFinalInt - dataInicialInt) / (1000 * 3600 * 24)) > 6) {
		swal({
			title: l['informeDatasComIntervalosValidosParaAPesquisa'],
			text: l['asDatasInformadasParaPesquisaDevemTerDiferencaMaximaDeSeteDias'],
			type: 'warning'
		})
	} else {
		const status = $('#statusPesquisaCupons').val() ?? '';
		const filialCupons = $('#filialPesquisaCupons').val() ?? '';
		const urlAjax = dataViews.data('url_get_cupons');

		let get = `${urlAjax}`;
		let hasGet = false;

		if (dataInicialPesquisa != '') {
			hasGet = true;
			get += `?dataInicial=${dataInicialPesquisa.split('/').reverse().join('-')}`;
		}

		if (dataFinalPesquisa != '') {
			if (hasGet) {
				get += '&';
			} else {
				get += '?';
				hasGet = true;
			}

			get += `dataFinal=${dataFinalPesquisa.split('/').reverse().join('-')}`
		}

		if (filialCupons != '') {
			if (hasGet) {
				get += '&';
			} else {
				get += '?';
				hasGet = true;
			}

			get += `filial=${filialCupons.toString()}`
		}

		if (status != '') {
			get += (hasGet) ? '&' : '?';
			get += `status=${status.toString()}`
		}

		table.each(function () {
			if ($.fn.DataTable.isDataTable(this)) {
				const dataTable = $(this).DataTable();
				dataTable.clear();
				dataTable.destroy();
			}
		});

		$(table).data("url_ajax", get);
		allTables();
	}
}

function getTemplateInfoCupons(dadosCupom) {
	let template = '';

	if (dadosCupom.infoCupom.hashkey) {
		if (dadosCupom.infoCupom.erroMsg) {
			template += isOldLayout
				? `
					<div class="row">
						<div class="container-msg">
							<div class="espacamento"></div>
							<div class="alert alert-danger alert-dismissable animated flipInX" role="alert">
								<b>${l['erroAoIntegrarDocumento']}: </b> <br> ${dadosCupom.infoCupom.erroMsg}
							</div>
						</div>
					</div>
				` : `
					<div class="container-msg mb-5">
						<div class="alert alert-danger alert-dismissable animated flipInX d-flex flex-wrap pb-5" role="alert">
							<a class="close text-decoration-none pe-4 col-12 d-flex justify-content-end mb-3" data-bs-dismiss="alert" aria-label="close" style="cursor: pointer;">
								<i class="fa-solid fa-xmark"></i>
							</a>
							<p class="col-12 text-center">
								<span class="fw-medium">${l['erroAoIntegrarDocumento']}: </span>
								<br>
								${dadosCupom.infoCupom.erroMsg}
							</p>
						</div>
					</div>
				`;
		}

		template += `
			<h4 class="${isOldLayout ? 'mb-1 mt-1' : 'section-title col-12 mb-4'}">
				${l['dadosDaSubstituicao']}
			</h4>
			
			<div class="${isOldLayout ? 'form-row' : 'd-flex align-items-center justify-content-between col-12 flex-wrap col-md-6-space gap-3'}">
				<div class="form-group ${classNewV2FormGroup} col-12">
					<label class="${classNewV2Labels}">
						${l['identificadorPortal']}
					</label>
					<input type="text" readonly disabled 
						   value="${dadosCupom.infoCupom.idCupons}"
						   class="form-control"
					/>
				</div>
				<div class="form-group ${classNewV2FormGroup} col-12">
					<label class="${classNewV2Labels}">
						${l['dataEHoraDaSubstituicao']}
					</label>
					<input type="text" readonly disabled 
						   value="${dadosCupom.infoCupom.dataHoraSubstituicao}"
						   class="form-control"
					/>
				</div>
				<div class="form-group ${classNewV2FormGroup} col-12">
					<label class="${classNewV2Labels}">
						${l['usuarioSubstituicao']}
					</label>
					<input type="text" readonly disabled 
						   value="${dadosCupom.infoCupom.usuario}"
						   class="form-control"
					/>
				</div>
			${isOldLayout ? '</div><div class="form-row">' : ''}
				<div class="form-group ${classNewV2FormGroup} col-12">
					<label class="${classNewV2Labels}">
						${l['docNumNF']}
					</label>
					<input type="text" readonly disabled 
						   value="${dadosCupom.infoCupom.docNum}"
						   class="form-control"
					/>
				</div>
				<div class="form-group ${classNewV2FormGroup} col-12">
					<label class="${classNewV2Labels}">
						${l['cliente']}
					</label>
					<input type="text" readonly disabled 
						   value="${dadosCupom.infoCupom.codigoClientes}"
						   class="form-control"
					/>
				</div>
				<div class="form-group ${classNewV2FormGroup} col-12">
					<label class="${classNewV2Labels}">
						${l['dataNF']}
					</label>
					<input type="text" readonly disabled 
						   value="${dadosCupom.infoCupom.data}"
						   class="form-control"
					/>
				</div>
				<div class="form-group col-12 ${isOldLayout ? 'mt-3' : ''}">
					<label class="${classNewV2Labels}">${l['observacoes']} </label>
					<input type="text" readonly disabled 
						   value="${dadosCupom.infoCupom.observacoes}"
						   class="form-control"
					/>
				</div>
			</div>
			
			${isOldLayout ? '<hr>' : '<div class="separador-view my-5"></div>'}
		`;
	}

	template += `
		<div class="${isOldLayout ? 'form-row' : 'd-flex align-items-center justify-content-between col-12 flex-wrap col-md-6-space gap-3'}">
			<div class="form-group ${classNewV2FormGroup} col-12">
				<label class="${classNewV2Labels}">
					${l['status']}
				</label>
				<input type="text" readonly disabled 
					   value="${dadosCupom.infoCupom.status}"
					   class="form-control"
				/>
			</div>
			<div class="form-group ${classNewV2FormGroup} col-12">
				<label class="${classNewV2Labels}">
					${l['dataEmissao']}
				</label>
				<input type="text" readonly disabled 
					   value="${dadosCupom.infoCupom.dataEmissao}"
					   class="form-control"
				/>
			</div>
			<div class="form-group ${classNewV2FormGroup} col-12">
				<label class="${classNewV2Labels}">
					${l['serie']}
				</label>
				<input type="text" readonly disabled 
					   value="${dadosCupom.infoCupom.serie}"
					   class="form-control"
				/>
			</div>
			<div class="form-group col-12 ${isOldLayout ? 'mt-3' : ''}">
				<label class="${classNewV2Labels}">
					${l['chaveAcesso']}
				</label>
				<input type="text" readonly disabled 
					   value="${dadosCupom.infoCupom.chaveAcesso}"
					   class="form-control"
				/>
			</div>
		</div>
	`;

	return template;
}

function getTemplateGerarSelecionados(objCupom, idCupom, unificado = false) {
	const numeroCupom = unificado
		? objCupom.numeroCupom.split('_').join(', ')
		: objCupom.numeroCupom;

	const urlGetClientes = dataViews.data('url_get_clientes');

	// verificar modulo compras
	let campoCliente = `
		<input type="text" autocomplete="off" name="cliente_cupom[${idCupom}]" 
			id="cliente_cupom${idCupom}" class="form-control campo_cliente" placeholder="..." 
		/>
	`;

	if (isModuloComprasAtivo) {
		campoCliente = ` 
 			<div class="separador-geral">
				<select class="form-control select select_ajax select_cliente_cupons campo_cliente" 
						name="cliente_cupom[${idCupom}]" id="cliente_cupom${idCupom}"
						data-url="${urlGetClientes}" data-placeholder="${l['selecione']}">
				</select>
			</div>
		`;
	}

	return `
		${isOldLayout ? `
			<div class="text-right">
				<p class="mr-2 mb-0 text-right">${unificado ? l['numeroCupons'] : l['numeroCupom']}</p>
				<h4 class="mr-2 mt-0 numero-cupom">${numeroCupom}</h4>
			</div>
		` : `
			<div class="identif-cupom col-12">
				<p class="txt-blue2 fw-medium fs-2">
					${unificado ? l['numeroCupons'] : l['numeroCupom']}: ${numeroCupom}
				</p>
			</div>
		`}
		<div class="informacoes_add mt-3">
			<input type="hidden" name="id_cupom[${idCupom}]" class="id_cupons" value="${idCupom}" >
		
			<div class="d-flex align-items-start justify-content-between col-12 flex-wrap">
				<div class="form-group col-12">
					<label class="${classNewV2Labels}" for="cliente_cupom[${idCupom}]">
						${l['cliente']} 
					</label>
					
					${campoCliente}	   
				</div>
				<div class="form-group col-12 mt-3">
					<label for="observacoes_cupom[${idCupom}]" class="${classNewV2Labels}">
						${l['observacoes']} 
					</label>
					<input type="text" autocomplete="off"
						   class="form-control observacoes_cupons" 
						   maxlength="100" placeholder="..."
						   id="observacoes_cupom${idCupom}"	   
						   name="observacoes_cupom[${idCupom}]" 
					/>
				</div>
			</div>
		</div>
	`;
}

atualizaDataTable();