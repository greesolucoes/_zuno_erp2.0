//aqui é chamado a função de download do excel
$('#imprimir button.print-document-content').unbind('click');
$('#imprimir button.print-document-content').on('click', function() {
	/**
	 * Parâmetros necessários vinculados ao objeto html:
	 * tipo de arquivo 				= pdf,pdf_relatorio, excel
	 * nome_arquivo  				= exemplo: listaDeItens ou listaDasNotasFiscais
	 * id_area_impressao 			= Exemplo: #id_print_area_itens
	 * id_area_impressao_destino	= exemplo: #id_destino_para_impressao
	 */
	imprimirConteudoParaArquivo($(this));
});