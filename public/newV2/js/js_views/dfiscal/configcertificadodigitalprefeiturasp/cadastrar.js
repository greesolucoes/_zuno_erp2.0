function criaCostumizacoes() {
    $('.select_ajax').select2Ajax();
    $('.select_ajax').data('init', '');

    $('.select_normal').select2Simple();
}

function controlaNameFiles(execute) {
    let __funControlaFile = function (objSelect, valSelect) {
        let __changeName = function (objFile, val, prefix) {

        	let valName = prefix+'[' + val + ']';
            if(is_empty(val, 1)) {
				valName = '';
            }

			$(objFile).attr('name', valName);
			$(objFile).prop('name', valName);
        };

        if(is_empty(objSelect, 1)) {
            $("input.certificados-cert").each(function () {
                __changeName(
                    $(this),
					getUniqueValues(this),
					"certificados-cert"
                );
            });

			$("input.certificados-cert").each(function () {
				__changeName(
					$(this),
					$($($(this).parents("tr")).find("select.certificados-cert-ssl")).val(),
					"certificados-cert-ssl"
				);
			});

			$("input.certificados-cert").each(function () {
				__changeName(
					$(this),
					$($($(this).parents("tr")).find("select.certificados-cert-key")).val(),
					"certificados-cert-key"
				);
			});
        } else {
            __changeName(
                $($(objSelect).parents("tr")).find("input.certificados-cert"),
				valSelect,
				"certificados-cert"
            );

			__changeName(
				$($(objSelect).parents("tr")).find("input.certificados-cert-ssl"),
				valSelect,
				"certificados-cert-ssl"
			);

			__changeName(
				$($(objSelect).parents("tr")).find("input.certificados-cert-key"),
				valSelect,
				"certificados-cert-key"
			);
        }
    };

    $("select.certificados-filial").off("select2:unselect");
    $("select.certificados-filial").on("select2:unselect", function () {
        __funControlaFile($(this), getUniqueValues(this));
    });

    $("select.certificados-filial").off("select2:select");
    $("select.certificados-filial").on("select2:select", function () {
        __funControlaFile($(this), getUniqueValues(this));
    });

	$("select.certificados-municipio").off("select2:unselect");
	$("select.certificados-municipio").on("select2:unselect", function () {
		__funControlaFile($(this), getUniqueValues(this));
	});

	$("select.certificados-municipio").off("select2:select");
	$("select.certificados-municipio").on("select2:select", function () {
		__funControlaFile($(this), getUniqueValues(this));
	});

	$(document).on("change", 'input.certificados-cnpj', function() {
		__funControlaFile($(this), getUniqueValues(this));
	});

	$(document).on("change", 'input.certificados-cert', function() {
		__funControlaFile($(this), getUniqueValues(this));
	});

	$(document).on("change", 'input.certificados-senha', function() {
		__funControlaFile($(this), getUniqueValues(this));
	});

    if(!is_empty(execute, 1)) {
        __funControlaFile();
    }
}

$(document).on("change", ".certificados-municipio", function() {
	var selectedText = $(this).find("option:selected").text();
	$($(this).parents("tr").find(".certificados-municipio-nome-input-hidden")).val( selectedText );
});

function controlaCheckTecnoSpeed() {
	// Remove qualquer event handler anterior e aplica o novo para o checkbox
	$("input.certificados-tecnospeed").off("change");
	$("input.certificados-tecnospeed").on("change", function () {
		const urlTecnospeed = $(".data_views").data("url_tecnospeed");
		const isChecked = this.checked ? "1" : "0";
		const hiddenField = $(this).parents("tr").find(".certificados-tecnospeed-hidden");

		// Se url_tecnospeed for vazio, impede o checkbox de ser marcado como "sim"
		if (is_empty(urlTecnospeed, 1) && isChecked === "1") {
			this.checked = false;
			hiddenField.val("0");
			return;
		}

		hiddenField.val(isChecked);

		$(this).parents("tr").find(".select.certificados-municipio").val([]).trigger("change");
		setSelectMunicioParams(this);
	});
}

function setSelectMunicioParams(closeComponent) {
	const parentComponent = $(closeComponent).parents("tr");
	const checkedParam = parentComponent.find("input.certificados-tecnospeed").prop('checked') ? "1" : "0";
	const confCnpj = (parentComponent.find("input.certificados-cnpj").val() ?? '0').replace(/\D/g, '');

	// Seleciona o elemento <select> na mesma linha
	const selectMunicipio = parentComponent.find("select.certificados-municipio");
	const currentUrl = selectMunicipio.data('url');
	const newUrl = currentUrl.replace(/\/\d+\/\d+$/, `/${confCnpj}/${checkedParam}`);
	selectMunicipio.attr("data-url", newUrl);

	// Recarrega o Select2 com a nova URL
	selectMunicipio.select2('destroy');
	selectMunicipio.select2Ajax(newUrl);
}

function getUniqueValues(closeComponent) {
	const parentComponent = $(closeComponent).parents("tr");
	const idFilial = parentComponent.find("select.certificados-filial").val();
	const confCnpj = (parentComponent.find("input.certificados-cnpj").val() ?? '0').replace(/\D/g, '');
	const idMunicipio = parentComponent.find("select.certificados-municipio").val();

	return `${idFilial}-${confCnpj}-${idMunicipio}`;
}

function verificaCNPJ() {
	function isCnpjComplete(cnpj) {
		return cnpj.replace(/\D/g, '').length === 14;
	}

	$(document).on("change", 'input.certificados-cnpj', function() {
		const parentComponent = $(this).parents('tr');
		const selectMunicipio = parentComponent.find('select.certificados-municipio');

		if (isCnpjComplete($(this).val())) {
			selectMunicipio.removeClass('readonly');
		} else {
			selectMunicipio.addClass('readonly');
		}

		setSelectMunicioParams(this);
	});
}

controlaCheckTecnoSpeed();
verificaCNPJ();
criaCostumizacoes();
controlaTabelaSuite({
    "ref": "#certificados-tabela",
    "funAposAddItem": function () {
        controlaNameFiles();
		controlaCheckTecnoSpeed();
    }
});
controlaNameFiles(true);