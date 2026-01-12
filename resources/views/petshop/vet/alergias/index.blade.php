@extends('default.layout', ['title' => 'Alergias'])

@section('content')
<x-table
    :data="$alergias"
    :table_headers="[
        ['label' => 'Nome', 'width' => '30%', 'align' => 'start'],
        ['label' => 'Status', 'width' => '15%'],
        ['label' => 'Atualizado em', 'width' => '15%'],
        ['label' => 'Informações', 'width' => '30%', 'align' => 'start'],
    ]"
    :modal_actions="false">

    <x-slot name="title">Alergias</x-slot>

    <x-slot name="buttons">
        <a href="{{ route('vet.allergies.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Nova alergia
        </a>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-3">
                {!! Form::text('search', 'Pesquisar')
                    ->placeholder('Buscar por nome, descrição ou orientações')
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
                <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.allergies.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach ($alergias as $alergia)
        @include('components.petshop.vet.alergias._table_row', [
            'item' => $alergia,
            'statusOptions' => $statusOptions,
        ])
    @endforeach
</x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection
