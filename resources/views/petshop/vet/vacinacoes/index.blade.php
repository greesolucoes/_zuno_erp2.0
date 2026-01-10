@extends('default.layout', ['title' => $pageTitle ?? 'Vacinações Veterinárias'])

@section('css')
    <style>
        .vet-vacinacoes__summary-card {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.06);
            background: #fff;
            box-shadow: 0 10px 24px rgba(22, 22, 107, 0.08);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .vet-vacinacoes__summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(22, 22, 107, 0.12);
        }

        .vet-vacinacoes__summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .vet-vacinacoes__badge-soft-primary {
            color: #556ee6;
            background: rgba(85, 110, 230, 0.12);
        }

        .vet-vacinacoes__badge-soft-success {
            color: #34c38f;
            background: rgba(52, 195, 143, 0.15);
        }

        .vet-vacinacoes__badge-soft-warning {
            color: #ffae00;
            background: rgba(255, 174, 0, 0.18);
        }

        .vet-vacinacoes__badge-soft-danger {
            color: #f46a6a;
            background: rgba(244, 106, 106, 0.18);
        }

        .vet-vacinacoes__tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(22, 22, 107, 0.06);
            color: #16166b;
            font-size: .75rem;
            font-weight: 600;
        }

        .vet-vacinacoes__detail-card {
            border-radius: 20px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 12px 30px rgba(22, 22, 107, 0.08);
        }

        .vet-vacinacoes__detail-section + .vet-vacinacoes__detail-section {
            border-top: 1px solid rgba(22, 22, 107, 0.08);
            padding-top: 1.25rem;
            margin-top: 1.25rem;
        }

        .vet-vacinacoes__timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 14px;
            width: 2px;
            background: rgba(85, 110, 230, 0.25);
        }

        .vet-vacinacoes__timeline-item {
            padding-left: 36px;
            position: relative;
        }

        .vet-vacinacoes__timeline-item::before {
            content: '';
            position: absolute;
            top: 4px;
            left: 6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #556ee6;
            box-shadow: 0 0 0 4px rgba(85, 110, 230, 0.15);
        }

        .vet-vacinacoes__timeline-date {
            font-size: .75rem;
            font-weight: 600;
            color: #556ee6;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .vet-vacinacoes__check-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .vet-vacinacoes__status-badge {
            border-radius: 999px;
            padding: .35rem .85rem;
            font-weight: 600;
            font-size: .75rem;
        }

        .vet-vacinacoes__metric-card {
            border-radius: 14px;
            padding: 1rem;
            border: 1px solid rgba(85, 110, 230, 0.12);
            background: rgba(85, 110, 230, 0.05);
        }

        .vet-vacinacoes__metric-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #16166b;
        }

        .vet-vacinacoes__alert-card {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            padding: 1rem;
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            background: #fff;
        }

        .vet-vacinacoes__filters .form-control,
        .vet-vacinacoes__filters .form-select {
            border-radius: 12px;
        }

        .vet-vacinacoes__badge {
            border-radius: 999px;
            padding: .3rem .75rem;
            font-weight: 600;
            font-size: .75rem;
        }

        @media (max-width: 767px) {
            .vet-vacinacoes__summary-card {
                box-shadow: 0 12px 26px rgba(22, 22, 107, 0.12);
            }
        }
    </style>
@endsection

@section('content')
    @php
        $vaccinationCollection = $vaccinations instanceof \Illuminate\Support\Collection
            ? $vaccinations
            : collect($vaccinations);

        $vaccinationAlerts = isset($vaccinationAlerts)
            ? ($vaccinationAlerts instanceof \Illuminate\Support\Collection
                ? $vaccinationAlerts
                : collect($vaccinationAlerts))
            : $vaccinationCollection->flatMap(function ($item) {
                return collect($item['alerts'] ?? [])->map(function ($alert) use ($item) {
                    return $alert + [
                        'patient' => $item['patient'] ?? 'Paciente sem nome',
                        'code' => $item['code'] ?? null,
                    ];
                });
            });

        $vaccinationReminders = isset($vaccinationReminders)
            ? ($vaccinationReminders instanceof \Illuminate\Support\Collection
                ? $vaccinationReminders
                : collect($vaccinationReminders))
            : $vaccinationCollection->flatMap(function ($item) {
                return collect($item['reminders'] ?? [])->map(function ($reminder) use ($item) {
                    return [
                        'text' => $reminder,
                        'patient' => $item['patient'] ?? 'Paciente sem nome',
                        'code' => $item['code'] ?? null,
                    ];
                });
            });

        $statusBreakdown = isset($statusBreakdown)
            ? ($statusBreakdown instanceof \Illuminate\Support\Collection
                ? $statusBreakdown
                : collect($statusBreakdown))
            : $vaccinationCollection
                ->groupBy(fn ($item) => $item['status'] ?? 'Sem status')
                ->map->count();

        $upcomingVaccinations = isset($upcomingVaccinations)
            ? ($upcomingVaccinations instanceof \Illuminate\Support\Collection
                ? $upcomingVaccinations
                : collect($upcomingVaccinations))
            : $vaccinationCollection->take(4);

        $vaccinationAlerts = $vaccinationAlerts->values();
        $vaccinationReminders = $vaccinationReminders->values();
        $upcomingVaccinations = $upcomingVaccinations->values();

        $clearFilterRoute = route((($viewMode ?? null) === 'scheduled')
            ? 'vet.vaccinations.scheduled'
            : 'vet.vaccinations.index');
    @endphp

    @if (($viewMode ?? null) === 'history')
        <div class="alert alert-info border-0 bg-info-subtle text-info mb-4">
            Listando apenas vacinas com aplicação registrada.
        </div>
    @elseif (($viewMode ?? null) === 'scheduled')
        <div class="alert alert-primary border-0 bg-primary-subtle text-primary mb-4">
            Listando vacinas agendadas prontas para aplicação.
        </div>
    @endif

    <x-table
        :data="$vaccinationCollection"
        :table_headers="[
            ['label' => 'Paciente', 'width' => '15%', 'align' => 'left'],
            ['label' => 'Vacina', 'width' => '15%', 'align' => 'left'],
            ['label' => 'Agendamento', 'width' => '12%', 'align' => 'left'],
            ['label' => 'Veterinário', 'width' => '16%'],
            ['label' => 'Status', 'width' => '10%'],
            ['label' => 'Próxima dose', 'width' => '12%', 'align' => 'left'],
        ]"
        :modal_actions="false"
        :pagination="false"
    >
        <x-slot name="title" class="text-color">{{ $tableTitle ?? 'Agenda de Vacinações' }}</x-slot>
        <x-slot name="buttons">
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end vet-vacinacoes__filters">
                    <div class="col-md-3">
                        {!! Form::text('pesquisa', 'Pesquisar paciente ou tutor')
                            ->placeholder('Digite o nome ou contato')
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
                            'vaccine_type',
                            'Tipo de vacina',
                            ['' => 'Todas'] + collect($filters['vaccine_types'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'species',
                            'Espécie',
                            ['' => 'Todas'] + collect($filters['species'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'veterinarian',
                            'Veterinário',
                            ['' => 'Todos'] + collect($filters['veterinarians'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-1">
                        {!! Form::select(
                            'period',
                            'Período',
                            ['' => 'Qualquer'] + collect($filters['periods'])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-12 col-md-12 col-lg-12 d-flex gap-2 justify-content-start mt-2">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ $clearFilterRoute }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($vaccinationCollection as $vaccination)
            @include('components.petshop.vet.vacinacoes._table_row', [
                'vaccination' => $vaccination,
                'modalId' => 'vet-vacc-modal-' . \Illuminate\Support\Str::slug($vaccination['code'] ?? uniqid()),
                'canApply' => ($viewMode ?? null) !== 'history',
            ])
        @endforeach
    </x-table>

    @foreach ($vaccinationCollection as $vaccination)
        @include('petshop.vet.vacinacoes.partials.vaccination-modal', [
            'vaccination' => $vaccination,
            'modalId' => 'vet-vacc-modal-' . \Illuminate\Support\Str::slug($vaccination['code'] ?? uniqid()),
        ])
    @endforeach

    @include('petshop.vet.vacinacoes.partials.overview-modal', [
        'summary' => $summary,
        'statusBreakdown' => $statusBreakdown,
        'upcomingVaccinations' => $upcomingVaccinations,
    ])

    @include('petshop.vet.vacinacoes.partials.alerts-modal', [
        'alerts' => $vaccinationAlerts,
        'reminders' => $vaccinationReminders,
    ])
@endsection