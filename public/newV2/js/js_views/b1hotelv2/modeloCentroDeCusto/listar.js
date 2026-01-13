acaoDeletarCommom();

$('#modalModelo .salvar').click(function(){
	salvarCommom(
		$(this).data('action'),
		{
			nomeModelo:$('input[name="nomeModelo"]').val(),
			guid:'',
			isAdd: true
		}
	);
});