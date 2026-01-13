/**
 * Number.prototype.format(n, x, s, c)
 *
 * @param n integer: Numero de casas decimais
 * @param x integer: Numero de casas miliares
 * @param s mixed:   Delimitador de miliar
 * @param c mixed:   Delimitador de decimas
 */
Number.prototype.format = function(n, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
        num = this.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};

/**
 * Function reporCaracteresNaoSendo.
 * Repõe todos os caracteres da string enviada por nada, a não ser os enviados na variavel chars
 * @param str        String para serem testados os caracteres
 * @param chars      Characters para ignorar o replace
 * @returns {string}
 */
function reporCaracteresNaoSendo(str, chars) {
    var novaStr = "";
    if (str === null || str === '' || str === undefined || chars === null || chars === '' || chars === undefined) return novaStr;
    str = str.toString();
    chars = chars.toString();

    for (var i = 0; i < str.length; i++) {
        var bolRemover = true;

        for (var x = 0; x < chars.length; x++) {
            if (str.charAt(i) === chars.charAt(x)) {
                bolRemover = false;
                break;
            }
        }

        if (!bolRemover) novaStr += str.charAt(i);
    }

    return novaStr;
}

/**
 * Function hasChar.
 * Encontra palavra em string
 * @param str         Frase onde a palavra será procurada para ser feita a pesquisa
 * @param find        Palavra para achar
 * @returns {boolean}
 */
function hasChar(str, find){
    return !(str.indexOf(find) === -1);
}

$.fn.extend({
    fnMascaraNumero: function () {
		function mascaraQtd(e) {
			var i = 0;
			var maxDecimal = $(this).data('maxdecimal');
			var formato = $(this).data('formato') || 'BR';
			if (maxDecimal === null || maxDecimal === '' || maxDecimal === undefined) maxDecimal = 2;

			var valor = $(this).val();
			var countSeparator = 0;

			// Definir separadores com base no formato
			var decimalSeparator = formato === 'USA' ? '.' : ',';
			var thousandSeparator = formato === 'USA' ? ',' : '.';

			if (valor.length !== 0) {
				// Remove caracteres inválidos, exceto o separador de decimais do formato
				valor = valor.replace(new RegExp(`[^0-9${decimalSeparator}]`, 'g'), '');
				countSeparator = $(this).val().match(new RegExp(`\\${decimalSeparator}`, 'g'));

				if (countSeparator !== null) {
					countSeparator = countSeparator.length;

					if (countSeparator > 1) {
						valor = valor.split(decimalSeparator);
						valor = valor[0] + decimalSeparator + valor[1];
					}
					if (countSeparator > 0) {
						valor = valor.split(decimalSeparator);
						valor = valor[0] + decimalSeparator + valor[1].substr(0, maxDecimal);
					}
				}

				var i = 0;
				var novoValor = '';
				if (valor.includes(decimalSeparator)) {
					valor = valor.split(decimalSeparator);
					// Formatar a parte inteira com separador de milhar
					for (i = 1; i <= valor[0].length; i++) {
						if (((valor[0].length - i) % 3) === 0 && (valor[0].length - i) !== 0) {
							novoValor = novoValor + '' + valor[0][(i - 1)] + thousandSeparator;
						} else {
							novoValor = novoValor + '' + valor[0][(i - 1)];
						}
					}
					valor = novoValor + decimalSeparator + valor[1];
				} else {
					// Se não houver decimais, formatar apenas a parte inteira
					for (i = 1; i <= valor.length; i++) {
						if (((valor.length - i) % 3) === 0 && (valor.length - i) !== 0) {
							novoValor = novoValor + '' + valor[(i - 1)] + thousandSeparator;
						} else {
							novoValor = novoValor + '' + valor[(i - 1)];
						}
					}
					valor = novoValor;
				}

				$(this).val(valor);
			}
		}
        function posMascaraQtd(e){
            var valor     = $(this).val();
			var formato = $(this).data('formato') || 'BR';
            var allowZero = $(this).hasClass('allow_zero');

			// Definir separadores com base no formato
			var decimalSeparator = formato === 'USA' ? '.' : ',';
			var thousandSeparator = formato === 'USA' ? ',' : '.';

            var i     = 0;
            if(valor !== '' && valor !== null) {
                if(valor.length == 1) {
                    var charPos = valor.slice(-1);
                    charPos = charPos.replace(/[^0-9]/g, '');
                    valor = valor.substring(0, (valor.length - 1)) + '' + charPos;
                } else{
                    var charPos = valor.slice(-1);
                    charPos = charPos.replace(/[^0-9]/g, '');

                    var charPre = valor.charAt(0);
                    if(charPre === decimalSeparator) charPre = '0'+decimalSeparator;

                    valor = charPre + '' + valor.substring(1, (valor.length - 1)) + '' + charPos;
                }
            }

            var valorTest = valor;
            if(valorTest !== null && valorTest !== '' && valorTest !== undefined) {
                valorTest = valorTest.toString().replace('.', '');
                valorTest = valorTest.toString().replace(decimalSeparator, '.');
                valorTest = parseFloat(valorTest);
            }

            if((!allowZero && valorTest === 0) || valorTest === null || valorTest === '' || valorTest === undefined){
                valor = '';
            } else{
                if(valor.includes(decimalSeparator)){
                    valor    = valor.split(decimalSeparator);
                    valor[2] = parseFloat(valor[1]);
                    if(valor[2] > 0){
                        var novoValor = valor[0] + decimalSeparator;
                        var hasSlice  = 0;

                        valor[2] = valor[1];
                        for(i = valor[1].length; i >= 1; i--){
                            if(valor[2].slice(-1) !== '0' && hasSlice === 0){
                                novoValor = novoValor + '' + valor[2];
                                hasSlice = 1
                            }
                            valor[2] = valor[2].substring(0, (valor[2].length - 1));
                        }

                        valor = novoValor;
                    }else{
                        valor = valor[0];
                    }
                }else{
                    if(valorTest === 0) valor = 0;
                }
            }
            $(this).val(valor);
        }
        $(this)
            .unbind('input', mascaraQtd)
            .on('input', mascaraQtd)
            .unbind('blur', posMascaraQtd)
            .on('blur', posMascaraQtd);
    },
    /**
     * Method fnMascaraNumeroV2.
     * Faz campos serem decimais.
     * Contempla quantidades e dinheiro
     *
     * @data tipoMoeda         tipo da moeda:
     *                          - 'real' adiciona o prefixo 'R$ '
     * @data decimalDelimiter  O caracter que separa os numeros decimais
     * @data thousandDelimiter O caracter que separa os numeros milhares
     * @data maxDecimal        Número de casas decimais permitidas
     * @data canNegativos      Define se pode ou não ser um número negativo
     */
    fnMascaraNumeroV2: function () {
        /**
         * Function preMascaraNumero.
         * Durante ao clicar no campo faz a formatação do número
         * @param e evento da ocorrencia (Não usado)
         */
        function preMascaraNumero(e){
            //decimalDelimiter E thousandDelimiter NÃO PODEM SER NUMEROS, NEM '-', NEM '+'
            //DEFINO AS VARIAVEIS E GARANTO QUE ELAS SERÃO STRINGS VÁLIDAS
            var prefixo = $(this).data('prefixo');
            var sufixo = $(this).data('sufixo');
            var valor     = $(this).val().toString();
            var decimalDelimiter   = $(this).data('decimal_delimiter');
            var thousandDelimiter   = $(this).data('thousand_delimiter');

            //DEFINO VALORES PADRÕES PARA AS VARIAVEIS CASO ELAS SEJAM VAZIAS OU INDEFINIDAS
            if(prefixo === null || prefixo === '' || prefixo === undefined) prefixo = '';
            if(sufixo === null || sufixo === '' || sufixo === undefined) sufixo = '';
            if(thousandDelimiter === null || thousandDelimiter === '' || thousandDelimiter === undefined) thousandDelimiter = '';
            if(decimalDelimiter === null || decimalDelimiter === '' || decimalDelimiter === undefined) decimalDelimiter = ',';
            if(decimalDelimiter === thousandDelimiter) thousandDelimiter = '';

            //DEFINO QUAL SERÁ O PREFIXO E SUFIXO DA MOEDA A SER MOSTRADA
            prefixo = prefixo.toString().trim() + " ";
            sufixo = " " + sufixo.toString().trim();
            if(prefixo.trim() === "") prefixo = "";
            if(sufixo.trim() === "") sufixo = "";

            if(valor === '' || valor === null || valor === undefined) {
                $(this).val(prefixo + sufixo);
                return;
            }

            //DEFINO QUE O VALOR CONTERÁ SOMENTE NÚMEROS, VIRGULAS, TRAÇOS E QUE SERÁ UMAS STRING VÁLIDA
            valor = reporCaracteresNaoSendo(valor, "0123456789-" + decimalDelimiter);
            valor = valor.toString();

            //SUBSTITUO o decimalDelimiter POR PONTO E CONVERTO PARA FLOAT
            valor = valor.replace(decimalDelimiter, ".");
            valor = parseFloat(valor);
            if(is_empty_numeric(valor)) valor = 0;

            //SUBSTITUO O PONTO POR decimalDelimiter NOVAMENTE
            valor = valor.toString();
            valor = valor.replace(".", decimalDelimiter);

            $(this).val(prefixo + valor + sufixo);
        }
        /**
         * Function mascaraNumero.
         * Durante a digitação faz a formatação do número
         * @param e evento da ocorrencia (Não usado)
         */
        function mascaraNumero(e) {
            //decimalDelimiter E thousandDelimiter NÃO PODEM SER NUMEROS, NEM '-', NEM '+'
            //DEFINO AS VARIAVEIS E GARANTO QUE ELAS SERÃO STRINGS VÁLIDAS
            var maxDecimal   = $(this).data('maxdecimal');
            var decimalDelimiter   = $(this).data('decimal_delimiter');
            var thousandDelimiter   = $(this).data('thousand_delimiter');
            var prefixo    = $(this).data('prefixo');
            var sufixo     = $(this).data('sufixo');
            var canNegativos    = $(this).data('bol_negative');
			var maxNum   = $(this).data('maxnum');

            //DEFINO VALORES PADRÕES PARA AS VARIAVEIS CASO ELAS SEJAM VAZIAS OU INDEFINIDAS
            if(thousandDelimiter === null || thousandDelimiter === '' || thousandDelimiter === undefined) thousandDelimiter = '';
            if(decimalDelimiter === null || decimalDelimiter === '' || decimalDelimiter === undefined) decimalDelimiter = ',';
            if(decimalDelimiter === thousandDelimiter) thousandDelimiter = '';
            if(prefixo === null || prefixo === '' || prefixo === undefined) prefixo = '';
            if(sufixo === null || sufixo === '' || sufixo === undefined) sufixo = '';
            if(maxDecimal === null || maxDecimal === '' || maxDecimal === undefined) maxDecimal = '2';
            if(canNegativos === null || canNegativos === '' || canNegativos === undefined || canNegativos === 'false' || canNegativos === false) canNegativos = false;
            else                                                                                                                                 canNegativos = true;
			if(maxNum === null || maxNum === '' || maxNum === undefined) maxNum = null;
			else                                                         maxNum = parseFloat(maxNum);
			if(isNaN(maxNum)) maxNum = null;

            //GARANTO QUE A VARIAVEL SERA UM INTEIRO VÁLIDO
            maxDecimal = parseInt(maxDecimal);
            if(isNaN(maxDecimal)) maxDecimal = 2;

            //DEFINO QUAL SERÁ O PREFIXO E SUFIXO DA MOEDA A SER MOSTRADA
            prefixo = prefixo.toString().trim() + " ";
            sufixo = " " + sufixo.toString().trim();
            if(prefixo.trim() === "") prefixo = "";
            if(sufixo.trim() === "") sufixo = "";

            //DEFINO QUE O VALOR CONTERÁ SOMENTE NÚMEROS, VIRGULAS, TRAÇOS E QUE SERÁ UMAS STRING VÁLIDA
            var valor = reporCaracteresNaoSendo($(this).val().toString(), "0123456789+" + decimalDelimiter + (canNegativos ? '-' : ''));
            valor = valor.toString();
            if(valor === null || valor === '' || valor === undefined) valor = '';

            //SE EXISTIR '+' NA STRING, REMOVE O '-' E DEIXA O VALOR POSITIVO
            var matchStr = hasChar(valor, '+');
            if (matchStr) valor = reporCaracteresNaoSendo(valor, '0123456789' + decimalDelimiter);

            //SE EXISTIR '-' NA STRING, JOGA O '-' PARA O COMEÇO E TRANSFORMA O VALOR PARA NEGATIVO
            var isNegativo = false;
            matchStr = hasChar(valor, '-');
            if (matchStr) {
                isNegativo = true;
                valor = '-' + reporCaracteresNaoSendo(valor, '0123456789' + decimalDelimiter);
            }

            //GARANTO QUE O VALOR APENAS CONTERÁ 1 decimalDelimiter
            var numerosZeroDepoisVirgula = 0;
            matchStr = hasChar(valor, decimalDelimiter);
            if(matchStr){
                valor = valor.split(decimalDelimiter);
                for (var i = 0; i < valor[1].toString().substr(0, maxDecimal).length; i++) {
                    if(valor[1].toString().substr(0, maxDecimal).charAt(i) === '0') {
                        numerosZeroDepoisVirgula++;
                    } else {
                        numerosZeroDepoisVirgula = 0;
                    }
                }

                valor = valor[0].toString() + decimalDelimiter + valor[1].toString().substr(0, maxDecimal);
                valor = valor.toString();
            }

            if(valor === null || valor === '' || valor === undefined) valor = '';

            //SUBSTITUO o decimalDelimiter POR PONTO
            valor = valor.toString();
            valor = valor.replace(decimalDelimiter, ".");

            //SE STRINGS FOREM DIFERENTES DE '-', '-0', '-.', '0', '.' ENTRA AKI (CASO CONTRÁRIO DARIA ERRO)
            if(valor === ''){
                $(this).val(prefixo + sufixo);
                return;
            }
            //TODO: Bolar jeito de fazer funcionar essa parte do código
            // console.log(e.which);
            // console.log(event.which);
            // if(
            //     valor === '-' &&
            //     (event.which === 8 || event.which === 46)
            // ){
            //     $(this).val(prefixo + '0' + sufixo);
            //     return;
            // }
            if(
                valor === '-' ||
                valor === '-0' ||
                valor === '-00'
            ){
                $(this).val(prefixo + '-0' + sufixo);
                return;
            }
            if(valor === '0'){
                $(this).val(prefixo + '0' + sufixo);
                return;
            }
            if(
                valor === '.' ||
                valor === '0.'
            ){
                $(this).val(prefixo + '0' + decimalDelimiter + sufixo);
                return;
            }
            if(
                valor === '-.' ||
                valor === '-0.'
            ){
                valor = '-0';
                valor = valor.toString() + decimalDelimiter;

                $(this).val(prefixo + valor + sufixo);
                return;
            }

            //VERIFICA SE O ULTIMO CARACTER É SEPARAÇÃO DECIMAL
            var commaFinal = false;
            if(valor.toString().substr((valor.length - 1), 1) === '.') commaFinal = true;

            //CONVERTE PARA FLOAT O VALOR E SE O ULTIMO CARACTER TIVER SIDO '.' ADICIONA VIRGULA NO FINAL
            valor = parseFloat(valor);
            if(maxNum != null) {
				if(valor > maxNum) {
					valor = maxNum;
				}
			}
            valor = valor.toString();

            if(commaFinal) valor = valor.toString() + decimalDelimiter;
            else           valor = valor.replace(".", decimalDelimiter);

            if(numerosZeroDepoisVirgula > 0) {
                isNegativo = isNegativo && !hasChar(valor, decimalDelimiter);
                var preValor = (isNegativo ? '-' : '') + valor + (hasChar(valor, decimalDelimiter) ? "" : decimalDelimiter);

                valor = "";
                for(var i = 0; i < numerosZeroDepoisVirgula; i++){
                    valor = '0' + valor;
                }
                valor = preValor + valor;
            }

            $(this).val(prefixo + valor + sufixo);
        }
        /**
         * Function posMascaraNumero.
         * Após sair da digitação faz a formatação do número
         * @param e evento da ocorrencia (Não usado)
         */
        function posMascaraNumero(e){
            //decimalDelimiter E thousandDelimiter NÃO PODEM SER NUMEROS, NEM '-', NEM '+'
            //DEFINO AS VARIAVEIS E GARANTO QUE ELAS SERÃO STRINGS VÁLIDAS
            var maxDecimal   = $(this).data('maxdecimal');
            var maxNum   = $(this).data('maxnum');
            var decimalDelimiter   = $(this).data('decimal_delimiter');
            var thousandDelimiter   = $(this).data('thousand_delimiter');
            var prefixo    = $(this).data('prefixo');
            var sufixo    = $(this).data('sufixo');

            //DEFINO VALORES PADRÕES PARA AS VARIAVEIS CASO ELAS SEJAM VAZIAS OU INDEFINIDAS
            if(thousandDelimiter === null || thousandDelimiter === '' || thousandDelimiter === undefined) thousandDelimiter = '';
            if(decimalDelimiter === null || decimalDelimiter === '' || decimalDelimiter === undefined) decimalDelimiter = ',';
            if(decimalDelimiter === thousandDelimiter) thousandDelimiter = '';
            if(prefixo === null || prefixo === '' || prefixo === undefined) prefixo = '';
            if(sufixo === null || sufixo === '' || sufixo === undefined) sufixo = '';
            if(maxDecimal === null || maxDecimal === '' || maxDecimal === undefined) maxDecimal = '2';
            if(maxNum === null || maxNum === '' || maxNum === undefined) maxNum = null;
            else                                                         maxNum = parseFloat(maxNum);
            if(isNaN(maxNum)) maxNum = null;

            //GARANTO QUE A VARIAVEL SERA UM INTEIRO VÁLIDO
            if(maxDecimal.toString() === "0") {
                maxDecimal = 0;
                decimalDelimiter = "";
            } else {
                maxDecimal = parseInt(maxDecimal);
                if(isNaN(maxDecimal)) maxDecimal = 2;
            }

            //DEFINO QUAL SERÁ O PREFIXO E SUFIXO DA MOEDA A SER MOSTRADA
            prefixo = prefixo.toString().trim() + " ";
            sufixo = " " + sufixo.toString().trim();
            if(prefixo.trim() === "") prefixo = "";
            if(sufixo.trim() === "") sufixo = "";

            //DEFINO QUE O VALOR CONTERÁ SOMENTE NÚMEROS, VIRGULAS, TRAÇOS E QUE SERÁ UMAS STRING VÁLIDA
            var valor = reporCaracteresNaoSendo($(this).val().toString(), "0123456789-" + decimalDelimiter);
            // var valor = $(this).val().replace(/[^0-9,-]/g,'');
            if(valor === null || valor === '' || valor === undefined) valor = '0';
            valor = valor.toString();

            //SUBSTITUO o decimalDelimiter POR PONTO
            if(decimalDelimiter !== "") valor = valor.replace(decimalDelimiter, ".");

            //SE FOR ESSES VALORES, RETORNA NADA PARA O CAMPO
            if(
                valor === '' ||
                valor === '-' ||
                valor === '-0' ||
                valor === '0' ||
                valor === '0.' ||
                valor === '-0.' ||
                valor === '-.'
            ){
                $(this).val('');
                return;
            }

            //CONVERTO O VALOR PARA FLOAT
            valor = parseFloat(valor);
            if(isNaN(valor) || valor === 0){
                $(this).val('');
                return;
            }
            if(maxNum != null) {
            	if(valor > maxNum) {
            		valor = maxNum;
				}
			}
            if(maxDecimal !== 0) {
                valor = valor.format(maxDecimal, 3, thousandDelimiter, decimalDelimiter);
            }

            $(this).val(prefixo + valor + sufixo);
        }
        $(this)
            .off('focus', preMascaraNumero)
            .on('focus', preMascaraNumero)
            .off('input', mascaraNumero)
            .on('input', mascaraNumero)
            .off('blur', posMascaraNumero)
            .on('blur', posMascaraNumero);
    },
    fnMascaraMoeda: function () {
        function mascaraMoeda(e){
            var maxDecimal   = $(this).data('maxdecimal');
            if(maxDecimal === null || maxDecimal === '' || maxDecimal === undefined) maxDecimal = 2;

            var tipoMoeda    = $(this).data('moeda');
            var prefixoMoeda = '';
            if(tipoMoeda === null || tipoMoeda === '' || tipoMoeda === undefined){
                prefixoMoeda = 'R$ ';
            }else{
                if(tipoMoeda === 'real') prefixoMoeda = 'R$ ';
            }

            var valor = $(this).val();
            var countComma = 0;
            if (valor.length !== 0) {
                valor = valor.replace(/[^0-9,]/g,'');

                countComma = $(this).val().match(/,/g);
                if (countComma !== null) {
                    countComma = countComma.length;

                    if (countComma > 1) {
                        valor = valor.split(',');
                        valor = valor[0] + ',' + valor[1];
                    }

                    if(countComma > 0){
                        valor = valor.split(',');
                        valor = valor[0] + ',' + valor[1].substr(0, maxDecimal);
                    }
                }

                var i          = 0;
                var novoValor  = '';
                if(valor.includes(",")){
                    valor = valor.split(',');
                    for(i = 1; i <= valor[0].length; i++){
                        if(((valor[0].length - i) % 3) === 0 && (valor[0].length - i) !== 0){
                            novoValor = novoValor + '' + valor[0][(i - 1)] + '.';
                        }else{
                            novoValor = novoValor + '' + valor[0][(i - 1)];
                        }
                    }
                    valor = novoValor + ',' + valor[1];
                }else{
                    for(i = 1; i <= valor.length; i++){
                        if(((valor.length - i) % 3) === 0 && (valor.length - i) !== 0){
                            novoValor = novoValor + '' + valor[(i - 1)] + '.';
                        }else{
                            novoValor = novoValor + '' + valor[(i - 1)];
                        }
                    }
                    valor = novoValor;
                }

                $(this).val(prefixoMoeda + valor);
            }
        }
        function posMascaraMoeda(e){
            var maxDecimal   = $(this).data('maxdecimal');
            if(maxDecimal === null || maxDecimal === '' || maxDecimal === undefined) maxDecimal = 2;

            var i            = 0;
            var tipoMoeda    = $(this).data('moeda');
            var prefixoMoeda = '';
            if(tipoMoeda === null || tipoMoeda === '' || tipoMoeda === undefined){
                prefixoMoeda = 'R$ ';
            }else{
                if(tipoMoeda === 'real') prefixoMoeda = 'R$ ';
            }

            var valor = $(this).val();
            valor = valor.replace(/[^0-9,]/g, '');

            if(valor !== '' && valor !== null) {
                if(valor.length == 1) {
                    var charPos = valor.slice(-1);
                    charPos = charPos.replace(/[^0-9]/g, '');
                    valor = valor.substring(0, (valor.length - 1)) + '' + charPos;
                } else{
                    var charPos = valor.slice(-1);
                    charPos = charPos.replace(/[^0-9]/g, '');

                    var charPre = valor.charAt(0);
                    if(charPre === ',') charPre = '0,';

                    valor = charPre + '' + valor.substring(1, (valor.length - 1)) + '' + charPos;
                }

                if(valor.includes(",")){
                    valor = valor.split(',');
                    valor[2] = valor[0] + ',' + valor[1];
                    for(i = valor[1].length; i < maxDecimal; i++){
                        valor[2] = valor[2] + '' + '0';
                    }
                    valor = valor[2];
                }else{
                    valor = valor + ',';
                    for(i = 0; i < maxDecimal; i++){
                        valor = valor + '' + '0';
                    }
                }
            }

            var valorTest = valor;
            if(valorTest !== null && valorTest !== '' && valorTest !== undefined){
                valorTest = valorTest.toString().replace('.', '');
                valorTest = valorTest.toString().replace(',', '.');
                valorTest = parseFloat(valorTest);
            }

            if(valorTest === 0 || valorTest === null || valorTest === '' || valorTest === undefined){
                $(this).val('');
            }else{
                var i          = 0;
                var novoValor  = '';
                if(valor.includes(",")){
                    valor = valor.split(',');
                    for(i = 1; i <= valor[0].length; i++){
                        if(((valor[0].length - i) % 3) === 0 && (valor[0].length - i) !== 0){
                            novoValor = novoValor + '' + valor[0][(i - 1)] + '.';
                        }else{
                            novoValor = novoValor + '' + valor[0][(i - 1)];
                        }
                    }
                    valor = novoValor + ',' + valor[1];
                }else{
                    for(i = 1; i <= valor.length; i++){
                        if(((valor.length - i) % 3) === 0 && (valor.length - i) !== 0){
                            novoValor = novoValor + '' + valor[(i - 1)] + '.';
                        }else{
                            novoValor = novoValor + '' + valor[(i - 1)];
                        }
                    }
                    valor = novoValor;
                }

                $(this).val(prefixoMoeda + valor);
            }
        }
        function preMascaraMoeda(e){
            var i            = 0;
            var tipoMoeda    = $(this).data('moeda');
            var prefixoMoeda = '';
            if(tipoMoeda === null || tipoMoeda === '' || tipoMoeda === undefined){
                prefixoMoeda = 'R$ ';
            }else{
                if(tipoMoeda === 'real') prefixoMoeda = 'R$ ';
            }

            var valor = $(this).val();
            if(valor === '' || valor === null || valor === undefined) {
                valor = prefixoMoeda;
            }else{
                if(valor.includes(",")){
                    valor    = valor.split(',');
                    valor[2] = parseFloat(valor[1]);
                    if(valor[2] > 0){
                        var novoValor = valor[0] + ',';
                        var hasSlice  = 0;

                        valor[2] = valor[1];
                        for(i = valor[1].length; i >= 1; i--){
                            if(valor[2].slice(-1) !== '0' && hasSlice === 0){
                                novoValor = novoValor + valor[2];
                                hasSlice = 1
                            }
                            valor[2] = valor[2].substring(0, (valor[2].length - 1));
                        }

                        valor = novoValor;
                    }else{
                        valor = valor[0];
                    }
                }
            }
            $(this).val(valor);
        }
        $(this)
            .unbind('input', mascaraMoeda)
            .on('input', mascaraMoeda)
            .unbind('blur', posMascaraMoeda)
            .on('blur', posMascaraMoeda)
            .unbind('focus', preMascaraMoeda)
            .on('focus', preMascaraMoeda);
    }
});