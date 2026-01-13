/** renumera as linhas do form */
function renumeraLinhasForm(){
	let count = 0;
	$('.item-number').each(function () {
		$(this).html(count);
		count++;
	});
}
/** cria a linha tr de insercao de novos itens */
controlaTabelaSuite({
	"ref": "#cadastro-permitidos",
	"funAposAddItem": function () {
		renumeraLinhasForm();
	},
	"funAposRemoverItem": function (){
		renumeraLinhasForm();
	}
});

function acoesBotoes(){

	// funcao para trocar automaticamente dados nos itens
	$("select#controllerMaster").on('change', function () {

		let origemContollerPval = $("option:selected", this).val();
		let itemSelecionado = $("option:selected", this).html();
		const selectDasLinhas = '.select_permissoes';
		let selecao = 'Permissoes';

		if(is_empty($(this).val())){
			swal({
				title: "Você irá remover todas o Item " + selecao +  " das linhas abaixo!",
				text: l["desejaContinuar?"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["sim!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				// limpa todos
				$(selectDasLinhas).empty();
			}).catch(swal.noop);
		}else{
			swal({
				title: "Você irá atribuir " + itemSelecionado + " para todas as linhas abaixo!",
				text: l["desejaContinuar?"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: l["sim!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				// atribui o mesmo item para todos os  abaixo nas linhas
				$(selectDasLinhas).data('init', JSON.parse('{"id":'+origemContollerPval+',"text":"'+ itemSelecionado +'"}'));
				$(selectDasLinhas).select2Ajax();
			}).catch(swal.noop);
		}

	});

}

acoesBotoes();
allFunctions();