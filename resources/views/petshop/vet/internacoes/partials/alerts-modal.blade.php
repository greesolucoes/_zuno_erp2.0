@php($variantMap = [
    'primary' => 'primary',
    'success' => 'success',
    'warning' => 'warning',
    'danger' => 'danger',
    'secondary' => 'secondary',
    'info' => 'info',
])

<div class="modal fade" id="vet-hosp-alerts-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-color">Alertas assistenciais</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column gap-3">
                    @forelse ($alerts as $alert)
                        @php($variant = $variantMap[$alert['color'] ?? 'primary'] ?? 'primary')
                        <div class="alert alert-{{ $variant }} mb-0" role="alert">
                            <div class="d-flex align-items-start gap-2">
                                <i class="{{ $alert['icon'] }} mt-1"></i>
                                <div>
                                    <p class="text-muted small mb-1">{{ ucfirst($variant) }} • {{ now()->format('d/m/Y') }}</p>
                                    <p class="mb-0">{{ $alert['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light mb-0">Nenhum alerta registrado.</div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">
                    <i class="ri-notification-line me-1"></i>
                    Enviar comunicado à equipe
                </button>
            </div>
        </div>
    </div>
</div>