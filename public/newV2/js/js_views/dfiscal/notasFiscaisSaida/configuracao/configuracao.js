controlaTabelaSuite({
	"ref": "#cadastro_de-para-parceiro",
	"funAposAddItem": function () {
	}
});

$('input[name="depara[replicarFiliais]"]').on('change', function() {
	const select = $('select[name="depara[filiais][]"]').parent('div');

	if ($('input[name="depara[replicarFiliais]"]:checked').val()==2){
		select.removeClass('d-none');
	} else {
		select.addClass('d-none');
	}
});

$('#replicar').on("submit",function(e){
	e.preventDefault();
	let data  = $(this).serialize();
	let url = $(this).attr("action");

	swal({
		title: l["temCertezaDeQueDesejaReplicar?"],
		text: l["replicar"],
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: l["continuar!"],
		cancelButtonText: l["cancelar!"]
	}).then(() => {
		toggleLoading();
		ajaxRequest(true, url, null, 'text', data, function (ret) {
			try{
				let titulo = l["erro"];
				let texto = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
				let tipo = "error";

				if (ret) {
					titulo = l['sucesso!'];
					texto = l["operaçãoEfetuadaComSucesso!"];
					tipo = "success";
				}

				swal(
					titulo,
					texto,
					tipo
				).catch(swal.noop);

				toggleLoading();
			}catch(err){
				swal(
					l["erro!"],
					l["tempoDeRespostaDoServidorEsgotado!"],
					"error"
				).catch(swal.noop);
				forceToggleLoading(0);
			}
		});
	}).catch(swal.noop);
});

