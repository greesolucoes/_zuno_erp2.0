@extends('layouts.app', ['title' => 'Atualizar pet'])

@section('content')
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="text-color">Atualizar pet</h3>

    <a href="{{ route('animais.pacientes.index',['page'=>request()->query('page', 1)]) }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
      <i class="ri-arrow-left-double-fill"></i>Voltar
    </a>
  </div>

  <div class="card-body">
    {!!Form::open()->fill($item)
    ->put()
    ->id('form-pacientes')
    ->route('animais.pacientes.update', [$item->id])
    ->multipart()
    !!}
    <div class="pl-lg-4">
      @include('animais.pacientes._forms')
    </div>
    {!!Form::close()!!}
  </div>
</div>
@include('modals._novo_cliente')
@include('modals._pelagem')
@include('modals._raca')
@include('modals._especie')
@endsection