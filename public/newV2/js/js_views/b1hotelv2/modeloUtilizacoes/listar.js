acaoDeletarCommom("idB1HV2ModeloUtilizacoes");

$('#modalModelo .salvar').click(function(){
	salvarCommom(
		$(this).data('action'),
		{
			nomeModelo:$('input[name="nomeModelo"]').val(),
			guid:''
		}
	);
});