@extends('default.layout', ['title' => 'Editar sala de internação'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vet.salas-internacao.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar sala de internação</h5>
			</div>
			<hr>

			{!! Form::open()
			->fill($salaInternacao)
			->put()
			->id('form-salas-internacao')
			->route('vet.salas-internacao.update', [$salaInternacao->id]) !!}
			<div class="pl-lg-4">
				@include('petshop.vet.salas_internacao._form')
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection
