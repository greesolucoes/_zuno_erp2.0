let removeWorkflowAprovacoesUsuarios = function(){
	$('table#workflowAprovacoesUsuariosTable button.removeWorkflowAprovacoesUsuarios')
		.unbind('click')
		.click(function () {
			let rem = $(this).parents('tr');

			rem.fadeOut(270, function () {
				rem.remove();
				calculaNumerosLinhas();
			});
		});
}

let calculaNumerosLinhas = function() {
	$('input.sequencia').each(function(numLinha, elLinha) {
		$(elLinha).val(numLinha + 1);
	})
}

$('button.addWorkflowAprovacoesUsuarios').click(function () {
	let seletorTableTbodyTr = 'table#workflowAprovacoesUsuariosTable tbody template';

	$('table#workflowAprovacoesUsuariosTable tbody')
		.append($(seletorTableTbodyTr).first().html());

	$(".idUsuarios").last().select2Simple();
	$(".idUsuarios").last().val('');
	$(".idUsuarios").last().trigger('change');

	removeWorkflowAprovacoesUsuarios();
	calculaNumerosLinhas();
});
removeWorkflowAprovacoesUsuarios();
calculaNumerosLinhas();

$('.nNecessarioRejeicoes, .nNecessarioAutorizacoes').off('change').on('change', function() {
	const numSequencias = $('input.sequencia').length;
	if($(this).val() > numSequencias) {
		swal(
			l['atenção!'],
			l['oValorDesteCampoNaoPodeSerMaiorQueONumeroTotalDeAprovadores'],
			'warning'
		);

		$(this).val($('input.sequencia').length);
	}
});

