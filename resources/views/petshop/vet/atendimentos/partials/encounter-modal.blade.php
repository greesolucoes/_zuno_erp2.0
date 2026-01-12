@php
    $modalId = $modalId
        ?? ('vet-encounter-modal-' . \Illuminate\Support\Str::slug($encounter['code'] ?? uniqid()));
@endphp

@php
    $resolvedStatusColor = $statusColor ?? $encounter['status_color'] ?? 'primary';
    if ($resolvedStatusColor === '') {
        $resolvedStatusColor = 'primary';
    }
    $checklistByStatus = [
        'danger' => [
            'Finalizar prescrição pendente e anexar exames ao prontuário.',
            'Confirmar contato com o tutor para alinhar retorno e orientações de cuidado.',
            'Atualizar evolução clínica no prontuário antes do encerramento.',
        ],
        'warning' => [
            'Registrar sinais vitais e evolução do paciente na última hora.',
            'Validar se medicações de suporte foram administradas no horário correto.',
            'Checar se há exames aguardando laudo e informar equipe responsável.',
        ],
        'success' => [
            'Garantir que o relatório de alta esteja anexado ao prontuário.',
            'Confirmar orientações entregues ao tutor e agendamento de retorno.',
        ],
        'primary' => [
            'Verificar documentação necessária para início do atendimento.',
            'Atualizar observações iniciais e checklist de abertura.',
        ],
    ];
    $checklistItems = $checklistByStatus[$statusColor] ?? $checklistByStatus['primary'];
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title text-color fw-bold mb-1">{{ $encounter['patient'] ?? 'Paciente' }}</h5>
                    <p class="text-muted small mb-0">{{ $encounter['service'] ?? 'Serviço não informado' }} • {{ $encounter['code'] ?? 'Código não informado' }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="row g-4">
                    <div class="col-12 col-xl-5">
                        <div class="vet-atendimentos__detail-card p-4 h-100">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                                <span class="badge bg-light text-muted">Código {{ $encounter['code'] ?? '—' }}</span>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <p class="text-muted small mb-1">Horário agendado</p>
                                    <span class="fw-semibold text-color">
                                        @if (!empty($encounter['start']))
                                            {{ \Illuminate\Support\Carbon::parse($encounter['start'])->format('d/m/Y \à\s H:i') }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-1">Veterinário responsável</p>
                                    <span class="fw-semibold text-color">{{ $encounter['veterinarian'] ?? '—' }}</span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-1">Local de atendimento</p>
                                    <span class="fw-semibold text-color">{{ $encounter['room'] ?? '—' }}</span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-1">Tutor</p>
                                    <span class="fw-semibold text-color">{{ $encounter['tutor'] ?? '—' }}</span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-1">Espécie</p>
                                    <span class="fw-semibold text-color">{{ $encounter['species'] ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-7">
                        <div class="vet-atendimentos__detail-card p-4 h-100">
                            <div class="mb-4">
                                <p class="text-uppercase text-muted small fw-semibold mb-2">Observações registradas</p>
                                <p class="text-color mb-0">{{ $encounter['notes'] ?? 'Sem observações registradas.' }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-uppercase text-muted small fw-semibold mb-2">Checklist assistencial</p>
                                <div class="d-flex flex-column gap-2">
                                    
                                </div>
                            </div>

                            <div>
                                <p class="text-uppercase text-muted small fw-semibold mb-2">Pontos de atenção</p>
                                <div class="d-flex flex-wrap gap-2">
                                  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary">
                    <i class="ri-share-forward-line me-1"></i>
                    Enviar atualização ao tutor
                </button>
            </div>
        </div>
    </div>
</div>