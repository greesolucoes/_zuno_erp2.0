@extends('default.layout', ['title' => 'Novo checklist'])

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="text-color">Novo checklist</h3>

        <a href="{{ route('vet.checklist.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
            <i class="ri-arrow-left-double-fill"></i>Voltar
        </a>
    </div>

    <div class="card-body">
        {!! Form::open()
            ->post()
            ->id('form-checklists')
            ->route('vet.checklist.store') !!}
        <div class="pl-lg-4">
            @include('petshop.vet.checklists._form')
        </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection