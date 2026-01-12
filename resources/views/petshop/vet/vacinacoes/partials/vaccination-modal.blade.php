@php($modalId = $modalId ?? ('vet-vacc-modal-' . \Illuminate\Support\Str::slug($vaccination['code'] ?? uniqid())))

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div class="d-flex align-items-start gap-5">
                    <div class="d-flex flex-column align-item-center gap-2">
                        <h4 class="modal-title fw-semibold text-white" id="{{ $modalId }}-label">{{ $vaccination['patient'] ?? 'Paciente' }}</h4>
                        <p class="text-white mb-0">
                            {{ $vaccination['species'] ?? '—' }}
                            @if(!empty($vaccination['breed']))
                                • {{ $vaccination['breed'] }}
                            @endif
                            @if(!empty($vaccination['tutor']))
                                • Tutor: {{ $vaccination['tutor'] }}
                            @endif
                        </p>
                    </div>
                    <div class="d-flex flex-column g-2">
                        <span class="badge bg-light text-purple mb-2" style="width: min-content">{{ $vaccination['code'] ?? 'Sem código' }}</span>
                        <p class="text-white small mb-0">Próxima dose em <strong>{{ $vaccination['next_due'] ?? '—' }}</strong></p>
                    </div>
                    <div class="text-end">
                        <span class="vet-vacinacoes__status-badge bg-{{ $vaccination['status_color'] ?? 'light' }}-subtle text-{{ $vaccination['status_color'] ?? 'muted' }}">{{ $vaccination['status'] ?? 'Sem status' }}</span>
                    </div>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @forelse ($vaccination['tags'] ?? [] as $tag)
                        <span class="vet-vacinacoes__tag"><i class="ri-price-tag-3-line"></i>{{ $tag }}</span>
                    @empty
                        <span class="text-muted small">Sem etiquetas cadastradas.</span>
                    @endforelse
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-syringe-line me-2"></i>Informações da vacina</h6>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="d-flex justify-content-between"><span class="text-muted">Vacina</span><span class="fw-semibold text-color">{{ data_get($vaccination, 'vaccine.name', '—') }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Fabricante</span><span class="fw-semibold text-color">{{ data_get($vaccination, 'vaccine.manufacturer', '—') }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Lote</span><span class="fw-semibold text-color">{{ data_get($vaccination, 'vaccine.lot', '—') }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Validade</span><span class="fw-semibold text-color">{{ data_get($vaccination, 'vaccine.valid_until', '—') }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Dose</span><span class="fw-semibold text-color">{{ data_get($vaccination, 'vaccine.dose', '—') }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Via</span><span class="fw-semibold text-color">{{ data_get($vaccination, 'vaccine.route', '—') }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Local</span><span class="fw-semibold text-color text-end">{{ data_get($vaccination, 'vaccine.site', '—') }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-calendar-event-line me-2"></i>Agenda e equipe</h6>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="d-flex justify-content-between"><span class="text-muted">Agendado para</span><span class="fw-semibold text-color">{{ $vaccination['scheduled_at'] ?? '—' }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Última aplicação</span><span class="fw-semibold text-color">{{ $vaccination['last_application'] ?? '—' }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Veterinário</span><span class="fw-semibold text-color">{{ $vaccination['veterinarian'] ?? '—' }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Ambiente</span><span class="fw-semibold text-color">{{ $vaccination['clinic_room'] ?? '—' }}</span></div>
                                @if (!empty(data_get($vaccination, 'attendance.code')))
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Atendimento</span>
                                        <a
                                            href="{{ data_get($vaccination, 'attendance.url') }}"
                                            class="fw-semibold text-color text-decoration-none"
                                            target="_blank"
                                        >
                                            {{ data_get($vaccination, 'attendance.code') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-information-line me-2"></i>Observações clínicas</h6>
                            <p class="text-muted small mb-0">{{ $vaccination['observations'] ?? 'Sem observações adicionais.' }}</p>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-notification-3-line me-2"></i>Lembretes</h6>
                            <div class="d-flex flex-column gap-2" id="{{ $modalId }}-reminders">
                                @forelse ($vaccination['reminders'] ?? [] as $reminder)
                                    <span class="text-color small">{{ $reminder }}</span>
                                @empty
                                    <span class="text-muted small">Nenhum lembrete configurado.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-7">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-time-line me-2"></i>Linha do tempo</h6>
                            <div class="position-relative">
                                @if (!empty($vaccination['timeline']))
                                    <div class="vet-vacinacoes__timeline d-flex flex-column gap-3">
                                        @foreach ($vaccination['timeline'] as $event)
                                            <div class="vet-vacinacoes__timeline-item">
                                                <span class="vet-vacinacoes__timeline-date">{{ $event['date'] ?? '—' }}</span>
                                                <h6 class="text-color fw-semibold mb-1">{{ $event['title'] ?? 'Evento' }}</h6>
                                                <p class="text-muted small mb-0">{{ $event['description'] ?? 'Sem descrição.' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted small mb-0">Nenhum evento registrado.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-alert-line me-2"></i>Alertas e reações</h6>
                            <div class="d-flex flex-column gap-2">
                                @forelse ($vaccination['alerts'] ?? [] as $alert)
                                    <div class="border rounded-3 p-3 d-flex align-items-start gap-2">
                                        <i class="ri-alert-line text-{{ $alert['type'] === 'danger' ? 'danger' : ($alert['type'] === 'warning' ? 'warning' : 'info') }} mt-1"></i>
                                        <div>
                                            <h6 class="text-color fw-semibold mb-1">{{ $alert['title'] ?? 'Alerta' }}</h6>
                                            <p class="text-muted small mb-0">{{ $alert['description'] ?? 'Sem detalhes.' }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-muted small">Nenhum alerta específico.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-clipboard-line me-2"></i>Próximos passos</h6>
                            <ul class="list-group list-group-flush small mb-0">
                                @forelse ($vaccination['follow_up'] ?? [] as $step)
                                    <li class="list-group-item px-0">{{ $step }}</li>
                                @empty
                                    <li class="list-group-item px-0 text-muted">Nenhum próximo passo definido.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-task-line me-2"></i>Checklist pré-aplicação</h6>
                            <div class="d-flex flex-column gap-2">
                                @forelse ($vaccination['checklist'] ?? [] as $item)
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="vet-vacinacoes__check-icon {{ !empty($item['checked']) ? 'bg-success-subtle text-success' : 'bg-light text-muted' }}">
                                            <i class="{{ !empty($item['checked']) ? 'ri-check-line' : 'ri-checkbox-blank-line' }}"></i>
                                        </span>
                                        <span class="text-color small">{{ $item['label'] ?? 'Item do checklist' }}</span>
                                    </div>
                                @empty
                                    <span class="text-muted small">Checklist ainda não iniciado.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-archive-line me-2"></i>Documentos anexados</h6>
                            <div class="d-flex flex-column gap-2">
                                @forelse ($vaccination['documents'] ?? [] as $document)
                                    <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2">
                                        <div>
                                            <h6 class="text-color fw-semibold mb-0">{{ $document['label'] ?? 'Documento' }}</h6>
                                            <p class="text-muted small mb-0">{{ $document['type'] ?? 'Tipo desconhecido' }}{{ !empty($document['date']) ? ' • ' . $document['date'] : '' }}</p>
                                        </div>
                                        <button class="btn btn-light btn-sm" type="button">
                                            <i class="ri-download-2-line"></i>
                                        </button>
                                    </div>
                                @empty
                                    <span class="text-muted small">Nenhum documento anexado.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-3 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-dropper-line me-2"></i>Estoque do lote</h6>
                            <div class="d-flex flex-column gap-2">
                                <div class="vet-vacinacoes__metric-card">
                                    <span class="text-muted small">Doses disponíveis</span>
                                    <div class="vet-vacinacoes__metric-value">{{ data_get($vaccination, 'inventory.stock_available', '—') }}</div>
                                </div>
                                <div class="vet-vacinacoes__metric-card">
                                    <span class="text-muted small">Reservas confirmadas</span>
                                    <div class="vet-vacinacoes__metric-value">{{ data_get($vaccination, 'inventory.reserved_doses', '—') }}</div>
                                </div>
                                <div class="vet-vacinacoes__metric-card">
                                    <span class="text-muted small">Perdas registradas</span>
                                    <div class="vet-vacinacoes__metric-value">{{ data_get($vaccination, 'inventory.wastage', '—') }}</div>
                                </div>
                                <div class="vet-vacinacoes__metric-card">
                                    <span class="text-muted small">Controle de temperatura</span>
                                    <div class="vet-vacinacoes__metric-value">{{ data_get($vaccination, 'inventory.temperature_monitoring', '—') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <a href="{{ route('vet.vaccinations.create') }}" class="btn btn-primary">
                    <i class="ri-calendar-check-line me-1"></i> Reagendar reforço
                </a>
            </div>
        </div>
    </div>
</div>