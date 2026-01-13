// Função para adicionar mensagens no modal
function addMessage(jsonMessage, selector = '.container-msg'){
	$(selector).html(
		`<div class="espacamento"></div>
		
		<div class="alert alert-${jsonMessage.class} alert-dismissable animated flipInX" role="alert">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			${jsonMessage.msg}
		</div>`
	);
}

// Código para remover a mensagem do modal ao reutilizar o mesmo
$(document).on('click', '.btn.btn-success, .editarLinha', function() {
	$(".container-msg").empty();
});

/**
 * O métodos 'refazerGeracaoOneStream' e 'cancelarGeracaoOneStream' são basicamente os mesmos
 * Estão separados caso haja necessidade de especificações de funcionalidade
 */
function refazerGeracaoOneStream() {
	$('.buttonAcaoRefazerOneStream')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url") + $(obj).data("id");
			let tableDataTable = $(".table-exibe").DataTable();

			swal({
				title: l["gerarNovoArquivo"],
				text: l["desejaGerarNovamenteOArquivoOneStreamComOsDadosDestaRequisicao"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				$.get(url, function(ret) {
					ret = JSON.parse(ret);
					toggleLoading();

					let titulo = l["erro"];
					let texto = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
					let tipo = "error";

					if (ret) {
						titulo = ret['titulo'];
						texto = ret['text'];
						tipo = ret['class'];
					}

					swal(titulo, texto, tipo);
				})

				toggleLoading();
				tableDataTable.draw();
			}).catch(swal.noop);
		});
}

function cancelarGeracaoOneStream() {
	$('.buttonAcaoCancelaOneStream')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url") + $(obj).data("id");
			let tableDataTable = $(".table-exibe").DataTable();

			swal({
				title: l["cancelarGeracao"],
				text: l["desejaCancelarAGeracaoDesteArquivoOneStream"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				$.get(url, function(ret) {
					ret = JSON.parse(ret);
					toggleLoading();

					let titulo = l["erro"];
					let texto = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
					let tipo = "error";

					if (ret) {
						titulo = ret['titulo'];
						texto = ret['text'];
						tipo = ret['class'];
					}

					swal(titulo, texto, tipo);
				})

				toggleLoading();
				tableDataTable.draw();
			}).catch(swal.noop);
		});
}

function solicitarGeracaoOneStream() {
	$('.novoArquivoOneStream')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);

			swal({
				title: l["gerarNovoArquivo"],
				text: $(obj).data("text_novo_arquivo"),
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
					{
						"dataInicioPedido": $('#dataInicialOneStream').val(),
						"dataFimPedido": $('#dataFimOneStream').val(),
						"layoutOneStream": $('#layoutOneStream').val(),
					},
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						toggleLoading();
						$(".table-exibe").DataTable().draw();
					}
				);
			}).catch(swal.noop);
		});
}
solicitarGeracaoOneStream();

// inicializa o select de layouts na tela de geração de arquivos
function initSelects() {
	$("select#layoutOneStream").select2Ajax();
	$("select#layoutOneStream").data('init', '');
}

initSelects();

// LAYOUTS
const nomeArquivos = $('#nomeArquivosGerados');
const nomeArquivosTemplate ='SAPOST056 {{CC}} YYMM {{actualInterco}} {{workflow}} YYMMDDHHMMSS.txt';

function toggleStatusLayout() {
	$('.buttonAcaoToggleLayout')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);

			swal({
				title: $(obj).prop('title'),
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
					$(obj).data("url") + $(obj).data("layout_id"),
					null,
					'text',
					null,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["titulo"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						$(".table-exibe").DataTable().draw();

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});
}

async function ajaxEditarLayout(elemento){
	toggleLoading();
	const guidLayout = $(elemento).data('layout_id');

	await $.post(
		$(elemento).data('url'),
		{
			guidLayout,
			...tokenCsrf
		},
		function(retorno){
			$('#modalCadastroLayout input[name="idLayout"]').val(guidLayout);
			$('#modalCadastroLayout input[name="descricaoLayout"]').val(retorno.descricaoLayout);
			$('#modalCadastroLayout select[name="tipoSeparador"]').val(retorno.tipoSeparador);
			$('#modalCadastroLayout select[name="tipoSeparador"]').select2();
			$('#modalCadastroLayout input[name="siglaPais"]').val(retorno.siglaPais);
			$('#modalCadastroLayout select[name="actualInterco"]').val(retorno.actualOrInterco);
			$('#modalCadastroLayout select[name="actualInterco"]').select2();
			$('#modalCadastroLayout input[name="workflow"]').val(retorno.workflow);
		}
	);

	$('#modalCadastroLayout').modal('show');
	toggleLoading();
}

function salvar(){
	$('#modalCadastroLayout').on('hidden.bs.modal', function () {
		$('#div-alert>a.close').first().trigger('click');
		$('#modalCadastroLayout input[name="idLayout"]').val('');
		$('#modalCadastroLayout input[name="descricaoLayout"]').val('');
		$('#modalCadastroLayout select[name="tipoSeparador"]').select2();
		$('#modalCadastroLayout input[name="siglaPais"]').val('BR');
		$('#modalCadastroLayout select[name="actualInterco"]').select2();
		$('#modalCadastroLayout input[name="workflow"]').val('');
	});

	$('#modalCadastroLayout .salvar').click(async function() {
		if (!validarPreenchimentoCampos()) {
			toggleLoading();

			await $.post(
				$(this).data('action'),
				{
					guidLayout: $('#modalCadastroLayout input[name="idLayout"]').val(),
					descricaoLayout: $('#modalCadastroLayout input[name="descricaoLayout"]').val(),
					tipoSeparador: $('#modalCadastroLayout select[name="tipoSeparador"]').val(),
					siglaPais: $('#modalCadastroLayout input[name="siglaPais"]').val(),
					actualInterco: $('#modalCadastroLayout select[name="actualInterco"]').val(),
					workflow: $('#modalCadastroLayout input[name="workflow"]').val(),
					...tokenCsrf
				},
				function (retorno) {
					$(".table-exibe").DataTable().draw();

					addMessage(retorno);

					if (retorno.edit == 0) {
						$('#modalCadastroLayout input[name="idLayout"]').val('');
						$('#modalCadastroLayout input[name="descricaoLayout"]').val('');
						$('#modalCadastroLayout select[name="tipoSeparador"]').val('');
						$('#modalCadastroLayout input[name="siglaPais"]').val('BR');
						$('#modalCadastroLayout select[name="actualInterco"]').val('');
						$('#modalCadastroLayout input[name="workflow"]').val('');
					}
					toggleLoading();
					$('.nomeArquivo').trigger('change');

			});
		}
	});
}

function atualizarNomeArquivoLayout() {
	$('#modalCadastroLayout').on('show.bs.modal', function () {
		$('.nomeArquivo').trigger('change');
	});


	$('.nomeArquivo').on('change keyup', function(){
		if (($('#workflow').val() != '') && ($('#actualInterco').val() != '') && ($('#siglaPais').val() != '')) {
			nomeArquivos.show();
		} else {
			nomeArquivos.hide();
		}

		$('.select2-selection__clear').hide();
		nomeArquivos.html(nomeArquivosTemplate);
		nomeArquivos.html(
			nomeArquivos
				.text()
				.replace('{{workflow}}', `<b>${$('#workflow').val().toUpperCase()}</b>`)
				.replace('{{actualInterco}}', `<b>${$('#actualInterco').val().toUpperCase()}</b>`)
				.replace('{{CC}}', `<b>${$('#siglaPais').val().toUpperCase()}</b>`)
		);
	});
}

function validarPreenchimentoCampos() {
	// começa com letras, tem letras, números e undeline no meio e deve terminar com números ou letras
	if (/^[A-Za-z]+[A-Za-z0-9_]*[a-zA-Z0-9]*$/.test($('#modalCadastroLayout input[name="descricaoLayout"]').val()) === false) {
		swal({
			title: l["atenção!"],
			text: l["oCampoNomeDoLayoutDeveConterApenasLetrasNumerosOuUnderlines"],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		})

		return true;
	}

	if (/[A-Za-z]{2}/.test($('#modalCadastroLayout input[name="siglaPais"]').val()) === false) {
		swal({
			title: l["atenção!"],
			text: l["oCampoPaisDeveConterDuasLetras"],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		})

		return true;
	}

	if (/[A-Za-z0-9]{3}/.test($('#modalCadastroLayout input[name="workflow"]').val()) === false) {
		swal({
			title: l["atenção!"],
			text: l["oCampoWorkflowDeveConterTresLetrasENumeros"],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		})

		return true;
	}

	return false
}

salvar();
atualizarNomeArquivoLayout();
// LAYOUTS