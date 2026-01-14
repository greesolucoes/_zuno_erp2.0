@extends('default.layout', ['title' => 'Nova sala de internação'])

@section('content')
<x-form-page
	title="Nova sala de internação"
	:back-url="route('vet.salas-internacao.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->post()
	->id('form-salas-internacao')
	->route('vet.salas-internacao.store') !!}
		@include('petshop.vet.salas_internacao._form', ['salaInternacao' => null])
	{!! Form::close() !!}
</x-form-page>
@endsection
