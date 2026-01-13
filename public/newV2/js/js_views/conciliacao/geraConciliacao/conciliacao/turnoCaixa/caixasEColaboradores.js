var ultimoAjaxInformacoesCaixa = null;

/**
 * Function ativaTriggerInformacoesCaixa
 * Ativa triggers para pegar informações das caixas
 */
function ativaTriggerInformacoesCaixa() {
	const newLayoutSelector = (!isOldLayout) ? '.visualizar' : '';

    $(`.r-get-info-caixa${newLayoutSelector}`).off('click');
    $(`.r-get-info-caixa${newLayoutSelector}`).on('click', function (e) {
        getInformacoesCaixa(
			$(this).data('caixa'),
			$(this).data('colaborador_periodo'),
			{
				caixa_nome: $(this).data('caixa_nome') ?? '',
				colaborador_periodo_nome: $(this).data('colaborador_periodo_nome') ?? ''
			}
		);
    });

    getInformacoesCaixa(null, null);
}

/**
 * Function getInformacoesCaixa
 * Pega as informações da caixa selecionada, ou sobre o saldo
 */
function getInformacoesCaixa(caixa, colaborador_periodo, dadosCaixa = {}) {
    if(!is_empty(ultimoAjaxInformacoesCaixa)) ultimoAjaxInformacoesCaixa.abort();
    ultimoAjaxInformacoesCaixa = null;

    var dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
    var url  = $(".data_caixas_e_colaboradores").data('part_url_get_info_caixas');

    toggleLoadingOnDivSmall("#r-ajax-info-caixas", true);
    ultimoAjaxInformacoesCaixa = ajaxRequest(true, url, null, 'text', {
        'dataConciliacao': dataConciliacao,
        'caixa': caixa,
        'colaborador_periodo': colaborador_periodo,
		'caixaNome': dadosCaixa.caixa_nome ?? '',
		'colaboradorNome': dadosCaixa.colaborador_periodo_nome ?? ''
    }, function(ret){
        $("#r-ajax-info-caixas").html(ret);
    });
}

ativaTriggerInformacoesCaixa();

/**
 * Function funcoesModalPagamento
 * Define todas as funções do modal de pagamentos das caixas
 */
function funcoesModalPagamento() {
    var ultimoAjaxModalPagamento   = null;

    /**
     * Function ativaTriggerBtnCaixaDblClick
     * Ativa triggers para ao clicar duas vezes no botão da caixa, abrir pop-up
     */
    function ativaTriggerBtnCaixaDblClick() {
		const newLayoutSelector = (!isOldLayout) ? '.alterar' : '';

        $(`.r-get-info-caixa${newLayoutSelector}`).off('dblclick');
        $(`.r-get-info-caixa${newLayoutSelector}`).on('dblclick', function (e) {
            if(!is_empty(ultimoAjaxModalPagamento)) ultimoAjaxModalPagamento.abort();
            ultimoAjaxModalPagamento = null;

            var dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
            var url  = $(".data_caixas_e_colaboradores").data('part_url_get_pagamentos_caixas');

            var caixa       = $(this).data('caixa');
            var colaboradorPeriodo = $(this).data('colaborador_periodo');

            $('#r-modal-pagamentos-conciliacao .data_modal_pagamentos_conciliacao').data('colaborador_periodo', colaboradorPeriodo);
            $('#r-modal-pagamentos-conciliacao .data_modal_pagamentos_conciliacao').data('caixa', caixa);

            $('#r-modal-pagamentos-conciliacao').modal('show');

            toggleLoadingOnDivSmall($('#r-modal-pagamentos-conciliacao #r-ajax-modal-body'), true);
            ultimoAjaxModalPagamento = ajaxRequest(true, url, null, 'text', {
                'dataConciliacao': dataConciliacao,
                'caixa': caixa,
                'colaboradorPeriodo': colaboradorPeriodo
            }, function(ret){
                $("#r-modal-pagamentos-conciliacao #r-ajax-modal-body").html(ret);
            });
        });

        $(`.r-get-info-caixa${newLayoutSelector}`).off('taphold');
        $(`.r-get-info-caixa${newLayoutSelector}`).on('taphold', function (e) {
            $(this).trigger('dblclick');
        })

		if (!isOldLayout) {
			$(`.r-get-info-caixa${newLayoutSelector}`).off('click');
			$(`.r-get-info-caixa${newLayoutSelector}`).on('click', function (e) {
				$(this).trigger('dblclick');
			})
		}
    }

    // /**
    //  * Function onOpenModalPagamentosTurnoCaixas.
    //  * Desabilita o botão de salvar o modal quando ele abre
    //  */
    // function onOpenModalPagamentosTurnoCaixas() {
    //     $('#r-modal-pagamentos-conciliacao').unbind('shown.bs.modal');
    //     $('#r-modal-pagamentos-conciliacao').on('shown.bs.modal', function () {
    //
    //     })
    // }

    /**
     * Function onDismissModalPagamentosCaixas
     * Ao fechar modal, excluir informações do mesmo
     */
    function onDismissModalPagamentosCaixas() {
        $('#r-modal-pagamentos-conciliacao').unbind('hidden.bs.modal');
        $('#r-modal-pagamentos-conciliacao').on('hidden.bs.modal', function () {
            if(!is_empty(ultimoAjaxModalPagamento)) ultimoAjaxModalPagamento.abort();
            ultimoAjaxModalPagamento = null;

            $(this).find('.data_modal_pagamentos_conciliacao').data('colaborador_periodo', '');
            $(this).find('.data_modal_pagamentos_conciliacao').data('caixa', '');

            $(this).find('#r-ajax-modal-body').html('');

            $("#r-modal-pagamentos-conciliacao .modal-footer button#r-salvar-pagamentos").prop("disabled", true);
        })
    }

    /**
     * Function saveModalPagamentos
     * Salva as alterações do modal de pagamentos por colaborador e caixas
     */
    function saveModalPagamentos(){
        $("#r-modal-pagamentos-conciliacao .modal-footer button#r-salvar-pagamentos").off('click');
        $("#r-modal-pagamentos-conciliacao .modal-footer button#r-salvar-pagamentos").on('click', function (e) {
            e.preventDefault();

            var objModal           = $(this).parents('#r-modal-pagamentos-conciliacao');
            var linhas             = $(objModal).find("table#r-tabela-pagamentos-caixas-colaboradores tbody tr");
            var colaboradorPeriodo = $(objModal).find('.data_modal_pagamentos_conciliacao').data('colaborador_periodo');
            var caixa              = $(objModal).find('.data_modal_pagamentos_conciliacao').data('caixa');
            var pagamentosCaixa    = [];
            var separador_decimal  = $(".data_caixas_e_colaboradores").data("separador_decimal");

            $.each(linhas, function (idLinha, linha) {
                var idPagamento    = $(linha).data('pagamento_id');
                var precoPagamento = stringParaFloat($(linha).find('td.caixa input.r-caixa-input').val().trim(), separador_decimal, true);
                if(is_empty_numeric(precoPagamento)) precoPagamento = 0;

                if(!is_empty(idPagamento, 1)){
                    pagamentosCaixa.push({
                        'idPagamento': idPagamento,
                        'preco': precoPagamento
                    });
                }
            });

			// Validação trava de valor máximo conciliação
			if ( ($(".data_views").data('bol_conc_trava_valor') == '1') &&
				 ($(".data_views").data('bol_conc_automatica') == '0')
			) {
				let valorMaxConciliacao = parseFloat($(".data_views").data('conc_trava_valor'));
				let valorParcialFinal = parseFloat($("#valor_parcial_conciliacao_caixas").val());
				let valorCaixaFinal = parseFloat($("#valor_caixa_conciliacao_caixas").val());

				if ( valorCaixaFinal > (valorParcialFinal + valorMaxConciliacao) ) {
					swal(
						l["erro!"],
						l["erroTravaValorConciliacao"],
						"error"
					).then().catch(swal.noop);
					return null;
				}

				if ( valorCaixaFinal < (valorParcialFinal - valorMaxConciliacao) ) {
					swal(
						l["erro!"],
						l["erroTravaValorConciliacao"],
						"error"
					).then().catch(swal.noop);
					return null;
				}
			}

            var dataConciliacao = $('#r-ajax-form').data('data_conciliacao');
            var url             = $(".data_caixas_e_colaboradores").data('part_url_save_pagamentos_caixas_colaboradores');

            swal({
                title: l["salvarPagamentos?"],
                text: l["temCertezaDeQueDesejaSalvarOsPagamentos?"],
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: l['salvar!'],
                cancelButtonText: l["cancelar!"]
            }).then(function () {
                toggleLoading();
                ajaxRequest(true, url, null, 'text', {
                    'colaboradorPeriodo': colaboradorPeriodo,
                    'caixa': caixa,
                    'pagamentosCaixa': pagamentosCaixa,
                    'dataConciliacao': dataConciliacao
                }, function (ret) {
                    try{
                        // console.log(ret);
                        ret = JSON.parse(ret);
                        if(!is_empty(ret['bol'], 1)) {
                            $(objModal).modal('hide');
                            getInformacoesCaixa(caixa, colaboradorPeriodo);
                        }

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
                        // consoleSystem(err, 'error');
                    }
                });
            }).catch(swal.noop);
        });
    }

    ativaTriggerBtnCaixaDblClick();
    onDismissModalPagamentosCaixas();
    // onOpenModalPagamentosTurnoCaixas();
    saveModalPagamentos();
}

funcoesModalPagamento();