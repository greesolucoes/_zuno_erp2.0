@php($modalId = $modalId ?? 'vet-prescriptions-alerts-modal')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-semibold text-color" id="{{ $modalId }}-label">Alertas do setor</h5>
                    <p class="text-muted mb-0">Avisos gerais e pendências que exigem atenção da equipe assistencial.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="d-flex flex-column gap-3">
                    @forelse ($alerts as $alert)
                        <div class="p-3 border rounded d-flex align-items-start gap-3">
                            @php($variant = $alert['type'] === 'warning' ? 'warning' : ($alert['type'] === 'danger' ? 'danger' : ($alert['type'] === 'success' ? 'success' : 'primary')))
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $variant }}-subtle text-{{ $variant }} p-3">
                                <i class="ri-alert-line fs-5"></i>
                            </span>
                            <div>
                                <h6 class="text-color fw-semibold mb-1">{{ $alert['title'] ?? 'Alerta' }}</h6>
                                <p class="text-muted small mb-0">{{ $alert['description'] ?? 'Sem detalhes adicionais.' }}</p>
                            </div>
                        </div>
                    @empty
                        <span class="text-muted small">Nenhum alerta registrado no momento.</span>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>