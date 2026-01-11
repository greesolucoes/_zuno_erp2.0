@extends('layouts.app', ['title' => 'Editar Evento de Quarto'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Editar Evento de Quarto</h3>

            <a href="{{ route('quartos.eventos.index', ['quarto_id' => $item->quarto_id]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!! Form::open()->put()->route('quartos.eventos.update', [$item->id])->id('main-form') !!}
                @include('quartos.eventos._forms')
            {!! Form::close() !!}
        </div>
    </div>
@endsection 