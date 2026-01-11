@extends('default.layout', ['title' => 'Editar medicamento'])

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vet.medicines.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar medicamento</h5>
			</div>
			<hr>

			{!! Form::open()
			->put()
			->id('form-medicamentos')
			->route('vet.medicines.update', [$medicine['id'], 'page' => request()->query('page', 1)]) !!}
			<div class="pl-lg-4">
				@include('petshop.vet.medicines._form', [
					'medicine' => $medicine,
					'therapeuticClasses' => $therapeuticClasses,
					'routes' => $routes,
					'presentations' => $presentations,
					'ageRestrictions' => $ageRestrictions,
					'storageConditions' => $storageConditions,
					'especiesOptions' => $especiesOptions,
					'dispensingOptions' => $dispensingOptions,
				])
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection

@section('js')
    @include('petshop.vet.medicines._scripts')
@endsection
