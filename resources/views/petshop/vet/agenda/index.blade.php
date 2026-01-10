@extends('default.layout', ['title' => 'Agenda Médica Veterinária'])

@section('css')
    <link rel="stylesheet" href="/assets/vendor/fullcalendar/main.min.css">
    <style>
        .vet-agenda__page {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .vet-agenda__page--fullscreen {
            gap: 1.5rem;
        }

        .vet-agenda__page--fullscreen .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
        }

        .vet-agenda__header {
            border-radius: 18px;
            padding: 1.75rem;
            background: linear-gradient(135deg, rgba(85, 110, 230, 0.12) 0%, rgba(43, 122, 255, 0.12) 100%);
            border: 1px solid rgba(85, 110, 230, 0.08);
        }

        .vet-agenda__header h2 {
            font-size: clamp(1.5rem, 1.8vw, 2rem);
        }

        .vet-agenda__header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .vet-agenda__header-actions .btn {
            border-radius: 999px;
            padding-inline: 1.25rem;
        }

        .vet-agenda__page--fullscreen .vet-agenda__header {
            background: linear-gradient(135deg, rgba(85, 110, 230, 0.18) 0%, rgba(43, 122, 255, 0.24) 100%);
            border-color: rgba(85, 110, 230, 0.24);
        }

        .vet-calendar__frame {
            flex: 1 1 auto;
            max-height: 560px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 0.35rem;
            margin-right: -0.35rem;
        }

        #vet-calendar {
            min-height: 500px;
        }

        .vet-agenda__page--fullscreen .vet-calendar__frame {
            max-height: none;
            overflow: visible;
            padding-right: 0;
            margin-right: 0;
        }

        .vet-agenda__page--fullscreen #vet-calendar {
            min-height: calc(100vh - 280px);
        }

        .vet-agenda__summary-card {
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 6px 16px rgba(22, 22, 107, 0.06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .vet-agenda__summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(22, 22, 107, 0.12);
        }

        .vet-agenda__summary-value {
            font-size: clamp(1.5rem, 1.8vw, 2rem);
            font-weight: 700;
        }

        .vet-agenda__filters .form-label {
            font-weight: 600;
            color: #45454d;
        }

        .vet-agenda__filters .form-control,
        .vet-agenda__filters .form-select {
            border-radius: 10px;
        }

        .vet-agenda__empty-state {
            border: 1px dashed rgba(22, 22, 107, 0.35);
            border-radius: 16px;
            padding: 2.5rem 1.5rem;
            background: rgba(22, 22, 107, 0.02);
        }

        .vet-agenda__page--fullscreen .vet-agenda__empty-state {
            background: rgba(85, 110, 230, 0.12);
            border-color: rgba(85, 110, 230, 0.35);
            color: #1f2633;
        }

        .vet-agenda__appointment-card {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            padding: 1rem 1.25rem;
            background: #fff;
            box-shadow: 0 10px 30px rgba(22, 22, 107, 0.08);
        }

        .vet-agenda__timeline-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .vet-agenda__selected-card {
            border-radius: 16px;
            border: 1px solid rgba(85, 110, 230, 0.18);
            background: rgba(85, 110, 230, 0.06);
            padding: 1.25rem 1.5rem;
        }

        .vet-calendar__toolbar {
            border-bottom: 1px solid rgba(22, 22, 107, 0.08);
        }

        .vet-agenda__page--fullscreen .vet-calendar__toolbar {
            border: none;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 10px 28px rgba(22, 22, 107, 0.15);
        }

        .vet-calendar__nav-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .vet-calendar__nav-buttons .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding-inline: 0.9rem;
            border-radius: 999px;
        }

        .vet-calendar__legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1.25rem;
        }

        .vet-calendar__legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: inline-block;
        }

        .vet-calendar__view-button {
            border-radius: 999px !important;
            border: 1px solid rgba(85, 110, 230, 0.3);
            color: #556ee6;
            background: #fff;
            transition: all .2s ease;
        }

        .vet-calendar__view-button.active,
        .vet-calendar__view-button:hover {
            background: #556ee6;
            color: #fff;
        }

        .vet-calendar__event {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            padding: 0 !important;
            background: #fff;
            box-shadow: 0 12px 26px rgba(22, 22, 107, 0.12);
            overflow: hidden;
        }

        .vet-calendar-event__body {
            padding: 0.5rem 0.75rem 0.6rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .vet-calendar-event__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .vet-calendar-event__dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .vet-calendar-event__title {
            font-weight: 600;
            color: #1f2633;
        }

        .vet-calendar-event__meta {
            color: #5f6472;
            font-size: 0.775rem;
        }

        .fc .fc-scrollgrid,
        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: rgba(22, 22, 107, 0.08);
        }

        .fc .fc-day-today {
            background: rgba(85, 110, 230, 0.12) !important;
        }

        .fc .fc-timegrid-slot {
            height: 60px;
        }

        .fc .fc-list-event-dot {
            border-color: currentColor;
        }

        .fc .fc-toolbar.fc-header-toolbar {
            display: none;
        }

        @media (max-width: 991px) {
            .vet-agenda__header {
                padding: 1.5rem;
            }

            .vet-calendar__frame {
                max-height: none;
                padding-right: 0;
                margin-right: 0;
            }

            #vet-calendar {
                min-height: 460px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $isFullscreen = request()->boolean('fullscreen');
        $query = request()->query();
        $fullscreenQuery = array_merge($query, ['fullscreen' => 1]);
        $exitFullscreenQuery = $query;
        unset($exitFullscreenQuery['fullscreen']);
        $fullscreenUrl = url()->current() . ($fullscreenQuery ? '?' . http_build_query($fullscreenQuery) : '');
        $exitFullscreenUrl = url()->current() . ($exitFullscreenQuery ? '?' . http_build_query($exitFullscreenQuery) : '');
    @endphp

    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto"></div>
                </div>

                <div class="col">
                    <h6 class="mb-0 text-uppercase">Agenda</h6>
                    <hr/>

                    <div class="vet-agenda__page {{ $isFullscreen ? 'vet-agenda__page--fullscreen' : '' }}">
        <div class="row g-4">
            <div class="col-12">
                <div class="vet-agenda__header d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                    <div>
                        <h2 class="text-color fw-bold mb-1">Agenda Médica</h2>
                        <p class="text-muted mb-0">Visualize consultas, procedimentos e organize a rotina da equipe veterinária.</p>
                    </div>
                    <div class="vet-agenda__header-actions">
                        <button type="button" class="btn btn-outline-primary" id="vet-agenda-today">
                            <i class="ri-calendar-line me-1"></i>
                            Hoje
                        </button>
                        @if ($isFullscreen)
                            <a href="{{ $exitFullscreenUrl }}" class="btn btn-light">
                                <i class="ri-arrow-go-back-line me-1"></i>
                                Visão padrão
                            </a>
                        @else
                            <a href="{{ $fullscreenUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                                <i class="ri-fullscreen-line me-1"></i>
                                Abrir em tela cheia
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @unless ($isFullscreen)
                <div class="col-12">
                    <div class="row g-3">
                        @forelse ($statusSummary as $status)
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="vet-agenda__summary-card p-3 h-100 bg-{{ $status['color'] }}-subtle text-{{ $status['color'] }}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-semibold">{{ $status['label'] }}</span>
                                        <span class="badge rounded-pill bg-{{ $status['color'] }} text-white">Dia</span>
                                    </div>
                                    <div class="vet-agenda__summary-value mt-3">{{ $status['value'] }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="vet-agenda__empty-state text-center">
                                    <i class="ri-calendar-2-line display-6 text-primary"></i>
                                    <p class="mt-3 mb-0 fw-semibold text-muted">Ainda não há dados de resumo para exibir.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endunless

            @unless ($isFullscreen)
                <div class="col-12 col-lg-4 col-xxl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body vet-agenda__filters">
                            <h5 class="text-color fw-semibold mb-3">Filtros</h5>
                            <form id="vet-agenda-filter" class="row g-3" method="GET" action="{{ route('vet.agenda.index') }}">
                                <div class="col-12">
                                    <label for="filter-veterinarian" class="form-label">Veterinário</label>
                                    <select id="filter-veterinarian" name="veterinarian" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach ($filters['veterinarians'] as $veterinarian)
                                            <option value="{{ $veterinarian['id'] ?? $veterinarian['value'] ?? '' }}"
                                                @selected(request('veterinarian') == ($veterinarian['id'] ?? $veterinarian['value'] ?? ''))>
                                                {{ $veterinarian['name'] ?? $veterinarian['label'] ?? 'Veterinário' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="filter-service" class="form-label">Serviço</label>
                                    <select id="filter-service" name="service" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach ($filters['services'] as $service)
                                            <option value="{{ $service['id'] ?? $service['value'] ?? '' }}"
                                                @selected(request('service') == ($service['id'] ?? $service['value'] ?? ''))>
                                                {{ $service['name'] ?? $service['label'] ?? 'Serviço' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="filter-location" class="form-label">Sala / Local</label>
                                    <select id="filter-location" name="location" class="form-select">
                                        <option value="">Todas</option>
                                        @foreach ($filters['locations'] as $location)
                                            <option value="{{ $location['id'] ?? $location['value'] ?? '' }}"
                                                @selected(request('location') == ($location['id'] ?? $location['value'] ?? ''))>
                                                {{ $location['name'] ?? $location['label'] ?? 'Local' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="filter-start" class="form-label">Início</label>
                                    <input type="date" id="filter-start" name="start_date" class="form-control"
                                        value="{{ request('start_date') }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="filter-end" class="form-label">Fim</label>
                                    <input type="date" id="filter-end" name="end_date" class="form-control"
                                        value="{{ request('end_date') }}">
                                </div>
                                <div class="col-12 d-flex gap-2 mt-1">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="ri-search-2-line me-1"></i>
                                        Aplicar
                                    </button>
                                    <a href="{{ route('vet.agenda.index') }}" class="btn btn-light flex-fill">
                                        <i class="ri-eraser-line me-1"></i>
                                        Limpar
                                    </a>
                                </div>
                            </form>

                            <hr class="my-4">

                            <h6 class="text-muted text-uppercase fw-semibold small">Atalhos rápidos</h6>
                            <div class="d-grid gap-2 mt-3">
                                <button class="btn btn-outline-secondary" type="button" data-agenda-action="week">
                                    <i class="ri-calendar-week-line me-1"></i>
                                    Próxima semana
                                </button>
                                <button class="btn btn-outline-secondary" type="button" data-agenda-action="month">
                                    <i class="ri-calendar-2-line me-1"></i>
                                    Próximo mês
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endunless

        <div class="col-12 {{ $isFullscreen ? '' : 'col-lg-8 col-xxl-6' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="vet-calendar__toolbar d-flex flex-column flex-xxl-row gap-3 gap-xxl-0 align-items-xxl-center justify-content-between pb-3 mb-3">
                        <div>
                            <h5 class="text-color fw-semibold mb-1" id="vet-calendar-range">Agenda dinâmica</h5>
                            <p class="text-muted small mb-0" id="vet-calendar-summary">Carregando eventos da agenda...</p>
                        </div>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <div class="vet-calendar__nav-buttons" role="group" aria-label="Navegação da agenda">
                                <button type="button" class="btn btn-outline-secondary" data-calendar-nav="prev"
                                    aria-label="Anterior" title="Anterior">
                                    <i class="ri-arrow-left-s-line"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-calendar-nav="next"
                                    aria-label="Próximo" title="Próximo">
                                    <i class="ri-arrow-right-s-line"></i>
                                </button>
                            </div>
                            <div class="btn-group" role="group" aria-label="Visões da agenda">
                                <button type="button" class="btn vet-calendar__view-button active" data-calendar-view="timeGridWeek">Semana</button>
                                <button type="button" class="btn vet-calendar__view-button" data-calendar-view="dayGridMonth">Mês</button>
                                <button type="button" class="btn vet-calendar__view-button" data-calendar-view="timeGridDay">Dia</button>
                                <button type="button" class="btn vet-calendar__view-button" data-calendar-view="listWeek">Lista</button>
                            </div>
                        </div>
                    </div>

                    @if (!empty($statusLegend))
                        <div class="vet-calendar__legend mb-3">
                            @foreach ($statusLegend as $legend)
                                <span class="d-flex align-items-center gap-2 text-muted small">
                                    <span class="vet-calendar__legend-dot" style="background: {{ $legend['color'] }}"></span>
                                    {{ $legend['label'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @php
                        $calendarEventsJson = json_encode($calendarEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                        $calendarEventsJson = $calendarEventsJson !== false ? $calendarEventsJson : '[]';
                    @endphp

                    <div id="vet-calendar-empty"
                        class="vet-agenda__empty-state text-center {{ empty($calendarEvents) ? '' : 'd-none' }}">
                        <i class="ri-calendar-event-line display-6 text-primary"></i>
                        <p class="mt-3 mb-0 fw-semibold text-muted">Nenhum atendimento programado para o período filtrado.</p>
                        <p class="text-muted small mb-0">Utilize os filtros ao lado para buscar outras datas ou serviços.</p>
                    </div>

                    <div class="vet-calendar__frame mt-3">
                        <div id="vet-calendar" data-events='{{ $calendarEventsJson }}'></div>
                    </div>
                </div>
            </div>
        </div>

        @unless ($isFullscreen)
            <div class="col-12 col-xxl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="text-color fw-semibold mb-0">Próximos atendimentos</h5>
                            <span class="badge bg-primary-subtle text-primary">Agenda</span>
                        </div>

                        <div id="vet-appointments-highlight" class="vet-agenda__selected-card mb-4 d-none"></div>

                        <div id="vet-appointments-list" class="d-flex flex-column gap-3">
                            @forelse ($upcomingAppointments as $appointment)
                                <div class="vet-agenda__appointment-card">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="vet-agenda__timeline-dot bg-primary"></span>
                                            <span class="fw-semibold text-color">
                                                {{ $appointment['time_range'] ?? $appointment['time'] ?? '--:--' }}
                                            </span>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary">{{ $appointment['status'] ?? 'Agendado' }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <p class="mb-1 fw-semibold">{{ $appointment['patient'] ?? 'Paciente não informado' }}</p>
                                        <p class="mb-0 text-muted small">{{ $appointment['service'] ?? 'Serviço a definir' }}</p>
                                        @if (!empty($appointment['day_label']))
                                            <p class="mb-0 text-muted small">
                                                <i class="ri-calendar-event-line me-1"></i>
                                                {{ $appointment['day_label'] }}
                                            </p>
                                        @endif
                                        <p class="mb-0 text-muted small">
                                            <i class="ri-stethoscope-line me-1"></i>
                                            {{ $appointment['veterinarian'] ?? 'Equipe Veterinária' }}
                                        </p>
                                        @if (!empty($appointment['room']))
                                            <p class="mb-0 text-muted small">
                                                <i class="ri-map-pin-line me-1"></i>
                                                {{ $appointment['room'] }}
                                            </p>
                                        @endif
                                        @if (!empty($appointment['tutor']))
                                            <p class="mb-0 text-muted small">
                                                <i class="ri-user-heart-line me-1"></i>
                                                {{ $appointment['tutor'] }}
                                                @if (!empty($appointment['tutor_contact']))
                                                    <span class="ms-1">• {{ $appointment['tutor_contact'] }}</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="vet-agenda__empty-state text-center">
                                    <i class="ri-emotion-laugh-line display-6 text-primary"></i>
                                    <p class="mt-3 mb-0 fw-semibold text-muted">Nenhum atendimento programado para os filtros atuais.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endunless
    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="/assets/vendor/fullcalendar/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/pt-br.js"></script>
    <script src="/js/vet/agenda.js"></script>
@endsection
