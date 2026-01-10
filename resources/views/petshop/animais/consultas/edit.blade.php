@extends('default.layout', ['title' => 'Atualizar consulta'])

@section('content')
  <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="text-color">Atualizar consulta</h4>

        <a href="{{ route('animais.consultas.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm px-3">
            <i class="ri-arrow-left-double-fill"></i>Voltar
        </a>
      </div>

      <div class="card-body">
          {!!Form::open()->fill($item)
          ->put()
          ->id('form-consultas')
          ->route('animais.consultas.update', [$item->id])
          ->multipart()
          !!}
            <div class="pl-lg-4">
                @include('petshop.animais.consultas._forms')
            </div>
          {!!Form::close()!!}
      </div>
  </div>
@endsection

