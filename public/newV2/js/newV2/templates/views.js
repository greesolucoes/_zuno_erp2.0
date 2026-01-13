function searchFlags() {
	$('.input-search-flag').on('input', function () {
		const searchText = $(this).val().toLowerCase();

		$(this).closest('.search-flags').next('.config-flags').find('.form-check').each(function () {
			const labelText = $(this).find('label').text().toLowerCase();

			if (labelText.includes(searchText)) {
				$(this).removeClass('d-none');
			} else {
				$(this).addClass('d-none');
			}
		});
	});
}

function changeActionIcons(json) {
	const iconReplacements = {
		"fa-envelope": "mdi:envelope-outline",
		"fa-search": "ic:outline-search",
		"fa-search-2": "mingcute:file-search-line",
		"fa-pencil": "mingcute:edit-line",
		"fa-commenting-o": "icon-park-outline:comment",
		"fa-object-ungroup": "fa6-regular:object-ungroup",
		"fa-bullhorn": "majesticons:megaphone-line",
		"fa-upload": "ph:export-bold",
		"fa-thumbs-o-down": "uil:thumbs-down",
		"fa-thumbs-o-up": "uil:thumbs-up",
		"fa-times-circle-o": "gravity-ui:circle-xmark",
		"fa-times": "ph:trash-simple-bold",
		"fa-trash-o": "ph:trash-simple-bold",
		"fa-check": "ic:outline-check",
		"fa-cogs": "clarity:cog-line",
		"fa-file-pdf-o": "uiw:file-pdf",
		"fa-file-text-o": "akar-icons:file",
		"fa-check-circle-o": "icon-park-outline:check-one",
		"fa-file-code-o": "mdi:file-xml",
		"fa-ban": "ph:trash-simple-bold",
		"fa-clipboard": "wpf:clipboard",
		"fa-refresh": "pepicons-pop:refresh",
		"fa-exclamation-circle": "gravity-ui:circle-exclamation",
		"fa-undo": "iconamoon:do-undo-fill",
		"fa-search-plus": "majesticons:search-plus-line",
		"fa-spinner": "eos-icons:bubble-loading",
		"fa-thermometer-half": "fa:thermometer-2",
		"fa-print": "mingcute:print-line",
		"fa-sign-out": "mdi:sign-out",
		"fa-product-hunt": "cib:product-hunt"
	}

	json.data = json.data.map(item => {
		for (const [oldIconClass, newIcon] of Object.entries(iconReplacements)) {
			const regex = new RegExp(`<i class=['"]fa ${oldIconClass}['"]></i>`, 'g');

			if (item.acoes) {
				item.acoes = item.acoes.replace(regex, `<span class="iconify" data-icon='${newIcon}'></span>`);
			}
			if (item.acao) {
				item.acao = item.acao.replace(regex, `<span class="iconify" data-icon='${newIcon}'></span>`);
			}
			if (item.acoesBotoes) {
				item.acoesBotoes = item.acoesBotoes.replace(regex, `<span class="iconify" data-icon='${newIcon}'></span>`);
			}
		}
		return item;
	});
}

searchFlags();
