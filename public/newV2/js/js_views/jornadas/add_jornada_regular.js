/**
 * add_jornada_regular.js
 * Adaptado para a nova estrutura da tabela com os seguintes campos:
 * - DSR? (toggle Sim/Não) → enviado como "horarios[index][is_dsr]"
 * - Dia da semana (dropdown) → "horarios[index][dia]"
 * - 1ª Entrada → "horarios[index][entrada1]"
 * - 1ª Saída → "horarios[index][saida1]"
 * - 2ª Entrada → "horarios[index][entrada2]"
 * - 2ª Saída → "horarios[index][saida2]"
 * - Intervalo Principal (dropdown com opções: Manual, Livre, Automático)
 *    - Manual: exibe dois campos de tempo para preenchimento (Início e Fim) → "horarios[index][intervalo_inicio]" e "horarios[index][intervalo_fim]"
 *    - Livre: não subtrai nenhum intervalo
 *    - Automático: calcula a diferença entre a 1ª Saída e a 2ª Entrada
 * - Sub total (calculado com base na duração trabalhada) e campo hidden "horas_dia" (valor numérico)
 * - Ação (remover linha)
 *
 * Os names foram ajustados para que o array validado seja "horarios" com índices numéricos para cada linha.
 */

/* --- Variável com os valores padrão para cada escala ---
 * Nota: Os valores do campo intervalo serão convertidos:
 * "intervalo_automatico" → "1"
 * "almoco_livre" → "0"
 * "manual" → "2"
 */
var defaultSchedules = {
    "fixa": [
        { diaSemana: "monday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "tuesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "wednesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "thursday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "friday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "saturday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "sunday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 }
    ],
    "5x2": [
        { diaSemana: "monday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "tuesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "wednesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "thursday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "friday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "saturday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "sunday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 }
    ],
    "6x1": [
        { diaSemana: "monday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "tuesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "wednesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "thursday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "friday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "saturday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "sunday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 }
    ],
    "12x36": [
        { diaSemana: "monday", entrada1: "07:00", saida1: "19:00", entrada2: "", saida2: "", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "tuesday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "wednesday", entrada1: "07:00", saida1: "19:00", entrada2: "", saida2: "", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "thursday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "friday", entrada1: "07:00", saida1: "19:00", entrada2: "", saida2: "", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "saturday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "sunday", entrada1: "07:00", saida1: "19:00", entrada2: "", saida2: "", intervalo: "intervalo_automatico", isContado: 0 }
    ],
    "24x48": [
        { diaSemana: "monday", entrada1: "00:00", saida1: "23:59", entrada2: "", saida2: "", intervalo: "0", isContado: 0 },
        { diaSemana: "tuesday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "wednesday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "thursday", entrada1: "00:00", saida1: "23:59", entrada2: "", saida2: "", intervalo: "0", isContado: 0 },
        { diaSemana: "friday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "saturday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "sunday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 }
    ],
    "4x2": [
        { diaSemana: "monday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "tuesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "wednesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "thursday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "friday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "saturday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "sunday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 }
    ],
    "5x1": [
        { diaSemana: "monday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "tuesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "wednesday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "thursday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "friday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 },
        { diaSemana: "saturday", entrada1: "", saida1: "", entrada2: "", saida2: "", intervalo: "", isContado: 1 },
        { diaSemana: "sunday", entrada1: "08:00", saida1: "12:00", entrada2: "13:00", saida2: "17:00", intervalo: "intervalo_automatico", isContado: 0 }
    ]
};

/* --- Função para preencher a tabela com os valores padrão da escala selecionada --- */
function populateTable(scaleType) {
    if (!defaultSchedules.hasOwnProperty(scaleType)) return;
    var schedule = defaultSchedules[scaleType];
    var tbody = $("table#conteudoTable tbody");
    tbody.empty(); // Limpa as linhas atuais

    for (var i = 0; i < schedule.length; i++) {
        var rowData = schedule[i];
        // Para dias DSR (isContado == 1) forçamos o valor do intervalo para "0"
        var intervaloVal;
        if (rowData.isContado == 1) {
            intervaloVal = "0";
        } else {
            intervaloVal = rowData.intervalo;
            if (intervaloVal === "intervalo_automatico") {
                intervaloVal = "1";
            } else if (intervaloVal === "almoco_livre") {
                intervaloVal = "0";
            } else if (intervaloVal === "manual") {
                intervaloVal = "2";
            }
        }
        var row = '<tr>' +
            // Coluna DSR? (enviado como is_dsr)
            '<td>' +
            '<div class="separador-geral">' +
            '<div class="pretty p-default p-curve p-toggle">' +
            '<input type="number" value="'+ rowData.isContado +'" class="is-contado" name="horarios['+ i +'][is_dsr]" />' +
            '<input type="checkbox" class="isContadoCheck" ' + (rowData.isContado == 1 ? 'checked="checked"' : '') + ' />' +
            '<div class="state p-success p-on"><label>Sim</label></div>' +
            '<div class="state p-danger p-off"><label>Não</label></div>' +
            '</div>' +
            '</div>' +
            '</td>' +
            // Coluna Dia da semana
            '<td>' +
            '<select class="form-control select select_diaSemana" name="horarios['+ i +'][dia]" style="width: 100%;" data-placeholder="Dia da semana">' +
            '<option value=""></option>' +
            '<option value="monday" ' + (rowData.diaSemana === "monday" ? "selected" : "") + '>Segunda-feira</option>' +
            '<option value="tuesday" ' + (rowData.diaSemana === "tuesday" ? "selected" : "") + '>Terça-feira</option>' +
            '<option value="wednesday" ' + (rowData.diaSemana === "wednesday" ? "selected" : "") + '>Quarta-feira</option>' +
            '<option value="thursday" ' + (rowData.diaSemana === "thursday" ? "selected" : "") + '>Quinta-feira</option>' +
            '<option value="friday" ' + (rowData.diaSemana === "friday" ? "selected" : "") + '>Sexta-feira</option>' +
            '<option value="saturday" ' + (rowData.diaSemana === "saturday" ? "selected" : "") + '>Sábado</option>' +
            '<option value="sunday" ' + (rowData.diaSemana === "sunday" ? "selected" : "") + '>Domingo</option>' +
            '</select>' +
            '</td>' +
            // Coluna 1ª Entrada
            '<td>' +
            '<div class="input-group date">' +
            '<span class="input-group-text"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>' +
            '<input data-picker="time" name="horarios['+ i +'][entrada1]" class="form-control" placeholder="00:00" value="'+ rowData.entrada1 +'" />' +
            '</div>' +
            '</td>' +
            // Coluna 1ª Saída
            '<td>' +
            '<div class="input-group date">' +
            '<span class="input-group-text"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>' +
            '<input data-picker="time" name="horarios['+ i +'][saida1]" class="form-control" placeholder="00:00" value="'+ rowData.saida1 +'" />' +
            '</div>' +
            '</td>' +
            // Coluna 2ª Entrada
            '<td>' +
            '<div class="input-group date">' +
            '<span class="input-group-text"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>' +
            '<input data-picker="time" name="horarios['+ i +'][entrada2]" class="form-control" placeholder="00:00" value="'+ rowData.entrada2 +'" />' +
            '</div>' +
            '</td>' +
            // Coluna 2ª Saída
            '<td>' +
            '<div class="input-group date">' +
            '<span class="input-group-text"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>' +
            '<input data-picker="time" name="horarios['+ i +'][saida2]" class="form-control" placeholder="00:00" value="'+ rowData.saida2 +'" />' +
            '</div>' +
            '</td>' +
            // Coluna Intervalo Principal (com container para campos manuais)
            '<td>' +
            '<div class="intervalo-container">' +
            '<select class="form-control select select_intervalo" name="horarios['+ i +'][intervalo_tipo]" style="width: 100%;" data-placeholder="Intervalo Principal">' +
            '<option value=""></option>' +
            '<option value="2" ' + (rowData.intervalo === "manual" ? "selected" : "") + '>Manual</option>' +
            '<option value="0" ' + ((rowData.intervalo === "almoco_livre" || rowData.intervalo === "0" || rowData.isContado == 1) ? "selected" : "") + '>Livre</option>' +
            '<option value="1" ' + (rowData.intervalo === "intervalo_automatico" ? "selected" : "") + '>Automático</option>' +
            '</select>' +
            '<div class="manual-intervalo-fields" style="display:none; margin-top:5px;">' +
            '<div class="input-group date" style="margin-bottom:3px;">' +
            '<span class="input-group-text"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>' +
            '<input data-picker="time" name="horarios['+ i +'][intervalo_inicio]" class="form-control" placeholder="Início" />' +
            '</div>' +
            '<div class="input-group date">' +
            '<span class="input-group-text"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>' +
            '<input data-picker="time" name="horarios['+ i +'][intervalo_fim]" class="form-control" placeholder="Fim" />' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</td>' +
            // Coluna Sub total e campo hidden para horas_dia
            '<td>' +
            '<input type="text" class="form-control subTotal" placeholder="Sub Total" readonly="readonly"/>' +
            '<input type="hidden" class="horas_dia" name="horarios['+ i +'][horas_dia]" value="0" />' +
            '</td>' +
            // Coluna Ação
            '<td>' +
            '<button type="button" class="button-form danger-button removeItens" title="Retirar item da solicitação!">' +
            '<i class="fa fa-times"></i>' +
            '</button>' +
            '</td>' +
            '</tr>';
        tbody.append(row);

        // Se for DSR, força o select de intervalo para "0"
        if (rowData.isContado == 1) {
            tbody.find("tr").last().find("select.select_intervalo").val("0").trigger("change");
        }
    }
    updateHorariosIndex();
    criaSelects();
    addButtonsRemoveItens();
    addSomaCampos();
    somaCampos();
    $('[data-picker="time"]').datetimepicker({ format: 'HH:mm' });
}

/* --- Função para atualizar os índices dos inputs "horarios" --- */
function updateHorariosIndex(){
    $("table#conteudoTable tbody tr").each(function(index){
        $(this).find('[name^="horarios"]').each(function(){
            var name = $(this).attr("name");
            var newName = name.replace(/horarios\[\d*\]/, 'horarios['+ index +']');
            $(this).attr("name", newName);
        });
    });
}

/* --- Evento para atualizar o container do intervalo ---
 * Se a opção for "manual" (valor "2"), exibe os campos para Início e Fim.
 */
$(document).on('change', 'select.select_intervalo', function() {
    var val = $(this).val();
    var container = $(this).closest('.intervalo-container').find('.manual-intervalo-fields');
    if(val === '2'){
        container.show();
        container.find('[data-picker="time"]').datetimepicker({ format: 'HH:mm' });
    } else {
        container.hide();
        container.find('input').val('');
    }
    somaCampos();
});

/* --- Atualiza o campo "DSR?" e força o select de intervalo para "0" se DSR ---
 * Quando o checkbox de DSR é alterado, atualiza o campo hidden e, se marcado (DSR = 1),
 * define automaticamente o select de intervalo para "0" (Livre).
 */
$(document).on('change', '.isContadoCheck', function() {
    let isDsr = $(this).is(':checked') ? 1 : 0;
    let hiddenInput = $(this).closest('.pretty').find('input.is-contado');
    hiddenInput.val(isDsr);
    if(isDsr === 1) {
        let $row = $(this).closest('tr');
        $row.find('select[name*="[intervalo_tipo]"]').val("0").trigger('change');
    }
});

/* --- Inicializa os selects --- */
function criaSelects(){
    $(".select_diaSemana").select2Simple();
    $(".select_intervalo").select2Simple();
    $(".select_departamento").select2Simple();
}

/**
 * Converte string de tempo ("hh:mm" ou "hh:mm:ss") para segundos
 */
function parseTime(t) {
    if (!t) return 0;
    var parts = t.split(':');
    if (parts.length === 2) {
        return parseInt(parts[0], 10) * 3600 + parseInt(parts[1], 10) * 60;
    } else if (parts.length === 3) {
        return parseInt(parts[0], 10) * 3600 + parseInt(parts[1], 10) * 60 + parseInt(parts[2], 10);
    }
    return 0;
}

/**
 * Formata segundos para string "hh:mm"
 */
function formatSeconds(sec) {
    var hours = Math.floor(sec / 3600);
    var minutes = Math.floor((sec % 3600) / 60);
    return ("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2);
}

/**
 * Calcula o subtotal para uma linha e atualiza o campo horas_dia (valor numérico)
 */
function calcSubTotalForRow($row) {
    var entrada1  = $row.find('input[name*="[entrada1]"]').val();
    var saida1    = $row.find('input[name*="[saida1]"]').val();
    var entrada2  = $row.find('input[name*="[entrada2]"]').val();
    var saida2    = $row.find('input[name*="[saida2]"]').val();
    var intervaloOption = $row.find('select[name*="[intervalo_tipo]"]').val();
    var breakSeconds = 0;

    if(intervaloOption === "2") {
        var manualInicio = $row.find('input[name*="[intervalo_inicio]"]').val();
        var manualFim = $row.find('input[name*="[intervalo_fim]"]').val();
        breakSeconds = parseTime(manualFim) - parseTime(manualInicio);
    } else if(intervaloOption === "0") {
        breakSeconds = 0;
    } else if(intervaloOption === "1") {
        var automaticBreak = parseTime(entrada2) - parseTime(saida1);
        breakSeconds = (automaticBreak > 0 ? automaticBreak : 0);
    } else if(intervaloOption) {
        breakSeconds = parseInt(intervaloOption, 10) * 60;
    } else {
        breakSeconds = 0;
    }

    var duration1 = (entrada1 && saida1) ? parseTime(saida1) - parseTime(entrada1) : 0;
    var duration2 = (entrada2 && saida2) ? parseTime(saida2) - parseTime(entrada2) : 0;
    var totalSec = duration1 + duration2 - breakSeconds;
    if (totalSec < 0) totalSec = 0;
    $row.find('input.subTotal').val(formatSeconds(totalSec));
    $row.find('input.horas_dia').val((totalSec / 3600).toFixed(2));
    return totalSec;
}

/**
 * Calcula os subtotais de cada linha e o total geral da tabela
 */
function somaCampos(){
    var total = 0;
    $("table#conteudoTable tbody tr").each(function (){
        total += calcSubTotalForRow($(this));
    });
    $("table#conteudoTable input.totalGeral").val(formatSeconds(total));
}

/**
 * Replica a data de entrega para todos os itens (se necessário)
 */
function replicaDataNecessaria(){
    if ($('input#dataNecessariaReplica').length === 0) {
        return;
    }

    var isFocus = true;
    var valorAnterior = '';
    $('input#dataNecessariaReplica').on('focus', function(){
        isFocus = true;
    }).on('blur', function(e){
        e.preventDefault();
        if (isFocus && valorAnterior !== $(this).val()){
            var $tabela = $('div#conteudo table#conteudoTable tbody tr');
            isFocus = false;
            swal({
                title: "Replicar data para os itens?",
                text: "Tem certeza de que deseja replicar a data de recebimento para todos os itens desta solicitação? As datas necessárias já digitadas serão perdidas!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim!',
                cancelButtonText: "Cancelar!"
            }).then(function () {
                $tabela.find('input.dataNecessaria').val($('input#dataNecessariaReplica').val());
                valorAnterior = $('input#dataNecessariaReplica').val();
            }, function () {
                $('input#dataNecessariaReplica').val('');
                valorAnterior = '';
            }).catch(swal.noop);
        }
    });
}

/**
 * Conta os caracteres do campo de observações e atualiza o contador
 */
function contaCharObservacoes(){
    if ($('#observacoes').length === 0 || $('#numChars').length === 0) {
        return;
    }

    var maxLenObs = 254;
    function updateCount(){
        var currentValue = $('#observacoes').val() || '';
        var len = currentValue.length;
        var remaining = maxLenObs - len;
        $('#numChars').text(remaining + ' caracteres restantes.');
    }
    $('#observacoes').on('keyup', updateCount);
    updateCount();
}

/**
 * Configura o botão para remover itens da tabela
 */
function addButtonsRemoveItens(){
    $('table#conteudoTable button.removeItens').off('click').on('click', function(){
        var $row = $(this).closest('tr');
        $row.fadeOut(270, function(){
            $row.remove();
            updateHorariosIndex();
            somaCampos();
        });
    });
}

/**
 * Associa eventos nos inputs de tempo e no select de intervalo para recalcular os totais.
 * Utiliza binding delegado com "change keyup blur".
 */
function addSomaCampos(){
    $(document).off('change keyup blur', 'input[data-picker="time"]').on('change keyup blur', 'input[data-picker="time"]', somaCampos);
    $(document).off('change', 'select[name*="[intervalo_tipo]"]').on('change', 'select[name*="[intervalo_tipo]"]', somaCampos);
}

/**
 * Handler para o botão de adicionar nova linha, com trava de 7 dias
 */
$('table#conteudoTable button.addItens').off('click').on('click', function(){
    if ($(this).prop('disabled')) {
        return;
    }
    var $tbody = $('table#conteudoTable tbody');
    var rowCount = $tbody.find('tr').length;
    if (rowCount >= 7) {
        alert("O limite de 7 dias foi atingido. Não é possível adicionar mais dias.");
        return;
    }
    var $firstRow = $tbody.find('tr').first();
    var modelo = $firstRow.html();
    $tbody.append('<tr>' + modelo + '</tr>');
    var $newRow = $tbody.find('tr').last();

    $newRow.find('button.removeItens').prop('disabled', false);
    $newRow.find('.select').select2Reset();
    $newRow.find('input[type="text"]').val('');
    $newRow.find('select.select_diaSemana').val('').trigger('change');
    $newRow.find('select.select_intervalo').val('').trigger('change');
    $newRow.find('.isContadoCheck').prop('checked', false);
    $newRow.find('.is-contado').val(0);

    updateHorariosIndex();
    addButtonsRemoveItens();
    addSomaCampos();
    criaSelects();
    $('[data-picker="time"]').datetimepicker({ format: 'HH:mm' });
});

/**
 * Evento que dispara o preenchimento automático da tabela quando o usuário altera o tipo de escala.
 */
$(document).on('change', '.select_departamento', function() {
    var scaleType = $(this).val();
    populateTable(scaleType);
});

/**
 * Ao submeter o formulário, percorre os inputs de tempo e força a formatação "hh:mm:ss"
 * e, além disso, para cada linha marcada como DSR (is_dsr = 1), e para os que estiverem vazios, força o select de intervalo para "0".
 */
$('form.cadastro').on('submit', function(){
    $(this).find('tr').each(function(){
        var isDsr = $(this).find('input.is-contado').val();
        if (isDsr == "1") {
            $(this).find('select[name*="[intervalo_tipo]"]').val("0").trigger("change");
        } else {
            // Se o select estiver vazio (null ou ""), força o valor "0"
            var valIntervalo = $(this).find('select[name*="[intervalo_tipo]"]').val();
            if(valIntervalo === "" || valIntervalo === null) {
                $(this).find('select[name*="[intervalo_tipo]"]').val("0").trigger("change");
            }
        }
    });
    $(this).find('input[data-picker="time"]').each(function(){
        var timeVal = $(this).val();
        if (timeVal && timeVal.length === 5) {
            $(this).val(timeVal + ':00');
        }
    });
});

/**
 * Inicialização quando o documento está pronto.
 */
$(document).ready(function(){
    addButtonsRemoveItens();
    contaCharObservacoes();
    replicaDataNecessaria();
    addSomaCampos();
    criaSelects();
    somaCampos();

    if (typeof initDatePickers === 'function') {
        initDatePickers();
    }
});
