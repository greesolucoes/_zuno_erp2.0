$(document).ready(function () {
    // Inicializa os selects (Turno, Departamento e Gerente)
    $(".select_colaborador").select2({
        placeholder: "Selecione um Colaborador",
        language: "pt-BR",
        allowClear: true
    });
    $(".select_aprovador").select2({
        placeholder: "Selecione um Aprovador",
        language: "pt-BR",
        allowClear: true
    });
    $(".select_tipo_de_abono").select2({
        placeholder: "Selecione um Tipo de ábono",
        language: "pt-BR",
        allowClear: true
    });

    /**
     * Formata o valor digitado para o padrão HH:MM.
     * Enquanto o usuário digita, se já houver 3 ou 4 dígitos sem ":",
     * o script insere o ":" após os dois primeiros dígitos.
     * Também garante que, se já existir o ":", os componentes fiquem com 2 dígitos.
     */
    function formatHora(value) {
        if (!value) return "";
        // Remove caracteres indesejados, mantendo apenas dígitos e ":"
        let clean = value.replace(/[^\d:]/g, "");
        if (clean.indexOf(":") > -1) {
            // Se já houver ":", separe e padronize cada parte
            let parts = clean.split(":");
            let hours = parts[0].substring(0,2);
            let minutes = parts[1].substring(0,2);
            hours = hours.padStart(2, "0");
            minutes = minutes.padEnd(2, "0").substring(0,2);
            return hours + ":" + minutes;
        } else {
            // Se não houver ":" e tiver pelo menos 3 dígitos, insere ":" após os 2 primeiros dígitos
            if (clean.length >= 3) {
                let hours = clean.substring(0,2);
                let minutes = clean.substring(2,4);
                return hours + ":" + minutes;
            }
            return clean;
        }
    }

    // Atualiza o total da tabela (soma os horários de cada linha)
    function updateTotal() {
        let totalMinutes = 0;
        $("table#conteudoTable tbody tr").each(function () {
            let timeStr = $(this).find("input.quantidade").val();
            let parts = timeStr.split(":");
            if (parts.length === 2) {
                let hours = parseInt(parts[0], 10) || 0;
                let minutes = parseInt(parts[1], 10) || 0;
                totalMinutes += hours * 60 + minutes;
            }
            // Atualiza o subtotal (mantém o valor já formatado)
            $(this).find("input.subTotal").val($(this).find("input.quantidade").val());
        });
        // Formata o total para HH:MM
        let totalHours = Math.floor(totalMinutes / 60);
        let totalMins = totalMinutes % 60;
        $("table#conteudoTable input.totalGeral").val(
            ("0" + totalHours).slice(-2) + ":" + ("0" + totalMins).slice(-2)
        );
    }

    // Enquanto o usuário digita, formata o valor automaticamente
    $("table#conteudoTable").on("input", "input.quantidade", function () {
        let current = $(this).val();
        let formatted = formatHora(current);
        if (formatted !== current) {
            $(this).val(formatted);
        }
        updateTotal();
    });

    // Vincula o evento de remoção de linha
    function bindRemoveItem() {
        $("table#conteudoTable button.removeItens").off("click").on("click", function () {
            let $row = $(this).closest("tr");
            $row.fadeOut(200, function () {
                $(this).remove();
                updateTotal();
            });
        });
    }
    bindRemoveItem();


    // Adiciona uma nova linha clonando a primeira e limpando os campos
    $("table#conteudoTable button.addItens").on("click", function () {
        let $firstRow = $("table#conteudoTable tbody tr").first();
        let $newRow = $("<tr>").html($firstRow.html());
        $newRow.find("input").val("");
        $newRow.find("button.removeItens").prop("disabled", false);
        $("table#conteudoTable tbody").append($newRow);
        bindRemoveItem();
    });

    // Contagem de caracteres para o campo "Observações"
    function updateCharCount() {
        const maxLen = 254;
        const len = $("#observacoes").val().length;
        $("#numChars").text((maxLen - len) + " caracteres restantes.");
    }
    $("#observacoes").on("keyup", updateCharCount);
    updateCharCount();
});
