@php($modalId = $modalId ?? ('vet-hosp-modal-' . ($hospitalization->id ?? uniqid())))
@php($patient = $hospitalization->animal)
@php($tutor = $patient?->cliente)
@php($attendance = $hospitalization->attendance)
@php($veterinarian = $hospitalization->veterinarian?->funcionario)
@php($colorMap = [
    'primary' => 'primary',
    'success' => 'success',
    'warning' => 'warning',
    'danger' => 'danger',
    'secondary' => 'secondary',
    'info' => 'info',
])
@php($statusVariant = $colorMap[$hospitalization->status_color] ?? 'primary')
@php($riskVariant = $colorMap[$hospitalization->risk_color] ?? 'secondary')

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $patient?->nome ?? 'Paciente' }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-3" style="overflow-x: hidden">
                <p class="text-color fw-semibold small mb-3">
                    {{ $patient?->especie?->nome ?? 'Espécie não informada' }}
                    @if ($patient?->raca?->nome)
                        • {{ $patient->raca->nome }}
                    @endif
                    @if ($patient?->peso)
                        • {{ number_format((float) $patient->peso, 2, ',', '.') }} kg
                    @endif
                </p>
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge text-bg-{{ $statusVariant }}">{{ $hospitalization->status_label }}</span>
                                    <span class="badge text-bg-{{ $riskVariant }}">{{ $hospitalization->risk_label }}</span>
                                </div>
                                <dl class="row mb-0">
                                    <dt class="col-5 text-muted small">Admissão</dt>
                                    <dd class="col-7 text-color fw-semibold">{{ optional($hospitalization->internado_em)->format('d/m/Y H:i') ?? '—' }}</dd>
                                    <dt class="col-5 text-muted small">Previsão de alta</dt>
                                    <dd class="col-7 text-color">{{ optional($hospitalization->previsao_alta_em)->format('d/m/Y') ?? '—' }}</dd>
                                    <dt class="col-5 text-muted small">Unidade</dt>
                                    <dd class="col-7 text-color">
                                        {{ $hospitalization->room?->nome ?? '—' }}
                                        @if ($hospitalization->room?->tipo)
                                            <span class="text-muted">• {{ $hospitalization->room->tipo }}</span>
                                        @endif
                                    </dd>
                                    <dt class="col-5 text-muted small">Profissional</dt>
                                    <dd class="col-7 text-color">
                                        {{ $veterinarian?->nome ?? 'Não informado' }}
                                        @if ($hospitalization->veterinarian?->especialidade)
                                            <span class="text-muted">• {{ $hospitalization->veterinarian->especialidade }}</span>
                                        @endif
                                    </dd>
                                    @if ($attendance)
                                        <dt class="col-5 text-muted small">Atendimento</dt>
                                        <dd class="col-7 text-color">
                                            <a href="{{ route('vet.atendimentos.history', $attendance->id) }}" class="text-decoration-none">
                                                {{ $attendance->codigo ?? ('ATD-' . str_pad($attendance->id, 6, '0', STR_PAD_LEFT)) }}
                                            </a>
                                        </dd>
                                    @endif
                                </dl>
                                <hr class="my-4">
                                <p class="text-uppercase text-muted small fw-semibold mb-2">Motivo da internação</p>
                                <p class="text-color mb-4">{{ $hospitalization->motivo ?? 'Não informado.' }}</p>
                                <p class="text-uppercase text-muted small fw-semibold mb-2">Observações</p>
                                <p class="text-muted mb-0">{{ $hospitalization->observacoes ?? 'Nenhuma observação registrada.' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-semibold text-color mb-2">Tutor responsável</h6>
                                <p class="mb-1">{{ $tutor->razao_social ?? $tutor->nome_fantasia ?? $tutor->contato ?? 'Não informado' }}</p>
                                <p class="text-muted small mb-2">
                                    @if ($tutor?->cpf_cnpj)
                                        Documento: {{ $tutor->cpf_cnpj }}
                                    @else
                                        Documento não informado
                                    @endif
                                </p>
                                <ul class="list-unstyled text-muted small mb-4">
                                    @if ($tutor?->telefone)
                                        <li><i class="ri-phone-line me-1"></i>{{ $tutor->telefone }}</li>
                                    @endif
                                    @if ($tutor?->telefone_secundario)
                                        <li><i class="ri-phone-line me-1"></i>{{ $tutor->telefone_secundario }}</li>
                                    @endif
                                    @if ($tutor?->telefone_terciario)
                                        <li><i class="ri-phone-line me-1"></i>{{ $tutor->telefone_terciario }}</li>
                                    @endif
                                    @if ($tutor?->contato)
                                        <li><i class="ri-user-3-line me-1"></i>{{ $tutor->contato }}</li>
                                    @endif
                                    @if ($tutor?->email)
                                        <li><i class="ri-mail-line me-1"></i>{{ $tutor->email }}</li>
                                    @endif
                                </ul>
                                <h6 class="fw-semibold text-color mb-2">Histórico clínico</h6>
                                <p class="text-muted small mb-0">
                                    Última atualização em {{ optional($hospitalization->updated_at)->format('d/m/Y H:i') ?? '—' }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
