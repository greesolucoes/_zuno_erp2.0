function criaCostumizacoes() {
	$('.select_ajax').select2Ajax();
	$('.select_ajax').data('init', '');
}
// Inicializa o acesso às abas de configuração
handleIntegracoes();

// Ativa ou desativa configurações de integrações junto ao SAP
$('input[type="checkbox"]').on('change', function() {
	handleIntegracoes();
});

// função para esconder as configurações de integrações não ativas
function handleIntegracoes() {
	document
		.querySelectorAll(`.informacoes_add ${isOldLayout ? '.row' : '.config-flags'} input[type='checkbox']`)
		.forEach(function(e) {

			let tabIntegracao = $("[data-integracao_tab='" + e.value + "']");
			if (e.checked) {
				tabIntegracao.removeClass('disabled');
			} else {
				tabIntegracao.addClass('disabled');
				tabIntegracao.removeClass('active');
				$(`#${tabIntegracao.attr('aria-controls')}`).removeClass('active');
			}
		});
};

$('#remove-field-util-1').first().hide();
var contadorRea = 1;
$('#add-field-util').click(function() {
	contadorRea++;
	var novaSecao = $('.section-util').last().clone();
	novaSecao.attr('id', 'section-util-' + contadorRea);
	novaSecao.find('option').remove();
	novaSecao.find('.getUtilizacao').attr('data-init', '');
	novaSecao.insertBefore('#add-field-util');
	novaSecao.find('.getUtilizacao').select2Ajax();

	novaSecao.find('button').attr('id', 'remove-field-util-' + contadorRea).show().unbind('click').click(function() {
		$(this).closest('.section-util').remove();
	});
});

$('.remove-btn-util').click(function() {
	var val_id = $(this).attr('val_id');
	if( val_id != 1 ){
		$('#remove-field-util-'+val_id).closest('.section-util').remove();
	}
});