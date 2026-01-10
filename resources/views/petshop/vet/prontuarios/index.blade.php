@extends('default.layout', ['title' => 'Consultas Veterinárias'])

@section('css')
    <style>
        .vet-prontuarios__summary-card {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 12px 28px rgba(22, 22, 107, 0.08);
        }

        .vet-prontuarios__summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        .vet-prontuarios__summary-value {
            font-size: clamp(1.8rem, 2vw, 2.2rem);
            font-weight: 700;
            color: #221f4f;
        }

        .vet-prontuarios__badge-soft-primary,
        .vet-prontuarios__badge-soft-success,
        .vet-prontuarios__badge-soft-warning,
        .vet-prontuarios__badge-soft-danger,
        .vet-prontuarios__badge-soft-info {
            border-radius: 999px;
            padding: .25rem .75rem;
            font-weight: 600;
            font-size: .75rem;
        }

        .vet-prontuarios__badge-soft-primary {
            background: rgba(76, 63, 179, 0.15);
            color: #4c3fb3;
        }

        .vet-prontuarios__badge-soft-success {
            background: rgba(25, 135, 84, 0.15);
            color: #157347;
        }

        .vet-prontuarios__badge-soft-warning {
            background: rgba(255, 193, 7, 0.18);
            color: #b8860b;
        }

        .vet-prontuarios__badge-soft-danger {
            background: rgba(220, 53, 69, 0.18);
            color: #b02a37;
        }

        .vet-prontuarios__badge-soft-info {
            background: rgba(91, 115, 232, 0.18);
            color: #5b73e8;
        }

        .vet-prontuarios__badge-outline-primary,
        .vet-prontuarios__badge-outline-success,
        .vet-prontuarios__badge-outline-warning,
        .vet-prontuarios__badge-outline-danger,
        .vet-prontuarios__badge-outline-info {
            border-radius: 999px;
            border: 1px solid transparent;
            padding: .2rem .75rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .vet-prontuarios__badge-outline-primary {
            color: #4c3fb3;
            border-color: rgba(76, 63, 179, 0.45);
            background: rgba(76, 63, 179, 0.08);
        }

        .vet-prontuarios__badge-outline-success {
            color: #157347;
            border-color: rgba(21, 115, 71, 0.45);
            background: rgba(21, 115, 71, 0.08);
        }

        .vet-prontuarios__badge-outline-warning {
            color: #b8860b;
            border-color: rgba(184, 134, 11, 0.45);
            background: rgba(184, 134, 11, 0.08);
        }

        .vet-prontuarios__badge-outline-danger {
            color: #b02a37;
            border-color: rgba(176, 42, 55, 0.45);
            background: rgba(176, 42, 55, 0.08);
        }

        .vet-prontuarios__badge-outline-info {
            color: #5b73e8;
            border-color: rgba(91, 115, 232, 0.45);
            background: rgba(91, 115, 232, 0.08);
        }

        .vet-prontuarios__tag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(22, 22, 107, 0.06);
            color: #16166b;
            font-size: .7rem;
            padding: .3rem .65rem;
            font-weight: 600;
        }

        .vet-prontuarios__metric-card {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            padding: 1rem;
            background: #fff;
            box-shadow: 0 10px 24px rgba(22, 22, 107, 0.06);
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .vet-prontuarios__metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(76, 63, 179, 0.12);
            color: #4c3fb3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .vet-prontuarios__vitals-card {
            border-radius: 14px;
            border: 1px dashed rgba(22, 22, 107, 0.2);
            padding: 1rem;
            background: rgba(102, 95, 199, 0.05);
        }

        .vet-prontuarios__timeline {
            position: relative;
            padding-left: .5rem;
        }

        .vet-prontuarios__timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 14px;
            width: 2px;
            background: rgba(102, 95, 199, 0.2);
        }

        .vet-prontuarios__timeline-item {
            position: relative;
            padding-left: 3rem;
        }

        .vet-prontuarios__timeline-item::before {
            content: '';
            position: absolute;
            left: 8px;
            top: .35rem;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #665fc7;
        }

        .vet-prontuarios__timeline-time {
            font-size: .75rem;
            font-weight: 600;
            color: #665fc7;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .vet-prontuarios__alert-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .vet-prontuarios__note-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(102, 95, 199, 0.12);
            color: #4c3fb3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
    </style>
@endsection

@section('content')
    <x-table
        :data="collect($records)"
        :table_headers="[
            ['label' => 'Paciente', 'width' => '10%', 'align' => 'center'],
            ['label' => 'Tutor', 'width' => '10%', 'align' => 'center'],
            ['label' => 'Responsável', 'width' => '12%'],
            ['label' => 'Status', 'width' => '12%'],
            ['label' => 'Tipo', 'width' => '12%'],
            ['label' => 'Atualização', 'width' => '12%', 'align' => 'left'],
        ]"
        :modal_actions="false"
        :pagination="false"
    >
        <x-slot name="title" class="text-color">Histórico de Consultas</x-slot>
        <x-slot name="buttons">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        {!! Form::text('search', 'Pesquisar consultas')
                            ->placeholder('Paciente, tutor ou código')
                            ->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'status',
                            'Status',
                            ['' => 'Todos'] + collect($filters['status'] ?? [])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'type',
                            'Tipo de atendimento',
                            ['' => 'Todos'] + collect($filters['types'] ?? [])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'veterinarian',
                            'Responsável',
                            ['' => 'Todos'] + collect($filters['veterinarians'] ?? [])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'timeframe',
                            'Período',
                            ['' => 'Recentes'] + collect($filters['timeframes'] ?? [])->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-1 mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.records.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($records as $index => $record)
            @php
                $modalId = 'vet-record-modal-' . $index . '-' . \Illuminate\Support\Str::slug($record['code'] ?? 'prontuario');
            @endphp
            @include('components.petshop.vet.prontuarios._table_row', [
                'record' => $record,
                'modalId' => $modalId,
            ])
        @endforeach
    </x-table>

    @foreach ($records as $index => $record)
        @php
            $modalId = 'vet-record-modal-' . $index . '-' . \Illuminate\Support\Str::slug($record['code'] ?? 'prontuario');
        @endphp
        @include('petshop.vet.prontuarios.partials.record-modal', [
            'record' => $record,
            'modalId' => $modalId,
        ])
    @endforeach

    @include('petshop.vet.prontuarios.partials.overview-modal', [
        'summary' => $summary,
        'recentNotes' => $recentNotes,
    ])

    @include('petshop.vet.prontuarios.partials.alerts-modal', [
        'clinicalAlerts' => $clinicalAlerts,
        'recentNotes' => $recentNotes,
    ])
@endsection