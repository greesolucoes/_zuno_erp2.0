@extends('default.layout', ['title' => 'Editar medicamento'])

@section('content')
<x-form-page
	title="Editar medicamento"
	:back-url="route('vet.medicines.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->put()
	->id('form-medicamentos')
	->route('vet.medicines.update', [$medicine['id'], 'page' => request()->query('page', 1)]) !!}
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
	{!! Form::close() !!}
</x-form-page>
@endsection

@section('js')
    @include('petshop.vet.medicines._scripts')
@endsection
