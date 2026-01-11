@extends('layouts.app', ['title' => 'Editar Reserva de Creche'])

@section('content')
  <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="text-color">Editar Reserva de Creche</h3>

          <a href="{{ route('creches.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
              <i class="ri-arrow-left-double-fill"></i>Voltar
          </a>
      </div>

      <div class="card-body">
          {!!Form::open()->fill($data)
          ->put()
          ->route('creches.update', [$data->id])
          ->multipart()
          ->id('main-form')
          !!}
          <div class="pl-lg-4">
              @include('creches._forms')
          </div>
          {!!Form::close()!!}
      </div>
  </div>
@endsection