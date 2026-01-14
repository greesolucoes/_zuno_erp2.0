@extends('default.layout', ['title' => 'Novo status da internação'])

@php($patient = $internacao->animal)

@section('content')
<x-form-page
	title="Novo status da internação"
	heading="Novo status para {{ $patient?->nome ?? 'paciente' }}"
	:back-url="route('vet.hospitalizations.status.index', $internacao)"
>
	<div class="text-muted small mb-3">Internação #{{ $internacao->id }}</div>

	{!! Form::open()->post()->route('vet.hospitalizations.status.store', ['internacao' => $internacao->id]) !!}
		@include('petshop.vet.internacoes.status._form', [
			'statusRecord' => null,
			'evolutionOptions' => $evolutionOptions,
		])
	{!! Form::close() !!}
</x-form-page>
@endsection
