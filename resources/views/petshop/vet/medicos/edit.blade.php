@extends('default.layout', ['title' => 'Editar médico veterinário'])

@section('content')
<x-form-page
	title="Editar médico veterinário"
	heading="Editar médico"
	:back-url="route('vet.medicos.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
		->put()
		->id('form-medicos')
		->route('vet.medicos.update', [$medico->id]) !!}
		@include('petshop.vet.medicos._form', ['medico' => $medico])
	{!! Form::close() !!}
</x-form-page>
@endsection
