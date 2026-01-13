// Função que monta a URL com os parâmetros filtrados e redireciona para acionar a exportação
function __acaoAtualizaDataTable(filters) {
    // Monta a URL usando a variável global exportAfdUrl definida no template
    let url = exportAfdUrl + "?";
    if (filters.usuarios && filters.usuarios.length > 0) {
        url += "select_usuarios=" + filters.usuarios.join(",") + "&";
    }
    if (filters.departamento) {
        url += "select_departments=" + filters.departamento + "&";
    }
    if (filters.cargo) {
        url += "select_cargos=" + filters.cargo + "&";
    }
    if (filters.dataInicial) {
        url += "dataInicial=" + filters.dataInicial + "&";
    }
    if (filters.dataFinal) {
        url += "dataFinal=" + filters.dataFinal + "&";
    }
    console.log("Pesquisando com filtros:", filters);
    console.log("URL:", url);
    // Redireciona a página para a URL gerada para acionar o download do AFD
    window.location.href = url;
}

// Função que coleta os valores dos filtros e monta o objeto de filtros
function applyFilters() {
    let selectedUsuarios = $("#select_usuarios").val();
    let selectedDepartment = $("#select_departments").val();
    let selectedCargo = $("#select_cargos").val();
    let dataInicial = $("#dataInicial").val();
    let dataFinal = $("#dataFinal").val();

    // Se o filtro de usuários incluir "all", desconsidera os filtros de departamento e cargo
    if (selectedUsuarios !== null && selectedUsuarios.includes("all")) {
        selectedDepartment = "";
        selectedCargo = "";
    }

    let filters = {
        usuarios: selectedUsuarios,
        departamento: selectedDepartment,
        cargo: selectedCargo,
        dataInicial: dataInicial,
        dataFinal: dataFinal
    };

    if (selectedDepartment && selectedCargo) {
        console.warn("Atenção: Departamento e Cargo selecionados. Verifique se os filtros estão alinhados.");
    }
    __acaoAtualizaDataTable(filters);
}

// Inicializa os selects com os plugins desejados
function criaCostumizacoes() {
    $("select#select_departments").select2Simple();
    $("select#select_cargos").select2Simple();
    $("select#select_acao_sefaz").select2Simple();
    $("select#filiaisRelatorio").select2Ajax();
    $("select#filiaisRelatorio").data('init', '');
    $("select.select_usuarios").select2();
    $("select.select_usuarios").data('init', '');
}

// Configura o evento do botão "Pesquisar"
function pesquisaPersonalizada() {
    $("#search-table").on("click", function () {
        applyFilters();
    });
}

$(document).ready(function () {
    criaCostumizacoes();
    pesquisaPersonalizada();
});
