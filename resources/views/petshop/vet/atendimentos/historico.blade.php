@extends('layouts.app', ['title' => 'Histórico do atendimento'])

@section('css')
    @parent
    <style>
        .vet-encounter-history__summary {
            border-radius: 20px;
        }

        .vet-encounter-history__summary .badge {
            letter-spacing: .06em;
        }

        .vet-encounter-history__metric {
            background: rgba(76, 63, 179, 0.05);
            border-radius: 14px;
            padding: .85rem 1rem;
            height: 100%;
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
            margin-bottom: 0;
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

        .vet-encounter-history__prescription {
            background: rgba(76, 63, 179, 0.05);
            border-radius: 16px;
        }

        .vet-encounter-history__checklist li {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }

        .vet-encounter-history__checklist i {
            margin-top: .1rem;
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
            .vet-encounter-history__metric {
                border-radius: 12px;
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
                padding: 0 0 0 3.4rem;
                flex-basis: auto;
            }

            .vet-encounter-timeline__icon {
                left: 0;
                right: auto;
            }

            .vet-encounter-timeline__moment {
                flex-direction: row;
                align-items: center;
                gap: .5rem;
            }
        }

        @media (max-width: 575.98px) {
            .vet-encounter-timeline__card {
                padding: 1.25rem;
            }

            .vet-encounter-timeline__details {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection


@section('content')
    @php
        $prescriptionParams = array_filter([
            'atendimento' => $encounter['id'] ?? null,
            'patient_id' => $encounter['patient_id'] ?? $encounter['animal_id'] ?? null,
            'veterinarian_id' => $encounter['veterinarian_id'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-2 mb-3">
           
        </div>

        <div class="card shadow-sm border-0 mb-4 vet-encounter-history__summary">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <div class="flex-grow-1">
                        <span class="badge bg-{{ $encounter['status_color'] ?? 'primary' }} text-uppercase">{{ $encounter['status'] ?? '—' }}</span>
                        <h2 class="h4 text-color mb-1 mt-2">{{ $encounter['patient'] ?? 'Paciente não informado' }}</h2>
                        <div class="text-muted">
                            {{ $encounter['species'] ?? 'Espécie não informada' }}
                            @if(!empty($encounter['breed']))
                                • {{ $encounter['breed'] }}
                            @endif
                        </div>
                        @if(!empty($encounter['tutor']))
                            <div class="text-muted small">
                                Tutor: {{ $encounter['tutor'] }}
                            </div>
                        @endif
                        <div class="text-muted small">Código: {{ $encounter['code'] ?? '—' }}</div>
                    </div>
                    <div class="d-flex flex-column flex-lg-column align-items-lg-end gap-3 flex-grow-1">
                        <div class="d-flex justify-content-lg-end">
                        </div>
                        <div class="row g-3 flex-grow-1">
                        <div class="col-sm-6 col-lg-3">
                            <div class="vet-encounter-history__metric">
                                <span class="text-muted small text-uppercase">Serviço</span>
                                <div class="fw-semibold text-color">{{ $encounter['service'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="vet-encounter-history__metric">
                                <span class="text-muted small text-uppercase">Veterinário</span>
                                <div class="fw-semibold text-color">{{ $encounter['veterinarian'] ?? 'Não definido' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="vet-encounter-history__metric">
                                <span class="text-muted small text-uppercase">Horário previsto</span>
                                <div class="fw-semibold text-color">{{ $encounter['start_display'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="vet-encounter-history__metric">
                                <span class="text-muted small text-uppercase">Sala</span>
                                <div class="fw-semibold text-color">{{ $encounter['room'] ?? '—' }}</div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="text-color mb-1">Eventos do atendimento</h5>
                        <p class="text-muted small mb-0">Linha do tempo com os principais marcos do atendimento.</p>
                    </div>
                    <div class="card-body">
                        <div class="vet-encounter-timeline">
                            @forelse ($history as $event)
                                @php
                                    $hasTimestamp = !empty($event['time']) && $event['time'] !== '—';
                                    $timeParts = $hasTimestamp ? explode(' ', $event['time']) : [];
                                    $dateLabel = $timeParts[0] ?? null;
                                    $hourLabel = $timeParts[1] ?? null;
                                @endphp
                                <div class="vet-encounter-timeline__item">
                                    <div class="vet-encounter-timeline__marker">
                                        <div class="vet-encounter-timeline__moment">
                                            <span class="vet-encounter-timeline__date {{ $dateLabel ? '' : 'text-muted' }}">{{ $dateLabel ?? 'Sem data' }}</span>
                                            @if($hourLabel)
                                                <span class="vet-encounter-timeline__hour">{{ $hourLabel }}</span>
                                            @endif
                                        </div>
                                        <div class="vet-encounter-timeline__icon">
                                            <i class="{{ $event['icon'] }}"></i>
                                        </div>
                                    </div>
                                    <div class="vet-encounter-timeline__card">
                                        <div class="vet-encounter-timeline__card-header">
                                            <h6 class="vet-encounter-timeline__title text-color">{{ $event['title'] }}</h6>
                                            @if(!empty($event['link']))
                                                <a href="{{ $event['link'] }}" class="btn btn-sm btn-outline-primary">
                                                    Ver detalhes
                                                </a>
                                            @endif
                                        </div>
                                        @if(!empty($event['description']))
                                            <p class="vet-encounter-timeline__description text-muted small">{{ $event['description'] }}</p>
                                        @endif
                                        @if(!empty($event['details']))
                                            <dl class="vet-encounter-timeline__details">
                                                @foreach($event['details'] as $label => $value)
                                                    <div class="vet-encounter-timeline__detail">
                                                        <dt>{{ $label }}</dt>
                                                        <dd>{{ $value }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Nenhuma movimentação registrada para este atendimento.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @if(!empty($triageDetails))
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="text-color mb-1">Triagem e sinais vitais</h6>
                            <p class="text-muted small mb-0">Resumo dos dados coletados na chegada.</p>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                                @foreach($triageDetails as $item)
                                    <li>
                                        <span class="text-muted small text-uppercase d-block">{{ $item['label'] }}</span>
                                        <span class="fw-semibold text-color">{{ $item['value'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if(!empty($encounter['motivo_visita']) || !empty($encounter['observacoes_triagem']))
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="text-color mb-1">Anotações do atendimento</h6>
                            <p class="text-muted small mb-0">Informações fornecidas pelo tutor e equipe.</p>
                        </div>
                        <div class="card-body">
                            @if(!empty($encounter['motivo_visita']))
                                <div class="mb-3">
                                    <span class="text-muted small text-uppercase d-block">Motivo da visita</span>
                                    <p class="mb-0 text-color small">{{ $encounter['motivo_visita'] }}</p>
                                </div>
                            @endif
                            @if(!empty($encounter['observacoes_triagem']))
                                <div class="mb-0">
                                    <span class="text-muted small text-uppercase d-block">Observações de triagem</span>
                                    <p class="mb-0 text-color small">{{ $encounter['observacoes_triagem'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(!empty($records))
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="text-color mb-1">Prontuários vinculados</h6>
                            <p class="text-muted small mb-0">Registros clínicos associados a este atendimento.</p>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($records as $record)
                                <a href="{{ $record['url'] }}" class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-semibold text-color">{{ $record['code'] }}</div>
                                            @if(!empty($record['summary']))
                                                <div class="text-muted small">{{ $record['summary'] }}</div>
                                            @endif
                                            <div class="text-muted small mt-1">
                                                @if(!empty($record['registered_at']))
                                                    {{ $record['registered_at'] }}
                                                @endif
                                                @if(!empty($record['veterinarian']))
                                                    @if(!empty($record['registered_at']))
                                                        •
                                                    @endif
                                                    {{ $record['veterinarian'] }}
                                                @endif
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $record['status_color'] }} align-self-start">{{ $record['status'] }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($prescriptions))
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="text-color mb-1">Prescrições emitidas</h6>
                            <p class="text-muted small mb-0">Medicamentos e orientações liberados na consulta.</p>
                        </div>
                        <div class="card-body d-flex flex-column gap-3">
                            @foreach($prescriptions as $prescription)
                                <div class="vet-encounter-history__prescription p-3 border">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <div class="fw-semibold text-color">Prescrição #{{ $prescription['id'] }}</div>
                                        <span class="badge bg-{{ $prescription['status_color'] }}">{{ $prescription['status'] }}</span>
                                    </div>
                                    @if(!empty($prescription['issued_at']))
                                        <div class="text-muted small mb-1">Emitida em {{ $prescription['issued_at'] }}</div>
                                    @endif
                                    @if(!empty($prescription['summary']))
                                        <p class="text-muted small mb-1">{{ $prescription['summary'] }}</p>
                                    @endif
                                    @if(!empty($prescription['medications']))
                                        <div class="small text-color">
                                            <i class="ri-capsule-line me-1"></i> {{ $prescription['medications'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($vaccinations))
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="text-color mb-1">Vacinações relacionadas</h6>
                            <p class="text-muted small mb-0">Agendamentos e aplicações vinculados a este atendimento.</p>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($vaccinations as $vaccination)
                                @if(!empty($vaccination['link']))
                                    <a href="{{ $vaccination['link'] }}" class="list-group-item list-group-item-action py-3">
                                @else
                                    <div class="list-group-item py-3">
                                @endif
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="fw-semibold text-color">{{ $vaccination['code'] }}</div>
                                                @if(!empty($vaccination['summary']))
                                                    <div class="text-muted small">{{ $vaccination['summary'] }}</div>
                                                @endif
                                                <div class="text-muted small mt-1">
                                                    @if(!empty($vaccination['scheduled_at']))
                                                        Agendada para {{ $vaccination['scheduled_at'] }}
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="badge bg-{{ $vaccination['status_color'] }} align-self-start">{{ $vaccination['status'] }}</span>
                                        </div>
                                @if(!empty($vaccination['link']))
                                    </a>
                                @else
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($encounter['checklists']))
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="text-color mb-1">Checklist assistencial</h6>
                            <p class="text-muted small mb-0">Atividades concluídas durante o atendimento.</p>
                        </div>
                        <div class="card-body d-flex flex-column gap-3">
                            @foreach($encounter['checklists'] as $key => $items)
                                @php
                                    $itemsCollection = collect(is_array($items) ? $items : [$items])->filter();
                                @endphp
                                @continue($itemsCollection->isEmpty())
                                @php
                                    $displayName = is_string($key)
                                        ? \Illuminate\Support\Str::headline($key)
                                        : 'Checklist ' . ($loop->iteration ?? 1);
                                @endphp
                                <div>
                                    <div class="fw-semibold text-color small mb-2">{{ $displayName }}</div>
                                    <ul class="list-unstyled mb-0 vet-encounter-history__checklist">
                                        @foreach($itemsCollection as $item)
                                            <li>
                                                <i class="ri-check-line text-success"></i>
                                                <span class="text-color small">{{ $item }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($attachments))
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="text-color mb-1">Anexos do atendimento</h6>
                            <p class="text-muted small mb-0">Arquivos compartilhados com a equipe.</p>
                        </div>
                        <div class="card-body d-flex flex-column gap-3">
                            @foreach($attachments as $attachment)
                                <div class="d-flex justify-content-between align-items-center gap-3 border rounded-3 px-3 py-2">
                                    <div>
                                        <div class="fw-semibold text-color">{{ $attachment['name'] }}</div>
                                        <div class="text-muted small">
                                            {{ $attachment['uploaded_at'] ?? '—' }}
                                            @if(!empty($attachment['uploaded_by']))
                                                • {{ $attachment['uploaded_by'] }}
                                            @endif
                                            @if(!empty($attachment['size']))
                                                • {{ $attachment['size'] }}
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ $attachment['url'] }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                        Abrir
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection