@extends('default.layout', ['title' => 'Atualizar espécie'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Atualizar espécie</h3>

            <a href="{{ route('animais.especies.index',['page'=>request()->query('page', 1)]) }}"
               class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!!Form::open()->fill($item)
            ->put()
            ->id('form-especies')
            ->route('animais.especies.update', [$item->id])
            ->multipart()
            !!}
            <div class="pl-lg-4">
                @include('petshop.animais.especies._forms')
            </div>
            {!!Form::close()!!}
        </div>
    </div>
@endsection

