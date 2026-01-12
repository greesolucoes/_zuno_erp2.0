@php
    if (!isset($modalId)) {
        $modalId = 'vet-record-modal-' . \Illuminate\Support\Str::slug($record['code'] ?? uniqid());
    }
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <div>
                    <span class="badge vet-prontuarios__badge-soft-{{ $record['status_color'] ?? 'primary' }} mb-2">
                        {{ $record['status'] ?? 'Sem status' }}
                    </span>
                    <h4 class="modal-title fw-semibold text-color mb-1" id="{{ $modalId }}-label">{{ $record['patient'] ?? 'Paciente' }}</h4>
                    <p class="text-muted mb-0">
                        {{ $record['species'] ?? '—' }}
                        @if(!empty($record['breed']))
                            • {{ $record['breed'] }}
                        @endif
                        @if(!empty($record['age']))
                            • {{ $record['age'] }}
                        @endif
                        @if(!empty($record['code']))
                            • Código: {{ $record['code'] }}
                        @endif
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge vet-prontuarios__badge-outline-{{ $record['type_color'] ?? 'info' }} mb-2">
                        {{ $record['type'] ?? 'Tipo não informado' }}
                    </span>
                    <p class="text-muted small mb-0">Última atualização em <strong>{{ $record['updated_at'] ?? '—' }}</strong></p>
                    @if(!empty($record['clinic_room']))
                        <p class="text-muted small mb-0">Local: {{ $record['clinic_room'] }}</p>
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @forelse ($record['tags'] ?? [] as $tag)
                        <span class="vet-prontuarios__tag">{{ $tag }}</span>
                    @empty
                        <span class="text-muted small">Nenhuma etiqueta cadastrada.</span>
                    @endforelse
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-4 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-file-list-2-line me-2"></i>Informações gerais</h6>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="d-flex justify-content-between"><span class="text-muted">Tutor</span><span class="fw-semibold text-color text-end">{{ $record['tutor'] ?? '—' }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Contato</span><span class="text-color text-end">{{ $record['contact'] ?? '—' }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Responsável</span><span class="fw-semibold text-color text-end">{{ $record['veterinarian'] ?? '—' }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Equipe</span><span class="text-color text-end">{{ $record['team'] ?? '—' }}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Tipo / Local</span><span class="text-color text-end">{{ collect(array_filter([$record['type'] ?? null, $record['clinic_room'] ?? null]))->implode(' • ') ?: '—' }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-4 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-heart-pulse-line me-2"></i>Resumo clínico</h6>
                            <p class="text-muted small mb-0">{{ $record['summary'] ?? 'Sem informações clínicas registradas.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-4 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-bar-chart-box-line me-2"></i>Indicadores assistenciais</h6>
                            <div class="row g-3">
                                @forelse ($record['metrics'] ?? [] as $metric)
                                    <div class="col-12 col-sm-6">
                                        <div class="vet-prontuarios__metric-card h-100">
                                            <span class="vet-prontuarios__metric-icon">
                                                <i class="{{ $metric['icon'] ?? 'ri-pulse-line' }}"></i>
                                            </span>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">{{ $metric['label'] ?? 'Indicador' }}</span>
                                                <span class="fw-semibold text-color">{{ $metric['value'] ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <span class="text-muted small">Nenhum indicador disponível.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-4 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-stethoscope-line me-2"></i>Sinais vitais</h6>
                            <div class="row g-3">
                                @forelse ($record['vital_signs'] ?? [] as $vital)
                                    <div class="col-12 col-sm-6">
                                        <div class="vet-prontuarios__vitals-card h-100">
                                            <span class="text-muted small">{{ $vital['label'] ?? 'Sinal vital' }}</span>
                                            <div class="fw-semibold text-color">{{ $vital['value'] ?? '—' }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <span class="text-muted small">Sem sinais vitais registrados.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-4 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-task-line me-2"></i>Próximos passos</h6>
                            <ul class="list-group list-group-flush small mb-0">
                                @forelse ($record['next_steps'] ?? [] as $step)
                                    <li class="list-group-item px-0">{{ $step }}</li>
                                @empty
                                    <li class="list-group-item px-0 text-muted">Nenhuma pendência registrada.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="p-3 border rounded-4 h-100">
                            <h6 class="fw-semibold text-color mb-3"><i class="ri-time-line me-2"></i>Linha do tempo</h6>
                            <div class="position-relative">
                                @if (!empty($record['timeline']))
                                    <div class="vet-prontuarios__timeline d-flex flex-column gap-3">
                                        @foreach ($record['timeline'] as $event)
                                            <div class="vet-prontuarios__timeline-item">
                                                <span class="vet-prontuarios__timeline-time">{{ $event['time'] ?? '—' }}</span>
                                                <h6 class="text-color fw-semibold mb-1">{{ $event['title'] ?? 'Evento' }}</h6>
                                                <p class="text-muted small mb-0">{{ $event['description'] ?? 'Sem descrição disponível.' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted small mb-0">Nenhum evento registrado.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-light">
                        <i class="ri-printer-line me-1"></i>
                        Imprimir prontuário
                    </button>
                    <button type="button" class="btn btn-outline-primary">
                        <i class="ri-edit-line me-1"></i>
                        Atualizar informações
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>