@extends('default.layout', ['title' => 'Nova Tele-entrega'])

@section('css')
    <style>
        .form-input-100 .form-group {
            width: 100%;
        }
    </style>
@endsection

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('tele_entregas.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Nova Tele-entrega</h5>
            </div>
            <hr>

            {!! Form::open()->post()->id('form-especies')->route('tele_entregas.store')->multipart() !!}
            <div class="pl-lg-4">
                @include('tele_entregas._forms')
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection
