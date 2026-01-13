function preparaHTMLPrintResumo() {
    let htmlPrint = "";
    htmlPrint += $('.data_print .inicio_print').html();
    htmlPrint += '<hr />';
    htmlPrint += $('#print-area-resumo_caixa').html();
    htmlPrint += '<hr />';
    if ($('#print-area-a_receber').length > 0) {
        htmlPrint += $('#print-area-a_receber').html();
        htmlPrint += '<hr />';
    }
    if ($('#print-area-movimentos_caixa').length > 0) {
        htmlPrint += $('#print-area-movimentos_caixa').html();
        htmlPrint += '<hr />';
    }

    $('#print-resumo').html(htmlPrint);
    htmlPrint = null;
}

function printResumoPDF(){
    $('#imprimir button.print-preview').unbind('click');
    $('#imprimir button.print-preview').on('click', function() {
        $(this).prop("disabled", true);
        $(this).removeClass('fa-print');

        preparaHTMLPrintResumo();

        $(this).html('<i class="fa fa-spinner fa-pulse fa-fw mb-4"></i> ' + l["carregando"]);
        $('#print-resumo').printThis({
			// printDelay: 1000,
            pageTitle: l["itens"],
            footer: 'ManyMinds'
        });

        const obj = $(this);
        setTimeout(function(){
			if (isOldLayout) {
				$(obj).addClass('fa-print')
				$(obj).html($(obj).data('imprimir_novamente'));
			} else {
				$(obj).html(
					`<span data-icon="vscode-icons:file-type-pdf2" class="iconify fs-8" style="transform: translateX(-1rem);"></span>` +
					`<span class="mt-3 fw-bold text-center txt-blue2">${$(obj).data('imprimir_novamente')}</span>`
				);
			}
			$(obj).prop("disabled", false);
        }, 1000);
    });
}

function printResumoExcel() {
    $('#imprimir button.imprimir_excel').unbind('click');
    $('#imprimir button.imprimir_excel').on('click', function() {
        $(this).prop("disabled", true);
        $(this).removeClass('fa-print');

        preparaHTMLPrintResumo();

        $(this).html('<i class="fa fa-spinner fa-pulse fa-fw mb-4"></i> ' + l["carregando"]);
        download_file(
            $('.data_resumo-principal').data('data_conciliacao') + ".xls",
            $('#print-resumo').html(),
            'application/vnd.ms-excel'
        );

        const obj = $(this);
        setTimeout(function(){
			if (isOldLayout) {
				$(obj).addClass('fa-print')
				$(obj).html($(obj).data('imprimir_novamente'));
			} else {
				$(obj).html(
					`<span data-icon="vscode-icons:file-type-excel" class="fs-8 iconify" style="transform: translateX(-1rem);"></span>` +
					`<span class="mt-3 fw-bold text-center txt-blue2">${$(obj).data('imprimir_novamente')}</span>`
				);
			}
			$(obj).prop("disabled", false);
        }, 1000);
    });
}

function prepararBotoesDeImpressao(){
	$('#imprimir button.print-document-content').unbind('click');
	$('#imprimir button.print-document-content').on('click', function() {
		/**
		 * Parâmetros necessários vinculados ao objeto html:
		 * tipo de arquivo 				= pdf, excel
		 * nome_arquivo  				= exemplo: listaDeItens ou listaDasNotasFiscais
		 * id_area_impressao 			= Exemplo: #id_print_area_itens
		 * id_area_impressao_destino	= exemplo: #id_destino_para_impressao
		 */
		imprimirConteudoParaArquivo($(this));
	});

	// esconde todos os botoes na chamada da tela
	$("div [class^='buttons_']").css("display", "none");
	$("div.buttons_resumo").css("display", "block");

	/**
	 * trata quais botoes devem aparecer de acordo com a aba ativa
	 */
	$(".nav-link").on('click', function (e){
		e.preventDefault();
		const tab = isOldLayout
			? $(this).attr('href')
			: $(this).data('bs-target');

		// esconde todos os botoes pra habilitar apenas o da respectiva aba
		$("div [class^='buttons_']").css("display", "none");

		// abas ID do resumo do movimento
		// #r-caixas-turnos-resumo
		// #r-resumo-caixa-resumo
		// #r-saldo-resumo
		// #r-itens-nota
		// #r-descricao-rejeicao-resumo

		// checa qual aba esta ativa, clicou na aba
		if(tab == '#r-itens-nota') {
			$("div.buttons_itens_da_nota").css("display", "block");
		}else{
			$("div.buttons_resumo").css("display", "block");
		}
	})
}

printResumoPDF();
printResumoExcel();
prepararBotoesDeImpressao();