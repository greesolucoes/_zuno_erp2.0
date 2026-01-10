<div class="modal fade" id="vet-records-overview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title text-color fw-bold mb-1">Visão geral dos prontuários</h5>
                    <p class="text-muted small mb-0">Resumo assistencial com indicadores e atualizações recentes.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="row g-3 mb-4">
                    @foreach ($summary as $item)
                        <div class="col-12 col-sm-6">
                            <div class="vet-prontuarios__summary-card p-4 h-100">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <span class="vet-prontuarios__summary-icon bg-{{ $item['variant'] ?? 'primary' }}-subtle text-{{ $item['variant'] ?? 'primary' }}">
                                        <i class="{{ $item['icon'] ?? 'ri-bar-chart-line' }}"></i>
                                    </span>
                                    <i class="ri-pulse-line text-muted"></i>
                                </div>
                                <div class="vet-prontuarios__summary-value">{{ $item['value'] ?? '—' }}</div>
                                <p class="text-muted mb-0 fw-semibold">{{ $item['label'] ?? 'Indicador' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="vet-prontuarios__detail-card p-4">
                    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start mb-4">
                        <div>
                            <h6 class="fw-semibold text-color mb-1">Atualizações da equipe</h6>
                            <p class="text-muted small mb-0">Movimentações recentes registradas nos prontuários ativos.</p>
                        </div>
                        <span class="vet-prontuarios__badge-outline-info">
                            <i class="ri-time-line me-1"></i>
                            Últimos 7 dias
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-7">
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
                                    <span class="text-muted small">Nenhuma atualização recente registrada.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <div class="p-3 border rounded-4 h-100 bg-light">
                                <h6 class="fw-semibold text-color mb-3"><i class="ri-bar-chart-2-line me-2"></i>Destaques dos indicadores</h6>
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($summary as $item)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small">{{ $item['label'] ?? 'Indicador' }}</span>
                                            <span class="fw-semibold text-color">{{ $item['value'] ?? '—' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>