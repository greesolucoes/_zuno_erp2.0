document.addEventListener('DOMContentLoaded', function () {
    window.selectDiv = function (ref) {
        $('button').removeClass('link-active')
        if (ref == 'aliquotas') {
            $('.div-aliquotas').removeClass('d-none')
            $('.div-identificacao').addClass('d-none')
            $('.btn-aliquotas').addClass('link-active')
        } else {
            $('.div-aliquotas').addClass('d-none')
            $('.div-identificacao').removeClass('d-none')
            $('.btn-identificacao').addClass('link-active')
        }
    }

    // #region Variáveis de controle do arquivo
    
        let reopen_modal_novo_agendamento_estetica = false;
        let current_agendamentos = [];

        let jornada_empresa = null;
        let jornada_colaborador = null;
        let colaborador_has_jornada_trabalho = false;

        let container = $(document.body);
        let id_estetica = container.find('#id_estetica').val();

        $('#modal_novo_agendamento_estetica').on('show.bs.modal', function (e) {
            container = $(this).length > 0 ? $(this) : $(document.body);
            id_estetica = container.find('#id_estetica').val();
        });

        $('#edit_reserva_estetica').on('show.bs.modal', function (e) {
            container = $(this).length > 0 ? $(this) : $(document.body);
            id_estetica = container.find('#id_estetica').val();
        });
    // #endregion
    
    // #region Configurações dos campos select2

        function setAnimalSelect2() {
            const parent_modal = $('#modal_novo_agendamento_estetica');
            
            $(parent_modal.length > 0 ? parent_modal : $('body')).find('select[name="animal_id"]').each(function () {
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

        function setColaboradorSelect2ForEstetica() {
            const parent_modal = $('#modal_novo_agendamento_estetica');

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
            }).on('select2:select', async function (e) {
                await getSelectedDayJornada();
                validateDataAndHorarioFromEsteticaAgendamento();
            })
            
            const selected_colaborador = $('input[name="id_colaborador"]').val();
            const label_colaborador = $('input[name="nome_colaborador"]').val();

            if (selected_colaborador && label_colaborador) {
                const option = new Option(label_colaborador, selected_colaborador, true, true);
                $('select[name="colaborador_id"]').append(option).trigger('change');
            }
        }

        function setupServicoSelects() {
            const dropdownParent = $('#modal_novo_agendamento_estetica').length > 0 ? $('#modal_novo_agendamento_estetica') : $(document.body);
            
            $(dropdownParent).find('select[name="servico_id[]"]').each(function(id, element) {
                $(this).select2({
                    dropdownParent: dropdownParent.length > 0 ? dropdownParent : $(document.body),
                    minimumInputLength: 2,
                    language: 'pt-BR',
                    placeholder: 'Digite para buscar o serviço',
                    width: '100%',
                    ajax: {
                        cache: true,
                        url: path_url + 'api/petshop/servicos',
                        dataType: 'json',
                        data: function(params) {
                            const payload = {
                                pesquisa: params.term,
                                empresa_id: $('#empresa_id').val(),
                            };

                            if ($(element).closest('table').hasClass('table-estetica-servico-frete')) {
                                payload.is_frete = true;
                                payload.categoria = 'FRETE';
                            } else {
                                payload.categoria = 'ESTETICA';
                            }
                            

                            return payload;
                        },
                        processResults: function(response) {
                            return {
                                results: response.map(function(v) {
                                    return { 
                                        id: v.id,
                                        text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor),
                                        tempo_execucao: v.tempo_execucao,
                                        valor: v.valor
                                    };
                                })
                            };
                        }
                    }
                }).on('select2:select', function(e) {
                    var row = $(this).closest('tr');
                    var servico_id = $(this).val();

                    if (!servico_id) return;

                    row.find('.subtotal-servico').val(convertFloatToMoeda(e.params.data.valor));

                    $('#servico_idu').val(servico_id);
                    servicoSelect = document.querySelector('select.servico_id');

                    if (!$(this).data('is-frete')) {
                        $(this).siblings('input[name="tempo_execucao"]').first().val(e.params.data.tempo_execucao);

                        handleDataAgendamentoEsteticaFields();
                    }

                    if ($(this).data('is-frete')) {
                        handleAddressModalForEstetica();
                    }
                });
            });
        }

        function setupProdutoSelects() {
            $('select.produto_id').each(function() {
                const dropdownParent = $(this).closest('.modal');
                if ($(this).data('select2')) {
                    $(this).off('change').select2('destroy');
                }
                $(this).select2({
                    dropdownParent: dropdownParent.length ? dropdownParent : $(document.body),
                    minimumInputLength: 2,
                    language: 'pt-BR',
                    placeholder: 'Digite para buscar o produto',
                    width: '100%',
                    ajax: {
                        cache: true,
                        url: path_url + 'api/produtos',
                        dataType: 'json',
                        data: function(params) {
                            return {
                                pesquisa: params.term,
                                empresa_id: $('#empresa_id').val(),
                                categoria: 'ESTETICA'
                            };
                        },
                        processResults: function(response) {
                            return {
                                results: response.map(function(v) {
                                    return { id: v.id, text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor_unitario) };
                                })
                            };
                        }
                    }
                }).on('change', function() {
                    var $row = $(this).closest('tr');
                    var produto_id = $(this).val();
                    if (!produto_id) return;
                    $.get(path_url + 'api/produtos/findId/' + produto_id)
                        .done(function(res) {
                            var valor = res.valor_unitario || 0;
                            $row.find('.valor_unitario-produto').val(convertFloatToMoeda(valor));
                            var qtd = parseFloat($row.find('.qtd-produto').val()) || 0;
                            $row.find('.subtotal-produto').val(convertFloatToMoeda(valor * qtd));
                        });
                });
            });
        }

        setupServicoSelects();
        setupProdutoSelects();
        setAnimalSelect2();
        setColaboradorSelect2ForEstetica();

    // #endregion

    // #region Ações dos botões auxiliares dos campos em tabela 
        $(document).on('click', '.btn-add-estetica-tr', function () {
            const row = $(this).closest('.row');

            let table = row.siblings('.table-dynamic');
            if (!table.length) {
                table = row.prevAll('.table-dynamic').first();
            }

            let is_empty = false;
            table.find('input, select').each(function () {
                if ((($(this).val() === '' || $(this).val() === null) &&
                    $(this).attr('type') !== 'hidden' &&
                    $(this).attr('type') !== 'file' &&
                    !$(this).hasClass('ignore') &&
                    !$(this).prop('disabled'))) {
                    is_empty = true;
                }
            });

            if (is_empty) {
                new swal('Atenção', 'Preencha todos os campos antes de adicionar novos.', 'warning');
                return;
            }

            const tr = table.find('.dynamic-form').first();
            if (tr.length === 0) return;
            
            let clone = tr.clone();

            clone.find('input, select').val('');
            clone.find('input, select').removeClass('is-invalid').removeClass('is-valid').tooltip('dispose');
            clone.find('.select2-container').remove();
            clone.find('select.servico_id, select.produto_id')
                .removeClass('select2-hidden-accessible')
                .removeAttr('data-select2-id')
                .removeAttr('aria-hidden') 
                .removeAttr('tabindex');

            table.append(clone);

            setTimeout(function () {
                setupServicoSelects();
                setupProdutoSelects();
            }, 100);
        });

        $(document).on('click', '.estetica-btn-remove-tr', function (e) {
            e.preventDefault();

            const btn = $(this);
            const tr = btn.closest('tr');

            const is_reserva_servico = tr.find('select[name="servico_id[]"]').length > 0 && !tr.find('select[name="servico_id[]"]').data('is-frete');

            Swal.fire({
                icon: 'warning',
                title: 'Deseja realmente remover esse item?',
                text: `
                    ${
                        is_reserva_servico ? 
                        'Você precisará redefinir o horário de agendamento.' : 
                        'Essa ação não poderá ser desfeita.'
                    } 
                `,
                showCancelButton: true,
                confirmButtonText: 'Remover',
                cancelButtonText: 'Cancelar',
            }).then((willDelete) => {
                if (willDelete.isConfirmed) {
                    const tbody = btn.closest('tbody');

                    tr.find('input, select').removeClass('is-invalid').removeClass('is-valid').tooltip('dispose'); 

                    if (tbody.find('tr.dynamic-form').length > 1) {
                        btn.closest('tr').remove();
                    } else {
                        btn.closest('tr').find('input, select').val(null).trigger('change');
                    }

                    if (tr.find('select[name="servico_id[]"]').data('is-frete')) {
                        handleAddressModalForEstetica();
                    }

                    handleDataAgendamentoEsteticaFields();
                } 
            });
        });

        $(document).on('blur', '.qtd-produto', function () {
            var $row = $(this).closest('tr');
            var valor = convertMoedaToFloat($row.find('.valor_unitario-produto').val());
            var qtd = parseFloat($(this).val()) || 0;
            $row.find('.subtotal-produto').val(convertFloatToMoeda(valor * qtd));
        });
    // #endregion

    // #region Configuração e manipulação dos campos de data e horário

        container.find(`
            input[name="data_agendamento"],
            input[name="horario_agendamento"],
            input[name="horario_saida"]
        `).on('focus', function () {
            $(this).data('old', $(this).val());
        })

        container.find(`
            input[name="data_agendamento"],
            input[name="horario_saida"]
        `).on('blur', function () {
            validateDataAndHorarioFromEsteticaAgendamento($(this));
        })

        container.find('input[name="horario_agendamento"]').on('blur', function () {
            if (!validateDataAndHorarioFromEsteticaAgendamento($(this))) return;

            Swal.fire({
                icon: 'question',
                title: 'Deseja preencher o horário de saída automaticamente?',
                text: 'O horário de saída será calculado com base no tempo de execução dos serviço selecionados até o momento.',
                showCancelButton: true,
                confirmButtonText: 'Sim, preencher!',
                cancelButtonText: 'Não, manter como está.',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    calcAgendamentoPeriodWithServicos();
                }
            });
        })

        container.find(`
            input[name="data_agendamento"]
        `).on('change', function () {
            if (!$(this).val() || $(this).val().length !== 10) return;

            const [ano, mes, dia] = $(this).val().split('-');
            if (ano.length !== 4 || ano < 1000) return;

            if (mes.length !== 2 || dia.length !== 2) return;

            validateDataAndHorarioFromEsteticaAgendamento($(this));
            handleDataAgendamentoEsteticaFields();
        })

        /**
         * Controla o campo de data do agendamento
         * conforme o serviço de agendamento é selecionado ou deselecionado
         * e os campos de horário de início e saída conforme a data selecionada.
         * @returns 
         */
        function handleDataAgendamentoEsteticaFields() {
            const selected_servicos = container.find('select[name="servico_id[]"]').filter(
                function () {
                    return $(this).val() !== null;
                }
            );

            const data_agendamento_field = container.find('input[name="data_agendamento"]');
            const horario_agendamento_field = container.find('input[name="horario_agendamento"]');
            const horario_saida_field = container.find('input[name="horario_saida"]');

            if (selected_servicos.length > 0) {
                data_agendamento_field.prop('disabled', false);
                data_agendamento_field.tooltip('dispose');
            } else {
                const empty_msg_content = container.find('#empty-msg-content');
                const empresa_schedule_date_content = container.find('#empresa-schedule-content');
                const funcionario_schedule_date_content = container.find('#funcionario-schedule-content');
                const current_agendamentos_content = container.find('#current-agendamentos-estetica-content');

                current_agendamentos_content.addClass('d-none');
                empresa_schedule_date_content.addClass('d-none');
                funcionario_schedule_date_content.addClass('d-none');
                empty_msg_content.removeClass('d-none');

                empty_msg_content.html(`
                    <i class="ri-calendar-close-fill"></i>
                    Nenhuma data escolhida
                `);

                data_agendamento_field.prop('disabled', true);
                initializeTooltip(data_agendamento_field, 'Selecione um serviço primeiro para definir o agendamento.', { trigger: 'hover focus' });

                horario_agendamento_field.prop('disabled', true);
                horario_saida_field.prop('disabled', true);

                data_agendamento_field.val(null);
                horario_agendamento_field.val(null);
                horario_saida_field.val(null);

                data_agendamento_field.removeClass('is-invalid').removeClass('is-valid');
                horario_agendamento_field.removeClass('is-invalid').removeClass('is-valid');
                horario_saida_field.removeClass('is-invalid').removeClass('is-valid');

                data_agendamento_field.data('old', null);
                horario_agendamento_field.data('old', null);
                horario_saida_field.data('old', null);

                return;
            }

            if (data_agendamento_field.val()) {
                horario_agendamento_field.prop('disabled', false);
                horario_saida_field.prop('disabled', false);

                horario_agendamento_field.tooltip('dispose');
                horario_saida_field.tooltip('dispose');
            } else {
                horario_agendamento_field.prop('disabled', true);
                horario_saida_field.prop('disabled', true);

                initializeTooltip(horario_agendamento_field, 'Selecione uma data primeiro para definir o horário.', { trigger: 'hover focus' });
                initializeTooltip(horario_saida_field, 'Selecione uma data primeiro para definir o horário.', { trigger: 'hover focus' });

                return;
            }

            getSelectedDayJornada();
            getCurrentAgendamentosEstetica();
        }
 
        if (!window.handleDataAgendamentoEsteticaFields) {
            window.handleDataAgendamentoEsteticaFields = handleDataAgendamentoEsteticaFields;
        }

        /**
         * Valida os campos de data e de horário do agendamento de estética
         * e da um alerta caso eles estejam inválidos
         * 
         * @param {JQuery | null} trigger Input que disparou a validação
         * 
         * @returns 
         */
        function validateDataAndHorarioFromEsteticaAgendamento(trigger = null) {
            const data_agendamento_field = container.find('input[name="data_agendamento"]');
            const horario_agendamento_field = container.find('input[name="horario_agendamento"]');
            const horario_saida_field = container.find('input[name="horario_saida"]');

            const requested_colaborador_id = container.find('select[name="colaborador_id"]').val();

            const data_agendamento_date = new Date(data_agendamento_field.val());
            data_agendamento_date.setDate(data_agendamento_date.getDate() + 1);
            data_agendamento_date.setHours(0, 0, 0, 0);

            data_agendamento_field.removeClass('is-invalid');
            data_agendamento_field.tooltip('dispose');

            if (colaborador_has_jornada_trabalho) {
                if (!jornada_colaborador || jornada_colaborador == 'null') {
                    horario_agendamento_field.val('');
                    horario_saida_field.val('');

                    horario_agendamento_field.prop('disabled', true);
                    horario_saida_field.prop('disabled', true);

                    initializeTooltip(horario_agendamento_field, 'O colaborador selecionado está de folga neste dia', { trigger: 'hover focus' });
                    initializeTooltip(horario_saida_field, 'O colaborador selecionado está de folga neste dia', { trigger: 'hover focus' });

                    return false;
                } else {
                    if (data_agendamento_field.val()) {
                        horario_agendamento_field.prop('disabled', false);
                        horario_saida_field.prop('disabled', false);

                        horario_agendamento_field.tooltip('dispose');
                        horario_saida_field.tooltip('dispose');
                    }
                }
            } else if (data_agendamento_field.val()) {
                horario_agendamento_field.prop('disabled', false);
                horario_saida_field.prop('disabled', false);

                horario_agendamento_field.tooltip('dispose');
                horario_saida_field.tooltip('dispose');    
            }

            if (!horario_agendamento_field.val()) return false;

            const horario_agendamento_date_time = new Date(`${data_agendamento_field.val()}T${horario_agendamento_field.val()}`);

            horario_agendamento_field.removeClass('is-invalid');

            if (horario_saida_field.val()) {
                const horario_saida_date_time = new Date(`${data_agendamento_field.val()}T${horario_saida_field.val()}`);

                horario_saida_field.removeClass('is-invalid');

                if (horario_saida_date_time <= horario_agendamento_date_time) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Horário de saída inválido!',
                        text: 'O horário de saída do agendamento precisa ser posterior ao horário de início.'
                    });

                    if (trigger) {
                        trigger.val(trigger.data('old'));
                        trigger.addClass('is-invalid');

                        return false;
                    } 

                    horario_saida_field.val(horario_saida_field.data('old'));
                    horario_saida_field.addClass('is-invalid');

                    return false;
                }
            }

            if (colaborador_has_jornada_trabalho) {
                if (jornada_colaborador && jornada_colaborador != 'null') {
                    const requested_horario_agendamento_date_time = new Date(`${data_agendamento_field.val()}T${horario_agendamento_field.val()}`);
                    const requested_horario_saida_date_time = new Date(`${data_agendamento_field.val()}T${horario_saida_field.val()}`);

                    const horario_agendamento_jornada_funcionario = new Date(
                        `${data_agendamento_field.val()}T${jornada_colaborador.hora_inicio}`
                    );
                    const horario_fim_jornada_funcionario = new Date(
                        `${data_agendamento_field.val()}T${jornada_colaborador.hora_fim}`
                    );

                    if (
                        horario_agendamento_jornada_funcionario > requested_horario_agendamento_date_time ||
                        horario_fim_jornada_funcionario < requested_horario_agendamento_date_time ||
                        horario_fim_jornada_funcionario < requested_horario_saida_date_time
                    ) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Horário selecionado inválido!',
                            text: 'O horário selecionado não está dentro da jornada de trabalho do colaborador selecionado.'
                        })

                        if (trigger) {
                            trigger.val(trigger.data('old'));
                            trigger.addClass('is-invalid');

                            initializeTooltip(trigger, 'O horário selecionado não está dentro da jornada de trabalho do colaborador selecionado', { trigger: 'hover focus' });
                        }

                        return false;
                    }

                    if (jornada_colaborador.inicio_intervalo && jornada_colaborador.fim_intervalo) {
                        const horario_agendamento_intervalo = new Date(
                            `${data_agendamento_field.val()}T${jornada_colaborador.inicio_intervalo}`
                        )
                        const horario_fim_intervalo = new Date(
                            `${data_agendamento_field.val()}T${jornada_colaborador.fim_intervalo}`
                        )

                        if (
                            (
                                requested_horario_agendamento_date_time > horario_agendamento_intervalo &&
                                requested_horario_agendamento_date_time < horario_fim_intervalo
                            ) ||
                            (
                                requested_horario_saida_date_time > horario_agendamento_intervalo &&
                                requested_horario_saida_date_time < horario_fim_intervalo 
                            )
                        ) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Horário selecionado inválido!',
                                text: 'O horário selecionado invade o intervalo do colaborador.'
                            })

                            if (trigger) {
                                trigger.val(trigger.data('old'));
                                trigger.addClass('is-invalid');

                                initializeTooltip(trigger, 'O horário selecionado invade o intervalo do colaborador.', { trigger: 'hover focus' });
                            }

                            return false;
                        }
                    }
                }
            }

            if (jornada_empresa) {
                const requested_horario_agendamento_date_time = new Date(`${data_agendamento_field.val()}T${horario_agendamento_field.val()}`);
                const requested_horario_saida_date_time = new Date(`${data_agendamento_field.val()}T${horario_saida_field.val()}`);

                const horario_agendamento_jornada_empresa = new Date(
                    `${data_agendamento_field.val()}T${jornada_empresa.hora_inicio}`
                );
                const horario_fim_jornada_empresa = new Date(
                    `${data_agendamento_field.val()}T${jornada_empresa.hora_fim}`
                );

                if (
                    horario_agendamento_jornada_empresa > requested_horario_agendamento_date_time ||
                    horario_fim_jornada_empresa < requested_horario_agendamento_date_time ||
                    horario_fim_jornada_empresa < requested_horario_saida_date_time
                ) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Horário selecionado inválido!',
                        text: 'O horário selecionado não está dentro da jornada de trabalho da empresa.'
                    })

                    if (trigger) {
                        trigger.val(trigger.data('old'));
                        trigger.addClass('is-invalid');

                        initializeTooltip(trigger, 'O horário selecionado não está dentro da jornada de trabalho da empresa.', { trigger: 'hover focus' });
                    }

                    return false;
                }
            }

            if (current_agendamentos.length > 0 && data_agendamento_field.val() && horario_agendamento_field.val() && horario_saida_field.val()) {
                let is_valid = true;
                let conflited_colaborador = null;
                let conflited_horario = null;

                current_agendamentos.forEach(agendamento => {
                    if (id_estetica) {
                        if (id_estetica == agendamento.id) return;
                    }

                    const requested_horario_agendamento_date_time = new Date(`${data_agendamento_field.val()}T${horario_agendamento_field.val()}`);
                    const requested_horario_saida_date_time = new Date(`${data_agendamento_field.val()}T${horario_saida_field.val()}`);

                    const horario_agendamento_agendamento_date_time = new Date(`${agendamento.data_agendamento}T${agendamento.horario_agendamento}:00`);
                    const horario_saida_agendamento_date_time = new Date(`${agendamento.data_agendamento}T${agendamento.horario_saida}:00`);

                    if (!agendamento.colaborador_id && !requested_colaborador_id) {
                        if (
                            horario_saida_agendamento_date_time > requested_horario_agendamento_date_time &&
                            horario_agendamento_agendamento_date_time < requested_horario_saida_date_time
                        ) {
                            conflited_horario = `${agendamento.horario_agendamento} - ${agendamento.horario_saida}`;

                            is_valid = false;
                            return false;
                        }                    
                    }

                    if (requested_colaborador_id) {
                        if (agendamento.colaborador_id == requested_colaborador_id) {
                            if (
                                horario_saida_agendamento_date_time > requested_horario_agendamento_date_time &&
                                horario_agendamento_agendamento_date_time < requested_horario_saida_date_time
                            ) {
                                conflited_horario = `${agendamento.horario_agendamento} - ${agendamento.horario_saida}`;
                                conflited_colaborador = agendamento.colaborador.nome;

                                is_valid = false;
                                return false;
                            }
                        }
                    }
                });

                if (!is_valid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Horário selecionado inválido!',
                        html: `
                            <div class="d-flex flex-column align-items-start gap-1 mt-3">
                                <div>
                                    O horário selecionado conflita com o horário de outro agendamento.
                                </div>
                                ${conflited_colaborador ? `
                                    <div>
                                        <b>Colaborador:</b> ${conflited_colaborador}<br>
                                    </div>
                                ` : ''}
                                <div>
                                    <b>Horário:</b> ${conflited_horario}
                                </div>
                            </div>
                        `,
                    });

                    if (trigger && trigger.prop('name' ) != 'data_agendamento') {
                        trigger.val(trigger.data('old'));
                        trigger.addClass('is-invalid');

                        initializeTooltip(trigger, 'O horário selecionado conflita com o horário de outro agendamento.', { trigger: 'hover focus' });
                    }
                
                    return false;
                }
            }

            data_agendamento_field.prop('disabled', false);
            horario_agendamento_field.prop('disabled', false);
            horario_saida_field.prop('disabled', false);

            data_agendamento_field.tooltip('dispose');
            horario_agendamento_field.tooltip('dispose');
            horario_saida_field.tooltip('dispose');

            data_agendamento_field.removeClass('is-invalid');
            horario_agendamento_field.removeClass('is-invalid');
            horario_saida_field.removeClass('is-invalid');

            return true;
        }
    
        if (!window.validateDataAndHorarioFromEsteticaAgendamento) {
            window.validateDataAndHorarioFromEsteticaAgendamento = validateDataAndHorarioFromEsteticaAgendamento;
        }

        /**
         * Calcula o tempo total dos serviços de agendamento passados até o momento
         * retornando o total em minutos
         * 
         * @returns {number} Tempo total em minutos
         */
        function calcAgendamentoPeriodWithServicos() {
            const tempo_execucao_servicos_fields = container.find('input[name="tempo_execucao"]').filter(
                function () {
                    return $(this).val() !== '';
                }
            );

            let total_tempo_execucao = 0;
            tempo_execucao_servicos_fields.each(function () {
                total_tempo_execucao += parseInt($(this).val());
            }); 

            const data_agendamento_field = container.find('input[name="data_agendamento"]');
            const horario_agendamento_field = container.find('input[name="horario_agendamento"]');
            const horario_saida_field = container.find('input[name="horario_saida"]');

            if (!horario_agendamento_field.val()) return;

            const horario_agendamento_date_time = new Date(`${data_agendamento_field.val()}T${horario_agendamento_field.val()}`);
            horario_agendamento_date_time.setMinutes(horario_agendamento_date_time.getMinutes() + total_tempo_execucao);
            
            const output_time = `${horario_agendamento_date_time.getHours().toFixed(0).padStart(2, '0')}:${horario_agendamento_date_time.getMinutes().toFixed(0).padStart(2, '0')}`;

            horario_saida_field.val(output_time).trigger('blur');
        }

        /**
         * Busca e retorna os horários de funcionamento conforme o dia selecionado
         * exibindo tanto a jornada de trabalho da empresa como a do colaborador
         * 
         * @returns 
         */
        async function getSelectedDayJornada() {
            const data_agendamento_field = container.find('input[name="data_agendamento"]');

            if (!data_agendamento_field.val()) return;
            
            const funcionario_id = container.find('select[name="colaborador_id"]').val();
            const empresa_id = $('#empresa_id').val();
            
            await $.ajax({
                url: path_url + 'api/esteticas/get-jornada',
                method: 'GET',
                data: {
                    dia_agendamento: data_agendamento_field.val(),
                    funcionario_id,
                    empresa_id
                },
                success: function (res) {
                    if (res.success) {
                        if (res.jornada_empresa) {
                            jornada_empresa = res.jornada_empresa;
                        }

                        if (res.has_jornada_funcionario) {
                            colaborador_has_jornada_trabalho = true;
                            jornada_colaborador = res.jornada_funcionario;
                        } else {
                            colaborador_has_jornada_trabalho = false;
                            jornada_colaborador = null;
                        }
                    }
                },
                dataType: 'json' 
            })

            const empty_msg_content = container.find('#empty-msg-content');
            const empresa_schedule_content = container.find('#empresa-schedule-content');
            const funcionario_schedule_content = container.find('#funcionario-schedule-content');

            if (jornada_empresa || jornada_colaborador) {
                empty_msg_content.addClass('d-none');

                const empresa_horario_funcionamento_content = container.find('#empresa-horario-funcionamento-content');

                const funcionario_horario_funcionamento_content = container.find('#funcionario-horario-funcionamento-content');
                const funcionario_horario_intervalo_container = container.find('#funcionario-horario-intervalo-container');
                const funcionario_horario_intervalo_content = container.find('#funcionario-horario-intervalo-content');

                if (jornada_empresa) {
                    empresa_schedule_content.removeClass('d-none');

                    const formmated_empresa_hora_inicio = jornada_empresa.hora_inicio.split(':')[0] + ':' + jornada_empresa.hora_inicio.split(':')[1];
                    const formatted_empresa_hora_fim = jornada_empresa.hora_fim.split(':')[0] + ':' + jornada_empresa.hora_fim.split(':')[1];
                    const formatted_empresa_horario_funcionamento = formmated_empresa_hora_inicio + ' - ' + formatted_empresa_hora_fim;

                    empresa_horario_funcionamento_content.text(formatted_empresa_horario_funcionamento);
                } else {
                    empresa_schedule_content.addClass('d-none');
                }

                if (colaborador_has_jornada_trabalho) {
                    if (jornada_colaborador) {
                        funcionario_schedule_content.removeClass('d-none');

                        const formmated_funcionario_hora_inicio = jornada_colaborador.hora_inicio.split(':')[0] + ':' + jornada_colaborador.hora_inicio.split(':')[1];
                        const formatted_funcionario_hora_fim = jornada_colaborador.hora_fim.split(':')[0] + ':' + jornada_colaborador.hora_fim.split(':')[1];
                        const formatted_funcionario_horario_funcionamento = formmated_funcionario_hora_inicio + ' - ' + formatted_funcionario_hora_fim;

                        funcionario_horario_funcionamento_content.text(formatted_funcionario_horario_funcionamento);

                        if (jornada_colaborador.inicio_intervalo && jornada_colaborador.fim_intervalo) {
                            funcionario_horario_intervalo_container.removeClass('d-none');

                            const formatted_hora_inicio_intervalo = jornada_colaborador.inicio_intervalo.split(':')[0] + ':' + jornada_colaborador.inicio_intervalo.split(':')[1];
                            const formatted_funcionario_hora_fim_intervalo = jornada_colaborador.fim_intervalo.split(':')[0] + ':' + jornada_colaborador.fim_intervalo.split(':')[1];
                            const formatted_horario_intervalo = formatted_hora_inicio_intervalo + ' - ' + formatted_funcionario_hora_fim_intervalo;

                            funcionario_horario_intervalo_content.text(formatted_horario_intervalo);
                        } else {
                            funcionario_horario_intervalo_container.addClass('d-none');
                        }
                    } else {
                        funcionario_schedule_content.removeClass('d-none');
                        funcionario_horario_intervalo_container.addClass('d-none');

                        funcionario_horario_funcionamento_content.text('O colaborador selecionado está de folga neste dia.');
                    }
                } else {
                    funcionario_schedule_content.addClass('d-none');
                    funcionario_horario_intervalo_container.addClass('d-none');
                }

            } else {
                empresa_schedule_content.addClass('d-none');
                funcionario_schedule_content.addClass('d-none');
                empty_msg_content.removeClass('d-none');

                empty_msg_content.html(`
                    <i class="ri-calendar-close-fill"></i>
                    Sem horário de funcionamento para este dia
                `);
            }
        }       

        if (!window.getSelectedDayJornada) {
            window.getSelectedDayJornada = getSelectedDayJornada;
        }

        async function getCurrentAgendamentosEstetica() {
            const data_agendamento_field = container.find('input[name="data_agendamento"]');

            if (!data_agendamento_field.val()) return;

            const empresa_id = $('#empresa_id').val();

            await $.ajax({
                url: path_url + 'api/esteticas/get-current-agendamentos',
                method: 'GET',
                data: {
                    dia_agendamento: data_agendamento_field.val(),
                    empresa_id
                },
                success: function (res) {
                    if (res.success) {
                        current_agendamentos = res.data;
                    }
                },
                dataType: 'json' 
            })

            const current_agendamentos_content = container.find('#current-agendamentos-estetica-content');
            const agendamentos_content_template = current_agendamentos_content.find('.current-agendamento-estetica-item.agendamento-estetica-item-template').clone();
            const current_agendamentos_empty_msg = current_agendamentos_content.find('#current-agendamentos-estetica-empty-msg');

            if (current_agendamentos.length > 0) {
                current_agendamentos_content.removeClass('d-none');
                current_agendamentos_empty_msg.addClass('d-none');

                current_agendamentos_content.find('.current-agendamento-estetica-item:not(.agendamento-estetica-item-template)').remove();

                current_agendamentos.forEach((agendamento) => {
                    const agendamento_item = agendamentos_content_template.clone();
                    agendamento_item.removeClass('agendamento-estetica-item-template');
                    
                    if (agendamento.id == id_estetica) {
                        agendamento_item.addClass('active-agendamento');
                    }

                    const formatted_horario = agendamento.horario_agendamento + ' - ' + agendamento.horario_saida;
                    agendamento_item.find('.agendamento-estetica-horario').text(formatted_horario);

                    const formatted_servicos = agendamento.servicos.map(
                        (servico) => `
                            <li>${servico.servico.nome}</li>
                        `
                    );
                    agendamento_item.find('.agendamento-estetica-servicos').html(formatted_servicos);

                    agendamento_item.find('.agendamento-estetica-cliente').html(agendamento.cliente.razao_social);

                    agendamento_item.find('.agendamento-estetica-pet').html(agendamento.animal.nome);

                    agendamento_item.find('.agendamento-estetica-funcionario').html(
                        agendamento.colaborador ? agendamento.colaborador.nome : '--'
                    );
                    
                    current_agendamentos_content.append(agendamento_item);
                })
            } else {
                current_agendamentos_content.removeClass('d-none');
                
                if (current_agendamentos_content.find('.current-agendamento-estetica-item').length > 0) {
                    current_agendamentos_content.find('.current-agendamento-estetica-item').remove();
                }

                current_agendamentos_content.append(agendamentos_content_template);

                current_agendamentos_empty_msg.removeClass('d-none');

                current_agendamentos_empty_msg.html(`
                    <i class="ri-calendar-check-fill"></i>
                    Sem agendamentos para este dia
                `);
            }

            current_agendamentos_content.find('.current-agendamento-estetica-item.template').remove();
        }

        handleDataAgendamentoEsteticaFields();

    // #endregion

    // #region Controle e manipulação dos modais auxiliares do formulário
        /**
         * Manipula o botão de disparo que abre o modal de endereço e limpa os campos
         * caso não haja um frete selecionado
         */
        function handleAddressModalForEstetica() {
            const container = $('#modal_novo_agendamento_estetica').length > 0 ? $('#modal_novo_agendamento_estetica') : $(document.body);

            const frete_input = container.find('select[name="servico_id[]"][data-is-frete="true"]').first();
            const handle_modal_btn = container.find('#handle-address-btn');
            const is_new_reserva = handle_modal_btn.closest('#modal_novo_agendamento_estetica').length > 0;

            const submmit_button = $('#submit_endereco_cliente')

            const endereco_data = container.find('#endereco_cliente').val() && JSON.parse(container.find('#endereco_cliente').val());

            if (frete_input.length && handle_modal_btn.length && frete_input.val()) {
                handle_modal_btn.prop('disabled', false);

                handle_modal_btn.off('click').on('click', function () {
                    const modal_endereco_cliente = $('#modal_endereco_cliente');
                    const modal_novo_agendamento_estetica = $('#modal_novo_agendamento_estetica');

                    if (modal_novo_agendamento_estetica.length > 0) {
                        modal_novo_agendamento_estetica.modal('hide');

                        modal_endereco_cliente.off('hidden.bs.modal').on('hidden.bs.modal', function () {
                            reopen_modal_novo_agendamento_estetica = true;
                            modal_novo_agendamento_estetica.modal('show');
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

                    if (window.location.href.includes('estetica/esteticas') || is_new_reserva) {
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
        handleAddressModalForEstetica()

        /**
         * Preenche os campos de endereço do modal 
         * com base no endereço original do cliente caso o usuário deseje
         */
        function getClienteAddressForModalFieldsEstetica() {
            const cliente_input = $('input[name="cliente_id"]');

            if (cliente_input.length <= 0 || !cliente_input.val()) return;

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
            setTimeout(() => {
                getClienteAddressForModalFieldsEstetica();
            }, 100);
        });

    // #endregion

    // #region Controle e manipulação do modal de novo agendamento de estética

        $('#modal_novo_agendamento_estetica').on('show.bs.modal', function (e) {
            if (reopen_modal_novo_agendamento_estetica) return;

            setupServicoSelects();
            setupProdutoSelects();
            setAnimalSelect2();
            setColaboradorSelect2ForEstetica();
            handleAddressModalForEstetica();
        });

        $(`
            #modal_novo_agendamento_estetica .btn-close,
            #modal_novo_agendamento_estetica .btn-close-modal
        `).on('click', function () {
            $('#modal_novo_agendamento_estetica').find('input, select').val(null).trigger('change');
            $('#modal_novo_agendamento_estetica').find('input, select').removeClass('is-valid');
            $('#modal_novo_agendamento_estetica').find('input, select').removeClass('is-invalid');

            $('#modal_novo_agendamento_estetica').find('.select2') && $('#modal_novo_agendamento_estetica').find('.select2').val(null).trigger('change');
            $('#modal_novo_agendamento_estetica').find('.select2') && $('#modal_novo_agendamento_estetica').find('.select2').find('.select2-selection--single').removeClass('select2-valid');
            $('#modal_novo_agendamento_estetica').find('.select2') && $('#modal_novo_agendamento_estetica').find('.select2').find('.select2-selection--single').removeClass('select2-invalid');

            $('#modal_endereco_cliente').find('input, select').val(null);
            $('#modal_endereco_cliente').find('input, select').removeClass('is-valid').removeClass('is-invalid').tooltip('dispose');

            reopen_modal_novo_agendamento_estetica = false;

            handleDataAgendamentoEsteticaFields();
        });

    // #endregion

    // #region Funções de validação do formulário

        /**
         * Valida se os campos de frete foram preenchidos 
         * caso o agendamento contenha um serviço de frete
         * 
         * @returns {boolean}
         */
        function validateEsteticaFrete() {
            const frete_input = $('select[name="servico_id[]"][data-is-frete="true"]').first();

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

    // #endregion

    // #region Configuração de envio do formulário de estética

        $('#btn-store').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (
                !addClassRequired('#main-form') ||
                !validateEsteticaFrete() || 
                !validateDataAndHorarioFromEsteticaAgendamento()
            ) return;

            $('#main-form').submit();
        });

        $('#submit_novo_agendamento_estetica').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (
                !addClassRequired('#form-novo-agendamento-estetica', true) ||
                !validateEsteticaFrete() ||
                !validateDataAndHorarioFromEsteticaAgendamento()
            ) return;

            let formDataArray = $('#form-novo-agendamento-estetica').serializeArray();

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
                url: path_url + 'api/esteticas/store-estetica',
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
                        title: 'Erro ao realizar reserva.!',
                        text: msg
                    });
                }
            });
        });

    // #endregion
});
