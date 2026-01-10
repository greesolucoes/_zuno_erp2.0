@extends('layouts.app', ['title' => 'Internados'])

@section('css')
    <link href="/css/agenda.css" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                    <div class="d-flex flex-column" style="flex: 1">
                        <h3 class="text-gold mb-2">
                            Internados
                        </h3>
                        <p class="text-muted mb-4">
                            Visualize os pets internados com o mesmo layout da agenda diária e filtre por risco, setor ou
                            profissional responsável.
                        </p>

                        <div class="col-lg-12">
                            {!! Form::open()->fill(request()->all())->get()->id('inpatients-filter-form') !!}
                                <input type="hidden" name="status" id="status-filter-input" value="{{ $activeFilters['status'] ?? \App\Models\Petshop\Internacao::STATUS_ACTIVE }}">

                                <div class="row align-items-end g-2 new-colors">
                                    <div class="col-md-3">
                                        {!!
                                            Form::text('pesquisa', 'Buscar')
                                                ->placeholder('Pet, tutor ou leito')
                                                ->value($activeFilters['search'] ?? null)
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!
                                            Form::select('nivel_risco', 'Nível de risco')
                                                ->options(['' => 'Todos'] + collect($filters['risks'] ?? [])->pluck('label', 'value')->toArray())
                                                ->value($activeFilters['risk'] ?? null)
                                                ->attrs(['class' => 'select2 ignore'])
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!
                                            Form::select('sector', 'Setor')
                                                ->options(['' => 'Todos'] + collect($filters['sectors'] ?? [])->pluck('label', 'value')->toArray())
                                                ->value($activeFilters['sector'] ?? null)
                                                ->attrs(['class' => 'select2 ignore'])
                                        !!}
                                    </div>
                                    <div class="col-md-3">
                                        {!!
                                            Form::select('veterinario_id', 'Profissional responsável')
                                                ->options(['' => 'Todos'] + ($filters['veterinarians'] ?? []))
                                                ->value($activeFilters['veterinarian'] ?? null)
                                                ->attrs(['class' => 'select2 ignore'])
                                        !!}
                                    </div>
                                    <div class="col-md-2 text-left d-flex align-items-end gap-1 mt-3">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="ri-search-line"></i>
                                            Pesquisar
                                        </button>
                                        <a class="btn btn-danger" href="{{ route('vet.hospitalizations.inpatients') }}">
                                            <i class="ri-eraser-fill"></i>
                                            Limpar
                                        </a>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                        </div>
                    </div>

                    <div class="d-flex flex-column align-items-end gap-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <button
                                type="button"
                                class="btn btn-primary service-btn {{ ($activeFilters['status'] ?? \App\Models\Petshop\Internacao::STATUS_ACTIVE) === '__all__' ? 'selected-service-button' : '' }}"
                                data-status-value="__all__"
                            >
                                <i class="ri-grid-fill"></i>
                                Todos os registros
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary service-btn {{ ($activeFilters['status'] ?? \App\Models\Petshop\Internacao::STATUS_ACTIVE) === \App\Models\Petshop\Internacao::STATUS_ACTIVE ? 'selected-service-button' : '' }}"
                                data-status-value="{{ \App\Models\Petshop\Internacao::STATUS_ACTIVE }}"
                            >
                                <i class="ri-hotel-bed-line"></i>
                                Internados
                            </button>
                        </div>

                        <span class="badge fw-semibold px-3 py-2" style="background: rgba(83, 49, 117, 0.12); color: #533175;">
                            <i class="ri-heart-pulse-line me-1"></i>
                            Total de internados: {{ $totalInpatients }}
                        </span>

                        <div class="d-flex flex-column mt-2 g-2" id="agenda-status-select">
                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-end">
                                @foreach ($statusLegend as $status)
                                    @php
                                        $buttonClass = str_replace(['estado-', '-area'], ['btn-', ''], $status['class'] ?? '');
                                        $isActive = ($activeFilters['status'] ?? \App\Models\Petshop\Internacao::STATUS_ACTIVE) === ($status['value'] ?? null);
                                    @endphp
                                    <button
                                        type="button"
                                        class="btn-status {{ $buttonClass }} {{ $isActive ? 'selected' : '' }}"
                                        data-status-value="{{ $status['value'] ?? '' }}"
                                    >
                                        <i class="{{ $status['icon'] ?? 'ri-information-line' }} mr-3"></i>
                                        {{ $status['label'] ?? '' }} ({{ strtoupper($status['short'] ?? '') }})
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                @if (empty($groups))
                    <div class="without-agendamentos text-center">
                        <img src="/assets/images/svg/sem agendamento pet.svg" alt="Sem registros">
                        <p class="fw-semibold mb-1">Sem pacientes internados no momento...</p>
                        <span class="text-muted">Registre uma internação para acompanhar por aqui.</span>
                    </div>
                @else
                    <div class="d-flex flex-column gap-4">
                        @foreach ($groups as $group)
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex flex-wrap align-items-center gap-2 text-uppercase fw-semibold text-muted">
                                    @if ($group['date'])
                                        <span class="text-color">{{ strtoupper($group['label']) }}</span>
                                        <span>{{ $group['formatted_date'] }}</span>
                                        @if ($group['is_today'])
                                            <span class="badge bg-warning text-dark fw-semibold">Hoje</span>
                                        @endif
                                    @else
                                        <span class="text-color">{{ strtoupper($group['label']) }}</span>
                                    @endif
                                </div>

                                <div class="d-flex flex-column gap-3">
                                    @foreach ($group['patients'] as $inpatient)
                                        @include('petshop.vet.internacoes.partials.inpatient-card', ['inpatient' => $inpatient])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusInput = document.getElementById('status-filter-input');
            const filterForm = document.getElementById('inpatients-filter-form');

            if (!statusInput || !filterForm) {
                return;
            }

            const handleStatusChange = (value) => {
                const current = statusInput.value || '{{ \App\Models\Petshop\Internacao::STATUS_ACTIVE }}';

                if (current === value && value !== '{{ \App\Models\Petshop\Internacao::STATUS_ACTIVE }}') {
                    statusInput.value = '{{ \App\Models\Petshop\Internacao::STATUS_ACTIVE }}';
                } else {
                    statusInput.value = value;
                }

                filterForm.requestSubmit();
            };

            document.querySelectorAll('[data-status-value]').forEach((button) => {
                button.addEventListener('click', () => {
                    handleStatusChange(button.dataset.statusValue || '{{ \App\Models\Petshop\Internacao::STATUS_ACTIVE }}');
                });
            });
        });
    </script>
@endpush