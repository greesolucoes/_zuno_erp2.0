@extends('layouts.app', ['title' => 'Novo Plano Petshop'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Novo Plano</h3>
            <a href="{{ route('petshop.gerenciar.planos', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
        <div class="card-body">
            {!! Form::open()->post()->route('petshop.planos.store')->id('main-form')->fill($planoData ?? []) !!}
                <div class="pl-lg-4">
                    @include('petshop.planos._form', ['servicos' => $servicos ?? null, 'produtos' => $produtos ?? null, 'planoData' => $planoData ?? null])
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('js')
    <script src="/js/petshop_planos_beneficios.js"></script>
    <script src="/js/petshop_planos_valores.js"></script>
    <script src="/js/petshop_planos_vigencia.js"></script>
@endsection