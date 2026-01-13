/**
 * Function removeItensContaAReceber
 * Remove itens na tabela de contas a receber
 */
function removeItensContaAReceber() {
    $('table#r-tabela-a-receber button.r-remove-itens-a-receber').off('click');
    $('table#r-tabela-a-receber button.r-remove-itens-a-receber').on("click", function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCamposAReceber();
            triggerSomaCamposAReceber()
        });
    });
}

/**
 * Function addItensContasAReceber
 * Adiciona itens na tabela de contas a receber
 */
function addItensContasAReceber() {
    $('table#r-tabela-a-receber button#r-add-itens-a-receber').off("click");
    $('table#r-tabela-a-receber button#r-add-itens-a-receber').on("click", function () {
        var tbody  = $('table#r-tabela-a-receber tbody');
        var modelo = $(tbody).find('tr').first().html();

        $(tbody).append('<tr>' + modelo + '</tr>');

        allFunctions();
        removeItensContaAReceber();

        somaCamposAReceber();
        triggerSomaCamposAReceber();
    });
}

/**
 * Function somaCamposAReceber
 * Soma o total do à receber distribuido de acordo com cada linha
 */
function somaCamposAReceber() {
    var campos        = $("table#r-tabela-a-receber tbody tr input.r-valor-a-receber");
    var valorTotal    = 0;
    var casasPreco    = $('.data_contas_receber').data('casas_preco');

    var cifrao = $(".data_contas_receber").data("cifrao");
    var cifrao_is_prefixo = $(".data_contas_receber").data("cifrao_is_prefixo");
    var separador_decimal = $(".data_contas_receber").data("separador_decimal");
    var separador_milhar = $(".data_contas_receber").data("separador_milhar");

    $.each(campos, function (idCampo, campo) {
        var valor = stringParaFloat($(campo).val(), separador_decimal, true);
        if(is_empty_numeric(valor)) valor = 0;

        valorTotal += valor;
    });

    $('.r-total-distribuido-a-receber').text(formataDecimal(valorTotal, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

/**
 * Function triggerSomaCamposAReceber
 * Prepara gatilhos que ativam a soma do À Receber distribuido
 */
function triggerSomaCamposAReceber() {
    var tabela = $("table#r-tabela-a-receber tbody tr");
    $(tabela).find("input.r-valor-a-receber").off("keyup").on("keyup", function() {
        somaCamposAReceber();
    });
}

addItensContasAReceber();
removeItensContaAReceber();

somaCamposAReceber();
triggerSomaCamposAReceber();

allFunctions();