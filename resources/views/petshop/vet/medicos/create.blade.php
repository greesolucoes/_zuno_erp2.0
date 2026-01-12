@extends('default.layout', ['title' => 'Cadastrar médico veterinário'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vet.medicos.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Novo médico</h5>
			</div>
			<hr>

			@if ($employees->isEmpty())
			<div class="alert alert-info mb-0" role="alert">
				Nenhum colaborador disponível para vínculo. Cadastre um colaborador na área de
				<a href="{{ url('/funcionarios') }}" class="alert-link">Funcionários</a> para vinculá-lo como médico.
			</div>
			@else
			{!! Form::open()
			->post()
			->id('form-medicos')
			->route('vet.medicos.store') !!}
			<div class="pl-lg-4">
				@include('petshop.vet.medicos._form', ['medico' => null])
			</div>
			{!! Form::close() !!}
			@endif
		</div>
	</div>
</div>
@endsection
