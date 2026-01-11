@extends('layouts.app', ['title' => 'Cadastrar Evento de Quarto'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Novo Evento de Quarto</h3>

            <a href="{{ route('quartos.eventos.index', ['quarto_id' => $quartoId]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!! Form::open()->post()->route('quartos.eventos.store')->id('main-form') !!}
                @include('quartos.eventos._forms')
            {!! Form::close() !!}
        </div>
    </div>
@endsection 