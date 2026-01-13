let debounceAjaxSelect2 = debounceSelect2(function (params, success, failure, urlPesquisa, select) {
	Object.assign(params.data, tokenCsrf);
	const laravelToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || null;
	if (laravelToken && (params.data._token == null || params.data._token === '')) {
		params.data._token = laravelToken;
	}
	if (window.__DEBUG_SELECT2_AJAX) {
		console.log('%c[select2Ajax] request', 'color:#0b7285', { url: urlPesquisa, data: params.data });
	}
	jQuery.ajax({
		url: urlPesquisa,
		data: params.data,
		dataType: 'json',
		cache: false,
		type: 'POST',
		headers: laravelToken ? { 'X-CSRF-TOKEN': laravelToken } : undefined,
		success: function (response) {
			if (window.__DEBUG_SELECT2_AJAX) {
				console.log(
					'%c[select2Ajax] response',
					'color:#0b7285',
					{ url: urlPesquisa, size: Array.isArray(response) ? response.length : null, response: response }
				);
			}
			success(response);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log('%c ------------------------ SELECT2 ERROR ------------------------', 'color: #990000');
			console.log('%c URL: ' + urlPesquisa, 'color: #990000');
			console.log('%c Code: ' + jqXHR.status, 'color: #990000');
			console.log('%c Message: ' + jqXHR.statusText, 'color: #990000');
			console.log('%c Text: ' + jqXHR.responseText, 'color: #990000');
			if (jqXHR.responseJSON) {
				console.log('%c JSON: ', 'color: #990000', jqXHR.responseJSON);
			}
			console.log(select);
			console.log('%c ------------------------ SELECT2 ERROR ------------------------', 'color: #990000');
			console.log(' ');
			success([]);
		}
	})
}, 500);

let debounceResizeAjax = debounceSelect2(function (obj) {
	$(obj).select2Reset();
	$(obj).select2Ajax();
}, 500);
let debounceResizeSimple = debounceSelect2(function (obj, placeholder, search) {
	$(obj).select2Reset();
	$(obj).select2Simple(placeholder, search);
}, 500);

function debounceSelect2(func, wait, immediate) {
	let timeout;
	return function () {
		let context = this, args = arguments;
		let later = function () {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		let callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
}

$.fn.extend({
	select2AjaxProdutos: function () {
		//O retorno deve ser um Array JSON com id e text
		//[{"id": 123,"text": "texto1"}, {"id": 124,"text": "texto2"}]

		//resolução de resize da tela
		if ($(this).length > 0 && !$(this).hasClass('select2Resize')) {
			let obj = this;
			$(window).resize(function () {
				debounceResizeAjax(obj);
			});
			$(this).addClass('select2Resize');
			obj = null;
		}
		//resolução de resize da tela

		$(this).css('width', '100%');

		$(this).each(function () {
			var urlPesquisa = $(this).data('url');
			var placeholder = $(this).data('placeholder');
			if (!placeholder && $(this).find('option[value=""]').length) {
				placeholder = $(this).find('option[value=""]').text();
			}
			var limiteCaracteres = $(this).data('limiteCaracteres');
			var init = $(this).data('init');

			if(limiteCaracteres == null || limiteCaracteres == undefined) limiteCaracteres = 9;
			if(urlPesquisa != null && urlPesquisa != undefined) {
				for(var i = 0; i < 2; i++) {    //resolução do bug de reload caso a página seja recarregada dinamicamente
					$(this).each(function () {
						$(this).select2Reset();
						var select = $(this);

						if($(this).prop('multiple')) {  //parametrização caso seja um select multiplo
							var ph = placeholder;
							if(placeholder == null || placeholder == undefined) ph = l["selecioneUmOuMaisValores"];
							var maximumSelectionLength = null;
							if ($(this).data('limitselect')) {
								try {
									maximumSelectionLength = parseInt($(this).data('limitselect'));
								} catch (e) {
								}
							}
							var select = $(this);
							let opts = {
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: true,
								minimumInputLength: 0,
								minimumResultsForSearch: 1, //altera o minimo de registro no search, buga se trazer menos
								maximumSelectionLength: maximumSelectionLength,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},
									data: function (params) {
										return {
											pesquisa: params.term
										};
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
												// return {results: data['content']};
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
									}
								},
								templateSelection: function (data) {
									var txt = '';
									$(select).find('option').each(function () {
										if($(this).prop('value') == data.id){
											txt = $(this).text();
											return false;
										}
									});
									if (txt.length > limiteCaracteres) txt = txt.substring(0, limiteCaracteres) + '...';
									return txt;
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}
									return data.text;
								}
							}
							$(this).select2(opts);
						} else{                         //parametrização caso seja um select simples
							var ph = placeholder;
							if(placeholder == null || placeholder == undefined) ph = l["selecioneUmValor"];
							$(this).select2({
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: false,
								minimumInputLength: 0,
								minimumResultsForSearch: null,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},

									data: function (params) {
										return {
											pesquisa: params.term
										};
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
												// return {results: data['content']};
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
										// return {results: data};
									}
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}
									return data.text;
								}
							});
						}
					});
				}
			} else{
				$(this).html($('<option value=""></option>').text(l["adicioneUmaUrlParaPesquisaAjax!"])).show().parent().find('div.bootstrap-select').remove();
			}

			//alterar escopo dos options
			function formatter(data){
				var ret = [];
				$.each(data, function (key, value) {
					ret.push({
						'id': value.idProdutos,
						'text': value.codigoProdutos + ' - ' + value.nomeProdutos,
						'data': {
							'precofixo': value.precoFixo,
							'umfixanome': value.nomeUMProduto,
							'umfixa': value.idUMProduto
						}
					});
				});
				return {results: ret};
			}

			//alterar escopo dos options
			if (init != undefined && init != null && init != '') {
				// console.log(init);
				$(this).append(
					$('<option/>')
					.attr('value', init.idProdutos)
					.data('precofixo', init.precoFixo)
					.data('umfixanome', init.nomeUMProduto)
					.data('umfixa', init.idUMProduto)
					.text(init.codigoProdutos + ' - ' + init.nomeProdutos)
				).val(init.idProdutos).trigger('change');
			}
		});
	},
	select2Ajax: function (newUrl) {

		//resolução de resize da tela
		if ($(this).length > 0 && !$(this).hasClass('select2Resize')) {
			let obj = this;
			$(window).resize(function () {
				debounceResizeAjax(obj);
			});
			$(this).addClass('select2Resize');
			obj = null;
		}
		//resolução de resize da tela

		$(this).css('width', '100%');

		$(this).each(function () {
			var urlPesquisa = newUrl || $(this).data('url');
			var placeholder = $(this).data('placeholder');
			if (!placeholder && $(this).find('option[value=""]').length) {
				placeholder = $(this).find('option[value=""]').text();
			}
			var limiteCaracteres = $(this).data('limiteCaracteres');
			var dropdownParent = $(this).data('dropdown');
			var init = $(this).data('init');
			var opts = {};

			if(limiteCaracteres == null || limiteCaracteres == undefined) limiteCaracteres = 9;
			if(urlPesquisa != null && urlPesquisa != undefined) {
					for(var i = 0; i < 2; i++) {    //resolução do bug de reload caso a página seja recarregada dinamicamente
						$(this).each(function () {
							$(this).select2Reset();
							var select = $(this);

							if($(this).prop('multiple')) {  //parametrização caso seja um select multiplo
								var ph = placeholder;
								if(placeholder == null || placeholder == undefined) ph = l["selecioneUmOuMaisValores"];
							var maximumSelectionLength = null;
							if ($(this).data('limitselect')) {
								try {
									maximumSelectionLength = parseInt($(this).data('limitselect'));
								} catch (e) {
								}
							}
							opts = {
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: true,
								minimumInputLength: 0,
								minimumResultsForSearch: 1, //altera o minimo de registro no search, buga se trazer menos
								maximumSelectionLength: maximumSelectionLength,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},

									data: function (params) {
										var payload = { pesquisa: params.term };
										var empresaId = select.data('empresa_id');
										var localId = select.data('local_id');
										if (!is_empty(empresaId, 1)) payload.empresa_id = empresaId;
										if (!is_empty(localId, 1)) payload.local_id = localId;
										return payload;
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
									}
								},
								templateSelection: function (data) {
									var txt = '';
									$(select).find('option').each(function () {
										if($(this).prop('value') == data.id){
											txt = $(this).text();
											return false;
										}
									});
									if (txt.length > limiteCaracteres) txt = txt.substring(0, limiteCaracteres) + '...';
									return txt;
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}
									return data.text;
								}
							};
							if(!is_empty(dropdownParent, 1)) {
								opts['dropdownParent'] = $(dropdownParent);
							}
							$(this).select2(opts);
						} else{                         //parametrização caso seja um select simples
							var ph = placeholder;
							if(placeholder == null || placeholder == undefined) ph = l["selecioneUmValor"];
							opts = {
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: false,
								minimumInputLength: 0,
								minimumResultsForSearch: null,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},

									data: function (params) {
										var payload = { pesquisa: params.term };
										var empresaId = select.data('empresa_id');
										var localId = select.data('local_id');
										if (!is_empty(empresaId, 1)) payload.empresa_id = empresaId;
										if (!is_empty(localId, 1)) payload.local_id = localId;
										return payload;
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
												// return {results: data['content']};
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
										// return {results: data};
									}
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}

									return data.text;
								}
							};
							if(!is_empty(dropdownParent, 1)) {
								opts['dropdownParent'] = $(dropdownParent);
							}
							$(this).select2(opts);
						}
					});
				}
			} else{
				$(this).html($('<option value=""></option>').text(l["adicioneUmaUrlParaPesquisaAjax!"])).show().parent().find('div.bootstrap-select').remove();
			}

			//alterar escopo dos options
			function formatter(data){
				var ret = [];
				$.each(data, function (key, value) {
					ret.push({
						'id': value.id,
						'text': decodeHTMLEntities(value.text)
					});
				});
				return {results: ret};
			}

			//alterar escopo dos options
			if (init != undefined && init != null && init != '') {
				$(this).append(
					$('<option/>')
					.attr('value', init.id)
					.text(init.text)
				).val(init.id).trigger('change');
			}
		});
	},
	select2AjaxComCodigo: function () {

		//resolução de resize da tela
		if ($(this).length > 0 && !$(this).hasClass('select2Resize')) {
			let obj = this;
			$(window).resize(function () {
				debounceResizeAjax(obj);
			});
			$(this).addClass('select2Resize');
			obj = null;
		}
		//resolução de resize da tela

		$(this).css('width', '100%');

		$(this).each(function () {
			var urlPesquisa = $(this).data('url');
			var placeholder = $(this).data('placeholder');
			if (!placeholder && $(this).find('option[value=""]').length) {
				placeholder = $(this).find('option[value=""]').text();
			}
			var limiteCaracteres = $(this).data('limiteCaracteres');
			var init = $(this).data('init');

			if(limiteCaracteres == null || limiteCaracteres == undefined) limiteCaracteres = 9;
			if(urlPesquisa != null && urlPesquisa != undefined) {
				for(var i = 0; i < 2; i++) {    //resolução do bug de reload caso a página seja recarregada dinamicamente
					$(this).each(function () {
						$(this).select2Reset();

						if($(this).prop('multiple')) {  //parametrização caso seja um select multiplo

							var ph = placeholder;
							if(placeholder == null || placeholder == undefined) ph = l["selecioneUmOuMaisValores"];
							var maximumSelectionLength = null;
							if ($(this).data('limitselect')) {
								try {
									maximumSelectionLength = parseInt($(this).data('limitselect'));
								} catch (e) {
								}
							}
							var select = $(this);
							$(this).select2({
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: true,
								minimumInputLength: 0,
								minimumResultsForSearch: 1, //altera o minimo de registro no search, buga se trazer menos
								maximumSelectionLength: maximumSelectionLength,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},

									data: function (params) {
										return {
											pesquisa: params.term
										};
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
									}
								},
								templateSelection: function (data) {
									var txt = '';
									$(select).find('option').each(function () {
										if($(this).prop('value') == data.id){
											txt = $(this).text();
											return false;
										}
									});
									if (txt.length > limiteCaracteres) txt = txt.substring(0, limiteCaracteres) + '...';
									return txt;
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}
									return data.text;
								}
							});
						} else{                         //parametrização caso seja um select simples
							var ph = placeholder;
							if(placeholder == null || placeholder == undefined) ph = l["selecioneUmValor"];
							$(this).select2({
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: false,
								minimumInputLength: 0,
								minimumResultsForSearch: null,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},

									data: function (params) {
										return {
											pesquisa: params.term
										};
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
												// return {results: data['content']};
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
										// return {results: data};
									}
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}
									return data.text;
								}
							});
						}
					});
				}
			} else{
				$(this).html($('<option value=""></option>').text(l["adicioneUmaUrlParaPesquisaAjax!"])).show().parent().find('div.bootstrap-select').remove();
			}

			//alterar escopo dos options
			function formatter(data){
				var ret = [];
				$.each(data, function (key, value) {
					ret.push({
						'id': value.idProdutos,
						'text': value.codigoProdutos + ' - ' + value.nomeProdutos
					});
				});
				return {results: ret};
			}

			//alterar escopo dos options
			if (init != undefined && init != null && init != '') {
				$(this).append(
					$('<option/>')
					.attr('value', init.id)
					.text(init.text)
				).val(init.id).trigger('change');
			}
		});
	},
	select2AjaxProdutosHt: function () {
		//O retorno deve ser um Array JSON com id e text
		//[{"id": 123,"text": "texto1"}, {"id": 124,"text": "texto2"}]

		//resolução de resize da tela
		if ($(this).length > 0 && !$(this).hasClass('select2Resize')) {
			let obj = this;
			$(window).resize(function () {
				debounceResizeAjax(obj);
			});
			$(this).addClass('select2Resize');
			obj = null;
		}
		//resolução de resize da tela

		$(this).css('width', '100%');

		$(this).each(function () {
			var urlPesquisa = $(this).data('url');
			var placeholder = $(this).data('placeholder');
			if (!placeholder && $(this).find('option[value=""]').length) {
				placeholder = $(this).find('option[value=""]').text();
			}
			var limiteCaracteres = $(this).data('limiteCaracteres');
			var idEmpresa = $(this).data('idempresa');
			var init = $(this).data('init');

			if(limiteCaracteres == null || limiteCaracteres == undefined) limiteCaracteres = 9;
			if(urlPesquisa != null && urlPesquisa != undefined) {
				for(var i = 0; i < 2; i++) {    //resolução do bug de reload caso a página seja recarregada dinamicamente
					$(this).each(function () {
						$(this).select2Reset();

						if($(this).prop('multiple')) {  //parametrização caso seja um select multiplo

							var ph = placeholder;
							if(placeholder == null || placeholder == undefined) ph = l["selecioneUmOuMaisValores"];
							var maximumSelectionLength = null;
							if ($(this).data('limitselect')) {
								try {
									maximumSelectionLength = parseInt($(this).data('limitselect'));
								} catch (e) {
								}
							}
							var select = $(this);
							$(this).select2({
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: true,
								minimumInputLength: 0,
								minimumResultsForSearch: 1, //altera o minimo de registro no search, buga se trazer menos
								maximumSelectionLength: maximumSelectionLength,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},
									data: function (params) {
										return {
											pesquisa: params.term,
											idEmpresa: idEmpresa,
										};
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
									}
								},
								templateSelection: function (data) {
									var txt = '';
									$(select).find('option').each(function () {
										if($(this).prop('value') == data.id){
											txt = $(this).text();
											return false;
										}
									});
									if (txt.length > limiteCaracteres) txt = txt.substring(0, limiteCaracteres) + '...';
									return txt;
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}
									return data.text;
								}
							});
						} else {                         //parametrização caso seja um select simples
							var ph = placeholder;
							if(placeholder == null || placeholder == undefined) ph = l["selecioneUmValor"];
							$(this).select2({
								placeholder: ph,
								language: "pt-BR",
								allowClear: true,
								multiple: false,
								minimumInputLength: 0,
								minimumResultsForSearch: null,
								ajax: {
									transport: function (params, success, failure) {
										debounceAjaxSelect2(params, success, failure, urlPesquisa, select);
									},

									data: function (params) {
										return {
											pesquisa: params.term,
											idEmpresa: idEmpresa,
										};
									},
									processResults: function (data) {
										if (
											typeof data['type'] !== 'undefined' &&
											typeof data['message'] !== 'undefined' &&
											typeof data['content'] !== 'undefined'
										) {
											if (data['type'] == 'success') {
												return formatter(data['content']);
												// return {results: data['content']};
											} else {
												console.log('Erro: ' + data['content']);
												return {results: []};
											}
										}
										return formatter(data);
										// return {results: data};
									}
								},
								templateResult: function (data, container) {
									if (data.element) {
										$(container).addClass($(data.element).attr("class"));
									}
									return data.text;
								}
							});
						}
					});
				}
			} else{
				$(this).html($('<option value=""></option>').text(l["adicioneUmaUrlParaPesquisaAjax!"])).show().parent().find('div.bootstrap-select').remove();
			}

			//alterar escopo dos options
			function formatter(data){
				var ret = [];
				$.each(data, function (key, value) {
					ret.push({
						'id': value.idProdutos,
						'text': value.codigoProdutos + ' - ' + value.nomeProdutos
					});
				});
				return {results: ret};
			}

			//alterar escopo dos options
			if (init != undefined && init != null && init != '') {
				$(this).append($('<option/>').attr('value', init.idItem).text(init.codigoItem + ' - ' + init.descricaoItem)).val(init.idItem).trigger('change');
			}
		});
	},
	select2Simple: function (placeholder, search, optExt=[]) {

		//resolução de resize da tela
		if ($(this).length > 0 && !$(this).hasClass('select2Resize')) {
			let obj = this;
			$(window).resize(function () {
				debounceResizeSimple(obj, placeholder, search);
			});
			$(this).addClass('select2Resize');
			obj = null;
		}
		//resolução de resize da tela

		$(this).css('width', '100%');

		var sendPlaceHolder = placeholder;
		if(is_empty(search, 1)) {
			search = 1
		} else {
			search = Infinity;
		}
		$(this).each(function () {
			if (!placeholder) {
				if ($(this).data('placeholder')) {
					sendPlaceHolder = $(this).data('placeholder');
				} else if ($(this).find('option[value=""]').length) {
					sendPlaceHolder = $(this).find('option[value=""]').text();
				} else {
					sendPlaceHolder = l["selecione"];
				}
			} else {
				sendPlaceHolder = placeholder;
			}
			let multiple = !is_empty($(this).prop('multiple'), 1);
			let tags = !is_empty($(this).data('tags'), 1);
			let esconderSelecionados = !is_empty($(this).data('esconder_selecionados'), 1);
			let permitirCriacaoTags = !is_empty($(this).data('permitir_criacao_tags'), 1);
			let idModal = !is_empty($(this).data('id_modal'), 1) ? $(this).data('id_modal') : null;
			let prefixoTags = !is_empty($(this).data('prefixo_tags'), 1) ? $(this).data('prefixo_tags') : null;
			let maximumSelectionLength = null;
			if ($(this).data('limitselect')) {
				try {
					maximumSelectionLength = parseInt($(this).data('limitselect'));
				} catch (e) {
				}
			}

			let opts = {
				containerCss: {"display": "block"},
				allowClear: (typeof optExt['allowClear']== 'boolean') ? optExt['allowClear'] : true,
				placeholder: placeholder,
				language: _lang,
				maximumSelectionLength: maximumSelectionLength,
				dropdownParent: is_empty(idModal, 1) ? null : $('#' + idModal),
				templateResult: function (data, container) {
					if (data.element) {
						$(container).addClass($(data.element).attr("class"));
					}
					return data.text;
				}
			};
			if(multiple) {
				if(tags) {
					opts['tags'] = true;
					opts['tokenSeparators'] = [',', ';'];
				} else {
					opts['multiple'] = true;
				}
				if(esconderSelecionados) {
					opts['templateResult'] = function (data, container) {
						if (data.element) {
							$(container).addClass($(data.element).attr("class"));
							if (data.element.selected) {
								return;
							}
						}
						return data.text;
					};
				}
				if(permitirCriacaoTags) {
					opts['createTag'] = function (params) {
						let termTxt = $.trim(params.term).split("'").join("").split('"').join("");
						if (is_empty(termTxt, 0)) {
							return null;
						}
						let termId = (!is_empty(prefixoTags, 1) ? prefixoTags : "") + termTxt;

						return {
							id: termId,
							text: termTxt,
							newTag: true // add additional parameters
						}
					};
				}
				opts['minimumResultsForSearch'] = search;
			}
			// checa se tem o elemento pai do modal (resolve BO de select2 no modal)
			if(!is_empty(optExt['dropdownParent'], 1)) {
				opts['dropdownParent'] = $(optExt['dropdownParent']);
			}
			$(this).select2Reset();
			$(this).select2(opts);
		});

	},
	select2Reset: function () {
		if ($(this).hasClass('select2-hidden-accessible') || $(this).data('select2')) {
			$(this.selector).select2('destroy');
		}
		$(this).show();

		// Remove apenas elementos injetados pelo Select2/Bootstrap-select, sem apagar label/ajudas do campo.
		const $parent = $(this).parent();
		$parent.find('span.select2, span.select2-container, span.select2-container--default').remove();
		$parent.find('div.select2-container, div.select2-container--default').remove();
		$parent.find('div.bootstrap-select').remove();
	}
});
