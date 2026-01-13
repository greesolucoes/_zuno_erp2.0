/**
 * Created by vitor on 13/09/2017.
 */
function filiaisDebugEnabled() {
    try {
        if (window.location && String(window.location.search || '').includes('debugFilial=1')) return true;
        if (window.localStorage && window.localStorage.getItem('debug_filiais') === '1') return true;
    } catch (e) {}
    return false;
}

(function filiaisStartupLog() {
    try {
        var count = document.querySelectorAll('select.select_empresaSelect').length;
        if (count > 0) {
            // eslint-disable-next-line no-console
            console.log('[filiais] default.js loaded', { selectsFound: count, debug: filiaisDebugEnabled() });
            if (!filiaisDebugEnabled()) {
                // eslint-disable-next-line no-console
                console.log('[filiais] to enable debug: add ?debugFilial=1 or localStorage.setItem("debug_filiais","1")');
            }
        }
    } catch (e) {}
})();

function filiaisDebug() {
    if (!filiaisDebugEnabled()) return;
    try {
        // eslint-disable-next-line no-console
        console.info.apply(console, ['[filiais]'].concat([].slice.call(arguments)));
    } catch (e) {}
}

function filiaisDebugError() {
    if (!filiaisDebugEnabled()) return;
    try {
        // eslint-disable-next-line no-console
        console.error.apply(console, ['[filiais]'].concat([].slice.call(arguments)));
    } catch (e) {}
}

function initSelect2EmpresaTemplate(destroy) {
    var objSelect = $('select.select_empresaSelect');
    filiaisDebug('initSelect2EmpresaTemplate start', {
        destroy: destroy,
        hasJquery: !!window.jQuery,
        hasSelect2: !!($.fn && $.fn.select2),
        selectsFound: objSelect.length
    });

    if (!is_empty(destroy, 1)) {
        $(objSelect).select2('destroy').select2Reset();
    }

    // Para cada select, se existir data-init, adiciona a opção já selecionada
    objSelect.each(function() {
        var initData = $(this).attr('data-init');
        if (initData) {
            try {
                var initObj = JSON.parse(initData);
                if (initObj && initObj.id && initObj.text) {
                    // Cria uma option com o valor e o texto da filial logada
                    var newOption = new Option(initObj.text, initObj.id, true, true);
                    $(this).append(newOption).trigger('change');
                }
            } catch (e) {
                console.error("Erro ao parsear data-init:", e);
            }
        }
    });

    // Inicializa o select2 com AJAX, adaptando o formato de resposta do controller
    objSelect.each(function() {
        var $select = $(this);
        var selectEmpresaId = $select.data('empresa-id');
        var hiddenEmpresaId = $('#empresa_id').length ? $('#empresa_id').val() : null;
        var empresaId = !is_empty(selectEmpresaId, 1) ? selectEmpresaId : hiddenEmpresaId;
        var url = $select.data('url');

        if (is_empty(url, 1)) {
            // eslint-disable-next-line no-console
            console.error('[filiais] select missing data-url. Check navbar.blade.php data-url="{{ route(\'filiais.search\') }}"', $select.get(0));
        }
        if (is_empty(empresaId, 1)) {
            // eslint-disable-next-line no-console
            console.warn('[filiais] empresaId not found (hidden #empresa_id missing?)', { selectEmpresaId: selectEmpresaId, hiddenEmpresaId: hiddenEmpresaId });
        }
        filiaisDebug('select config', {
            name: $select.attr('name'),
            url: url,
            selectEmpresaId: selectEmpresaId,
            hiddenEmpresaId: hiddenEmpresaId,
            empresaId: empresaId,
            dropdown: $select.data('dropdown'),
        });

        $select.select2({
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                transport: function(params, success, failure) {
                    filiaisDebug('ajax request', {
                        url: params.url,
                        data: params.data,
                        type: params.type,
                    });
                    var request = $.ajax(params);
                    request.then(
                        function(data) {
                            filiaisDebug('ajax success', { items: Array.isArray(data) ? data.length : null });
                            success(data);
                        },
                        function(xhr) {
                            filiaisDebugError('ajax failure', {
                                status: xhr && xhr.status,
                                responseText: xhr && xhr.responseText
                            });
                            failure(xhr);
                        }
                    );
                    return request;
                },
                data: function(params) {
                    var payload = {
                        q: params.term // termo digitado pelo usuário
                    };
                    if (!is_empty(empresaId, 1)) {
                        payload.empresa_id = empresaId;
                    }
                    if (filiaisDebugEnabled()) {
                        payload.debugFilial = 1;
                    }
                    filiaisDebug('ajax payload', payload);
                    return payload;
                },
                processResults: function(data) {
                    // O controller retorna um array simples, convertendo para o formato aceito pelo select2
                    var results = data.map(function(filial) {
                        return {
                            id: filial.id,
                            text: filial.nome_filial
                        };
                    });
                    return {
                        results: results
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });
    });
}

$('select.select_empresaSelect').on("select2:select", function(e) {
    e.preventDefault();
    var obj = $(this);
    // A URL base é definida na div .datas_template na view; concatena o ID da filial selecionada
    var url = $('.datas_template').data('url_mudar_empresa') + $(obj).val();
    filiaisDebug('select2:select', { id: $(obj).val(), url: url });

    swal({
        title: l["mudarDeFilial?"],
        text: l["casoContinue,TeremosQueLheRedirecionarParaAPáginaPrincipal,DesejaContinuar?"],
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: l["sim!"],
        cancelButtonText: l["cancelar!"]
    }).then(function() {
        toggleLoading();
        window.location = url;
    }, function() {
        initSelect2EmpresaTemplate(1);
        allFunctions();
        swal(
            l["trocaCancelada"],
            l["operaçãoCancelada!"],
            'error'
        );
    }).catch(swal.noop);
});

$('select.select_empresaSelect')
    .on('select2:opening', function() { filiaisDebug('select2:opening'); })
    .on('select2:open', function() { filiaisDebug('select2:open'); });

function menuAcoesTemplate() {
    var nav_menu = 'ul.menu_lateral li.item_menu_lateral';
    var nav_menu_link = nav_menu + ' a.subMenu';
    var nav_submenu = 'ul.nav-second-level li.linkMenu';
    var nav_submenu_w_menu = nav_menu + ' ' + nav_submenu;

    var urlAgora = window.location.origin + window.location.pathname;
    var menuAgora = $(nav_menu).find('a[href="' + urlAgora + '"]');

    $(nav_menu_link).on('click', function() {
        var parentes = $(this).parent().find(nav_submenu);
        var isVisibleSub = $($(parentes).first()).is(":visible");

        $($(nav_submenu_w_menu).not(parentes)).hide();
        if (isVisibleSub) {
            parentes.fadeOut(270, function() {
                parentes.hide();
            });
        } else {
            parentes.fadeIn(270, function() {
                parentes.show();
            });
        }
    });

    $(nav_submenu_w_menu).hide();

    menuAgora.addClass('active');
    menuAgora.parents(nav_menu).find(nav_submenu).show();
    menuAgora.parents(nav_menu).find('a.subMenu').addClass('active');
}

function loadingPortal() {
    $('button[type="submit"]:not(.submit-custom)').on('click', function(e) {
        e.preventDefault();
        var formId = $(this).data('form_id');

        $('select').each(function() {
            if (is_empty($(this).val(), 1)) {
                if ($(this).data('select2')) $(this).select2('destroy');
                $(this).append('<option value="" selected="selected"></option>');
                $(this).val('');
            }
        });
        if (!e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey) {
            toggleLoading();

            var formulario = null;
            if (!is_empty(formId, 1)) formulario = 'form#' + formId;
            else formulario = 'form';

            $(formulario).submit();
            $(formulario + ' :submit').prop("disabled", "disabled");
            $(formulario).unbind('submit');
        }
    });
    $('a.adicionar, a.linkMenu, a.voltar, a.alterar, a.visualizar, button.adicionar, a.item_menu-link, button.voltar, button.alterar, button.visualizar, a.recebePedidos, button.recebePedidos').on('click', function(e) {
        if (!e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey && !e.target.target) {
            toggleLoading();
        }
    });
}

function changeLayoutProduct() {
    $('#changeLayoutProduct, #changeLayoutProductLink').off('click');
    $('#changeLayoutProduct, #changeLayoutProductLink').on("click", function() {
        const url = $('.datas_template').data('url_alterar_layout_produto');
        const versaoAntiga = $('.datas_template').data('versao_layout_produto');

        const textSwal =
            "<p>" +
            l['porTempoLimitadoVoceAindaTemAOpcaoDeAcessarAVersaoAntiga'] +
            "<br>" + l['noEntantoEImportanteNotarQueA'] +
            "<b style='font-weight: 700;'>" + l['versaoAntigaSeraDescontinuadaEmBreve'] + "</b>" +
            "<br>" + l['estamosComprometidosEmEvoluirConstantementeParaOferecerAMelhorExperienciaPossivel!'] +
            "<br>" + l['EncorajamosVoceAExplorarAsMelhoriasQueONovoSistemaOferece'] +
            "</p>";

        swal({
            title: versaoAntiga ? l['experimenteANovaCaraDoSistema!'] : l['aManymindsSabeQueMudancasPodemSerDesafiadoras'],
            html: textSwal,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: versaoAntiga ? '#3085d6' : '#0081F9',
            cancelButtonColor: versaoAntiga ? '#d33' : '#0081F9',
            confirmButtonText: versaoAntiga ? l["utilizarNovaVersao"] : l["utilizarAntigaVersao"],
            cancelButtonText: versaoAntiga ? l["manterAntigaVersao"] : l["manterNovaVersao"],
            onOpen: function(modal) {
                modal.classList.add('inverted-buttons-swal');
            }
        }).then(function() {
            toggleLoading();

            ajaxRequest(true, url, null, 'text', null, function(ret) {
                try {
                    ret = JSON.parse(ret);
                    swal(
                        ret['titulo'],
                        ret['text'],
                        ret['class']
                    ).then(function() {
                        location.reload();
                    }).catch(swal.noop);

                    toggleLoading();
                } catch (err) {
                    swal(
                        l["erro!"],
                        l["tempoDeRespostaDoServidorEsgotado!"],
                        "error"
                    ).catch(swal.noop);
                    forceToggleLoading(0);
                }
            });
        }, function() {
            // SE DER ERRO
        }).catch(swal.noop);
    });
}

menuAcoesTemplate();
filiaisDebug('default.js loaded, calling initSelect2EmpresaTemplate');
initSelect2EmpresaTemplate(0);

// Re-inicializa quando o header for re-renderizado/alterado (ex: scripts do template/SPA)
setTimeout(function () {
    try {
        if (window.jQuery && $.fn && $.fn.select2) {
            filiaisDebug('reinit select2 after timeout');
            initSelect2EmpresaTemplate(1);
            initSelect2EmpresaTemplate(0);
        }
    } catch (e) {}
}, 1200);

allFunctions();
loadingPortal();
autoCheck();
changeLayoutProduct();
