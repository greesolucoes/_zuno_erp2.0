@extends('default.layout', ['title' => 'Editar sala de atendimento'])

@section('content')
<x-form-page
	title="Editar sala de atendimento"
	heading="Editar sala de atendimento"
	:back-url="route('vet.salas-atendimento.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
		->fill($salaAtendimento)
		->put()
		->id('form-salas-atendimento')
		->route('vet.salas-atendimento.update', [$salaAtendimento->id]) !!}
		@include('petshop.vet.salas_atendimento._form')
	{!! Form::close() !!}
</x-form-page>
@endsection
