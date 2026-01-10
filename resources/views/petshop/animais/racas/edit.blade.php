@extends('default.layout', ['title' => 'Atualizar raça'])

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color">Atualizar raça</h3>

            <a href="{{ route('animais.racas.index',['page'=>request()->query('page', 1)]) }}"
               class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!!Form::open()->fill($item)
            ->put()
            ->id('form-racas')
            ->route('animais.racas.update', [$item->id])
            ->multipart()
            !!}
            <div class="pl-lg-4">
                @include('animais.racas._forms')
            </div>
            {!!Form::close()!!}
        </div>
    </div>
@endsection


