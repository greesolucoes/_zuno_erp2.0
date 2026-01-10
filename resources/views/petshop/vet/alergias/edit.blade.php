@extends('layouts.app', ['title' => 'Editar alergia'])

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="text-color">Editar alergia</h3>

        <a href="{{ route('vet.allergies.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
            <i class="ri-arrow-left-double-fill"></i>Voltar
        </a>
    </div>

    <div class="card-body">
        {!! Form::open()
            ->put()
            ->id('form-alergias')
            ->route('vet.allergies.update', [$alergia->id]) !!}
        <div class="pl-lg-4">
            @include('petshop.vet.alergias._form', ['alergia' => $alergia])
        </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection