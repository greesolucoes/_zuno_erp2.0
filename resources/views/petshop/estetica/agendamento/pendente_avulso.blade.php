@extends('layouts.app', ['title' => 'Agendamentos pendentes (avulso)'])

@section('content')
<x-table
    :data="$data"
    :table_headers="[
        ['label' => 'Pet', 'width' => '15%'],
        ['label' => 'Cliente', 'width' => '15%'],
        ['label' => 'Serviço', 'width' => '15%'],
        ['label' => 'Data', 'width' => '10%'],
        ['label' => 'Horário', 'width' => '10%'],
        ['label' => 'Cadastro', 'width' => '10%'],
        ['label' => 'Status', 'width' => '15%'],
        ['label' => 'O.S', 'width' => '10%'],
        ['label' => 'Valor', 'width' => '10%'],
    ]"
    :modal_actions="false">
    <x-slot name="title" class="text-color">Agendamentos pendentes (avulso)</x-slot>
    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-2">
            <div class="col-md-5">
                {!! Form::text('pesquisa', 'Pesquisar Estetica: (Pet, Cliente)')->placeholder('Digite o dado')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
                <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i>Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('petshop.esteticista.agendamentos.pendente-avulso') }}"><i class="ri-eraser-fill"></i>Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach($data as $item)
        @include('components.petshop.esteticas._table_row', ['item' => $item])
    @endforeach
</x-table>
@endsection