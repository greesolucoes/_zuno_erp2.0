/**
 * JS utilizado na view "Novo Usuário"
 */

// Configuração global para AJAX, garantindo que o token CSRF seja enviado em todas as requisições
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function habilitaBotaoRemoverLinha(){
    $('table#colaboradoresTable button.removeColaboradores').unbind('click');
    $('table#colaboradoresTable button.removeColaboradores').click(function () {
        var rem = $(this).parents('tr');
        rem.fadeOut(270, function () {
            rem.remove();
        });
    });
}

function criaSelect() {
    $(".select_colaborador").select2({
        placeholder: "Selecione um colaborador",
        language: "pt-BR",
        allowClear: true
    });
    $(".select_afastamento").select2({
        placeholder: "Selecione o tipo de afastamento",
        language: "pt-BR",
        allowClear: true
    });
    // Inicializa o botão de remoção de linha
    habilitaBotaoRemoverLinha();
}

$('button.addColaboradores').click(function () {
    var modelo = $('table#colaboradoresTable tbody tr').first().html();
    $('table#colaboradoresTable').append('<tr>' + modelo + '</tr>');
    $($('table#colaboradoresTable tbody tr').last()).find('button.removeColaboradores').prop('disabled', false);

    // Reseta os selects
    $('table#colaboradoresTable .select').select2Reset();

    // Desmarca seleções anteriores
    var removeOptions = $($('table#colaboradoresTable tbody tr').last()).find('select.select_colaborador');
    var selectAfastamento = $($('table#colaboradoresTable tbody tr').last()).find('select.select_afastamento');
    $(removeOptions).find('option:selected').prop('selected', false);
    $(selectAfastamento).val(null).trigger('change');

    criaSelect();
});

// Inicialização dos selects ao carregar a página
criaSelect();
