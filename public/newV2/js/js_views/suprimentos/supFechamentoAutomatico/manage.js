function adicionar() {

	$('.adicionar-registro').unbind('click');

	$('.adicionar-registro').click(function() {

		target = $('.table tbody');

		row = $(target).find('.linha-config-hidden').first().clone();

		$(target).append(row);

		$(target).find('tr:last').removeClass('linha-config-hidden');
		$(target).find('tr:last').addClass('linha-config');
		$(target).find('tr:last').removeClass('no-remove');

		$(target).find('tr:last input[type=time]').attr('name','horario[]');
		$(target).find('tr:last #diaSemana').attr('name','diaSemana[]');

		$(target).find('tr:last').find('.remover-registro').show();

		criaSelects();
		remove();

	});

}

function remove() {
	$('.remover-registro').unbind('click');
	$('.remover-registro').click(function() {
		$(this).parents('tr').remove();
	});
}

function criaSelects() {
	$(".select_dia_semana").select2Simple();
}

adicionar();
remove();
criaSelects();
