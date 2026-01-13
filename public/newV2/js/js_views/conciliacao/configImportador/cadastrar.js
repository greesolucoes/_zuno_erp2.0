/**
 * Remove a linha de template vazia da tabela de cartões antes do submit,
 * além de remover linhas com campos obrigatórios vazios.
 */
document.addEventListener("DOMContentLoaded", function() {
    // Seleciona o formulário dentro da aba "cartoes"
    var formCartoes = document.querySelector("#cartoes form");
    if(formCartoes) {
        formCartoes.addEventListener("submit", function(e) {
            // Remove todas as linhas com a classe "ocultar" do tbody da tabela
            var linhasOcultas = formCartoes.querySelectorAll("#tabela-cartoes tbody tr.ocultar");
            linhasOcultas.forEach(function(tr) {
                tr.remove();
            });

            // Remove linhas que estejam com campos vazios (code ou descricao)
            formCartoes.querySelectorAll("#tabela-cartoes tbody tr").forEach(function(tr) {
                var inputCode = tr.querySelector('input[name="code[]"]');
                var inputDescricao = tr.querySelector('input[name="descricao[]"]');
                if (inputCode && inputDescricao) {
                    var codeValue = inputCode.value.trim();
                    var descricaoValue = inputDescricao.value.trim();
                    if(codeValue === '' || descricaoValue === '') {
                        tr.remove();
                    }
                }
            });
        });
    }
});

/**
 * Function controlaTabelaImportador
 * Controla as tabelas da área
 */
function controlaTabelaImportador() {
    // Não existem particularidades para as tabelas do importador
    controlaTabelaSuite({
        'funDepois' : function () {
            $('table#tabela-cartoes button.remove-itens-table-geral.ajax-delete').off('click');
            $('table#tabela-cartoes button.remove-itens-table-geral.ajax-delete').on("click", function (e) {
                e.preventDefault();
                var objTr = $(this).parents('tr');
                var idSap = $(objTr).find('input.cartoes-id-sap').val();
                var url = $('table#tabela-cartoes').data('url_delete_cartoes');

                swal({
                    title: l["desejaContinuar?"],
                    text: "",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: l["sim!"],
                    cancelButtonText: l["cancelar!"]
                }).then(function () {
                    toggleLoading();
                    ajaxRequest(true, url, null, 'text', {'id_sap': idSap}, function (ret) {
                        try{
                            ret = JSON.parse(ret);
                            if(!is_empty(ret['bol'], 1)) $(objTr).remove();

                            swal(
                                ret['titulo'],
                                ret['text'],
                                ret['class']
                            ).catch(swal.noop);

                            toggleLoading();
                        }catch(err){
                            swal(
                                l["erro!"],
                                l["tempoDeRespostaDoServidorEsgotado!"],
                                "error"
                            ).catch(swal.noop);
                            forceToggleLoading(0);
                        }
                    });
                }, function () {
                    // SE DER ERRO
                }).catch(swal.noop);
            });
        }
    });
}

/**
 * Função que desmarca uma flag dinamicamente
 * Passar o seletor como parâmetro Ex. #id
 */
function trigerDesmarcarFlag(selector){
    if ($(selector).is(':checked')){
        $(selector).parents('label').removeClass('active');
        $(selector).removeAttr('checked');
        $(selector).parents('label').find('i').removeClass('fa-check-square').addClass('fa-square-o');
    }
}

/**
 * Function exibeCampos
 * Define se exibirá os inputs
 */
function exibeCampos() {
    /** Essa flag ativar irá mostrar o de-para de centro de renda que vai mostrar os campos */
    $('input#controle-centro-renda-itens-nota').unbind('change');
    $('input#controle-centro-renda-itens-nota').on("change", function() {
        if(this.checked) {
            $('#geral-campo_de_para_centro_de_renda_nome').removeClass('ocultar');
        } else {
            $('#geral-campo_de_para_centro_de_renda_nome').addClass('ocultar');
        }
    });

    let inputChanges = $('#geral-campo_de_para_centro_de_renda_nome input[name^=\'desativar_nome_centro_renda_\']');
    inputChanges.unbind('change');
    inputChanges.on("change", function() {
        let id = $(this).data('index');
        if(this.checked) {
            for (let i = id; i <= 5; i++){
                $('#geral-campo_de_para_centro_de_renda_nome input#nome_centro_renda_'+i).prop("disabled", true);
                $('#geral-campo_de_para_centro_de_renda_nome input#desativar_nome_centro_renda_'+i).prop('checked', true);

                if (i !== id){
                    $('#geral-campo_de_para_centro_de_renda_nome input#desativar_nome_centro_renda_'+i).attr('onclick', "return false;");
                }
            }
        } else {
            for (let i = id; i <= 5; i++){
                $('#geral-campo_de_para_centro_de_renda_nome input#nome_centro_renda_'+i).prop("disabled", false);
                $('#geral-campo_de_para_centro_de_renda_nome input#desativar_nome_centro_renda_'+i).prop('checked', false);

                if (i !== id){
                    $('#geral-campo_de_para_centro_de_renda_nome input#desativar_nome_centro_renda_'+i).removeAttr('onclick');
                }
            }
        }
    });

    /** FIM Essa flag ativar irá mostrar o de-para de centro de renda que vai mostrar os campos */

    $('input#geral-habilitar_controle_filial_conta_contabil_recebimento_cartoes').unbind('change');
    $('input#geral-habilitar_controle_filial_conta_contabil_recebimento_cartoes').on("change", function() {
        if(this.checked) {
            trigerDesmarcarFlag('input#geral-recebimento_cartoes_multibase');
            $('input#geral-recebimento_cartoes_multibase').attr('disabled', true);
            $('#geral-campo_busca_bplid_contas_contabeis_recebimento_cartoes_form').removeClass('ocultar');
        } else {
            $('input#geral-recebimento_cartoes_multibase').removeAttr('disabled');
            $('#geral-campo_busca_bplid_contas_contabeis_recebimento_cartoes_form').addClass('ocultar');
        }
    });

    $('input#geral-usar_cod_item_importar_taxa').unbind('change');
    $('input#geral-usar_cod_item_importar_taxa').on("change", function() {
        if(this.checked) {
            $('#geral-cod_item_taxa-form').removeClass('ocultar');
        } else {
            $('#geral-cod_item_taxa-form').addClass('ocultar');
        }
    });
    $('input#geral-usar_cod_item_importar_entrada').unbind('change');
    $('input#geral-usar_cod_item_importar_entrada').on("change", function() {
        if(this.checked) {
            $('#geral-cod_item_entrada-form').removeClass('ocultar');
        } else {
            $('#geral-cod_item_entrada-form').addClass('ocultar');
        }
    });
    $('input#geral-usar_cod_item_importar_gorjeta').unbind('change');
    $('input#geral-usar_cod_item_importar_gorjeta').on("change", function() {
        if(this.checked) {
            $('#geral-cod_item_gorjeta-form').removeClass('ocultar');
        } else {
            $('#geral-cod_item_gorjeta-form').addClass('ocultar');
        }
    });
    $('input#geral-integrar_notas_antes_pagamentos').unbind('change');
    $('input#geral-integrar_notas_antes_pagamentos').on("change", function() {
        if(this.checked) {
            $('#geral-dt_corte_integracao_notas_antes_pagamentos-form').removeClass('ocultar');
        } else {
            $('#geral-dt_corte_integracao_notas_antes_pagamentos-form').addClass('ocultar');
        }
    });
    $('input#geral-bol_inserir_item_por_centro_renda').off('change');
    $('input#geral-bol_inserir_item_por_centro_renda').on("change", function() {
        if(this.checked) {
            $('#geral-configs_centro_renda-form').removeClass('ocultar');
        } else {
            $('#geral-configs_centro_renda-form').addClass('ocultar');
        }
    });
    $('input#geral-agrupar-justificativas-de-caixa').unbind('change');
    $('input#geral-agrupar-justificativas-de-caixa').on("change", function() {
        if(this.checked) {
            $('#geral-justificativa-para-agrupar-form').removeClass('ocultar');
        } else {
            $('#geral-justificativa-para-agrupar-form').addClass('ocultar');
        }
    });
    $('input#geral-habilitar_recebimento_cartoes').unbind('change');
    $('input#geral-habilitar_recebimento_cartoes').on("change", function() {
        if(this.checked) {
            $('#geral-recebimento_cartoes_multibase').removeClass('disabled');
        } else {
            $('#geral-recebimento_cartoes_multibase').addClass('disabled');
        }
    });

    $('input#geral-habilitar_controle_filial_conta_contabil_recebimento_cartoes').trigger('change');

    $('input#geral-agrupar-justificativas-de-caixa').trigger('change');
    $('input#geral-usar_cod_item_importar_gorjeta').trigger('change');
    $('input#geral-usar_cod_item_importar_taxa').trigger('change');
    $('input#geral-usar_cod_item_importar_entrada').trigger('change');
    $('input#geral-integrar_notas_antes_pagamentos').trigger('change');
    if($('input#geral-bol_inserir_item_por_centro_renda').length > 0) {
        $('input#geral-bol_inserir_item_por_centro_renda').trigger('change');
    }
    $('input#geral-habilitar_recebimento_cartoes').trigger('change');
    $('input#controle-centro-renda-itens-nota').trigger('change');

    $("#origem-cardservice-config-geral").on("change", function(){
        // Obtém o objeto "text" do item selecionado
        const data = $(this).select2('data');
        const selectedText = (data && data.length) ? data[0].text.toLowerCase() : "";

        /* Caso o texto selecionado seja alguns dos listados abaixo,
           é feita a remoção da classe ocultar nas divs com a classe "ocultar-card-service-arquivos-configs" */
        const textosRemover = ["skytef"];

        // Se o texto selecionado estiver na lista, a classe será removida, caso contrário, ela será adicionada/mantida
        $(".ocultar-card-service-arquivos-configs").toggleClass("ocultar", !textosRemover.includes(selectedText));
    }).trigger("change");
}

function iniciaSelectsConfigImportador(){
    $("#geral-select-id-justificativa-agrupada").select2Ajax();
    $("#geral-select-id-justificativa-agrupada").data('init', '');

    $("#origem-cardservice-config-geral").select2Ajax();
    $("#origem-cardservice-config-geral").data('init', '');

    $("#versaoLayoutArquivoCS").select2Ajax();
    $("#versaoLayoutArquivoCS").data('init', '');
}

function controlaModalDuplicacaoDados() {
    var btnAbreModal = "button.duplicar_dados_confimp-btn";
    var modalRef = "#modal-duplicar_config_importador_empresa";
    var btnsDuplicar = modalRef + " input[type='checkbox'].duplicar-duplicacao_dados";

    $(btnAbreModal).off('click');
    $(btnAbreModal).on("click", function (e) {
        e.preventDefault();
        $(modalRef).modal('show');
    });

    // Carrega as empresas na listagem
    $('.empresa-duplicacao_dados').select2Ajax();
    $('.empresa-duplicacao_dados').data('init', '');

    $(btnsDuplicar).off('change');
    $(btnsDuplicar).on('change', function (e) {
        if ($(this).prop('checked')) {
            $(this).parents('label.form-check-label').removeClass('btn-danger');
            $(this).parents('label.form-check-label').addClass('btn-success');
            $(this).parents('label.form-check-label').find('i').remove();
            $(this).parents('label.form-check-label').append('<i class="fa fa-check" aria-hidden="true"></i>');
            $(this).val('1');
        } else {
            $(this).parents('label.form-check-label').removeClass('btn-success');
            $(this).parents('label.form-check-label').addClass('btn-danger');
            $(this).parents('label.form-check-label').find('i').remove();
            $(this).parents('label.form-check-label').append('<i class="fa fa-times" aria-hidden="true"></i>');
            $(this).val('0');
        }
    });
}

function alteraHtmlOvernight () {
    $(document).on("click", ".checkbox-overnight", function() {
        if ($(this).prop('checked')) {
            $(this).val(1);
            $(this).closest("td").find(".cartoes-is_overnight-hidden").val(1);
        } else {
            $(this).val(0);
            $(this).closest("td").find(".cartoes-is_overnight-hidden").val(0);
        }
    });
}

alteraHtmlOvernight();
controlaTabelaImportador();
controlaModalDuplicacaoDados();
iniciaSelectsConfigImportador();
exibeCampos();
