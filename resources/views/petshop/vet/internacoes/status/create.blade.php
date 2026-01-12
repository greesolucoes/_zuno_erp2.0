@extends('default.layout', ['title' => 'Novo status da internação'])

@php($patient = $internacao->animal)

@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vet.hospitalizations.status.index', $internacao) }}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>

			<div class="card-title">
				<h5 class="mb-0 text-primary">Novo status para {{ $patient?->nome ?? 'paciente' }}</h5>
				<span class="text-muted small">Internação #{{ $internacao->id }}</span>
			</div>
			<hr>

			{!! Form::open()->post()->route('vet.hospitalizations.status.store', ['internacao' => $internacao->id]) !!}
			<div class="pl-lg-4">
				@include('petshop.vet.internacoes.status._form', [
					'statusRecord' => null,
					'evolutionOptions' => $evolutionOptions,
				])
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>
@endsection
