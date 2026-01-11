@extends('layouts.app', ['title' => 'Editar Reserva de Hotel'])

@section('content')
  <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="text-color">Editar Reserva de Hotel</h3>

          <a href="{{ route('hoteis.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
              <i class="ri-arrow-left-double-fill"></i>Voltar
          </a>
      </div>

      <div class="card-body">
          {!!Form::open()->fill($data)
          ->put()
          ->route('hoteis.update', [$data->id])
          ->multipart()
          ->id('main-form')
          !!}
          <div class="pl-lg-4">
              @include('hoteis._forms')
          </div>
          {!!Form::close()!!}
      </div>
  </div>
@endsection
