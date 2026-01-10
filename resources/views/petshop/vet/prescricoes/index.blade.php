@extends('default.layout', ['title' => 'Prescrições Veterinárias'])

@section('content')
    <x-table
        :data="collect($prescriptions)"
        :table_headers="[
            ['label' => 'Paciente', 'width' => '18%', 'align' => 'center'],
            ['label' => 'Tutor', 'width' => '15%', 'align' => 'center'],
            ['label' => 'Status', 'width' => '12%', 'align' => 'center'],
            ['label' => 'Prioridade', 'width' => '12%', 'align' => 'center'],
            ['label' => 'Revalidação', 'width' => '13%'],
            ['label' => 'Resumo clínico', 'width' => '22%', 'align' => 'center'],
        ]"
        :modal_actions="false"
        :pagination="false"
    >
        <x-slot name="title" class="text-color">Historico de Prescrições</x-slot>
        <x-slot name="buttons">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">             
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        {!! Form::text('search', 'Pesquisar prescrição')->placeholder('Paciente, tutor ou código')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'status',
                            'Status',
                            ['' => 'Todos'] + collect(data_get($filters, 'status', []))->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'type',
                            'Tipo terapêutico',
                            ['' => 'Todos'] + collect(data_get($filters, 'types', []))->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'priority',
                            'Prioridade',
                            ['' => 'Todas'] + collect(data_get($filters, 'priorities', []))->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select(
                            'veterinarian',
                            'Veterinário responsável',
                            ['' => 'Todos'] + collect(data_get($filters, 'veterinarians', []))->pluck('label', 'value')->toArray()
                        )->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-1 mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.prescriptions.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($prescriptions as $index => $prescription)
            @php($modalId = 'vet-prescription-modal-' . $index . '-' . \Illuminate\Support\Str::slug($prescription['code'] ?? 'prescricao'))
            @include('components.petshop.vet.prescricoes._table_row', [
                'prescription' => $prescription,
                'modalId' => $modalId,
            ])
        @endforeach
    </x-table>

    @foreach ($prescriptions as $index => $prescription)
        @php($modalId = 'vet-prescription-modal-' . $index . '-' . \Illuminate\Support\Str::slug($prescription['code'] ?? 'prescricao'))
        @include('petshop.vet.prescricoes.partials.prescription-modal', [
            'prescription' => $prescription,
            'modalId' => $modalId,
        ])
    @endforeach

    @include('petshop.vet.prescricoes.partials.overview-modal', [
        'summary' => $summary,
        'adherenceIndicators' => $adherenceIndicators,
        'upcomingRenewals' => $upcomingRenewals,
        'supplyLevels' => $supplyLevels,
    ])

    @include('petshop.vet.prescricoes.partials.alerts-modal', [
        'alerts' => $globalAlerts,
    ])
@endsection