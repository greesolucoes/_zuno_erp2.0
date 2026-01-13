//CRIAR UM FRAMEWORK PRA CONTROLAR TAGS! A LONGO PRAZO O CÓDIGO SE TORNARÁ COMPLEXO E DESNECESSÁRIO!
let iLine = 0;
let infValues = [];

function criaSelects() {
	$("select.select_informacao").select2Reset();
	$("select.codigoDeBarras").select2Reset();
	$("select.select_tipo_codigo_barras").select2Reset();
	$("select.orientacao").select2Reset();
	$("select.fonte").select2Reset();
	$("select.select_informacao").select2Simple(l["selecione"]);
	$("select.codigoDeBarras").select2Simple(l["selecione"]);
	$("select.select_tipo_codigo_barras").select2Simple(l["selecione"]);
	$("select.orientacao").select2Simple(l["selecione"]);
	$("select.fonte").select2Simple(l["selecione"]);

	$("select.select_informacao").off("select2:select");
	$("select.select_informacao").on("select2:select", function (evt) {
		infValues[$($(this).parents("tr")).data("line")].push({
			id: evt.params.data.id,
			text: evt.params.data.text,
		});
		$(this).find("option:selected").remove();

		let obj = $(this);
		$(obj).select2Reset();
		$.each(infValues[$($(this).parents("tr")).data("line")], function (idxOpt, opt) {
			$(obj).append('<option value="' + opt.id + '" selected="selected">' + opt.text + '</option>');
			if($(obj).find("option[value='" + opt.id + "']:not(:selected)").length === 0) {
				$(obj).append('<option value="' + opt.id + '">' + opt.text + '</option>');
			}
		});
		$(obj).select2Simple(l["selecione"]);

		$(obj).trigger("change");
	});
	$("select.select_informacao").off("select2:unselect");
	$("select.select_informacao").on("select2:unselect", function (evt) {
		evt.params.data.element.remove();

		infValues[$($(this).parents("tr")).data("line")] = [];
		$($(this).find("option:selected")).each(function (idxOpt, thisOpt) {
			infValues[$($(this).parents("tr")).data("line")].push({
				id: $(thisOpt).val(),
				text: $(thisOpt).text(),
			});
		})
	});
	$("select.select_informacao").off("select2:clear");
	$("select.select_informacao").on("select2:clear", function () {
		infValues[$($(this).parents("tr")).data("line")] = [];
	});
}

$('table#conteudoTable button.addItens').click(function () {
	iLine++;

	let modelo = $('table#conteudoTable tbody tr').first().html();
	$('table#conteudoTable tbody').append('<tr>' + modelo + '</tr>');
	let limpaCampos = $($('table#conteudoTable tbody tr').last());
	$(limpaCampos).find('input[type="text"]').attr('value', '');
	$(limpaCampos).find('button.removeItens').prop('disabled', false);
	$(limpaCampos).find('select.codigoDeBarras option:selected').attr('selected', false);
	$(limpaCampos).find('select.codigoDeBarras').val("1");
	$(limpaCampos).find('select.orientacao option:selected').attr('selected', false);
	$(limpaCampos).find('select.select_tipo_codigo_barras option:selected').attr('selected', false);
	$(limpaCampos).find('select.select_tipo_codigo_barras').addClass("readonly").attr('readonly', true);
	$(limpaCampos).data("line", iLine);
	infValues[iLine] = [];

	let duplicated = {};
	$($($(limpaCampos).find('select.select_informacao')).find("option")).each(function (idxField, field) {
		if(field.value.length >= 7 && field.value.substring(0, 7) === '[TXTMM]') {
			$(this).remove();
			return;
		}

		if(is_empty(duplicated[field.value], 1)) {
			duplicated[field.value] = 1;
			return;
		}
		duplicated[field.value]++;
	});
	$($($(limpaCampos).find('select.select_informacao')).find("option")).each(function (idxField, field) {
		if(duplicated[field.value] === 1) {
			return;
		}
		$(this).remove();
		duplicated[field.value]--;
	});
	$(limpaCampos).find('select.select_informacao option:selected').attr('selected', false);
	criaSelects();
	actions();
	resetSelectsIndex();
	numberLine();
});

function actions() {
	$('table#conteudoTable button.removeItens').off('click')
	$('table#conteudoTable button.removeItens').on('click', function () {
		var rem = $(this).parents('tr');
		$(rem).fadeOut(270, function () {
			infValues[$(rem).data("line")] = [];
			$(rem).remove();
			resetSelectsIndex();
			numberLine();
		});
	});
	$('table#conteudoTable select.codigoDeBarras').on('change', function () {
		if ($(this).val() == "1") {
			$(this).parents('tr').find('select.select_tipo_codigo_barras').removeClass("readonly").attr('readonly', false);
		} else {
			$(this).parents('tr').find('select.select_tipo_codigo_barras').addClass("readonly").attr('readonly', true);
		}
	});
}

function numberLine() {
	var i = 1;
	$('table#conteudoTable tbody th.number').each(function() {
		$(this).text(i);
		i++;
	});
}

function resetSelectsIndex() {
	var i = 0;
	$('table#conteudoTable tbody tr').each(function () {
		$(this).find('select.select_informacao').attr("name", "informacao[" + i + "][]");
		i++;
	});
}

$('table#conteudoTable tbody tr').each(function (idxTr, thisTr) {
	iLine++;

	$(thisTr).data("line", iLine);
	infValues[iLine] = [];
	$($(thisTr).find("select.select_informacao option:selected")).each(function (idxOpt, thisOpt) {
		infValues[$($(this).parents("tr")).data("line")].push({
			id: $(thisOpt).val(),
			text: $(thisOpt).text(),
		});
	})
})

criaSelects();
actions();
resetSelectsIndex();
numberLine();