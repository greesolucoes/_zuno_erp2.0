$(document).ready(function () {
    // 1) Inicializa Select2 no <select class="select_cargo"> (campo encryption)
    $(".select_cargo").select2({
        placeholder: "Cargo", // Texto exibido quando vazio
        language: "pt-BR",
        allowClear: true
    });

    // 2) Transforma o input[type=file] em "buttonFile" (caso você use esse plugin)
    $('input.input_files').buttonFile();

    // 3) Submissão AJAX do formulário (se quiser enviar sem recarregar página)
    $('#formNovoEmail').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                // Se o controller devolveu { success: true, message: "..."}
                if (response.success) {
                    swal("Sucesso!", response.message, "success");
                } else {
                    // Se for success=false, mas com status 200
                    swal("Erro!", response.message, "error");
                }
            },
            error: function (xhr, status, error) {
                // Se caiu aqui, tente ler o JSON de erro
                if (xhr.status === 422) {
                    // Erros de validação do Laravel
                    let errors = xhr.responseJSON.errors;
                    swal("Validação", "Verifique os campos e tente novamente.", "warning");
                } else {
                    // Pode ser erro de conexão (500) ou outro
                    let msg = "Ocorreu um erro ao cadastrar!";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    swal("Erro", msg, "error");
                }
            }
        });
    });

    // 4) Controla o download de CSV de erros
    controlaDownloadsCSV();
});

/**
 * Se você precisa do botão "baixar_csv_erro" para baixar um CSV de erros,
 * mantemos esta função.
 */
function controlaDownloadsCSV() {
    $("button.baixar_csv_erro").off('click').on('click', function () {
        let url = $(this).data('url');

        // is_empty() é alguma função que você possua no seu projeto
        // tokenCsrf é objeto com CSRF, ex: let tokenCsrf = { '_token': '...' };
        if (!is_empty(url, 1)) {
            let params = {
                'errors': $("#csv_json").text(),
                ...tokenCsrf
            };

            let form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", url);
            form.setAttribute("target", "_blank");

            for (let key in params) {
                if (!params.hasOwnProperty(key)) continue;
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = params[key];
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    });
}
