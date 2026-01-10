@extends('default.layout', ['title' => 'Condições crônicas'])

@section('content')
<x-table
    :data="$condicoes"
    :table_headers="[
        ['label' => 'Nome', 'width' => '30%', 'align' => 'start'],
        ['label' => 'Status', 'width' => '15%'],
        ['label' => 'Atualizado em', 'width' => '15%'],
        ['label' => 'Informações', 'width' => '30%', 'align' => 'start'],
    ]"
    :modal_actions="false">

    <x-slot name="title">Condições crônicas</x-slot>

    <x-slot name="buttons">
        <a href="{{ route('vet.chronic-conditions.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Nova condição
        </a>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-3">
                {!! Form::text('search', 'Pesquisar')
                    ->placeholder('Buscar por nome, descrição ou planos de cuidado')
                    ->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('status', 'Status')
                    ->options(['' => 'Todos'] + $statusOptions)
                    ->value(request('status'))
                    ->attrs(['class' => 'form-select select2 ignore']) !!}
            </div>
            <div class="col-md-3 text-left">
                <br>
                <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.chronic-conditions.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach ($condicoes as $condicaoCronica)
        @include('components.petshop.vet.condicoes_cronicas._table_row', [
            'item' => $condicaoCronica,
            'statusOptions' => $statusOptions,
        ])
    @endforeach
</x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection
