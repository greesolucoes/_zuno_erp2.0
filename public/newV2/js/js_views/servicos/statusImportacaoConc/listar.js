$(document).ready(function () {
	// Função para adicionar um novo item à tabela
	$('.add-itens-table-geral').on('click', function () {
		const tbody = $('#tabela-config-emails_importacao tbody');
		const newRow = `
                <tr>
                    <td>
                        <input type="hidden" class="is_fake-no_post" name="emails_importacao-is_fake[]" value="0" />
                        <input type="text" name="emails_importacao-email[]"
                               class="form-control emails_importacao-email"
                               placeholder="${l['email']}" />
                    </td>
                    <td>
                        <button type="button" title="${l['retirarItem']}"
                                class="${isOldLayout ? 'btn btn-danger' : 'button-form danger-button'} remove-itens-table-geral"
                        >
                        	<i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>`;
		tbody.append(newRow);
		rebindRemoveButtons();
	});

	// Função para remover um item da tabela
	function rebindRemoveButtons() {
		$('.remove-itens-table-geral').off('click').on('click', function () {
			$(this).closest('tr').remove();
		});
	}

	// Inicializa os eventos para os botões já presentes
	rebindRemoveButtons();
});