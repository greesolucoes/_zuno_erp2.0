@extends('default.layout', ['title' => 'Editar Modelo de Atendimento'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vet.modelos-atendimento.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar Modelo de Atendimento</h5>
			</div>
			<hr>

			{!! Form::open()->put()->route('vet.modelos-atendimento.update', [$item->id])->id('main-form') !!}
				@include('petshop.vet.modelos_atendimento._form')
			{!! Form::close() !!}

			<hr>

			<div class="d-flex justify-content-end">
				<button type="submit" form="main-form" id="submit-btn" class="btn btn-primary px-4">
					<i class="ri-save-line"></i> Salvar
				</button>
			</div>
		</div>
	</div>
</div>
@endsection
