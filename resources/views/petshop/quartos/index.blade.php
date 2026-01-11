@extends('layouts.app', ['title' => 'Quartos'])

@section('content')
<x-table
    :data="$data"
    :table_headers="[
    ['label' => 'Colaborador Responsável', 'width' => '15%'],
    ['label' => 'Nome do Quarto', 'width' => '15%'],
    ['label' => 'Porte de Pets', 'width' => '10%'],
    ['label' => 'Situação', 'width' => '10%'],
    ['label' => 'Capacidade de Pets', 'width' => '15%'],
    ['label' => 'Data de cadastro', 'width' => '20%', 'align' => 'left'],
]"
    :modal_actions="false">
    <x-slot name="title" class="text-color">Gerenciar Quartos</x-slot>
    <x-slot name="buttons">

        <div class="d-flex align-items-center justify-content-end gap-2">
            <a href="{{ route('quartos.create') }}" class="btn btn-success">
                <i class="ri-add-circle-fill"></i>
                Novo Quarto
            </a>

        </div>

    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-3">
            <div class="col-md-5">
                {!! Form::text('pesquisa', 'Pesquisar quarto: (nome)')->placeholder('Digite o nome do quarto')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('status', 'Situação')
                    ->options([
                        '' => 'Todas',
                        'disponivel' => 'Disponível',
                        'em_limpeza' => 'Em Limpeza',
                        'manutencao' => 'Manutenção/Organização',
                        'em_uso' => 'Em uso com animal',
                        'reservado' => 'Reservado para serviço',
                        'bloqueado' => 'Bloqueado',
                    ])
                    ->attrs(['class' => 'select2 ignore']) 
                !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('tipo', 'Porte dos Pets')
                    ->options([
                        '' => 'Todos',
                        'pequeno' => 'Pequeno Porte',
                        'grande' => 'Grande Porte',
                        'individual' => 'Individual',
                        'coletivo' => 'Coletivo',
                    ])
                    ->attrs(['class' => 'select2 ignore']) 
                !!}
            </div>
            <div class="col-md-2">
                {!! 
                    Form::tel('start_capacidade', 'Capacidade inicial (Pets)')
                    ->placeholder('Digite um valor')
                    ->attrs([
                        'class' => 'ignore',
                        'data-mask' => '0000000'
                    ]) 
                !!}
            </div>
            <div class="col-md-2">
                {!! 
                    Form::tel('end_capacidade', 'Capacidade final (Pets)')
                    ->placeholder('Digite um valor')
                    ->attrs([
                        'class' => 'ignore',
                        'data-mask' => '0000000'
                    ]) 
                !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('start_date', 'Data inicial (Cadastro)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('end_date', 'Data final (Cadastro)')->attrs(['class' => 'ignore']) !!}
            </div>
         
            <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
                <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('quartos.index') }}"><i
                        class="ri-eraser-fill"></i>Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot name="search_form">

    @foreach($data as $item)
    @include('components.petshop.hoteis.quartos._table_row', ['item' => $item])
    @endforeach
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
<script type="text/javascript" src="/js/quartos.js"></script>
@endsection