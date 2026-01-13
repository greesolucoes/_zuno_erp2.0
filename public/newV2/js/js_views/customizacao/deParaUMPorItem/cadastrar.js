$('select.de-para-produto_id').select2Ajax();
$('select.de-para-produto_id').data('init', '');

$('select.select_um').select2Simple();

controlaTabelaSuite({
	"ref": "#cadastro_de-para-umporitem",
	"funAposAddItem": function () {
		let produto= $('select.de-para-produto_id').val();
		if(is_empty(produto,1)){
			$('table#cadastro_de-para-umporitem tbody tr:not(.ocultar)').last().remove();
			swal({
				title: l['atencao'],
				text: l['escolhaUmProduto'],
				type: "warning",
				confirmButtonColor: '#3085d6',
			});
		}else{
			$('select.select_um').select2Simple();
			callEventos();
		}
	}
});
$('select.de-para-produto_id').off('change').on('change',function(){
	let produto= $(this).val();
	if(!is_empty(produto,1)){
		toggleLoading();
		let selectUMModelo= $('table#cadastro_de-para-umporitem tbody tr.ocultar').first().find('select.select_um');
		const url = $(".data_views").data('url_produto');
		ajaxRequest(true, url, null, 'text', {'produto': produto}, function (ums) {
			ums = $.parseJSON(ums);

			$(selectUMModelo).html('').append('<option value=""></option>');
			$.each(ums, function (id, value) {
				$(selectUMModelo).append('<option value="' + value.idUnidadesMedidas + '">' + value.nomeUnidadesMedidas + '</option>');
			});
			toggleLoading();
		});

		let trEmTela=  $('table#cadastro_de-para-umporitem tbody tr:not(.ocultar)');
		if(trEmTela.length > 0 && is_empty($('select.de-para-produto_id').attr('triggerInit'),1)){
			trEmTela.remove();
			swal({
				title: l['atencao'],
				text: l['aMudançaDoProdutoRemoveuAsLinhasDeUnidadesDeMedidas'],
				type: "warning",
				confirmButtonColor: '#3085d6',
			});
		}
	}else{
		$('table#cadastro_de-para-umporitem tbody tr:not(.ocultar)').remove();
	}

});

function callEventos(){
	$('select.select_um').off('change').on('change', function () {
		let selectUM = $(this);
		let umSelected = selectUM.val();

		// Guardo todas as unidades que estão em tela
		let selectUMidx = $("select.select_um").index(selectUM) - 1;
		let umsEmTela = $('table#cadastro_de-para-umporitem tbody tr:not(.ocultar) select.select_um');
		let vetorUMSelecionadas = [];
		umsEmTela.each(function (i) {
			if( (!is_empty($(this).val(), 1)) &&
				(selectUMidx != i)
			) {
				vetorUMSelecionadas.push($(this).val());
			}
		});

		if((!is_empty(umSelected, 1)) && (vetorUMSelecionadas.includes(umSelected))) {
			selectUM.val('');
			swal({
				title: l['atencao'],
				text: l['unidadeDeMedidaUsadaEmOutraLinha'],
				type: "warning",
				confirmButtonColor: '#3085d6',
			});
			return 0;
		}
		$('select.select_um').select2Simple();
	});
}
$(document).ready(function(){
	$('select.de-para-produto_id').attr('triggerInit',1);
	$('select.de-para-produto_id').trigger('change');
	$('select.de-para-produto_id').attr('triggerInit',0);
	callEventos();
});