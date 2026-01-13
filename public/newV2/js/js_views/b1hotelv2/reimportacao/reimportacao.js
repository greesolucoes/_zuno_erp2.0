const input = $("input#b1hv2reimportacao");

const updateDia = () => {
	input.on('dp.change', function() {
        toggleLoading();
        $('#r-info-dia-reimportacao').html('');

		ajaxRequest(
			true,
			$(this).data('urldia'),
			'r-info-dia-reimportacao',
			'text',
			{ 'dia': $(this).val() ?? '' },
			null
		)

        setTimeout(() => toggleLoading(), 2000);
    });
}

const reimportacaoButtons = [
	{"btnId": "btnReimportar", "tipo": "reimportarHotel"},
	{"btnId": "btnReimportarTodos", "tipo": "reimportarTodos"},
	{"btnId": "btnReprocessar", "tipo": "reprocessarHotel"},
	{"btnId": "btnReprocessarTodos", "tipo": "reprocessarTodos"},
	{"btnId": "btnBloquearDia", "tipo": "bloquearDia"}
];

const solicitaReimportacao = () => {
	reimportacaoButtons.forEach(function(btn) {
		let button = $('button[id="' + btn.btnId + '"]');

		button.on('click', function() {
			btn_click(
				button,
				btn.tipo
			)
		});
	})
}

const btn_click = (button, tipo) => {
	let btn = $(button);

	swal({
		title: btn.attr('data-titulo'),
		text: btn.attr('data-texto'),
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: l["continuar!"],
		cancelButtonText: l["cancelar!"]
	}).then(function() {
		toggleLoading();

		ajaxRequest(
			true,
			btn.attr('data-url') + '/' + tipo,
			'r-info-dia-reimportacao',
			'text',
			{
				'dia': input.val(),
				'lcm': ($("#opt-lcm").prop("checked") === true) ? 1 : 0,
				'rh': ($("#opt-rh").prop("checked") === true) ? 1 : 0,
				'barboc': ($("#opt-barboc").prop("checked") === true) ? 1 : 0,
				'recebimentoDetalhado': ($("#opt-recebimentoDetalhado").prop("checked") === true) ? 1 : 0,
				'estatistica': ($("#opt-estatistica").prop("checked") === true) ? 1 : 0,
				'adiantamento': ($("#opt-adiantamento").prop("checked") === true) ? 1 : 0
			},
			function() {
				toggleLoading()
			}
		).catch(swal.noop);
	}).catch(swal.noop)
}

// auto executa ao carregar o script
(function() {
	updateDia();
	solicitaReimportacao();
})();