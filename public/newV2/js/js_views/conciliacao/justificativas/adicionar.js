/**
 * Function acoesBotesJustificativas
 * Habilita apenas uma opção por vez para ser clicada.
 */
function acoesBotesJustificativas(){
    $('input#adiantamento').unbind('change');
    $('input#adiantamento').on("change", function() {
        if($(this).is(':checked')) {
            $('input#lcm').prop('checked', false);
            $('label#label_lcm').removeClass('active');
            $('label#label_lcm i.fa.fa-check-square.icone-check')
                .removeClass('fa-check-square')
                .addClass('fa-square-o');
            $('input#lcm').prop('disabled', true);

            $('input#conta_manual').prop('checked', false);
            $('label#label_conta_manual').removeClass('active');
            $('label#label_conta_manual i.fa.fa-check-square.icone-check')
                .removeClass('fa-check-square')
                .addClass('fa-square-o');
            $('input#conta_manual').prop('disabled', true);

            // $('#cliente_adiantamento_div').removeClass('ocultar');
        } else {
            // $('#cliente_adiantamento_div').addClass('ocultar');
            $('input#lcm').prop('disabled', false);
            $('input#conta_manual').prop('disabled', false);
        }
    });

    $('input#lcm').unbind('change');
    $('input#lcm').on("change", function() {
        if($(this).is(':checked')) {
            $('input#adiantamento').prop('checked', false);
            $('label#label_adiantamento').removeClass('active');
            $('label#label_adiantamento i.fa.fa-check-square.icone-check')
                .removeClass('fa-check-square')
                .addClass('fa-square-o');
            $('input#adiantamento').prop('disabled', true);

			$('input#conta_manual').prop('checked', false);
			$('label#label_conta_manual').removeClass('active');
			$('label#label_conta_manual i.fa.fa-check-square.icone-check')
				.removeClass('fa-check-square')
				.addClass('fa-square-o');
			$('input#conta_manual').prop('disabled', true);
        } else {
            $('input#adiantamento').prop('disabled', false);
			$('input#conta_manual').prop('disabled', false);
        }
    });

    $('input#conta_manual').unbind('change');
    $('input#conta_manual').on("change", function() {
        if($(this).is(':checked')) {
			$('input#adiantamento').prop('checked', false);
			$('label#label_adiantamento').removeClass('active');
			$('label#label_adiantamento i.fa.fa-check-square.icone-check')
				.removeClass('fa-check-square')
				.addClass('fa-square-o');
			$('input#adiantamento').prop('disabled', true);

			$('input#lcm').prop('checked', false);
			$('label#label_lcm').removeClass('active');
			$('label#label_lcm i.fa.fa-check-square.icone-check')
				.removeClass('fa-check-square')
				.addClass('fa-square-o');
			$('input#lcm').prop('disabled', true);
        } else {
            $('input#adiantamento').prop('disabled', false);
            $('input#lcm').prop('disabled', false);
        }
    });

    if($('input#lcm').is(':checked')) $('input#lcm').trigger('change');
    if($('input#adiantamento').is(':checked')) $('input#adiantamento').trigger('change');
    if($('input#conta_manual').is(':checked')) $('input#conta_manual').trigger('change');
}

acoesBotesJustificativas();