function printPage(){
	$('button.print-preview').unbind('click');
	$('button.print-preview').on('click', function() {
		$(this).prop("disabled", true);
		if (isOldLayout) $(this).removeClass('fa-print');

		const loadingIcon = (
			isOldLayout
				? '<i class="fa fa-spinner fa-pulse fa-fw"></i>'
				: '<span data-icon="eos-icons:bubble-loading" class="iconify fs-1_8 me-3"></span>'
		);
		$(this).html(loadingIcon + l['aguarde']);

		$('#print-area').printThis({
			pageTitle: l['transferenciaDeDeposito'],
			footer: ''
		});

		var obj = $(this);
		setTimeout(function(){
			if (isOldLayout) {
				$(obj).addClass('fa-print');
			}

			$(obj).html(
				isOldLayout
					? l['imprimirNovamente']
					: '<span data-icon="mingcute:print-line" class="me-2 iconify"></span>' + l['imprimirNovamente']
			);
			$(obj).prop("disabled", false);
		}, 1000);
	});
}

printPage();