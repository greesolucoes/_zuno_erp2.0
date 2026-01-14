@extends('default.layout', ['title' => 'Nova alergia'])

@section('content')
<x-form-page
	title="Nova alergia"
	:back-url="route('vet.allergies.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->post()
	->id('form-alergias')
	->route('vet.allergies.store') !!}
		@include('petshop.vet.alergias._form', ['alergia' => null])
	{!! Form::close() !!}
</x-form-page>
@endsection
