@extends('default.layout', ['title' => 'Editar checklist'])

@section('content')
<x-form-page
	title="Editar checklist"
	heading="Editar checklist"
	:back-url="route('vet.checklist.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()
		->fill($checklist)
		->put()
		->id('form-checklists')
		->route('vet.checklist.update', [$checklist->id]) !!}
		@include('petshop.vet.checklists._form', ['checklist' => $checklist])
	{!! Form::close() !!}
</x-form-page>
@endsection
