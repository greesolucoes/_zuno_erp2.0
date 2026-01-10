@extends('layouts.app', ['title' => 'Modelos de Prescrição'])

@section('content')
    @php
        $categoryFilterOptions = ['' => 'Todas'] + \App\Support\Petshop\Vet\PrescriptionModelOptions::categories();
        $statusFilterOptions = ['' => 'Todos'] + \App\Support\Petshop\Vet\PrescriptionModelOptions::statusOptions();
    @endphp

    <x-table
        :data="$modelosPrescricao"
        :table_headers="[
            ['label' => 'Título do Modelo', 'width' => '35%'],
            ['label' => 'Categoria', 'width' => '20%'],
            ['label' => 'Atualizado em', 'width' => '10%', 'align' => 'left'],
            ['label' => 'Status', 'width' => '15%'],
        ]"
        :modal_actions="false"
    >
        <x-slot name="title" class="text-color">Modelos de Prescrição</x-slot>

        <x-slot name="description">
            <p class="mb-0 text-muted">
                Organize protocolos terapêuticos, prescrições de alta e tratamentos contínuos em modelos padronizados para agilizar o dia a dia do time clínico.
            </p>
        </x-slot>

        <x-slot name="buttons">
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('vet.prescription-models.create') }}" class="btn btn-success">
                    <i class="ri-add-circle-fill"></i>
                    Criar Modelo de Prescrição
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
                        <a id="clear-filter" class="btn btn-danger flex-fill" href="{{ route('vet.prescription-models.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @forelse ($modelosPrescricao as $item)
            @include('components.petshop.vet.modelos_prescricao._table_row')
        @empty
            <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                    Nenhum modelo de prescrição cadastrado até o momento.
                </td>
            </tr>
        @endforelse
    </x-table>
@endsection