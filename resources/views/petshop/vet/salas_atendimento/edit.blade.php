@extends('default.layout', ['title' => 'Editar sala de atendimento'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vet.salas-atendimento.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar sala de atendimento</h5>
			</div>
			<hr>

			{!! Form::open()
			->fill($salaAtendimento)
			->put()
			->id('form-salas-atendimento')
			->route('vet.salas-atendimento.update', [$salaAtendimento->id]) !!}
			<div class="pl-lg-4">
				@include('petshop.vet.salas_atendimento._form')
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection
