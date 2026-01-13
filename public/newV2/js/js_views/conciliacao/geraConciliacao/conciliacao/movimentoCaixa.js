/**
 * Function criaSelectsMovimentoCaixa
 * Cria o Select2 de todos os selects da página e altera o data-init deles para nada
 * (pode ocasionar problemas se não for feito isso após a 1 chamada do select 2)
 */
function criaSelectsMovimentoCaixa(){
    $(".r-select_tipo-movimento-caixa").select2({
        placeholder: l["tipoDoMovimento"],
        language: _lang,
        allowClear: true
    });
}

/**
 * Function removeItensContaMovimentoCaixa
 * Remove itens na tabela de movimento de caixa
 */
function removeItensContaMovimentoCaixa() {
    $('table#r-tabela-movimento-caixa button.r-remove-itens-movimento-caixa').off('click');
    $('table#r-tabela-movimento-caixa button.r-remove-itens-movimento-caixa').on("click", function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCamposMovimentoCaixa();
            triggerSomaCamposMovimentoCaixa();
        });
    });
}

/**
 * Function addItensContasMovimentoCaixa
 * Adiciona itens na tabela de movimento de caixa
 */
function addItensContasMovimentoCaixa() {
    $('table#r-tabela-movimento-caixa button#r-add-itens-movimento-caixa').off("click");
    $('table#r-tabela-movimento-caixa button#r-add-itens-movimento-caixa').on("click", function () {
        var tbody  = $('table#r-tabela-movimento-caixa tbody');
        var modelo = $(tbody).find('tr').first().html();

        $(tbody).append('<tr>' + modelo + '</tr>');
        $(tbody).find('select').select2Reset();

        allFunctions();
        criaSelectsMovimentoCaixa();
        removeItensContaMovimentoCaixa();

        somaCamposMovimentoCaixa();
        triggerSomaCamposMovimentoCaixa();
    });
}

/**
 * Function somaCamposMovimentoCaixa
 * Soma o total do movimento de caixa de acordo com o tipo do movimento de cada linha
 */
function somaCamposMovimentoCaixa() {
    var camposSelects = $("table#r-tabela-movimento-caixa .r-select_tipo-movimento-caixa");
    var valorTotal    = 0;
    var casasPreco    = $('.data_movimento_caixa').data('casas_preco');

    var cifrao = $(".data_movimento_caixa").data("cifrao");
    var cifrao_is_prefixo = $(".data_movimento_caixa").data("cifrao_is_prefixo");
    var separador_decimal = $(".data_movimento_caixa").data("separador_decimal");
    var separador_milhar = $(".data_movimento_caixa").data("separador_milhar");

    $.each(camposSelects, function (idSelect, campoSelect) {
        var valor = 0;
        if(intVal($(campoSelect).val()) === 1) { //SUPRI
            valor = stringParaFloat($(campoSelect).parents('tr').find('.r-valor-movimento-caixa').val(), separador_decimal, true);
            if(is_empty_numeric(valor)) valor = 0;

            valorTotal += valor;
        }else{
            if(intVal($(campoSelect).val()) === 2) { //SAÍDA
                valor = stringParaFloat($(campoSelect).parents('tr').find('.r-valor-movimento-caixa').val(), separador_decimal, true);
                if(is_empty_numeric(valor)) valor = 0;

                valorTotal -= valor;
            }
        }
    });

    $('#r-total-movimento-caixa').text(formataDecimal(valorTotal, '.', separador_decimal, separador_milhar, cifrao, cifrao_is_prefixo, casasPreco));
}

/**
 * Function triggerSomaCamposMovimentoCaixa
 * Prepara gatilhos que ativam a soma do movimento de caixa
 */
function triggerSomaCamposMovimentoCaixa() {
    var tabela = $("table#r-tabela-movimento-caixa tbody tr");

    $(tabela).find("select.r-select_tipo-movimento-caixa").off("select2:select").on("select2:select", function() {
        somaCamposMovimentoCaixa();
    });
    $(tabela).find("select.r-select_tipo-movimento-caixa").off("select2:unselect").on("select2:unselect", function() {
        //FORÇO O VALOR A SER NADA POIS O UNSELECT DO SELECT2 PEGA O ULTIMO VALOR ANTES DELE SER NADA,
        //E O NADA NÃO ATIVA O SELECT DO SELECT2
        $(this).val("");
        somaCamposMovimentoCaixa();
    });
    $(tabela).find("input.r-valor-movimento-caixa").off("keyup").on("keyup", function() {
        somaCamposMovimentoCaixa();
    });
}

addItensContasMovimentoCaixa();
criaSelectsMovimentoCaixa();
removeItensContaMovimentoCaixa();

somaCamposMovimentoCaixa();
triggerSomaCamposMovimentoCaixa();

allFunctions();