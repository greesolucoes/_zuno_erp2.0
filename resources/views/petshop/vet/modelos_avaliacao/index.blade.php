@extends('default.layout', ['title' => 'Modelos de Avaliação'])

@section('content')
    @php
        $categoryFilterOptions = ['' => 'Todas'] + \App\Models\Petshop\ModeloAvaliacao::categories();
        $statusFilterOptions = ['' => 'Todos'] + \App\Models\Petshop\ModeloAvaliacao::statusOptions();
    @endphp

    <x-table
        :data="$modelosAvaliacao"
        :table_headers="[
            ['label' => 'Título do Modelo', 'width' => '35%'],
            ['label' => 'Categoria', 'width' => '20%'],
            ['label' => 'Atualizado em', 'width' => '10%', 'align' => 'left'],
            ['label' => 'Status', 'width' => '15%'],
        ]"
        :modal_actions="false"
    >
        <x-slot name="title">Modelos de avaliação</x-slot>

        <x-slot name="buttons">
            <a href="{{ route('vet.assessment-models.create') }}" type="button" class="btn btn-success">
                <i class="bx bx-plus"></i> Novo modelo
            </a>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::text('search', 'Buscar modelo')->placeholder('Digite o nome ou palavra-chave')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('category', 'Categoria', $categoryFilterOptions)->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('status', 'Status', $statusFilterOptions)->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.assessment-models.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($modelosAvaliacao as $item)
            @include('components.petshop.vet.modelos_avaliacao._table_row')
        @endforeach
    </x-table>
@endsection
