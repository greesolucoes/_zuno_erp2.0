$(document).ready(function () {
    $(document).on("input", "#tolerancia", function () {
        let current = $(this).val();

        // Remove tudo que não for número
        current = current.replace(/\D/g, "");

        // Limita a 4 caracteres numéricos
        if (current.length > 4) {
            current = current.substring(0, 4);
        }

        // Adiciona o ":" automaticamente entre os números
        if (current.length > 2) {
            current = current.substring(0, 2) + ":" + current.substring(2);
        }

        // Atualiza o valor formatado no input
        $(this).val(current);
    });

    // Se o campo já vem preenchido, corrigir o formato ao carregar a página
    let toleranciaValue = $("#tolerancia").val().replace(/\D/g, ""); // Remove caracteres não numéricos
    if (toleranciaValue.length === 4) {
        $("#tolerancia").val(toleranciaValue.substring(0, 2) + ":" + toleranciaValue.substring(2));
    }
});
