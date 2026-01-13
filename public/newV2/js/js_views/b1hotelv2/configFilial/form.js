$('input[name="recebimento[replicarFiliais]"]').on('change', function() {
	const select = $('select[name="recebimento[filiais][]"]').parent('div');

	if ($('input[name="recebimento[replicarFiliais]"]:checked').val()==2){
		select.removeClass('d-none');
	} else {
		select.addClass('d-none');
	}
});

$('input[name="receita[replicarFiliais]"]').on('change', function() {
	const select = $('select[name="receita[filiais][]"]').parent('div');

	if ($('input[name="receita[replicarFiliais]"]:checked').val()==2){
		select.removeClass('d-none');
	} else {
		select.addClass('d-none');
	}
});

// Ativa ou desativa configurações de integrações junto ao SAP
$('input[type="checkbox"]').on('change', function() {
	let integracao = $('[data-integracao_tab="' + this.value + '"]');
	let abaIntegracao = $(`#${integracao.attr('aria-controls')}`);

	if (this.checked) {
		integracao.removeClass('disabled');
	} else {
		integracao.addClass('disabled');
		integracao.removeClass('active');
		abaIntegracao.removeClass('active');
	}
});


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
			});

			$('select[name="config-cartoes-card-service[dePara]['+index+'][idB1HV2Cartoes]"]').select2Simple();

			$(aba + ' tfoot').attr('data-count', index);
		})
	},

	/**
	 * Função genérica para quando um botão for acionado
	 * @param btn			Seletor do botão
	 * @param dataText		Texto da mensagem a ser exibida
	 * @param title			Título da mensagem a ser exibida
	 * @param textSuccess	Texto da mensagem de sucesso
	 */
	ajaxBtnAction: function (btn, dataText, title, textSuccess) {
		const actionbtn = $(btn);
		actionbtn
			.unbind('click')
			.on("click", function (e) {
				e.preventDefault();

				const url = actionbtn.data('url');
				const textReset = actionbtn.data(dataText);

				swal({
					title: title,
					text: textReset,
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: l["reiniciar"],
					cancelButtonText: l["cancelar!"]
				}).then(() => {
					toggleLoading();

					$.get(url, function(ret) {
						let titulo = l["erro"];
						let texto = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
						let tipo = "error";

						if (ret) {
							titulo = l['sucesso!'];
							texto = textSuccess;
							tipo = "success";
						}

						swal(titulo, texto, tipo);
					});

					toggleLoading();
				}).catch(swal.noop);
			})
	},

	// função para esconder as configurações de integrações não feitas
	handleIntegracoes: function () {
		// primeiro esconde todas
		document
			.querySelectorAll("a.nav-link")
			.forEach(function (e) {
				e.classList.add('disabled');
			})

		// depois mostra as que estão habilitadas por empresa
		document
			.querySelectorAll("#integracoes .row.item input[type='checkbox']")
			.forEach(function (e) {
				let integracao = $("[data-integracao_tab='" + e.value + "']");

				e.checked ? integracao.removeClass('disabled') : integracao.addClass('disabled');
			});

		//data-fixed significa que sempre vai aparecer em todos os momentos
		$("[data-fixed='1']").removeClass('disabled');
	}
}

// de-paras disponíveis
// adicionar aqui caso haja outro
const btnsDePara = [
	{ 'btn': 'config-cartoes-card-service', 'aba': '#configCartaoCardService' },
	{ 'btn': 'receita', 'aba': '#receitas-aba' },
	{ 'btn': 'recebimento', 'aba': '#recebimentos-aba'  }
];

// para cada de-para, cria-se a funcionalidade
btnsDePara.forEach(function(dePara) { formUtils.addItemDePara(dePara.btn, dePara.aba) });

// função que se executa ao carregar a view
(function() {
	// Prepara o Reprocessamento de arquivos XML
	formUtils.ajaxBtnAction(
		'button.reprocessarXmlNfs',
		'text_reprocessa_xml',
		l["b1hv2reprocessarArquivosXmlNfs"],
		l["oProximoProcessamentoDeArquivosXMLdeNotasFicaisDeServicoOcorreraSobreOsArquivosPresentesNaPastaDeArquivosProcessados"]
	);

	formUtils.ajaxBtnAction(
		'button.reimportarImportacaoDiaria',
		'text_reimportar_importacao_diaria',
		l["reimportarArquivosDaPastaDeImportaçãoDiária"],
		l["osArquivosDaPastaCadastradaSerãoProcessadosNoPróximoCicloDeIntegração"]
	);

	formUtils.handleIntegracoes();
})();
