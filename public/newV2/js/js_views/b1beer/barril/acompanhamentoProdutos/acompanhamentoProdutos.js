
function acoesBotoes(){

	$('.modal-detalhes-itens')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);
			let idProdutoEstrutura= $(obj).data("idprodutoestrutura")

			var html='<div class="centraliza"><img src="'+BASE_URL+'assets/images/imagens_publicas/loading.gif" width="80px"><p>Buscando itens...</p></div>';
			$("#modalDetalhesItens .modal-body").html(html);
			$("#modalDetalhesItens").modal('show');

			ajaxRequest(
				true,
				BASE_URL+'BrEquipamentos/getItensProdutoEnvasado',
				null,
				'html',
				{
					'idProdutoEstrutura' : idProdutoEstrutura
				},
				function (ret) {
					if(ret){
						$("#modalDetalhesItens .modal-body").html(ret);
					}else {
						$("#modalDetalhesItens").modal('hide');
						swal(
							'Erro na requisição',
							'Houve um erro na requisição, tente novamente!',
							'error'
						).catch(swal.noop);
					}
				}
			);

		});
}
acoesBotoes();