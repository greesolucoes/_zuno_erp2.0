@extends('default.layout', ['title' => 'Editar condição crônica'])

@section('content')
<x-form-page
	title="Editar condição crônica"
	:back-url="route('vet.chronic-conditions.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->put()
	->id('form-condicoes-cronicas')
	->route('vet.chronic-conditions.update', [$condicaoCronica->id]) !!}
		@include('petshop.vet.condicoes_cronicas._form', ['condicaoCronica' => $condicaoCronica])
	{!! Form::close() !!}
</x-form-page>
@endsection
