@extends('layouts.app', ['title' => 'Editar Quarto'])

@section('content')
  <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="text-color">Editar Quarto</h3>

          <a href="{{ route('quartos.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
              <i class="ri-arrow-left-double-fill"></i>Voltar
          </a>
      </div>

      <div class="card-body">
          {!!Form::open()->fill($quarto)
          ->put()
          ->route('quartos.update', [$quarto->id])
          ->multipart()
          ->id('main-form')
          !!}
          <div class="pl-lg-4">
              @include('quartos._forms')
          </div>
          {!!Form::close()!!}
      </div>
  </div>
@endsection
