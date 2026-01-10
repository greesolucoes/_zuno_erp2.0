@extends('default.layout', ['title' => 'Salas de atendimento'])

@section('content')
<x-table
    :data="$salas"
    :table_headers="[
        ['label' => 'Sala', 'width' => '25%'],
        ['label' => 'Tipo', 'width' => '15%'],
        ['label' => 'Status', 'width' => '15%'],
        ['label' => 'Capacidade', 'width' => '15%'],
        ['label' => 'Atualizado em', 'width' => '15%'],
    ]"
    :modal_actions="false">

    <x-slot name="title">Salas de atendimento</x-slot>

    <x-slot name="buttons">
        <a href="{{ route('vet.salas-atendimento.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Nova sala
        </a>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-3">
                {!! Form::text('busca', 'Pesquisar Sala: (Nome, Identificador, Equipamento)')
                    ->placeholder('Digite o dado')
                    ->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('tipo', 'Tipo')
                    ->options(['' => 'Todos'] + $tiposSala)
                    ->value(request('tipo'))
                    ->attrs(['class' => 'form-select select2 ignore']) !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('status', 'Status')
                    ->options(['' => 'Todos'] + $statusSala)
                    ->value(request('status'))
                    ->attrs(['class' => 'form-select select2 ignore']) !!}
            </div>
            <div class="col-md-3 text-left">
                <br>
                <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.salas-atendimento.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach ($salas as $sala)
        @include('components.petshop.vet.salas_atendimento._table_row', [
            'item' => $sala,
            'tiposSala' => $tiposSala,
            'statusSala' => $statusSala,
        ])
    @endforeach
</x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection
