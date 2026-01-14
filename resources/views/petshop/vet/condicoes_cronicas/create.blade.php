@extends('default.layout', ['title' => 'Nova condição crônica'])

@section('content')
<x-form-page
	title="Nova condição crônica"
	:back-url="route('vet.chronic-conditions.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
	->post()
	->id('form-condicoes-cronicas')
	->route('vet.chronic-conditions.store') !!}
		@include('petshop.vet.condicoes_cronicas._form', ['condicaoCronica' => null])
	{!! Form::close() !!}
</x-form-page>
@endsection
