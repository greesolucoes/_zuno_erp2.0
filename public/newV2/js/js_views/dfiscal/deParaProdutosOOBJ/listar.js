/**
 * pega a url que sera responsavel pelo merge de informações do de-para (Salvar e Alterar)
 * @type {*|jQuery}
 */
let urlMergeDeParaProdutosOOBJ = $('.data_views').data('url_merge_de_para_produtos_oobj');
/**
 * select de produtos dentro do modal
 * @type {*|jQuery|HTMLElement}
 */
let selectProduto = $("#idProdutos");
/**
 * Input do codigo oobj dentro do modal
 * @type {*|jQuery|HTMLElement}
 */
let inputOOBJ = $("#idOOBJ");
/**
 * div do modal de de-para de produtos oobj
 * @type {*|jQuery|HTMLElement}
 */
let modalDeParaProdutosOOBJ = $('#modalDeParaProdutosOOBJ');
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
function startInput(opts='') {

	let param = '';
	if (opts!=''){
		param = {id:opts.idProdutos,text:opts.nomeProduto};
	}
	selectProduto.data('init', param);
	selectProduto.select2Ajax();
}

/**
 * Função para limpar campos de texto e select do modal
 */
function clearInputs(){
	$('#idProdutosAnterior').val('');
	$('#idOOBJAnterior').val('');
	selectProduto.select2Reset();
	selectProduto.find('option').remove();
	inputOOBJ.val('');
}

/**
 * Função para limpar as informações do modal
 */
function clearModalInfo(){
	clearInputs();
	modalMsgs.html('');
	botaoAcaoModal.html('');
	botaoAcaoModal.removeAttr('data-action')
	inputContinuarAdd.hide();
}

/**
 * Função que ira abrir o modal para inserir o item do de-para
 */
function insertItem(){
	botaoAcaoModal.attr('data-action',urlMergeDeParaProdutosOOBJ);
	botaoAcaoModal.html(l['salvar']);
	inputContinuarAdd.show();
	modalDeParaProdutosOOBJ.modal('show');
	startInput();
}

/**
 * Função que ira abrir o modal já com as informações preenchidas e permitindo o utilizador editar
 * @param item Recebemos os dados para buscar e editar o cadastro
 */
function editarItem(item){
	let idoobj = $(item).data('idoobj');
	let idprodutos = $(item).data('idprodutos');
	let urlGet = $(item).data('url');

	params = {
		idProdutos:idprodutos,
		idOOBJ:idoobj,
		addMore: false
	};

	Object.assign(params, tokenCsrf);
	$.post(
		urlGet,
		params,
		function(retorno){
			botaoAcaoModal.attr('data-action',urlMergeDeParaProdutosOOBJ);
			botaoAcaoModal.html(l['atualizar']);
			inputContinuarAdd.hide();
			modalDeParaProdutosOOBJ.modal('show');
			$('#idProdutosAnterior').val(retorno.idProdutosAnterior);
			$('#idOOBJAnterior').val(retorno.idOOBJAnterior);
			$('#idOOBJ').val(retorno.idOOBJ);
			startInput(retorno);
		}
	);
}

/**
 * Função que ira deletar um de-para de produtos oobj
 * @param item Recebemos os dados necessarios para deletar o item
 */
function removerItem(item){
	let obj = $(item);
	let tableDataTable = $(".table-exibe").DataTable();
	let paramsAjax = { idProdutos: $(obj).data("idprodutos"), idOOBJ: $(obj).data("idoobj") }
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
$('#modalDeParaProdutosOOBJ').on('hidden.bs.modal', function() {
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
botaoAcaoModal.click(function(){
	toggleLoading();

	params = {
		idProdutos:$('#idProdutos').val(),
		idOOBJ:$('#idOOBJ').val(),
		idProdutosAnterior:$('#idProdutosAnterior').val(),
		idOOBJAnterior:$('#idOOBJAnterior').val(),
		addMore:$('#addMore:checked').val()
	};

	Object.assign(params, tokenCsrf);
	$.post(
		botaoAcaoModal.data('action'),
		params,
		function(retorno){
			dadosAjaxRetorno = retorno;
			if (retorno.closeModal){
				modalDeParaProdutosOOBJ.modal('hide');
				addMessageModalGrid(retorno,'#container-msg-grid');
			}else{
				addMessageModalGrid(retorno,'#container-msg-modal');
			}
			if (retorno.class === 'success'){
				if (!retorno.closeModal){
					clearInputs();
					startInput();
				}
			}
		}
	).done(function(data) {
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
function addMessageModalGrid(jsonMessage, selector = '.container-msg'){
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

