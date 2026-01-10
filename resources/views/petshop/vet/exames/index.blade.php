@extends('default.layout', ['title' => 'Histórico de Exames'])

@section('content')
    <x-table
        :data="$exams"
        :table_headers="[
            ['label' => 'Exame', 'width' => '18%', 'align' => 'left'],
            ['label' => 'Paciente / Tutor', 'width' => '18%', 'align' => 'left'],
            ['label' => 'Laboratório', 'width' => '18%', 'align' => 'left'],
            ['label' => 'Veterinário Responsável', 'width' => '18%', 'align' => 'left'],
            ['label' => 'Status', 'width' => '10%'],
            ['label' => 'Solicitado em', 'width' => '9%'],
            ['label' => 'Concluído em', 'width' => '9%'],
        ]"
        :pagination="false"
        :modal_actions="false"
    >
        <x-slot name="title" class="text-color">Histórico de Exames</x-slot>

        <x-slot name="buttons">
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('vet.exams.types') }}" class="btn btn-light">
                    <i class="ri-file-list-3-line"></i>
                    Tipos de Exame
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
            <input type="hidden" name="attendance" value="{{ request('attendance') }}">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4">
                    {!! Form::text('search', 'Buscar por paciente, tutor ou laboratório')
                        ->placeholder('Digite para pesquisar')
                        ->attrs(['class' => 'ignore']) !!}
                </div>
                <div class="col-sm-6 col-lg-3">
                    {!! Form::select('status', 'Status', collect($filters['status'])->pluck('label', 'value')->toArray())
                        ->attrs(['class' => 'form-select ignore']) !!}
                </div>
                <div class="col-sm-6 col-lg-3">
                    {!! Form::select('timeframe', 'Período', collect($filters['timeframes'])->pluck('label', 'value')->toArray())
                        ->attrs(['class' => 'form-select ignore']) !!}
                </div>
                <div class="col-sm-12 col-lg-2 d-flex align-items-end gap-2 mt-2 mt-lg-0 flex-wrap">
                    <button class="btn btn-primary flex-fill" type="submit">
                        <i class="ri-search-line"></i>
                        Pesquisar
                    </button>
                    <a id="clear-filter" class="btn btn-danger flex-fill" href="{{ route('vet.exams.index') }}">
                        <i class="ri-eraser-fill"></i>
                        Limpar
                    </a>
                </div>
            </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($exams as $exam)
            @include('components.petshop.vet.exames._table_row', ['exam' => $exam])
        @endforeach
    </x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
    <script>
        (function () {
            const MODAL_SELECTOR = '[data-exam-document-modal]';
            const attachmentBlobCache = new Map();

            function getBootstrapModalConstructor() {
                if (window.bootstrap?.Modal) {
                    return window.bootstrap.Modal;
                }

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    return bootstrap.Modal;
                }

                return null;
            }

            function selectCard(modal, card) {
                const activeClassList = ['border-primary', 'shadow-sm'];
                const cards = modal.querySelectorAll('[data-document-card]');
                cards.forEach((item) => {
                    activeClassList.forEach((className) => item.classList.remove(className));
                });

                if (!card) {
                    updateActionButtons(modal, null);
                    return;
                }

                activeClassList.forEach((className) => card.classList.add(className));
                updateActionButtons(modal, card);
            }

            function updateActionButtons(modal, card) {
                const zoomButton = modal.querySelector('[data-action="zoom"]');
                const newTabButton = modal.querySelector('[data-action="open-new-tab"]');
                const hasDocument = Boolean(card && card.dataset.documentUrl);

                [zoomButton, newTabButton].forEach((button) => {
                    if (!button) {
                        return;
                    }

                    button.disabled = !hasDocument;
                    button.setAttribute('aria-disabled', hasDocument ? 'false' : 'true');
                });
            }

            function parseSharePayload(modal) {
                const payload = modal.getAttribute('data-share-payload');

                if (!payload) {
                    return [];
                }

                try {
                    const parsed = JSON.parse(payload);

                    if (Array.isArray(parsed)) {
                        return parsed.filter((item) => item && item.download_url);
                    }

                    return [];
                } catch (error) {
                    console.error('Erro ao processar anexos para compartilhamento.', error);

                    return [];
                }
            }

            function decodeShareMessage(modal) {
                const encoded = modal.getAttribute('data-share-message');

                if (!encoded) {
                    return '';
                }

                try {
                    return decodeURIComponent(encoded);
                } catch (error) {
                    console.error('Erro ao decodificar mensagem de compartilhamento.', error);

                    return '';
                }
            }

            function setShareButtonLoading(button, isLoading) {
                if (!button) {
                    return;
                }

                const spinner = button.querySelector('[data-share-button-spinner]');
                const label = button.querySelector('[data-share-button-label]');
                const defaultLabel = button.dataset.shareDefaultLabel || 'Compartilhar via WhatsApp';

                if (!button.dataset.shareDefaultLabel && label) {
                    button.dataset.shareDefaultLabel = label.textContent?.trim() || defaultLabel;
                }

                if (isLoading) {
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                    if (spinner) {
                        spinner.classList.remove('d-none');
                    }
                    if (label) {
                        label.textContent = 'Preparando documentos...';
                    }
                } else {
                    button.disabled = false;
                    button.setAttribute('aria-disabled', 'false');
                    if (spinner) {
                        spinner.classList.add('d-none');
                    }
                    if (label) {
                        label.textContent = button.dataset.shareDefaultLabel || defaultLabel;
                    }
                }
            }

            async function downloadAttachment(attachment) {
                const cacheKey = attachment.download_url;

                if (!cacheKey) {
                    throw new Error('O anexo não possui uma URL para download.');
                }

                if (attachmentBlobCache.has(cacheKey)) {
                    const cached = attachmentBlobCache.get(cacheKey);
                    return new File([cached.blob], cached.fileName, { type: cached.type });
                }

                const response = await fetch(cacheKey, {
                    credentials: 'include',
                });

                if (!response.ok) {
                    throw new Error(`Falha ao baixar o anexo: ${attachment.name || 'Documento'}`);
                }

                const blob = await response.blob();
                const type = blob.type || attachment.mime_type || 'application/octet-stream';
                const fileName = attachment.file_name || attachment.name || 'documento';

                attachmentBlobCache.set(cacheKey, { blob, type, fileName });

                return new File([blob], fileName, { type });
            }

            function showShareError(message, error) {
                console.error(message, error);
                window.alert(message);
            }

            async function shareDocuments(modal, button) {
                const attachments = parseSharePayload(modal);

                if (attachments.length === 0) {
                    showShareError('Nenhum documento disponível para compartilhar.', null);
                    return;
                }

                if (typeof window.File !== 'function' || !navigator?.share) {
                    showShareError('Este dispositivo não suporta o compartilhamento direto de arquivos. Baixe os documentos e envie manualmente pelo WhatsApp.', null);
                    return;
                }

                const message = decodeShareMessage(modal);

                setShareButtonLoading(button, true);

                try {
                    const files = [];

                    for (const attachment of attachments) {
                        const file = await downloadAttachment(attachment);
                        files.push(file);
                    }

                    const shareData = { files };

                    if (message) {
                        shareData.text = message;
                    }

                    if (navigator.canShare && !navigator.canShare(shareData)) {
                        throw new Error('Este dispositivo não permite compartilhar os documentos selecionados.');
                    }

                    await navigator.share(shareData);
                } catch (error) {
                    if (error instanceof DOMException && (error.name === 'AbortError' || error.name === 'NotAllowedError')) {
                        return;
                    }

                    const fallbackUrl = modal.getAttribute('data-share-fallback-url');

                    if (fallbackUrl) {
                        console.warn('Falha ao compartilhar documentos. URL de contingência disponível.', fallbackUrl, error);
                    }

                    showShareError('Não foi possível compartilhar os documentos automaticamente neste dispositivo. Baixe os arquivos e envie manualmente pelo WhatsApp.', error);
                } finally {
                    setShareButtonLoading(button, false);
                }
            }

            function initModal(modal) {
                const cards = modal.querySelectorAll('[data-document-card]');
                const zoomButton = modal.querySelector('[data-action="zoom"]');
                const newTabButton = modal.querySelector('[data-action="open-new-tab"]');
                const shareButton = modal.querySelector('[data-action="share"]');
                const zoomModalId = modal.getAttribute('data-zoom-modal');
                const zoomModal = zoomModalId ? document.getElementById(zoomModalId) : null;
                const zoomFrame = zoomModal ? zoomModal.querySelector('[data-zoom-frame]') : null;
                const BootstrapModal = getBootstrapModalConstructor();
                let selectedCard = null;
                let isSharing = false;

                function handleSelect(card) {
                    selectedCard = card;
                    selectCard(modal, selectedCard);
                }

                cards.forEach((card) => {
                    card.addEventListener('click', () => {
                        handleSelect(card);
                    });

                    card.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            handleSelect(card);
                        }
                    });
                });

                if (zoomButton) {
                    zoomButton.addEventListener('click', () => {
                        if (!selectedCard) {
                            return;
                        }

                        const documentUrl = selectedCard.dataset.documentUrl;
                        if (!documentUrl) {
                            return;
                        }

                        if (zoomModal && zoomFrame && BootstrapModal) {
                            zoomFrame.src = documentUrl;
                            BootstrapModal.getOrCreateInstance(zoomModal).show();
                        } else {
                            window.open(documentUrl, '_blank', 'noopener');
                        }
                    });
                }

                if (zoomModal && zoomFrame && BootstrapModal) {
                    zoomModal.addEventListener('hidden.bs.modal', () => {
                        zoomFrame.src = '';
                    });
                }

                if (newTabButton) {
                    newTabButton.addEventListener('click', () => {
                        if (!selectedCard) {
                            return;
                        }

                        const targetUrl = selectedCard.dataset.documentDownloadUrl || selectedCard.dataset.documentUrl;
                        if (!targetUrl) {
                            return;
                        }

                        window.open(targetUrl, '_blank', 'noopener');
                    });
                }

                if (shareButton) {
                    shareButton.addEventListener('click', async () => {
                        if (isSharing) {
                            return;
                        }

                        isSharing = true;
                        try {
                            await shareDocuments(modal, shareButton);
                        } finally {
                            isSharing = false;
                        }
                    });
                }

                modal.addEventListener('shown.bs.modal', () => {
                    selectedCard = cards.length > 0 ? cards[0] : null;
                    selectCard(modal, selectedCard);
                });

                modal.addEventListener('hidden.bs.modal', () => {
                    selectedCard = null;
                    selectCard(modal, null);
                    if (zoomFrame) {
                        zoomFrame.src = '';
                    }
                    if (shareButton) {
                        setShareButtonLoading(shareButton, false);
                    }
                });

                if (cards.length > 0) {
                    handleSelect(cards[0]);
                } else {
                    updateActionButtons(modal, null);
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll(MODAL_SELECTOR).forEach((modal) => {
                    initModal(modal);
                });
            });
        })();
    </script>
@endsection