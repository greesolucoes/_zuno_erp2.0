@extends('layouts.app', ['title' => 'Cadastrar médico veterinário'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Novo médico</h3>

            <a href="{{ route('vet.medicos.index', ['page' => request()->query('page', 1)]) }}"
               class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>
                Voltar
            </a>
        </div>

        <div class="card-body">
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
                    <div class="pl-lg-4">
                        @include('petshop.vet.medicos._form', ['medico' => null])
                    </div>
                {!! Form::close() !!}
            @endif
        </div>
    </div>
@endsection