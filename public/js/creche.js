$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

// ----------
// Configuração dos campos select2

$(document).ready(function () {
    let selectedService = null;
    let reopen_modal_novo_agendamento_creche = false;

    /**
     * Configura o select2 do animal
    */
    function getAnimaisForSelect2() {
        const parent_modal = $('#modal_novo_agendamento_creche');
    
        $(parent_modal ? parent_modal : $('body')).find('select[name="animal_id"]').each(function () {
            $(this).select2({
                minimumInputLength: 2,
                language: 'pt-BR',
                placeholder: 'Digite para buscar o animal (pet)',
                dropdownParent: parent_modal.length > 0 ? parent_modal : null,      
                ajax: {
                    cache: true,
                    url: path_url + 'api/animais/',
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            pesquisa: params.term,
                            empresa_id: $('#empresa_id').val(),
                        };
                        return query;
                    },
                    processResults: function (response) {
                        var results = [];
                        console.log(response);
                        $.each(response.data, function (i, v) {
                            var o = {};
                            o.id = v.id;
                            o.text =
                                v.nome +
                                ' -  Tutor: ' + v.cliente.razao_social;
                            o.value = v.id;
                            o.cliente_id = v.cliente_id;

                            results.push(o);
                        });
                        return {
                            results: results,
                        };
                    },
                },
            }).on('select2:select', function (e) {
            var data = e.params.data;

            $('input[name="cliente_id"]').val(data.cliente_id);
        });

            const selected_animal = $('input[name="id_animal"]').val();
            const label_animal = $('input[name="animal_info"]').val();
            if (selected_animal && label_animal) {
                const option = new Option(label_animal, selected_animal, true, true);
                $('select[name="animal_id"]').append(option).trigger('change');
            }
        });
    }
    getAnimaisForSelect2();

    /**
     * Pega os colaboradores e configura o select2 para o campo de colaborador
    */
    function getFuncionariosForCrecheSelect2() {
        const parent_modal = $('#modal_novo_agendamento_creche');

        $(parent_modal.length > 0 ? parent_modal : $('body')).find('select[name="colaborador_id"]').select2({
            minimumInputLength: 2,
            language: 'pt-BR',
            placeholder: 'Digite para buscar o colaborador',
            dropdownParent: parent_modal.length > 0 ? parent_modal : null,
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
        })
        const selected_colaborador = $('input[name="id_colaborador"]').val();
        const label_colaborador = $('input[name="nome_colaborador"]').val();

        if (selected_colaborador && label_colaborador) {
            const option = new Option(label_colaborador, selected_colaborador, true, true);
            $('select[name="colaborador_id"]').append(option).trigger('change');
        }
    }
    getFuncionariosForCrecheSelect2();

    function getServicosForCrecheSelect2 () {
        const parent_modal = $('#modal_novo_agendamento_creche');

        $('select[name="servico_ids[]"]').each(function (id, element) {
            if ($(element).data('select2')) {
                $(element).off('select2:select');
                $(element).select2('destroy');
            }

            $(this).select2({
                minimumInputLength: 2,
                language: 'pt-BR',
                placeholder: 'Digite para buscar o serviço',
                width: '100%',
                dropdownParent: parent_modal.length > 0 ? parent_modal : null,
                ajax: {
                    cache: true,
                    url: path_url + 'api/petshop/servicos',
                    dataType: 'json',
                    data: function (params) {
                        const payload = {
                            pesquisa: params.term,
                            empresa_id: $('#empresa_id').val(),
                        };

                        if ($(this).data('is-reserva')) {
                            payload.categoria = 'CRECHE';
                        }

                        if ($(element).closest('table').hasClass('table-creche-servico-frete')) {
                            payload.is_frete = true;
                        }

                        return payload;
                    },
                    processResults: function (response) {
                        if ($(this).data('is-reserva')) {
                            response = response.filter(v => v.categoria?.nome !== 'CRECHE');
                            if (selectedService) {
                                response = response.filter(v => v.id !== selectedService.id);
                            }
                        }

                        setDateValidationForCrecheServicosExtrasSelect2();

                        return {
                            results: response.map((v) => ({
                                id: v.id,
                                text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor),
                                valor: v.valor,
                                categoria: v.categoria,
                                tempo_execucao: v.tempo_execucao,
                            }))
                        };
                    },
                },
            }).on('select2:select', function (e) {
                const data = e.params.data;
                let $row = $(this).closest('tr');
                $row.find('.valor-servico').val('R$ ' + convertFloatToMoeda(data.valor)).trigger('blur');
                $row.find('input[name="servico_categoria[]"]').val(data.categoria.nome);
                $row.find('input[name="tempo_execucao"]').val(data.tempo_execucao);

                if ($(this).data('is-reserva')) {
                    selectedService = data;
                    calculateDataSaida();
                }

                if ($(this).data('is-frete')) {
                    handleAddressModalForCreche();
                }
                
                setTimeout(() => calcTotalCrecheServicos(), 300);
            });
        });

    }
    getServicosForCrecheSelect2();

    /**
     * Manipula o botão de disparo que abre o modal de endereço e limpa os campos
     * caso não haja um frete selecionado
     */
    function handleAddressModalForCreche() {
        const container = $('#modal_novo_agendamento_creche').length > 0 ? $('#modal_novo_agendamento_creche') : $('body');

        const frete_input = container.find('select[name="servico_ids[]"][data-is-frete="true"]').first();
        const handle_modal_btn = container.find('#handle-address-btn');
        const is_new_reserva = handle_modal_btn.closest('#modal_novo_agendamento_creche').length > 0;

        const submmit_button = $('#submit_endereco_cliente');

        const endereco_data = container.find('#endereco_cliente').val() && JSON.parse(container.find('#endereco_cliente').val());

        if (frete_input.length && handle_modal_btn.length && frete_input.val()) {            
            handle_modal_btn.prop('disabled', false);

            handle_modal_btn.off('click').on('click', function () {
                const modal_endereco_cliente = $('#modal_endereco_cliente');
                const modal_novo_agendamento_creche = $('#modal_novo_agendamento_creche');

                if (modal_novo_agendamento_creche.length > 0) {
                    modal_novo_agendamento_creche.modal('hide');

                    modal_endereco_cliente.off('hidden.bs.modal').on('hidden.bs.modal', function () {
                        reopen_modal_novo_agendamento_creche = true;
                        modal_novo_agendamento_creche.modal('show');
                    });
                }

                if (endereco_data) {
                    $('#modal_endereco_cliente').find('input[name="cep"]').val(endereco_data.cep);
                    $('#modal_endereco_cliente').find('input[name="rua"]').val(endereco_data.rua);
                    $('#modal_endereco_cliente').find('input[name="bairro"]').val(endereco_data.bairro);
                    $('#modal_endereco_cliente').find('input[name="numero"]').val(endereco_data.numero);
                    $('#modal_endereco_cliente').find('input[name="complemento"]').val(endereco_data.complemento);

                    const new_option = new Option(endereco_data.cidade.nome, endereco_data.cidade_id, false, false);
                    $('#modal_endereco_cliente').find('select[name="modal_cidade_id"]').append(new_option).trigger('change');
                }

                if (window.location.href.includes('creche/creches') || is_new_reserva) {
                    submmit_button.addClass('d-none');
                }

                modal_endereco_cliente.modal('show');
            });

            return;
        }

        $('#endereco_cliente').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="cep"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="cep"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="rua"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="rua"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="bairro"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="bairro"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="numero"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="numero"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="complemento"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="complemento"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('select[name="modal_cidade_id"]').val(null).trigger('change');

        handle_modal_btn.prop('disabled', true);
    }
    handleAddressModalForCreche()

    /**
     * Preenche os campos de endereço do modal 
     * com base no endereço original do cliente caso o usuário deseje
     */
    function getClienteAddressForModalFieldsForCreche() {
        const cliente_input = $('input[name="cliente_id"]');

        if (cliente_input.lenght <= 0 || !cliente_input.val()) return;

        const address_fields = $('#modal_endereco_cliente').find('input, select');

        let is_filled = false;

        address_fields.each(function () {
            if ($(this).val()) {
                is_filled = true;
            }
        });

        if (is_filled) return;

        let address = null;

        $.ajax({
            url: path_url + 'api/clientes/find/' + cliente_input.val(),
            dataType: 'json',
            method: 'GET',
            async: false,
            success: function (response) {
                address = response;
            }
        });

        if (address) {
            setTimeout(() => {
                Swal.fire({
                    title: 'Deseja utilizar o endereço do cliente?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim',
                    cancelButtonText: `Não`,
                    icon: 'question',
                    revertButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('input[name="cep"]').val(address.cep).trigger('blur');
                        $('input[name="rua"]').val(address.rua).trigger('blur');
                        $('input[name="numero"]').val(address.numero).trigger('blur');
                        $('input[name="bairro"]').val(address.bairro).trigger('blur');
                        $('input[name="complemento"]').val(address.complemento).trigger('blur');

                        const cidade_option = new Option(address.cidade.nome, address.cidade_id, true, true);
                        $('select[name="modal_cidade_id"]').append(cidade_option).trigger('change');
                    }    
                })
            }, 600);
        }
    }


    $('#modal_endereco_cliente').off('show.bs.modal').on('show.bs.modal', function (e) {
        getClienteAddressForModalFieldsForCreche();
    });

    /**
     * Prepara os inputs de data e hora para serem validados 
     * quando um serviço extra for alterado ou adicionado
    */
    function setDateValidationForCrecheServicosExtrasSelect2 () {
        $('.table-creche-servicos select[name="servico_ids[]"]').each(function () {
            const input = $(this);

            input.on('select2:select', function () {
                setTimeout(() => {
                    validateCrecheServicoExtraRangeDate($(this));
                }, 100);
            });
        })
        $('.table-creche-servicos input[name="servico_datas[]"]').each(function () {
            const input = $(this);

            input.on('blur', function () {
                validateCrecheServicoExtraRangeDate($(this));
            });
        })
        $('.table-creche-servicos input[name="servico_horas[]"]').each(function () {
            const input = $(this);

            input.on('blur', function () {
                validateCrecheServicoExtraRangeDate($(this));
            });
        })
    }
    setDateValidationForCrecheServicosExtrasSelect2();

    $('.btn-add-tr').off('click').on('click', function () {
        let table = $(this).closest('table.table-dynamic');
        if (!table.length) {
            table = $(this).closest('.row').siblings('table.table-dynamic');
        }
        let tr = table.find('.dynamic-form').first();
        if (tr.length === 0) return;

        let is_empty = false;

        table.find('input').each(function () {
            if (
                ($(this).val() == '' || $(this).val() == null) &&
                $(this).attr('type') != 'hidden' &&
                $(this).attr('type') != 'file' &&
                !$(this).hasClass('ignore')
            ) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Preencha todos os campos antes de adicionar novos.'
                })

                is_empty = true;
            }
        });

        if (is_empty) {
            return;
        }

        let clone = tr.clone();
        clone.find('input').val('');
        clone.find('select').val('');
        clone.find('.select2-container').remove();
        clone.find('select.servico_id, select.produto_id')
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id')
            .removeAttr('aria-hidden')
            .removeAttr('tabindex');
        clone.find('input[type="date"], input[type="time"]').prop('disabled', false);
        clone.find('.hotel-btn-remove-tr').prop('disabled', false);
        table.append(clone);
        clone.find('input').removeClass('is-valid');
        clone.find('select').removeClass('is-valid');
        clone.find('input[type="date"], input[type="time"]').removeClass('is-valid');

        if ($(this).data('content') == 'servicos') {
            getServicosForCrecheSelect2();
        }

        if ($(this).data('content') == 'produtos') {
            getProdutosForCrecheSelect2();
        }
    });

    $(document).delegate('.creche-btn-remove-tr', 'click', function (e) { 
        const current_btn = $(this);

        e.preventDefault();
        const $row = current_btn.closest('tr');
        const index = $('.dynamic-form').index($row);
        const removedFirst = index === 0;

        Swal.fire({
            icon: 'warning',
            title: 'Deseja realmente remover esse item?',
            text: 'Essa ação não poderá ser desfeita',
            showCancelButton: true,
            confirmButtonText: 'Remover',
            cancelButtonText: 'Cancelar',
        }).then((willDelete) => {
            if (willDelete.isConfirmed) {
                if (
                    (   
                        current_btn.closest('table.table-creche-servicos').length > 0 &&
                        current_btn.closest('table').find('.servico_id').length > 1
                    ) ||
                    (
                        current_btn.closest('table.table-creche-produtos').length > 0 &&
                        current_btn.closest('table').find('.produto_id').length > 1
                    )
                ) {
                    $row.remove();
                } else {
                    $row.find('input').val(null);
                    $row.find('select').val(null).trigger('change');
                    $row.find('input').removeClass('is-valid');
                    $row.find('select').removeClass('is-valid');
                    $row.find('input').removeClass('is-invalid');
                    $row.find('select').removeClass('is-invalid');
                    $row.find('input').tooltip('dispose');
                    $row.find('select').tooltip('dispose');
                    
                    if (removedFirst) {
                        selectedService = null;
                    }

                    if (removedFirst) {
                        $('select.servico_id').first().val(null).trigger('change');
                    }

                    if ($row.find('select').data('is-frete')) {
                        handleAddressModalForCreche();
                    }
                }

                getServicosForCrecheSelect2();

                calcTotalCrecheServicos();
                calcTotalCrecheProdutos();
            }
        })
    });

    function calcTotalCrecheServicos () {
        let total = 0;

        $('.table-creche-servicos input[name="servico_valor[]"]').each(function () {
            const value = convertMoedaToFloat($(this).val());

            if (value > 0) {
                total += value;
            }
        });

        $('.table-creche-servicos .total-servicos').html('R$ ' + convertFloatToMoeda(total));
    }
    calcTotalCrecheServicos();

    function getProdutosForCrecheSelect2 () {
        const parent_modal = $('#modal_novo_agendamento_creche');

        $('select.produto_id').each((id, element) => {
            $(element).select2({
                minimumInputLength: 2,
                language: 'pt-BR',
                placeholder: 'Digite para buscar o produto',
                width: '100%',
                dropdownParent: parent_modal.length ? parent_modal : null,
                ajax: {
                    cache: true,
                    url: path_url + 'api/produtos',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            pesquisa: params.term,
                            empresa_id: $('#empresa_id').val(),
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response.map((v) => ({
                                id: v.id,
                                text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor_unitario),
                                valor: v.valor_unitario,
                            }))
                        };
                    },
                },
            }).on('select2:select', function (e) {
                const data = e.params.data;
                let $row = $(this).closest('tr');
                $row.find('.qtd-produto').val('1');
                $row.find('.valor_unitario-produto').val('R$ ' + convertFloatToMoeda(data.valor));
                $row.find('.subtotal-produto').val('R$ ' + convertFloatToMoeda(data.valor));

                calcTotalCrecheProdutos();
            });
        });
    }
    getProdutosForCrecheSelect2()

    function calcTotalCrecheProdutos () {
        let total = 0;

        $('input[name="subtotal_produto[]"]').each(function () {
            const value = convertMoedaToFloat($(this).val());

            total += value;
        });

        $('.total-produtos').html('R$ ' + convertFloatToMoeda(total));
    }
    calcTotalCrecheProdutos();

    $(document).on('blur', '.qtd-produto', function () {
        const qtd = parseFloat($(this).val().replace(',', '.')) || 0;
        const $row = $(this).closest('tr');
        const valor = convertMoedaToFloat($row.find('.valor_unitario-produto').val());
        $row.find('.subtotal-produto').val('R$ ' + convertFloatToMoeda(qtd * valor));
        calcTotalCrecheProdutos();
    });

    function handleTurmaInput () {
        const is_index_view = window.location.pathname === '/creche/creches';

        
        if (is_index_view) return;

        const turma_input = $('select[name="turma_id"]');

        const data_entrada = $('input[name="data_entrada"]');
        const horario_entrada_input = $('input[name="horario_entrada"]');
        const data_saida = $('input[name="data_saida"]');
        const horario_saida_input = $('input[name="horario_saida"]');

        if (!data_entrada.val() || !horario_entrada_input.val() || !data_saida.val() || !horario_saida_input.val()) {
            turma_input.prop('disabled', true);
            initializeTooltip(turma_input, 'Determine o período da reserva primeiro.');

            return;
        }

        const data_entrada_time = `${data_entrada.val()} ${horario_entrada_input.val()}`;
        const data_saida_time = `${data_saida.val()} ${horario_saida_input.val()}`;

        turma_input.select2({
            placeholder: 'Selecione um turma',
            width: '100%',
            ajax: {
                url: path_url + 'api/turmas/',
                dataType: 'json',
                data: function (params) {
                    return {
                        pesquisa: params.term,
                        empresa_id: $('#empresa_id').val(),
                        data_entrada: data_entrada_time,
                        data_saida: data_saida_time,
                    };
                },
                processResults: function (response) {
                    return {
                        results: response.data.map(function (turma) {
                            return {
                                id: turma.id,
                                text: turma.nome,
                            };
                        })
                    };
                }
            }

        }).on('select2:select', function (e) {
            const turma = e.params.data;
            const capacidade_turma_input = $('input[name="capacidade_turma"]');

            capacidade_turma_input.val(turma.capacidade);
        });

        turma_input.prop('disabled', false);

        const selected_turma = $('input[name="id_turma"]').val();
        const label_turma = $('input[name="nome_turma"]').val();

        if (selected_turma && label_turma) {
            const option = new Option(label_turma, selected_turma, true, true);
            $('select[name="turma_id"]').append(option).trigger('change');
        }

    }
    handleTurmaInput();

    // Fim das configurações dos campos select2
    // ----------

    // -------
    // Funções auxiliares


    // Fim das funções auxiliares
    // -------

    // -------
    // Validação e manipulação de datas

    /**
     * Valida as datas dos campos de data de entrada e de saída para
     * confirmar se elas estão respeitando a ordem do dia e hora de cada uma
     * 
     * @param {JQuery} trigger Input que disparou a função 
     * @returns 
     */
    function validateCrecheRangeDate (trigger) {
        const data_entrada = $('input[name="data_entrada"]');
        const horario_entrada_input = $('input[name="horario_entrada"]');
        const data_saida = $('input[name="data_saida"]');
        const horario_saida_input = $('input[name="horario_saida"]');

        const data_entrada_iso = `${data_entrada.val()}T00:00`;
        const data_saida_iso = `${data_saida.val()}T00:00`;

        let data_entrada_time = new Date(data_entrada_iso);
        let data_saida_time = new Date(data_saida_iso);
        const horario_entrada_time = convertHoursAndMinutesToInt(horario_entrada_input.val());
        const horario_saida_time = convertHoursAndMinutesToInt(horario_saida_input.val());

        if (data_entrada.val() && data_saida.val()) {
            if (data_entrada_time > data_saida_time) {
                new swal('Data inválida!', 'A data de entrada deve ser menor ou igual a data de saída.', 'warning');

                trigger ? trigger.val(trigger.data('old')) : data_entrada.val(data_entrada.data('old'));
                initializeTooltip(trigger ?? data_entrada, 'Data inválida.');
                trigger ? trigger.addClass('is-invalid') : data_entrada.addClass('is-invalid');

                return false;
            }
        }

        if (data_entrada.val() && horario_entrada_input.val() && data_saida.val() && horario_saida_input.val()) {
            if (data_entrada_time.getTime() === data_saida_time.getTime() && horario_entrada_time >= horario_saida_time) {
                new swal('Horário inválido!', 'O horário de entrada deve ser menor do que o horário de saída.', 'warning');
                trigger.val() ? trigger.val(trigger.data('old')) : horario_entrada_input.val(horario_entrada_input.data('old'));
                initializeTooltip(trigger ?? horario_entrada_input, 'Horário inválido.');
                trigger.addClass('is-invalid') ? trigger.addClass('is-invalid') : horario_entrada_input.addClass('is-invalid');

                return false;
            }
    }
    }

    /**
     * Manipula os campos de reserva de serviço e de data de saída conforme os campos
     * de data e horário de entrada foram preenchidos
    */
    function handleServicoReservaAndDataSaidaFields () {
        const data_entrada = $('input[name="data_entrada"]');
        const data_entrada_time_input = $('input[name="horario_entrada"]');
        const data_saida = $('input[name="data_saida"]');
        const horario_saida_input = $('input[name="horario_saida"]');

        const servico_reserva_input_line = $('.table-creche-servico-reserva tbody .dynamic-form');
        const servico_input = servico_reserva_input_line.find('select[name="servico_ids[]"]').first();
        const valor_servico_input = servico_reserva_input_line.find('input[name="servico_valor[]"]').first();

        if (
            (!data_entrada.val() || !data_entrada_time_input.val()) &&
            !servico_input.val()
        ) {
            valor_servico_input.prop('disabled', true);

            initializeTooltip(servico_input.next('.select2'), 'Determine o período de entrada da reserva primeiro.');
            initializeTooltip(valor_servico_input, 'Determine o período de entrada da reserva primeiro.');

            return;
        }

        if (data_entrada.val() && data_entrada_time_input.val() && !servico_input.val()) {
            servico_input.prop('disabled', false);
            valor_servico_input.prop('disabled', false);

            servico_input.data('select2')?.$container.tooltip('dispose');
            valor_servico_input.tooltip('dispose');

            return;
        }        
    }
    handleServicoReservaAndDataSaidaFields();


    /**
     * Libera os campos de saída para serem preenchidos e estabelece um limite de data para eles
     * 
     * @param {*} data_saidaDate 
     */
    function updateCrecheMainServiceDateTime (data_saidaDate = '') {
        const $dateInput = $('input[name="servico_datas[]"]').first();

        if ($dateInput.length) {
            if (data_saidaDate) {
                $('input[name="data_saida"]').prop('disabled', false);
                $('input[name="horario_saida"]').prop('disabled', false);

                $('input[name="data_saida"]').tooltip('dispose');
                $('input[name="horario_saida"]').tooltip('dispose');

                $dateInput.attr('max', data_saidaDate);
            } else {
                $dateInput.removeAttr('max');
            }
        }
    }

    /**
     * Faz o cálculo do período de saída com base no período de entrada e no tempo de execução 
     * do serviço determinado pelo usuário
     */
    function calculateDataSaida () {
        const data_entradaDate = $('input[name="data_entrada"]').val();
        const data_entradaTime = $('input[name="horario_entrada"]').val();

        const $data_saida = $('input[name="data_saida"]');
        const $timedata_saida = $('input[name="horario_saida"]');

        if (!$data_saida.val() && !$timedata_saida.val()) {
            $data_saida.attr('disabled', true);
            $timedata_saida.attr('disabled', true);

            initializeTooltip($data_saida, 'Determine o serviço de reserva primeiro.');
            initializeTooltip($timedata_saida, 'Determine o serviço de reserva primeiro.');
        } else {
            $data_saida.attr('disabled', false);
            $timedata_saida.attr('disabled', false);
        }      

        updateCrecheMainServiceDateTime();

        if (!selectedService) return;

        const data_entradaDT = new Date(`${data_entradaDate}T${data_entradaTime}`);

        const dt = new Date(data_entradaDT.getTime());
        const tempo = parseInt(selectedService.tempo_execucao, 10);

        dt.setMinutes(dt.getMinutes() + tempo);

        const year   = dt.getFullYear();
        const month  = String(dt.getMonth() + 1).padStart(2, '0');
        const day    = String(dt.getDate()).padStart(2, '0');
        const hour   = String(dt.getHours()).padStart(2, '0');
        const minute = String(dt.getMinutes()).padStart(2, '0');

        $data_saida.val(`${year}-${month}-${day}`).trigger('blur');
        $timedata_saida.val(`${hour}:${minute}`).trigger('blur');
        $data_saida.trigger('focus');
        $timedata_saida.trigger('focus');

        updateCrecheMainServiceDateTime(`${year}-${month}-${day}`);
        handleTurmaInput();
    }
    calculateDataSaida();

    /**
     * Valida o periodo da reserva e o tempo de execução do serviço de reserva
     * e retorna se ele consegue ser executado no periodo escolhido
     * 
     * @param {JQuery} trigger campo que disparou a validação
     * 
     * @returns {boolean} resposta da validação
    */
    function validateCrecheServicoReservaRangeDate (trigger) {
        const data_entrada = $('input[name="data_entrada"]');
        const horario_entrada_input = $('input[name="horario_entrada"]');
        const data_saida = $('input[name="data_saida"]');
        const horario_saida_input = $('input[name="horario_saida"]');

        if (!data_entrada.val() || !horario_entrada_input.val() || !data_saida.val() || !horario_saida_input.val()) return;

        const servico_reserva_input_line = $('.table-creche-servico-reserva tbody .dynamic-form');
        const servico_input = servico_reserva_input_line.find('select[name="servico_ids[]"]');

        if (!servico_input.val()) return;

        const tempo_execucao_servico = parseInt(servico_reserva_input_line.find('input[name="tempo_execucao"]').val());

        const date_data_entrada_iso = `${data_entrada.val()}T${horario_entrada_input.val()}`;
        const data_data_saida_iso = `${data_saida.val()}T${horario_saida_input.val()}`;

        let date_data_entrada_time = new Date(date_data_entrada_iso);
        let data_data_saida_time = new Date(data_data_saida_iso);

        let diff_ms = data_data_saida_time - date_data_entrada_time;
        const diff_time = Math.floor(diff_ms / 1000 / 60);

        if (tempo_execucao_servico <= diff_time) {
            return true;
        }

            
        Swal.fire({
            title: 'Permanência da reserva inválida',
            html: `
                O tempo de execução do serviço: 
                <b>
                    ${servico_input.find('option:selected').text().split(' R$')[0]}
                </b>
                excede esse período de permanência da reserva.
            `,
            icon: 'warning',
        });

        initializeTooltip(trigger, 'O tempo de execução do serviço de reserva deve ser maior ou igual ao tempo de permanência da reserva.');
        trigger.addClass('is-invalid');
        trigger.val(trigger.data('old') ?? null);

        return false;
    }

    /**
     * Verifica se a data de entrada e o tempo de execução do serviço
     * respeita o tempo de permanência da reserva
     * 
     * Ex: reserva de 3 dias, e o serviço tem 2 dias, 
     * então o serviço deve ser executado nos 1° dia, caso contrário ele será barrado
     * 
     * @param {JQuery} element Linha de campos do serviço extra
     * @returns 
     */
    function validateCrecheServicoExtraRangeDate (element) {
        const servico_input = element.closest('tr').find('select[name="servico_ids[]"]');
        const tempo_execucao_input = $('.table-creche-servicos input[name="tempo_execucao"]');
        const data_servico_input = element.closest('tr').find('input[name="servico_datas[]"]');
        const hora_servico_input = element.closest('tr').find('input[name="servico_horas[]"]');
        const valor_servico_input = element.closest('tr').find('input[name="servico_valor[]"]');

        if (!servico_input.val() || !tempo_execucao_input.val()) return false;

        const date_data_entrada = $('input[name="data_entrada"]');
        const date_data_entrada_time_input = $('input[name="horario_entrada"]');
        const date_data_saida = $('input[name="data_saida"]');
        const data_data_saida_time_input = $('input[name="horario_saida"]');

        const date_data_entrada_iso = `${date_data_entrada.val()}T${date_data_entrada_time_input.val()}`;
        const data_data_saida_iso = `${date_data_saida.val()}T${data_data_saida_time_input.val()}`;

        let date_data_entrada_time = new Date(date_data_entrada_iso);
        let data_data_saida_time = new Date(data_data_saida_iso);

        let diff_ms = data_data_saida_time - date_data_entrada_time;
        const diff_time = Math.floor(diff_ms / 1000 / 60);

        if (diff_time < parseInt(tempo_execucao_input.val())) {
            Swal.fire({
                title: 'Tempo de execução inválido.',
                html: `
                    O tempo de execução do serviço extra:
                        <b>
                            ${servico_input.find('option:selected').text().split(' R$')[0]}
                        </b>  
                    excede o tempo de permanência da reserva.
                `,
                icon: 'warning'
            })

            servico_input.val(null).trigger('change');
            valor_servico_input.val(null).trigger('change');

            return false;
        }

        if (!data_servico_input.val()) {
            initializeTooltip(data_servico_input, 'Determine a data de início primeiro.');
            data_servico_input.addClass('is-invalid');

            return false;
        };

        if (new Date(data_servico_input.val()).getTime() < new Date(date_data_entrada.val()).getTime()) {
            Swal.fire({
                title: 'Data de início inválida.',
                html: `
                    A data de início do serviço extra:
                        <b>
                            ${servico_input.find('option:selected').text().split(' R$')[0]}
                        </b>  
                    deve ser maior ou igual a data de entrada da reserva.
                `,
                icon: 'warning'
            })

            initializeTooltip(data_servico_input, 'Data de início menor do que a data de entrada.');
            data_servico_input.addClass('is-invalid');
            data_servico_input.val(data_servico_input.data('old'));

            return false;
        }

        if (data_servico_input.val() == date_data_entrada.val() && hora_servico_input.val()) {
            if (hora_servico_input.val() < date_data_entrada_time_input.val()) {
                Swal.fire({
                    title: 'Horário de início inválido.',
                    html: `
                        O horário de início do serviço extra:
                            <b>
                                ${servico_input.find('option:selected').text().split(' R$')[0]}
                            </b>  
                        deve ser maior ou igual ao horário de entrada da reserva.
                    `,
                    icon: 'warning'
                })

                initializeTooltip(data_servico_input, 'Horário de início menor do que o horário de entrada.');
                hora_servico_input.addClass('is-invalid');
                hora_servico_input.val(hora_servico_input.data('old'));

                return false;
            }
        }

        if (new Date(data_servico_input.val()).getTime() > new Date(date_data_saida.val()).getTime()) {
            Swal.fire({
                title: 'Data de início inválida.',
                html: `
                    A data de início do serviço extra:
                        <b>
                            ${servico_input.find('option:selected').text().split(' R$')[0]}
                        </b>  
                    deve ser menor ou igual a data de saída da reserva.
                `,
                icon: 'warning'
            })

            initializeTooltip(data_servico_input, 'Data de início maior do que a data de saída.');
            data_servico_input.addClass('is-invalid');
            data_servico_input.val(data_servico_input.data('old'));

            return false;
        }

        if (!hora_servico_input.val()) {
            initializeTooltip(hora_servico_input, 'Determine o horário de início primeiro.');
            hora_servico_input.addClass('is-invalid');

            return false;
        };

        const data_servico_iso = `${data_servico_input.val()}T${hora_servico_input.val()}`;
        let data_servico_time = new Date(data_servico_iso);

        let data_servico_data_saida_diff_ms = data_data_saida_time - data_servico_time;
        const data_servico_data_saida_diff_time = Math.floor(data_servico_data_saida_diff_ms / 1000 / 60);

        if (data_servico_data_saida_diff_time < parseInt(tempo_execucao_input.val())) {
            Swal.fire({
                title: 'Data de início inválida.',
                html: `
                    O tempo de execução do serviço extra:
                        <b>
                            ${servico_input.find('option:selected').text().split(' R$')[0]}
                        </b>  
                    excede o tempo de permanência da reserva.
                `,
                icon: 'warning'
            })

            if (data_servico_data_saida_diff_time - parseInt(tempo_execucao_input.val()) <= -1440) {
                data_servico_input.val(data_servico_input.data('old'));
                initializeTooltip(data_servico_input, 'A data de início excede o tempo de permanência da reserva.');
                data_servico_input.addClass('is-invalid');
            } else {
                hora_servico_input.val(hora_servico_input.data('old'));
                initializeTooltip(hora_servico_input, 'O horário de início excede o tempo de permanência da reserva.');
                hora_servico_input.addClass('is-invalid');
            }

            return false;
        } else {
            data_servico_input.removeClass('is-invalid');
            hora_servico_input.removeClass('is-invalid');
        }

        return true;
    }

    /**
     * Manipula os campos de data e hora do serviço extra
     * conforme os campos de período de entrada e período de saída foram preenchidos
     * 
     * @returns 
    */
    function handleCrecheServicosExtraFields () {
        const servico_extra_input_line = $('.table-creche-servicos tbody .dynamic-form');

        const servico_frete_input_line = $('.table-creche-servico-frete tbody .dynamic-form');
        const servico_frete_input = servico_frete_input_line.find('select[name="servico_ids[]"]').first();
        const servico_frete_valor_input = servico_frete_input_line.find('input[name="servico_valor[]"]').first();

        const data_entrada = $('input[name="data_entrada"]');
        const data_entrada_time_input = $('input[name="horario_entrada"]');
        const data_saida = $('input[name="data_saida"]');
        const data_saida_time_input = $('input[name="horario_saida"]');

        servico_extra_input_line.each(function () {
            const current_line = $(this);

            const servico_input = current_line.find('select[name="servico_ids[]"]').first();

            if (servico_input.data('is-reserva')) {
                return;
            }

            const data_servico_input = current_line.find('input[name="servico_datas[]"]');
            const hora_servico_input = current_line.find('input[name="servico_horas[]"]');
            const valor_servico_input = current_line.find('input[name="servico_valor[]"]');

            if (
                data_entrada.val() && data_entrada_time_input.val() &&
                data_saida.val() && data_saida_time_input.val()
            ) {
                servico_input.prop('disabled', false);
                data_servico_input.prop('disabled', false);
                hora_servico_input.prop('disabled', false);
                valor_servico_input.prop('disabled', false);
                servico_frete_input.prop('disabled', false);
                servico_frete_valor_input.prop('disabled', false);

                servico_input.next('.select2').tooltip('dispose');
                servico_frete_input.next('.select2').tooltip('dispose');
                data_servico_input.tooltip('dispose');
                hora_servico_input.tooltip('dispose');
                valor_servico_input.tooltip('dispose');
                servico_frete_valor_input.tooltip('dispose');

                return;
            }

            if (
                (
                    data_entrada.val() && data_entrada_time_input.val() &&
                    data_saida.val() && data_saida_time_input.val()
                ) &&
                (
                    data_saida_time_input.attr('disabled')
                )
            ) {

                disableWithHidden(servico_input);
                disableWithHidden(data_servico_input);
                disableWithHidden(hora_servico_input);
                disableWithHidden(valor_servico_input);

                return;
            }

            if (!data_entrada.val() || !data_entrada_time_input || !data_saida || !data_saida_time_input) {
                disableWithHidden(servico_input);
                disableWithHidden(data_servico_input);
                disableWithHidden(hora_servico_input);
                disableWithHidden(valor_servico_input);

                initializeTooltip(servico_input.next('.select2'), 'Determine o período de entrada e o período de saída da reserva primeiro.');
                initializeTooltip(servico_frete_input.next('.select2'), 'Determine o período de entrada e o período de saída da reserva primeiro.');
                initializeTooltip(data_servico_input, 'Determine o período de entrada e o período de saída da reserva primeiro.');
                initializeTooltip(hora_servico_input, 'Determine o período de entrada e o período de saída da reserva primeiro.');
                initializeTooltip(valor_servico_input, 'Determine o período de entrada e o período de saída da reserva primeiro.');
                initializeTooltip(servico_frete_valor_input, 'Determine o período de entrada e o período de saída da reserva primeiro.');

                return;
            }
        })

    }
    handleCrecheServicosExtraFields();

    /** 
     * Valida se os serviços extras estão respeitando o tempo de
     * permanência da reserva mesmo quando ele é mudado pelo usuário
     * 
     * @param {JQuery} trigger Input que disparou a função
    */
    function validateCrecheServicosExtrasDatesWhenRangeDateChange (trigger) {
        const date_data_entrada = $('input[name="data_entrada"]');
        const date_data_entrada_time_input = $('input[name="horario_entrada"]');
        const date_data_saida = $('input[name="data_saida"]');
        const data_data_saida_time_input = $('input[name="horario_saida"]');

        const date_data_entrada_iso = `${date_data_entrada.val()}T${date_data_entrada_time_input.val()}`;
        const data_data_saida_iso = `${date_data_saida.val()}T${data_data_saida_time_input.val()}`;

        let date_data_entrada_time = new Date(date_data_entrada_iso);
        let data_data_saida_time = new Date(data_data_saida_iso);

        let diff_ms = data_data_saida_time - date_data_entrada_time;
        const diff_time = Math.floor(diff_ms / 1000 / 60);

        const tempo_execucao_fields = $('input[name="tempo_execucao"]');
        let total_tempo_execucao = 0;

        tempo_execucao_fields.each(function () {
            total_tempo_execucao += parseInt($(this).val());
        })

        let inputs_list = [];
        $('.table-creche-servicos input[name="tempo_execucao"]').each(function () {
            const tempo_execucao_input = $(this);
            const servico_input = tempo_execucao_input.closest('tr').find('select[name="servico_ids[]"]');
            const data_servico_input = tempo_execucao_input.closest('tr').find('input[name="servico_datas[]"]');
            const hora_servico_input = tempo_execucao_input.closest('tr').find('input[name="servico_horas[]"]');

            if (total_tempo_execucao > diff_time) {
                inputs_list.push(servico_input.find('option:selected').text().split(' R$')[0]);
        
                return;
            }

            if (diff_time < parseInt(tempo_execucao_input.val())) {
                if (servico_input.length > 1) {
                    inputs_list.push(servico_input.find('option:selected').text().split(' R$')[0]);
                }

                if (!data_servico_input.val() && !hora_servico_input.val()) {
                    trigger.val(trigger.data('old'));

                    return;
                }

                if (diff_time - parseInt(tempo_execucao_input.val()) <= -1440 && data_servico_input.val()) {
                    initializeTooltip(data_servico_input, 'A data de entrada excede o tempo de permanência da reserva.');
                    data_servico_input.addClass('is-invalid');

                    return;
                } else {
                    initializeTooltip(hora_servico_input, 'O horário de entrada excede o tempo de permanência da reserva.');
                    hora_servico_input.addClass('is-invalid');

                    return;
                }
            } else {
                data_servico_input.removeClass('is-invalid');
                hora_servico_input.removeClass('is-invalid');
                data_servico_input.tooltip('dispose');
                hora_servico_input.tooltip('dispose');
            }
        })

        if (inputs_list.length > 0) {
            let plural = inputs_list.length > 1 ? 's' : '';

            Swal.fire({
                title: 'Data de entrada inválida.',
                html: `
                    O${plural} seguinte${plural} serviço${plural} extra${plural}:
                        <b>
                            ${inputs_list.join(', ')}
                        </b>  
                    excede${inputs_list.length > 1 ? 'm' : ''} o tempo de permanência da reserva.
                `,
                icon: 'warning'
            })

            return false;
        }

        return true;
    }

    /**
     * Manipula o campo de turma para decidir se ele será desabilitado 
     * ou não conforme a data de check in e check out
     * 
     * @returns 
     */
    function handleTurmaInput () {
        const is_index_view = window.location.pathname === '/creche/creches';

        if (is_index_view) return;

        const turma_input = $('select[name="turma_id"]');

        const data_entrada_input = $('input[name="data_entrada"]');
        const horario_entrada_input = $('input[name="horario_entrada"]');
        const data_saida_input = $('input[name="data_saida"]');
        const horario_saida_input = $('input[name="horario_saida"]');

        if (!data_entrada_input.val() || !horario_entrada_input.val() || !data_saida_input.val() || !horario_saida_input.val()) {
            turma_input.prop('disabled', true);
            initializeTooltip(turma_input, 'Determine o período da reserva primeiro.');

            return;
        }

        const data_entrada_time = `${data_entrada_input.val()} ${horario_entrada_input.val()}`;
        const data_saida_time = `${data_saida_input.val()} ${horario_saida_input.val()}`;

        turma_input.select2({
            placeholder: 'Selecione uma turma',
            width: '100%',
            ajax: {
                url: path_url + 'api/turmas/',
                dataType: 'json',
                data: function (params) {
                    return {
                        pesquisa: params.term,
                        empresa_id: $('#empresa_id').val(),
                        data_entrada: data_entrada_time,
                        data_saida: data_saida_time,
                    };
                },
                processResults: function (response) {
                    return {
                        results: response.data.map(function (turma) {
                            return {
                                id: turma.id,
                                text: turma.nome,
                            };
                        })
                    };
                }
            }

        }).on('select2:select', function (e) {
            const turma = e.params.data;
            const capacidade_turma_input = $('input[name="capacidade_turma"]');

            capacidade_turma_input.val(turma.capacidade);
        });

        turma_input.prop('disabled', false);

        const selected_turma = $('input[name="id_turma"]').val();
        const label_turma = $('input[name="nome_turma"]').val();

        if (selected_turma && label_turma) {
            const option = new Option(label_turma, selected_turma, true, true);
            $('select[name="turma_id"]').append(option).trigger('change');
        }

    }
    handleTurmaInput();

    /**
     * Verifica se o turma selecionado para a reserva está livre
     * no periódo selecionado
     * 
     * @returns 
     */
    function validateTurmaIsFree () {
        const turma_id_input = $('select[name="turma_id"]');

        const data_entrada_input = $('input[name="data_entrada"]');
        const horario_entrada_input = $('input[name="horario_entrada"]');
        const data_saida_input = $('input[name="data_saida"]');
        const horario_saida_input = $('input[name="horario_saida"]');

        if (
            !turma_id_input.val() ||
            !data_entrada_input.val() ||
            !horario_entrada_input.val() ||
            !data_saida_input.val() ||
            !horario_saida_input.val()
        ) return;
        
        const data_entrada_time = `${data_entrada_input.val()} ${horario_entrada_input.val()}`;
        const data_saida_time = `${data_saida_input.val()} ${horario_saida_input.val()}`;

        let is_free = true;

        let reserva_id = window.location.pathname.split('/edit')[0].split('/').pop();

        reserva_id = (Number.isInteger(parseInt(reserva_id))) ? parseInt(reserva_id) : null;

        reserva_id = $('#modal_novo_agendamento_creche').length ? $('#modal_novo_agendamento_creche').data('agendamento-id') : reserva_id;

        $.ajax({
            url: path_url + 'api/turmas/check-turma-free',
            method: 'GET',
            data: {
                turma_id: turma_id_input.val(),
                empresa_id: $('#empresa_id').val(),
                data_entrada: data_entrada_time,
                data_saida: data_saida_time,
                reserva_id
            },
            async: false,
            success: function (response) {
                if (!response.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Turma indisponível',
                        html: `
                            A turma
                            <b>${turma_id_input.find('option:selected').text()}</b>
                            está ocupado no período escolhido. <br>

                            <small>Selecione outro turma ou altere o período da reserva.</small>
                        `
                    });

                    initializeTooltip(turma_id_input.next('.select2'), 'Turma indisponível no período escolhido.');
                    turma_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-valid');
                    turma_id_input.next('.select2').find('.select2-selection--single').addClass('select2-invalid');

                    is_free = false;
                } else {
                    turma_id_input.tooltip('dispose');
                    turma_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-invalid');
                    turma_id_input.next('.select2').find('.select2-selection--single').addClass('select2-valid');
                }
            }
        })

        return is_free;
    }

    /**
     * Valida se os campos de serviços extras estão realmente preenchidos
     * quando um mesmo for aplicado
     * 
     * @returns {boolean}
     */
    function validateCrecheExtraServicos () {
        let is_valid = true;

        $('table.table-creche-servicos tbody .dynamic-form select[name="servico_ids[]"]').each(function () {
            const servico_input = $(this);
            if (servico_input.val()) {
                const data_servico = servico_input.closest('tr').find('input[name="servico_datas[]"]');
                const hora_servico = servico_input.closest('tr').find('input[name="servico_horas[]"]');

                if (!data_servico.val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Reserva inválida',
                        html: `
                            Selecione a data na qual o serviço
                            <b>${servico_input.find('option:selected').text().split(' R$')[0]}</b>
                            extra será executado.
                        `
                    });
                    initializeTooltip(data_servico, 'A data é obrigatória.');
                    data_servico.addClass('is-invalid');

                    is_valid = false;

                    return false;
                }

                if (!hora_servico.val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Reserva inválida',
                        html: `
                            Selecione o horário no qual o serviço
                            <b>${servico_input.find('option:selected').text().split(' R$')[0]}</b>
                            será executado.
                        `
                    });

                    initializeTooltip(hora_servico, 'O horário é obrigatório.');
                    hora_servico.addClass('is-invalid');

                    is_valid = false;

                    return false;
                }

                is_valid = validateCrecheServicoExtraRangeDate($(this));
            } 
        })

        return is_valid;
    }

    /**
     * Valida se os campos de produtos foram preenchidos
     * caso um produto tenha sido selecionado
     * 
     * @returns 
     */
    function validateCrecheProdutos () {
        let is_valid = true;

        $('select[name="produto_id[]"]').each(function () {
            const produto_input = $(this);
            const selected_produto = produto_input.find('option:selected').text().split(' R$')[0];

            if (produto_input.val()) {
                const quantidade_produto = produto_input.closest('tr').find('input[name="qtd_produto[]"]');

                if (!quantidade_produto.val() || quantidade_produto.val() < 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Produto inválido',
                        html: `Determine alguma quantidade para o produto
                            <b>${selected_produto}</b>
                        `
                    });

                    quantidade_produto.addClass('is-invalid');
                    initializeTooltip(quantidade_produto, 'Quantidade inválida.');

                    is_valid = false

                    return;
                } else {
                    quantidade_produto.removeClass('is-invalid');
                    quantidade_produto.tooltip('dispose');
                }
            }
        });

        return is_valid;
    }

    /**
     * Valida se os campos de frete foram preenchidos 
     * caso o agendamento contenha um serviço de frete
     * 
     * @returns {boolean}
     */
    function validateCrecheFrete() {
        let frete_input = null;

        if ($('#modal_novo_agendamento_creche').length > 0) {
            frete_input = $('#modal_novo_agendamento_creche').find('select[name="servico_ids[]"][data-is-frete="true"]').first();
        } else {
            frete_input = $('select[name="servico_ids[]"][data-is-frete="true"]').first();
        }

        if (frete_input.length > 0) {
            if (!frete_input.val()) return true;

            const required_address_fields = $('#modal_endereco_cliente').find('input:required, select:required');

            let is_valid = true;

            required_address_fields.each(function () {
                if (!$(this).val()) {
                    is_valid = false;
                    return false;
                }
            });

            if (!is_valid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Endereço do cliente inválido',
                    html: `Selecione um endereço válido para o frete.`
                });
            }

            return is_valid;
        }

        return true;
    }

    // Fim das validações e manipulações de datas
    // -------

    // -------
    // Eventos de disparo das funções

    $(`
        input[name="data_entrada"],
        input[name="horario_entrada"],
        input[name="data_saida"],
        input[name="horario_saida"],
        input[name="servico_datas[]"],
        input[name="servico_horas[]"]
    `).on('focus', function () {
        $(this).data('old', $(this).val());
    });


    $(document).on('blur', 'input[name="data_entrada"]', function () {
        validateCrecheRangeDate($(this));
        handleServicoReservaAndDataSaidaFields();
        setTimeout(() => {
            validateCrecheServicoReservaRangeDate($(this));
        }, 200)
        handleCrecheServicosExtraFields();
        validateCrecheServicosExtrasDatesWhenRangeDateChange($(this));
        handleTurmaInput();
        validateTurmaIsFree();
    });
    $(document).on('blur', 'input[name="horario_entrada"]', function () {
        validateCrecheRangeDate($(this));
        handleServicoReservaAndDataSaidaFields();
        setTimeout(() => {
            validateCrecheServicoReservaRangeDate($(this));
        }, 200)
        handleCrecheServicosExtraFields();
        validateCrecheServicosExtrasDatesWhenRangeDateChange($(this));
        handleTurmaInput();
        validateTurmaIsFree();
    });
    $(document).on('blur', 'input[name="data_saida"]', function () {
        validateCrecheRangeDate($(this));
        setTimeout(() => {
            validateCrecheServicoReservaRangeDate($(this));
        }, 200)
        handleCrecheServicosExtraFields();
        validateCrecheServicosExtrasDatesWhenRangeDateChange($(this));
        handleTurmaInput();
        validateTurmaIsFree();
    });
    $(document).on('blur', 'input[name="horario_saida"]', function () {
        validateCrecheRangeDate($(this));
        handleCrecheServicosExtraFields();
        validateCrecheServicosExtrasDatesWhenRangeDateChange($(this));
        setTimeout(() => {
            validateCrecheServicoReservaRangeDate($(this));
        }, 200)
        handleTurmaInput()
        validateTurmaIsFree();
    });

    $(document).on('select2:close select2:unselect select2:select', 'select[name="turma_id"]', function () {
        validateTurmaIsFree();
    });

    $('#modal_novo_agendamento_creche').on('show.bs.modal', function () {
        if (reopen_modal_novo_agendamento_creche) return;

        getAnimaisForSelect2();
        getServicosForCrecheSelect2();
        getProdutosForCrecheSelect2();
        getFuncionariosForCrecheSelect2();

        calculateDataSaida();    
        calcTotalCrecheServicos();
        calcTotalCrecheProdutos();
        handleTurmaInput();
        handleServicoReservaAndDataSaidaFields();
        handleCrecheServicosExtraFields();
        setDateValidationForCrecheServicosExtrasSelect2();
    });

    $(`
        #modal_novo_agendamento_creche .btn-close,
        #modal_novo_agendamento_creche .btn-close-modal
    `).on('click', function () {
        $('#modal_novo_agendamento_creche').find('input, select').val(null).trigger('change');
        $('#modal_novo_agendamento_creche').find('input, select').removeClass('is-valid');
        $('#modal_novo_agendamento_creche').find('input, select').removeClass('is-invalid');

        $('#modal_novo_agendamento_creche').find('.select2') && $('#modal_novo_agendamento_creche').find('.select2').val(null).trigger('change');
        $('#modal_novo_agendamento_creche').find('.select2') && $('#modal_novo_agendamento_creche').find('.select2').find('.select2-selection--single').removeClass('select2-valid');
        $('#modal_novo_agendamento_creche').find('.select2') && $('#modal_novo_agendamento_creche').find('.select2').find('.select2-selection--single').removeClass('select2-invalid');
        selectedService = null;

        $('#modal_endereco_cliente').find('input, select').val(null);
        $('#modal_endereco_cliente').find('input, select').removeClass('is-valid').removeClass('is-invalid').tooltip('dispose');

        reopen_modal_novo_agendamento_creche = false;

        handleTurmaInput();
        handleServicoReservaAndDataSaidaFields();
        handleCrecheServicosExtraFields();
        handleAddressModalForCreche();
    });

    // Fim dos eventos de disparo das funções
    // -------


    // -------
    // Validações finais para o envio do formulário

    $('#btn-store').on('click', function (e) {
        e.preventDefault();

        if (addClassRequired('#main-form') && validateCrecheServicoReservaRangeDate() && validateCrecheExtraServicos() && validateCrecheProdutos() && validateTurmaIsFree() && validateCrecheFrete()) {
            $('#main-form').trigger('submit');
        }
    });

    $('#submit_novo_agendamento_creche').on('click', function (e) {
        e.preventDefault();

        if (
            !addClassRequired('#form-novo-agendamento-creche', true) ||
            !validateCrecheServicoReservaRangeDate() ||
            !validateCrecheExtraServicos() ||
            !validateCrecheProdutos() ||
            !validateTurmaIsFree() ||
            !validateCrecheFrete()
        ) {
            return;
        }

        let formDataArray = $('#form-novo-agendamento-creche').serializeArray();

        let filteredFormData = formDataArray.filter(function(field) {
            return field.value.trim() !== '';
        });

        let formDataObj = {};
        filteredFormData.forEach(function(field) {
            if (field.name.endsWith("[]")) {
                let key = field.name.replace("[]", "");

                if (!formDataObj[key]) {
                    formDataObj[key] = [];
                }

                formDataObj[key].push(field.value);
            } else {
                formDataObj[field.name] = field.value;
            }
        });

        const frete_modal = document.getElementById('modal_endereco_cliente');

        formDataObj.agendamento_id = frete_modal.getAttribute('data-reserva-id');
        formDataObj.cliente_id = frete_modal.getAttribute('data-cliente-id');
        formDataObj.modulo = frete_modal.getAttribute('data-modulo');
        formDataObj.empresa_id = $('#empresa_id').val();
        formDataObj.cep = frete_modal.querySelector('input[name="cep"]').value;
        formDataObj.rua = frete_modal.querySelector('input[name="rua"]').value;
        formDataObj.bairro = frete_modal.querySelector('input[name="bairro"]').value;
        formDataObj.numero = frete_modal.querySelector('input[name="numero"]').value;
        formDataObj.modal_cidade_id = frete_modal.querySelector('select[name="modal_cidade_id"]').value;
        formDataObj.complemento = frete_modal.querySelector('textarea[name="complemento"]').value;

        let formData = $.param(formDataObj);

        $.ajax({
            url: path_url + 'api/creches/store-creche',
            method: 'POST',
            data: formData,
            success: function (response) {
                if (response.success == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reserva agendada com sucesso!',
                    }).then(() => {
                        location.reload(); 
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao realizar reserva',
                        text: response.message ?? ''
                    });
                }
            },
            error: function (xhr) {
                let msg = 'Ocorreu um erro desconhecido ao realizar a reserva.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao realizar reserva!',
                    text: msg
                });
            }
        });
    });

    // Fim das validações finais para o envio do formulário
    // -------
});