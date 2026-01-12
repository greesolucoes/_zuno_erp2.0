@extends('default.layout', ['title' => 'Editar condição crônica'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vet.chronic-conditions.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar condição crônica</h5>
			</div>
			<hr>

			{!! Form::open()
			->put()
			->id('form-condicoes-cronicas')
			->route('vet.chronic-conditions.update', [$condicaoCronica->id]) !!}
			<div class="pl-lg-4">
				@include('petshop.vet.condicoes_cronicas._form', ['condicaoCronica' => $condicaoCronica])
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection
