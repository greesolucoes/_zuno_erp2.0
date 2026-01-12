function servicosParseInt(value) {
    const clean = String(value || '').replace(/[^0-9]/g, '');
    if (!clean) return 0;
    const number = parseInt(clean, 10);
    return Number.isFinite(number) ? number : 0;
}

function selectDivServico(ref) {
    $('button').removeClass('link-active');

    if (ref === 'tempo') {
        $('.div-tempo-servico').removeClass('d-none');
        $('.div-identificacao-servico').addClass('d-none');
        $('.btn-tempo-servico').addClass('link-active');
    } else {
        $('.div-tempo-servico').addClass('d-none');
        $('.div-identificacao-servico').removeClass('d-none');
        $('.btn-identificacao-servico').addClass('link-active');
    }
}

function servicosSyncTempoServico() {
    const diasInput = document.querySelector('input[name="dias"]');
    const horasInput = document.querySelector('input[name="horas"]');
    const minutosInput = document.querySelector('input[name="minutos"]');
    const tempoServicoHidden = document.querySelector('input[name="tempo_servico"]');

    if (!diasInput || !horasInput || !minutosInput || !tempoServicoHidden) return;

    const dias = servicosParseInt(diasInput.value);
    const horas = servicosParseInt(horasInput.value);
    const minutos = servicosParseInt(minutosInput.value);

    const totalMin = dias * 1440 + horas * 60 + minutos;
    tempoServicoHidden.value = totalMin > 0 ? String(totalMin) : '';
}

function servicosNormalizeTempoExecucao() {
    const diasInput = document.querySelector('input[name="dias"]');
    const horasInput = document.querySelector('input[name="horas"]');
    const minutosInput = document.querySelector('input[name="minutos"]');

    if (!diasInput || !horasInput || !minutosInput) return;

    let dias = servicosParseInt(diasInput.value);
    let horas = servicosParseInt(horasInput.value);
    let minutos = servicosParseInt(minutosInput.value);

    if (minutos >= 60) {
        horas += Math.floor(minutos / 60);
        minutos = minutos % 60;
    }

    if (horas >= 24) {
        dias += Math.floor(horas / 24);
        horas = horas % 24;
    }

    diasInput.value = dias > 0 ? String(dias) : '';
    horasInput.value = horas > 0 ? String(horas) : '';
    minutosInput.value = minutos > 0 ? String(minutos) : '';

    servicosSyncTempoServico();
}

function servicosOnlyDigitsAndLimit(event) {
    const input = event.target;
    if (!input || input.tagName !== 'INPUT') return;

    const maxLength = input.getAttribute('maxlength');
    let clean = String(input.value || '').replace(/[^0-9]/g, '');
    if (maxLength) clean = clean.slice(0, parseInt(maxLength, 10));
    input.value = clean;
}

function servicosValidateTempoExecucao() {
    const dias = servicosParseInt(document.querySelector('input[name="dias"]')?.value);
    const horas = servicosParseInt(document.querySelector('input[name="horas"]')?.value);
    const minutos = servicosParseInt(document.querySelector('input[name="minutos"]')?.value);

    if (dias <= 0 && horas <= 0 && minutos <= 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Determine qual será o tempo de execução.',
            });
        }
        return false;
    }

    return true;
}

$(document).ready(function () {
    const inputs = $('input[name="dias"], input[name="horas"], input[name="minutos"]');
    if (!inputs.length) return;

    selectDivServico('identificacao');

    inputs.on('input', function (e) {
        servicosOnlyDigitsAndLimit(e);
        servicosSyncTempoServico();
    });

    inputs.on('change blur', function () {
        servicosNormalizeTempoExecucao();
    });

    servicosNormalizeTempoExecucao();

    const form = $('#main-form');
    if (form.length) {
        form.on('submit', function (e) {
            servicosNormalizeTempoExecucao();
            if (!servicosValidateTempoExecucao()) {
                e.preventDefault();
                return false;
            }
        });
    }
});
