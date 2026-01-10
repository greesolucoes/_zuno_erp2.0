@php($modalId = $modalId ?? 'vet-vacc-alerts-modal')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-semibold text-color" id="{{ $modalId }}-label">Alertas e lembretes</h5>
                    <p class="text-muted mb-0">Pendências críticas, orientações pós-aplicação e lembretes configurados para os pacientes.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="mb-4">
                    <h6 class="text-uppercase small text-muted mb-2">Alertas ativos</h6>
                    <div class="d-flex flex-column gap-3">
                        @forelse ($alerts as $alert)
                            <div class="vet-vacinacoes__alert-card">
                                <span class="badge vet-vacinacoes__badge bg-{{ $alert['type'] ?? 'warning' }}-subtle text-{{ $alert['type'] ?? 'warning' }}">
                                    {{ ucfirst($alert['type'] ?? 'Alerta') }}
                                </span>
                                <div>
                                    <div class="d-flex flex-wrap justify-content-between gap-2">
                                        <div>
                                            <h5 class="fw-semibold text-color mb-1">{{ $alert['title'] ?? 'Alerta' }}</h5>
                                            <p class="text-muted small mb-1">{{ $alert['description'] ?? 'Sem descrição.' }}</p>
                                        </div>
                                        <span class="badge bg-light text-muted align-self-start">{{ $alert['patient'] ?? 'Paciente' }} {{ $alert['code'] ? '• ' . $alert['code'] : '' }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <span class="text-muted small">Nenhum alerta ativo no momento.</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h6 class="text-uppercase small text-muted mb-2">Lembretes configurados</h6>
                    <div class="d-flex flex-column gap-2">
                        @forelse ($reminders as $reminder)
                            <div class="border rounded-3 px-3 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-semibold text-color mb-1">{{ $reminder['patient'] ?? 'Paciente' }}</h6>
                                    <p class="text-muted small mb-0">{{ $reminder['text'] ?? 'Sem detalhes.' }}</p>
                                </div>
                                <span class="badge bg-primary-subtle text-primary">{{ $reminder['code'] ?? 'Sem código' }}</span>
                            </div>
                        @empty
                            <span class="text-muted small">Nenhum lembrete configurado.</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>