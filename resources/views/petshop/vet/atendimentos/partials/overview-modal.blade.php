@php($modalId = $modalId ?? 'vet-appointments-overview-modal')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}-label">Visão geral dos atendimentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    @foreach ($summary as $card)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $card['variant'] ?? 'primary' }} text-white" style="width: 40px; height: 40px;">
                                            <i class="{{ $card['icon'] ?? 'ri-information-line' }}"></i>
                                        </span>
                                        <span class="badge bg-{{ $card['variant'] ?? 'primary' }}">Hoje</span>
                                    </div>
                                    <p class="text-muted text-uppercase small mb-1">{{ $card['label'] ?? 'Indicador' }}</p>
                                    <h4 class="text-color mb-0">{{ $card['value'] ?? '—' }}</h4>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-3">
                    <div class="col-12 col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small mb-3">Próximos atendimentos</h6>
                                <div class="d-flex flex-column gap-3">
                                    @forelse ($upcomingEncounters as $appointment)
                                        <div class="border rounded p-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width: 36px; height: 36px;">
                                                    <i class="ri-calendar-check-line"></i>
                                                </span>
                                                <div class="flex-grow-1">
                                                    <h6 class="text-color fw-semibold mb-0">{{ $appointment['patient'] ?? 'Paciente' }}</h6>
                                                    <p class="text-muted small mb-1">
                                                        @if (!empty($appointment['start']))
                                                            {{ \Illuminate\Support\Carbon::parse($appointment['start'])->format('d/m \à\s H:i') }}
                                                        @else
                                                            Horário não informado
                                                        @endif
                                                        • {{ $appointment['service'] ?? 'Serviço' }}
                                                    </p>
                                                    <span class="badge bg-{{ $appointment['status_color'] ?? 'primary' }}">{{ $appointment['status'] ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">Nenhum atendimento programado.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small mb-3">Distribuição por status</h6>
                                <div class="d-flex flex-column gap-2">
                                    @forelse ($statusBreakdown as $status)
                                        <div class="d-flex align-items-center justify-content-between border rounded p-3">
                                            <div>
                                                <p class="text-muted small mb-1">{{ $status['status'] ?? 'Status' }}</p>
                                                <h5 class="text-color fw-semibold mb-0">{{ $status['count'] ?? 0 }}</h5>
                                            </div>
                                            <span class="badge bg-{{ $status['status_color'] ?? 'primary' }}">{{ $status['status'] ?? 'Status' }}</span>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">Nenhum atendimento registrado.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small mb-3">Fluxo recomendado</h6>
                                <div class="list-group list-group-flush">
                                    @forelse ($timeline as $step)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-primary border" style="width: 36px; height: 36px;">
                                                    <i class="{{ $step['icon'] ?? 'ri-information-line' }}"></i>
                                                </span>
                                                <div>
                                                    <h6 class="text-color fw-semibold mb-1">{{ $step['title'] ?? 'Etapa' }}</h6>
                                                    <p class="text-muted small mb-1">{{ $step['description'] ?? 'Descrição não informada.' }}</p>
                                                    <span class="badge bg-light text-muted">{{ $step['time'] ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted small mb-0">Nenhuma etapa definida.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>