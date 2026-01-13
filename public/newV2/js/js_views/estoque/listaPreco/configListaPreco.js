controlaTabelaSuite({
	"ref": "#lista_precos_estoque",
	"funAposAddItem": function () {
	}
});

$('.select_lista_preco').select2Simple({
	allowClear: true
});
$('.select_lista_preco').data('init', '');