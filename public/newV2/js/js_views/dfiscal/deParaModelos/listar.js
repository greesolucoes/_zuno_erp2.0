/**
 * pega a url que sera responsavel pelo merge de informações do de/para (Salvar e Alterar)
 * @type {*|jQuery}
 */
let urlMergeDeParaModelos = $('.data_views').data('url_merge_de_para_modelos');
/**
 * select de produtos dentro do modal
 * @type {*|jQuery|HTMLElement}
 */
let selectModelo = $("#idModelo");
/**
 * Input do codigo oobj dentro do modal
 * @type {*|jQuery|HTMLElement}
 */
let inputOrigem = $("#idOrigem");
/**
 * div do modal de de/para de modelos
 * @type {*|jQuery|HTMLElement}
 */
let modalDeParaModelos = $('#modalDeParaModelos');
/**
 * div dentro do modal que recebe as mensagens de erro, sucesso e alertas
 * @type {*|jQuery|HTMLElement}
 */
let modalMsgs = $('#container-msg-modal');
/**
 * Botão de ação dentro do modal onde ele ira ser utilizado para salvar ou atualizar cadastros
 * @type {*|jQuery|HTMLElement}
 */
let botaoAcaoModal = $('#botaoAcaoModal');
/**
 * Input que faz com que o modal não feche apos a inserção. OBS: Sempre ocultar o mesmo na edição
 */
let inputContinuarAdd = $('#continuarInserindo');

/**
 * Função para iniciar os campos do modal, tem a opção de enviar parametros para iniciar o select 2 com valor
 * @param opts
 */
function startInput(opts = '') {
	let param = '';
	if (opts !== '') {
		param = {id: opts.idModeloNotaFiscal, text: opts.nomeModelo};
	}
	selectModelo.data('init', param);
	selectModelo.select2Ajax();
}

/**
 * Função para limpar campos de texto e select do modal
 */
function clearInputs() {
	selectModelo.select2Reset();
	selectModelo.find('option').remove();
	inputOrigem.val('');
	inputOrigem.prop('readonly', false);
}

/**
 * Função para limpar as informações do modal
 */
function clearModalInfo() {
	clearInputs();
	modalMsgs.html('');
	botaoAcaoModal.html('');
	botaoAcaoModal.removeAttr('data-action')
	botaoAcaoModal.removeAttr('data-request')
	inputContinuarAdd.hide();
}

/**
 * Função que ira abrir o modal para inserir o modelo do de/para
 */
function insertItem() {
	botaoAcaoModal.attr('data-action', urlMergeDeParaModelos);
	botaoAcaoModal.attr('data-request', 'insercao');
	botaoAcaoModal.html(l['salvar']);
	inputContinuarAdd.show();
	modalDeParaModelos.modal('show');
	startInput();
}

/**
 * Função que ira abrir o modal já com as informações preenchidas e permitindo o utilizador editar
 * @param item Recebemos os dados para buscar e editar o cadastro
 */
function editarItem(item) {
	let idOrigem = $(item).data('idorigem');
	let urlGet = $(item).data('url');

	$('#idOrigem').val(idOrigem).prop('readonly', true);
	$.post(
		urlGet,
		{
			idOrigem: idOrigem,
			addMore: false,
			...tokenCsrf
		},
		function (retorno) {
			if (retorno.error) {
				swal(
					l["erro!"],
					l['éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte'],
					"error"
				).catch(swal.noop);
			} else {
				botaoAcaoModal.attr('data-action', urlMergeDeParaModelos);
				botaoAcaoModal.attr('data-request', 'atualizacao');
				botaoAcaoModal.html(l['atualizar']);
				inputContinuarAdd.hide();
				modalDeParaModelos.modal('show');
				startInput(retorno);
			}
		}
	);
}

/**
 * Função que ira deletar um de/para de modelo
 * @param item Recebemos os dados necessarios para deletar o item
 */
function removerItem(item) {
	let obj = $(item);
	let tableDataTable = $(".table-exibe").DataTable();
	let paramsAjax = {
		idModelo: $(obj).data("idmodelo"),
		idOrigem: $(obj).data("idorigem")
	}

	swal({
		title: l["deletarRegistro"],
		text: l["desejaContinuar?"],
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
			$(obj).data("url"),
			null,
			'text',
			paramsAjax,
			function (ret) {
				ret = JSON.parse(ret);

				swal(
					ret["titulo"],
					ret["text"],
					ret["class"]
				).catch(swal.noop);

				if (!is_empty(ret["bol"], 1)) {
					tableDataTable.draw();
				}

				toggleLoading();
			}
		);
	}).catch(swal.noop);

}

/**
 * Sempre que fechamos o modal limpamos os campos e limpamos as mensagens do modal
 */
$('#modalDeParaModelos').on('hidden.bs.modal', function () {
	clearModalInfo();
	let tableDataTable = $(".table-exibe").DataTable();
	tableDataTable.draw();
});

/**
 * Quando clicado no botão do modal ele ira disparar o post via ajax para inserir ou editar, nessa mesma função ele tem
 * uma tela de loading quando inicia o processo e finaliza.
 * O modal ele apenas continua aberto se for uma inserção e a opção de inserir mais estiver marcada no modal, caso se uma
 * alteração ele fecha por padrão.
 */
botaoAcaoModal.on('click', function () {
	toggleLoading();

	$.post(
		botaoAcaoModal.data('action'),
		{
			idModelo: $('#idModelo').val(),
			idOrigem: $('#idOrigem').val(),
			addMore: $('#addMore:checked').val(),
			requestType: botaoAcaoModal.attr('data-request'),
			...tokenCsrf
		},
		function (retorno) {
			dadosAjaxRetorno = retorno;

			if (retorno.closeModal) {
				modalDeParaModelos.modal('hide');
				addMessageModalGrid(retorno, '#container-msg-grid');
			} else {
				addMessageModalGrid(retorno, '#container-msg-modal');
			}

			if (retorno.class === 'success') {
				if (!retorno.closeModal) {
					clearInputs();
					startInput();
				}
			}
		}
	).done(function (data) {
		toggleLoading();
	});
});

/**
 * Função que adiciona as mensagens de erro, sucesso, alerta, etc...
 * @param jsonMessage json array com a mensagem e a classe, deve-se ter os seguintes parametros:
 * {
 *     class:[success,danger,warning],
 *     msg:"string com o texto da mensagem"
 *
 * }
 * @param selector Este será o local onde ira aparecer a mensagem
 */
function addMessageModalGrid(jsonMessage, selector = '.container-msg') {
	const htmlContent = isOldLayout
		? `	<div class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX" role="alert">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				${jsonMessage.msg}
			</div>`
		: `	<div class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX d-flex flex-wrap pb-5" role="alert">
				<a class="close text-decoration-none pe-4 col-12 d-flex justify-content-end mb-3" data-bs-dismiss="alert" aria-label="close" style="cursor: pointer;">
					<i class="fa-solid fa-xmark"></i>
				</a>
				<p class="col-12 text-center">
					${jsonMessage.msg}
				</p>
			</div>
		</div>`;
	$(selector).html(htmlContent);
}