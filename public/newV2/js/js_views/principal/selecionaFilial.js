/* Created by vitor on 26/08/2017. */

$(function() {
    // Esconde os indicadores de loading assim que o DOM estiver pronto
    $('.loadingPortalRSI, .many-loading').hide();

    // Aplica o tema do usuário, se for o layout atual
    if (!isOldLayout) {
        setLoggedUserPreferenceTheme();
    }

    initSelects();
    initPage();
});

function initSelects() {
    $('select.select_empresaSelect').select2({
        placeholder: "Selecione uma filial",
        width: '100%',
        minimumResultsForSearch: Infinity // remove a barra de pesquisa interna
    });
}

function initPage() {
    const select = $('select.select_empresaSelect');
    const form = select.closest('form');
    const submitButton = form.find('button.login[type="submit"], button.login:not([type])');

    function hasSelectedBranch() {
        return !!select.val();
    }

    function syncSubmitEnabled() {
        submitButton.prop('disabled', !hasSelectedBranch());
    }

    // Regra absoluta: não pode prosseguir sem escolher filial (botão, Enter, submit programático)
    form.off('submit.chooseBranchGuard').on('submit.chooseBranchGuard', function (e) {
        if (!hasSelectedBranch()) {
            e.preventDefault();
            syncSubmitEnabled();
            alert('Selecione uma filial antes de continuar.');
            return false;
        }

        $('.loadingPortalRSI, .many-loading').show();
        submitButton.prop('disabled', true);
        return true;
    });

    select.off('change.chooseBranchGuard').on('change.chooseBranchGuard', function () {
        syncSubmitEnabled();
    });

    syncSubmitEnabled();

    // Inicializa demais funções globais da aplicação
    allFunctions();
}
