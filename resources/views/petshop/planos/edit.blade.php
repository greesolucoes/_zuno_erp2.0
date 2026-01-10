@extends('layouts.app', ['title' => 'Editar Plano Petshop'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Editar Plano</h3>
            <a href="{{ route('petshop.gerenciar.planos', ['page' => request()->query('page', 1)]) }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
        <div class="card-body">
            {!! Form::open()->fill($planoData)->put()->route('petshop.planos.update', [$plano->id])->id('main-form') !!}
                <div class="pl-lg-4">
                    @include('petshop.planos._form', ['servicos' => $servicos, 'produtos' => $produtos, 'planoData' => $planoData])
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