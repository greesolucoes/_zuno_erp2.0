// Configuração global do AJAX para incluir o token CSRF
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Inicializa o select de usuários usando Select2 com método GET
function initSelectUsuarios() {
    $('#geral-usuarios').select2({
        placeholder: $('#geral-usuarios').data('placeholder'),
        allowClear: true,
        ajax: {
            url: $('#geral-usuarios').data('url'),
            type: 'GET', // Força o método GET
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: data.map(function(usuario) {
                        return { id: usuario.id, text: usuario.full_name };
                    })
                };
            },
            cache: true
        }
    });
}

function criaObjetos() {
    // Inicializa os selects que usam AJAX para outros campos (se houver)
    $("select.select_ajax").not("#geral-usuarios").select2Ajax();
    $("select.select_ajax").not("#geral-usuarios").data('init', '');

    // Inicializa o select de usuários com método GET
    initSelectUsuarios();

    // Configuração de clique nos labels customizados
    $('label.label-custom').on("click", function(){
        $("label.label-custom-selected").removeClass("label-custom-selected");
        $(this).addClass("label-custom-selected");
    });

    // Configuração para a ação "Selecionar todos os usuários" (caso aplicável)
    $(".mml-div-ativa_todos_usuarios").off("click");
    $(".mml-div-ativa_todos_usuarios").on("click", function () {
        if ($("#mml-todas-usuarios").is(':checked')) {
            $("#mml-geral-usuarios-div").addClass("ocultar");
        } else {
            $("#mml-geral-usuarios-div").removeClass("ocultar");
        }
    });

    // Configuração para o botão de envio de manutenção em lote
    $(".mml-btn_enviar").off("click");
    $(".mml-btn_enviar").on("click", function () {
        let url = $(".data_monitoramento").data("url_manutencao_lote");
        let dados = null;
        if (is_empty(url, 1)) {
            return;
        }

        swal({
            title: l["desejaContinuar?"],
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: l["sim!"],
            cancelButtonText: l["cancelar!"]
        }).then(function () {
            toggleLoading();

            dados = {
                dataDe: $("#mml-data-inicial").val(),
                dataAte: $("#mml-data-final").val(),
                tipoAcao: $('input[type="radio"][name="mml-opcoes"]:checked').val(),
                todasUsuarios: ($("#mml-todas-usuarios").is(':checked') ? 1 : 0),
                usuarios: $("#mml-usuarios").val(),
            };

            if (!is_empty(dados.dataDe, 1)) {
                dados.dataDe = moment(dados.dataDe, $('.data_monitoramento').data('formato_date')).format('YYYY-MM-DD');
            } else {
                dados.dataDe = null;
            }
            if (!is_empty(dados.dataAte, 1)) {
                dados.dataAte = moment(dados.dataAte, $('.data_monitoramento').data('formato_date')).format('YYYY-MM-DD');
            } else {
                dados.dataAte = null;
            }

            // Realiza a requisição AJAX via método POST para manutenção em lote
            ajaxRequest(true, url, 'POST', 'text', dados, function (ret) {
                try {
                    ret = JSON.parse(ret);
                    swal(
                        ret['titulo'],
                        ret['text'],
                        ret['class']
                    ).catch(swal.noop);

                    atualizaMonitoramentoConciliacao();
                    toggleLoading();
                } catch (err) {
                    consoleProduction(err);
                    swal(
                        l["erro!"],
                        l["tempoDeRespostaDoServidorEsgotado!"],
                        "error"
                    ).catch(swal.noop);
                    forceToggleLoading(0);
                }
            });
        }).catch(swal.noop);
    });
}

function pararAtualizaMonitoramentoConciliacao() {
    if (!is_empty(auxiliaresMonitoramento['ajax'], 1))
        auxiliaresMonitoramento['ajax'].abort();
    auxiliaresMonitoramento['ajax'] = null;

    if (!is_empty(auxiliaresMonitoramento['timeout'], 1))
        clearTimeout(auxiliaresMonitoramento['timeout']);
    auxiliaresMonitoramento['timeout'] = null;
}

function atualizaMonitoramentoConciliacao(naoPararAtualiza) {
    if (is_empty(naoPararAtualiza, 1))
        pararAtualizaMonitoramentoConciliacao();

    // Inicia o loading para prevenir cliques indevidos
    toggleLoading();

    let select_usuarios = $("select#geral-usuarios");
    let competencia = $('#dataCompetencia').val(); // Captura o valor do período selecionado
    console.log("Competência selecionada:", competencia);

    // Requisição AJAX para atualizar o monitoramento conforme o período e os usuários selecionados
    auxiliaresMonitoramento['ajax'] = ajaxRequest(true, $('.data_monitoramento').data('url_monitoramento_ajax'), 'POST', 'text', {
        'competencia': competencia,
        'usuarios': toBase64($(select_usuarios).val().join(","))
    }, function (ret) {
        if (!auxiliaresMonitoramento['travar'])
            $('div.diagnostico div.indexAjax').html(ret);

        let txtDia = "";
        $('div.diagnostico div.indexAjax table.tabela-monitoramento thead tr th.dia').each(function (indexTableTR) {
            // Atualiza cada célula da tabela de monitoramento
            $('div.diagnostico div.indexAjax table.tabela-monitoramento tbody tr td.dia-' + (indexTableTR + 1)).each(function () {
                const isNotaInserida = $(this).data('is_nota_inserida');
                txtDia = "";

                txtDia += `
                    <div class="${isOldLayout ? 'centraliza' : 'dia-content'}
                        ${!is_empty_numeric(isNotaInserida) ? 'dia_has_nota' : 'dia_no_has_nota'}">
                `;

                if (!isOldLayout) txtDia += "<span class='bar-status-dia'></span> ";
                if (!isOldLayout) txtDia += "<span class='dia-calendario'>";
                if (!is_empty_numeric(isNotaInserida) && isOldLayout) {
                    txtDia += "<span class='fa fa-circle fa-sm circle-nota-inserida'></span> ";
                }
                txtDia += (indexTableTR + 1).toString().padStart(2, '0');
                if (!isOldLayout) txtDia += "</span>";

                if (!isOldLayout) {
                    const hasVendasPDV = $(this).data('has_vendas_pdv');
                    const hasCardService = $(this).data('has_cardservice');
                    txtDia += "<div class='icons-dia'>";
                    if (!$(this).hasClass('branco')) {
                        txtDia += !is_empty_numeric(hasCardService)
                            ? '<span data-icon="ion:card" class="iconify"></span>'
                            : '';
                        txtDia += (!is_empty_numeric(hasVendasPDV))
                            ? (hasVendasPDV === 1)
                                ? '<span data-icon="uil:dollar-sign-alt" class="iconify"></span>'
                                : '<span data-icon="tabler:currency-dollar-off" class="iconify"></span>'
                            : '';
                    }
                    txtDia += "</div>";
                }
                txtDia += "</div>";

                $(this).html(txtDia);
                txtDia = null;
            });
        });

        toggleLoading();
        auxiliaresMonitoramento['timeout'] = setTimeout(function () {
            atualizaMonitoramentoConciliacao(true);
        }, 30000);
    });
}

// Inicializa o datetimepicker para a seleção de períodos (competência)
$("[data-picker='date-month-right-now']").datetimepicker({
    locale: _lang,
    viewMode: 'months',
    format: $('.data_monitoramento').data('formato_date_time_picker'),
    useCurrent: true,
    widgetPositioning: {
        vertical: 'bottom',
        horizontal: 'right'
    }
}).on('dp.change', function(e) {
    atualizaMonitoramentoConciliacao();
});

// Eventos para atualização do monitoramento quando há alterações no select de usuários
$('#geral-usuarios').on('select2:select', function (e) {
    atualizaMonitoramentoConciliacao();
});
$(document).on('click', '#geral-usuarios-div .select2-selection__clear', function() {
    setTimeout(function(){
        atualizaMonitoramentoConciliacao();
    }, 200);
});
$("#geral-usuarios").on("select2:unselecting", function(e) {
    setTimeout(function(){
        atualizaMonitoramentoConciliacao();
    }, 200);
});

// Inicia a atualização e configura os objetos ao carregar a página
atualizaMonitoramentoConciliacao();
criaObjetos();
