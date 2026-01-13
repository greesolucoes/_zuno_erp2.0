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
	"ref": "#cadastro-origem-perda",
	"funAposAddItem": function () {
		renumeraLinhasForm();
	},
	"funAposRemoverItem": function (){
		renumeraLinhasForm();
	}
});