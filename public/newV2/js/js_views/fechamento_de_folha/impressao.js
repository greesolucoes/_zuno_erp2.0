function printPage() {
    // Desanexar qualquer evento anterior
    $('button.print-preview').off('click');

    // Vincular novamente
    $('button.print-preview').on('click', function() {
        // Desabilita o botão enquanto processa
        $(this).prop("disabled", true);

        // Se preferir, pode alterar a animação/ícone de loading
        const loadingIcon = '<i class="fa fa-spinner fa-pulse fa-fw"></i>';
        $(this).html(loadingIcon + ' Aguarde...');

        // Capturar o HTML dentro de #print-area
        const printContent = document.getElementById('print-area').innerHTML;

        // Abrir nova janela
        const printWindow = window.open('', '_blank', 'width=900,height=600');

        // Construir o HTML com estilos (você pode personalizar ainda mais, se quiser)
        printWindow.document.open();
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Folha de Ponto</title>
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
                    .subtitle {
                        font-size: 14px;
                        color: #111;
                    }
                    .details, .details_alt {
                        font-size: 12px;
                        color: #3B3B3B;
                        margin-bottom: 4px;
                    }
                    .highlight {
                        font-weight: bold;
                        color: #3B3B3B;
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
                    .responsive-table th,
                    .responsive-table td {
                        flex: 1;
                        border: none;
                        padding: 8px 10px 8px 0;
                        text-align: left;
                        font-size: 12px;
                    }
                    .responsive-table th {
                        background-color: #f2f2f2;
                        font-weight: bold;
                        color: #3B3B3B;
                    }
                    .responsive-table tbody tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .responsive-table tbody tr:hover {
                        background-color: #f1f1f1;
                    }
                    .info-totais p {
                        margin: 3px 0;
                        font-size: 12px;
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
                    <!-- Se quiser, pode colocar alguma identificação ou deixar vazio -->
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();

        // Aguarda a janela carregar completamente antes de imprimir
        printWindow.onload = function() {
            printWindow.print();
            printWindow.close();
        };

        // Reabilita o botão após um pequeno delay
        const buttonEl = $(this);
        setTimeout(function() {
            buttonEl.html('Imprimir Novamente');
            buttonEl.prop("disabled", false);
        }, 1500);
    });
}

// Chamar a função para bindar o evento no botão de impressão
printPage();
