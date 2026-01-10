@extends('layouts.app', ['title' => 'Histórico de Internação'])

@section('content')
    <x-table
        :data="collect($hospitalizations)"
        :table_headers="[
            ['label' => 'Paciente', 'width' => '15%', 'align' => 'left'],
            ['label' => 'Tutor', 'width' => '15%', 'align' => 'left'],
            ['label' => 'Profissional responsável', 'width' => '14%', 'align' => 'left'],
            ['label' => 'Status clínico', 'width' => '12%'],
            ['label' => 'Unidade', 'width' => '14%', 'align' => 'left'],
            ['label' => 'Admissão', 'width' => '12%', 'align' => 'left'],
            ['label' => 'Previsão de alta', 'width' => '12%', 'align' => 'left'],
        ]"
        :modal_actions="false"
        :pagination="false"
    >
        <x-slot name="title" class="text-color">Gerenciar Internações</x-slot>

        <x-slot name="buttons">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                <a href="{{ route('vet.hospitalizations.create') }}" class="btn btn-success d-flex align-items-center gap-1">
                    <i class="ri-add-circle-fill"></i>
                    Nova internação
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2">
                    <div class="col-md-4">
                        {!! Form::text('pesquisa', 'Pesquisar paciente ou tutor')
                            ->placeholder('Digite o dado')
                            ->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select(
                            'status',
                            'Status clínico',
                            ['' => 'Todos'] + collect($filters['status'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select(
                            'sector',
                            'Setor',
                            ['' => 'Todos'] + collect($filters['sectors'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'nivel_risco',
                            'Risco assistencial',
                            ['' => 'Todos'] + collect($filters['risk_levels'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data de admissão (início)')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data de admissão (fim)')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-1 mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.hospitalizations.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($hospitalizations as $hospitalization)
            @include('components.petshop.vet.internacoes._table_row', [
                'hospitalization' => $hospitalization,
                'modalId' => 'vet-hosp-modal-' . $hospitalization->id,
            ])
        @endforeach
    </x-table>

    @foreach ($hospitalizations as $hospitalization)
        @include('petshop.vet.internacoes.partials.patient-modal', [
            'hospitalization' => $hospitalization,
            'modalId' => 'vet-hosp-modal-' . $hospitalization->id,
        ])
    @endforeach

    @include('petshop.vet.internacoes.partials.overview-modal', [
        'overview' => $overview,
        'capacity' => $capacity,
    ])

    @include('petshop.vet.internacoes.partials.alerts-modal', [
        'alerts' => $alerts,
    ])
@endsection