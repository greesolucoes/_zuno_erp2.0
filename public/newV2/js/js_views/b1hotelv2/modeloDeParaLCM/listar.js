acaoDeletarCommom("idb1hv2modelodeparalcm");

$('#modalModelo .salvar').click(function(){
	salvarCommom(
		$(this).data('action'),
		{
			nomeModelo:$('input[name="nomeModelo"]').val(),
			contaDeDebito:$('input[name="contaDeDebito"]').val(),
			contaDeCredito:$('input[name="contaDeCredito"]').val(),
			guid:''
		}
	);
});
