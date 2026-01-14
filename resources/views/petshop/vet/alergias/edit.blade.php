@extends('default.layout', ['title' => 'Editar alergia'])

@section('content')
<x-form-page
	title="Editar alergia"
	:back-url="route('vet.allergies.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->put()
	->id('form-alergias')
	->route('vet.allergies.update', [$alergia->id]) !!}
		@include('petshop.vet.alergias._form', ['alergia' => $alergia])
	{!! Form::close() !!}
</x-form-page>
@endsection
