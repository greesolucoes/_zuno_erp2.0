@extends('default.layout', ['title' => 'Novo checklist'])

@section('content')
<x-form-page
	title="Novo checklist"
	heading="Novo checklist"
	:back-url="route('vet.checklist.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
		->post()
		->id('form-checklists')
		->route('vet.checklist.store') !!}
		@include('petshop.vet.checklists._form')
	{!! Form::close() !!}
</x-form-page>
@endsection
