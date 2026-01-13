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

		const printContent = document.getElementById('print-area').innerHTML;
		const printWindow = window.open(window.location.href, '_blank', 'width=900,height=600');

		printWindow.document.open();
		printWindow.document.write(`
            <!DOCTYPE html>
			<html lang="pt-BR">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Relatório de Presença</title>
				<style>
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}
			
			body {
				font-family: Arial, sans-serif;
				line-height: 1.5;
				margin: 15px;
				font-size: 14px;
			}
			
			.header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 20px;
			}
		
			.header .logo {
				max-height: 40px;
				margin-left: 10px;
			}
			
			.container {
				max-width: 760px;
				margin: 0 auto;
				padding: 5px;
			}
			
			h1 {
				color: #0380F9;
				margin-bottom: 20px;
				font-size: 18px;
			}
			
			h2 {
				color: #0380F9;
				margin-bottom: 5px;
				font-size: 14px;
			}
			
			.small-text {
				font-size: 8px;
				color: #111;
				margin-bottom: 5px;
			}
			
			.details {
				font-size: 10px;
				color: #3B3B3B;
				margin-bottom: 4px;
			}
			
			.details_alt{
				display: grid;
				grid-template-columns: 1fr 3fr;
				width: 100%;
				max-width: 100%;
			}
			
			.highlight {
				font-weight: bold;
				color: #3B3B3B;
			}
			
			.responsive-table {
				width: 100%;
				border-collapse: collapse;
			}
			
			.responsive-table th, .responsive-table td {
				flex: 1;
				border: none;
				padding: 8px 10px 8px 0;
				text-align: left;
				font-size: 10px;
				background-color: transparent;
        	}
			
			.responsive-table th {
				font-weight: bold;
				color: #3B3B3B;
			}
			
			.total-right {
				margin-top: 8px;
				text-align: right;
				font-size: 12px;
				font-weight: bold;
				padding-right: 12px;
			}
			
			.footer {
				text-align: left;
				font-size: 10px;
				padding: 10px;
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
			}
			
			.tr_alt{
				text-align: start;
			}
			
			.responsive-table {
				display: flex;
				flex-direction: column;
				width: 100%;
				border-collapse: collapse;
				margin-top: 15px;
				overflow-x: auto;
			}
			
			.responsive-table thead {
				display: flex;
				width: 100%;
			}
			
			.responsive-table tbody {
				display: flex;
				flex-direction: column;
				width: 100%;
			}
			
        	.responsive-table tr {
				display: flex;
				width: 100%;
			}
			
			.responsive-table th {
				background-color: #f2f2f2;
				font-weight: bold;
				font-size: 10px;
			}
			
			.responsive-table tbody tr:nth-child(even) {
				background-color: #f9f9f9;
			}
			
			.responsive-table tbody tr:hover {
				background-color: #f1f1f1;
			}
			
			.total-right {
				margin-top: 8px;
				text-align: right;
				font-size: 12px;
				font-weight: bold;
				padding-right: 12px;
			}
			
			.footer {
				text-align: left;
				font-size: 10px;
				padding: 10px;
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
				background-color: #f9f9f9;
			}
				</style>
			</head>
			<body>
				${printContent}
				<div class="footer">
					${window.location.href}
				</div>
			</body>
			</html>
        `);
		printWindow.document.close();

		// espero a janela carregar completamente antes de chamar o print
		printWindow.onload = function() {
			printWindow.print();
			printWindow.close();
		};

		var obj = $(this);
		setTimeout(function() {
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