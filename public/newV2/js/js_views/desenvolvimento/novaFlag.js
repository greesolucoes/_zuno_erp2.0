function criaSelects(){
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');
}

function text2noneFormat(txt=''){
	let letterNumber= /([\u0300-\u036f]|[^0-9a-zA-Z_])/g;
	return txt.normalize("NFD").replace(letterNumber, "");
}

function load_eventos(){
	$("#nomeTabela").keyup(function (){
		this.value= text2noneFormat(this.value);
	});
}

load_eventos();
criaSelects();