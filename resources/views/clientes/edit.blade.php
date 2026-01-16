@extends('default.layout',['title' => 'Editar Cliente'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('clientes.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar cliente</h5>
			</div>
			<hr>
			
			{!!Form::open()->fill($item)
			->put()
			->route('clientes.update', [$item->id])
			->multipart()!!}
			<div class="pl-lg-4">
				@include('clientes._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection

@section('js')
	<script type="text/javascript" src="/js/client.js"></script>
	<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
	<script type="text/javascript">
		$(document).on("blur", "#inp-cep", function () {
			let cep = $(this).val().replace(/[^0-9]/g,'')

			$url = "https://viacep.com.br/ws/"+cep+"/json";
			$.get($url)
			.done((success) => {
				$('#inp-rua').val(success.logradouro)
				$('#inp-numero').val(success.numero)
				$('#inp-bairro').val(success.bairro)

				findCidade(success.ibge)
			});
		});

		function findCidade(codigo_ibge) {
			$.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
			.done((res) => {
				var newOption = new Option(
					res.nome + " (" + res.uf + ")",
					res.id,
					false,
					false
				);
				$("#inp-cidade_id")
					.html(newOption)
					.trigger("change");
			});
		}
	</script>
@endsection
