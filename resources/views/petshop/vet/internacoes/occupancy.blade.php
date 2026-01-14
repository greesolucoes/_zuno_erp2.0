@extends('default.layout', ['title' => 'Ocupação de Leitos'])

@php
    $badgeClass = static function (?string $variant): string {
        return match ($variant) {
            'success' => 'badge bg-success',
            'warning' => 'badge bg-warning text-dark',
            'danger' => 'badge bg-danger',
            'info' => 'badge bg-info',
            'secondary' => 'badge bg-secondary',
            'light' => 'badge bg-light text-dark',
            default => 'badge bg-primary',
        };
    };
@endphp

@section('css')
    <style>
        .vet-occupancy {
            padding: 0 1rem 2rem;
        }

        .vet-occupancy .page-title {
            font-weight: 700;
            color: #533175;
        }

        .vet-occupancy .page-subtitle {
            color: #6c6a82;
            max-width: 680px;
        }

        .vet-occupancy .metric-card,
        .vet-occupancy .status-card,
        .vet-occupancy .room-card {
            border: none;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .vet-occupancy .metric-card:hover,
        .vet-occupancy .status-card:hover,
        .vet-occupancy .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.14);
        }

        .vet-occupancy .metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            font-size: 22px;
        }

        .vet-occupancy .metric-icon--primary { background: rgba(var(--bs-primary-rgb), 0.12); color: var(--bs-primary); }
        .vet-occupancy .metric-icon--success { background: rgba(var(--bs-success-rgb), 0.14); color: var(--bs-success); }
        .vet-occupancy .metric-icon--danger { background: rgba(var(--bs-danger-rgb), 0.14); color: var(--bs-danger); }
        .vet-occupancy .metric-icon--info { background: rgba(var(--bs-info-rgb), 0.14); color: var(--bs-info); }

        .vet-occupancy .room-card {
            border: 1px solid rgba(83, 49, 117, 0.08);
        }

        .vet-occupancy .room-card:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.35);
        }

        .vet-occupancy .room-header {
            border-bottom: 1px dashed rgba(83, 49, 117, 0.15);
            padding-bottom: 0.85rem;
        }

        .vet-occupancy .patient-card {
            border-radius: 16px;
            border: 1px dashed rgba(83, 49, 117, 0.26);
            background: rgba(248, 249, 252, 0.78);
            padding: 0.9rem;
        }

        .vet-occupancy .patient-card + .patient-card {
            margin-top: 0.75rem;
        }

        .vet-occupancy .sector-header {
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(83, 49, 117, 0.12), rgba(83, 49, 117, 0.05));
            padding: 1rem 1.25rem;
        }

        @media (max-width: 768px) {
            .vet-occupancy {
                padding: 0 0.25rem 2rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="card border-top border-0 border-4 border-primary">
            <div class="card-body p-4">
                <div class="container-fluid px-0 vet-occupancy">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <h2 class="page-title mb-2">Ocupação de Leitos</h2>
                            <p class="page-subtitle mb-0">
                                Visualize em tempo real onde cada paciente está internado, acompanhe a capacidade das salas e planeje os
                                próximos cuidados com agilidade.
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('vet.hospitalizations.create') }}" class="btn btn-success d-flex align-items-center gap-1">
                                <i class="bx bx-plus"></i>
                                Nova internação
                            </a>
                            <a href="{{ route('vet.salas-internacao.index') }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                                <i class="bx bx-grid-alt"></i>
                                Gerenciar salas
                            </a>
                            <a href="{{ route('vet.hospitalizations.index') }}" class="btn btn-light d-flex align-items-center gap-1">
                                <i class="bx bx-archive"></i>
                                Histórico
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="metric-card p-3 d-flex align-items-center gap-3 h-100">
                                <div class="metric-icon metric-icon--primary">
                                    <i class="bx bx-building-house"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Salas</div>
                                    <div class="h4 mb-0 fw-bold text-color">{{ $overview['total_rooms'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="metric-card p-3 d-flex align-items-center gap-3 h-100">
                                <div class="metric-icon metric-icon--success">
                                    <i class="bx bx-bed"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Leitos totais</div>
                                    <div class="h4 mb-0 fw-bold text-color">{{ $overview['total_capacity'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="metric-card p-3 d-flex align-items-center gap-3 h-100">
                                <div class="metric-icon metric-icon--danger">
                                    <i class="bx bx-user-x"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Ocupados</div>
                                    <div class="h4 mb-0 fw-bold text-color">{{ $overview['occupied_beds'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="metric-card p-3 d-flex align-items-center gap-3 h-100">
                                <div class="metric-icon metric-icon--info">
                                    <i class="bx bx-check-shield"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Livres</div>
                                    <div class="h4 mb-0 fw-bold text-color">{{ $overview['available_beds'] ?? 0 }}</div>
                                    @if (($overview['occupancy_rate'] ?? null) !== null)
                                        <div class="text-muted small">{{ $overview['occupancy_rate'] }}% de ocupação</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>


        @if (! empty($overview['critical_rooms']))
            <div class="alert alert-warning d-flex align-items-start gap-3 shadow-sm border-0 mb-4">
                <i class="bx bx-error fs-4 mt-1"></i>
                <div>
                    <h6 class="fw-semibold text-color mb-1">Salas em capacidade máxima</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($overview['critical_rooms'] as $critical)
                            <span class="badge rounded-pill bg-warning text-dark fw-semibold">
                                {{ $critical['name'] ?? 'Sala' }}
                                @if (! empty($critical['identifier']))
                                    • {{ $critical['identifier'] }}
                                @endif
                                — {{ $critical['occupied'] }}/{{ $critical['capacity'] }} leitos
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if (empty($sectors))
            <div class="card room-card">
                <div class="card-body text-center py-5">
                    <h5 class="fw-semibold text-color">Nenhuma sala cadastrada ainda</h5>
                    <p class="text-muted mb-0">Cadastre salas de internação para visualizar a ocupação dos leitos.</p>
                </div>
            </div>
        @else
            <div class="d-flex flex-column gap-4">
                @foreach ($sectors as $sector)
                    <div class="card border-0 shadow-sm">
                        <div class="sector-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="fw-semibold text-color mb-1">{{ $sector['label'] }}</h5>
                                <span class="text-muted small">
                                    {{ count($sector['rooms']) }} {{ \Illuminate\Support\Str::plural('sala', count($sector['rooms'])) }} mapeada(s)
                                </span>
                            </div>
                            <span class="badge bg-light text-color border">{{ $sector['type'] ?: 'Tipo não informado' }}</span>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                @forelse ($sector['rooms'] as $room)
                                    @php
                                        $percentage = $room['occupancy_percentage'] ?? 0;
                                        $progressVariant = 'success';
                                        if ($percentage >= 90) {
                                            $progressVariant = 'danger';
                                        } elseif ($percentage >= 70) {
                                            $progressVariant = 'warning';
                                        } elseif ($percentage >= 40) {
                                            $progressVariant = 'info';
                                        }
                                    @endphp
                                    <div class="col-xxl-4 col-xl-6">
                                        <div class="room-card h-100 p-3 d-flex flex-column">
                                            <div class="room-header d-flex flex-wrap align-items-start justify-content-between gap-2">
                                                <div>
                                                    <h5 class="fw-semibold text-color mb-0">
                                                        {{ $room['name'] ?? 'Sala sem identificação' }}
                                                    </h5>
                                                    <span class="text-muted small">
                                                        {{ $room['identifier'] ?: 'Identificador não informado' }}
                                                    </span>
                                                </div>
                                                <span class="{{ $badgeClass($room['status']['color'] ?? 'secondary') }}">
                                                    {{ $room['status']['label'] }}
                                                </span>
                                            </div>

                                            <div class="mt-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="fw-semibold text-color small">
                                                        {{ $room['occupied'] }} / {{ $room['capacity'] ?: '—' }} leitos ocupados
                                                    </span>
                                                    @if ($room['occupancy_percentage'] !== null)
                                                        <span class="text-muted small">{{ $room['occupancy_percentage'] }}%</span>
                                                    @endif
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div
                                                        class="progress-bar bg-{{ $progressVariant }}"
                                                        role="progressbar"
                                                        style="width: {{ $room['occupancy_percentage'] ?? 0 }}%;"
                                                        aria-valuenow="{{ $room['occupancy_percentage'] ?? 0 }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100"
                                                    ></div>
                                                </div>
                                                @if ($room['available'] > 0)
                                                    <span class="badge rounded-pill bg-success mt-3">{{ $room['available'] }} leito(s) livre(s)</span>
                                                @else
                                                    <span class="badge rounded-pill bg-danger mt-3">Capacidade total ocupada</span>
                                                @endif
                                            </div>

                                            @if (! empty($room['equipments']))
                                                <div class="mt-3">
                                                    <span class="text-muted text-uppercase small fw-semibold">Recursos disponíveis</span>
                                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                                        @foreach ($room['equipments'] as $equipment)
                                                            <span class="badge rounded-pill bg-light text-color border">{{ $equipment }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if (! empty($room['notes']))
                                                <div class="mt-3">
                                                    <span class="text-muted text-uppercase small fw-semibold">Observações</span>
                                                    <p class="text-color small mb-0">{{ $room['notes'] }}</p>
                                                </div>
                                            @endif

                                            <div class="mt-4">
                                                <span class="text-muted text-uppercase small fw-semibold">Pacientes internados</span>
                                                @if (empty($room['patients']))
                                                    <div class="alert alert-light border mt-2 mb-0 py-2 text-muted small">
                                                        Nenhum paciente internado neste momento.
                                                    </div>
                                                @else
                                                    <div class="mt-2">
                                                        @foreach ($room['patients'] as $patient)
                                                            <div class="patient-card">
                                                                <div class="d-flex align-items-start gap-3">
                                                                    <img
                                                                        src="{{ $patient['avatar'] }}"
                                                                        alt="{{ $patient['name'] }}"
                                                                        class="rounded-circle"
                                                                        style="object-fit: cover;"
                                                                        width="48"
                                                                        height="48"
                                                                    >
                                                                    <div class="flex-grow-1">
                                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                                    <div>
                                                                        <h6 class="mb-0 fw-semibold text-color">{{ $patient['name'] }}</h6>
                                                                                <div class="text-muted small">
                                                                                    {{ $patient['species'] ?? 'Espécie não informada' }}
                                                                                    @if (! empty($patient['breed']))
                                                                                        • {{ $patient['breed'] }}
                                                                                    @endif
                                                                                    @if (! empty($patient['age']))
                                                                                        • {{ $patient['age'] }}
                                                                                    @endif
                                                                                    @if (! empty($patient['weight']))
                                                                                        • {{ $patient['weight'] }}
                                                                                    @endif
                                                                                </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                                                                <span class="{{ $badgeClass($patient['status']['color'] ?? 'primary') }}">{{ $patient['status']['label'] }}</span>
                                                                                <span class="{{ $badgeClass($patient['risk']['color'] ?? 'secondary') }}">{{ $patient['risk']['label'] }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex flex-wrap align-items-center gap-3 mt-2 text-muted small">
                                                                            <span><i class="bx bx-user me-1"></i>{{ $patient['tutor']['name'] ?? 'Tutor não informado' }}</span>
                                                                            @if (! empty($patient['tutor']['contact']))
                                                                                <span><i class="bx bx-phone me-1"></i>{{ $patient['tutor']['contact'] }}</span>
                                                                            @elseif (! empty($patient['tutor']['phones'][0]))
                                                                                <span><i class="bx bx-phone me-1"></i>{{ $patient['tutor']['phones'][0] }}</span>
                                                                            @endif
                                                                            @if (! empty($patient['veterinarian']))
                                                                                <span><i class="bx bx-plus-medical me-1"></i>{{ $patient['veterinarian'] }}</span>
                                                                            @endif
                                                                        </div>
                                                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                                                            @if (! empty($patient['admitted_at']))
                                                                                <span class="badge bg-light text-color border">Admitido em {{ $patient['admitted_at'] }}</span>
                                                                            @endif
                                                                            @if (! empty($patient['expected_discharge_at']))
                                                                                <span class="badge bg-light text-color border">Alta prevista {{ $patient['expected_discharge_at'] }}</span>
                                                                            @endif
                                                                        </div>
                                                                        @if (! empty($patient['reason']))
                                                                            <div class="text-color small mt-2">
                                                                                <strong>Motivo:</strong>
                                                                                {{ \Illuminate\Support\Str::limit($patient['reason'], 160) }}
                                                                            </div>
                                                                        @endif
                                                                        @if (! empty($patient['notes']))
                                                                            <div class="text-muted small mt-1">
                                                                                <strong>Notas:</strong>
                                                                                {{ \Illuminate\Support\Str::limit($patient['notes'], 160) }}
                                                                            </div>
                                                                        @endif
                                                                        @if (! empty($patient['urls']['status']) || ! empty($patient['urls']['edit']))
                                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                                @if (! empty($patient['urls']['status']))
                                                                                    <a href="{{ $patient['urls']['status'] }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                                                                                        <i class="bx bx-pulse"></i>
                                                                                        Evolução
                                                                                    </a>
                                                                                @endif
                                                                                @if (! empty($patient['urls']['edit']))
                                                                                    <a href="{{ $patient['urls']['edit'] }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                                                                        <i class="bx bx-edit"></i>
                                                                                        Editar
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-light border text-center mb-0">
                                            Nenhuma sala cadastrada para este setor.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
                </div>
            </div>
        </div>
    </div>
@endsection
