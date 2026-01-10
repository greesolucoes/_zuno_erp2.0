@props([
    'modalId',
    'exam',
    'documents' => [],
])

@php
    $share = $exam['share'] ?? ['message' => null, 'attachments' => [], 'fallback_url' => null];
    $shareAttachments = $share['attachments'] ?? [];
    $shareMessage = $share['message'] ?? '';
    $shareFallbackUrl = $share['fallback_url'] ?? null;
@endphp

<div
    class="modal fade"
    id="{{ $modalId }}"
    tabindex="-1"
    aria-hidden="true"
    data-exam-document-modal
    data-zoom-modal="{{ $modalId }}Zoom"
    data-share-payload='@json($shareAttachments)'
    data-share-message="{{ rawurlencode($shareMessage ?? '') }}"
    data-share-fallback-url="{{ $shareFallbackUrl }}"
>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Documentos do exame · {{ $exam['type'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row gy-4">
                    <div class="col-lg-4">
                        <div class="vet-exams__preview p-3 rounded border h-100 d-flex flex-column">
                            <div class="mb-3">
                                <h6 class="mb-1">Paciente</h6>
                                <p class="mb-0 text-muted small">{{ $exam['animal'] }} · Tutor {{ $exam['guardian'] }}</p>
                            </div>
                            @if (!empty($exam['attendance']))
                                <div class="mb-3">
                                    <h6 class="mb-1">Atendimento associado</h6>
                                    <p class="mb-0 text-muted small">
                                        <a href="{{ $exam['attendance']['url'] ?? '#' }}" class="text-decoration-none">
                                            {{ $exam['attendance']['code'] ?? 'Atendimento' }}
                                        </a>
                                        @if (!empty($exam['attendance']['status']))
                                            · {{ $exam['attendance']['status'] }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                            <div class="mb-3">
                                <h6 class="mb-1">Resumo</h6>
                                <p class="text-muted small mb-0">{{ $exam['findings'] }}</p>
                            </div>
                            <div class="mt-auto">
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm w-100 mb-2"
                                    data-action="zoom"
                                    disabled
                                    aria-disabled="true"
                                >
                                    <i class="mdi mdi-magnify-plus-outline me-1"></i> Aproximar
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm w-100"
                                    data-action="open-new-tab"
                                    disabled
                                    aria-disabled="true"
                                >
                                    <i class="mdi mdi-open-in-new me-1"></i> Abrir em nova guia
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="vet-exams__documents-grid">
                            @forelse ($documents as $document)
                                <div
                                    class="vet-exams__document-card p-3 border rounded position-relative"
                                    data-document-card
                                    data-document-url="{{ $document['url'] ?? '' }}"
                                    data-document-download-url="{{ $document['download_url'] ?? ($document['url'] ?? '') }}"
                                    role="button"
                                    tabindex="0"
                                >
                                    @if (!empty($document['context_label']))
                                        <span class="badge bg-light text-muted position-absolute top-0 start-0 m-2">
                                            {{ $document['context_label'] }}
                                        </span>
                                    @endif
                                    @if (!empty($document['size']))
                                        <span class="badge bg-light text-dark position-absolute top-0 end-0 m-2">{{ $document['size'] }}</span>
                                    @endif
                                    <h6 class="mb-1">{{ $document['name'] }}</h6>
                                    <p class="mb-3 text-muted small">Enviado em {{ $document['uploaded_at'] ?? '--' }}</p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ $document['url'] }}" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-eye-outline me-1"></i> Visualizar
                                        </a>
                                        <a
                                            href="{{ $document['download_url'] ?? ($document['url'] ?? '#') }}"
                                            class="btn btn-sm btn-soft-secondary"
                                            target="_blank"
                                            rel="noopener"
                                            @if (!empty($document['download_url'])) download @endif
                                        >
                                            <i class="mdi mdi-download-outline me-1"></i> Baixar
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">Nenhum documento anexado para este exame.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                @if (!empty($shareAttachments))
                    <button type="button" class="btn btn-primary" data-action="share">
                        <span data-share-button-label>Compartilhar via WhatsApp</span>
                        <span
                            class="spinner-border spinner-border-sm align-middle ms-2 d-none"
                            role="status"
                            aria-hidden="true"
                            data-share-button-spinner
                        ></span>
                    </button>
                @else
                    <button type="button" class="btn btn-primary" disabled aria-disabled="true">Compartilhar via WhatsApp</button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="{{ $modalId }}Zoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visualizar documento do exame</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe
                    data-zoom-frame
                    class="w-100 border-0"
                    style="min-height: 75vh;"
                    title="Visualização do documento do exame"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </div>
</div>