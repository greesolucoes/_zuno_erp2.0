@extends('default.layout', ['title' => 'Novo medicamento'])

@section('content')
<x-form-page
	title="Novo medicamento"
	:back-url="route('vet.medicines.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->post()
	->id('form-medicamentos')
	->route('vet.medicines.store') !!}
		@include('petshop.vet.medicines._form', [
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
