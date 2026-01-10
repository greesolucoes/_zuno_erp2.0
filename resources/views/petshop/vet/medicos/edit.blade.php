@extends('default.layout', ['title' => 'Editar médico veterinário'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Editar médico</h3>

            <a href="{{ route('vet.medicos.index', ['page' => request()->query('page', 1)]) }}"
               class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>
                Voltar
            </a>
        </div>

        <div class="card-body">
            {!! Form::open()
                ->put()
                ->id('form-medicos')
                ->route('vet.medicos.update', [$medico->id]) !!}
                <div class="pl-lg-4">
                    @include('petshop.vet.medicos._form', ['medico' => $medico])
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection