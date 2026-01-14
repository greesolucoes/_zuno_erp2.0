@extends('default.layout', ['title' => 'Coleta de exame'])

@section('css')
    <style>
        .vet-exams-collection {
            position: relative;
        }

        .vet-exams-collection__badge {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.75rem;
        }

        .vet-exams-collection__section-card {
            border-radius: 18px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 14px 32px rgba(22, 22, 107, 0.08);
        }

        .vet-exams-collection__step-badge {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: rgba(40, 199, 111, 0.12);
            color: #28c76f;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .vet-exams-collection__readonly-input:disabled,
        .vet-exams-collection__readonly-input[readonly] {
            background-color: #f5f6fa;
            color: #6c757d;
            border-color: rgba(22, 22, 107, 0.08);
            cursor: not-allowed;
        }

        .vet-exams-collection__upload-area {
            border: 2px dashed rgba(22, 22, 107, 0.15);
            border-radius: 14px;
            background: rgba(40, 199, 111, 0.05);
        }

        .vet-exams-collection__upload-area.is-dragging {
            border-color: rgba(40, 199, 111, 0.6);
            background: rgba(40, 199, 111, 0.12);
        }

        .vet-exams-collection__upload-files {
            text-align: left;
        }

        .vet-exams-collection__upload-files li {
            margin-bottom: 0.25rem;
        }

        .vet-exams-collection__documents-list .vet-exams-collection__document-card {
            border-radius: 12px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background-color: #fdfefe;
        }
    </style>
@endsection

@section('js')
    <script>
        (function ($) {
            'use strict';

            $(function () {
                var $uploadArea = $('#vetExamCollectionUploadArea');
                var $attachmentInput = $('#vetExamCollectionAttachments');
                var $attachmentSelectLink = $('#vetExamCollectionAttachmentSelect');
                var $attachmentPreview = $('#vetExamCollectionAttachmentsPreview');
                var attachmentFiles = [];
                var attachmentDataTransfer = (function () {
                    if (typeof DataTransfer === 'undefined') {
                        return null;
                    }

                    try {
                        return new DataTransfer();
                    } catch (error) {
                        return null;
                    }
                })();
                var hasAttachmentDataTransfer =
                    attachmentDataTransfer && typeof attachmentDataTransfer.items !== 'undefined';

                function formatAttachmentSize(bytes) {
                    if (!bytes || isNaN(bytes)) {
                        return '0 B';
                    }

                    if (bytes < 1024) {
                        return bytes + ' B';
                    }

                    var units = ['KB', 'MB', 'GB', 'TB', 'PB'];
                    var size = bytes / 1024;

                    for (var index = 0; index < units.length; index++) {
                        if (size < 1024 || index === units.length - 1) {
                            var precision = size >= 10 ? 0 : 2;

                            return size.toLocaleString('pt-BR', {
                                minimumFractionDigits: precision,
                                maximumFractionDigits: precision,
                            }) + ' ' + units[index];
                        }

                        size /= 1024;
                    }

                    return size.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }) + ' PB';
                }

                function renderAttachmentList(files) {
                    if (!$attachmentPreview.length) {
                        return;
                    }

                    $attachmentPreview.empty();

                    if (!files.length) {
                        $attachmentPreview.addClass('d-none');
                        return;
                    }

                    var $title = $('<div/>', {
                        class: 'text-start small fw-semibold text-muted mb-2',
                    }).text('Arquivos selecionados:');

                    var $list = $('<ul/>', {
                        class: 'list-unstyled mb-0',
                    });

                    files.forEach(function (file) {
                        var $item = $('<li/>');
                        var $name = $('<span/>', {
                            class: 'fw-semibold text-color',
                        }).text(file.name);
                        var $size = $('<span/>', {
                            class: 'text-muted ms-1',
                        }).text('(' + formatAttachmentSize(file.size) + ')');

                        $item.append($name).append(' ').append($size);
                        $list.append($item);
                    });

                    $attachmentPreview
                        .removeClass('d-none')
                        .append($title)
                        .append($list);
                }

                function getAttachmentKey(file) {
                    return [file.name, file.size, file.type, file.lastModified].join('::');
                }

                function syncAttachmentInput() {
                    if (!$attachmentInput.length) {
                        renderAttachmentList(attachmentFiles);
                        return;
                    }

                    if (!hasAttachmentDataTransfer) {
                        renderAttachmentList(attachmentFiles);
                        return;
                    }

                    attachmentDataTransfer.items.clear();

                    attachmentFiles.forEach(function (file) {
                        attachmentDataTransfer.items.add(file);
                    });

                    $attachmentInput[0].files = attachmentDataTransfer.files;
                    renderAttachmentList(Array.from(attachmentDataTransfer.files));
                }

                function resetAttachmentSelection() {
                    attachmentFiles = [];

                    if ($attachmentInput.length) {
                        $attachmentInput.val('');
                    }

                    if (hasAttachmentDataTransfer) {
                        attachmentDataTransfer.items.clear();

                        if ($attachmentInput.length) {
                            $attachmentInput[0].files = attachmentDataTransfer.files;
                        }
                    }

                    renderAttachmentList([]);
                }

                function addAttachmentFiles(files) {
                    if (!files || !files.length) {
                        return;
                    }

                    var existingKeys = new Set(attachmentFiles.map(getAttachmentKey));

                    var added = false;

                    files.forEach(function (file) {
                        var fileKey = getAttachmentKey(file);

                        if (existingKeys.has(fileKey)) {
                            return;
                        }

                        attachmentFiles.push(file);
                        existingKeys.add(fileKey);
                        added = true;
                    });

                    if (!attachmentFiles.length) {
                        resetAttachmentSelection();
                        return;
                    }

                    if (!added) {
                        renderAttachmentList(attachmentFiles);
                        return;
                    }

                    if (hasAttachmentDataTransfer) {
                        syncAttachmentInput();
                        return;
                    }

                    renderAttachmentList(attachmentFiles);
                }

                if ($attachmentInput.length) {
                    $attachmentInput.on('change', function () {
                        var files = Array.from(this.files || []);

                        if (!files.length) {
                            resetAttachmentSelection();
                            return;
                        }

                        if (!hasAttachmentDataTransfer) {
                            attachmentFiles = files;
                            renderAttachmentList(files);
                            return;
                        }

                        addAttachmentFiles(files);
                    });
                }

                if ($attachmentSelectLink.length) {
                    $attachmentSelectLink.on('click', function (event) {
                        event.preventDefault();
                        $attachmentInput.trigger('click');
                    });
                }

                if ($uploadArea.length) {
                    $uploadArea.on('click keypress', function (event) {
                        var isKeyboard = event.type === 'keypress';

                        if (isKeyboard && event.key !== 'Enter' && event.key !== ' ') {
                            return;
                        }

                        if ($(event.target).closest('a,button,input,label').length) {
                            return;
                        }

                        event.preventDefault();
                        $attachmentInput.trigger('click');
                    });

                    $uploadArea.on('dragover dragenter', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        $uploadArea.addClass('is-dragging');
                    });

                    $uploadArea.on('dragleave dragend drop', function (event) {
                        if (event.type !== 'drop') {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        $uploadArea.removeClass('is-dragging');
                    });

                    $uploadArea.on('drop', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        var dataTransfer = event.originalEvent && event.originalEvent.dataTransfer;

                        if (!dataTransfer || !dataTransfer.files || !dataTransfer.files.length) {
                            return;
                        }

                        var droppedFiles = Array.from(dataTransfer.files);

                        if (!droppedFiles.length) {
                            return;
                        }

                        if (!hasAttachmentDataTransfer) {
                            attachmentFiles = droppedFiles;
                            renderAttachmentList(droppedFiles);
                            return;
                        }

                        addAttachmentFiles(droppedFiles);
                    });
                }
            });
        })(jQuery);
    </script>
@endsection

@section('content')
    @php
        $collectionAttachmentExtensions = $collectionAttachmentExtensions ?? [];
        $collectionAttachmentAcceptAttribute = $collectionAttachmentAccept
            ?? collect($collectionAttachmentExtensions)
                ->map(fn (string $extension) => '.' . ltrim($extension, '.'))
                ->implode(',');
        $collectionAttachmentFormatsSummary = !empty($collectionAttachmentExtensions)
            ? collect($collectionAttachmentExtensions)
                ->map(fn (string $extension) => strtoupper($extension))
                ->join(', ', ' e ')
            : 'PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, CSV, TXT, RTF, ODT, ODS, JPG, JPEG, PNG e MP4';
    @endphp

<x-form-page
    title="Coleta de exame"
    heading="Registro de coleta"
    :back-url="route('vet.exams.index', ['page' => request()->query('page', 1)])"
>
    <div class="vet-exams-collection">
        <p class="text-muted mb-4">
            Revise as informações definidas na solicitação e registre os dados coletados para o exame.
        </p>

        <form
            class="needs-validation"
            action="{{ route('vet.exams.update', $exam) }}"
            method="POST"
            enctype="multipart/form-data"
            novalidate
        >
            @csrf
            @method('PUT')

            <div class="vet-exams-collection__section-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="vet-exams-collection__step-badge">1</span>
                    <div>
                        <h5 class="mb-0">Dados da solicitação</h5>
                        <p class="mb-0 text-muted">
                            Estes dados foram definidos quando o exame foi solicitado e permanecem somente para consulta durante a coleta.
                        </p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Atendimento associado</label>
                        <input
                            type="text"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['attendance_label'] }}"
                            disabled
                        >
                        @if (!empty($collectionDetails['attendance_url']))
                            @php
                                $attendanceStatusColor = $collectionDetails['attendance_status_color'] ?? 'primary';
                                $attendanceStatusLabel = $collectionDetails['attendance_status_label'] ?? null;
                            @endphp
                            <div class="form-text small">
                                <a href="{{ $collectionDetails['attendance_url'] }}" class="text-decoration-none">
                                    Visualizar atendimento
                                </a>
                                @if ($attendanceStatusLabel)
                                    <span class="badge bg-{{ $attendanceStatusColor }}-subtle text-{{ $attendanceStatusColor }} ms-1">
                                        {{ $attendanceStatusLabel }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Paciente</label>
                        <input
                            type="text"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['patient'] }}"
                            disabled
                        >
                        @if (!empty($collectionDetails['guardian']))
                            <div class="form-text small">Tutor {{ $collectionDetails['guardian'] }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Veterinário responsável</label>
                        <input
                            type="text"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['veterinarian'] }}"
                            disabled
                        >
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Tipo de exame</label>
                        <input
                            type="text"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['exam_type'] }}"
                            disabled
                        >
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Data prevista de coleta</label>
                        <input
                            type="date"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['scheduled_collection_value'] }}"
                            disabled
                        >
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Laboratório parceiro</label>
                        <input
                            type="text"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['laboratory'] }}"
                            disabled
                        >
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Prioridade</label>
                        <input
                            type="text"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['priority_label'] }}"
                            disabled
                        >
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label">Status atual</label>
                        <input
                            type="text"
                            class="form-control vet-exams-collection__readonly-input"
                            value="{{ $collectionDetails['status_label'] }}"
                            disabled
                        >
                    </div>
                </div>
            </div>

            <div class="vet-exams-collection__section-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="vet-exams-collection__step-badge">2</span>
                    <div>
                        <h5 class="mb-0">Informações da coleta</h5>
                        <p class="mb-0 text-muted">Utilize este espaço para registrar observações e orientações da coleta.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label" for="observacoes_clinicas">Observações da coleta</label>
                        <textarea
                            id="observacoes_clinicas"
                            name="observacoes_clinicas"
                            class="form-control"
                            rows="5"
                            placeholder="Registre informações relevantes obtidas durante a coleta."
                        >{{ trim(old('observacoes_clinicas', $exam->observacoes_clinicas)) }}</textarea>
                        @error('observacoes_clinicas')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="vet-exams-collection__section-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="vet-exams-collection__step-badge">3</span>
                    <div>
                        <h5 class="mb-0">Documentos da solicitação</h5>
                        <p class="mb-0 text-muted">Consulte os arquivos anexados quando o exame foi solicitado.</p>
                    </div>
                </div>
                <div class="vet-exams-collection__documents-list row g-3">
                    @forelse ($documents['request'] as $document)
                        <div class="col-md-4">
                            <div class="vet-exams-collection__document-card p-3 h-100">
                                <h6 class="mb-1">{{ $document['name'] }}</h6>
                                <p class="text-muted small mb-2">{{ $document['description'] ?? 'Documento anexado na solicitação.' }}</p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ $document['url'] ?? '#' }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                    <a href="{{ $document['download_url'] ?? '#' }}" class="btn btn-sm btn-outline-secondary">Baixar</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted mb-0">Nenhum documento foi anexado durante a solicitação.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="vet-exams-collection__section-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="vet-exams-collection__step-badge">4</span>
                    <div>
                        <h5 class="mb-0">Anexos e documentação da coleta</h5>
                        <p class="mb-0 text-muted">Anexe laudos, imagens e demais arquivos gerados após a coleta.</p>
                    </div>
                </div>
                @if (!empty($documents['collection']))
                    <div class="vet-exams-collection__documents-list row g-3 mb-4">
                        @foreach ($documents['collection'] as $document)
                            <div class="col-md-4">
                                <div class="vet-exams-collection__document-card p-3 h-100">
                                    <h6 class="mb-1">{{ $document['name'] }}</h6>
                                    <p class="text-muted small mb-2">{{ $document['description'] ?? 'Documento anexado na coleta.' }}</p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ $document['url'] ?? '#' }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                        <a href="{{ $document['download_url'] ?? '#' }}" class="btn btn-sm btn-outline-secondary">Baixar</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-4">Nenhum documento de coleta foi anexado até o momento.</p>
                @endif
                <div
                    class="vet-exams-collection__upload-area p-4 text-center"
                    id="vetExamCollectionUploadArea"
                    role="button"
                    tabindex="0"
                >
                    <input
                        type="file"
                        id="vetExamCollectionAttachments"
                        name="collection_attachments[]"
                        class="d-none"
                        multiple
                        accept="{{ $collectionAttachmentAcceptAttribute }}"
                    >
                    <i class="mdi mdi-cloud-upload-outline text-success" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-1">
                        Arraste e solte arquivos aqui ou
                        <a href="#" id="vetExamCollectionAttachmentSelect">clique para selecionar</a>
                    </p>
                    <p class="text-muted small mb-0">
                        Formatos aceitos: {{ $collectionAttachmentFormatsSummary }} · Até 25 MB por arquivo
                    </p>
                </div>
                <div id="vetExamCollectionAttachmentsPreview" class="vet-exams-collection__upload-files mt-3 d-none"></div>
                @if ($errors->has('collection_attachments') || $errors->has('collection_attachments.*'))
                    <small class="text-danger d-block mt-2">
                        {{ $errors->first('collection_attachments') ?? $errors->first('collection_attachments.*') }}
                    </small>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('vet.exams.index', ['page' => request()->query('page', 1)]) }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4">Salvar coleta</button>
            </div>
        </form>
    </div>
</x-form-page>
@endsection
