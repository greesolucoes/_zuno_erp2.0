/**
 * Function criaSelectsFuncionarios
 * Cria o Select2 de todos os selects da página e altera o data-init deles para nada
 * (pode ocasionar problemas se não for feito isso após a 1 chamada do select 2)
 */
function criaSelectsFuncionarios() {
    $('.select_cargo').select2Ajax();
    $('.select_cargo').data('init', '');
}

criaSelectsFuncionarios();