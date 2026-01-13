// ===================== COMUM =====================
// Define a variável csrfToken com o valor do token CSRF
var csrfToken = $('meta[name="csrf-token"]').attr('content');
console.log("csrfToken definido:", csrfToken);

/**
 * URLs utilizadas
 */
let urlMergeDeParaModelos = $('.data_views').data('url_merge_de_para_modelos'); // Deve ser "/config_email"
console.log("URL Merge de/Para Modelos:", urlMergeDeParaModelos);
let urlConfigAddon = $('.data_views').data('url_config_addon'); // Ex: "/config-addon/store"
console.log("URL Config Addon:", urlConfigAddon);

/**
 * Seletores do modal de Config E-mail
 */
let emailSelect         = $("#email_id");
let servicoSelect       = $("#servico_id");
let modalDeParaModelos  = $('#modalDeParaModelos');
let modalMsgs           = $('#container-msg-modal');
let botaoAcaoModal      = $('#botaoAcaoModal');
let inputContinuarAdd   = $('#continuarInserindo');

/**
 * Seletores do modal de Config Addon
 */
let templateSelect        = $("#template_id");
let dataDisparoInput      = $("#data_de_disparo");
let horarioDisparoInput   = $("#horario_de_disparo");
let modalConfigAddon      = $("#modalConfigAddon");
let modalMsgsAddon        = $("#container-msg-config-addon");
let botaoAcaoConfigAddon  = $("#botaoAcaoConfigAddon");
let inputContinuarAddAddon = $("#continuarInserindoAddon");
let idConfigAddonHidden   = $("#id_config_addon"); // campo oculto para armazenar o id_config ou id do registro em edição

// ===================== FUNÇÕES DO MODAL DE CONFIG E-MAIL =====================
function startInput(opts = '') {
    console.log("Iniciando selects do modal de Config E-mail com select2...");
    emailSelect.select2();
    servicoSelect.select2();
}

function clearInputs() {
    console.log("Limpando inputs dos selects do modal de Config E-mail...");
    emailSelect.val('').trigger('change');
    servicoSelect.val('').trigger('change');
}

function clearModalInfo() {
    console.log("Limpando informações do modal de Config E-mail...");
    clearInputs();
    modalMsgs.html('');
    botaoAcaoModal.html('');
    // Remove atributos para limpar a ação anterior
    botaoAcaoModal.removeAttr('data-action data-request');
    inputContinuarAdd.hide();
}

function insertItem() {
    console.log("Abrindo modal de Config E-mail para nova inserção...");
    // Para inserção, usa a URL definida na div data-views
    botaoAcaoModal.attr('data-action', urlMergeDeParaModelos);
    botaoAcaoModal.attr('data-request', 'insercao');
    botaoAcaoModal.html(l['salvar'] || 'Salvar');
    inputContinuarAdd.show();
    modalDeParaModelos.modal('show');
    startInput();
}

$('#modalDeParaModelos').on('hidden.bs.modal', function () {
    console.log("Modal de Config E-mail fechado. Limpando informações...");
    clearModalInfo();
    let tableDataTable = $(".table-exibe").DataTable();
    tableDataTable.draw();
});

// Função para abrir o modal em modo edição e preencher os dados via AJAX
function editItemConfig(configId) {
    console.log("Abrindo modal para edição de configuração, id:", configId);
    $.ajax({
        url: '/config_email/' + configId + '/edit-modal',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Preenche os campos com os dados retornados
            emailSelect.val(data.email_id).trigger('change');
            servicoSelect.val(data.servico).trigger('change');
            // Atualiza o atributo data-action para a rota de update
            botaoAcaoModal.attr('data-action', '/config_email/' + configId);
            botaoAcaoModal.attr('data-request', 'edicao');
            botaoAcaoModal.html(l['atualizar'] || 'Atualizar Configuração');
            // Oculta a opção "Continuar adicionando" no modo edição
            inputContinuarAdd.hide();
            // Abre o modal e reinicia os selects
            modalDeParaModelos.modal('show');
            startInput();
        },
        error: function(xhr) {
            console.error("Erro ao carregar dados para edição", xhr);
            swal("Erro", "Não foi possível carregar os dados da configuração.", "error");
        }
    });
}

// Ação do botão no modal de Config E-mail
botaoAcaoModal.on('click', function () {
    console.log("Botão de ação do modal de Config E-mail clicado. Preparando envio AJAX...");
    toggleLoading();
    let requestData = {
        email_id: emailSelect.val(),
        servico_id: servicoSelect.val(),
        addMore: $('#addMore:checked').val(),
        requestType: botaoAcaoModal.attr('data-request'),
        _token: csrfToken
    };
    // Define o método: 'PUT' para edição, 'POST' para inserção
    let methodType = (botaoAcaoModal.attr('data-request') === 'edicao') ? 'PUT' : 'POST';
    console.log("URL de ação:", botaoAcaoModal.attr('data-action'));
    console.log("Método:", methodType);
    $.ajax({
        url: botaoAcaoModal.attr('data-action'),
        type: methodType,
        data: requestData,
        dataType: 'json',
        success: function(retorno) {
            toggleLoading();
            swal(retorno.class === 'success' ? "Sucesso" : "Erro", retorno.msg, retorno.class);
            if (retorno.closeModal) {
                modalDeParaModelos.modal('hide');
            } else if (retorno.class === 'success' && botaoAcaoModal.attr('data-request') !== 'edicao') {
                clearInputs();
                startInput();
            }
        },
        error: function(xhr) {
            toggleLoading();
            let resp;
            try {
                resp = JSON.parse(xhr.responseText);
            } catch(e) {
                resp = { msg: "Erro desconhecido ao salvar a configuração de e-mail." };
            }
            swal("Erro", resp.msg, "error");
        }
    });
});

// ===================== FUNÇÕES DO MODAL DE CONFIG ADDON =====================
function startInputAddon(opts = '') {
    console.log("Iniciando select do modal de Config Addon com select2...");
    templateSelect.select2();
}

function clearInputsAddon() {
    console.log("Limpando inputs do modal de Config Addon...");
    templateSelect.val('').trigger('change');
    dataDisparoInput.val('');
    horarioDisparoInput.val('');
}

function clearModalInfoAddon() {
    console.log("Limpando informações do modal de Config Addon...");
    clearInputsAddon();
    modalMsgsAddon.html('');
    botaoAcaoConfigAddon.html('');
    botaoAcaoConfigAddon.removeAttr('data-action data-request');
    inputContinuarAddAddon.hide();
}

// Função para buscar e preencher dados do addon (edição ou inserção, se não existir)
function editItemAddon(configId) {
    console.log("Buscando dados do Config Addon para Config ID:", configId);
    $.ajax({
        url: '/config-addon/json/' + configId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            idConfigAddonHidden.val(configId);
            if(response.addon) {
                console.log("Addon encontrado, entrando em modo edição.");
                inputContinuarAddAddon.hide();
                templateSelect.val(response.addon.id_template).trigger('change');
                dataDisparoInput.val(response.addon.data_de_disparo).trigger('change');
                horarioDisparoInput.val(response.addon.horario_de_disparo);
                botaoAcaoConfigAddon.attr('data-request', 'edicao');
                botaoAcaoConfigAddon.attr('data-action', '/config-addon/update/' + response.addon.id);
                botaoAcaoConfigAddon.html(l['atualizar'] || 'Atualizar');
            } else {
                console.log("Nenhum addon encontrado, entrando em modo inserção.");
                inputContinuarAddAddon.show();
                botaoAcaoConfigAddon.attr('data-request', 'insercao');
                botaoAcaoConfigAddon.attr('data-action', urlConfigAddon);
                botaoAcaoConfigAddon.html(l['salvar'] || 'Salvar');
            }
            modalConfigAddon.modal('show');
            startInputAddon();
        },
        error: function(xhr) {
            console.error("Erro ao buscar dados do Config Addon:", xhr.responseText);
            swal("Erro", "Não foi possível carregar os dados do Config Addon.", "error");
        }
    });
}

// Evento para os botões "Integradores" – chamando editItemAddon
$('.integradores').on('click', function(e) {
    e.preventDefault();
    let configId = $(this).data('id');
    console.log("Botão Integradores clicado. Config ID:", configId);
    editItemAddon(configId);
});

// Ação do botão do modal de Config Addon
botaoAcaoConfigAddon.on('click', function () {
    console.log("Botão de ação do modal de Config Addon clicado. Preparando envio AJAX...");
    toggleLoading();
    let requestType = botaoAcaoConfigAddon.attr('data-request');
    let actionUrl   = botaoAcaoConfigAddon.attr('data-action');
    let methodType  = (requestType === 'edicao') ? 'PUT' : 'POST';
    let requestData = {
        id_config: idConfigAddonHidden.val(),
        id_template: templateSelect.val(),
        data_de_disparo: dataDisparoInput.val(),
        horario_de_disparo: horarioDisparoInput.val(),
        addMore: $('#addMoreAddon:checked').val(),
        requestType: requestType,
        _token: csrfToken
    };
    $.ajax({
        url: actionUrl,
        type: methodType,
        data: requestData,
        dataType: 'json',
        success: function(retorno) {
            toggleLoading();
            swal(retorno.class === 'success' ? "Sucesso" : "Erro", retorno.msg, retorno.class);
            if(retorno.closeModal) {
                modalConfigAddon.modal('hide');
            } else if(retorno.class === 'success') {
                clearInputsAddon();
                startInputAddon();
            }
        },
        error: function(xhr) {
            toggleLoading();
            let resp;
            try {
                resp = JSON.parse(xhr.responseText);
            } catch(e) {
                resp = { msg: "Erro desconhecido ao salvar Config Addon." };
            }
            swal("Erro", resp.msg, "error");
        }
    });
});

$('#modalConfigAddon').on('hidden.bs.modal', function () {
    console.log("Modal de Config Addon fechado. Limpando informações...");
    clearModalInfoAddon();
});
