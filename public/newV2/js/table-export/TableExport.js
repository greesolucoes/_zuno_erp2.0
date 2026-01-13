/**
 * Function funcoesEspecificasSave2Excel.
 * Cria estilo especifico do cliente.
 * @return String Estilo para gerar o excel
 */
function funcoesEspecificasSave2Excel(obj) {
    /*
     * ========================== Customizações Especificas Do Cliente ==========================
     * Use as seguintes classes para formatar:
     *
     *
     * ========================== Customizações Especificas Do Cliente ==========================
     */
    var estilo = "";



    return estilo;
}

/**
 * Gerador de planilhas eltrônicas a partir de tables html
 * Por: Fábio Santos
 * Update: 26/09/2017
 *
 * @param target {Tabela_Objeto_Jquery}
 * @param info {Variaveis}
 * @param params {
 * 					permiteZerosEsquerda : bool // Caso true, permite que números sejam exportados com o zero a esquerda
 * 				}
 */
function save2excel(target, info, params = {permiteZerosEsquerda: false}) {
    /*
     * =================================== PARÂMETROS ==================================
     * not: Objeto Jquery para ser ignorado. exemplo: $('.ignore'),
     * name: 'Nome da Planilha',
     * filename: 'Nome do arquivo que será gerado.xls'
     * =================================== PARÂMETROS ==================================
     *
     * ============================= Customizações Gerais ==============================
     * Use as seguintes classes para formatar:
     * (Medidas utilizadas em pixels)
     * (o conteúdo das <th> por DEFAULT são em negrito)
     *
     * Especifico: isImgTd (Classe de TD - Especifica se a coluna é própria para imagem)
     * Alinhamento: left, center e right
     * Largura: w100, w200, w300, w350, w400 e w500
     * Altura: h40, h50 e h60 (default 30)
     * Tamanho fonte: f14, f16 e f18 (default 12)
     * Cores fonte: fcinza, fbranco e fazul (default preto)
     * Formatações fonte: n (negrito), i (itálico), s (sublinhado)
     * Cores background: bcinza1, bcinza2, bazul, bvermelho, bamarelo, bmarrom e bverde
     * ============================= Customizações Gerais ==============================
     */

    var linhas = [];
    if(info.not){
        linhas = $('tr', $(target).find('thead')).not(info.not);
        Array.prototype.push.apply(linhas, $('tr', $(target).find('tbody')).not(info.not));
        Array.prototype.push.apply(linhas, $('tr', $(target).find('tfoot')).not(info.not));
    }else{
        linhas = $('tr', $(target).find('thead'));
        Array.prototype.push.apply(linhas, $('tr', $(target).find('tbody')));
        Array.prototype.push.apply(linhas, $('tr', $(target).find('tfoot')));
    }

    var dados = '<table id="table2excelCustom">';
    dados += '<tbody>';
	let colunas_ocultar= target.data('colunas_ocultar_xlsx');
	var colunasOcultar= [];
	if(colunas_ocultar){
		if(colunas_ocultar.toString().includes(',')){
			colunasOcultar= colunas_ocultar.split(",");
			colunasOcultar= colunasOcultar.map(function(i){
				return parseInt(i);
			});
		}else{
			colunasOcultar.push(parseInt(colunas_ocultar));
		}
	}
    $(linhas).each(function () {
        var style = 'vertical-align: middle;';
        if(!$(this).parents('table').hasClass('notBorder')) style += "border: 1px solid #333;";

        dados += '<tr>';
        $('th, td', this).each(function (indexColuna) {
            var data = null;
            if(!$(this).hasClass('isImgTd')){
				if(colunasOcultar.length > 0){
					if(jQuery.inArray(indexColuna, colunasOcultar) == -1){
						data = (params.permiteZerosEsquerda) ? $(this).text() + String.fromCharCode(8203) : $(this).text();
					}
				}else{
					data = (params.permiteZerosEsquerda) ? $(this).text() + String.fromCharCode(8203) : $(this).text();
				}
                // if (data.search('%') !== -1) {
                //     data = data.split('.').join(',');
                // }
            }else{
                data = $(this).html();
            }
			if(data !== null){

				if ($(this).hasClass('center')) {
					style += 'text-align: center;';
				} else if ($(this).hasClass('left')) {
					style += 'text-align: left;';
				} else if ($(this).hasClass('right')) {
					style += 'text-align: right;';
				}

				if ($(this).hasClass('w100')) {
					style += 'width: 100px;';
				} else if ($(this).hasClass('w150')) {
					style += 'width: 150px;';
				} else if ($(this).hasClass('w200')) {
					style += 'width: 200px;';
				} else if ($(this).hasClass('w300')) {
					style += 'width: 300px;';
				} else if ($(this).hasClass('w350')) {
					style += 'width: 350px;';
				} else if ($(this).hasClass('w400')) {
					style += 'width: 400px;';
				} else if ($(this).hasClass('w500')) {
					style += 'width: 500px;';
				}

				if ($(this).parents('tr').hasClass('h40')) {
					style += 'height: 40px;';
				} else if ($(this).parents('tr').hasClass('h50')) {
					style += 'height: 50px;';
				} else if ($(this).parents('tr').hasClass('h60')) {
					style += 'height: 60px;';
				} else{
					style += 'height: 30px;';
				}

				if ($(this).hasClass('f14')) {
					style += 'font-size: 14px;';
				} else if ($(this).hasClass('f16')) {
					style += 'font-size: 16px;';
				} else if ($(this).hasClass('f18')) {
					style += 'font-size: 18px;';
				} else{
					style += 'font-size: 12px;';
				}

				if ($(this).parents('tr').hasClass('bcinza1')) {
					style += 'background-color: #EEEEEE;';
				} else if ($(this).parents('tr').hasClass('bcinza2')) {
					style += 'background-color: #E0E0E0;';
				} else if ($(this).parents('tr').hasClass('bazul')) {
					style += 'background-color: #0b4595;';
				} else if ($(this).parents('tr').hasClass('bvermelho')) {
					style += 'background-color: #f44336;';
				} else if ($(this).parents('tr').hasClass('bamarelo')) {
					style += 'background-color: #FFC107;';
				} else if ($(this).parents('tr').hasClass('bmarrom')) {
					style += 'background-color: #795548;';
				} else if ($(this).parents('tr').hasClass('bverde')) {
					style += 'background-color: #4CAF50;';
				}

				if ($(this).parents('tr').hasClass('fcinza')) {
					style += 'color: #424242;';
				} else if ($(this).parents('tr').hasClass('fbranco')) {
					style += 'color: #ffffff;';
				} else if ($(this).parents('tr').hasClass('fazul')) {
					style += 'color: #0b4595;';
				} else{
					style += 'color: #212121;';
				}

				if ($(this).hasClass('n')) {
					style += 'font-weight: bold;';
				} else if ($(this).hasClass('i')) {
					style += 'font-style: italic;';
				} else if ($(this).hasClass('u')) {
					style += 'text-decoration: underline;';
				}

				style += funcoesEspecificasSave2Excel($(this));

				if ($(this).is('th')) {
					dados += '<th rowspan="' + $(this).prop('rowspan') + '" colspan="' + $(this).prop('colspan') + '" style="' + style + '">' + data + '</th>';
				} else {
					dados += '<td rowspan="' + $(this).prop('rowspan') + '" colspan="' + $(this).prop('colspan') + '" style="' + style + '">' + data + '</td>';
				}
			}
        });
        dados += '</tr>';
    });
    dados += '</tbody>';
    dados += '</table>';

    $('body').append('<div id="table2excelCustomRemove" style="display: none;">' + dados + '</div>');

    $('#table2excelCustomRemove table#table2excelCustom').table2excel({
        name: info.name,
        filename: info.filename,
        exclude_img: false,
        exclude_links: false
    });
    // window.open('data:application/vnd.ms-excel,' + encodeURIComponent( $('#table2excelCustomRemove').html()));
    // e.preventDefault();

    $('#table2excelCustomRemove').remove();
}