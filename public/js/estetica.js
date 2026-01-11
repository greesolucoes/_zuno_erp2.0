$('#inp-entrada').on('blur', function () {
    validateRangeDate();
    checkIfServicoIsFree();
})
$('#inp-hora_entrada').on('blur', function () {
    validateRangeDate();
    checkIfServicoIsFree();
})
$('#inp-saida').on('blur', function () {
    validateRangeDate();
    checkIfServicoIsFree();
})
$('#inp-hora_saida').on('blur', function () {
    validateRangeDate();
    checkIfServicoIsFree();
})
$('#inp-colaborador_id').on('select2:select', function () {
    checkIfServicoIsFree();
})

function validateRangeDate () {
    const data_entrada = $('#inp-entrada');
    const hora_entrada = $('#inp-hora_entrada');
    const data_saida = $('#inp-saida');
    const hora_saida = $('#inp-hora_saida');

    const data_entrada_iso = `${data_entrada.val()}T00:00`;
    const data_saida_iso = `${data_saida.val()}T00:00`;
    
    let data_entrada_time = new Date(data_entrada_iso);
    let data_saida_time = new Date(data_saida_iso);
    const hora_entrada_time = convertHoursAndMinutesToInt(hora_entrada.val());
    const hora_saida_time = convertHoursAndMinutesToInt(hora_saida.val());

    let now = new Date();
    now.setHours(0, 0, 0, 0);

    if (data_entrada.val()) {
        if (data_entrada_time < now) {
            new swal('Data inválida!', 'A data de entrada deve ser maior ou igual a data atual.', 'warning');
            data_entrada.val('');
            initializeTooltip(data_entrada, 'Data inválida.');
            data_entrada.addClass('is-invalid');
            return;
        }
    }

    if (data_saida.val()) {
        if (data_saida_time < now) {
            new swal('Data inválida!', 'A data de saída deve ser maior ou igual a data atual.', 'warning');
            data_saida.val('');
            initializeTooltip(data_saida, 'Data inválida.');
            data_saida.addClass('is-invalid');
            return;
        }
    }

    if (data_entrada.val() && data_saida.val()) {
        if (data_entrada_time > data_saida_time) {
            new swal('Data inválida!', 'A data de entrada deve ser menor ou igual a data de saída.', 'warning');
            data_entrada.val('');
            initializeTooltip(data_entrada, 'Data inválida.');
            data_entrada.addClass('is-invalid');
            return;
        }
    }
    if (data_entrada.val() && hora_entrada.val() && data_saida.val() && hora_saida.val()) {
        if (data_entrada_time.getTime() === data_saida_time.getTime() && hora_entrada_time >= hora_saida_time) {
            new swal('Horário inválido!', 'O horário de entrada deve ser menor ao horário de saída.', 'warning');
            hora_entrada.val('');
            initializeTooltip(hora_entrada, 'Horário inválido.');
            hora_entrada.addClass('is-invalid');

            return;
        }
    }
}

function checkIfServicoIsFree () {
    const colaborador_input = $('#inp-colaborador_id');

    const data_entrada_input = $('#inp-entrada');
    const hora_entrada_input = $('#inp-hora_entrada');
    const data_saida_input = $('#inp-saida');
    const hora_saida_input = $('#inp-hora_saida');

    if (!colaborador_input.val() || !data_entrada_input.val() || !hora_entrada_input.val() || !data_saida_input.val() || !hora_saida_input.val()) return;

    let id = null
    if (window.location.pathname.includes('/edit')) {
        id = window.location.pathname.split('/edit')[1].split('/')[1];
    }

    const full_data_entrada = convertDateToDb(data_entrada_input.val() + ' ' + hora_entrada_input.val());
    const full_data_saida = convertDateToDb(data_saida_input.val() + ' ' + hora_saida_input.val());

    $.ajax({
        url: path_url + 'api/esteticas/check-servico-free',
        method: 'GET',
        data: {
            empresa_id: $('#empresa_id').val(),
            colaborador_id: colaborador_input.val(),
            entrada: full_data_entrada,
            saida: full_data_saida,
            id
        }
    }).then(function (response) {
        if (response.success) {
            data_entrada_input.removeClass('is-invalid');
            data_entrada_input.tooltip('dispose');
            data_saida_input.removeClass('is-invalid');
            data_saida_input.tooltip('dispose');

            return;
        } 

        const locale_options = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };

        const toDate = (iso) => new Date(iso.replace(/\.(\d{3})\d*Z$/, '.$1Z'));

        const entrada = toDate(response.servico.entrada); 
        const saida = toDate(response.servico.saida);

        const formated_entrada = entrada.toLocaleString('pt-BR', locale_options);
        const formated_saida = saida.toLocaleString('pt-BR', locale_options);

        Swal.fire({
            icon: 'warning',
            title: 'Horário indisponível!',
            html: `
            <div class="text-center">
                <div class="my-1">
                    Já existe um agendamento para esse colaborador neste horário.
                </div>

                <br>
                <b>Agendamento encontrado: </b> 
                ${formated_entrada} -
                 ${formated_saida}
            </div>
            `,
        })

        data_entrada_input.addClass('is-invalid');
        data_saida_input.addClass('is-invalid');

        initializeTooltip(data_entrada_input, 'Data indisponível.');
        initializeTooltip(data_saida_input, 'Data indisponível.');
    })
} 

$('#btn-store').on('click', function (e) {
    e.preventDefault();

    if (addClassRequired('#main-form')) {
        $('#main-form').trigger('submit');
    }
});