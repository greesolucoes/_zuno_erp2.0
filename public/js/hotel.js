$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

$(function () {
getServicosForHotelSelect2();
getProdutosForHotelSelect2();

let selectedService = null;
let reopen_modal_novo_agendamento_hotel = false;

$('input[name="checkin"], input[name="timecheckin"]').on('change input', function () {
    calculateCheckout();
});

// Estado inicial
calculateCheckout();

$('#modal_novo_agendamento_hotel').on('show.bs.modal', function () {
    if (reopen_modal_novo_agendamento_hotel) return;

    getServicosForHotelSelect2();
    getProdutosForHotelSelect2();
    setAnimalSelect2();
    setColaboradorSelect2();

    calculateCheckout();    
    calcTotalServicos();
    calcTotalProdutos();
    handleQuartoInput();
    handleServicoReservaFields();
    handleServicosExtraFields();
    setDateValidationForServicosExtrasSelect2();
});

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

$(`
    #modal_novo_agendamento_hotel .btn-close,
    #modal_novo_agendamento_hotel .btn-close-modal
`).on('click', function () {
    $('#modal_novo_agendamento_hotel').find('input, select').val(null).trigger('change');
    $('#modal_novo_agendamento_hotel').find('input, select').removeClass('is-valid');
    $('#modal_novo_agendamento_hotel').find('input, select').removeClass('is-invalid');

    $('#modal_novo_agendamento_hotel').find('.select2') && $('#modal_novo_agendamento_hotel').find('.select2').val(null).trigger('change');
    $('#modal_novo_agendamento_hotel').find('.select2') && $('#modal_novo_agendamento_hotel').find('.select2').find('.select2-selection--single').removeClass('select2-valid');
    $('#modal_novo_agendamento_hotel').find('.select2') && $('#modal_novo_agendamento_hotel').find('.select2').find('.select2-selection--single').removeClass('select2-invalid');
    selectedService = null;

    $('#modal_endereco_cliente').find('input, select').val(null);
    $('#modal_endereco_cliente').find('input, select').removeClass('is-valid').removeClass('is-invalid').tooltip('dispose');
    reopen_modal_novo_agendamento_hotel = false;

    handleQuartoInput();
    handleServicoReservaFields();
    handleServicosExtraFields();
    handleAddressModalForHotel();
});

/**
 * Configura o select2 do animal
 */
function select2Defaults(options = {}) {
    return Object.assign(
        {
            minimumInputLength: 2,
            language: 'pt-BR',
            width: '100%',
            theme: 'bootstrap4',
        },
        options
    );
}

function setAnimalSelect2() {
    const parent_modal = $('#modal_novo_agendamento_hotel');
    
    $(parent_modal.length > 0 ? parent_modal : $('body')).find('select[name="animal_id"]').each(function () {
        $(this).select2(select2Defaults({
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
        })).on('select2:select', function (e) {
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
setAnimalSelect2();

/**
 * Configura o select2 do colaborador
 */
function setColaboradorSelect2() {
    const parent_modal = $('#modal_novo_agendamento_hotel');

    $(parent_modal.length > 0 ? parent_modal : $('body')).find('select[name="colaborador_id"]').each(function () {
        $(this).select2(select2Defaults({
            placeholder: 'Digite para buscar o colaborador',
            dropdownParent: parent_modal.length > 0 ? parent_modal : null,
            ajax: {
                cache: true,
                url: path_url + 'api/funcionarios/pesquisa',
                dataType: 'json',
                data: function (params) {
                    return {
                        pesquisa: params.term,
                        empresa_id: $('#empresa_id').val(),
                    };
                },
                processResults: function (response) {
                    var results = [];

                    $.each(response, function (i, v) {
                        results.push({
                            id: v.id,
                            text: v.nome + ' - Cargo: ' + v.cargo,
                            value: v.id,
                            nome: v.nome,
                        });
                    });

                    return { results: results };
                },
            },
        })).on('select2:select', function (e) {
            var data = e.params.data;

            $('input[name="id_colaborador"]').val(data.id);
            $('input[name="nome_colaborador"]').val(data.nome ?? null);
        });

        const selected_colaborador = $('input[name="id_colaborador"]').val();
        const label_colaborador = $('input[name="nome_colaborador"]').val();

        if (selected_colaborador && label_colaborador) {
            const option = new Option(label_colaborador, selected_colaborador, true, true);
            $(this).append(option).trigger('change');
        }
    });
}
setColaboradorSelect2();

function getServicosForHotelSelect2 () {
    const parent_modal = $('#modal_novo_agendamento_hotel');

    $('select[name="servico_ids[]"]').each(function (id, element) {
        if ($(element).data('select2')) {
            $(element).off('select2:select');
            $(element).select2('destroy');
        }

        $(this).select2(select2Defaults({
            placeholder: 'Digite para buscar o serviço',
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
                        payload.categoria = 'HOTEL';
                    }

                    if ($(element).closest('table').hasClass('table-hotel-servico-frete')) {
                        payload.is_frete = true;
                    }

                    return payload;
                },
                processResults: function (response) {
                     if ($(this).data('is-reserva')) {
                        response = response.filter(v => v.categoria?.nome !== 'HOTEL');
                        if (selectedService) {
                            response = response.filter(v => v.id !== selectedService.id);
                        }
                    }

                    setDateValidationForServicosExtrasSelect2();

                    return {
                        results: response.map((v) => ({
                            id: v.id,
                            text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor),
                            valor: v.valor,
                            categoria: v.categoria,
                            tempo_execucao: v.tempo_execucao ?? v.tempo_servico,
                        }))
                    };
                },
            },
        })).on('select2:select', function (e) {
            const data = e.params.data;
            let $row = $(this).closest('tr');
            $row.find('.valor-servico').val('R$ ' + convertFloatToMoeda(data.valor)).trigger('blur');
            $row.find('input[name="servico_categoria[]"]').val(data.categoria.nome);
            $row.find('input[name="tempo_execucao"]').val(data.tempo_execucao ?? data.tempo_servico);

            if ($(this).data('is-reserva')) {
                selectedService = data;
                calculateCheckout();
            }

            if ($(this).data('is-frete')) {
                handleAddressModalForHotel();
            }

            setTimeout(() => calcTotalServicos(), 300);
        });
    });

}

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
        getServicosForHotelSelect2();
    }

    if ($(this).data('content') == 'produtos') {
        getProdutosForHotelSelect2();
    }
});

$(document).delegate('.hotel-btn-remove-tr', 'click', function (e) { 
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
                    current_btn.closest('table.table-hotel-servicos').length > 0 &&
                    current_btn.closest('table').find('.servico_id').length > 1
                ) ||
                (
                    current_btn.closest('table.table-hotel-produtos').length > 0 &&
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
                    handleAddressModalForHotel();
                }
            }

            getServicosForHotelSelect2();

            calcTotalServicos();
            calcTotalProdutos();
        }
    })
});

function calcTotalServicos () {
    let total = 0;

    $('input[name="servico_valor[]"]').each(function () {
        const value = convertMoedaToFloat($(this).val());
        
        if (
            !$(this).closest('tr').find('select[name="servico_ids[]"]').attr('data-is-reserva') && 
            !$(this).closest('tr').find('select[name="servico_ids[]"]').attr('data-is-frete')
        ) {
            total += value;
        }
    });

    $('.total-servicos').html('R$ ' + convertFloatToMoeda(total));
}

calcTotalServicos();

$('.valor-servico').off('blur').on('blur', function () {
    calcTotalServicos();
});

function getProdutosForHotelSelect2 () {
    const parent_modal = $('#modal_novo_agendamento_hotel');

    $('select.produto_id').each((id, element) => {
        $(element).select2(select2Defaults({
            placeholder: 'Digite para buscar o produto',
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
        })).on('select2:select', function (e) {
            const data = e.params.data;
            let $row = $(this).closest('tr');
            $row.find('.qtd-produto').val('1');
            $row.find('.valor_unitario-produto').val('R$ ' + convertFloatToMoeda(data.valor));
            $row.find('.subtotal-produto').val('R$ ' + convertFloatToMoeda(data.valor));

            calcTotalProdutos();
        });
    });
}

function calcTotalProdutos () {
    let total = 0;

    $('input[name="subtotal_produto[]"]').each(function () {
        const value = convertMoedaToFloat($(this).val());

        total += value;
    });

    $('.total-produtos').html('R$ ' + convertFloatToMoeda(total));
}

$(document).on('blur', '.qtd-produto', function () {
    const qtd = parseFloat($(this).val().replace(',', '.')) || 0;
    const $row = $(this).closest('tr');
    const valor = convertMoedaToFloat($row.find('.valor_unitario-produto').val());
    $row.find('.subtotal-produto').val('R$ ' + convertFloatToMoeda(qtd * valor));
    calcTotalProdutos();
});

calcTotalProdutos();

/**
 * Manipula o botão de disparo que abre o modal de endereço e limpa os campos
 * caso não haja um frete selecionado
 */
function handleAddressModalForHotel() {
    const container = $('#modal_novo_agendamento_hotel').length > 0 ? $('#modal_novo_agendamento_hotel') : $('body');

    const frete_input = container.find('select[name="servico_ids[]"][data-is-frete="true"]').first();
    const handle_modal_btn = container.find('#handle-hotel-address-btn');
    const is_new_reserva = handle_modal_btn.closest('#modal_novo_agendamento_hotel').length > 0;

    const submmit_button = $('#submit_endereco_cliente')

    const endereco_data = container.find('#endereco_cliente').val() && JSON.parse(container.find('#endereco_cliente').val());

    if (frete_input.length && handle_modal_btn.length && frete_input.val()) {
        handle_modal_btn.prop('disabled', false);

        handle_modal_btn.off('click').on('click', function () {
            const modal_endereco_cliente = $('#modal_endereco_cliente');
            const modal_novo_agendamento_hotel = $('#modal_novo_agendamento_hotel');

            if (modal_novo_agendamento_hotel.length > 0) {
                modal_novo_agendamento_hotel.modal('hide');

                modal_endereco_cliente.off('hidden.bs.modal').on('hidden.bs.modal', function () {
                    reopen_modal_novo_agendamento_hotel = true;
                    modal_novo_agendamento_hotel.modal('show');
                })
            }

            if (endereco_data) {
                modal_endereco_cliente.find('input[name="cep"]').val(endereco_data.cep);
                modal_endereco_cliente.find('input[name="rua"]').val(endereco_data.rua);
                modal_endereco_cliente.find('input[name="bairro"]').val(endereco_data.bairro);
                modal_endereco_cliente.find('input[name="numero"]').val(endereco_data.numero);
                modal_endereco_cliente.find('input[name="complemento"]').val(endereco_data.complemento);

                const new_option = new Option(endereco_data.cidade.nome, endereco_data.cidade_id, false, false);
                modal_endereco_cliente.find('select[name="modal_cidade_id"]').append(new_option).trigger('change');
            }

            if (window.location.href.includes('hotel/hoteis') || is_new_reserva) {
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
handleAddressModalForHotel()

/**
 * Preenche os campos de endereço do modal 
 * com base no endereço original do cliente caso o usuário deseje
 */
function getClienteAddressForModalFieldsForHotel() {
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
    getClienteAddressForModalFieldsForHotel();
});

/**
 * Libera os campos de check out para serem preenchidos e estabelece um limite de data para eles
 * 
 * @param {*} checkoutDate 
 */
function updateMainServiceDateTime (checkoutDate = '') {
    const $dateInput = $('input[name="servico_datas[]"]').first();

    if ($dateInput.length) {
        if (checkoutDate) {
            $('input[name="checkout"]').prop('disabled', false);
            $('input[name="timecheckout"]').prop('disabled', false);

            $('input[name="checkout"]').tooltip('dispose');
            $('input[name="timecheckout"]').tooltip('dispose');

            $dateInput.attr('max', checkoutDate);
        } else {
            $dateInput.removeAttr('max');
        }
    }
}

function calculateCheckout () {
  const checkinDate = $('input[name="checkin"]').val();
  const checkinTime = $('input[name="timecheckin"]').val();

  const $checkout = $('input[name="checkout"]');
  const $timecheckout = $('input[name="timecheckout"]');

  updateTempoExecucaoHint();

  if ($checkout.val() && $timecheckout.val()) {
    $checkout.prop('disabled', false);
    $timecheckout.prop('disabled', false);

    $checkout.tooltip('dispose');
    $timecheckout.tooltip('dispose');

    return;
  }

  $checkout.attr('disabled', true);
  $timecheckout.attr('disabled', true);

  initializeTooltip($checkout, 'Determine o serviço de reserva primeiro.', { trigger: 'hover focus' });
  initializeTooltip($timecheckout, 'Determine o serviço de reserva primeiro.', { trigger: 'hover focus' });

  updateMainServiceDateTime();

  if (!selectedService) return;
  if (!checkinDate || !checkinTime) return;

  const checkinDT = new Date(`${checkinDate}T${checkinTime}`);
  if (isNaN(checkinDT.getTime())) return;

  const dt = new Date(checkinDT.getTime());
  const tempo = parseInt(selectedService.tempo_execucao ?? selectedService.tempo_servico, 10);
  if (!Number.isFinite(tempo) || tempo <= 0) return;

  dt.setMinutes(dt.getMinutes() + tempo);

  const year   = dt.getFullYear();
  const month  = String(dt.getMonth() + 1).padStart(2, '0');
  const day    = String(dt.getDate()).padStart(2, '0');
  const hour   = String(dt.getHours()).padStart(2, '0');
  const minute = String(dt.getMinutes()).padStart(2, '0');

  $checkout.val(`${year}-${month}-${day}`).trigger('blur');
  $timecheckout.val(`${hour}:${minute}`).trigger('blur');

  updateMainServiceDateTime(`${year}-${month}-${day}`);
  handleQuartoInput();

  updateTempoExecucaoHint();
}

function formatTempoExecucao(minutosTotal) {
    const min = parseInt(minutosTotal, 10);
    if (!Number.isFinite(min) || min <= 0) return null;

    const dias = Math.floor(min / 1440);
    const resto = min % 1440;
    const horas = Math.floor(resto / 60);
    const minutos = resto % 60;

    const parts = [];
    if (dias) parts.push(`${dias}d`);
    if (horas) parts.push(`${horas}h`);
    if (minutos) parts.push(`${minutos}m`);
    return parts.length ? parts.join(' ') : '0m';
}

function formatDTBR(dateStr, timeStr) {
    if (!dateStr || !timeStr) return null;
    const dt = new Date(`${dateStr}T${timeStr}`);
    if (isNaN(dt.getTime())) return null;

    const day = String(dt.getDate()).padStart(2, '0');
    const month = String(dt.getMonth() + 1).padStart(2, '0');
    const year = dt.getFullYear();
    const hour = String(dt.getHours()).padStart(2, '0');
    const minute = String(dt.getMinutes()).padStart(2, '0');

    return `${day}/${month}/${year} ${hour}:${minute}`;
}

function updateTempoExecucaoHint() {
    const $hint = $('[data-tempo-execucao-hint="true"][data-module="hotel"]').first();
    if (!$hint.length) return;

    const dateInName = $hint.data('date-in-name');
    const timeInName = $hint.data('time-in-name');
    const dateOutName = $hint.data('date-out-name');
    const timeOutName = $hint.data('time-out-name');

    const dateIn = $(`input[name="${dateInName}"]`).val();
    const timeIn = $(`input[name="${timeInName}"]`).val();
    const dateOut = $(`input[name="${dateOutName}"]`).val();
    const timeOut = $(`input[name="${timeOutName}"]`).val();

    const $servicoSelect = $('.table-hotel-servico-reserva select[name="servico_ids[]"][data-is-reserva="true"]').first();
    const servicoText = $servicoSelect.find('option:selected').text() || '—';
    const servicoNome = servicoText ? servicoText.split(' R$')[0] : '—';

    const tempo = selectedService?.tempo_execucao || $('.table-hotel-servico-reserva input[name="tempo_execucao"]').val();
    const tempoFmt = formatTempoExecucao(tempo);

    $hint.find('[data-role="servico"]').text(servicoNome || '—');
    $hint.find('[data-role="tempo"]').text(tempoFmt || 'Selecione o serviço de reserva');
    $hint.find('[data-role="entrada"]').text(formatDTBR(dateIn, timeIn) || '—');
    $hint.find('[data-role="saida"]').text(formatDTBR(dateOut, timeOut) || '—');
}


/**
 * Manipula os campos de check out conforme os campos
 * de check in foram preenchidos
*/
function handleServicoReservaFields () {
    const checkin_input = $('input[name="checkin"]');
    const checkin_time_input = $('input[name="timecheckin"]');
    const checkout_input = $('input[name="checkout"]');
    const checkout_hour_input = $('input[name="timecheckout"]');

    const servico_reserva_input_line = $('.table-hotel-servico-reserva tbody .dynamic-form');
    const servico_input = servico_reserva_input_line.find('select[name="servico_ids[]"]');
    const valor_servico_input = servico_reserva_input_line.find('input[name="servico_valor[]"]');

    const checkout_datetime = new Date(`${checkout_input.val()}T${checkout_hour_input.val()}`);

    const now = new Date();

    if (
        (!checkin_input.val() || !checkin_time_input.val()) &&
        !servico_input.val()
    ) {
        valor_servico_input.prop('disabled', true);

        initializeTooltip(servico_input.next('.select2'), 'Determine o check in da reserva primeiro.', { trigger: 'hover focus' });
        initializeTooltip(valor_servico_input, 'Determine o check in da reserva primeiro.', { trigger: 'hover focus' });

        return;
    }

    if (checkin_input.val() && checkin_time_input.val() && !servico_input.val()) {
        servico_input.prop('disabled', false);
        valor_servico_input.prop('disabled', false);

        servico_input.data('select2')?.$container.tooltip('dispose');
        valor_servico_input.tooltip('dispose');

        return;
    }
}
handleServicoReservaFields();

/**
 * Manipula os campos de data e hora do serviço extra
 * conforme os campos de check in e check out foram preenchidos
 * 
 * @returns 
*/
function handleServicosExtraFields () {
    const servico_extra_input_line = $('.table-hotel-servicos tbody .dynamic-form');

    const servico_frete_input_line = $('.table-hotel-servico-frete tbody .dynamic-form');
    const servico_frete_input = servico_frete_input_line.find('select[name="servico_ids[]"]').first();
    const servico_frete_valor_input = servico_frete_input_line.find('input[name="servico_valor[]"]').first();

    const checkin_input = $('input[name="checkin"]');
    const checkin_time_input = $('input[name="timecheckin"]');
    const checkout_input = $('input[name="checkout"]');
    const checkout_time_input = $('input[name="timecheckout"]');

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
            checkin_input.val() && checkin_time_input.val() &&
            checkout_input.val() && checkout_time_input.val()
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
                checkin_input.val() && checkin_time_input.val() &&
                checkout_input.val() && checkout_time_input.val()
            ) &&
            (
                checkout_time_input.attr('disabled')
            )
        ) {

            disableWithHidden(servico_input);
            disableWithHidden(servico_frete_input);
            disableWithHidden(servico_frete_valor_input);
            disableWithHidden(data_servico_input);
            disableWithHidden(hora_servico_input);
            disableWithHidden(valor_servico_input);

            return;
        }

        if (!checkin_input.val() || !checkin_time_input || !checkout_input || !checkout_time_input) {
            disableWithHidden(servico_input);
            disableWithHidden(servico_frete_input);
            disableWithHidden(servico_frete_valor_input);
            disableWithHidden(data_servico_input);
            disableWithHidden(hora_servico_input);
            disableWithHidden(valor_servico_input);

            initializeTooltip(servico_input.next('.select2'), 'Determine o check in e check out da reserva primeiro.', { trigger: 'hover focus' });
            initializeTooltip(servico_frete_input.next('.select2'), 'Determine o check in e check out da reserva primeiro.', { trigger: 'hover focus' });
            initializeTooltip(data_servico_input, 'Determine o check in e check out da reserva primeiro.', { trigger: 'hover focus' });
            initializeTooltip(hora_servico_input, 'Determine o check in e check out da reserva primeiro.', { trigger: 'hover focus' });
            initializeTooltip(valor_servico_input, 'Determine o check in e check out da reserva primeiro.', { trigger: 'hover focus' });
            initializeTooltip(servico_frete_valor_input, 'Determine o check in e check out da reserva primeiro.', { trigger: 'hover focus' });

            return;
        }
    })

}
handleServicosExtraFields();

/**
 * Valida as datas dos campos de check in e check out para
 * confirmar se elas estão respeitando a ordem do dia e hora de cada uma
 * 
 * @param {JQuery} trigger Input que disparou a função 
 * @returns 
 */
function validateRangeDate (trigger) {
    const checkin_input = $('input[name="checkin"]');
    const checkin_hour_input = $('input[name="timecheckin"]');
    const checkout_input = $('input[name="checkout"]');
    const checkout_hour_input = $('input[name="timecheckout"]');

    const checkin_iso = `${checkin_input.val()}T00:00`;
    const checkout_iso = `${checkout_input.val()}T00:00`;

    let checkin_time = new Date(checkin_iso);
    let checkout_time = new Date(checkout_iso);
    const checkin_hour_time = convertHoursAndMinutesToInt(checkin_hour_input.val());
    const checkout_hour_time = convertHoursAndMinutesToInt(checkout_hour_input.val());

    if (checkin_input.val() && checkout_input.val()) {
        if (checkin_time > checkout_time) {
            new swal('Data inválida!', 'A data de check in deve ser menor ou igual a data de check out.', 'warning');

            trigger ? trigger.val(trigger.data('old')) : checkin_input.val(checkin_input.data('old'));
            initializeTooltip(trigger ?? checkin_input, 'Data inválida.');
            trigger ? trigger.addClass('is-invalid') : checkin_input.addClass('is-invalid');

            return false;
        }
    }

    if (checkin_input.val() && checkin_hour_input.val() && checkout_input.val() && checkout_hour_input.val()) {
        if (checkin_time.getTime() === checkout_time.getTime() && checkin_hour_time >= checkout_hour_time) {
            new swal('Horário inválido!', 'O horário de check in deve ser menor do que o horário de check out.', 'warning');
            trigger.val() ? trigger.val(trigger.data('old')) : checkin_hour_input.val(checkin_hour_input.data('old'));
            initializeTooltip(trigger ?? checkin_hour_input, 'Horário inválido.');
            trigger.addClass('is-invalid') ? trigger.addClass('is-invalid') : checkin_hour_input.addClass('is-invalid');

            return false;
        }
   }
}

/**
 * Manipula o campo de quarto para decidir se ele será desabilitado 
 * ou não conforme a data de check in e check out
 * 
 * @returns 
 */
function handleQuartoInput () {
    const is_index_view = window.location.pathname === '/hotel/hoteis';

    if (is_index_view) return;

    const parent_modal = $('#modal_novo_agendamento_hotel');
    const quarto_input = $('select[name="quarto_id"]');

    const checkin_input = $('input[name="checkin"]');
    const checkin_hour_input = $('input[name="timecheckin"]');
    const checkout_input = $('input[name="checkout"]');
    const checkout_hour_input = $('input[name="timecheckout"]');

    if (!checkin_input.val() || !checkin_hour_input.val() || !checkout_input.val() || !checkout_hour_input.val()) {
        quarto_input.prop('disabled', true);
        initializeTooltip(quarto_input, 'Determine o período da reserva primeiro.', { trigger: 'hover focus' });

        return;
    }

    const checkin_time = `${checkin_input.val()} ${checkin_hour_input.val()}`;
    const checkout_time = `${checkout_input.val()} ${checkout_hour_input.val()}`;

    quarto_input.select2(select2Defaults({
        placeholder: 'Selecione um quarto',
        dropdownParent: parent_modal.length > 0 ? parent_modal : null,
        ajax: {
            cache: true,
            url: path_url + 'api/quartos/',
            dataType: 'json',
            data: function (params) {
                return {
                    pesquisa: params.term,
                    empresa_id: $('#empresa_id').val(),
                    checkin: checkin_time,
                    checkout: checkout_time,
                };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(function (quarto) {
                        return {
                            id: quarto.id,
                            text: quarto.nome,
                        };
                    })
                };
            }
        }

    })).on('select2:select', function (e) {
        const quarto = e.params.data;
        const capacidade_quarto_input = $('input[name="capacidade_quarto"]');

        capacidade_quarto_input.val(quarto.capacidade);
    });

    quarto_input.prop('disabled', false);

    const selected_quarto = $('input[name="id_quarto"]').val();
    const label_quarto = $('input[name="nome_quarto"]').val();

    if (selected_quarto && label_quarto) {
        const option = new Option(label_quarto, selected_quarto, true, true);
        $('select[name="quarto_id"]').append(option).trigger('change');
    }

}
handleQuartoInput();

/**
 * Verifica se o quarto selecionado para a reserva está livre
 * no periódo selecionado
 * 
 * @returns 
 */
function validateQuartoIsFree () {
    const quarto_id_input = $('select[name="quarto_id"]');

    const checkin_input = $('input[name="checkin"]');
    const checkin_hour_input = $('input[name="timecheckin"]');
    const checkout_input = $('input[name="checkout"]');
    const checkout_hour_input = $('input[name="timecheckout"]');

    if (
        !quarto_id_input.val() ||
        !checkin_input.val() ||
        !checkin_hour_input.val() ||
        !checkout_input.val() ||
        !checkout_hour_input.val()
    ) return;

    const checkin_time = `${checkin_input.val()} ${checkin_hour_input.val()}`;
    const checkout_time = `${checkout_input.val()} ${checkout_hour_input.val()}`;

    let is_free = true;

    let reserva_id = window.location.pathname.split('/edit')[0].split('/').pop();

    reserva_id = (Number.isInteger(parseInt(reserva_id))) ? parseInt(reserva_id) : null;

    reserva_id = $('#modal_novo_agendamento_hotel').length ? $('#modal_novo_agendamento_hotel').data('agendamento-id') : reserva_id;

    $.ajax({
        url: path_url + 'api/quartos/check-quarto-free',
        method: 'GET',
        data: {
            quarto_id: quarto_id_input.val(),
            empresa_id: $('#empresa_id').val(),
            checkin: checkin_time,
            checkout: checkout_time,
            reserva_id
        },
        async: false,
        success: function (response) {
            if (!response.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Quarto indisponível',
                    html: `
                        O quarto
                        <b>${quarto_id_input.find('option:selected').text()}</b>
                        está ocupado no período escolhido. <br>

                        <small>Selecione outro quarto ou altere o período da reserva.</small>
                    `
                });

                initializeTooltip(quarto_id_input.next('.select2'), 'Quarto indisponível no período escolhido.');
                quarto_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-valid');
                quarto_id_input.next('.select2').find('.select2-selection--single').addClass('select2-invalid');

                is_free = false;
            } else {
                quarto_id_input.tooltip('dispose');
                quarto_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-invalid');
                quarto_id_input.next('.select2').find('.select2-selection--single').addClass('select2-valid');
            }
        }
    })

    return is_free;
}

/**
    * Valida o periodo da reserva e o tempo de execução do serviço de reserva
    * e retorna se ele consegue ser executado no periodo escolhido
    * 
    * @param {JQuery} trigger campo que disparou a validação
    * 
    * @returns {boolean} resposta da validação
*/
function validateServicoReservaRangeDate (trigger) {
    const checkin = $('input[name="checkin"]');
    const checkin_hour_input = $('input[name="timecheckin"]');
    const checkout = $('input[name="checkout"]');
    const checkout_hour_input = $('input[name="timecheckout"]');

    if (!checkin.val() || !checkin_hour_input.val() || !checkout.val() || !checkout_hour_input.val()) return;

    const servico_reserva_input_line = $('.table-hotel-servico-reserva tbody .dynamic-form');
    const servico_input = servico_reserva_input_line.find('select[name="servico_ids[]"]');

    if (!servico_input.val()) return;

    const tempo_execucao_servico = parseInt(servico_reserva_input_line.find('input[name="tempo_execucao"]').val());

    const date_checkin_iso = `${checkin.val()}T${checkin_hour_input.val()}`;
    const data_checkout_iso = `${checkout.val()}T${checkout_hour_input.val()}`;

    let date_checkin_time = new Date(date_checkin_iso);
    let data_checkout_time = new Date(data_checkout_iso);

    let diff_ms = data_checkout_time - date_checkin_time;
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

    initializeTooltip(trigger, 'O tempo de execução do serviço de reserva deve ser maior ou igual ao tempo de permanência da reserva.', { trigger: 'hover focus' });
    trigger.addClass('is-invalid');
    trigger.val(trigger.data('old') ?? null);

    return false;
}

/**
 * Verifica se a data de check in e o tempo de execução do serviço
 * respeita ao tempo de permanência da reserva
 * 
 * Ex: reserva de 3 dias, e o serviço tem 2 dias, 
 * então o serviço deve ser executado nos 1° dia, caso contrário ele será barrado
 * 
 * @param {JQuery} element Linha de campos do serviço extra
 * @returns 
 */
function validateServicoExtraRangeDate (element) {
    const servico_input = element.closest('tr').find('select[name="servico_ids[]"]');
    const tempo_execucao_inputs = $('.table-hotel-servicos input[name="tempo_execucao"]');
    const data_servico_input = element.closest('tr').find('input[name="servico_datas[]"]');
    const hora_servico_input = element.closest('tr').find('input[name="servico_horas[]"]');
    const valor_servico_input = element.closest('tr').find('input[name="servico_valor[]"]');

    if (!servico_input.val() || !tempo_execucao_inputs.val()) return false;

    const data_checkin_input = $('input[name="checkin"]');
    const data_checkin_time_input = $('input[name="timecheckin"]');
    const data_checkout_input = $('input[name="checkout"]');
    const data_checkout_time_input = $('input[name="timecheckout"]');

    const data_checkin_iso = `${data_checkin_input.val()}T${data_checkin_time_input.val()}`;
    const data_checkout_iso = `${data_checkout_input.val()}T${data_checkout_time_input.val()}`;

    let data_checkin_time = new Date(data_checkin_iso);
    let data_checkout_time = new Date(data_checkout_iso);

    let diff_ms = data_checkout_time - data_checkin_time;
    const diff_time = Math.floor(diff_ms / 1000 / 60);

    let total_tempo_execucao = 0;
    tempo_execucao_inputs.each(function () {
        total_tempo_execucao += parseInt($(this).val());
    });
    
    if (diff_time < total_tempo_execucao) {
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
        initializeTooltip(data_servico_input, 'Determine a data de início primeiro.', { trigger: 'hover focus' });
        data_servico_input.addClass('is-invalid');

        return false;
    };

    if (new Date(data_servico_input.val()).getTime() < new Date(data_checkin_input.val()).getTime()) {
        Swal.fire({
            title: 'Data de início inválida.',
            html: `
                A data de início do serviço extra:
                    <b>
                        ${servico_input.find('option:selected').text().split(' R$')[0]}
                    </b>  
                deve ser maior ou igual ao check in da reserva.
            `,
            icon: 'warning'
        })

        initializeTooltip(data_servico_input, 'Data de início menor do que a data de check in.');
        data_servico_input.addClass('is-invalid');
        data_servico_input.val(data_servico_input.data('old'));

        return false;
    }

    if (data_servico_input.val() == data_checkin_input.val() && hora_servico_input.val()) {
        if (hora_servico_input.val() < data_checkin_time_input.val()) {
            Swal.fire({
                title: 'Horário de início inválido.',
                html: `
                    O horário de início do serviço extra:
                        <b>
                            ${servico_input.find('option:selected').text().split(' R$')[0]}
                        </b>  
                    deve ser maior ou igual ao horário de check in da reserva.
                `,
                icon: 'warning'
            })

            initializeTooltip(data_servico_input, 'Horário de início menor do que o horário de check in.');
            hora_servico_input.addClass('is-invalid');
            hora_servico_input.val(hora_servico_input.data('old'));

            return false;
        }
    }

    if (new Date(data_servico_input.val()).getTime() > new Date(data_checkout_input.val()).getTime()) {
        Swal.fire({
            title: 'Data de início inválida.',
            html: `
                A data de início do serviço extra:
                    <b>
                        ${servico_input.find('option:selected').text().split(' R$')[0]}
                    </b>  
                deve ser menor ou igual ao check out da reserva.
            `,
            icon: 'warning'
        })

        initializeTooltip(data_servico_input, 'Data de início maior do que a data de check out.');
        data_servico_input.addClass('is-invalid');
        data_servico_input.val(data_servico_input.data('old'));

        return false;
    }

    if (!hora_servico_input.val()) {
        initializeTooltip(hora_servico_input, 'Determine o horário de início primeiro.', { trigger: 'hover focus' });
        hora_servico_input.addClass('is-invalid');

        return false;
    };

    const data_servico_iso = `${data_servico_input.val()}T${hora_servico_input.val()}`;
    let data_servico_time = new Date(data_servico_iso);

    let data_servico_checkout_diff_ms = data_checkout_time - data_servico_time;
    const data_servico_checkout_diff_time = Math.floor(data_servico_checkout_diff_ms / 1000 / 60);

    if (data_servico_checkout_diff_time < total_tempo_execucao) {
        Swal.fire({
            title: 'Data de início inválida.',
            html: `
                O tempo de execução do serviço extra:
                    <b>
                        ${servico_input.find('option:selected').text().split(' R$')[0]}
                    </b>  
                excede o check out da reserva.
            `,
            icon: 'warning'
        })

        if (data_servico_checkout_diff_time - total_tempo_execucao <= -1440) {
            data_servico_input.val(data_servico_input.data('old'));
            initializeTooltip(data_servico_input, 'A data de início excede o check out da reserva.');
            data_servico_input.addClass('is-invalid');
        } else {
            hora_servico_input.val(hora_servico_input.data('old'));
            initializeTooltip(hora_servico_input, 'O horário de início excede o check out da reserva.');
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
 * Valida se os serviços extras estão respeitando o tempo de
 * permanência da reserva mesmo quando ele é mudado pelo usuário
 * 
 * @param {JQuery} trigger Input que disparou a função
*/
function validateServicosExtrasDatesWhenRangeDateChange (trigger) {
    const data_checkin_input = $('input[name="checkin"]');
    const data_checkin_time_input = $('input[name="timecheckin"]');
    const data_checkout_input = $('input[name="checkout"]');
    const data_checkout_time_input = $('input[name="timecheckout"]');

    const data_checkin_iso = `${data_checkin_input.val()}T${data_checkin_time_input.val()}`;
    const data_checkout_iso = `${data_checkout_input.val()}T${data_checkout_time_input.val()}`;

    const tempo_execucao_fields = $('input[name="tempo_execucao"]');
    let total_tempo_execucao = 0;

    tempo_execucao_fields.each(function () {
        total_tempo_execucao += parseInt($(this).val());
    })

    let data_checkin_time = new Date(data_checkin_iso);
    let data_checkout_time = new Date(data_checkout_iso);

    let diff_ms = data_checkout_time - data_checkin_time;
    const diff_time = Math.floor(diff_ms / 1000 / 60);

    let inputs_list = [];
    $('.table-hotel-servicos input[name="tempo_execucao"]').each(function () {
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
                initializeTooltip(data_servico_input, 'A data de check in excede o check out da reserva.');
                data_servico_input.addClass('is-invalid');

                return;
            } else {
                initializeTooltip(hora_servico_input, 'O horário de check in excede o check out da reserva.');
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
            title: 'Data de check in inválida.',
            html: `
                O${plural} seguinte${plural} serviço${plural} extra${plural}:
                    <b>
                        ${inputs_list.join(', ')}
                    </b>  
                excede${inputs_list.length > 1 ? 'm' : ''} o check out da reserva.
            `,
            icon: 'warning'
        })

        return false;
    }

    return true;
}

$(`input[name="checkin"],
    input[name="timecheckin"],
    input[name="checkout"],
    input[name="timecheckout"],
    input[name="servico_datas[]"],
    input[name="servico_horas[]"]
`).on('focus', function () {
    $(this).data('old', $(this).val());
});

$(document).on('blur', 'input[name="checkin"]', function () {
    validateRangeDate($(this));
    handleServicoReservaFields();
    handleServicosExtraFields();
    setTimeout(() => {
        validateServicoReservaRangeDate($(this));
    }, 200)
    validateServicosExtrasDatesWhenRangeDateChange($(this));
    handleQuartoInput();
    validateQuartoIsFree();
});
$(document).on('blur', 'input[name="timecheckin"]', function () {
    validateRangeDate($(this));
    handleServicoReservaFields();
    handleServicosExtraFields();
    setTimeout(() => {
        validateServicoReservaRangeDate($(this));
    }, 200)
    validateServicosExtrasDatesWhenRangeDateChange($(this));
    handleQuartoInput();
    validateQuartoIsFree();
});
$(document).on('blur', 'input[name="checkout"]', function () {
    validateRangeDate($(this));
    handleServicosExtraFields();
    validateServicosExtrasDatesWhenRangeDateChange($(this));
    handleQuartoInput();
    validateQuartoIsFree();
});
$(document).on('blur', 'input[name="timecheckout"]', function () {
    validateRangeDate($(this));
    handleServicosExtraFields();
    setTimeout(() => {
        validateServicoReservaRangeDate($(this));
    }, 200)
    validateServicosExtrasDatesWhenRangeDateChange($(this));
    handleQuartoInput()
    validateQuartoIsFree();
});
$(document).on('select2:close select2:unselect select2:select', 'select[name="quarto_id"]', function () {
    validateQuartoIsFree();
});

/**
 * Prepara os inputs de data e hora para serem validados 
 * quando um serviço extra for alterado ou adicionado
*/
function setDateValidationForServicosExtrasSelect2 () {
    $('.table-hotel-servicos select[name="servico_ids[]"]').each(function () {
        const input = $(this);

        input.on('select2:select', function () {
            setTimeout(() => {
                validateServicoExtraRangeDate($(this));
            }, 100);
        });
    })
    $('.table-hotel-servicos input[name="servico_datas[]"]').each(function () {
        const input = $(this);

        input.on('blur', function () {
            validateServicoExtraRangeDate($(this));
        });
    })
    $('.table-hotel-servicos input[name="servico_horas[]"]').each(function () {
        const input = $(this);

        input.on('blur', function () {
            validateServicoExtraRangeDate($(this));
        });
    })
}
setDateValidationForServicosExtrasSelect2();

/**
 * Valida se os campos de serviços extras estão realmente preenchidos
 * quando um mesmo for aplicado
 * 
 * @returns {boolean}
 */
function validateExtraServicos () {
    let is_valid = true;
    $('table.table-hotel-servicos tbody .dynamic-form select[name="servico_ids[]"]').each(function () {
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

            is_valid = validateServicoExtraRangeDate($(this));
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
function validateProduto () {
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
function validateFrete() {
    const frete_input = $('select[name="servico_ids[]"][data-is-frete="true"]').first();

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

$('#btn-store').on('click', function (e) {
    e.preventDefault();

    if (addClassRequired('#main-form') && validateExtraServicos() && validateProduto() && validateQuartoIsFree() && validateFrete()) {
        $('#main-form').trigger('submit');
    }
});

$('#submit_novo_agendamento_hotel').on('click', function (e) {
    e.preventDefault();

    if (!addClassRequired('#form-novo-agendamento-hotel', true) || !validateExtraServicos() || !validateProduto() || !validateQuartoIsFree() || !validateFrete()) return;

    let formDataArray = $('#form-novo-agendamento-hotel').serializeArray();

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

    formData = formData + '&empresa_id=' + encodeURIComponent($('#empresa_id').val());    

    $.ajax({
        url: 'api/hoteis/store-hotel',
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

});
