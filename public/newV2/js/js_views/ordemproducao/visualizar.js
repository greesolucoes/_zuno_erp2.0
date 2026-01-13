function valorTotalBipagem(){
	valorTotal = 0;
	$('.valorItem').each(function() {
		var valor = $(this).val();
		if(!is_empty(valor)) {
			valor = valor.replace("R$ ", "");
			valor = parseFloat(valor.replace(",", "."));
			valorTotal += valor;
		}
	});
	$('#valorTotal').val(formataDecimal(valorTotal, ".", ",", ".", "R$", true, 2));
}
valorTotalBipagem();