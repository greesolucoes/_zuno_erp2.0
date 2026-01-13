/**
 * Função de acesso/envio de dados dinâmico...
 *
 * @param async assincrona/sincrona, default true
 * @param page url da página que retornará os dados
 * @param target id do objeto que exibirá as informações (não obrigatório), default null
 * @param dataType tipo do retorno exemplo: text, json...
 * @param param parametros post exemplo: {nome: 'Fabio', sobrenome: 'Santos'}
 * @param callback função que será executada ao final da requisição, não obrigatório, exemplo: function(ret){ alert(ret) }
 */
function getLaravelCsrfToken() {
	const meta = document.querySelector('meta[name="csrf-token"]');
	return meta ? meta.getAttribute('content') : null;
}

function ajaxRequest(async, page, target, dataType, param, callback) {
	param = param ?? {};
	Object.assign(param, tokenCsrf);

	const laravelToken = getLaravelCsrfToken();
	if (laravelToken && (param._token == null || param._token === '')) {
		param._token = laravelToken;
	}

	$.ajax({
		async: async,
		url: page,
		dataType: dataType,
		type: 'POST',
		data: param,
		headers: laravelToken ? { 'X-CSRF-TOKEN': laravelToken } : undefined,
		success: function(data) {
			if (target !== null) {
				$('#' + target).html(data);
			}
			if (callback) {
				callback(data);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			console.log('Error: ' + textStatus + ' | ' + errorThrown);
		}
	});
}
