// Função para formatar uma data/hora ISO no padrão "dd/mm/yyyy HH:mm:ss"
function formatDateTime(isoString) {
    if (!isoString) return '-';
    var date = new Date(isoString);
    if (isNaN(date.getTime())) return isoString;
    return date.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'medium' });
}

// Função para formatar um horário no padrão "HH:mm"
function formatTime(timeStr) {
    if (!timeStr || timeStr === 'null') return '-';
    var parts = timeStr.split(':');
    if (parts.length >= 2) {
        return parts[0] + ':' + parts[1];
    }
    return timeStr;
}

// Função simples para exibir/ocultar o loading no elemento
function toggleLoadingOnDivSmall($element, show) {
    if (show) {
        $element.html('<div class="loading" style="text-align:center; padding:20px;">Carregando...</div>');
    } else {
        $element.find('.loading').remove();
    }
}

// Desvincula os eventos "shown" dos modais
$('#modalErrors').unbind('shown.bs.modal');
$('#modalErrorsEstoqueNegativo').unbind('shown.bs.modal');
$('#modal_especificacoes_dia').unbind('shown.bs.modal');

// Quando qualquer um dos modais for mostrado, trava a atualização
$(document).on('shown.bs.modal', '#modalErrors, #modal_especificacoes_dia, #modalErrorsEstoqueNegativo', function () {
    console.log('Modal aberto => parando atualização do monitoramento');
    auxiliaresMonitoramento['travar'] = true;
    pararAtualizaMonitoramentoConciliacao();
});

// Eventos de fechamento dos modais para retomar a atualização
$('#modalErrors').unbind('hidden.bs.modal');
$('#modalErrors').on("hidden.bs.modal", function () {
    console.log('Fechou modalErrors => retoma atualização');
    auxiliaresMonitoramento['travar'] = false;
    atualizaMonitoramentoConciliacao();
});

$('#modalErrorsEstoqueNegativo').unbind('hidden.bs.modal');
$('#modalErrorsEstoqueNegativo').on("hidden.bs.modal", function () {
    console.log('Fechou modalErrorsEstoqueNegativo => retoma atualização');
    auxiliaresMonitoramento['travar'] = false;
    atualizaMonitoramentoConciliacao();
});

$('#modal_especificacoes_dia').unbind('hidden.bs.modal');
$('#modal_especificacoes_dia').on('hidden.bs.modal', function () {
    console.log('Fechou modal_especificacoes_dia => limpa e retoma atualização');
    $(this).find('.data_modal_especificacoes_dia').data('mon_date', '');
    $(this).find('.data_modal_especificacoes_dia').data('user_id', '');
    $(this).find('.modal-body').html('');
    $(this).find('.modal-header .titulo_especificacoes').html('');
    auxiliaresMonitoramento['travar'] = false;
    atualizaMonitoramentoConciliacao();
});

/**
 * Função funcoesModalEspecificacoesDia
 * Define todas as funções do modal de especificações do dia,
 * aplicando o padrão de layout e com o loading corrigido.
 */
function funcoesModalEspecificacoesDia() {
    var ultimoAjaxModalEspecificacoesDia = null;

    function ativaTriggerClickDia() {
        $(".td-data-monitoramento").off('click').on('click', function (e) {
            // Pega a data no formato "YYYY-MM-DD" do atributo data-mon_date
            var dataMonitoramento = $(this).data('mon_date'); // Ex: "2025-03-05"
            var partes = dataMonitoramento.split('-');        // ["2025", "03", "05"]
            var month = partes[0] + '-' + partes[1];            // "2025-03"
            var day = partes[2];                                // "05"

            // Recupera o ID do usuário (do elemento <tr data-user_id="...">)
            var user_id = $(this).closest('tr').data('user_id');

            // Obtém a URL do atributo data-url da tabela; se não existir, usa a rota fixa
            var url = $(this).closest('table').data('url');
            if (typeof url === 'undefined') {
                url = '/monitoriamento/detalhes-dia';
            }

            console.log('Clique no dia => Enviando dados:', {
                user_id: user_id,
                day: day,
                month: month,
                url: url
            });

            // Armazena os dados no modal para uso posterior
            $('#modal_especificacoes_dia .data_modal_especificacoes_dia')
                .data('mon_date', dataMonitoramento)
                .data('user_id', user_id);

            // Abre o modal, se ainda não estiver aberto
            if (!($('#modal_especificacoes_dia').data('bs.modal') || {})._isShown) {
                $('#modal_especificacoes_dia').modal('show');
            }

            // Exibe o loading enquanto aguarda os dados
            toggleLoadingOnDivSmall($('#modal_especificacoes_dia .modal-body'), true);

            // Faz a requisição AJAX para buscar os detalhes do dia
            ultimoAjaxModalEspecificacoesDia = $.ajax({
                url: url,
                method: 'GET',    // ou 'POST', conforme sua rota
                dataType: 'json', // Espera receber JSON
                data: {
                    user_id: user_id,
                    day: day,
                    month: month,
                    page: 1
                },
                beforeSend: function() {
                    console.log('Enviando requisição para detalhesDia...', {
                        user_id: user_id,
                        day: day,
                        month: month
                    });
                },
                success: function(ret) {
                    console.log('Sucesso => Resposta do servidor detalhesDia', ret);

                    if (ret.error) {
                        $("#modal_especificacoes_dia .modal-body").html(
                            `<p class="text-danger">Erro: ${ret.error}</p>`
                        );
                        return;
                    }

                    // Atualiza o cabeçalho do modal com data e status
                    $('#modal_especificacoes_dia .modal-header .titulo_especificacoes').html(`
                        <span class="titulo_filial">Data: ${ret.formattedDate}</span><br>
                        <span class="titulo_data">Status: ${ret.status}</span>
                    `);

                    // Monta o conteúdo do modal
                    var bodyHtml = '';

                    // Exibe os dados de attendance, se disponíveis
                    if (ret.attendance) {
                        bodyHtml += `
                            <p><strong>Registro do dia:</strong></p>
                            <ul>
                                <li>Check In: ${formatTime(ret.attendance.check_in)}</li>
                                <li>Break In: ${formatTime(ret.attendance.break_in)}</li>
                                <li>Break Out: ${formatTime(ret.attendance.break_out)}</li>
                                <li>Check Out: ${formatTime(ret.attendance.check_out)}</li>
                            </ul>
                            <div class="separador-view my-4"></div>
                        `;
                    } else {
                        bodyHtml += `<p><strong>Nenhum registro encontrado para este dia.</strong></p>`;
                    }

                    // Exibe os logs, se houver
                    if (ret.logs && ret.logs.length > 0) {
                        bodyHtml += `<p><strong>Logs:</strong></p><ul>`;
                        ret.logs.forEach(function(log) {
                            bodyHtml += `<li>${formatDateTime(log.operation_timestamp)} - ${log.operation_type}: `;
                            if (Object.keys(log.changes).length > 0) {
                                // Formata as alterações de cada campo (antes e depois)
                                var changesFormatted = '';
                                Object.entries(log.changes).forEach(function([campo, valores]) {
                                    var antes = formatTime(valores.antes);
                                    var depois = formatTime(valores.depois);
                                    changesFormatted += `${campo} (Antes: ${antes}, Depois: ${depois}); `;
                                });
                                bodyHtml += changesFormatted;
                            } else {
                                bodyHtml += 'Sem alterações';
                            }
                            bodyHtml += `</li>`;
                        });
                        bodyHtml += `</ul>`;
                    } else {
                        bodyHtml += `<p><strong>Nenhum log registrado para este dia.</strong></p>`;
                    }

                    // Atualiza o corpo do modal com o conteúdo montado
                    $("#modal_especificacoes_dia .modal-body").html(bodyHtml);
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log('Erro na requisição AJAX detalhesDia =>', xhr, textStatus, errorThrown);
                    let msg = 'Ocorreu um erro ao buscar detalhes do dia.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                    $("#modal_especificacoes_dia .modal-body").html(
                        `<p class="text-danger">${msg}</p>`
                    );
                },
                complete: function() {
                    console.log('Requisição detalhesDia finalizada (success ou error).');
                    // Remove o loading ao final da requisição
                    toggleLoadingOnDivSmall($('#modal_especificacoes_dia .modal-body'), false);
                }
            });
        });

        // Suporte para dispositivos touch: taphold dispara o clique
        $(".td-data-monitoramento").off('taphold').on('taphold', function () {
            $(this).trigger('click');
        });
    }

    // Inicializa a trigger de clique
    ativaTriggerClickDia();
}

// Chama a função assim que o script é carregado
funcoesModalEspecificacoesDia();

/**
 * Adiciona o rodapé com os botões "Abrir" e "Fechar" no modal,
 * independentemente se há dados ou não.
 */
function addModalFooterButtons() {
    // Verifica se o footer já foi adicionado para não duplicar
    if ($('#modal_especificacoes_dia .modal-footer').length === 0) {
        var footerHtml = `
            <div class="modal-footer d-flex align-items-center justify-content-between justify-content-lg-start col-12 flex-wrap p-0">
                <div class="my-5 col-6 col-lg-4 col-xl-3 pe-2 m-0">
                    <button type="button" class="confirm-button button-modal w-100 mml-btn_enviar" title="Abrir">
                        Abrir
                    </button>
                </div>
                <div class="my-5 col-6 col-lg-4 col-xl-3 ps-2 m-0">
                    <button type="button" class="secondary-button button-modal w-100" data-bs-dismiss="modal" aria-label="Fechar">
                        Fechar
                    </button>
                </div>
            </div>
        `;
        $('#modal_especificacoes_dia .modal-content').append(footerHtml);
    }
}

// Chama a função para adicionar os botões no modal
addModalFooterButtons();

/**
 * Evento para o botão "Abrir"
 * Redireciona para a rota monitoriamento/detalhes.blade.php passando user_id, day e month.
 */
$(document).on('click', '.mml-btn_enviar', function() {
    // Obtém os dados armazenados no modal
    var $modal = $('#modal_especificacoes_dia');
    var mon_date = $modal.find('.data_modal_especificacoes_dia').data('mon_date');
    var user_id = $modal.find('.data_modal_especificacoes_dia').data('user_id');

    if (!mon_date || !user_id) {
        console.error("Dados insuficientes para redirecionamento.");
        return;
    }

    // Extrai day e month a partir da data (formato "YYYY-MM-DD")
    var partes = mon_date.split('-');
    var month = partes[0] + '-' + partes[1];
    var day = partes[2];

    // Monta a URL de redirecionamento com os parâmetros via query string
    var urlRedirect = '/monitoriamento/detalhes?user_id=' + encodeURIComponent(user_id) +
        '&day=' + encodeURIComponent(day) +
        '&month=' + encodeURIComponent(month);

    console.log("Redirecionando para:", urlRedirect);

    // Redireciona o navegador
    window.location.href = urlRedirect;
});
