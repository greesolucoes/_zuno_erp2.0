

function init(){
	$("table.table-exibe").on('click', '.btn-open', function(){
		let modulo = $(this).data('modulo')
		let empresas = $(this).data('content')
		$("#modal-empresas-content").html(empresas)
		$("#modal-empresas-title").html(`<h5><small class="text-secondary">módulo:</small> <span class="badge badge-warning">${modulo}</span></h5>`)
		$("#modal-empresas").modal('show', 600)
	})
}

function pesquisaPersonalizada() {

	var __preparaDadosParaBusca = (url_table, search) => {
		let gets_url = "";
		let modulos = $("#colapseModulos .ch-modulo:checked").map(function(){
			return this.value
		}).get().join();

		if(!is_empty(modulos, 1)) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "modulos=" + encodeURI(modulos);
		}

		if(search !== undefined) {
			if(!is_empty(gets_url, 1)) {
				gets_url += "&";
			}
			gets_url += "q="+encodeURIComponent(search);
			gets_url += "&columns="+encodeURIComponent($(".table").data('colunas_ajax'));
		}

		if(!is_empty(gets_url, 1)) {
			url_table += "?" + gets_url;
		}

		gets_url = null;
		return url_table;
	}

	var __downloadExcell = function () {
		let url = $("#downloadExcel").data("url");
		let search = $('.dataTables_filter input').val();
		window.location.href = __preparaDadosParaBusca(url, search);
	}
	var __acaoAtualizaDataTable = function () {
		const ref_table_search = ".table-exibe";
		let url_table = $(ref_table_search).data("url_principal");
		let dataTable = null;
		$(ref_table_search).data("url_ajax", __preparaDadosParaBusca(url_table));

		$(ref_table_search).each(function (){
			if($.fn.DataTable.isDataTable(this)) {
				dataTable = $(this).DataTable();
				dataTable.clear();
				dataTable.destroy();
			}
		});

		allTables();
		__atualizaChart();
	}

	const __atualizaChart = function() {
		let url = $("#container").data('url');
		let modulos = $("#colapseModulos .ch-modulo:checked").map(function(){
			return this.value
		}).get().join();
		url += "?modulos=" + encodeURI(modulos);
		$.get(url, function(response) {
			let opt = response.map(function(el, idx){
				return [el.modulo, el.qtde];
			})
			chartOptions.series[0].data = opt;
			Highcharts.chart('container', chartOptions);
		})
	}

	$("#buscar").off("click");
	$("#buscar").on("click", function () {
		__acaoAtualizaDataTable();
	});

	$('#checkall').click(function() {
		let checked = $(this).prop('checked');
		$('.ch-modulo').prop('checked', checked);
		__acaoAtualizaDataTable();
	});

	$('.ch-modulo').change(function() {
		__acaoAtualizaDataTable();
	})

	$(".acts").on("click","#downloadExcel", function () {
		__downloadExcell();
	})

	__acaoAtualizaDataTable();
}

const chartOptions = {
	chart: {type: 'column'},
	title: {text: 'Módulos em uso por empresa'},
	subtitle: {text: 'ManyMinds - analítico do uso de módulos'},
	xAxis: {
		type: 'category',
		labels: {
			rotation: -45,
			style: {
				fontSize: '13px',
				fontFamily: 'Verdana, sans-serif'
			}
		}
	},
	yAxis: {
		min: 0,
		title: {text: 'Quantidade'}
	},
	legend: {enabled: true},
	tooltip: {pointFormat: '<b>{point.y:.0f} </b>empresas'},
	series: [{
		name: 'Módulos',
		colors: [
			'#3667c9', '#2f72c3', '#277dbd', '#1f88b7', '#1693b1', '#0a9eaa',
			'#03c69b',  '#00f194'
		],
		colorByPoint: true,
		groupPadding: 0,
		data: [],
		dataLabels: {
			enabled: true,
			rotation: -90,
			color: '#FFFFFF',
			align: 'right',
			format: '{point.y:.1f}', // one decimal
			y: 10, // 10 pixels down from the top
			style: {
				fontSize: '13px',
				fontFamily: 'Verdana, sans-serif'
			}
		}
	}]
}

init();
pesquisaPersonalizada();


