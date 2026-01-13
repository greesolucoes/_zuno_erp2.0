function sempreSelecionadoSeParaResetar() {
	$("label.switch input#valor_reset_sync").off("change");
	$("label.switch input#valor_reset_sync").on("change", function (e) {
		if(!this.checked && $(this).parents("label.switch").hasClass("readonly")){
			$(this).prop("checked", true);
			$(this).trigger("change");
		}
	});
	$("label.switch input#valor_reset_sync").trigger("change");
}

sempreSelecionadoSeParaResetar();