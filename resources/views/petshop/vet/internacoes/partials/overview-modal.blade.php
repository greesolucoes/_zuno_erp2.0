@php($variantMap = [
    'primary' => 'primary',
    'success' => 'success',
    'warning' => 'warning',
    'danger' => 'danger',
    'secondary' => 'secondary',
    'info' => 'info',
])

<div class="modal fade" id="vet-hosp-overview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-color">Visão geral da internação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    @foreach ($overview as $item)
                        @php($summaryVariant = $variantMap[$item['variant'] ?? 'primary'] ?? 'primary')
                        <div class="col-12 col-sm-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <span class="badge rounded-pill text-bg-{{ $summaryVariant }} fs-5">
                                            <i class="{{ $item['icon'] }}"></i>
                                        </span>
                                        <i class="ri-pulse-line text-muted"></i>
                                    </div>
                                    <h4 class="text-color fw-bold mb-1">{{ $item['value'] }}</h4>
                                    <p class="text-muted small mb-0">{{ $item['label'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card mt-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
                            <div>
                                <h6 class="fw-semibold text-color mb-1">Ocupação dos leitos</h6>
                                <p class="text-muted small mb-0">Monitoramento consolidado por ala e disponibilidade imediata.</p>
                            </div>
                            <span class="badge rounded-pill text-bg-primary d-inline-flex align-items-center gap-1">
                                <i class="ri-hotel-bed-line"></i>
                                {{ $capacity['occupied_beds'] }}/{{ $capacity['total_beds'] }} ocupados
                            </span>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small">Taxa de ocupação geral</span>
                                <strong class="text-color">{{ $capacity['occupancy_rate'] }}%</strong>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div
                                    class="progress-bar bg-primary"
                                    role="progressbar"
                                    style="width: {{ $capacity['occupancy_rate'] }}%"
                                    aria-valuenow="{{ $capacity['occupancy_rate'] }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                >
                                    <span class="visually-hidden">{{ $capacity['occupancy_rate'] }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body">
                                        <p class="text-uppercase small fw-semibold text-muted mb-1">UTI</p>
                                        <h4 class="text-primary fw-bold mb-1">{{ $capacity['icu_rate'] }}%</h4>
                                        <p class="text-muted small mb-0">{{ max($capacity['total_beds'] - $capacity['occupied_beds'], 0) }} leitos disponíveis</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body">
                                        <p class="text-uppercase small fw-semibold text-muted mb-1">Internação geral</p>
                                        <h4 class="text-color fw-bold mb-1">{{ $capacity['ward_rate'] }}%</h4>
                                        <p class="text-muted small mb-0">{{ $capacity['available_beds'] }} leitos livres</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>