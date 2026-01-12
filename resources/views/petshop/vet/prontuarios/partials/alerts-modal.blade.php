@php($modalId = $modalId ?? 'vet-records-alerts-modal')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title text-color fw-bold mb-1" id="{{ $modalId }}-label">Alertas assistenciais</h5>
                    <p class="text-muted small mb-0">Avisos prioritários e observações relevantes para a equipe.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="d-flex flex-column gap-4">
                    <div>
                        <h6 class="text-color fw-semibold mb-3"><i class="ri-alert-line me-2"></i>Alertas críticos</h6>
                        <div class="d-flex flex-column gap-3">
                            @forelse ($clinicalAlerts as $alert)
                                <div class="p-3 border rounded-4 d-flex align-items-start gap-3">
                                    <span class="vet-prontuarios__alert-icon bg-{{ $alert['type'] ?? 'primary' }}-subtle text-{{ $alert['type'] ?? 'primary' }}">
                                        <i class="ri-notification-3-line"></i>
                                    </span>
                                    <div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                                            <h6 class="text-color fw-semibold mb-0">{{ $alert['label'] ?? 'Alerta' }}</h6>
                                            @if(!empty($alert['value']))
                                                <span class="badge bg-light text-muted">{{ $alert['value'] }}</span>
                                            @endif
                                        </div>
                                        <p class="text-muted small mb-0">{{ $alert['description'] ?? 'Sem detalhes adicionais.' }}</p>
                                    </div>
                                </div>
                            @empty
                                <span class="text-muted small">Nenhum alerta crítico registrado.</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h6 class="text-color fw-semibold mb-3"><i class="ri-time-line me-2"></i>Últimas movimentações</h6>
                        <div class="d-flex flex-column gap-3">
                            @forelse ($recentNotes as $note)
                                <div class="border rounded-4 p-3 d-flex gap-3 align-items-start">
                                    <span class="vet-prontuarios__note-icon">
                                        <i class="{{ $note['icon'] ?? 'ri-clipboard-line' }}"></i>
                                    </span>
                                    <div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <h6 class="text-color fw-semibold mb-0">{{ $note['title'] ?? 'Atualização' }}</h6>
                                            <span class="badge bg-light text-muted">{{ $note['time'] ?? '—' }}</span>
                                        </div>
                                        <p class="text-muted small mb-0">{{ $note['description'] ?? 'Sem detalhes adicionais.' }}</p>
                                    </div>
                                </div>
                            @empty
                                <span class="text-muted small">Sem movimentações recentes.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>