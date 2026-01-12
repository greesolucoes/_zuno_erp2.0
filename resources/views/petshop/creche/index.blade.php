@extends('default.layout', ['title' => 'Creches'])

@section('content')
<x-table
    :data="$data"
    :table_headers="[
        ['label' => 'O.S.', 'width' => '5%'],
        ['label' => 'Pet', 'width' => '10%'],
        ['label' => 'Cliente', 'width' => '15%'],
        ['label' => 'Situação', 'width' => '10%'],
        ['label' => 'Turma', 'width' => '10%'],
        ['label' => 'Data de Entrada', 'width' => '10%', 'align' => 'left'],
        ['label' => 'Data de Saída', 'width' => '10%', 'align' => 'left'],
        ['label' => 'Data de Cadastro', 'width' => '10%', 'align' => 'left'],
        ['label' => 'Valor', 'width' => '10%', 'align' => 'right'],
    ]"
    :modal_actions="false">
    <x-slot name="title" class="text-color">Reservas de Creche</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('creches.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Nova Reserva
        </a>

    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-5">
                {!! Form::text('pesquisa', 'Pesquisar Creche: (Pet, Cliente)')->placeholder('Digite o dado')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('turma_id', 'Turma')
                    ->options(['' => 'Selecione um quarto'] + $turmas->pluck('nome', 'id')->all())
                    ->attrs(['class' => 'select2 ignore']) 
                !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('estado', 'Situação')
                    ->options([
                        '' => 'Todas',
                        'agendado' => 'Agendado',
                        'em_andamento' => 'Em andamento',
                        'concluido' => 'Concluído'
                    ])
                    ->attrs(['class' => 'select2 ignore']) 
                !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('start_date', 'Data Inicial (Data entrada)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('end_date', 'Data Final (Data saída)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('created_at_start_date', 'Data Inicial (Cadastro)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('created_at_end_date', 'Data Final (Cadastro)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3 text-left ">
                <br>
                <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('creches.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot name="search_form">

    @foreach($data as $item)
        @include('components.petshop.creches._table_row', ['item' => $item, 'servicos' => $servicos])
    @endforeach
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
<script type="text/javascript" src="/js/creche.js"></script>
@endsection
