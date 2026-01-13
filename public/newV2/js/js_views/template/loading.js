/**
 * Created by vitor on 26/08/2017.
 */
function toggleLoading() {
    if ($('.loadingPortalRSI, .many-loading').css("display") === 'none') {
        $('.loadingPortalRSI, .many-loading').css("display", "block");
    } else {
        $('.loadingPortalRSI, .many-loading').css("display", "none");
    }
}

function forceToggleLoading(bolEnable) {
    if(is_empty(bolEnable, 1)){
        $('.loadingPortalRSI, .many-loading').css("display", "none");
    }else{
        $('.loadingPortalRSI, .many-loading').css("display", "block");
    }
}

function toggleLoadingOnDiv(div, ativaTexto) {
    if(is_empty(div, 1)) $('.div_geral_loading');

    $(div).html(''
		+ '<div class="centraliza flex-column"> ' +
			(isOldLayout
				? '<i class="fa fa-4x fa-spinner fa-spin" aria-hidden="true"></i>'
				: '<span data-icon="eos-icons:bubble-loading" class="iconify fs-8"></span>'
			) +
			'<div class="espacamento" style="height: 15px"></div> ' +
        	l["carregando"] +
        '</div>' +
	'');
}

function toggleLoadingOnDivSmall(div, ativaTexto) {
    if(is_empty(div, 1)) $('.div_geral_loading');

    $(div).html(
        '<div class="centraliza flex-column gap-3">' +
			(isOldLayout
				? '<i class="fa fa-spinner fa-pulse fa-spin" aria-hidden="true"></i>'
				: '<span data-icon="eos-icons:bubble-loading" class="iconify fs-4"></span>'
			)
			+ '' +
			(is_empty(ativaTexto, 1)
				? ''
				: l["carregando"])
		+ '</div>'
    );
}

$('document').ready(function(){
    toggleLoading();
});

function scrollDown(){
    var obj = $(".modal.modalItensPorGrupo .modal-content .modal-body #responsive-table-modal");

    var limit = 10;
    var i = 0;
    $(obj).find('table tbody tr').each(function () {
        if(i >= limit) return false;
        $(this).removeClass('ocultar');
        i++;
    });

    $(obj).unbind('scroll');
    $(obj).scroll(function () {
        var h = $(this).height();
        var y = ($(this).prop('scrollHeight') - $(this).scrollTop()) - h;

        if(y <= 1.5){
            i = 0;
            $(this).find('table tbody tr.ocultar').each(function () {
                if(i >= limit) return;
                $(this).removeClass('ocultar');
                i++;
            });
        }

    });
}
