$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

$(document).ready(function () {
    $('#inp-codigo, #inp-tempo_servico').on('input', function (e) {
        let clean_value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(clean_value);
    })
})

$('input[name="codigo_cnae"]').on('blur', function () {
    if (!$(this).val()) {
        return;
    }

    let clean_value = $(this).val().replace(/[^0-9]/g, '');

    if (clean_value.length != 7) {
        Swal.fire({
            title: 'Código CNAE inválido',
            text: 'O código informado não corresponde ao padrão CNAE (00.00-0/00)',
            icon: 'error'
        });

        initializeTooltip($(this), 'Código fora do padrão CNAE.');
        $(this).addClass('is-invalid');

        return;
    }

    $(this).removeClass('is-invalid');
    $(this).tooltip('dispose');

})

$('#modal_servico').on('show.bs.modal', function (e) {
    getFuncionariosForSelect2('#modal_servico');
})

function getFuncionariosForSelect2 (parent_modal = null) {
    $('select[name="funcionario_id"]').select2({
        minimumInputLength: 2,
        language: 'pt-BR',
        placeholder: 'Digite para buscar o colaborador',
        dropdownParent: parent_modal,
        ajax: {
            cache: true,
            url: path_url + 'api/funcionarios/pesquisa',
            dataType: 'json',
            data: function (params) {
                console.clear();
                var query = {
                    pesquisa: params.term,
                    empresa_id: $('#empresa_id').val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];

                $.each(response, function (i, v) {
                    var o = {};
                    o.id = v.id;

                    o.text =
                        v.nome +
                        ' - Cargo: ' +
                        v.cargo;
                    o.value = v.id;
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    });
}

$(document).ready(function () {
    $('.btn-store-categoria-servico').on('click', function (e) {
        e.preventDefault();

        if (!$('#inp-nome_categoria').val()) {
            new swal('Erro', 'Informe o nome da categoria', 'error');
            return;
        }

        let item = {
            nome: $('#inp-nome_categoria').val(),
            marketplace: $('#inp-is_marketplace').val(),
            empresa_id: $('#empresa_id').val(),
        };

        $.post(path_url + 'api/categoria-servico/store-categoria-servico', item)
            .done((result) => {
                let new_option = new Option(result.categoria.nome, result.categoria.id, false, true);
                $('#inp-categoria_id').append(new_option);
                $('#inp-categoria_id').trigger('change');
                $('#modal_categoria_servico').modal('hide');

                new swal('Sucesso', 'Categoria de serviço cadastrada!', 'success')
            })
            .fail((error) => {
                console.log(error)
                $('#modal_categoria_servico').modal('hide');
                new swal('Ocorreu um erro', error.responseJSON.message, 'error')
            })
    })

    setTimeout(() => {
        $('#inp-servico_id').change(() => {
            let servico_id = $('#inp-servico_id').val();
            if (servico_id) {
                $.get(path_url + 'api/ordemServico/find/' + servico_id)
                    .done((e) => {
                        let qntd = $('#inp-quantidade').val('1,00');
                        let qtd = parseFloat(qntd.val().replace(',', '.'));

                        if (isNaN(qtd)) {
                            qtd = 0;
                        }
                        let conta = e.valor * qtd;

                        $('#inp-nome').val(e.nome);
                        $('#inp-valor').val(convertFloatToMoeda(e.valor));
                        $('#inp-subtotal').val(
                            conta.toFixed(2).replace('.', ','),
                        );
                    })
                    .fail((e) => {
                        console.log(e);
                    });
            }
        });
    }, 100);
})

function handleDateFields () {
    const tempo_execucao_input_line = $('.table-duracao-servico tbody tr'); 

    const dia_input = tempo_execucao_input_line.find('input[name="dias"]');
    const horas_input = tempo_execucao_input_line.find('input[name="horas"]');
    const minutos_input = tempo_execucao_input_line.find('input[name="minutos"]');

    if (parseInt(horas_input.val()) >= 24) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'O valor de horas deve ser menor do que 24.',
        }).then(() => {
            Swal.fire({
                icon: 'question',
                title: 'Deseja aumentar a quantidade de dias?',
                text: 'Sera adicionado 1 dia automaticamente.',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                cancelButtonText: 'Nao',
            }).then((result) => {
                if (result.isConfirmed) {
                    horas_input.val(null);
                    dia_input.val(parseInt(dia_input.val() || 0) + 1);
                } else {
                    dia_input.val(null);
                }
            })
        })

        return;
    }

    if (parseInt(minutos_input.val()) >= 60) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'O valor de minutos deve ser menor do que 60.',
        }).then(() => {
            Swal.fire({
                icon: 'question',
                title: 'Deseja aumentar a quantidade de horas?',
                text: 'Sera adicionado 1 hora automaticamente.',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                cancelButtonText: 'Nao',
            }).then((result) => {
                if (result.isConfirmed) {
                    minutos_input.val(null);
                    horas_input.val(parseInt(horas_input.val() || 0) + 1).trigger('change');
                } else {
                    horas_input.val(null);
                }
            })
        })

        return;
    }
}

$(document).delegate('input[name="horas"], input[name="minutos"]', 'change', function () {
    handleDateFields();
});

function validateTempoExecucao () {
    const tempo_execucao_input_line = $('.table-duracao-servico tbody tr'); 

    const dia_input = tempo_execucao_input_line.find('input[name="dias"]');
    const horas_input = tempo_execucao_input_line.find('input[name="horas"]');
    const minutos_input = tempo_execucao_input_line.find('input[name="minutos"]');

    if (!dia_input.val() && !horas_input.val() && !minutos_input.val()) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: 'Determine qual será o tempo de execução  .',
        })

        return false;
    }

    return true;
}

$('#btn-store').on('click', function (e) {
    e.preventDefault();

    if (addClassRequired('#main-form') && validateTempoExecucao()) {
        $('#main-form').trigger('submit');
    }
});

$('#btn-store-modal-servico').on('click', function (e) {
    e.preventDefault();

    if (!addClassRequired('#form-modal-servico', true)) return;

    const form = document.getElementById('form-modal-servico');
    const formData = new FormData(form);
    formData.append('empresa_id', $('#empresa_id').val());

    $.ajax({
        url: '/api/servicos',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.success) {
                $('#modal_servico').modal('hide');
                new swal('Sucesso', 'Serviço cadastrado com sucesso!', 'success');

                const servico = res.servico;

                const option = new Option(
                    servico.nome + ' R$ ' + convertFloatToMoeda(servico.valor),
                    servico.id,
                    false,
                    false
                );
                $(option)
                    .attr('data-id', servico.id)
                    .attr('data-valor', servico.valor)
                    .attr('data-tempo', servico.tempo_servico);

                const selects = $('select.servico_id');

                selects.each(function (index) {
                    const $select = $(this);
                    const isMultiple = $select.prop('multiple');
                    const currentValues = $select.val() || [];

                    if (!$select.find(`option[value="${servico.id}"]`).length) {
                        $select.append($(option).clone());
                    }

                    if (index === selects.length - 1) {
                        if (isMultiple) {
                            currentValues.push(servico.id.toString());
                            $select.val([...new Set(currentValues)]).trigger('change');
                        } else {
                            $select.val(servico.id).trigger('change');
                        }

                        $select[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    } else {
                        $select.val(currentValues).trigger('change');
                    }
                });

                form.reset();
                $('#form-modal-servico select').val(null).trigger('change');
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Ocorreu um problema ao cadastrar o serviço.';
            new swal('Erro ao cadastrar serviço.', msg, 'error');
        }
    });
});