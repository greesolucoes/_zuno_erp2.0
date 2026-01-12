@php
    $modalId = $modalId ?? 'vet-appointments-alerts-modal';
    $alerts = collect($alerts ?? [])
        ->filter(fn ($alert) => !empty($alert))
        ->values();
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}-label">Alertas assistenciais</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column gap-3">
                    @if ($alerts->isNotEmpty())
                        @foreach ($alerts as $alert)
                            @php
                                $severity = $alert['status_color'] ?? 'primary';
                                $icon = [
                                    'danger' => 'ri-error-warning-line',
                                    'warning' => 'ri-alert-line',
                                    'success' => 'ri-checkbox-circle-line',
                                    'primary' => 'ri-information-line',
                                ][$severity] ?? 'ri-information-line';
                            @endphp
                            <div class="card shadow-sm border-{{ $severity === 'primary' ? 'light' : $severity }}">
                                <div class="card-body d-flex gap-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $severity }} text-white" style="width: 40px; height: 40px;">
                                        <i class="{{ $icon }}"></i>
                                    </span>
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                            <h6 class="text-color fw-semibold mb-0">{{ $alert['patient'] ?? 'Paciente' }} • {{ $alert['service'] ?? 'Serviço' }}</h6>
                                            <span class="badge bg-{{ $severity }}">{{ ucfirst($severity) }}</span>
                                        </div>
                                        <p class="text-muted small mb-2">Código {{ $alert['code'] ?? '—' }} • {{ $alert['start'] ?? 'Horário não informado' }}</p>
                                        <p class="mb-0">{{ $alert['notes'] ?? 'Sem observações registradas.' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted small text-center py-4">
                            Nenhum alerta assistencial registrado no momento.
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>