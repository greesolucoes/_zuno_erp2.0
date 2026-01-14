@extends('default.layout', ['title' => 'Cadastrar médico veterinário'])

@section('content')
<x-form-page
	title="Cadastrar médico veterinário"
	heading="Novo médico"
	:back-url="route('vet.medicos.index', ['page' => request()->query('page', 1)])"
>
	@if ($employees->isEmpty())
		<div class="alert alert-info mb-0" role="alert">
			Nenhum colaborador disponível para vínculo. Cadastre um colaborador na área de
			<a href="{{ url('/funcionarios') }}" class="alert-link">Funcionários</a> para vinculá-lo como médico.
		</div>
	@else
		{!! Form::open()
			->post()
			->id('form-medicos')
			->route('vet.medicos.store') !!}
			@include('petshop.vet.medicos._form', ['medico' => null])
		{!! Form::close() !!}
	@endif
</x-form-page>
@endsection
