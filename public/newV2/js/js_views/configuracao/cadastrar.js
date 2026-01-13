$(document).ready(function() {
    // Inicializa os selects com Select2
    function initSelect2() {
        $('#pais').select2({
            placeholder: 'Selecione o país',
            language: 'pt-BR',
            allowClear: true
        });
        $('#fuso_horario').select2({
            placeholder: 'Selecione o fuso horário',
            language: 'pt-BR',
            allowClear: true
        });
    }
    initSelect2();

    // Controle exclusivo para fuso horário:
    // Se "Utilizar Fuso-Horário padrão da Filial" for marcado, desabilita "Fuso-Horário do Usuário" e vice-versa.
    function controlaCheckboxFuso() {
        $('#flag_fuso_horario_padrao').on('change', function() {
            if (this.checked) {
                $('#flag_fuso_horario_usuario').prop('checked', false).prop('disabled', true);
            } else {
                $('#flag_fuso_horario_usuario').prop('disabled', false);
            }
        });
        $('#flag_fuso_horario_usuario').on('change', function() {
            if (this.checked) {
                $('#flag_fuso_horario_padrao').prop('checked', false).prop('disabled', true);
            } else {
                $('#flag_fuso_horario_padrao').prop('disabled', false);
            }
        });
    }
    controlaCheckboxFuso();

    // Função para carregar os fusos horários via API e atualizar o select
    function loadTimezones(countryCode, preselected = '') {
        fetch(`/api/timezones/${countryCode}`)
            .then(response => response.json())
            .then(data => {
                var $timezoneSelect = $('#fuso_horario');
                $timezoneSelect.empty().append('<option value="">Selecione um Fuso Horário</option>');
                if (data.timezones && Array.isArray(data.timezones)) {
                    data.timezones.forEach(function(timezone) {
                        $timezoneSelect.append($('<option>', { value: timezone, text: timezone }));
                    });
                    if (preselected) {
                        $timezoneSelect.val(preselected);
                    }
                } else {
                    console.error('Resposta inesperada:', data);
                }
                $timezoneSelect.trigger('change');
            })
            .catch(error => console.error('Erro ao buscar fusos horários:', error));
    }

    // Ao mudar o país, carrega os fusos correspondentes
    $('#pais').on('change', function() {
        var countryCode = $(this).val();
        if (!countryCode) {
            $('#fuso_horario').empty().append('<option value="">Selecione um Fuso Horário</option>').trigger('change');
            return;
        }
        loadTimezones(countryCode);
    });

    // Se já houver um país selecionado na inicialização, carrega os fusos
    var initialCountry = $('#pais').val();
    if (initialCountry) {
        var preselectedTimezone = $('#fuso_horario').data('selected') || '';
        loadTimezones(initialCountry, preselectedTimezone);
    }
});
