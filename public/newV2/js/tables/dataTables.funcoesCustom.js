var oLanguage = {
    "sEmptyTable": l["nenhumRegistroEncontrado"],
    "sInfo": l["mostrandoDeStartAtéEndDeTotalRegistros"],
    "sInfoEmpty": l["mostrando0Até0De0Registros"],
    "sInfoFiltered": "(" + l["filtradosDeMaxRegistros"] + ")",
    "sInfoPostFix": "",
    "sInfoThousands": ".",
    "sLengthMenu": l["menuResultadosPorPágina"],
    "sLoadingRecords": l["carregando"],
    "sProcessing": l["processando"],
    "sZeroRecords": l["nenhumRegistroEncontrado"],
    "sSearch": "<i class=\"fa fa-search\" aria-hidden=\"true\"></i>",
    "sSearchPlaceholder": l["pesquisar"],
    "oPaginate": {
        "sFirst": "<i class=\"fa fa-angle-double-left\"></i>",
        "sLast": "<i class=\"fa fa-angle-double-right\"></i>",
        "sNext": "<i class=\"fa fa-angle-right\"></i>",
        "sPrevious": "<i class=\"fa fa-angle-left\"></i>"
    },
    "oAria": {
        "sSortAscending": ": " + l["ordenarColunasDeFormaAscendente"],
        "sSortDescending": ": " + l["ordenarColunasDeFormaDescendente"]
    }
};
const scrollY = isOldLayout ? "50vh" : "auto";
var jsonPorTabela = [];
$(function () {
	if(parseInt(EXECUTA_ALLTABLES_JS) > 0) allTables();
});

function retornaArrayComColunas(idx, arr, tipos_col, ajaxConfig, colunas_ocultar_view) {
    var colunas = [];
    if(!is_empty(tipos_col, 1)) colunas = tipos_col.split(",");

    if(!is_empty(ajaxConfig, 1)) {
        ajaxConfig['colunasArr'] = ajaxConfig['colunas'].split(",");
        ajaxConfig['colunas'] = null;

        arr["ajax"] = {};
        arr["ajax"]['url'] = ajaxConfig['url'];
		arr["ajax"]['data'] = function(data) {
			Object.assign(data, tokenCsrf);
		};
        arr["ajax"]['error'] = function (jqXHR, textStatus, errorThrown) {
            // console.log(jqXHR);
        };
        arr["ajax"]["dataSrc"] = function(json) {
			if (!isOldLayout) {
				changeActionIcons(json);
			}
			jsonPorTabela[idx] = json.data;
			return json.data;
		}
        arr["ajax"]['type'] = 'post';
        arr["processing"] = true;
        arr["serverSide"] = true;

        arr["columns"] = [];
        $.each(ajaxConfig['colunasArr'], function(indexCol, coluna){
			// A coluna ‘observacoes’ - os valores serão tratados como uma string de texto
			if (coluna === 'observacoes') {
				arr["columns"].push({"data": coluna, "render": $.fn.dataTable.render.text()});
			} else {
				arr["columns"].push(
					{
						"data": coluna,
						"render": function ( data, type, row ) {
							// Não permite a tag <img> no datatable
							if (/<img[^>]*>/g.test(data)) {
								return encodeHTMLEntities(data);
							}else {
								return data;
							}
						}
					}
				);
			}
        });

		//funcionalidade para ocultar colunas na tela mas que serão usadas no relatório excel pela função save2excel()
		//ex.: data-colunas_ocultar_view="0,1,3,6"
		if(colunas_ocultar_view){
			var colunasOcultarView= [];
			if(colunas_ocultar_view.toString().includes(',')){
				colunasOcultarView= colunas_ocultar_view.split(",");
				colunasOcultarView= colunasOcultarView.map(function(i){
					return parseInt(i);
				});
			}else{
				colunasOcultarView.push(parseInt(colunas_ocultar_view));
			}
			arr["createdRow"] = function(row, data, dataIndex){
				colunasOcultarView.forEach(function(col) {
					col++;
					$(row).find(':nth-child('+ col +')').addClass('ocultar');
				});
			};
		}
    }

	if(!is_empty(colunas, 1)){
        if(arr['columns'] === undefined) arr["aoColumns"] = [];

        $.each(colunas, function(indexCol,coluna){
            switch (coluna.toString().trim()){
                case "d pt-br":
                case "ds pt-br":
                case "date pt-br":
                case "dates pt-br":
                case "data pt-br":
                case "datas pt-br":
                    if(arr['columns'] === undefined) arr["aoColumns"].push({"sType": "date-eu"}); //ordena padrão data
                    break;
                case "num pt-br":
                case "nums pt-br":
                case "numeric pt-br":
                case "numerics pt-br":
                case "decimal pt-br":
                case "decimals pt-br":
                case "number pt-br":
                case "numbers pt-br":
                case "numero pt-br":
                case "numeros pt-br":
                    if(arr['columns'] === undefined) arr["aoColumns"].push({"sType": "numeric-comma"}); //ordena padrão data
                    break;
                default:
                    if(arr['columns'] === undefined) arr["aoColumns"].push(null); //ordena padrão data
                    break;
            }
        });
    }

    return arr;
}

function allTables() {
	$("[data-table='table']").each(function (indexInput) {
		if($.fn.DataTable.isDataTable($(this))) {
			try{$(this).DataTable().clear().destroy();}catch (e){};
		}
	});
	$("[data-table='table-no-fixed']").each(function (indexInput) {
		if($.fn.DataTable.isDataTable($(this))) {
			try{$(this).DataTable().clear().destroy();}catch (e){};
		}
	});
	$("[data-table='table-custom']").each(function (indexInput) {
		if($.fn.DataTable.isDataTable($(this))) {
			try{$(this).DataTable().clear().destroy();}catch (e){};
		}
	});
	$("[data-table='false-table']").each(function (indexInput) {
		if($.fn.DataTable.isDataTable($(this))) {
			try{$(this).DataTable().clear().destroy();}catch (e){};
		}
	});
    $("[data-table='table']").each(function (indexInput) {
		let idx = "table-" + indexInput;
		$(this).addClass(idx);

		let export_excel = $(this).data('export_excel');
		let colunaOrder = $(this).data('coluna_order');
		let typeOrder = $(this).data('type_order');
		let notOrder = $(this).data('not_order');
		let filterByCol = $(this).data('is_filter_by_col');
		if(is_empty(colunaOrder, 0)) {
			colunaOrder = 1;
		} else {
			colunaOrder = parseInt(colunaOrder);
		}
		if(is_empty(typeOrder, 1)) {
			typeOrder = 'desc';
		} else {
			typeOrder = typeOrder.toString().toLowerCase();
		}
		if(is_empty(notOrder, 1)) {
			notOrder = false;
		} else {
			notOrder = true;
		}
		if(is_empty(filterByCol, 1)) {
			filterByCol = false;
		} else {
			filterByCol = true;
		}
		if(is_empty(export_excel, 1)) {
			export_excel = false;
		} else {
			export_excel = true;
		}

		if(filterByCol) {
			if($(this).find('tfoot').length === 0) {
				$(this).append("<tfoot></tfoot>");
			}
			$($(this).find('tfoot')).html("<tr></tr>");
			$($(this).find('thead tr:first th')).each( function (i) {
				let placeholderFilter = $(this).text();
				$($(this).parents("table").find('tfoot tr:first')).append('<th><input type="text" class="search_dt-col" style="width: 100%;" placeholder="'+placeholderFilter+'" data-index="'+i+'" /></th>');
			} );
		}
        var arrayToConfig = {
            'aoColumnDefs': [{
                'bSortable': false,
                'aTargets': ['nosort']
            }],
            "oLanguage": oLanguage,
            "sScrollX": "100%",
            "stateSave": true,
            "sScrollXInner": "100%",
			"searchDelay": 700,
            "fnDrawCallback": function (oSettings) {
                allFunctions();
                var minWidth = $('.DTFC_ScrollWrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner table[data-table="table"] thead tr th:last-child').css('min-width');
                $('.DTFC_ScrollWrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner table[data-table="table"] thead tr th:last-child').prop('style', 'min-width:' + minWidth + ';');

				let colId = !is_empty($(this).data('col_id'), 1) ? $(this).data('col_id') : null;
				if(colId !== null) {
					$.each(jsonPorTabela[idx], function (idxJson, valueJson) {
						if(!is_empty(valueJson[colId], 1)) {
							$($($('.dataTables_scrollBody table.' + idx + ' tbody').find("tr")).get(idxJson)).data("id", valueJson[colId])
						}
					});
				}
				colId = null;

				$("."+idx).trigger("datatable:draw:custom");

			},
            "fixedColumns": {
                'leftColumns': 1
            },
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "bScrollCollapse": false,
            "sScrollY": scrollY

            // , 'ajax': '//dev3/vitor.delgallo/b1-food-web/Logs/carregarListagem',
            // 'columns': [
            //     {data: 'idLogs'},
            //     {data: 'modulo'},
            //     {data: 'tipoRequisicao'},
            //     {data: 'log'},
            //     {data: 'dataRegistro'}
            // ]
        };
		if(!notOrder) {
			arrayToConfig.order = [[colunaOrder, typeOrder]];
		} else {
			arrayToConfig.ordering = false;
		}
		if(export_excel) {
			arrayToConfig.dom = 'lfrtip<"data_table-buttons_space centraliza"B>';
		}
		arrayToConfig.initComplete = function() {
			let api = this.api();
		}
		if(filterByCol) {
			arrayToConfig.initComplete = function() {
				let api = this.api();

				// Apply the search
				api.columns().every(function() {
					let that = this;

					$('input', this.footer()).on('keyup change', function() {
						if (that.search() !== this.value) {
							that
								.search(this.value)
								.draw();
						}
					});
				});
			}
		}

        var ajaxConfig = null;
        if(!is_empty($(this).data('url_ajax'), 1) && !is_empty($(this).data('colunas_ajax'), 1)){
            ajaxConfig = {
                "url": $(this).data('url_ajax'),
                "colunas": $(this).data('colunas_ajax')
            };
        }

		$(this).DataTable(retornaArrayComColunas(idx, arrayToConfig, $(this).data('tipos_col'), ajaxConfig,$(this).data('colunas_ocultar_view')));
		let table = $(this).DataTable().draw();
		if(export_excel) {
			$(this).DataTable().buttons('.buttons-copy').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-csv').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-pdf').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-print').nodes().addClass('ocultar');

			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn");
			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn-sm");
			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn-warning");
			let txtExcel = $(this).DataTable().buttons('.buttons-excel').nodes().text();
			$(this).DataTable().buttons('.buttons-excel').nodes().html("<i class='fa fa-print'></i> " + txtExcel);
		}

        // $(this).DataTable(arrayToConfig);
		customDataTableComponents(table)
    });

    $("[data-table='table-no-fixed']").each(function (indexInput) {
		let idx = "table-no-fixed-" + indexInput;
		$(this).addClass(idx);

		let export_excel = $(this).data('export_excel');
		let colunaOrder = $(this).data('coluna_order');
		let typeOrder = $(this).data('type_order');
		let notOrder = $(this).data('not_order');
		let filterByCol = $(this).data('is_filter_by_col');
		if(is_empty(colunaOrder, 0)) {
			colunaOrder = 1;
		} else {
			colunaOrder = parseInt(colunaOrder);
		}
		if(is_empty(typeOrder, 1)) {
			typeOrder = 'desc';
		} else {
			typeOrder = typeOrder.toString().toLowerCase();
		}
		if(is_empty(notOrder, 1)) {
			notOrder = false;
		} else {
			notOrder = true;
		}
		if(is_empty(filterByCol, 1)) {
			filterByCol = false;
		} else {
			filterByCol = true;
		}
		if(is_empty(export_excel, 1)) {
			export_excel = false;
		} else {
			export_excel = true;
		}

		if(filterByCol) {
			if($(this).find('tfoot').length === 0) {
				$(this).append("<tfoot></tfoot>");
			}
			$($(this).find('tfoot')).html("<tr></tr>");
			$($(this).find('thead tr:first th')).each( function (i) {
				let placeholderFilter = $(this).text();
				$($(this).parents("table").find('tfoot tr:first')).append('<th><input type="text" class="search_dt-col" style="width: 100%;" placeholder="'+placeholderFilter+'" data-index="'+i+'" /></th>');
			} );
		}
        var arrayToConfig = {
            'aoColumnDefs': [{
                'bSortable': false,
                'aTargets': ['nosort']
            }],
            "oLanguage": oLanguage,
            "sScrollX": "100%",
            "stateSave": true,
            "sScrollXInner": "100%",
			"searchDelay": 700,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "fnDrawCallback": function (oSettings) {
                allFunctions();
                var minWidth = $('.DTFC_ScrollWrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner table[data-table="table"] thead tr th:last-child').css('min-width');
                $('.DTFC_ScrollWrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner table[data-table="table"] thead tr th:last-child').prop('style', 'min-width:' + minWidth + ';');

                let colId = !is_empty($(this).data('col_id'), 1) ? $(this).data('col_id') : null;
				if(colId !== null) {
					$.each(jsonPorTabela[idx], function (idxJson, valueJson) {
						if(!is_empty(valueJson[colId], 1)) {
							$($($('.dataTables_scrollBody table.' + idx + ' tbody').find("tr")).get(idxJson)).data("id", valueJson[colId])
						}
					});
				}
				colId = null;

				$("."+idx).trigger("datatable:draw:custom");
			},
            "bScrollCollapse": true,
            "sScrollY": scrollY
        };
		if(!notOrder) {
			arrayToConfig.order = [[colunaOrder, typeOrder]];
		} else {
			arrayToConfig.ordering = false;
		}
		if(export_excel) {
			arrayToConfig.dom = 'lfrtip<"data_table-buttons_space centraliza"B>';
		}
		arrayToConfig.initComplete = function() {
			let api = this.api();
		}
		if(filterByCol) {
			arrayToConfig.initComplete = function() {
				let api = this.api();
				// Apply the search
				api.columns().every(function() {
					let that = this;

					$('input', this.footer()).on('keyup change', function() {
						if (that.search() !== this.value) {
							that
								.search(this.value)
								.draw();
						}
					});
				});
			}
		}

        var ajaxConfig = null;
        if(!is_empty($(this).data('url_ajax'), 1) && !is_empty($(this).data('colunas_ajax'), 1)){
            ajaxConfig = {
                "url": $(this).data('url_ajax'),
                "colunas": $(this).data('colunas_ajax')
            };
        }

		$(this).DataTable(retornaArrayComColunas(idx, arrayToConfig, $(this).data('tipos_col'), ajaxConfig,$(this).data('colunas_ocultar_view')));
		let table = $(this).DataTable().draw();
		if(export_excel) {
			$(this).DataTable().buttons('.buttons-copy').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-csv').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-pdf').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-print').nodes().addClass('ocultar');

			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn");
			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn-sm");
			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn-warning");
			let txtExcel = $(this).DataTable().buttons('.buttons-excel').nodes().text();
			$(this).DataTable().buttons('.buttons-excel').nodes().html("<i class='fa fa-print'></i> " + txtExcel);
		}
        // console.log(arrayToConfig);
		customDataTableComponents(table);
    });

	/**
	 * DATATABLE CUSTOMIZADA QUE PERMITE TOTAL CUSTOMIZACAO
	 * // ainda estou desenvolvendo as funcionalidades e aceito ajuda e sugestoes :) //
	 * Regras de funcionamento:
	 * 	1 - nao pode existir rowspan e nem colspan
	 * 	2 - nao pode ter row vazia
	 * CallBack functions
	 * 	<datatable>.on("column-reorder", function ( e, settings, details ) { // aqui vc faz o que quiser }
	 */
	$("[data-table='table-custom']").each(function (indexInput) {
		let idx = "table-custom-" + indexInput;
		$(this).addClass(idx);

		let export_excel = $(this).data('export_excel');
		let colunaOrder = $(this).data('coluna_order');
		let typeOrder = $(this).data('type_order');
		let notOrder = $(this).data('not_order');
		let filterByCol = $(this).data('is_filter_by_col');
		let colReoderByDragAndDrop = $(this).data('is_col_reoder_drag_and_drop');
		let simpleTable = $(this).data('simple_table');
		let refreshRateInSeconds = $(this).data('refresh_secs');
		let noProcessing = $(this).data('no_processing');

		if(is_empty(colunaOrder, 0)) {
			colunaOrder = 1;
		} else {
			colunaOrder = parseInt(colunaOrder);
		}
		if(is_empty(typeOrder, 1)) {
			typeOrder = 'desc';
		} else {
			typeOrder = typeOrder.toString().toLowerCase();
		}
		if(is_empty(notOrder, 1)) {
			notOrder = false;
		} else {
			notOrder = true;
		}
		if(is_empty(filterByCol, 1)) {
			filterByCol = false;
		} else {
			filterByCol = true;
		}
		if(is_empty(export_excel, 1)) {
			export_excel = false;
		} else {
			export_excel = true;
		}
		if(is_empty(simpleTable, 1)) {
			simpleTable = false;
		} else {
			simpleTable = true;
		}
		if(is_empty(refreshRateInSeconds, 1) || isNaN(refreshRateInSeconds)) {
			refreshRateInSeconds = false;
		} else {
			refreshRateInSeconds = parseFloat(refreshRateInSeconds).toFixed(2);
		}
		if(is_empty(noProcessing, 1)) {
			noProcessing = false;
		} else {
			noProcessing = true;
		}

		if(filterByCol) {
			if($(this).find('tfoot').length === 0) {
				$(this).append("<tfoot></tfoot>");
			}
			$($(this).find('tfoot')).html("<tr></tr>");
			$($(this).find('thead tr:first th')).each( function (i) {
				let placeholderFilter = $(this).text();
				$($(this).parents("table")
					.find('tfoot tr:first'))
					.append('<th><input type="text" class="search_dt-col" style="width: 100%;" placeholder="'+placeholderFilter+'" data-index="'+i+'" /></th>');
			} );
		}
		var arrayToConfig = {
			'aoColumnDefs': [{
				'bSortable': false,
				'aTargets': ['nosort']
			}],
			"oLanguage": oLanguage,
			"sScrollX": "100%",
			"stateSave": true,
			"sScrollXInner": "100%",
			"searchDelay": 700,
			// "fixedColumns": {
			// 	'leftColumns': 1
			// },
			"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
			"fnDrawCallback": function (oSettings) {
				allFunctions();
				var minWidth = $('.DTFC_ScrollWrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner table[data-table="table"] thead tr th:last-child').css('min-width');
				$('.DTFC_ScrollWrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner table[data-table="table"] thead tr th:last-child').prop('style', 'min-width:' + minWidth + ';');

				let colId = !is_empty($(this).data('col_id'), 1) ? $(this).data('col_id') : null;
				if(colId !== null) {
					$.each(jsonPorTabela[idx], function (idxJson, valueJson) {
						if(!is_empty(valueJson[colId], 1)) {
							$($($('.dataTables_scrollBody table.' + idx + ' tbody').find("tr")).get(idxJson)).data("id", valueJson[colId])
						}
					});
				}
				colId = null;

				$("."+idx).trigger("datatable:draw:custom");
			},
			"bScrollCollapse": true,
			"sScrollY": scrollY
		};
		if(!notOrder) {
			arrayToConfig.order = [[colunaOrder, typeOrder]];
		} else {
			arrayToConfig.ordering = false;
		}
		if(export_excel) {
			arrayToConfig.dom = 'lfrtip<"data_table-buttons_space centraliza"B>';
		}
		if(colReoderByDragAndDrop) {
			arrayToConfig.colReorder = true;
		}

		arrayToConfig.initComplete = function() {
			let api = this.api();
			$(api.table().container()).find('input').attr('autocomplete', 'false');
		}
		if(filterByCol) {
			arrayToConfig.initComplete = function() {
				let api = this.api();
				$(api.table().container()).find('input').attr('autocomplete', 'false');
				// Apply the search
				api.columns().every(function() {
					let that = this;

					$('input', this.footer()).on('keyup change', function() {
						if (that.search() !== this.value) {
							that
								.search(this.value)
								.draw();
						}
					});
				});
			}
		}

		var ajaxConfig = null;
		if(!is_empty($(this).data('url_ajax'), 1) && !is_empty($(this).data('colunas_ajax'), 1)){
			ajaxConfig = {
				"url": $(this).data('url_ajax'),
				"colunas": $(this).data('colunas_ajax')
			};
		}

		if(simpleTable) {
			arrayToConfig.paging = false;
			arrayToConfig.ordering = false;
			arrayToConfig.info = false;
			arrayToConfig.searching = false;
			arrayToConfig.bScrollCollapse = true;
			arrayToConfig.sScrollY = scrollY;
			arrayToConfig.sScrollX = "100%";
			arrayToConfig.sScrollXInner = "100%";
		}

		if(noProcessing) {
			arrayToConfig.bProcessing = false;
		}

		let dt = $(this).DataTable(retornaArrayComColunas(idx, arrayToConfig, $(this).data('tipos_col'), ajaxConfig,$(this).data('colunas_ocultar_view')));
		let table = $(this).DataTable().draw();
		if(export_excel) {
			$(this).DataTable().buttons('.buttons-copy').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-csv').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-pdf').nodes().addClass('ocultar');
			$(this).DataTable().buttons('.buttons-print').nodes().addClass('ocultar');

			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn");
			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn-sm");
			$(this).DataTable().buttons('.buttons-excel').nodes().addClass("btn-warning");
			let txtExcel = $(this).DataTable().buttons('.buttons-excel').nodes().text();
			$(this).DataTable().buttons('.buttons-excel').nodes().html("<i class='fa fa-print'></i> " + txtExcel);
		}
		// funcao ao alterar a ordem das colunas
		dt.on("column-reorder", function ( e, settings, details ) {

		});

		if (refreshRateInSeconds != false) {
			setInterval(function() {
				$('.table').DataTable().draw();
			}, refreshRateInSeconds * 1000);
		}

		customDataTableComponents(table);
	});

    $("[data-table='false-table']").DataTable({
        "paging": false,
        "ordering": false,
        "info": false,
        "searching": false,
		"bScrollCollapse": true,
		"sScrollY": scrollY,
		"sScrollX": "100%",
		"sScrollXInner": "100%",
		"searchDelay": 700,
        "oLanguage": oLanguage
    });

	function customDataTableComponents(table) {
		const lengthMenu = table.settings().init().lengthMenu[0];
		const $dataTableLength = $('.dataTable-length');
		const $dataTableSearch = $('.dataTable-search');

		// Verifica e adiciona opções ao select de resultado por página
		const addLengthMenuOptions = (lengthMenu, $dataTableLength) => {
			let optionsAdded = new Set($dataTableLength.find('option').map(function() {
				return $(this).val();
			}).get());

			lengthMenu.forEach(value => {
				if (!optionsAdded.has(String(value))) {
					$dataTableLength.append($('<option>', {
						value: value,
						text: value
					}));
					optionsAdded.add(String(value));
				}
			});
		};

		// Carrega o estado salvo do DataTable
		const loadStateValues = (table) => {
			const state = table.state.loaded();

			$dataTableSearch.attr("placeholder", l["pesquisar"]);
			if (state) {
				$dataTableSearch.val(state.search.search);
				$dataTableLength.val(state.length);
			}
		};

		// Implementa o comportamento de busca customizada no input custom
		const bindCustomSearch = ($dataTableSearch, table) => {
			$dataTableSearch.on('keyup change', function () {
				table.search(this.value).draw();
			});

			$dataTableSearch.on('input', function () {
				if (this.value === "") {
					table.search('').draw();
				}
			});
		};

		// Implementa o comportamento de troca de quantidade de itens por página no input custom
		const bindCustomLengthChange = ($dataTableLength, table) => {
			$dataTableLength.on('change', function () {
				table.page.len(this.value).draw();
			});
		};

		if (!isOldLayout) {
			// Executa as funções
			addLengthMenuOptions(lengthMenu, $dataTableLength);
			loadStateValues(table);
			bindCustomSearch($dataTableSearch, table);
			bindCustomLengthChange($dataTableLength, table);
		}
	}
}