/**
 * Function criaSelectsConcFiliaisConfig
 * Cria o Select2 de todos os selects da página e altera o data-init deles para nada
 * (pode ocasionar problemas se não for feito isso após a 1 chamada do select 2)
 */
function criaSelectsFiliaisConfig() {
    $(".cifrao_is_prefixo-config_geral").select2({
        language: _lang,
        allowClear: false
    });
    $(".separador_decimal-config_geral").select2({
        language: _lang,
        allowClear: false
    });
    $(".separador_milhar-config_geral").select2({
        placeholder: $('.data_config').data('selecione_lang'),
        language: _lang,
        allowClear: true
    });
    $(".formato_date_time-config_geral").select2({
        language: _lang,
        allowClear: false
    });

    $('.select_filial-config-geral').select2Ajax();
    $('.select_filial-config-geral').data('init', '');

    $('.select_origem-config-geral').select2Ajax();
    $('.select_origem-config-geral').data('init', '');
}

/**
 * Function populaCamposHiddenDuplicacao.
 * Irá popular os campos hidden de acordo com a tabela do modal (No post, não envia campos do modal)
 */
function populaCamposHiddenDuplicacao(){
    var tabelaModal = "#modal-duplicar_dados_filial table#table-modal_filiais_duplicacao tbody tr";
    var campoVariaveis = ".configs_hidden";

    $(campoVariaveis).html("");
    $(tabelaModal).each(function() {
        $(campoVariaveis).html($(campoVariaveis).html() + '<input type="hidden" name="filial-duplicacao_dados[]" value="' + $(this).find(".filial-duplicacao_dados").val() + '" />');
        $(campoVariaveis).html($(campoVariaveis).html() + '<input type="hidden" name="origem-duplicacao_dados[]" value="' + $(this).find(".origem-duplicacao_dados").val() + '" />');
        $(campoVariaveis).html($(campoVariaveis).html() + '<input type="hidden" name="duplicar-duplicacao_dados[]" value="' + $(this).find(".duplicar-duplicacao_dados").val() + '" />');
    });
}

/**
 * Function controlaModalDuplicacaoDados.
 * Realiza ações para do modal para duplicar dados entre filiais
 */
function controlaModalDuplicacaoDados(){
    var btnAbreModal   = "button.duplicar_dados-btn";
    var modalRef  = "#modal-duplicar_dados_filial";
    var btnsDuplicar = modalRef + " input[type='checkbox'].duplicar-duplicacao_dados";

    $(btnAbreModal).off('click');
    $(btnAbreModal).on("click", function (e) {
        e.preventDefault();

        $(modalRef).modal('show');
    });

    $(modalRef).unbind('hidden.bs.modal');
    $(modalRef).on('hidden.bs.modal', function () {
        populaCamposHiddenDuplicacao();
    });

    $('.filial-duplicacao_dados').select2Ajax();
    $('.filial-duplicacao_dados').data('init', '');
    $('.origem-duplicacao_dados').select2Ajax();
    $('.origem-duplicacao_dados').data('init', '');

    $(btnsDuplicar).off('change');
    $(btnsDuplicar).on('change', function (e) {
        if($(this).prop('checked')){
            $(this).parents('label.form-check-label').removeClass('btn-danger');
            $(this).parents('label.form-check-label').addClass('btn-success');
            $(this).parents('label.form-check-label').find('i').remove();
            $(this).parents('label.form-check-label').append('<i class="fa fa-check" aria-hidden="true"></i>');
            $(this).val('1');
        }else{
            $(this).parents('label.form-check-label').removeClass('btn-success');
            $(this).parents('label.form-check-label').addClass('btn-danger');
            $(this).parents('label.form-check-label').find('i').remove();
            $(this).parents('label.form-check-label').append('<i class="fa fa-times" aria-hidden="true"></i>');
            $(this).val('0');
        }
    });
}

criaSelectsFiliaisConfig();
controlaModalDuplicacaoDados();
populaCamposHiddenDuplicacao();
controlaTabelaSuite();
