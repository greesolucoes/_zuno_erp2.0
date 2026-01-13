function criaCostumizacoes() {
    $('.select_ajax').select2Ajax();
    $('.select_ajax').data('init', '');

    $('.select_normal').select2Simple();
}

function controlaNameFiles(execute) {
    let __funControlaFile = function (objSelect, valSelect) {
        let __changeName = function (objFile, val) {
        	let valName = 'certificados-cert[' + val + ']';
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
                    $($($(this).parents("tr")).find("select.certificados-filial")).val()
                );
            });
        } else {
            __changeName(
                $($(objSelect).parents("tr")).find("input.certificados-cert"),
				valSelect
            );
        }
    };

    $("select.certificados-filial").off("select2:unselect");
    $("select.certificados-filial").on("select2:unselect", function () {
        __funControlaFile($(this), null);
    });

    $("select.certificados-filial").off("select2:select");
    $("select.certificados-filial").on("select2:select", function () {
        __funControlaFile($(this), $(this).val());
    });

    if(!is_empty(execute, 1)) {
        __funControlaFile();
    }
}

function controlaValidaCertificadoDigital() {
    $('.certificados-validar-btn').off('click');
    $('.certificados-validar-btn').on('click', function(e) {
        let url = $('.data_views').data('url-valida');
        let idFiliais = $(this).data('id_filial');
        let data = null;

        toggleLoading();
        ajaxRequest(true, url, null, 'text', {idFiliais: idFiliais}, function (ret) {
            try {
                $(".valida-certificado-msg").addClass("ocultar");
                $(".valida-certificado-msg").removeClass("alert-info");
				$(".valida-certificado-msg .text-validacao-certificado").removeClass("alert-danger");
				$(".valida-certificado-msg .text-validacao-certificado").removeClass("alert-info");

                data = JSON.parse(ret);
                if (!is_empty(data['error'], 1)) {
                    $(".valida-certificado-msg .text-validacao-certificado").addClass("alert-danger");
                    $(".valida-certificado-msg .text-validacao-certificado").html('<p>' + data['error'] + '</p>');
                } else {
                    $(".valida-certificado-msg .text-validacao-certificado").addClass("alert-info");
                    $(".valida-certificado-msg .text-validacao-certificado").html('<p>' + data['data'].join('<br />') + '</p>');
                }
				$(".valida-certificado-msg").addClass("alert-info");
                $(".valida-certificado-msg").removeClass("ocultar");

                toggleLoading();
            } catch (err) {
                swal(
                    l["erro!"],
                    l["tempoDeRespostaDoServidorEsgotado!"],
                    "error"
                ).catch(swal.noop);
                forceToggleLoading(0);
            }
        });
    });
}

function controlaCheckIsAlterarNSU() {
	$("input#opt-is_importar_nsu").off("change");
	$("input#opt-is_importar_nsu").on("change", function () {
		if(this.checked) {
			$("#opt-is_importar_nsu-hidden").val("1");
			$(".destrava_nsu").prop("readonly", false);
		} else {
			$("#opt-is_importar_nsu-hidden").val("0");
			$(".destrava_nsu").prop("readonly", true);
		}
	});
}

criaCostumizacoes();
controlaTabelaSuite({
    "ref": "#certificados-tabela",
    "funAposAddItem": function () {
        controlaNameFiles();
    }
});
controlaNameFiles(true);
controlaValidaCertificadoDigital();
controlaCheckIsAlterarNSU();