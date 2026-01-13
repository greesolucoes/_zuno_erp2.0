function criaSelects(){
    $(".select_produtoId").select2AjaxProdutos();
    $(".select_fornecedorId").select2Ajax();
    $(".select_medida").select2({
        placeholder: l["unidadeDeMedida"],
        language: "pt-BR",
        allowClear: true
    });
    $(".select_observacoesId").select2Ajax();
    $(".select_observacoesId").data('init', '');
}

function ajaxSelectUM(){
    $(".select_produtoId").unbind('change');
    $(".select_produtoId").change(function (){
        var produto = $('option:selected', this).val();
        var selectUM = $(this).parents('tr').find('select.select_medida');
        var travaTriggerProd = $(this).data('travaselecao');

        if(travaTriggerProd != 1){
            $(selectUM).find('option').remove();
            $(selectUM).append('<option value=""></option>');
            var urlJsonUM = $('.data_views').data('url_json_um');
            if(produto !== null && produto !== '') {
                ajaxRequest(true, urlJsonUM, null, 'text', {'produto': produto}, function(ret){
                    ret = $.parseJSON(ret);
                    $.each(ret['medidas'], function (id, value) {
                        $(selectUM).append('<option value="' + value.idUnidadesMedidas + '">' + value.nomeUnidadesMedidas + '</option>');
                    });
                    $(selectUM).find('option[value="' + ret['UMBase'] + '"]').prop('selected', true);
                    $(selectUM).trigger('change');
                });
            }
        }

        $(this).data('travaselecao', 0);
    });
}

function addButtonsRemoveItens() {
    $('table#conteudoTable button.removeItens').unbind('click');
    $('table#conteudoTable button.removeItens').click(function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCampos();
        });

    });
}

function somaCampos(){
    var total = 0;
    $("table#conteudoTable tbody tr").each(function () {
        var subtotal =
            toFloat(
                $(this).find('input.quantidade').val(),
                $('.data_views').data('casas_qtd')
            ) *
            toFloat(
                $(this).find('input.precoUnitario').val(),
                $('.data_views').data('casas_preco')
            );

        $(this).find('input.subTotal').val(float2real(subtotal, true));
        total += subtotal;
    });
    $("table#conteudoTable input#totalGeral").val(float2real(total, true));

    $("table#despesasTable tbody tr input.valorDespesa").each(function () {
        total +=
            toFloat(
                $(this).val(),
                $('.data_views').data('casas_preco')
            );
    });
    $("div#conteudo input#valorTotalFinal").val(float2real(total, true));
}

function addSomaCampos(){
    $("input.quantidade, input.precoUnitario, input.valorDespesa").unbind('keyup').keyup(somaCampos);
}

$('table#conteudoTable button.addItens').click(function () {
    var modelo   = $('table#conteudoTable tbody tr').first().html();

    $('div#conteudo table#conteudoTable tbody').append('<tr>' + modelo + '</tr>');
    $($('table#conteudoTable tbody tr').last()).find('button.removeItens').prop('disabled', false);
    $('table#conteudoTable tbody tr .select').select2Reset();

    var limpaCampos = $($('div#conteudo table#conteudoTable tbody tr').last());
    $(limpaCampos).find('input[type="text"]').prop('value', '');
    $(limpaCampos).find('select.select_produtoId').find('option').remove();
    $(limpaCampos).find('select.select_produtoId').data('init', '');
    $(limpaCampos).find('select.select_medida').find('option').remove();
    $(limpaCampos).find('select.select_medida').append('<option value=""></option>');
    $(limpaCampos).find('select').find('option:selected').prop('selected', false);
    $(limpaCampos).find('select.select_medida option[value=""]').prop('selected', 'selected');
    $(limpaCampos).find('select.select_produtoId option[value=""]').prop('selected', 'selected');
    $('select.select_produtoId').data('travaselecao', 1);
    allFunctions();
    ajaxSelectUM();
    criaSelects();
    addButtonsRemoveItens();
    addSomaCampos();
    $('select.select_produtoId').data('travaselecao', 0);
});

criaSelects();
addButtonsRemoveItens();
addSomaCampos();
somaCampos();

$('select.select_produtoId').data('travaselecao', 0);