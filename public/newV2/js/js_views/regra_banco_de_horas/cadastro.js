$(document).ready(function(){
    // Inicializa o Select2 no elemento correto
    $("#duration").select2({
        placeholder: "Duração do Banco de Horas",
        language: "pt-BR",
        allowClear: true,
        dropdownParent: $('body')
    });

    // Se necessário, remova a classe que esconde as linhas já existentes
    $('#cadastro-origem-perda tbody tr.ocultar').removeClass('ocultar');

    // Função para renumerar as linhas
    function renumeraLinhasForm(){
        let count = 0;
        $('.item-number').each(function () {
            $(this).html(count);
            count++;
        });
    }

    // Configura a tabela dinâmica
    controlaTabelaSuite({
        "ref": "#cadastro-origem-perda",
        "funAposAddItem": function () {
            renumeraLinhasForm();
        },
        "funAposRemoverItem": function (){
            renumeraLinhasForm();
        }
    });
});
