$(document).on('click', '#btn-store-funcionario', function () {
    const form = $('#form-colaborador')[0];
    const formData = new FormData(form);
    console.log($('#form-colaborador').length); // Deve retornar 1

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    formData.append('empresa_id', $('#empresa_id').val());

    $.ajax({
        url: '/api/funcionarios',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.success) {
                $('#modal_colaborador').modal('hide');

                const funcionario = res.funcionario;
                const $select = $('#inp-funcionario_id');
                const option = new Option(funcionario.nome, funcionario.id, true, true);
                $select.append(option).trigger('change');

                new swal('Sucesso', 'Colaborador cadastrado com sucesso!', 'success');
            }
        },
        error: function (xhr) {
            console.error(xhr.responseJSON);
            new swal('Erro', 'Não foi possível cadastrar o colaborador.', 'error');
        }
    });
});

const modalColaboradorEl = document.getElementById('modal_colaborador');
const novoModal = new bootstrap.Modal(modalColaboradorEl, {
    backdrop: false,
    keyboard: false
});

$('#modal_colaborador').on('hidden.bs.modal', function () {
    $('.second-backdrop').remove();
    $('#event-modal .modal-overlay-blur').remove();
});

$('#modal_colaborador').on('shown.bs.modal', function () {
    if ($('.second-backdrop').length === 0) {
        $('body').append('<div class="modal-backdrop fade show second-backdrop"></div>');
    }
    if ($('#event-modal .modal-overlay-blur').length === 0) {
        $('#event-modal').append('<div class="modal-overlay-blur"></div>');
    }

    $('.second-backdrop').off('click').on('click', function () {
        novoModal.hide();
    });
    $("#inp-cidade_id").select2({
        minimumInputLength: 2,
        language: "pt-BR",
        placeholder: "Digite para buscar a cidade",
        width: "100%",
        dropdownParent: $("#modal_colaborador"),
        ajax: {
            cache: true,
            url: path_url + "api/buscaCidades",
            dataType: "json",
            data: function (params) {
                console.clear();
                var query = {
                    pesquisa: params.term,
                };
                return query;
            },
            processResults: function (response) {
                return {
                    results: response.map(v => ({
                        id: v.id,
                        text: v.info
                    }))
                };
            }
        },
    });
});
