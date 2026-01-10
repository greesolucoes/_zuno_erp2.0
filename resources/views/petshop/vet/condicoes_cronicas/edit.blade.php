@extends('layouts.app', ['title' => 'Editar condição crônica'])

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="text-color">Editar condição crônica</h3>

        <a href="{{ route('vet.chronic-conditions.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
            <i class="ri-arrow-left-double-fill"></i>Voltar
        </a>
    </div>

    <div class="card-body">
        {!! Form::open()
            ->put()
            ->id('form-condicoes-cronicas')
            ->route('vet.chronic-conditions.update', [$condicaoCronica->id]) !!}
        <div class="pl-lg-4">
            @include('petshop.vet.condicoes_cronicas._form', ['condicaoCronica' => $condicaoCronica])
        </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection