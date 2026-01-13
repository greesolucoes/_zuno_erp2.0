var auxiliaresMonitoramento = {
    travar: false,
    ajax: null,
    timeout: null
};
var auxiliaresModulos = {};
var auxiliaresMonitoramentoNotas = {
	travar: false,
	ajax: null,
	timeout: null
};

const configLocation = {
	codigo : null,
	nome : null,
	currency : null,
	currencyName : null,
	currencySymbol : null,
	currencyDecimal : null,
	currencyDecimalPoint : null,
	currencyThousandsSeparator : null,
	formatDate : null,
	formatDateTime : null,
	formatDatePicker : null,
	init: (config) => {
		configLocation.codigo = config.codigo,
		configLocation.nome = config.nome,
		configLocation.currency = config.currency,
		configLocation.currencyName = config.currencyName,
		configLocation.currencySymbol = config.currencySymbol,
		configLocation.currencyDecimal = config.currencyDecimal,
		configLocation.currencyDecimalPoint = config.currencyDecimalPoint,
		configLocation.currencyThousandsSeparator = config.currencyThousandsSeparator,
		configLocation.formatDate = config.formatDate
		configLocation.formatDateTime = config.formatDateTime,
		configLocation.formatDatePicker = config.formatDatePicker
	}
};