/**
 * Function removeItensContaPermutas
 * Remove itens na tabela de permutas
 */
function removeItensContaPermutas() {
    $('table#r-tabela-permuta button.r-remove-itens-permuta').off('click');
    $('table#r-tabela-permuta button.r-remove-itens-permuta').on("click", function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCamposPermutas();
            triggerSomaCamposPermutas();
        });
    });
}

/**
 * Function addItensPermutas
 * Adiciona itens na tabela de permutas
 */
function addItensPermutas() {
    $('table#r-tabela-permuta button#r-add-itens-permuta').off("click");
    $('table#r-tabela-permuta button#r-add-itens-permuta').on("click", function () {
        var tbody  = $('table#r-tabela-permuta tbody');
        var modelo = $(tbody).find('tr').first().html();

        $(tbody).append('<tr>' + modelo + '</tr>');
        $(tbody).find('select').select2Reset();

        allFunctions();
        removeItensContaPermutas();

        somaCamposPermutas();
        triggerSomaCamposPermutas();
    });
}

/**
 * Function somaCamposAReceber
 * Soma o total do à receber distribuido de acordo com cada linha
 */
function somaCamposPermutas() {
    var campos        = $("table#r-tabela-permuta tbody tr input.r-valor-permuta");
    var valorTotal    = 0;
    var casasPreco    = $('.data_permutas').data('casas_preco');

    var cifrao = $(".data_permutas").data("cifrao");
    var cifrao_is_prefixo = $(".data_permutas").data("cifrao_is_prefixo");
    var separador_decimal = $(".data_permutas").data("separador_decimal");
    var separador_milhar = $(".data_permutas").data("separador_milhar");

    $.each(campos, function (idCampo, campo) {
        var valor = stringParaFloat($(campo).val(), separador_decimal, true);
        if(is_empty_numeric(valor)) valor = 0;

        valorTotal += valor;
    });

    $('.r-total-distribuido-permuta').text(formataDecimal(valorTotal, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

/**
 * Function triggerSomaCamposPermutas
 * Prepara gatilhos que ativam a soma do À Receber distribuido
 */
function triggerSomaCamposPermutas() {
    var tabela = $("table#r-tabela-permuta tbody tr");
    $(tabela).find("input.r-valor-permuta").off("keyup").on("keyup", function() {
        somaCamposPermutas();
    });
}

addItensPermutas();
removeItensContaPermutas();

somaCamposPermutas();
triggerSomaCamposPermutas();

allFunctions();
