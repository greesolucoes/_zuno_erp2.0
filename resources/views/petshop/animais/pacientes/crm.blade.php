@extends('layouts.app', ['title' => 'CRM do Pet'])

@section('css')
    @parent
    <style>
        .pet-crm-header {
            background: linear-gradient(135deg, #4c3fb3 0%, #7566e3 100%);
            border: none;
            border-radius: 22px;
            color: #fff;
        }

        .pet-crm-header__year-selector .btn {
            border-radius: 999px;
            padding-inline: 1rem;
            font-weight: 600;
            letter-spacing: .02em;
        }

        .pet-crm-header__meta span + span {
            margin-left: .65rem;
        }

        .pet-crm-patient-card {
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 18px 48px rgba(32, 25, 110, 0.08);
        }

        .pet-crm-patient-card__title {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #837ddc;
            font-weight: 700;
        }

        .pet-crm-patient-card dl {
            margin-bottom: 0;
        }

        .pet-crm-patient-card dt {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #8b87b6;
            font-weight: 700;
            margin-bottom: .1rem;
        }

        .pet-crm-patient-card dd {
            margin-bottom: 1rem;
            color: #2f2c4a;
            font-weight: 600;
        }

        .pet-crm-metric-card {
            background: rgba(76, 63, 179, 0.07);
            border-radius: 16px;
            padding: 1.1rem 1.25rem;
            height: 100%;
        }

        .pet-crm-metric-card__label {
            font-size: .7rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #7168c7;
            font-weight: 700;
        }

        .pet-crm-metric-card__value {
            font-size: 1.85rem;
            font-weight: 700;
            color: #352f6b;
            margin: 0;
        }

        .pet-crm-month-title {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .12em;
        }

        .vet-encounter-timeline {
            position: relative;
            margin-top: 1.5rem;
            padding-left: 1rem;
        }

        .vet-encounter-timeline::before {
            content: '';
            position: absolute;
            top: .75rem;
            bottom: .75rem;
            left: 7.25rem;
            width: 3px;
            background: linear-gradient(180deg, rgba(76, 63, 179, 0.35) 0%, rgba(76, 63, 179, 0.08) 100%);
            border-radius: 999px;
        }

        .vet-encounter-timeline__item {
            position: relative;
            display: flex;
            gap: 2rem;
            padding-bottom: 2.5rem;
        }

        .vet-encounter-timeline__item:last-child {
            padding-bottom: 0;
        }

        .vet-encounter-timeline__marker {
            flex: 0 0 7.25rem;
            position: relative;
            text-align: right;
            padding-right: 2.25rem;
            padding-top: .4rem;
        }

        .vet-encounter-timeline__moment {
            display: flex;
            flex-direction: column;
            gap: .15rem;
        }

        .vet-encounter-timeline__date {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #4c3fb3;
        }

        .vet-encounter-timeline__hour {
            font-size: .85rem;
            font-weight: 600;
            color: #807aa6;
        }

        .vet-encounter-timeline__icon {
            position: absolute;
            top: .15rem;
            right: -.95rem;
            width: 3rem;
            height: 3rem;
            border-radius: 1.05rem;
            background: #fff;
            border: 2px solid rgba(76, 63, 179, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4c3fb3;
            font-size: 1.25rem;
            box-shadow: 0 12px 30px rgba(76, 63, 179, 0.12);
        }

        .vet-encounter-timeline__card {
            flex: 1;
            background: linear-gradient(135deg, #ffffff 0%, rgba(76, 63, 179, 0.05) 100%);
            border-radius: 1.25rem;
            border: 1px solid rgba(76, 63, 179, 0.12);
            box-shadow: 0 18px 48px rgba(32, 25, 110, 0.08);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .vet-encounter-timeline__card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .vet-encounter-timeline__title {
            margin-bottom: .1rem;
        }

        .vet-encounter-timeline__description {
            margin-bottom: 0;
            line-height: 1.55;
        }

        .vet-encounter-timeline__details {
            margin-bottom: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: .85rem 1.25rem;
        }

        .vet-encounter-timeline__detail dt {
            margin-bottom: .15rem;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #837ddc;
            font-weight: 700;
        }

        .vet-encounter-timeline__detail dd {
            margin: 0;
            font-size: .85rem;
            font-weight: 600;
            color: #2f2c4a;
        }

        .pet-crm-empty {
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(76, 63, 179, 0.06) 0%, rgba(76, 63, 179, 0.02) 100%);
        }

        @media (max-width: 1199.98px) {
            .vet-encounter-timeline::before {
                left: 6.75rem;
            }

            .vet-encounter-timeline__marker {
                flex-basis: 6.75rem;
                padding-right: 2rem;
            }
        }

        @media (max-width: 991.98px) {
            .pet-crm-header {
                border-radius: 18px;
            }

            .pet-crm-metric-card__value {
                font-size: 1.6rem;
            }

            .vet-encounter-timeline::before {
                left: 5.75rem;
            }

            .vet-encounter-timeline__marker {
                flex-basis: 5.75rem;
                padding-right: 1.75rem;
            }
        }

        @media (max-width: 767.98px) {
            .pet-crm-header__year-selector {
                width: 100%;
            }

            .vet-encounter-timeline {
                padding-left: 0;
            }

            .vet-encounter-timeline::before {
                left: 1.6rem;
            }

            .vet-encounter-timeline__item {
                flex-direction: column;
                gap: 1rem;
                padding-left: 0;
                padding-bottom: 2rem;
            }

            .vet-encounter-timeline__marker {
                text-align: left;
                padding-right: 0;
                padding-left: 3.5rem;
            }

            .vet-encounter-timeline__icon {
                left: 0;
                right: auto;
            }

            .vet-encounter-timeline::before {
                left: 1.5rem;
            }
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-0 px-md-2">
    <div class="card pet-crm-header mb-4 shadow-lg">
        <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h4 class="mb-1 fw-bold">Linha do tempo médica de {{ $animal->nome }}</h4>
                <div class="pet-crm-header__meta small text-white-50 d-flex flex-wrap gap-2">
                    @if($animal->especie?->nome)
                        <span><i class="ri-paw-line me-1"></i>{{ $animal->especie->nome }}</span>
                    @endif
                    @if($animal->raca?->nome)
                        <span><i class="ri-price-tag-3-line me-1"></i>{{ $animal->raca->nome }}</span>
                    @endif
                    @if(!is_null($animal->idade))
                        <span><i class="ri-hourglass-line me-1"></i>{{ $animal->idade }} anos</span>
                    @endif
                    @if($animal->cliente)
                        <span><i class="ri-user-heart-line me-1"></i>Tutor: {{ $animal->cliente->razao_social ?? $animal->cliente->nome_fantasia }}</span>
                    @endif
                </div>
            </div>
            <div class="pet-crm-header__year-selector d-flex flex-wrap justify-content-lg-end gap-2">
                @foreach($availableYears as $yearOption)
                    <a
                        href="{{ route('animais.pacientes.crm', [$animal->id, 'year' => $yearOption]) }}"
                        class="btn btn-sm {{ (int) $yearOption === (int) $selectedYear ? 'btn-light text-primary shadow-sm' : 'btn-outline-light' }}"
                    >
                        {{ $yearOption }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-4 col-xxl-3">
            <div class="card pet-crm-patient-card shadow-sm mb-3">
                <div class="card-body">
                    <span class="pet-crm-patient-card__title">Dados do paciente</span>
                    <h5 class="mt-2 mb-3 text-color fw-bold">{{ $animal->nome }}</h5>
                    <dl>
                        <dt>Espécie</dt>
                        <dd>{{ $animal->especie->nome ?? '—' }}</dd>
                        <dt>Raça</dt>
                        <dd>{{ $animal->raca->nome ?? '—' }}</dd>
                        <dt>Sexo</dt>
                        <dd>{{ $animal->sexo === 'F' ? 'Fêmea' : ($animal->sexo === 'M' ? 'Macho' : '—') }}</dd>
                        <dt>Data de nascimento</dt>
                        <dd>
                            @php
                                $birthDate = $animal->data_nascimento ? \Carbon\Carbon::parse($animal->data_nascimento) : null;
                            @endphp
                            {{ $birthDate ? $birthDate->format('d/m/Y') : '—' }}
                        </dd>
                        <dt>Peso</dt>
                        <dd>{{ $animal->peso ? number_format((float) $animal->peso, 2, ',', '.') . ' kg' : '—' }}</dd>
                        <dt>Identificação</dt>
                        <dd>{{ $animal->chip ?: '—' }}</dd>
                        <dt>Pedigree</dt>
                        <dd>
                            @php
                                $hasPedigree = $animal->tem_pedigree;
                                if (is_string($hasPedigree)) {
                                    $hasPedigree = strtoupper($hasPedigree) === 'S';
                                } elseif (is_numeric($hasPedigree)) {
                                    $hasPedigree = (bool) $hasPedigree;
                                }
                            @endphp
                            @if($hasPedigree === null)
                                —
                            @elseif($hasPedigree)
                                {{ $animal->pedigree ?: 'Sim' }}
                            @else
                                Não
                            @endif
                        </dd>
                        <dt>Tutor</dt>
                        <dd>{{ $animal->cliente->razao_social ?? $animal->cliente->nome_fantasia ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card pet-crm-patient-card shadow-sm">
                <div class="card-body">
                    <span class="pet-crm-patient-card__title">Resumo {{ $selectedYear }}</span>
                    <div class="row g-3 pt-3">
                        <div class="col-6">
                            <div class="pet-crm-metric-card">
                                <span class="pet-crm-metric-card__label">Atendimentos</span>
                                <p class="pet-crm-metric-card__value">{{ $stats['encounters'] ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pet-crm-metric-card">
                                <span class="pet-crm-metric-card__label">Consultas</span>
                                <p class="pet-crm-metric-card__value">{{ $stats['records'] ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pet-crm-metric-card">
                                <span class="pet-crm-metric-card__label">Prescrições</span>
                                <p class="pet-crm-metric-card__value">{{ $stats['prescriptions'] ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pet-crm-metric-card">
                                <span class="pet-crm-metric-card__label">Vacinações</span>
                                <p class="pet-crm-metric-card__value">{{ $stats['vaccinations'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-8 col-xxl-9">
            @if(!$hasEvents)
                <div class="card pet-crm-empty shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="ri-time-line display-4 text-primary mb-3"></i>
                        <h5 class="fw-semibold text-color">Nenhuma movimentação encontrada em {{ $selectedYear }}.</h5>
                        <p class="text-muted mb-0">Os registros de atendimentos veterinários aparecerão aqui assim que forem criados.</p>
                    </div>
                </div>
            @else
                @foreach($timeline as $month)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h6 class="pet-crm-month-title text-muted">{{ $month['label'] }} • {{ $selectedYear }}</h6>
                        </div>
                        <div class="card-body pt-3 pb-4">
                            <div class="vet-encounter-timeline">
                                @foreach($month['events'] as $event)
                                    @php
                                        $occurredAt = $event['occurred_at'] ?? null;
                                        $dateLabel = $occurredAt instanceof \Carbon\Carbon ? $occurredAt->format('d/m/Y') : null;
                                        $hourLabel = $occurredAt instanceof \Carbon\Carbon ? $occurredAt->format('H:i') : null;
                                        $encounter = $event['encounter'] ?? [];
                                    @endphp
                                    <div class="vet-encounter-timeline__item">
                                        <div class="vet-encounter-timeline__marker">
                                            <div class="vet-encounter-timeline__moment">
                                                <span class="vet-encounter-timeline__date {{ $dateLabel ? '' : 'text-muted' }}">{{ $dateLabel ?? 'Sem data' }}</span>
                                                <span class="vet-encounter-timeline__hour">{{ $hourLabel ? $hourLabel . 'h' : '—' }}</span>
                                            </div>
                                            <div class="vet-encounter-timeline__icon">
                                                <i class="{{ $event['icon'] ?? 'ri-information-line' }}"></i>
                                            </div>
                                        </div>
                                        <div class="vet-encounter-timeline__card">
                                            <div class="vet-encounter-timeline__card-header">
                                                <div>
                                                    <h6 class="vet-encounter-timeline__title text-color mb-0">{{ $event['title'] }}</h6>
                                                    @if(!empty($encounter['service']))
                                                        <span class="small text-muted">{{ $encounter['service'] }}</span>
                                                    @endif
                                                </div>
                                                @if(!empty($encounter['status']))
                                                    <span class="badge bg-{{ $encounter['status_color'] ?? 'primary' }} align-self-start">{{ $encounter['status'] }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($event['description']))
                                                <p class="vet-encounter-timeline__description text-muted small">{{ $event['description'] }}</p>
                                            @endif
                                            <dl class="vet-encounter-timeline__details">
                                                @if(!empty($encounter['code']))
                                                    <div class="vet-encounter-timeline__detail">
                                                        <dt>Atendimento</dt>
                                                        <dd>{{ $encounter['code'] }}</dd>
                                                    </div>
                                                @endif
                                                @if(!empty($encounter['veterinarian']))
                                                    <div class="vet-encounter-timeline__detail">
                                                        <dt>Veterinário</dt>
                                                        <dd>{{ $encounter['veterinarian'] }}</dd>
                                                    </div>
                                                @endif
                                                @if(!empty($encounter['start_display']))
                                                    <div class="vet-encounter-timeline__detail">
                                                        <dt>Início</dt>
                                                        <dd>{{ $encounter['start_display'] }}</dd>
                                                    </div>
                                                @endif
                                                @if(!empty($encounter['room']))
                                                    <div class="vet-encounter-timeline__detail">
                                                        <dt>Sala</dt>
                                                        <dd>{{ $encounter['room'] }}</dd>
                                                    </div>
                                                @endif
                                                @foreach($event['details'] as $label => $value)
                                                    <div class="vet-encounter-timeline__detail">
                                                        <dt>{{ $label }}</dt>
                                                        <dd>{{ $value }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                            @php
                                                $historyUrl = $encounter['history_url'] ?? null;
                                                $extraLink = !empty($event['link']) && $event['link'] !== $historyUrl;
                                            @endphp
                                            <div class="d-flex flex-wrap gap-2 pt-1">
                                                @if($historyUrl)
                                                    <a href="{{ $historyUrl }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ri-sticky-note-line me-1"></i>Ver atendimento
                                                    </a>
                                                @endif
                                                @if($extraLink)
                                                    <a href="{{ $event['link'] }}" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ri-external-link-line me-1"></i>Detalhes
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection