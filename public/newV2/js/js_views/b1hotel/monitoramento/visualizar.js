var isEditable = document.getElementById('isEditable');

//Se for TRUE - então permitimos alteração dos inputs
//Se for FALSE- todos os inputs vem com readonly
if (isEditable.dataset.editable) {
	var divs = document.querySelectorAll('.isEditable');
	for (var i = 0; i < divs.length; i++)
		divs[i].removeAttribute("readonly");
}

function reprocessarAdiantamento(){
	$('.reprocessarAdiantamento').off("click");
	$('.reprocessarAdiantamento').on("click", function(e){
		e.preventDefault();
		var obj  = $(this);
		var url  = $(obj).data('url');
		var id   = $(obj).data('id');
		var tipo = $(obj).data('tipo');
		var operacao = $(obj).data('operacao');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();

		swal({
			title: l["reprocessar"],
			text: l["temCertezaQueDesejaReprocessar?"],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['reprocessar'],
			cancelButtonText: l["cancelar!"]
		}).then(function(){
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id, 'tipo': tipo, 'operacao': operacao}, function(ret){
				if(ret != 0){
					swal(
						l['sucesso!'],
						l['XMLReprocessadoComSucesso'],
						"success"
					);
					$(obj).remove();
					tableDataTable.draw();
				}
				else {
					swal(
						l['erro'],
						l['falhaReprocessarXML'],
						"error"
					);
				}
				toggleLoading();
			})
		}).catch(swal.noop);
	})
};

function allFunctionsNotasFiscais(){
	reprocessarAdiantamento();
}

allFunctionsNotasFiscais();