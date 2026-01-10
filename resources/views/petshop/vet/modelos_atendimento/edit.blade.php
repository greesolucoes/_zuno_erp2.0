@extends('layouts.app', ['title' => 'Editar Modelo de Atendimento'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color mb-0">Editar Modelo de Atendimento</h3>

            <a href="{{ route('vet.modelos-atendimento.index', ['page' => request()->query('page', 1)]) }}"
               class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!! Form::open()->put()->route('vet.modelos-atendimento.update', [$item->id])->id('main-form') !!}
                @include('petshop.vet.modelos_atendimento._form')
            {!! Form::close() !!}

            <hr>

            <div class="d-flex justify-content-end">
                <button type="submit" form="main-form" id="submit-btn" class="btn btn-primary px-4">
                    <i class="ri-save-line"></i> Salvar
                </button>
            </div>
        </div>
    </div>
@endsection