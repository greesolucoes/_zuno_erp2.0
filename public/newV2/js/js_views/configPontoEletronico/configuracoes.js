$(document).ready(function(){
    // Grupo de checkboxes
    const checkboxGroup = [
        $("#reconhecimento_facial")[0],
        $("#ponto_manual")[0],
        $("#ponto_qr_code")[0],
        $("#ponto_codigo_barras")[0]
    ];

    // Função para atualizar o estado (disabled) dos checkboxes
    function updateCheckboxStates() {
        const anyChecked = checkboxGroup.some(cb => $(cb).prop("checked"));
        if (anyChecked) {
            checkboxGroup.forEach(cb => {
                if (!$(cb).prop("checked")) {
                    $(cb).prop("disabled", true);
                }
            });
        } else {
            checkboxGroup.forEach(cb => {
                $(cb).prop("disabled", false);
            });
        }
    }

    // Vincula o evento change para cada checkbox
    checkboxGroup.forEach(function(checkbox) {
        $(checkbox).on("change", function(){
            updateCheckboxStates();
        });
    });

    // Executa a função ao carregar a página para aplicar a lógica inicialmente
    updateCheckboxStates();
});
