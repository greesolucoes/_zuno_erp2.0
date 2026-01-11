@extends('layouts.app', ['title' => 'Novo Quarto'])

@section('content')
  <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="text-color">Novo Quarto</h3>

          <a href="{{ route('quartos.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
              <i class="ri-arrow-left-double-fill"></i>Voltar
          </a>
      </div>

      <div class="card-body">
          {!!Form::open()
            ->post()
            ->route('quartos.store')
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
