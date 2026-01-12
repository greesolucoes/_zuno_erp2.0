@php($modalId = $modalId ?? 'vet-prescriptions-overview-modal')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-semibold text-color" id="{{ $modalId }}-label">Visão geral das prescrições</h5>
                    <p class="text-muted mb-0">Indicadores assistenciais, prazos críticos e status de abastecimento para o setor.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="row g-3 mb-4">
                    @foreach ($summary as $card)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $card['variant'] ?? 'primary' }}-subtle text-{{ $card['variant'] ?? 'primary' }} p-3">
                                            <i class="{{ $card['icon'] ?? 'ri-information-line' }} fs-4"></i>
                                        </span>
                                        <span class="badge bg-{{ $card['variant'] ?? 'primary' }} text-uppercase">Hoje</span>
                                    </div>
                                    <p class="text-muted text-uppercase fw-semibold small mb-1 mt-4">{{ $card['label'] ?? 'Indicador' }}</p>
                                    <h3 class="text-color fw-bold mb-0">{{ $card['value'] ?? '—' }}</h3>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    <div class="col-12 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase small text-muted mb-3">Indicadores de adesão</h6>
                                <div class="d-flex flex-column gap-3">
                                    @forelse ($adherenceIndicators as $indicator)
                                        <div class="d-flex align-items-center gap-3 border rounded p-3">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $indicator['variant'] ?? 'primary' }}-subtle text-{{ $indicator['variant'] ?? 'primary' }} p-3">
                                                <i class="{{ $indicator['icon'] ?? 'ri-information-line' }} fs-5"></i>
                                            </span>
                                            <div>
                                                <p class="text-muted small mb-1">{{ $indicator['label'] ?? 'Indicador' }}</p>
                                                <h6 class="text-color fw-semibold mb-0">{{ $indicator['value'] ?? '—' }}</h6>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Nenhum indicador disponível.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase small text-muted mb-3">Revalidações próximas</h6>
                                <div class="d-flex flex-column gap-3">
                                    @forelse ($upcomingRenewals as $renewal)
                                        <div class="border rounded p-3 d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <h6 class="text-color fw-semibold mb-1">{{ $renewal['patient'] ?? 'Paciente' }}</h6>
                                                <p class="text-muted small mb-0">Tutor: {{ $renewal['tutor'] ?? '—' }}</p>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary-subtle text-primary mb-1">{{ $renewal['date'] ?? '—' }}</span>
                                                <p class="text-muted small mb-0">{{ $renewal['status'] ?? '—' }}</p>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Nenhuma revalidação programada.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase small text-muted mb-3">Níveis de estoque</h6>
                                <div class="d-flex flex-column gap-3">
                                    @forelse ($supplyLevels as $supply)
                                        <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-color fw-semibold mb-1">{{ $supply['label'] ?? 'Item' }}</h6>
                                                <p class="text-muted small mb-0">Reposição: {{ $supply['next_restock'] ?? '—' }}</p>
                                            </div>
                                            <span class="badge bg-{{ $supply['status_color'] ?? 'primary' }}-subtle text-{{ $supply['status_color'] ?? 'primary' }}">{{ $supply['status'] ?? '—' }}</span>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Nenhum item monitorado.</span>
                                    @endforelse
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