
// TODO Tentar remover o document
$(document).on('click', '.show_modal_motivo', function (e) {
	$('.modal_motivo #label_motivo').html($(this).attr("title"));
	$('.modal_motivo .descricao_motivo').html($(this).parents('td').find('.descricao_rejeicao').html());
	$('.modal_motivo').modal('toggle');
});