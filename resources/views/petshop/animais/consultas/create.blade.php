@extends('default.layout', ['title' => 'Nova consulta'])

@section('content')
  <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="text-color">Nova consulta</h4>

        <a href="{{ route('animais.consultas.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm px-3">
            <i class="ri-arrow-left-double-fill"></i>Voltar
        </a>
      </div>

      <div class="card-body">
          {!!Form::open()
          ->post()
          ->id('form-consultas')
          ->route('animais.consultas.store')
          ->multipart()
          !!}
            <div class="pl-lg-4">
                @include('petshop.animais.consultas._forms')
            </div>
          {!!Form::close()!!}
      </div>
  </div>
@endsection

