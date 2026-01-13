function excluirReg(){
	$('.excluirReg').off("click");
	$('.excluirReg').on("click", function(e){
		e.preventDefault();
		var obj = $(this);
		var url = $(obj).data('url');
		var id = $(obj).data('id');
		var tableDataTable = $(obj).parents('.table-exibe').DataTable();
		const urlRedirect = $('.datas_views').data('url_redirecionamento');

		swal({
			title: l['excluirMedicao'],
			text: l['desejaExcluirEssaMedicao'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l['excluir'],
			cancelButtonText: l['cancelar']
		}).then(function(){
			toggleLoading();
			ajaxRequest(true, url, null, 'text', {'id': id}, function(ret){
				if(ret != 0){
					window.location.href = urlRedirect;
				} else{
					swal(
						l['erro'],
						l['erroAoExcluirPorfavorEntreEmContatoComOSuporte'],
						"error"
					);
				}
				toggleLoading();
			})
		}).catch(swal.noop);
	})
}
excluirReg();