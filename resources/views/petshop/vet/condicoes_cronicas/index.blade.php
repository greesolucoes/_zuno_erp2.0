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

    <x-slot name="title" class="text-color">Gerenciar condições crônicas</x-slot>

    <x-slot name="buttons">
        <div class="d-flex align-items-center justify-content-end gap-2">
            <a href="{{ route('vet.chronic-conditions.create') }}" class="btn btn-success">
                <i class="ri-add-circle-fill"></i>
                Nova condição
            </a>
        </div>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-2">
            <div class="col-md-6">
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
            <div class="col-md-3 d-flex align-items-end gap-2 mt-3">
                <button class="btn btn-primary" type="submit">
                    <i class="ri-search-line"></i>Pesquisar
                </button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.chronic-conditions.index') }}">
                    <i class="ri-eraser-fill"></i>Limpar
                </a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @forelse ($condicoes as $condicaoCronica)
        @include('components.petshop.vet.condicoes_cronicas._table_row', [
            'item' => $condicaoCronica,
            'statusOptions' => $statusOptions,
        ])
    @empty
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">Nenhuma condição crônica cadastrada.</td>
        </tr>
    @endforelse
</x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection