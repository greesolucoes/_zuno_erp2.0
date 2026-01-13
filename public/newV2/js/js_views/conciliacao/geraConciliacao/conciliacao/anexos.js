function createFieldAnexos() {
	recriar($("div#documentos_anexo"));
	$('div#documentos_anexo').allUpload(
		'conteudo-anexos_name[]',
		'conteudo-anexos_blob[]',
		function (obj) {
			if(is_empty($("div.data_views_upload").data("vizualizacao"), 1)) {
				let name = $(obj).parents('.preview-doc').find(".file-name").val();
				let src = $(obj).parents('.preview-doc').find('.doc-zone img').prop('src');

				$(".modal-visualiza_anexo .modal-content .modal-title").text(name);
				$(".modal-visualiza_anexo .modal-content .modal-body").html('<img src="' + src + '" style="max-width:100%; margin:0 auto; display: block;" />');
				$('.modal-visualiza_anexo').modal('show');
			}
			return false;
		},
		'.preview-docs-zone',
		{
			"textUpload": $(".data_views_upload").data("text_upload"),
			"textVisualize": $(".data_views_upload").data("text_visualize_upload"),
			"noDocsText": $(".data_views_upload").data("text_no_docs_upload"),
			"obsText": $(".data_views_upload").data("text_obs_upload"),
		},
		function (obj) {
			let idDoc = $(obj).data("id");
			if(is_empty(idDoc, 1)) {
				idDoc = "";
			}
			$(obj).append('<input class="noEffect file-id" style="display: none;" name="conteudo-anexos_id_interno[]" value="' + idDoc + '" />');

			$(obj).append('<div class="tools-name-doc">' + ($($(obj).find(".file-name")).val()) + '</div>');

			let srcCheck = $($(obj).find(".file-blob")).val().toLowerCase().split(";")[0];
			if(!srcCheck.includes("image")) {
				let fileIcon = "";
				if (srcCheck.includes("text")) {
					fileIcon = isOldLayout ? 'fa fa-file-text-o' : 'fa-regular fa-file-lines';
				} else if(srcCheck.includes("excel")) {
					fileIcon = isOldLayout ? 'fa fa-file-excel-o' : 'fa-regular fa-file-excel';
				} else if(srcCheck.includes("pdf")) {
					fileIcon = isOldLayout ? 'fa fa-file-pdf-o' : 'fa-regular fa-file-pdf';
				} else if(srcCheck.includes("word")) {
					fileIcon = isOldLayout ? 'fa fa-file-word-o' : 'fa-regular fa-file-word';
				} else {
					fileIcon = isOldLayout ? 'fa fa-eye-slash' : 'fa-regular fa-eye-slash';
				}

				$($(obj).find(".text-zone")).html("<i class='" + fileIcon + "' style='font-size: 10em;'></i>");
				fileIcon = null;

				if(is_empty($("div.data_views_upload").data("vizualizacao"), 1)) {
					$($(obj).find(".action-visualize")).remove();
				}
			}
			srcCheck = null;

			if(!is_empty($("div.data_views_upload").data("vizualizacao"), 1)) {
				const url = $("div.data_views_upload").data("url_baixar_anexos");
				let id = $(obj).data('id');
				$($(obj).find(".action-visualize")).attr("href", (url + id));
				$($(obj).find(".action-visualize")).attr("target", "_blank");

				$($(obj).find(".action-visualize")).text($(".data_views_upload").data("text_download_upload"));
			}
		}
	);
	if(!is_empty($("div.data_views_upload").data("vizualizacao"), 1)) {
		$('div#documentos_anexo .link-adiciona-files').remove();
		$('div#documentos_anexo .preview-doc .doc-cancel').remove();
	}
}

function initFields() {
	$("select.select_ajax").select2Ajax();
	$("select.select_ajax").data('init', '');

	createFieldAnexos();
	$('.modal-visualiza_anexo').off('hidden.bs.modal');
	$('.modal-visualiza_anexo').on('hidden.bs.modal', function (e) {
		$($(this).find(".modal-content .modal-title")).text("");
		$($(this).find(".modal-content .modal-body")).text("");
	});
}
initFields();