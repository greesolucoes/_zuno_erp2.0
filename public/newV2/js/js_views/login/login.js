// Função para exibir o loading ao submeter o formulário
function loadingPortal() {
    $('button[type="submit"]').click(function (e) {
        e.preventDefault();
        // Se não houver tecla modificadora pressionada, prossegue com o submit
        if (!e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey) {
            $('form').submit();
            $('form :submit').prop("disabled", "disabled");
            $('form').unbind('submit');
            $('.loadingPortalRSI, .many-loading').css("display", "block");
        }
    });
}

// Função para ativar animações extras após a primeira animação do input
function ativaAnimacoesExtra(){
    $('input.usuario').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
        $('button.login').addClass('animated pulse');
    });
}

// Correção: use $(document).ready() sem aspas
$(document).ready(function(){
    $('.loadingPortalRSI, .many-loading').css("display", "none");
    // Executa as outras funções após o carregamento da página
    loadingPortal();
    ativaAnimacoesExtra();
    togglePassword();
});

// Função para limpar o localStorage, exceto alguns itens
function clearLocalStorageExceptSomeItems() {
    for (let key in localStorage) {
        if (key !== 'many_minds_theme') {
            localStorage.removeItem(key);
        }
    }
}

// Função para alternar a exibição da senha
function togglePassword() {
    $('.exibir-senha').on('click', function() {
        const inputField = $(`#${$(this).data('input')}`);

        const eyeIcon = 'eye-icon.svg';
        const eyeLockedIcon = 'eye-locked-icon.svg';

        if (inputField.attr('type') === 'password') {
            inputField.attr('type', 'text');
            $(this).find('img').attr('src', function(index, oldSrc) {
                return oldSrc.replace(eyeLockedIcon, eyeIcon);
            });
        } else {
            inputField.attr('type', 'password');
            $(this).find('img').attr('src', function(index, oldSrc) {
                return oldSrc.replace(eyeIcon, eyeLockedIcon);
            });
        }
    });
}

// Limpa sessionStorage e localStorage (exceto alguns itens)
sessionStorage.clear();
clearLocalStorageExceptSomeItems();
