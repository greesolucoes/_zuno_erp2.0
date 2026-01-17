@extends('default.layout', ['title' => 'Atendimentos Veterinários'])

@section('css')
    @parent
    <style>
        .vet-encounters-row-actions {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
        }

        .vet-encounters-row-btn {
            height: 2.05rem;
            border-radius: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            padding: 0 .75rem;
            line-height: 1;
            white-space: nowrap;
        }

        .vet-encounters-row-btn--icon {
            width: 2.05rem;
            padding: 0;
        }

        .vet-encounters-row-btn--actions {
            border-color: rgba(13, 110, 253, 0.35);
        }

        .vet-encounters-row-btn--actions:hover {
            border-color: rgba(13, 110, 253, 0.6);
        }

        .vet-encounters-row-btn--delete {
            border-color: rgba(220, 53, 69, 0.35);
        }

        .vet-encounters-row-btn--delete:hover {
            border-color: rgba(220, 53, 69, 0.65);
        }

        .vet-encounters-row-indicators {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .vet-encounters-row-indicators__divider {
            width: 1px;
            height: 1.4rem;
            background: rgba(33, 37, 41, 0.12);
            margin: 0 .15rem;
        }

        .vet-encounters-row-indicator {
            position: relative;
            width: 2.05rem;
            height: 2.05rem;
            border-radius: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(108, 117, 125, 0.10);
            border: 1px solid rgba(108, 117, 125, 0.20);
            color: #6c757d;
        }

        .vet-encounters-row-indicator.is-linked {
            background: rgba(40, 199, 111, 0.14);
            border-color: rgba(40, 199, 111, 0.35);
            color: #1a7f4b;
        }

        .vet-encounters-row-indicator.is-finance {
            background: rgba(85, 110, 230, 0.10);
            border-color: rgba(85, 110, 230, 0.22);
            color: #3b4bb6;
        }

        .vet-encounters-row-indicator.is-finance.is-billed {
            background: rgba(85, 110, 230, 0.16);
            border-color: rgba(85, 110, 230, 0.35);
        }

        .vet-encounters-row-indicator__count {
            position: absolute;
            top: -0.25rem;
            right: -0.25rem;
            min-width: 1.1rem;
            height: 1.1rem;
            padding: 0 .25rem;
            border-radius: 999px;
            background: #28c76f;
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        .vet-encounters-row-indicator.is-new .vet-encounters-row-indicator__count {
            display: none;
        }
    </style>
@endsection

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
        <x-slot name="title">Atendimentos</x-slot>
        <x-slot name="buttons">
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#vet-appointments-overview-modal">
                <i class="bx bx-bar-chart"></i> Visão geral
            </button>
            <a href="{{ route('vet.atendimentos.create') }}" type="button" class="btn btn-success">
                <i class="bx bx-plus"></i> Novo atendimento
            </a>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
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
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a class="btn btn-danger" id="clear-filter" href="{{ route('vet.atendimentos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
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
