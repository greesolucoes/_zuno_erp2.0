@extends('layouts.app', ['title' => 'Modelos de Avaliação'])

@section('content')
    @php
        $categoryFilterOptions = ['' => 'Todas'] + \App\Support\Petshop\Vet\AssessmentModelOptions::categories();
        $statusFilterOptions = ['' => 'Todos'] + \App\Support\Petshop\Vet\AssessmentModelOptions::statusOptions();
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
        <x-slot name="title" class="text-color">Modelos de Avaliação</x-slot>

        <x-slot name="description">
            <p class="mb-0 text-muted">
                Cadastre modelos padronizados para agilizar a avaliação clínica e garantir consistência nos atendimentos
                veterinários.
            </p>
        </x-slot>

        <x-slot name="buttons">
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('vet.assessment-models.create') }}" class="btn btn-success">
                    <i class="ri-add-circle-fill"></i>
                    Criar Modelo de Avaliação
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        {!! Form::text('search', 'Buscar modelo')->placeholder('Digite o nome ou palavra-chave')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('category', 'Categoria', $categoryFilterOptions)->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('status', 'Status', $statusFilterOptions)->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2 mt-2 mt-md-0 flex-wrap">
                        <button class="btn btn-primary flex-fill" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger flex-fill" href="{{ route('vet.assessment-models.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @forelse ($modelosAvaliacao as $item)
            @include('components.petshop.vet.modelos_avaliacao._table_row')
        @empty
            <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                    Nenhum modelo de avaliação cadastrado até o momento.
                </td>
            </tr>
        @endforelse
    </x-table>
@endsection