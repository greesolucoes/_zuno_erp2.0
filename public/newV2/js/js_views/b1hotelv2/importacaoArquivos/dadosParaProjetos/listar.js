const btnUpArquivo = $('#buttonImportacao');

// ativação do modal de importação de planilha
$('#btnImportarPlanilha').on('click', (e) => {
	e.preventDefault();
	// tira o que estiver presente do input file
	$('#arquivoImportacao').val('');

	$('#modalArquivo').modal('toggle');
})

/**
 * Ao adicionar um arquivo válido, o botão é habilitado.
 * Caso haja a substituição do arquivo válido, o botão é desabilitado.
 */
$('#arquivoImportacao').on('change', function() {
	let nomeArquivo = $('#arquivoImportacao').val();

	// habilita o botão para o click
	btnUpArquivo.addClass('disabled');

	if (nomeArquivo.substr(nomeArquivo.length - 4) === '.xls' ||
		nomeArquivo.substr(nomeArquivo.length - 5) === '.xlsx'
	) {
		btnUpArquivo.removeClass('disabled');
	}
})

/**
 * Ao clicar no botão, pega o arquivo do input:file e o envia para processamento.
 * A rota então retorna um JSON string, apresentando uma mensagem ao usuário.
 */
btnUpArquivo
	.unbind('click')
	.on('click', function(e) {
		e.preventDefault();
		let data = new FormData($('#formImportacaoArquivo')[0]);
		let tableDataTable = $(".table-exibe").DataTable();
		const url = $($(this)).data('url_importacao_arquivos');
		toggleLoading();

        Object.entries(tokenCsrf).forEach(([key, value]) => {
            data.append(key, value);
        });

        $.ajax({
            url: url,
            type: 'POST',
            data,
            cache: false,
            contentType: false,
            processData: false,
            success: function(ret) {
                ret = JSON.parse(ret);
                swal(
                    ret['titulo'],
                    ret['text'],
                    ret['class']
                );

                if (ret['class'] == 'success') {
                    // tira o que estiver presente do input file
                    $('#arquivoImportacao').val('');

                    // esconde o modal
                    $('#modalArquivo').modal('toggle');

                    // recarrega o datatable
                    tableDataTable.draw();
                }
            },
            complete: function(completeData) {
                $('#formImportacaoArquivo')[0].reset();
            }
        }).then(function () {
            toggleLoading();
        }).catch(swal.noop);
	});
