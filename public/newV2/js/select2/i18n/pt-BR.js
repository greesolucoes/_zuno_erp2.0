/*! Select2 4.0.3 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) var e = jQuery.fn.select2.amd;
    return e.define("select2/i18n/pt-BR", [], function () {
        return {
            errorLoading: function () {
				return l["carregandoMaisResultados"]
            }, inputTooLong: function (e) {
                var t = e.input.length - e.maximum, n = l["apague"] + " " + t + " " + (t > 1 ? l["caracteres"] : l["caracter"]);
                return n
            }, inputTooShort: function (e) {
                var t = e.minimum - e.input.length, n = l["digite"] + " " + t + " " + l["ouMaisCaracteres"];
                return n
            }, loadingMore: function () {
                return l["carregandoMaisResultados"]
            }, maximumSelected: function (e) {
                var t = l["vocêSóPodeSelecionar"] + " " + e.maximum;
                return e.maximum == 1 ? t += l.item : t += l.itens, t
            }, noResults: function () {
                return l["nenhumResultadoEncontrado"]
            }, searching: function () {
                return l["busca"]
            }
        }
    }), {define: e.define, require: e.require}
})();
