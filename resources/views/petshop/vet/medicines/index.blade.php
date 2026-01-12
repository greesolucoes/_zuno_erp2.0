@extends('default.layout', ['title' => 'Medicamentos'])

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
        <x-slot name="title">Medicamentos</x-slot>

        <x-slot name="buttons">
            <a href="{{ route('vet.medicines.create') }}" type="button" class="btn btn-success">
                <i class="bx bx-plus"></i> Novo medicamento
            </a>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
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

                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.medicines.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
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
