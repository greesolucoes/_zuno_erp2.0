@extends('default.layout', ['title' => 'Nova sala de atendimento'])

@section('content')
<x-form-page
	title="Nova sala de atendimento"
	heading="Nova sala de atendimento"
	:back-url="route('vet.salas-atendimento.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
		->post()
		->id('form-salas-atendimento')
		->route('vet.salas-atendimento.store') !!}
		@include('petshop.vet.salas_atendimento._form', ['salaAtendimento' => null])
	{!! Form::close() !!}
</x-form-page>
@endsection
