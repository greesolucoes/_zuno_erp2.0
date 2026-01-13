function getRowsConteudoTable() {
    return $('table#conteudoTable tbody tr:not(".ocultar")').length;
}

function testButtonRemoveAll() {
    var rowLen = getRowsConteudoTable();
    var buttonDeleteAll = $('table#conteudoTable tfoot tr td button.removeAllItens');
    if(rowLen <= 0) buttonDeleteAll.prop('disabled', true);
    else buttonDeleteAll.prop('disabled', false);

    return 'x';
}

function duplicaLines() {
    var modelo     = $('table#conteudoTable tbody tr').first().html();

    $('table#conteudoTable tbody').append('<tr>' + modelo + '</tr>');
    $('table#conteudoTable tbody tr .select').select2Reset();

    var limpaCampos = $($('table#conteudoTable tbody tr').last());
    $(limpaCampos).prop('style', null);
    $(limpaCampos).find('select.select_userId').prop("name", "userId[]");
    $(limpaCampos).find('input.valores').prop('name', 'valores[]');
    $(limpaCampos).find('input[type="text"]').prop('value', '');
    $(limpaCampos).find('select.select_userId').find('option').remove();
}

function changeDataNotIn(valor) {
    $('.data_views').data('id_user_not_in', valor);
    geraDataUrlUsersSelects();
}

function removeLine(rem) {
    if(is_empty(rem, 1)){
        var TrsDel = $('table#conteudoTable tbody tr:not(".ocultar")');

        TrsDel.remove();
        geraSequencia();
        testButtonRemoveAll();
    }else{
        rem.fadeOut(270, function () {
            rem.remove();
            geraSequencia();
            testButtonRemoveAll();
        });
    }
}

function changeUserAutId(){
    $(".select_userAutId").unbind('change');
    $(".select_userAutId").change(function (){
        var obj          = $(this);
        var valor        = $(obj).val();
        var existRow     = false;
        var remLines     = [];
        if(is_empty(valor, 1)) valor = 0;

        $("table#conteudoTable tbody tr td select.select_userId").each(function () {
            if($(this).val() == valor) {
                existRow = true;
                remLines.push({
                    lineTr: $(this).parents('tr')
                });
            }
        });


        if(!existRow){
            changeDataNotIn(valor);
        }else{
            swal({
                title: l["trocarAutorizadorMaster?"],
                text: l["existeUmaLinhaDeRegraComEsseUsuário,CasoContinueALinhaSeráDeletada,Continuar?"],
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: l["sim!"],
                cancelButtonText: l["cancelar!"]
            }).then(function () {
                changeDataNotIn(valor);
                $(remLines).each(function (index, lineRem) {
                    removeLine(lineRem.lineTr);
                });
            }, function () {
                $(obj).val($('.data_views').data('id_user_not_in')).trigger('change');
                swal(
                    l["trocaDeAutorizadorMasterCancelada"],
                    l["operaçãoCancelada!"],
                    'error'
                )
            }).catch(swal.noop);
        }
    });
}

function geraDataUrlUsersSelects(){
    var url        = $('.data_views').data('url_user_aprovadores');
    var idNotIn    = $('.data_views').data('id_user_not_in');
    var selectUser = $('table#conteudoTable tbody tr td select.select_userId');
    if(is_empty(idNotIn, 1)) idNotIn = '';

    $(selectUser).data('url', url + idNotIn);
    $(selectUser).select2('destroy').select2Reset();
    $(selectUser).select2Ajax();
}

function geraSequencia(){
    var i = 1;
    $("table#conteudoTable tbody tr").each(function () {
        if(!$(this).hasClass('ocultar')){
            $(this).find('td.sequencia').text(i);
            $(this).find('td button.removeItens').prop('disabled', false);
            i++;
        }else{
            $(this).find('td button.removeItens').prop('disabled', true);
        }
    });
}

function destroyInitsSelects(){
    $(".select_userId").data('init', '');
    $(".select_userAutId").data('init', '');
}

function criaSelects(){
    $(".select_userId").select2Ajax();
    $(".select_userAutId").select2Ajax();
}

function removeAllLinesTable() {
    $('table#conteudoTable tfoot tr td button.removeAllItens').unbind('click');
    $('table#conteudoTable tfoot tr td button.removeAllItens').click(function () {
        var rowLen = getRowsConteudoTable();

        if(rowLen > 0){
            swal({
                title: l["deletarTodasAsLinhasDeRegras?"],
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: l["continuar!"],
                cancelButtonText: l["cancelar!"]
            }).then(function () {
                removeLine();
                geraSequencia();
                testButtonRemoveAll();
            }, function () {
                swal(
                    l["açãoCancelada"],
                    '',
                    'error'
                )
            }).catch(swal.noop);
        }
    });
}

function addButtonsRemoveItens() {
    $('table#conteudoTable button.removeItens').unbind('click');
    $('table#conteudoTable button.removeItens').click(function () {
        var rem = $(this).parents('tr');
        removeLine(rem);
    });
}

function addRegrasTable() {
    $('table#conteudoTable button.addItens').unbind('click');
    $('table#conteudoTable button.addItens').click(function () {
        duplicaLines();
        allFunctions();
        criaSelects();
        addButtonsRemoveItens();
        geraSequencia();
        geraDataUrlUsersSelects();
        testButtonRemoveAll();
    });
}

function inicializarPagina() {
    addRegrasTable();
    criaSelects();
    addButtonsRemoveItens();
    destroyInitsSelects();
    geraSequencia();
    geraDataUrlUsersSelects();
    changeUserAutId();
    removeAllLinesTable();
    testButtonRemoveAll();
}

inicializarPagina();