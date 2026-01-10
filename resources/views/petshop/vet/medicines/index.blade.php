@extends('layouts.app', ['title' => 'Medicamentos'])

@section('content')
    <x-table
        :data="$medicines"
        :table_headers="[
            ['label' => 'Medicamento', 'width' => '32%', 'align' => 'left'],
            ['label' => 'Categoria terapêutica', 'width' => '20%'],
            ['label' => 'Via', 'width' => '10%'],
            ['label' => 'Espécies', 'width' => '18%'],
            ['label' => 'Estoque', 'width' => '10%'],
            ['label' => 'Status', 'width' => '10%'],
        ]"
        :modal_actions="false"
        :pagination="false"
    >
        <x-slot name="title" class="text-color">Catálogo de Medicamentos</x-slot>

        <x-slot name="buttons">
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('vet.medicines.create') }}" class="btn btn-success">
                    <i class="ri-add-circle-fill"></i>
                    Novo Medicamento
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        {!! Form::text('search', 'Pesquisar medicamento (Nome, princípio ativo, indicação)')
                            ->placeholder('Digite o dado')
                            ->attrs(['class' => 'ignore']) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::select('classe_terapeutica', 'Categoria terapêutica', $therapeuticClassOptions)
                            ->attrs(['class' => 'form-select ignore']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::select('via_administracao', 'Via de administração', $routeOptions)
                            ->attrs(['class' => 'form-select ignore']) !!}
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2 mt-2 mt-md-0">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>

                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.medicines.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($medicines as $medicine)
            @include('components.petshop.vet.medicines._table_row', ['medicine' => $medicine])
        @endforeach
    </x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection