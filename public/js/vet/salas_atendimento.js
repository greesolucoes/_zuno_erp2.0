function validateCapacidadeField() {
    const capacidade_field = $('input[name="capacidade"]');

    if (!capacidade_field.val()) {
        return true;
    }

    if (parseInt(capacidade_field.val()) < 1) {
        Swal.fire('Capacidade inválida', 'A capacidade da sala deve ser maior do que zero.', 'error');
        
        capacidade_field.addClass('is-invalid');
        initializeTooltip(capacidade_field, 'A capacidade da sala deve ser maior do que zero.');

        return false;
    }

    return true;
}

$('#btn-store').on('click', function (event) {
    event.preventDefault();

    if (!addClassRequired('#form-salas-atendimento') || !validateCapacidadeField()) return;

    $('#form-salas-atendimento').submit();
})