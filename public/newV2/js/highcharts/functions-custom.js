function criaHighchartsBars(id, titulo, subtitulo, xTexto, xCategorias, yText, dadosGrafico) {
    Highcharts.chart(id, {
        chart: {
            type: 'column'
        },

        title: {
            text: titulo
        },

        subtitle: {
            text: subtitulo
        },

        xAxis: {
            title: {
                text: xTexto
            },

            // ALTERAR
            categories: $.parseJSON(xCategorias)
        },

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        // layout: 'horizontal',
                        // align: 'center',
                        // verticalAlign: 'bottom',
                        enable: false
                    }
                }
            }]
        },

        yAxis: {
            title: {
                text: yText
            },
            labels: {
                format: '{value}'
            },
            min: 0,
            // max: 100,
            opposite: false
        },

        plotOptions: {
            dashStyle: 'solid',
            series: {
                animation: {
                    duration: 2000
                },
                label: {
                    connectorAllowed: true
                }
            }
        },

        series: [{
            name: 'Pedidos',
            // color: '#FF0000',
            // marker: {
            //     symbol: "diamond",
            //     fillColor: '#000000'
            // },

            // ALTERAR
            data: $.parseJSON(dadosGrafico)
        }],
        legend: {
            // layout: 'vertical',
            // align: 'right',
            // verticalAlign: 'middle',
            enabled: false
        },
        credits: {
            enabled: false
        }
    });
}