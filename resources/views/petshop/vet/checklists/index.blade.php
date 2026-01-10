@extends('default.layout', ['title' => 'Checklists'])

@section('content')
<x-table
    :data="$checklists"
    :table_headers="[
        ['label' => 'Título', 'width' => '30%', 'align' => 'start'],
        ['label' => 'Status', 'width' => '15%'],
        ['label' => 'Qtd. Itens', 'width' => '15%'],
        ['label' => 'Atualizado em', 'width' => '15%'],
        ['label' => 'Descrição', 'width' => '25%', 'align' => 'start'],
    ]"
    :modal_actions="false">

    <x-slot name="title">Checklists</x-slot>

    <x-slot name="buttons">
        <a href="{{ route('vet.checklist.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Novo checklist
        </a>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-3">
                {!! Form::text('search', 'Pesquisar')
                    ->placeholder('Buscar por título ou descrição')
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
                <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.checklist.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach ($checklists as $checklist)
        @include('components.petshop.vet.checklists._table_row', [
            'item' => $checklist,
            'statusOptions' => $statusOptions,
        ])
    @endforeach
</x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection
