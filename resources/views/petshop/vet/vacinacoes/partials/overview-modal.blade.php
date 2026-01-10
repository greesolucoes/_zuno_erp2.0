@php($modalId = $modalId ?? 'vet-vacc-overview-modal')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-semibold text-color" id="{{ $modalId }}-label">Visão geral das vacinações</h5>
                    <p class="text-muted mb-0">Resumo de indicadores, status por estágio e próximas aplicações planejadas.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="row g-3 mb-4">
                    @foreach ($summary as $card)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="vet-vacinacoes__summary-card p-3 h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="vet-vacinacoes__summary-icon vet-vacinacoes__badge-soft-{{ $card['variant'] ?? 'primary' }}">
                                        <i class="{{ $card['icon'] ?? 'ri-information-line' }}"></i>
                                    </span>
                                    <div>
                                        <h6 class="text-muted text-uppercase small mb-1">{{ $card['label'] ?? 'Indicador' }}</h6>
                                        <span class="fs-4 fw-semibold text-color">{{ $card['value'] ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="vet-vacinacoes__detail-card p-4 h-100">
                            <h6 class="text-uppercase small text-muted mb-3">Status por andamento</h6>
                            <div class="d-flex flex-column gap-3">
                                @forelse ($statusBreakdown as $status => $count)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold text-color">{{ $status }}</span>
                                        <span class="badge bg-primary-subtle text-primary">{{ $count }} registros</span>
                                    </div>
                                @empty
                                    <span class="text-muted small">Nenhum agendamento registrado.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="vet-vacinacoes__detail-card p-4 h-100">
                            <h6 class="text-uppercase small text-muted mb-3">Próximas vacinações</h6>
                            <div class="d-flex flex-column gap-3">
                                @forelse ($upcomingVaccinations as $vaccination)
                                    <div class="border rounded-3 p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h5 class="mb-0 fw-semibold text-color">{{ $vaccination['patient'] ?? 'Paciente' }}</h5>
                                            <span class="vet-vacinacoes__status-badge bg-{{ $vaccination['status_color'] ?? 'light' }}-subtle text-{{ $vaccination['status_color'] ?? 'muted' }}">
                                                {{ $vaccination['status'] ?? '—' }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            {{ data_get($vaccination, 'vaccine.name') }} • {{ $vaccination['scheduled_at'] ?? 'Sem data' }}
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($vaccination['tags'] ?? [] as $tag)
                                                <span class="vet-vacinacoes__tag"><i class="ri-price-tag-3-line"></i>{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-muted small">Nenhuma vacinação programada.</span>
                                @endforelse
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