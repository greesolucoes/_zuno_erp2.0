function ajaxButtonGenerico(seletor) {
	$(seletor)
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let url = $(obj).data("url") + $(obj).data("id");
			let title = $(obj).data("titulo");
			let text = $(obj).data("texto");

			let tableDataTable = $(".table-exibe").DataTable();

			swal({
				title,
				text,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				$.get(url, function(ret) {
					ret = JSON.parse(ret);
					toggleLoading();

					let titulo = l["erro"];
					let texto = l["éPossívelQueTenhaOcorridoUmErroNoBancoDeDados,PorFavor,EntreEmContatoComOSuporte"];
					let tipo = "error";

					if (ret) {
						titulo = ret['titulo'];
						texto = ret['text'];
						tipo = ret['class'];
					}

					swal(titulo, texto, tipo);
				})

				toggleLoading();
				tableDataTable.draw();
			}).catch(swal.noop);
		});
}

// Função para remover linhas na seção de departamentos
function removeTrDepartamentos (elemento) {
	$(elemento).parents('tr').fadeOut(270, function () {
		$(elemento).parents('tr').remove();
	});
}

// Função para adicionar linhas na seção de departamentos, baseadas em um template
function addDepartamento () {
	$('button[data-add="departamentos"]').click(function(e) {
		e.preventDefault();
		let aba = '.departamentos_add';
		let template = $(aba + ' template').html();
		let index = parseInt($(aba + ' tfoot').attr('data-count')) + 1;
		let html = template.replaceAll("{{n}}", index);

		$(aba + ' tbody').fadeIn(270, function() {
			$(aba + ' tbody').append(html);
		})

		$(aba + ' tfoot').attr('data-count', index);
	})
}

addDepartamento();