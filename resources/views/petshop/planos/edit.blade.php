@extends('default.layout', ['title' => 'Editar Plano Petshop'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('petshop.gerenciar.planos', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar Plano</h5>
			</div>
			<hr>
			{!! Form::open()->fill($planoData)->put()->route('petshop.planos.update', [$plano->id])->id('main-form') !!}
			<div class="pl-lg-4">
				@include('petshop.planos._form', ['servicos' => $servicos, 'produtos' => $produtos, 'planoData' => $planoData])
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection

@section('js')
    <script src="/js/petshop_planos_beneficios.js"></script>
    <script src="/js/petshop_planos_valores.js"></script>
    <script src="/js/petshop_planos_vigencia.js"></script>
@endsection
