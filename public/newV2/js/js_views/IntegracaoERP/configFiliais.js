configFiliais = {
	urlPadrao: null,
	idEmpresas: null,
	idFiliais: null,
	debug:false,
	init: (urlPadrao, idEmpresas, idFiliais)=> {
		// inicia configFiliais
		configFiliais.urlPadrao = urlPadrao
		configFiliais.idEmpresas = idEmpresas
		configFiliais.idFiliais = idFiliais
		configFiliais.listeners()
	},
	listeners: () => {
		// habilita os listeners (depende do jQuery)
		$("#act-update-tipocontato").on("click", function() {
			let url = `${configFiliais.urlPadrao}IntegracaoERPFiliais/getBlingTipoContato/${configFiliais.idFiliais}`
			forceToggleLoading(true)
			$.get(url, function (response) {
				window.location.reload();
			})
		})

		$("#act-update-formapgto").on("click", function() {
			let url = `${configFiliais.urlPadrao}IntegracaoERPFiliais/getBlingFormaPagamento/${configFiliais.idFiliais}`
			forceToggleLoading(true)
			$.get(url, function (response) {
				window.location.reload();
			})
		})

		$("#act-update-utilizacao").on("click", function() {
			let url = `${configFiliais.urlPadrao}IntegracaoERPFiliais/getBlingUtilizacao/${configFiliais.idFiliais}`
			forceToggleLoading(true)
			$.get(url, function (response) {
				window.location.reload();
			})
		})

	},
	dd:(title, message) => {
		// debuga
		if(configFiliais.debug) {
			console.log(title, message)
		}

	}
}
