/**
 * Seleciona (ou deseleciona) uma linha da tabela ao clicar nela.
 * Se for a primeira linha clicada, pergunta se deseja selecionar todas.
 */
function seleciona() {
    let primeiraSelecao = false; // Para evitar múltiplos alerts

    $('.table-exibe tbody').on('click', 'tr', function () {
        let $this       = $(this);
        let todasLinhas = $('.table-exibe tbody tr');

        console.log('Linha clicada:', $this);

        // Se for a primeira vez e a linha ainda não estiver selecionada
        if (!primeiraSelecao && !$this.hasClass('selected')) {
            primeiraSelecao = true;
            console.log('Primeira seleção não marcada. Exibindo alerta para selecionar todas.');

            swal({
                title: 'Selecionar todas?',
                text: 'Deseja selecionar todas as linhas da tabela?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText:  'Sim',
                cancelButtonText:   'Não'
            }).then(function () {
                console.log('Usuário clicou em Sim');
                todasLinhas.addClass('selected');
            }).catch(function () {
                console.log('Usuário clicou em Não');
                $this.toggleClass('selected');
            });
        } else {
            console.log('Toggle normal na linha clicada.');
            $this.toggleClass('selected');
        }
    });
}


/**************************************
 * Funções de Inicialização e Setup
 **************************************/
function criarSelect() {
    $('.dia_envio').data('init', '');
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
    }
});

/**
 * Configura os selects com a classe \"select-normal\" e os datepickers dos campos \"data_inicial\" e \"data_final\"
 * com a validação do intervalo entre as datas.
 */
function criaCostumizacoes() {
    // Inicializa os selects \"select-normal\" com select2Simple para a maioria
    $('select.select-normal').not('#colaboradorId').select2Simple();

    // Inicializa o select de colaboradores com uma configuração customizada para exibir a barra de pesquisa
    $('#colaboradorId').select2({
        placeholder: 'Buscar colaboradores...',
        allowClear: true,
        minimumInputLength: 0, // A barra de pesquisa aparece imediatamente
        width: '100%',
        language: {
            searching: function () {
                return 'Buscando...';
            }
        }
    });

    // Configura o datepicker para o campo \"data_inicial\" com validação
    $('#data_inicial').datetimepicker({
        locale: _lang,
        format: 'DD/MM/YYYY',
        useCurrent: false,
        widgetPositioning: { vertical: 'top', horizontal: 'left' }
    }).on('dp.change', function () {
        if (!is_empty($('#data_inicial').val()) && !is_empty($('#data_final').val())) {
            validaDate(this);
        }
    });

    // Configura o datepicker para o campo \"data_final\" com validação
    $('#data_final').datetimepicker({
        locale: _lang,
        format: 'DD/MM/YYYY',
        useCurrent: false,
        widgetPositioning: { vertical: 'top', horizontal: 'left' }
    }).on('dp.change', function () {
        if (!is_empty($('#data_inicial').val()) && !is_empty($('#data_final').val())) {
            validaDate(this);
        }
    });

    // Define a data máxima permitida (data atual) para ambos os datepickers
    var currentDate = moment();
    $('#data_inicial').data('DateTimePicker').maxDate(currentDate);
    $('#data_final').data('DateTimePicker').maxDate(currentDate);
}

/**************************************
 * Funções Auxiliares
 **************************************/
function validaDate(element) {
    let result = diffDays(formatDate($('#data_inicial').val()), formatDate($('#data_final').val()));
    if (result) {
        if (result > 31) {
            swal(l['listarMrp'], 'O intervalo entre as datas não pode ser superior a 31 dias.', 'error');
            $(element).val('');
        }
    } else {
        swal(l['dataInvalida'], l['dataInicialMaiorQueADataFinal'], 'error');
        $(element).val('');
    }
}

/**************************************
 * Filtragem via AJAX
 **************************************/
function filtroColaboradores() {
    var colaboradorId = $('#colaboradorId').val();
    var departmentId  = $('#departmentId').val();
    var cargoId       = $('#cargoId').val();

    // Obtém a URL de filtragem a partir do atributo data-url-filtrar da tabela
    var url = $('#table-itens').data('url-filtrar');

    $.ajax({
        url: url,
        type: 'GET',
        data: {
            colaboradorId: colaboradorId,
            departmentId:  departmentId,
            cargoId:       cargoId
        },
        beforeSend: function () {
            // Opcional: exibir indicador de loading
        },
        success: function (response) {
            $('#table-itens tbody').html(response.data);
        },
        error: function (xhr) {
            console.error('Erro ao filtrar', xhr);
        }
    });
}

/**************************************
 * Salvar Fechamento de Folha
 **************************************/
function salvarFechamento() {
    // Obtém a rota dinamicamente do botão (data-url-salvar)
    let urlSalvar = $('.btn-salvar').data('url-salvar');

    // Pega os valores dos campos
    let titulo       = $('[name=\"titulo\"]').val();        // name=\"titulo\"
    let data_inicial = $('[name=\"data_inicial\"]').val();  // name=\"data_inicial\"
    let data_final   = $('[name=\"data_final\"]').val();    // name=\"data_final\"

    // Cria array com os usuários selecionados
    let usuariosSelecionados = [];
    $('.table-exibe tbody tr.selected').each(function () {
        // ID está na 2ª coluna (td:eq(1))
        let id = $(this).find('td:eq(1)').text().trim();
        usuariosSelecionados.push({ id: parseInt(id) });
    });

    if (usuariosSelecionados.length === 0) {
        swal('Atenção', 'Nenhum usuário foi selecionado.', 'warning');
        return;
    }

    $.ajax({
        url: urlSalvar,
        type: 'POST',
        data: {
            titulo:            titulo,
            data_inicial:      data_inicial,
            data_final:        data_final,
            usuariosSelecionados: usuariosSelecionados
        },
        success: function (response) {
            swal('Sucesso', response.message, 'success');
            console.log('Resposta do servidor:', response);
        },
        error: function (xhr) {
            console.error('Erro ao salvar folhas:', xhr);

            // Caso venha o array de notifications do backend
            if (xhr.responseJSON && xhr.responseJSON.notifications) {
                xhr.responseJSON.notifications.forEach(function (n) {
                    swal(n.title || 'Aviso', n.message || '', n.type || 'info');
                });
                return;
            }

            // Fallback genérico
            let errorMsg = 'Erro ao tentar salvar o fechamento de folha.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            swal('Erro!', errorMsg, 'error');
        }
    });
}


/**************************************
 * Inicialização no Document Ready
 **************************************/
$(document).ready(function () {
    criarSelect();
    criaCostumizacoes();
    seleciona();

    // Inicializa os selects \"select-normal\" que não foram customizados individualmente
    $('select.select-normal').not('#colaboradorId').select2Simple();

    // Liga os eventos de mudança nos filtros para disparar o AJAX
    $('#colaboradorId, #departmentId, #cargoId').on('change', function () {
        filtroColaboradores();
    });

    // Botão para salvar
    $('.btn-salvar').on('click', function (e) {
        e.preventDefault();
        salvarFechamento();
    });
});
