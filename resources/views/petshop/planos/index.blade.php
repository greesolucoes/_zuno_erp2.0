@extends('default.layout', ['title' => 'Planos Petshop'])

@section('content')
<x-table
    :data="$planos"
    :table_headers="[
        ['label' => 'Slug', 'width' => '25%'],
        ['label' => 'Nome', 'width' => '45%'],
        ['label' => 'Ativo', 'width' => '15%'],
    ]"
    :modal_actions="false">
    <x-slot name="title">Planos</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('petshop.criar.plano') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Novo plano
        </a>
    </x-slot>
    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-3">
                {!! Form::text('pesquisa', 'Pesquisar Plano')->placeholder('Nome ou Slug')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3 text-left">
                <br>
                <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('petshop.gerenciar.planos') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach($planos as $item)
        @include('components.petshop.planos._table_row', ['item' => $item])
    @endforeach
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection
