// Eventos e funções dos SERVIÇOS

getServicosForSelect2();
function getServicosForSelect2 () {
    $('select.servico_id').each((id, element) => {
        const parent = $(element).closest('.modal');

        $(element).select2({
            minimumInputLength: 2,
            language: 'pt-BR',
            placeholder: 'Digite para buscar o serviço',
            width: '100%',
            dropdownParent: parent.length ? parent : $(document.body),
            ajax: {
                cache: true,
                url: path_url + 'api/servicos',
                dataType: 'json',
                data: function (params) {
                    return {
                        pesquisa: params.term,
                        empresa_id: $('#empresa_id').val(),
                    };
                },
                processResults: function (response) {
                    calcSubtotalServicos();

                    return {
                        results: response.map((v) => ({
                            id: v.id,
                            text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor),
                        })),
                    };
                },
            },
        });
        $(element).on('change', function () {
            let $row = $(this).closest('tr');

            let servico_id = $(this).val();
            if (servico_id) {
                $.get(path_url + 'api/ordemServico/find/' + servico_id)
                    .done((e) => {
                        $row.find('.qtd-servico').val('1');
                        $row.find('.valor_unitario-servico').val(
                            convertFloatToMoeda(e.valor),
                        ); 
                        $row.find('.subtotal-servico').val(
                            convertFloatToMoeda(e.valor),
                        );
                        $row.find('input[name="tempo_execucao[]"]').val(e.tempo_execucao);

                        calcSubtotalServicos();
                    })
                    .fail((e) => {
                        console.log(e);
                    });
            }
        });
    })
}

function calcSubtotalServicos () {
    let subtotal_servicos = 0;

    $('.subtotal-servico').each(function () {
        subtotal_servicos += convertMoedaToFloat($(this).val());
    })

    $('#subtotal-servicos').val(`R$ ${convertFloatToMoeda(subtotal_servicos)}`);
    $('.total-servicos').text(`R$ ${convertFloatToMoeda(subtotal_servicos)}`);
}

$(document).on('blur', '.qtd-servico', function () {
    if ($(this).val() <= 0 ) return;

    let line = $(this).closest('tr');
    let valor_unitario = convertMoedaToFloat(line.find('.valor_unitario-servico').val());

    line.find('.subtotal-servico').val(convertFloatToMoeda(valor_unitario * $(this).val()))
    calcSubtotalServicos();
})

// Calcula o subtotal dos serviços quando os selects de serviços for alterado
$(document).on('change change.select2', '.servico_id', function () {
    calcSubtotalServicos();
})

// Eventos e funções dos PRODUTOS

$(document).on('blur', '.qtd-produto', function () {
    if ($(this).val() <= 0) return;

    let line = $(this).closest('tr');
    let valor_unitario = convertMoedaToFloat(line.find('.valor_unitario-produto').val());

    line.find('.subtotal-produto').val(convertFloatToMoeda(valor_unitario * $(this).val()))
    calcSubtotalProdutos();
})

function calcSubtotalProdutos() {
    let subtotal_produtos = 0;

    $('.subtotal-produto').each(function () {
        subtotal_produtos += convertMoedaToFloat($(this).val());
    })

    $('.subtotal-produtos').val(`R$ ${convertFloatToMoeda(subtotal_produtos)}`);
    $('.total-produtos').text(`R$ ${convertFloatToMoeda(subtotal_produtos)}`);
}

function getProdutosForSelect2() {
    $('tbody select.produto_id').each((id, element) => {
        const parent = $(element).closest('.modal');

        $(element).select2({
            minimumInputLength: 2,
            language: 'pt-BR',
            placeholder: 'Digite para buscar o produto',
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: parent.length ? parent : null,
            ajax: {
                cache: true,
                url: path_url + 'api/produtos',
                dataType: 'json',
                data: function (params) {
                    let empresa_id = $('#empresa_id').val();
                    var query = {
                        pesquisa: params.term,
                        empresa_id: empresa_id,
                    };
                    return query;
                },
                processResults: function (response) {
                    var results = [];
                    let compra = 0;
                    if ($('#is_compra') && $('#is_compra').val() == 1) {
                        compra = 1;
                    }
                    $.each(response, function (i, v) {
                        results.push(parseProduto(v, compra));
                    });

                    calcSubtotalProdutos();

                    return {
                        results,
                    };
                },
            },
        });
        $(element).on('change', function () {
            let $row = $(this).closest('tr');

            let produto_id = $(this).val();
            if (produto_id) {
                $.get(path_url + 'api/ordemServico/findProduto/' + produto_id)
                    .done((e) => {
                        $row.find('.qtd-produto').val('1');
                        $row.find('.valor_unitario-produto').val(
                            convertFloatToMoeda(e.valor_unitario),
                        );
                        $row.find('.subtotal-produto').val(
                            convertFloatToMoeda(e.valor_unitario),
                        );

                        calcSubtotalProdutos();
                    })
                    .fail((e) => {
                        console.log(e);
                    });
            }
        });

    })
}

// Calcula o subtotal dos produtos quando os selects de produtos for alterado
$(document).on('change change.select2', '.produto_id', function () {
    calcSubtotalProdutos();
})

// Eventos que se aplicam tanto para o serviço quanto para o produto

$(document).on('change', '#inp-desconto', function () {
    let subtotal_val = convertMoedaToFloat($('#inp-total').val());
    let desconto_val = convertMoedaToFloat($(this).val());

    if (desconto_val > subtotal_val) {
        new swal(
            'Atenção!',
            'O desconto não pode ser maior que o subtotal',
            'warning',
        )
        $(this).val('');

        return;
    }
})
$('.btn-add-tr').off('click').on('click', function () {
    let table = $(this).closest('.row').siblings('.table-dynamic')
    let is_empty = false;

    table.find('input').each(function () {
        if (
            ($(this).val() == '' || $(this).val() == null) &&
            $(this).attr('type') != 'hidden' &&
            $(this).attr('type') != 'file' &&
            !$(this).hasClass('ignore')
        ) {
            is_empty = true;
        }
    });

    if (is_empty) {
        new swal(
            'Atenção',
            'Preencha todos os campos antes de adicionar novos.',
            'warning',
        );
        return;
    }
    let tr = table.find('.dynamic-form').first();
    if (tr.length === 0 ) return;

    let clone = tr.clone();
    clone.find('input, select').val('');
    clone.find('.select2-container').remove();
    clone.find('select.produto_id, select.servico_id')
        .removeClass('select2-hidden-accessible')
        .removeAttr('data-select2-id')
        .removeAttr('aria-hidden')
        .removeAttr('tabindex');

    table.append(clone);

    setTimeout(() => {
        clone[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 50);

    if ($(this).data('content') == 'produtos') {
        if (typeof getProdutosForSelect2 === 'function') {
            getProdutosForSelect2();
        }
    }

    if ($(this).data('content') == 'servicos') {
        getServicosForSelect2();
    }
});

$(document).delegate('.os-btn-remove-tr', 'click', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Você tem certeza?',
        text: 'Deseja remover esse item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ok',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const $tr = $(this).closest('tr');

            $tr.find('.form-control').val('');
            $tr.find('select').val(null).trigger('change');

            const $tbody = $tr.closest('tbody');
            const rowCount = $tbody.find('tr.dynamic-form:visible').length;

            if (rowCount > 1) {
                $tr.remove();
            } else {

                $tr.find('.form-control').val('');
                $tr.find('select').val(null).trigger('change');
            }

            calcSubtotalServicos();
        }
    });
});