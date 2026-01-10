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
    <x-slot name="title" class="text-color">Planos</x-slot>
    <x-slot name="buttons">
        <div class="d-flex align-items-center justify-content-end gap-2">
            <a href="{{ route('petshop.criar.plano') }}" class="btn btn-success">
                <i class="ri-add-circle-fill"></i>
                Adicionar Novo Plano
            </a>
        </div>
    </x-slot>
    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-2">
            <div class="col-md-5">
                {!! Form::text('pesquisa', 'Pesquisar Plano')->placeholder('Nome ou Slug')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
                <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i>Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('petshop.gerenciar.planos') }}"><i class="ri-eraser-fill"></i>Limpar</a>
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