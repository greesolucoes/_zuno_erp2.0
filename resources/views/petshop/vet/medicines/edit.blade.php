@extends('layouts.app', ['title' => 'Editar medicamento'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color mb-0">Editar medicamento</h3>

            <a href="{{ route('vet.medicines.index', ['page' => request()->query('page', 1)]) }}"
                class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!! Form::open()
                ->put()
                ->id('form-medicamentos')
                ->route('vet.medicines.update', [$medicine['id'], 'page' => request()->query('page', 1)]) !!}

                <div class="pl-lg-4">
                    @include('petshop.vet.medicines._form', [
                        'medicine' => $medicine,
                        'therapeuticClasses' => $therapeuticClasses,
                        'routes' => $routes,
                        'presentations' => $presentations,
                        'ageRestrictions' => $ageRestrictions,
                        'storageConditions' => $storageConditions,
                        'especiesOptions' => $especiesOptions,
                        'dispensingOptions' => $dispensingOptions,
                    ])
                </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('js')
    @include('petshop.vet.medicines._scripts')
@endsection