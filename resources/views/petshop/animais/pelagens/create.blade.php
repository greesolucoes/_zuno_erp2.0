@extends('layouts.app', ['title' => 'Nova pelagem'])

@section('content')
  <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="text-color">Nova pelagem</h3>

          <a href="{{ route('animais.pelagens.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
              <i class="ri-arrow-left-double-fill"></i>Voltar
          </a>
      </div>

      <div class="card-body">
          {!!Form::open()
          ->post()
          ->id('form-pelagem')
          ->route('animais.pelagens.store')
          ->multipart()
          !!}
            <div class="pl-lg-4">
                @include('animais.pelagens._forms')
            </div>
          {!!Form::close()!!}
      </div>
  </div>
@endsection


