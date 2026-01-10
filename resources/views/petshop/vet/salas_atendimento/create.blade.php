@extends('layouts.app', ['title' => 'Nova sala de atendimento'])

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="text-color">Nova sala de atendimento</h3>

        <a href="{{ route('vet.salas-atendimento.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
            <i class="ri-arrow-left-double-fill"></i>Voltar
        </a>
    </div>

    <div class="card-body">
        {!! Form::open()
            ->post()
            ->id('form-salas-atendimento')
            ->route('vet.salas-atendimento.store') !!}
        <div class="pl-lg-4">
            @include('petshop.vet.salas_atendimento._form', ['salaAtendimento' => null])
        </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection