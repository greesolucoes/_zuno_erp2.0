@php($modalId = $modalId ?? ('vet-prescription-modal-' . \Illuminate\Support\Str::slug($prescription['code'] ?? uniqid())))

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-semibold text-color" id="{{ $modalId }}-label">Detalhes da prescrição</h5>
                    <p class="text-muted mb-0">Resumo clínico completo, orientações e histórico assistencial do paciente.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $prescription['status_color'] ?? 'primary' }}-subtle text-{{ $prescription['status_color'] ?? 'primary' }} text-uppercase">{{ $prescription['status'] ?? '—' }}</span>
                                <span class="badge rounded-pill border border-{{ $prescription['priority_color'] ?? 'primary' }} text-{{ $prescription['priority_color'] ?? 'primary' }}">{{ $prescription['priority'] ?? '—' }}</span>
                            </div>
                            <div class="text-muted small">Atualizada em {{ $prescription['updated_at'] ?? '—' }}</div>
                        </div>

                        <div class="d-flex flex-column gap-1 mb-3">
                            <h4 class="text-color fw-bold mb-0">{{ $prescription['patient'] ?? 'Paciente não informado' }}</h4>
                            <p class="text-muted mb-0">
                                {{ $prescription['species'] ?? '—' }}
                                @if(!empty($prescription['breed']))
                                    • {{ $prescription['breed'] }}
                                @endif
                                @if(!empty($prescription['tutor']))
                                    • Tutor: {{ $prescription['tutor'] }}
                                @endif
                            </p>
                            <p class="text-muted small mb-0">{{ $prescription['summary'] ?? 'Sem resumo clínico cadastrado.' }}</p>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @forelse ($prescription['tags'] ?? [] as $tag)
                                <span class="badge rounded-pill text-bg-light d-inline-flex align-items-center gap-1">
                                    <i class="ri-price-tag-3-line"></i>
                                    {{ $tag }}
                                </span>
                            @empty
                                <span class="text-muted small">Nenhuma etiqueta registrada.</span>
                            @endforelse
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Código</p>
                                    <span class="fw-semibold text-color">{{ $prescription['code'] ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Veterinário responsável</p>
                                    <span class="fw-semibold text-color">{{ $prescription['veterinarian'] ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Emitida em</p>
                                    <span class="fw-semibold text-color">{{ $prescription['created_at'] ?? '—' }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="border rounded-3 p-3 h-100">
                                    <p class="text-muted small mb-1">Revalidar até</p>
                                    <span class="fw-semibold text-color">{{ $prescription['next_revalidation'] ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="row g-3" data-testid="prescription-metrics">
                            @forelse ($prescription['metrics'] ?? [] as $metric)
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <div class="card border shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center gap-3">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary p-3">
                                                <i class="{{ $metric['icon'] ?? 'ri-information-line' }} fs-5"></i>
                                            </span>
                                            <div>
                                                <p class="text-muted small mb-1">{{ $metric['label'] ?? 'Indicador' }}</p>
                                                <h6 class="text-color fw-semibold mb-0">{{ $metric['value'] ?? '—' }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-light mb-0">Nenhum indicador disponível.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase small text-muted mb-3 d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary p-2">
                                        <i class="ri-list-check"></i>
                                    </span>
                                    Orientações e segurança
                                </h6>
                                <div class="d-flex flex-column gap-3">
                                <div>
                                    <p class="text-muted text-uppercase small fw-semibold mb-2">Instruções ao tutor</p>
                                    <ul class="list-group list-group-flush">
                                        @forelse ($prescription['instructions'] ?? [] as $instruction)
                                            <li class="list-group-item px-0">{{ $instruction }}</li>
                                        @empty
                                            <li class="list-group-item px-0 text-muted">Nenhuma orientação registrada.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-muted text-uppercase small fw-semibold mb-2">Alertas de segurança</p>
                                    <ul class="list-group list-group-flush">
                                        @forelse ($prescription['safety_notes'] ?? [] as $note)
                                            <li class="list-group-item px-0 text-muted">{{ $note }}</li>
                                        @empty
                                            <li class="list-group-item px-0 text-muted">Nenhum alerta de segurança.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-muted text-uppercase small fw-semibold mb-2">Pendências assistenciais</p>
                                    <ul class="list-group list-group-flush">
                                        @forelse ($prescription['pending_actions'] ?? [] as $action)
                                            <li class="list-group-item px-0">{{ $action }}</li>
                                        @empty
                                            <li class="list-group-item px-0 text-muted">Sem pendências cadastradas.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase small text-muted mb-3 d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary p-2">
                                        <i class="ri-calendar-event-line"></i>
                                    </span>
                                    Checklist e linha do tempo
                                </h6>
                                <div class="d-flex flex-column gap-4">
                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($prescription['checklist'] ?? [] as $item)
                                            <div class="d-flex align-items-center gap-3">
                                                @php($checked = !empty($item['checked']))
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $checked ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} p-2">
                                                    <i class="{{ $checked ? 'ri-check-line' : 'ri-checkbox-blank-line' }}"></i>
                                                </span>
                                                <span class="text-color">{{ $item['label'] ?? '—' }}</span>
                                            </div>
                                        @empty
                                            <span class="text-muted small">Checklist ainda não iniciado.</span>
                                        @endforelse
                                    </div>
                                    <div>
                                        <div class="border-start border-2 border-primary ps-3 d-flex flex-column gap-3">
                                            @forelse ($prescription['timeline'] ?? [] as $event)
                                                <div>
                                                    <span class="text-uppercase text-primary small fw-semibold">{{ $event['time'] ?? '—' }}</span>
                                                    <h6 class="text-color fw-semibold mb-1">{{ $event['title'] ?? 'Evento' }}</h6>
                                                    <p class="text-muted small mb-0">{{ $event['description'] ?? 'Sem detalhes registrados.' }}</p>
                                                </div>
                                            @empty
                                                <p class="text-muted small mb-0">Nenhum evento registrado.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-xl-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase small text-muted mb-3 d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary p-2">
                                        <i class="ri-capsule-line"></i>
                                    </span>
                                    Plano medicamentoso
                                </h6>
                                <div class="d-flex flex-column gap-3">
                                    @forelse ($prescription['medications'] ?? [] as $medication)
                                    <div class="card border shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div>
                                                    <h6 class="text-color fw-semibold mb-1">{{ $medication['name'] ?? 'Medicamento' }}</h6>
                                                    <p class="text-muted small mb-0">{{ collect([$medication['dosage'] ?? null, $medication['frequency'] ?? null])->filter()->implode(' • ') }}</p>
                                                </div>
                                                @if(!empty($medication['duration']))
                                                    <span class="badge bg-primary-subtle text-primary">{{ $medication['duration'] }}</span>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-wrap gap-3 mt-3">
                                                @if(!empty($medication['start_at']))
                                                    <span class="text-muted small"><i class="ri-play-circle-line me-1"></i>{{ $medication['start_at'] }}</span>
                                                @endif
                                                @if(!empty($medication['end_at']))
                                                    <span class="text-muted small"><i class="ri-stop-circle-line me-1"></i>{{ $medication['end_at'] }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($medication['notes']))
                                                <p class="text-muted small mb-0 mt-3">{{ $medication['notes'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-muted small">Nenhum medicamento cadastrado.</span>
                                @endforelse

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex flex-column gap-4">
                                <div>
                                    <h6 class="text-uppercase small text-muted mb-3 d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary p-2">
                                            <i class="ri-calendar-check-line"></i>
                                        </span>
                                        Serviços relacionados
                                    </h6>
                                    <div class="d-flex flex-column gap-2">
                                        @forelse ($prescription['related_services'] ?? [] as $service)
                                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3">
                                                <span class="text-color">{{ $service['label'] ?? 'Serviço' }}</span>
                                                <span class="text-muted small">{{ $service['date'] ?? '—' }}</span>
                                            </div>
                                        @empty
                                            <span class="text-muted small">Sem serviços vinculados.</span>
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <h6 class="text-uppercase small text-muted mb-3 d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary p-2">
                                            <i class="ri-notification-badge-line"></i>
                                        </span>
                                        Alertas específicos
                                    </h6>
                                    <div class="d-flex flex-column gap-2">
                                        @forelse ($prescription['alerts'] ?? [] as $alert)
                                            @php($variant = $alert['type'] === 'warning' ? 'warning' : ($alert['type'] === 'danger' ? 'danger' : ($alert['type'] === 'success' ? 'success' : 'primary')))
                                            <div class="p-3 border rounded d-flex align-items-start gap-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $variant }}-subtle text-{{ $variant }} p-2">
                                                    <i class="ri-alert-line"></i>
                                                </span>
                                                <div>
                                                    <h6 class="text-color fw-semibold mb-1">{{ $alert['title'] ?? 'Alerta' }}</h6>
                                                    <p class="text-muted small mb-0">{{ $alert['description'] ?? 'Sem detalhes adicionais.' }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <span class="text-muted small">Nenhum alerta específico.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>