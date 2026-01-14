$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

$(document).ready(function () {
  const modalId = '#modal_novo_agendamento_veterinario';
  const formId = '#form-novo-agendamento-veterinario';

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

  function setPacienteSelect2() {
    const parent_modal = $(modalId);
    const $select = parent_modal.find('select[name="paciente_id"]');

    if (!$select.length) return;

    if ($select.data('select2')) {
      $select.off('select2:select');
      $select.select2('destroy');
    }

    $select
      .select2(
        select2Defaults({
          placeholder: 'Digite para buscar o animal (pet)',
          dropdownParent: parent_modal.length > 0 ? parent_modal : null,
          ajax: {
            cache: true,
            url: path_url + 'api/animais/',
            dataType: 'json',
            data: function (params) {
              return {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
              };
            },
            processResults: function (response) {
              const results = [];
              $.each(response.data || [], function (i, v) {
                results.push({
                  id: v.id,
                  text: `${v.nome} - Tutor: ${v.cliente?.razao_social ?? ''}`.trim(),
                  value: v.id,
                  cliente_id: v.cliente_id,
                  cliente: v.cliente || null,
                });
              });
              return { results };
            },
          },
        })
      )
      .on('select2:select', function (e) {
        const data = e.params.data || {};
        const cliente = data.cliente || {};

        const tutorId = data.cliente_id || cliente.id || null;
        const tutorNome = cliente.razao_social || cliente.nome || null;
        const tutorContato = cliente.telefone || cliente.fone || cliente.celular || null;
        const tutorEmail = cliente.email || null;

        const container = $(modalId);
        container.find('input[name="tutor_id"]').val(tutorId);
        container.find('input[name="tutor_nome"]').val(tutorNome);
        container.find('input[name="contato_tutor"]').val(tutorContato);
        container.find('input[name="email_tutor"]').val(tutorEmail);
      });
  }

  function resetVetModal() {
    const container = $(modalId);

    container.find('input, textarea, select').each(function () {
      const $el = $(this);

      if ($el.is('input[type="date"]')) return;
      if ($el.attr('name') === 'tipo_atendimento') return;

      $el.val(null);
    });

    container.find('textarea[name="motivo_visita"]').val('');

    container.find('select.select2').each(function () {
      try {
        $(this).val(null).trigger('change');
      } catch (e) {
        // ignore
      }
    });

    container.find('input, textarea, select').removeClass('is-valid is-invalid');
  }

  $(modalId).on('show.bs.modal', function () {
    setPacienteSelect2();

    $(this)
      .find('.select2')
      .each(function () {
        if (!$(this).data('select2')) {
          $(this).select2(
            select2Defaults({
              dropdownParent: $(modalId),
              placeholder: $(this).data('placeholder') || 'Selecione uma opção',
              minimumInputLength: 0,
            })
          );
        }
      });
  });

  $(`
    ${modalId} .btn-close,
    ${modalId} .btn-close-modal
  `).on('click', function () {
    resetVetModal();
  });

  $('#submit_novo_agendamento_veterinario').on('click', function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (!addClassRequired(formId, true)) return;

    const formDataArray = $(formId).serializeArray();
    const filteredFormData = formDataArray.filter(function (field) {
      return String(field.value || '').trim() !== '';
    });

    const formDataObj = {};
    filteredFormData.forEach(function (field) {
      if (field.name.endsWith('[]')) {
        const key = field.name.replace('[]', '');
        if (!formDataObj[key]) {
          formDataObj[key] = [];
        }
        formDataObj[key].push(field.value);
      } else {
        formDataObj[field.name] = field.value;
      }
    });

    formDataObj.empresa_id = $('#empresa_id').val();

    $.ajax({
      url: path_url + 'api/vet/atendimentos/store',
      method: 'POST',
      data: $.param(formDataObj),
      success: function (response) {
        if (response && response.success === true) {
          Swal.fire({
            icon: 'success',
            title: 'Atendimento agendado com sucesso!',
          }).then(() => {
            location.reload();
          });
          return;
        }

        Swal.fire({
          icon: 'error',
          title: 'Erro ao agendar atendimento',
          text: response?.message ?? '',
        });
      },
      error: function (xhr) {
        let msg = 'Ocorreu um erro desconhecido ao agendar o atendimento.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }

        Swal.fire({
          icon: 'error',
          title: 'Erro ao agendar atendimento!',
          text: msg,
        });
      },
    });
  });
});

