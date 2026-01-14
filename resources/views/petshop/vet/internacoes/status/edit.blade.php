@extends('default.layout', ['title' => 'Editar status da internação'])

@php($patient = $internacao->animal)

@section('content')
<x-form-page
	title="Editar status da internação"
	heading="Editar status de {{ $patient?->nome ?? 'paciente' }}"
	:back-url="route('vet.hospitalizations.status.index', $internacao)"
>
	<div class="text-muted small mb-3">Internação #{{ $internacao->id }}</div>

	{!! Form::open()
	->put()
	->route('vet.hospitalizations.status.update', [$internacao, $statusRecord]) !!}
		@include('petshop.vet.internacoes.status._form', [
			'statusRecord' => $statusRecord,
			'evolutionOptions' => $evolutionOptions,
		])
	{!! Form::close() !!}
</x-form-page>
@endsection
