$('.status, .enviar-sap, .conciliar').on('change', function (){
	if(
		$('#status').prop("checked") ||
		$('#enviar-sap').prop("checked") ||
		$('#conciliar').prop("checked")
	){
		$('.divOcultar').addClass('ocultar');
		if($('#btnIgnorar').prop("checked")){
			$('.btnIgnorar').click();
		}
	}else{
		$('.divOcultar').removeClass('ocultar');
	}
});