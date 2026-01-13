/**
 * Created by vitor on 26/08/2017.
 */

function criaSelects(){
    $(".select_produtoId").select2AjaxProdutos();
    $(".select_ajax").select2Ajax();
    $(".select_medida").select2({
        placeholder: l["unidadeDeMedida"],
        language: "pt-BR",
        allowClear: true
    });
    $(".select_utilizacao").select2({
        placeholder: l["utilização"],
        language: "pt-BR",
        allowClear: true
    });
	$(".select_deposito").select2({
		placeholder: l["depósito"],
		language: "pt-BR",
		allowClear: true
	});
	$(".select_regraDistribuicao").select2({
		placeholder: l["regraDeDistribuição"],
		language: "pt-BR",
		allowClear: true
	});
    $(".select_despesa").select2({
        placeholder: l["tipoDaDespesa"],
        language: "pt-BR",
        allowClear: true
    });
    $(".select_condicoesPagamento, .select_formasPagamento, .select_modeloNotaFiscal").select2({
        placeholder: l["selecione"],
        language: "pt-BR",
        allowClear: true
    });

    $(".select_moeda").select2({
        placeholder: l["selecione"],
        language: "pt-BR",
        allowClear: false
    });

    $(".select_observacoesId").select2Ajax();
    $(".select_observacoesId").data('init', '');
}

function criaSelectsModal(){
	$(".select_ajaxModal").select2Ajax();
    $(".select_medidaModal").select2({
        placeholder: l["unidadeDeMedida"],
        language: "pt-BR",
        allowClear: true,
        dropdownParent: $('.modal.modalItensPorGrupo')
    });
    $(".select_utilizacaoModal").select2({
        placeholder: l["utilização"],
        language: "pt-BR",
        allowClear: true,
        dropdownParent: $('.modal.modalItensPorGrupo')
    });
	$(".select_depositoModal").select2({
		placeholder:  l["depósito"],
		language: "pt-BR",
		allowClear: true,
		dropdownParent: $('.modal.modalItensPorGrupo')
	});
	$(".select_regraDistribuicaoModal").select2({
		placeholder:  "Regra de distribuição",
		language: "pt-BR",
		allowClear: true,
		dropdownParent: $('.modal.modalItensPorGrupo')
	});
}

function ativaMudancaMoeda() {
    var isFirstTime = true;

    $(".select_moeda").unbind('select2:select');
    $(".select_moeda").on('select2:select', function () {
        var iMoeda = 0;
        $($('table#conteudoTable tbody').find('tr')).each(function () {
            if(iMoeda === 0 || isFirstTime) {
                if(!isFirstTime){
                    $(this).find('input[type="text"]').prop('value', '');

                    $(this).find('select.select_produtoId').find('option').remove();
                    $(this).find('select.select_produtoId').data('init', '');

                    $(this).find('select.select_medida').find('option').remove();
                    $(this).find('select.select_medida').append('<option value=""></option>');

                    $(this).find('select').find('option:selected').prop('selected', false);
                    $(this).find('select.select_medida option[value=""]').prop('selected', 'selected');
                    $(this).find('select.select_produtoId option[value=""]').prop('selected', 'selected');

                    $(this).find('select.select_medida').removeClass('readonly');
                    $(this).find('input.precoUnitario').prop('readonly', false);

                    $(this).find('select.select_produtoId').data('travaselecao', 1);
                }

                trocaParametroCamposMaskNumerosV2($(this).find('input.precoUnitario'));
                trocaParametroCamposMaskNumerosV2($(this).find('input.subTotal'));
				trocaParametroCamposMaskNumerosV2($(this).find('input.conteudo-itens_valor_despesa1'));
				trocaParametroCamposMaskNumerosV2($(this).find('input.conteudo-itens_valor_despesa2'));
				trocaParametroCamposMaskNumerosV2($(this).find('input.conteudo-itens_valor_despesa3'));
                iMoeda++;
                return;
            }

            iMoeda++;
            if(!isFirstTime) $(this).remove();
        });

        iMoeda = 0;
        $("table#despesasTable tbody tr").each(function () {
            if(iMoeda === 0 || isFirstTime) {
                if(!isFirstTime){
                    $(this).find('input[type="text"]').prop('value', '');

                    $(this).find('select').find('option:selected').prop('selected', false);
                    $(this).find('select option[value=""]').prop('selected', 'selected');
                }

                trocaParametroCamposMaskNumerosV2($(this).find('input.valorDespesa'));

                iMoeda++;
                return;
            }

            iMoeda++;
            if(!isFirstTime) $(this).remove();
        });

		$('select.select_produtoId').data('travaselecao', 1);
        trocaParametroCamposMaskNumerosV2($('input#totalGeral'));
        trocaParametroCamposMaskNumerosV2($('input#valorTotalFinal'));
        allFunctions();
        ajaxSelectUM();
        criaSelects();
        addButtonsRemoveItens();
        addSomaCampos();
        somaCampos();
        $('select.select_produtoId').data('travaselecao', 0);
    });
    $(".select_moeda").trigger('select2:select');

    isFirstTime = false;
}

function trocaParametroCamposMaskNumerosV2(__this) {
    var v = $(__this).clone();
    $(__this).parent().append(v);
    $(__this).remove();

    $(v).attr('data-mask', 'numerov2');
    $(v).attr('data-prefixo', $($(".select_moeda").find('option:selected')).data('texto_impressao'));
    $(v).attr('data-sufixo', '');
    $(v).attr('data-thousand_delimiter', $($(".select_moeda").find('option:selected')).data('sep_milhar'));
    $(v).attr('data-decimal_delimiter', $($(".select_moeda").find('option:selected')).data('sep_decimal'));
    $(v).attr('data-bol_negative', 'false');
    $(v).attr('data-maxdecimal', $('.data_views').data('casas_preco'));

    $(v).fnMascaraNumeroV2();
}

function addClickButtonModal(){
    $('.modal.modalItensPorGrupo button.btnModal').unbind('click');
    $('.modal.modalItensPorGrupo button.btnModal').click(function () {
        var utilizacoes = $.parseJSON($('.data_utilizacoes').text());
		var depositos = $.parseJSON($('.data_depositos').text());
		var flagUtilizarDespesasAdicionaisLinhaItem = $('.data_views').data('utilizar_despesas_adicionais_linha_item');
		var despesasAdicionais = $.parseJSON($('.data_despesasAdicionais').text());
		var regrasDistribuicao = $.parseJSON($('.data_regrasDistribuicao').text());
        var urlGetProdAjax = $('.data_views').data('url_ajax_get_prod');
        var delRowEmpty = 0;

        $(this).parents('.modal.modalItensPorGrupo').find('table#table-modal tbody tr').each(function () {
            var dataIdProduto = $(this).data('idproduto');
            if($(this).find('input.quantidadeModal').val() != null &&
                $(this).find('input.quantidadeModal').val() != ''){
                delRowEmpty = 1
            }

            $("table#conteudoTable tbody tr").each(function () {
                if(dataIdProduto == $(this).find('select.select_produtoId').val()){
                    $(this).remove();
                }
                if(delRowEmpty == 1){
                    if(($(this).find('select.select_produtoId').val() == null || $(this).find('select.select_produtoId').val() == '') &&
                        ($(this).find('input.quantidade').val() == null || $(this).find('input.quantidade').val() == '')){
                        $(this).remove();
                    }
                }
            });
        });
        $('select.select_produtoId').data('travaselecao', 1);
        $(this).parents('.modal.modalItensPorGrupo').find('table#table-modal tbody tr').each(function () {
            if($(this).find('input.quantidadeModal').val() != null &&
                $(this).find('input.quantidadeModal').val() != ''){

                var dataIdProduto = $(this).data('idproduto');
                var retAjax = {
                    idProdutos: dataIdProduto,
                    precoFixo: $(this).data('precofixo'),
                    nomeUMProduto: $(this).data('umfixanome'),
                    idUMProduto: $(this).data('umfixa'),
                    nomeProdutos: $(this).data('nomeproduto'),
                    codigoProdutos: $(this).data('codigoproduto'),
					isControlarLote: $(this).data('iscontrolarlote'),
					utilizacao: $(this).data('utilizacao'),
					depositoSelecionado: $(this).data('depositoSelecionado'),
					idDespesa1: $(this).find("select.conteudo-itens_despesa1 option:selected").val(),
					nomeDespesa1: $(this).find("select.conteudo-itens_despesa1 option:selected").text(),
					valorDespesa1: $(this).find("input.conteudo-itens_valor_despesa1").val(),
					idDespesa2: $(this).find("select.conteudo-itens_despesa2 option:selected").val(),
					nomeDespesa2: $(this).find("select.conteudo-itens_despesa2 option:selected").text(),
					valorDespesa2: $(this).find("input.conteudo-itens_valor_despesa2").val(),
					idDespesa3: $(this).find("select.conteudo-itens_despesa3 option:selected").val(),
					nomeDespesa3: $(this).find("select.conteudo-itens_despesa3 option:selected").text(),
					valorDespesa3: $(this).find("input.conteudo-itens_valor_despesa3").val(),
					lotes: $(this).find(".lotes_item").text()
                };

				var htmlRegraDistr = null;
				if ($(this).data('qtddimensoes') > 0) {
					for (let i = 1; i <= $(this).data('qtddimensoes'); i++) {
						htmlRegraDistr += '' +
							'<td>' +
							'<select class="form-control select select_regraDistribuicao" name="regraDistribuicao' + i + '[]" style="width: 100%;">' +
							'<option value="" selected="selected"></option>' +
							'</select>' +
							'</td>';
					}
				}

				var htmlDespesasAdicionais = '';
				if(!is_empty(flagUtilizarDespesasAdicionaisLinhaItem,1)){
					var urlDespesasAdicionais = $(this).data('urldespesasadicionais');
					// select da despesa 1
					htmlDespesasAdicionais += '' +
						'<td>' +
						'<select class="form-control select select_ajax select_ajax_FI conteudo-itens_despesa1" name="idDespesasAdicionais1[]" style="width: 100%;" ' +
						'data-url="' + urlDespesasAdicionais + '" ' +
						' placeholder="' + l["selecione"] + '" >' +
						'<option value="" selected="selected"></option>' +
						'</select>' +
						'</td>';
					// valor da despesa 1
					htmlDespesasAdicionais += '' +
						'<td>' +
						'<input type="text" class="form-control conteudo-itens_valor_despesa1 valores somar_campos" ' +
						'value="" name="valorDespesa1[]" ' +
						'placeholder="' + l["preçoUnitário"] + '" ' +
						'data-maxdecimal="" />' +
						'</td>';
					// select da despesa 2
					htmlDespesasAdicionais += '' +
						'<td>' +
						'<select class="form-control select select_ajax select_ajax_FI conteudo-itens_despesa2" name="idDespesasAdicionais2[]" style="width: 200%;" ' +
						'data-url="' + urlDespesasAdicionais + '" ' +
						' placeholder="' + l["selecione"] + '" >' +
						'<option value="" selected="selected"></option>' +
						'</select>' +
						'</td>';
					// valor da despesa 2
					htmlDespesasAdicionais += '' +
						'<td>' +
						'<input type="text" class="form-control conteudo-itens_valor_despesa2 valores somar_campos" ' +
						'value="" name="valorDespesa2[]" ' +
						'placeholder="' + l["preçoUnitário"] + '" ' +
						'data-maxdecimal="" />' +
						'</td>';
					// select da despesa 3
					htmlDespesasAdicionais += '' +
						'<td>' +
						'<select class="form-control select select_ajax select_ajax_FI conteudo-itens_despesa3" name="idDespesasAdicionais3[]" style="width: 300%;" ' +
						'data-url="' + urlDespesasAdicionais + '" ' +
						' placeholder="' + l["selecione"] + '" >' +
						'<option value="" selected="selected"></option>' +
						'</select>' +
						'</td>';
					// valor da despesa 3
					htmlDespesasAdicionais += '' +
						'<td>' +
						'<input type="text" class="form-control conteudo-itens_valor_despesa3 valores somar_campos" ' +
						'value="" name="valorDespesa3[]" ' +
						'placeholder="' + l["preçoUnitário"] + '" ' +
						'data-maxdecimal="" />' +
						'</td>';
				}
                $("table#conteudoTable tbody").append('' +
                    '<tr>' +
                        '<td class="produtos">' +
                            '<select class="form-control select select_produtoId" name="produtoId[]" style="width: 100%;" data-url="' + urlGetProdAjax + '" data-placeholder="' + l["produto"] + '" data-travaselecao="1" data-init=\'' + JSON.stringify(retAjax) + '\'>' +
                                '<option value="" selected="selected"></option>' +
                            '</select>' +
                        '</td>' +
						'<input type="hidden" value="0" class="form-control" name="hasPedido[]" />' +
                        '<td>' +
                            '<input type="text" class="form-control" value="n/a" disabled="disabled" />' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" class="form-control quantidadeFaltando" value="0" disabled="disabled" />' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" ' +
                            'class="form-control quantidade" name="quantidade[]" ' +
                            'placeholder="' + l["quantidade"] + '"  data-mask="numerov2" ' +
                            'data-maxdecimal="" ' +
                            'data-thousand_delimiter="." ' +
                            'data-decimal_delimiter="," ' +
                            'data-bol_negative="false" value="" /> ' +
                        '</td>' +
                        '<td>' +
                            '<select class="form-control select select_medida" name="medida[]" style="width: 100%;">' +
                            '</select>' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" class="form-control precoUnitarioFaltando" value="0" disabled="disabled" />' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" class="form-control precoUnitario" ' +
                            'value="" name="precoUnitario[]" ' +
                            'placeholder="' + l["preçoUnitário"] + '" ' +
                            'data-maxdecimal="" />' +
                        '</td>' +
                        '<td>' +
                            '<select class="form-control select select_utilizacao" name="utilizacao[]" style="width: 100%;">' +
                                '<option value="" selected="selected"></option>' +
                            '</select>' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" class="form-control utilizacaoFaltando" value="n/a" disabled="disabled" />' +
                        '</td>' +
						'<td>' +
							'<select class="form-control select select_deposito" name="deposito[]" style="width: 100%;">' +
								'<option value="" selected="selected">' + l['depositoPadrao'] + '</option>' +
							'</select>' +
						'</td>' +
							htmlRegraDistr +
						    htmlDespesasAdicionais +
						'<td>' +
                            '<input type="text" class="form-control subTotal" ' +
                            'value="" name="total[]" ' +
                            'placeholder="' + l["totalDoItem"] + '" ' +
                            'data-maxdecimal="" disabled="disabled" /> ' +
                        '</td>' +
                        '<td> ' +
                            `<button type="button" class="${isOldLayout ? 'btn btn-danger' : 'button-form danger-button'} removeItens" 
								title="${l["retirarItemDoPedido!"]}"
							>
								<i class="fa fa-times"></i>
							</button> ` +
							`<button class="${isOldLayout ? 'btn btn-primary' : 'button-form primary-button'} controlar_lote 
								${(is_empty(retAjax.isControlarLote, 1) ? ' ocultar' : '')}" type="button" 
								title="${$(".data_views").data("lang_btn_lote")}"
							>
								${isOldLayout ? '<i class="fa fa-pencil-square-o"></i>' : '<span data-icon="mingcute:edit-line" class="iconify"></span>'}
							</button> ` +
							'<textarea class="ocultar conteudo-itens_lotes_json" name="lotes[]">' + (!is_empty(retAjax.isControlarLote, 1) && !is_empty(retAjax.lotes, 1) ? retAjax.lotes : '[]') + '</textarea> ' +
                        '</td> ' +
                    '</tr>'
                );

                trocaParametroCamposMaskNumerosV2($($("table#conteudoTable tbody tr input.precoUnitario").last()));
                trocaParametroCamposMaskNumerosV2($($("table#conteudoTable tbody tr input.subTotal").last()));
                $($("table#conteudoTable tbody tr input.quantidade").last()).val($(this).find('input.quantidadeModal').val());
                $($("table#conteudoTable tbody tr input.precoUnitario").last()).val($(this).find('input.precoUnitarioModal').val());

                var options        = $(this).find("select.select_medidaModal").html();
                var optionSelected = $(this).find("select.select_medidaModal").find('option:selected').val();
                $($("table#conteudoTable tbody tr select.select_medida").last()).append(options);
                $($("table#conteudoTable tbody tr select.select_medida").last()).find('option[value="' + optionSelected + '"]').prop('selected', 'selected');

                $.each(utilizacoes, function (idUtilizacoes, valueUtilizacoes) {
                    $($("table#conteudoTable tbody tr select.select_utilizacao").last()).append('' +
                        '<option value="' + valueUtilizacoes.idTipoUtilizacaoProduto + '">' + valueUtilizacoes.nomeTipoUtilizacaoProduto + '</option>'
                    );
                });
                $($("table#conteudoTable tbody tr select.select_utilizacao").last()).find("option:selected").prop('selected', '');
                $($("table#conteudoTable tbody tr select.select_utilizacao").last()).find("option[value='" + $(this).find("select.select_utilizacaoModal").find('option:selected').val() + "']").prop('selected', true);

				$.each(depositos, function (idDepositos, valueDepositos) {
					$($("table#conteudoTable tbody tr select.select_deposito").last()).append('' +
						'<option value="' + valueDepositos.codigoDepositoERP + '">' + valueDepositos.codigoDepositoERP + ' - ' + valueDepositos.nomeDeposito + '</option>'
					);
				});
				$($("table#conteudoTable tbody tr select.select_deposito").last()).find("option:selected").prop('selected', '');
				$($("table#conteudoTable tbody tr select.select_deposito").last()).find("option[value='" + $(this).find("select.select_depositoModal").find('option:selected').val() + "']").prop('selected', true);

				var xxx = $(this);
				$.each(regrasDistribuicao, function (idRegras, valueRegras) {
					$.each(valueRegras, function (key, regra) {
						$($("table#conteudoTable tbody tr select.select_regraDistribuicao[name='regraDistribuicao" + idRegras + "[]']").last()).append('' +
							'<option value="' + regra.idregra + '">' + regra.nomeregra + '</option>'
						);
					});
					$($("table#conteudoTable tbody tr select.select_regraDistribuicao[name='regraDistribuicao" + idRegras + "[]']").last()).find("option:selected").prop('selected', '');
					$($("table#conteudoTable tbody tr select.select_regraDistribuicao[name='regraDistribuicao" + idRegras + "[]']").last()).find("option[value='" + xxx.find('select.select_regraDistribuicaoModal.regraDistribuicaoModal'+idRegras).find('option:selected').val() + "']").prop('selected', true);
				});

				// preenche o select com todas as despesas do json vindo da div oculta
				if(!is_empty(flagUtilizarDespesasAdicionaisLinhaItem,1)) {
					// Despesas 1
					$.each(despesasAdicionais, function (idDespesasAd, valueDespesasAdicionais) {
						$($("table#conteudoTable tbody tr select.conteudo-itens_despesa1").last()).append('' +
							'<option value="' + valueDespesasAdicionais.idDespesasAdicionais + '">' + valueDespesasAdicionais.nomeDespesasAdicionais + '</option>'
						);

					});
					// Despesas 2
					$.each(despesasAdicionais, function (idDespesasAd, valueDespesasAdicionais) {
						$($("table#conteudoTable tbody tr select.conteudo-itens_despesa2").last()).append('' +
							'<option value="' + valueDespesasAdicionais.idDespesasAdicionais + '">' + valueDespesasAdicionais.nomeDespesasAdicionais + '</option>'
						);
					});
					// Despesas 3
					$.each(despesasAdicionais, function (idDespesasAd, valueDespesasAdicionais) {
						$($("table#conteudoTable tbody tr select.conteudo-itens_despesa3").last()).append('' +
							'<option value="' + valueDespesasAdicionais.idDespesasAdicionais + '">' + valueDespesasAdicionais.nomeDespesasAdicionais + '</option>'
						);
					});

					// preenche os dados da despesa 1
					$($("table#conteudoTable tbody tr select.conteudo-itens_despesa1").last()).find("option:selected").prop('selected', '');
					$($("table#conteudoTable tbody tr select.conteudo-itens_despesa1").last()).find("option[value='" + $(this).find("select.conteudo-itens_despesa1 option:selected").val() + "']").prop('selected', true);
					$($("table#conteudoTable tbody tr input.conteudo-itens_valor_despesa1").last()).val($(this).find("input.conteudo-itens_valor_despesa1").val());
					// preenche os dados da despesa 2
					$($("table#conteudoTable tbody tr select.conteudo-itens_despesa2").last()).find("option:selected").prop('selected', '');
					$($("table#conteudoTable tbody tr select.conteudo-itens_despesa2").last()).find("option[value='" + $(this).find("select.conteudo-itens_despesa2 option:selected").val() + "']").prop('selected', true);
					$($("table#conteudoTable tbody tr input.conteudo-itens_valor_despesa2").last()).val($(this).find("input.conteudo-itens_valor_despesa2").val());
					// preenche os dados da despesa 3
					$($("table#conteudoTable tbody tr select.conteudo-itens_despesa3").last()).find("option:selected").prop('selected', '');
					$($("table#conteudoTable tbody tr select.conteudo-itens_despesa3").last()).find("option[value='" + $(this).find("select.conteudo-itens_despesa3 option:selected").val() + "']").prop('selected', true);
					$($("table#conteudoTable tbody tr input.conteudo-itens_valor_despesa3").last()).val($(this).find("input.conteudo-itens_valor_despesa3").val());
				}
            }
        });

        $('.subTotal').data('maxdecimal', $('.data_views').data('casas_preco'));
        $('.quantidade').data('maxdecimal', $('.data_views').data('casas_qtd'));
        $('.precoUnitario').data('maxdecimal', $('.data_views').data('casas_preco'));
        $($("table#conteudoTable tbody tr").first()).find('button.removeItens').prop('disabled', 'disabled');

        allFunctions();
        ajaxSelectUM();
        criaSelects();
        addButtonsRemoveItens();
        somaCampos();
        addSomaCampos();
		controlaLote();
        $('select.select_produtoId').data('travaselecao', 0);

        $('.modal.modalItensPorGrupo').modal('hide');
    });
}

function setItensModalGrupo(){
    $("button.grupoItens").unbind('click');
    $("button.grupoItens").click(function (){
        var cabecalho        = $(".modal.modalItensPorGrupo .modal-content .modal-header :header");
        var nomeGrupoOnError = $(this).text();
        var corpo            = $(".modal.modalItensPorGrupo .modal-content .modal-body");
        var idGrupo          = $(this).data('id');
        var fornecedor       = $('.fornecedor_id').data('id_fornecedor');
        var urjJsonProdGrupo = $('.data_views').data('url_json_prod_grupo');

        $(cabecalho).html(l["carregandoGrupo"]);
		const loadingIcon = (
			isOldLayout
				? '<i class="fa fa-spinner fa-pulse fa-fw"></i>'
				: '<span data-icon="eos-icons:bubble-loading" class="iconify fs-6 my-3"></span>'
		);
		$(corpo).html(loadingIcon + l["aguarde,CarregandoItens"]);

        if(fornecedor !== null && fornecedor !== '' && fornecedor !== undefined){
            if(idGrupo !== null && idGrupo !== '') {
                var condicao         = 'c';
                var linhasProdutos   = [];

                $("table#conteudoTable tbody tr").each(function () {
                    linhasProdutos.push({
                        idProduto: $(this).find("input.idItemPedido").length ? $(this).find("input.idItemPedido").val() : $(this).find("select.select_produtoId").val(),
                        quantidade: $(this).find("input.quantidade").val(),
                        precoUnitario: formataFloat(
                            $(this).find("input.precoUnitario").val(),
                            $($(".select_moeda").find('option:selected')).data('sep_decimal'),
                            $('.data_views').data('casas_preco')
                        ),
                        medida: $(this).find("select.select_medida").val(),
                        utilizacao: $(this).find("select.select_utilizacao").val(),
						depositoSelecionado:    $(this).find(".select_deposito option:selected").val(),
						regraDistribuicao:      $(this).find(".select_regraDistribuicao option:selected").val(),
						nomeDespesasAdicionais1:$(this).find(".conteudo-itens_despesa1 option:selected").text(),
						idDespesasAdicionais1:  $(this).find(".conteudo-itens_despesa1 option:selected").val(),
						valorDespesa1:          formataFloat(
							$(this).find("input.conteudo-itens_valor_despesa1").val(),
							$($(".select_moeda").find('option:selected')).data('sep_decimal'),
							$('.data_views').data('casas_preco')
						),
						nomeDespesasAdicionais2:$(this).find(".conteudo-itens_despesa2 option:selected").text(),
						idDespesasAdicionais2:  $(this).find(".conteudo-itens_despesa2 option:selected").val(),
						valorDespesa2:          formataFloat(
							$(this).find("input.conteudo-itens_valor_despesa2").val(),
							$($(".select_moeda").find('option:selected')).data('sep_decimal'),
							$('.data_views').data('casas_preco')
						),
						nomeDespesasAdicionais3:$(this).find(".conteudo-itens_despesa3 option:selected").text(),
						idDespesasAdicionais3:  $(this).find(".conteudo-itens_despesa3 option:selected").val(),
						valorDespesa3:          formataFloat(
							$(this).find("input.conteudo-itens_valor_despesa3").val(),
							$($(".select_moeda").find('option:selected')).data('sep_decimal'),
							$('.data_views').data('casas_preco')
						),
						lotes: $($(this).find(".conteudo-itens_lotes_json")).val(),
                    });
                });


                ajaxRequest(true, urjJsonProdGrupo, null, 'text', {
                    'idGrupo': idGrupo,
                    'condicao': condicao,
                    'fornecedor': fornecedor,
                    'moeda': $(".select_moeda").val(),
                    'linhasProdutos': JSON.stringify(linhasProdutos)
                }, function(ret){
                    if(!is_empty(ret) && ret !== 'erro'){
                        $(corpo).html(ret);
                        $(cabecalho).html($(corpo).find('div#vars div#grupo').html());
                        allFunctions();
                        criaSelectsModal();
                        addClickButtonModal();
                        scrollDown();
                    }else{
                        $(cabecalho).html(nomeGrupoOnError);
                        $(corpo).html(l["itensNãoEncontrados"]);
                    }
                });
            }else{
                $(cabecalho).html(l["grupoNãoEncontrado"]);
                $(corpo).html(l["itensNãoEncontrados"]);
            }
        }else{
            $(cabecalho).html(nomeGrupoOnError);
            $(corpo).html(l["fornecedorNãoEncontrado"]);
        }
    });
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

			let isControlarLotes = $(".data_views").data("is_controlar_lote");
			if(is_empty(produto, 1) || is_empty(isControlarLotes, 1)) {
				$($(this).parents('tr').find('.conteudo-itens_lotes_json')).val("[]");
				$($(this).parents('tr').find('.controlar_lote')).addClass("ocultar");
			} else {
				let urlLote = $("div.data_views").data("url_verifica_produto_adm_lote");
				let objSelectProd = $(this);
				ajaxRequest(true, urlLote, null, 'text', {'idProduto': produto}, function (ret) {
					$($(objSelectProd).parents('tr').find('.conteudo-itens_lotes_json')).val("[]");
					if(!is_empty(ret, 1)) {
						$($(objSelectProd).parents('tr').find('.controlar_lote')).removeClass("ocultar");
					} else {
						$($(objSelectProd).parents('tr').find('.controlar_lote')).addClass("ocultar");
					}
				});
			}
        }

        $(this).data('travaselecao', 0);
    });
}

function somaCamposLotes() {
	let casasQtd = $("div.data_views").data('casas_qtd');
	const linhas = $("table#conteudo-lotes-tabela tbody tr:not(.ocultar)");

	if(is_empty(casasQtd, 1)) casasQtd = '0';
	casasQtd = parseInt(casasQtd.toString());

	let valorTotaisLinhas = 0;
	$.each(linhas, function (idLinha, linha) {
		valorTotaisLinhas += stringParaFloat(
			$(linha).find('.conteudo-lote_quantidade').val(), configLocation.currencyDecimalPoint, true
		);
	});
	valorTotaisLinhas =
		stringParaFloat(
			valorTotaisLinhas.toFixed(casasQtd).toString(),
			'.',
			true
		);

	$('#conteudo-lotes_total_geral').text(
		formataDecimal(
			valorTotaisLinhas,
			'.',
			",",
			"",
			"",
			true,
			casasQtd
		)
	);
}

function atualizaLinhasLotes() {
	let index = -1;
	$("table#conteudo-lotes-tabela tbody tr .conteudo-lotes_id_interno").each(function () {
		$(this).val(index);
		index++;
	});
}

function contaCharObservacoes() {
    var controleObs = $('.data_views').data('controle_obs');
    if(is_empty(controleObs, 1)) {
        var maxLenObs = 254;
        var char = 0;
        $('#observacoes').keyup(function () {
            var len = $(this).val().length;
            if (len > maxLenObs) {
                char = 0;
                $(this).val($(this).val().substring(0, maxLenObs));
            } else {
                char = maxLenObs - len;
            }
            $('p#numChars').text(char + ' ' + l["caracteresRestantes"]);
        });
        char = maxLenObs - $('#observacoes').val().length;
        $('p#numChars').text(char + ' ' + l["caracteresRestantes"]);
    }
}

function somaCampos(){
    var total = 0;
    $("table#conteudoTable tbody tr").each(function () {
        var subtotal =
            formataFloat(
                $(this).find('input.quantidade').val(),
				configLocation.currencyDecimalPoint,
                $('.data_views').data('casas_qtd')
            )  *
            formataFloat(
                $(this).find('input.precoUnitario').val(),
                $($(".select_moeda").find('option:selected')).data('sep_decimal'),
                $('.data_views').data('casas_preco')
            );
			var despesasAdicionais = formataFloat(
				$(this).find('input.conteudo-itens_valor_despesa1').val(),
				$($(".select_moeda").find('option:selected')).data('sep_decimal'),
				$('.data_views').data('casas_preco')
			) + formataFloat(
				$(this).find('input.conteudo-itens_valor_despesa2').val(),
				$($(".select_moeda").find('option:selected')).data('sep_decimal'),
				$('.data_views').data('casas_preco')
			) + formataFloat(
				$(this).find('input.conteudo-itens_valor_despesa3').val(),
				$($(".select_moeda").find('option:selected')).data('sep_decimal'),
				$('.data_views').data('casas_preco')
			);
			subtotal += despesasAdicionais;

        $(this).find('input.subTotal').val(number_format(
            subtotal,
            $('.data_views').data('casas_preco'),
            $($(".select_moeda").find('option:selected')).data('sep_decimal'),
            $($(".select_moeda").find('option:selected')).data('sep_milhar'),
            $($(".select_moeda").find('option:selected')).data('texto_impressao')
        ));
        total += subtotal;
    });
    $("table#conteudoTable input#totalGeral").val(number_format(
        total,
        $('.data_views').data('casas_preco'),
        $($(".select_moeda").find('option:selected')).data('sep_decimal'),
        $($(".select_moeda").find('option:selected')).data('sep_milhar'),
        $($(".select_moeda").find('option:selected')).data('texto_impressao')
    ));

    $("table#despesasTable tbody tr input.valorDespesa").each(function () {
        total +=
            formataFloat(
                $(this).val(),
                $($(".select_moeda").find('option:selected')).data('sep_decimal'),
                $('.data_views').data('casas_preco')
            );
    });
    $("div#conteudo input#valorTotalFinal").val(number_format(
        total,
        $('.data_views').data('casas_preco'),
        $($(".select_moeda").find('option:selected')).data('sep_decimal'),
        $($(".select_moeda").find('option:selected')).data('sep_milhar'),
        $($(".select_moeda").find('option:selected')).data('texto_impressao')
    ));
}

function addSomaCampos(){
	$("input.quantidade, " +
		"input.precoUnitario, " +
		"input.conteudo-itens_valor_despesa1, " +
		"input.conteudo-itens_valor_despesa2, " +
		"input.conteudo-itens_valor_despesa3, " +
		"input.valorDespesa").unbind('keyup').keyup(somaCampos);
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

function addButtonsRemoveDespesas() {
    $('table#despesasTable button.removeDespesa').unbind('click');
    $('table#despesasTable button.removeDespesa').click(function () {
        var rem = $(this).parents('tr');

        rem.fadeOut(270, function () {
            rem.remove();
            somaCampos();
        });

    });
}

$('table#conteudoTable button.addItens').click(function () {
    var urlGetProdAjax = $('.data_views').data('url_ajax_get_prod');
    var modelo   = $('table#conteudoTable tbody tr').first().html();

    $('div#conteudo table#conteudoTable tbody').append('<tr>' + modelo + '</tr>');

    var lastTr = $($('div#conteudo table#conteudoTable tbody tr').last());
    $(lastTr).find('td.produtos').html('' +
        '<select class="form-control select select_produtoId" name="produtoId[]" style="width: 100%;" data-url="' + urlGetProdAjax + '" data-placeholder="' + l["produto"] + '" data-travaselecao="0">' +
        '</select>'
    );

    $(lastTr).find('button.removeItens').prop('disabled', false);
    $('table#conteudoTable tbody tr .select').select2Reset();

    $(lastTr).append('' +
        '<input type="hidden" value="0" class="form-control" name="hasPedido[]" />'
    );
    $(lastTr).find('input[type="text"]').prop('value', '');
    $(lastTr).find('select.select_produtoId').find('option').remove();
    $(lastTr).find('input.quantidadeFaltando').prop('value', '0');
    $(lastTr).find('input.precoUnitarioFaltando').prop('value', 'R$ 0,00');
    $(lastTr).find('input.medidaFaltando, input.utilizacaoFaltando').prop('value', 'n/a');
    $(lastTr).find('select.select_medida').find('option').remove();
    $(lastTr).find('select.select_medida').append('<option value=""></option>');
    $(lastTr).find('select').find('option:selected').prop('selected', false);
    $(lastTr).find('select.select_medida option[value=""]').prop('selected', 'selected');
	// limpa os selects das despesas adicionais que possui a class select_ajax
	$(lastTr).find('select.select_ajax').find('option').remove();
	$(lastTr).find('select.select_ajax').data('init', '');

    $('select.select_produtoId').data('travaselecao', 1);
    allFunctions();
    ajaxSelectUM();
    criaSelects();
    addButtonsRemoveItens();
    addSomaCampos();
	controlaLote();
    $('select.select_produtoId').data('travaselecao', 0);
});

$('table#despesasTable button.addDespesa').click(function () {
    var modelo = $('table#despesasTable tbody tr').first().html();
    $('div#conteudo table#despesasTable tbody').append('<tr>' + modelo + '</tr>');
    $($('table#despesasTable tbody tr').last()).find('button.removeDespesa').prop('disabled', false);
    $('table#despesasTable tbody tr .select').select2Reset();

    var limpaCampos = $($('table#despesasTable tbody tr').last());
    $(limpaCampos).find('input[type="text"]').prop('value', '');
    $(limpaCampos).find('select.select_medida').find('option').remove();
    $(limpaCampos).find('select.select_medida').append('<option value=""></option>');
    $(limpaCampos).find('select.select_medida option[value=""]').prop('selected', 'selected');
    $(limpaCampos).find('select').find('option:selected').prop('selected', false);
	
	$(limpaCampos).find('select.select_ajax').find('option').remove();
	$(limpaCampos).find('select.select_ajax').data('init', '');
    allFunctions();
    criaSelects();
    addButtonsRemoveDespesas();
    addSomaCampos();
});

function addPorGrupos(){
    $('button.addPorGrupos').unbind('click');
    $('button.addPorGrupos').click(function () {
        if($('.grupos').css("display") === 'none'){
            $('button.addPorGrupos').text(l["esconderItensPorGrupo"]);
            $('.grupos').css("display", 'block');
        }else{
            if($('.grupos').css("display") === 'block'){
                $('button.addPorGrupos').text(l["adicionarItensPorGrupo"]);
                $('.grupos').css("display", 'none');
            }
        }
    });
}

function controlaLote() {
	if(is_empty($(".data_views").data("is_controlar_lote"), 1)) {
		return;
	}
	let trAddLote = null;

	$('button.controlar_lote').off('click');
	$('button.controlar_lote').on("click", function () {
		trAddLote = $($(this).parents("tr"));
		let lotes = JSON.parse($($(trAddLote).find(".conteudo-itens_lotes_json")).val());
		$($($('#conteudo-lotes-tabela tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");

		let casasQtd = $("div.data_views").data('casas_qtd');
		if(is_empty(casasQtd, 1)) casasQtd = '0';
		casasQtd = parseInt(casasQtd.toString());

		$.each(lotes, function (idx, lote) {
			$('#conteudo-lotes-tabela tfoot .add-itens-table-geral').trigger("click");
			$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lotes_id_interno")).val(lote.uidLinhaLote);
			$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_nome")).val(lote.nome);
			$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_quantidade")).val(
				formataDecimal(
					lote.quantidade,
					".",
					configLocation.currencyDecimalPoint,
					"",
					"",
					true,
					casasQtd
				)
			);
			if(!is_empty(lote.dataFabricacao, 1)) {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_data_fabricacao")).val(moment(lote.dataFabricacao, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
			}
			if(!is_empty(lote.dataVencimento, 1)) {
				$($('#conteudo-lotes-tabela tbody tr:last').find(".conteudo-lote_data_vencimento")).val(moment(lote.dataVencimento, "YYYY-MM-DD").format($('div.data_views').data('format_date')));
			}
		});
		$("table#conteudo-lotes-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
			somaCamposLotes();
		});
		somaCamposLotes();
		lotes = null;

		$('.modal-lote').modal('toggle');
	});

	$('.modal-lote button.btn-salvar-lote_itens').off('click');
	$('.modal-lote button.btn-salvar-lote_itens').on("click", function () {
		let lotes = [];
		let casasQtd = $("div.data_views").data('casas_qtd');

		if(is_empty(casasQtd, 1)) casasQtd = '0';
		casasQtd = parseInt(casasQtd.toString());

		$($("#conteudo-lotes-tabela tbody tr").not(':first')).each(function () {
			let obj = $(this);
			let push = {
				"uidLinhaLote": $(obj).find(".conteudo-lotes_id_interno").val(),
				"nome": $(obj).find(".conteudo-lote_nome").val(),
				"quantidade": formataDecimal(
					$(obj).find(".conteudo-lote_quantidade").val(),
					configLocation.currencyDecimalPoint,
					".",
					"",
					"",
					true,
					casasQtd
				),
				"dataFabricacao": $(obj).find(".conteudo-lote_data_fabricacao").val(),
				"dataVencimento": $(obj).find(".conteudo-lote_data_vencimento").val(),
			};
			if(!is_empty(push['dataFabricacao'], 1)) {
				push['dataFabricacao'] = moment(push['dataFabricacao'], $('div.data_views').data('format_date')).format('YYYY-MM-DD');
			} else {
				push['dataFabricacao'] = null;
			}
			if(!is_empty(push['dataVencimento'], 1)) {
				push['dataVencimento'] = moment(push['dataVencimento'], $('div.data_views').data('format_date')).format('YYYY-MM-DD');
			} else {
				push['dataVencimento'] = null;
			}

			lotes.push(push);
		});

		if(trAddLote !== null && trAddLote.length === 1) {
			$($(trAddLote).find(".conteudo-itens_lotes_json")).val(JSON.stringify(lotes));
		}

		trAddLote = null;
		$('.modal-lote').modal('toggle');
	});
}

$('select.select_produtoId').data('travaselecao', 1);
addPorGrupos();
setItensModalGrupo();
ajaxSelectUM();
criaSelects();
addButtonsRemoveItens();
addButtonsRemoveDespesas();
addSomaCampos();
somaCampos();
contaCharObservacoes();
controlaLote();
controlaTabelaSuite({
	"ref": "#conteudo-lotes-tabela",
	"funAposAddItem": function () {
		atualizaLinhasLotes();
		$("table#conteudo-lotes-tabela tbody tr:not(.ocultar) .somar_campos").off("keyup").on("keyup", function() {
			somaCamposLotes();
		});
		somaCamposLotes();
	},
	"funAposRemoverItem": function () {
		atualizaLinhasLotes();
		somaCamposLotes();
	}
});
$('select.select_produtoId').data('travaselecao', 0);
ativaMudancaMoeda();