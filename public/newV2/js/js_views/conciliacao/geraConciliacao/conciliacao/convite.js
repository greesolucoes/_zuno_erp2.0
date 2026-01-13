/**
 * Function removeItensContaConvite
 * Remove itens na tabela de convite
 */
function removeItensContaConvite() {
    $('table#r-tabela-convite button.r-remove-itens-convite').off('click');
    $('table#r-tabela-convite button.r-remove-itens-convite').on("click", function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCamposConvite();
            triggerSomaCamposConvite();
        });
    });
}

/**
 * Function addItensContasConvite
 * Adiciona itens na tabela de convite
 */
function addItensContasConvite() {
    $('table#r-tabela-convite button#r-add-itens-convite').off("click");
    $('table#r-tabela-convite button#r-add-itens-convite').on("click", function () {
        var tbody  = $('table#r-tabela-convite tbody');
        var modelo = $(tbody).find('tr').first().html();

        $(tbody).append('<tr>' + modelo + '</tr>');

        allFunctions();
        removeItensContaConvite();

        somaCamposConvite();
        triggerSomaCamposConvite();
    });
}

/**
 * Function somaCamposConvite
 * Soma o total do convite distribuido de acordo com cada linha
 */
function somaCamposConvite() {
    var campos        = $("table#r-tabela-convite tbody tr input.r-valor-convite");
    var valorTotal    = 0;
    var casasPreco    = $('.data_convite').data('casas_preco');

    var cifrao = $(".data_convite").data("cifrao");
    var cifrao_is_prefixo = $(".data_convite").data("cifrao_is_prefixo");
    var separador_decimal = $(".data_convite").data("separador_decimal");
    var separador_milhar = $(".data_convite").data("separador_milhar");

    $.each(campos, function (idCampo, campo) {
        var valor = stringParaFloat($(campo).val(), separador_decimal, true);
        if(is_empty_numeric(valor)) valor = 0;

        valorTotal += valor;
    });

    $('.r-total-distribuido-convite').text(formataDecimal(valorTotal, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

/**
 * Function triggerSomaCamposConvite
 * Prepara gatilhos que ativam a soma do convite distribuido
 */
function triggerSomaCamposConvite() {
    var tabela = $("table#r-tabela-convite tbody tr");
    $(tabela).find("input.r-valor-convite").off("keyup").on("keyup", function() {
        somaCamposConvite();
    });
}

addItensContasConvite();
removeItensContaConvite();

somaCamposConvite();
triggerSomaCamposConvite();

allFunctions();