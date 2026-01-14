@extends('default.layout', ['title' => 'Criar Modelo de Atendimento'])

@section('content')
<x-form-page
	title="Criar Modelo de Atendimento"
	:back-url="route('vet.modelos-atendimento.index', ['page' => request()->query('page', 1)])"
>
	{!! Form::open()->post()->route('vet.modelos-atendimento.store')->id('main-form') !!}
		@include('petshop.vet.modelos_atendimento._form')
	{!! Form::close() !!}

	<hr>

	<div class="d-flex justify-content-end">
		<button type="submit" form="main-form" id="submit-btn" class="btn btn-primary px-4">
			<i class="ri-save-line"></i> Salvar
		</button>
	</div>
</x-form-page>
@endsection
