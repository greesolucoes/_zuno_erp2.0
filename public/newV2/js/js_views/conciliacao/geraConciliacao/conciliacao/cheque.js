/**
 * Function criaSelectsCheque
 * Cria o Select2 de todos os selects da página e altera o data-init deles para nada
 * (pode ocasionar problemas se não for feito isso após a 1 chamada do select 2)
 */
function criaSelectsCheque(){
    $(".r-select_pais-cheque").select2Ajax();
    $(".r-select_pais-cheque").data('init', '');

    $(".r-select_banco-cheque").select2Ajax();
    $(".r-select_banco-cheque").data('init', '');
}

/**
 * Function removeItensCheque
 * Remove itens na tabela de cheques
 */
function removeItensCheque() {
    $('table#r-tabela-cheque button.r-remove-itens-cheque').off('click');
    $('table#r-tabela-cheque button.r-remove-itens-cheque').on("click", function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCamposCheque();
            triggerSomaCamposCheque();
        });
    });
}

/**
 * Function addItensCheque
 * Adiciona itens na tabela de cheques
 */
function addItensCheque() {
    $('table#r-tabela-cheque button#r-add-itens-cheque').off("click");
    $('table#r-tabela-cheque button#r-add-itens-cheque').on("click", function () {
        var tbody  = $('table#r-tabela-cheque tbody');
        var modelo = $(tbody).find('tr').first().html();

        $(tbody).append('<tr>' + modelo + '</tr>');
        $(tbody).find('select').select2Reset();

        allFunctions();
        criaSelectsCheque();
        removeItensCheque();

        somaCamposCheque();
        triggerSomaCamposCheque();
    });
}

/**
 * Function somaCamposCheque
 * Soma o total do cheque distribuido de acordo com cada linha
 */
function somaCamposCheque() {
    var campos        = $("table#r-tabela-cheque tbody tr input.r-valor-cheque");
    var valorTotal    = 0;
    var casasPreco    = $('.data_cheque').data('casas_preco');

    var cifrao = $(".data_cheque").data("cifrao");
    var cifrao_is_prefixo = $(".data_cheque").data("cifrao_is_prefixo");
    var separador_decimal = $(".data_cheque").data("separador_decimal");
    var separador_milhar = $(".data_cheque").data("separador_milhar");

    $.each(campos, function (idCampo, campo) {
        var valor = stringParaFloat($(campo).val(), separador_decimal, true);
        if(is_empty_numeric(valor)) valor = 0;

        valorTotal += valor;
    });

    $('.r-total-distribuido-cheque').text(formataDecimal(valorTotal, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

/**
 * Function triggerSomaCamposCheque
 * Prepara gatilhos que ativam a soma do cheque distribuido
 */
function triggerSomaCamposCheque() {
    var tabela = $("table#r-tabela-cheque tbody tr");
    $(tabela).find("input.r-valor-cheque").off("keyup").on("keyup", function() {
        somaCamposCheque();
    });
}

addItensCheque();
criaSelectsCheque();
removeItensCheque();

somaCamposCheque();
triggerSomaCamposCheque();

allFunctions();