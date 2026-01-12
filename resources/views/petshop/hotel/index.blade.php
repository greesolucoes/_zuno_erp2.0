@extends('default.layout', ['title' => 'Reservas'])

@section('content')
<x-table
    :data="$data"
    :table_headers="[
        ['label' => 'O.S.', 'width' => '5%'],
        ['label' => 'Pet', 'width' => '10%'],
        ['label' => 'Cliente', 'width' => '15%'],
        ['label' => 'Quarto', 'width' => '10%'],
        ['label' => 'Situação', 'width' => '10%'],
        ['label' => 'Check in', 'width' => '10%', 'align' => 'left'],
        ['label' => 'Check out', 'width' => '10%', 'align' => 'left'],
        ['label' => 'Data de Cadastro', 'width' => '10%', 'align' => 'left'],
        ['label' => 'Valor', 'width' => '10%', 'align' => 'right'],
    ]"
    :modal_actions="false">
    <x-slot name="title" class="text-color">Reservas de Hotel</x-slot>
    <x-slot name="buttons">
        <a href="{{ route('hoteis.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Nova Reserva
        </a>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-5">
                {!! Form::text('pesquisa', 'Pesquisar Reserva: (Pet, Cliente)')->placeholder('Digite o dado')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('quarto_id', 'Quarto')
                    ->options(['' => 'Selecione um quarto'] + $quartos->pluck('nome', 'id')->all())
                    ->attrs(['class' => 'select2 ignore']) 
                !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('estado', 'Situação')
                    ->options([
                        '' => 'Todas',
                        'Agendado' => 'Agendado',
                        'Em andamento' => 'Em andamento',
                        'Concluído' => 'Concluído'
                    ])
                    ->attrs(['class' => 'select2 ignore']) 
                !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('checkin_start_date', 'Data inicial (Check in)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('checkout_end_date', 'Data inicial (Check out)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('start_date', 'Data inicial (Cadastro)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::date('end_date', 'Data final (Cadastro)')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3 text-left ">
                <br>
                <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('hoteis.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot name="search_form">

    @foreach($data as $item)
    @include('components.petshop.hoteis._table_row', ['item' => $item, 'servicos' => $servicos])
    @endforeach
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
<script type="text/javascript" src="/js/hotel.js"></script>
@endsection
