@extends('default.layout', ['title' => 'Editar sala de internação'])

@section('content')
<x-form-page
	title="Editar sala de internação"
	:back-url="route('vet.salas-internacao.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->fill($salaInternacao)
	->put()
	->id('form-salas-internacao')
	->route('vet.salas-internacao.update', [$salaInternacao->id]) !!}
		@include('petshop.vet.salas_internacao._form')
	{!! Form::close() !!}
</x-form-page>
@endsection
