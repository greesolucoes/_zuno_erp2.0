@extends('default.layout', ['title' => 'Atendimentos Veterinários'])

@section('content')
    @php
        $encounterPaginator = $encounters instanceof \Illuminate\Pagination\LengthAwarePaginator ? $encounters : collect($encounters);
        $encounterCollection = $encounterPaginator instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? collect($encounterPaginator->items())
            : collect($encounterPaginator);
        $upcomingEncounters = collect($upcomingEncounters ?? [])
            ->sortBy(fn ($encounter) => $encounter['start'] ?? '')
            ->values();
        $statusBreakdown = collect($statusBreakdown ?? [])
            ->groupBy('status')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'status' => $first['status'] ?? 'Status',
                    'count' => $group->count(),
                    'status_color' => $first['status_color'] ?? 'primary',
                ];
            })
            ->values();
        $alerts = collect($alerts ?? [])
            ->filter(fn ($encounter) => in_array($encounter['status_color'] ?? 'primary', ['warning', 'danger']))
            ->values();
    @endphp

     <div id="vet-atendimentos-table-wrapper" class="vet-atendimentos-table-wrapper">
        <x-table
            :data="$encounters"
            :table_headers="[
                ['label' => 'Paciente', 'width' => '15%', 'align' => 'left'],
                ['label' => 'Veterinário', 'width' => '16%', 'align' => 'left'],
                ['label' => 'Serviço', 'width' => '18%'],
                ['label' => 'Início do atendimento', 'width' => '14%', 'align' => 'left'],
                ['label' => 'Status', 'width' => '12%'],
            ]"
            :modal_actions="false"
        >
        <x-slot name="title" class="text-color">Gerenciar Atendimentos</x-slot>
        <x-slot name="buttons">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vet-appointments-overview-modal">
                    <i class="ri-bar-chart-2-line"></i>
                    Visão geral
                </button>
                <a href="{{ route('vet.atendimentos.create') }}" class="btn btn-success">
                    <i class="ri-add-circle-fill"></i>
                    Novo atendimento
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        {!! Form::text('search', 'Pesquisar atendimento')
                            ->placeholder('Código, paciente ou tutor')
                            ->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'status',
                            'Status',
                            ['' => 'Todos'] + collect($filters['status'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'veterinarian',
                            'Veterinário responsável',
                            ['' => 'Todos'] + collect($filters['veterinarians'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'service',
                            'Serviço',
                            ['' => 'Todos'] + collect($filters['services'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'pet',
                            'Paciente',
                            ['' => 'Todos'] + collect($filters['pets'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('start', 'Data inicial')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('end', 'Data final')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2 mt-2 mt-md-0">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a class="btn btn-danger" id="clear-filter" href="{{ route('vet.atendimentos.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($encounterCollection as $encounter)
            @include('components.petshop.vet.atendimentos._table_row', [
                'encounter' => $encounter,
                'modalId' => 'vet-encounter-modal-' . \Illuminate\Support\Str::slug($encounter['code'] ?? uniqid()),
            ])
        @endforeach
        
        </x-table>
    </div>

    @foreach ($encounterCollection as $encounter)
        @include('petshop.vet.atendimentos.partials.encounter-modal', [
            'encounter' => $encounter,
            'modalId' => 'vet-encounter-modal-' . \Illuminate\Support\Str::slug($encounter['code'] ?? uniqid()),
            'statusColor' => $encounter['status_color'] ?? 'primary',
        ])
    @endforeach

    @include('petshop.vet.atendimentos.partials.overview-modal', [
        'summary' => $summary,
        'timeline' => $timeline,
        'upcomingEncounters' => $upcomingEncounters,
        'statusBreakdown' => $statusBreakdown,
    ])

    @include('petshop.vet.atendimentos.partials.alerts-modal', [
        'alerts' => $alerts,
    ])
@endsection

@section('js')
    <script src="{{ asset('js/vet/atendimentos.js') }}"></script>
@endsection