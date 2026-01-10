@extends('layouts.app', ['title' => 'Médicos Veterinários'])

@section('content')
    <x-table
        :data="$medicos"
        :table_headers="[
            ['label' => 'Médico', 'width' => '30%', 'align' => 'left'],
            ['label' => 'CRMV', 'width' => '15%'],
            ['label' => 'Especialidade', 'width' => '20%'],
            ['label' => 'Status', 'width' => '10%'],
        ]"
        :modal_actions="false"
    >
        <x-slot name="title" class="text-color">Gerenciar Médicos Veterinários</x-slot>

        <x-slot name="buttons">
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('vet.medicos.create', ['page' => request()->query('page', 1)]) }}" class="btn btn-success">
                    <i class="ri-add-circle-fill"></i>
                    Novo Médico
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        {!! Form::text('search', 'Pesquisar médico (Nome, CRMV, especialidade)')
                            ->placeholder('Digite o dado')
                            ->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('status', 'Status', [
                            '' => 'Todos',
                            'ativo' => 'Ativo',
                            'inativo' => 'Inativo',
                        ])->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2 mt-2 mt-md-0">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.medicos.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($medicos as $medico)
            @include('components.petshop.vet.medicos._table_row', ['medico' => $medico])
        @endforeach
    </x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection