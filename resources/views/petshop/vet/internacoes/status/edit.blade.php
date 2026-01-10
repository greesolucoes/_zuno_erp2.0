@extends('layouts.app', ['title' => 'Editar status da internação'])

@php($patient = $internacao->animal)

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h3 class="text-color mb-0">Editar status de {{ $patient?->nome ?? 'paciente' }}</h3>
                <span class="text-muted small">Internação #{{ $internacao->id }}</span>
            </div>
            <a
                href="{{ route('vet.hospitalizations.status.index', $internacao) }}"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2"
            >
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!! Form::open()
                ->put()
                ->route('vet.hospitalizations.status.update', [$internacao, $statusRecord]) !!}
                <div class="pl-lg-4">
                    @include('petshop.vet.internacoes.status._form', [
                        'statusRecord' => $statusRecord,
                        'evolutionOptions' => $evolutionOptions,
                    ])
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
