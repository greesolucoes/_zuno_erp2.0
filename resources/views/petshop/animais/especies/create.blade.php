@extends('layouts.app', ['title' => 'Nova espécie'])

@section('content')
  <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
         <h3 class="text-color">Nova espécie</h3>

          <a href="{{ route('animais.especies.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
              <i class="ri-arrow-left-double-fill"></i>Voltar
          </a>
      </div>

      <div class="card-body">
          {!!Form::open()
          ->post()
          ->id('form-especies')
          ->route('animais.especies.store')
          ->multipart()
          !!}
            <div class="pl-lg-4">
                @include('animais.especies._forms')
            </div>
          {!!Form::close()!!}
      </div>
  </div>
@endsection


