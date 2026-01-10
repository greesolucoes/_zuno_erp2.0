@extends('default.layout', ['title' => 'Novo Exame'])

@section('css')
    <style>
        .vet-exams-request {
            position: relative;
        }

        .vet-exams-request__badge {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.75rem;
        }

        .vet-exams-request .vet-exams__section-card {
            border-radius: 18px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 14px 32px rgba(22, 22, 107, 0.08);
        }

        .vet-exams-request .vet-exams__step-badge {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: rgba(85, 110, 230, 0.12);
            color: #556ee6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .vet-exams-request .vet-exams__upload-area {
            border: 2px dashed rgba(22, 22, 107, 0.15);
            border-radius: 14px;
            background: rgba(85, 110, 230, 0.05);
        }

        .vet-exams-request .vet-exams__upload-area.is-dragging {
            border-color: rgba(85, 110, 230, 0.6);
            background: rgba(85, 110, 230, 0.12);
        }

        .vet-exams-request .vet-exams__upload-files {
            text-align: left;
        }

        .vet-exams-request .vet-exams__upload-files li {
            margin-bottom: 0.25rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid vet-exams-request">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <span class="badge bg-primary-subtle text-primary vet-exams-request__badge">Solicitação de exame</span>
                <h2 class="text-color mb-1">Registrar solicitação de exame</h2>
                <p class="text-muted mb-0">
                    Preencha as informações da solicitação. A coleta será registrada em um formulário dedicado após esta etapa.
                </p>
            </div>
            <a
                    href="{{ route('vet.exams.index') }}"
                    class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2"
                >
                    <i class="ri-arrow-left-double-fill"></i>
                    Voltar
                </a>        
        </div>

        @php
            $availableAttendances = $attendances ?? [];
            $defaultAttendanceId = old('atendimento_id', $selectedAttendanceId ?? null);
            $defaultAnimalId = old('animal_id', $selectedAnimalId ?? null);
            $defaultVeterinarianId = old('medico_id', $selectedVeterinarianId ?? null);
            $initialAttendanceContext = $attendanceContext ?? null;
            $attachmentExtensions = $requestAttachmentExtensions ?? [];
            $attachmentAcceptAttribute = $requestAttachmentAccept
                ?? collect($attachmentExtensions)
                    ->map(fn (string $extension) => '.' . ltrim($extension, '.'))
                    ->implode(',');
            $attachmentFormatsSummary = !empty($attachmentExtensions)
                ? collect($attachmentExtensions)
                    ->map(fn (string $extension) => strtoupper($extension))
                    ->join(', ', ' e ')
                : 'PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, CSV, TXT, RTF, ODT, ODS, JPG, JPEG, PNG e MP4';
        @endphp

        <form class="needs-validation" action="{{ route('vet.exams.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="vet-exams__section-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="vet-exams__step-badge">1</span>
                    <div>
                        <h5 class="mb-0">Identificação do exame</h5>
                        <p class="mb-0 text-muted">Selecione o paciente e defina o procedimento solicitado.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="atendimento_id">Atendimento associado</label>
                        <select
                            id="atendimento_id"
                            name="atendimento_id"
                            class="form-select select2"
                            data-toggle="select2"
                            data-placeholder="Selecione o atendimento"
                            data-allow-clear="true"
                        >
                            <option value="">Sem associação</option>
                            @foreach ($availableAttendances as $attendance)
                                <option
                                    value="{{ $attendance['id'] }}"
                                    data-code="{{ $attendance['code'] }}"
                                    data-status="{{ $attendance['status'] }}"
                                    data-status-color="{{ $attendance['status_color'] }}"
                                    data-scheduled="{{ $attendance['scheduled_at'] }}"
                                    data-patient-id="{{ $attendance['animal_id'] }}"
                                    data-patient-name="{{ $attendance['animal_name'] }}"
                                    data-veterinarian-id="{{ $attendance['veterinarian_id'] }}"
                                    data-veterinarian-name="{{ $attendance['veterinarian_name'] }}"
                                    data-history-url="{{ $attendance['history_url'] }}"
                                    @selected($defaultAttendanceId === $attendance['id'])
                                >
                                    {{ $attendance['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">
                            Vincule um atendimento clínico para manter histórico, triagem e prescrições conectados ao exame.
                        </div>
                        @error('atendimento_id')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="animal_id">Paciente</label>
                        <select
                            id="animal_id"
                            name="animal_id"
                            class="form-select select2"
                            data-toggle="select2"
                            data-placeholder="Selecione o animal"
                            data-allow-clear="true"
                            required
                        >
                            <option value="">Selecione o animal</option>
                            @foreach ($animals as $animal)
                                <option value="{{ $animal['id'] }}" @selected($defaultAnimalId === $animal['id'])>
                                    {{ $animal['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('animal_id')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="medico_id">Veterinário responsável</label>
                        <select
                            id="medico_id"
                            name="medico_id"
                            class="form-select select2"
                            data-toggle="select2"
                            data-placeholder="Selecione"
                            data-allow-clear="true"
                            required
                        >
                            <option value="">Selecione</option>
                            @foreach ($veterinarians as $veterinarian)
                                <option value="{{ $veterinarian['id'] }}" @selected($defaultVeterinarianId === $veterinarian['id'])>
                                    {{ $veterinarian['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('medico_id')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="exame_id">Tipo de exame</label>
                        <select
                            id="exame_id"
                            name="exame_id"
                            class="form-select select2"
                            data-toggle="select2"
                            data-placeholder="Selecione o exame"
                            data-allow-clear="true"
                            required
                        >
                            <option value="">Selecione o exame</option>
                            @foreach ($examTypes as $type)
                                <option value="{{ $type['id'] }}" @selected(old('exame_id') === $type['id'])>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('exame_id')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div id="vet-exam-attendance-summary" class="alert alert-info d-flex align-items-start gap-3 mt-3">
                    @if ($initialAttendanceContext)
                        <span class="badge rounded-pill bg-{{ $initialAttendanceContext['status_color'] ?? 'primary' }}-subtle text-{{ $initialAttendanceContext['status_color'] ?? 'primary' }} fw-semibold mt-1">
                            {{ $initialAttendanceContext['status'] ?? 'Atendimento vinculado' }}
                        </span>
                        <div>
                            <div class="fw-semibold text-color">
                                <a href="{{ $initialAttendanceContext['history_url'] }}" class="text-decoration-none">
                                    {{ $initialAttendanceContext['code'] ?? 'Atendimento selecionado' }}
                                </a>
                            </div>
                            <div class="small text-muted">
                                @if (!empty($initialAttendanceContext['scheduled_at']))
                                    Agendado para {{ $initialAttendanceContext['scheduled_at'] }}
                                @endif
                                @if (!empty($initialAttendanceContext['patient']) || !empty($initialAttendanceContext['veterinarian']))
                                    <br>
                                    @if (!empty($initialAttendanceContext['patient']))
                                        Paciente: {{ $initialAttendanceContext['patient'] }}
                                    @endif
                                    @if (!empty($initialAttendanceContext['patient']) && !empty($initialAttendanceContext['veterinarian']))
                                        •
                                    @endif
                                    @if (!empty($initialAttendanceContext['veterinarian']))
                                        Veterinário: {{ $initialAttendanceContext['veterinarian'] }}
                                    @endif
                                @endif
                            </div>
                        </div>
                    @else
                        <i class="ri-information-line fs-4 text-primary mt-1"></i>
                        <div>
                            <strong>Nenhum atendimento vinculado.</strong>
                            <div class="small text-muted">Associe um atendimento para unificar histórico clínico, prescrições e futuras vacinações.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="vet-exams__section-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="vet-exams__step-badge">2</span>
                    <div>
                        <h5 class="mb-0">Detalhes do procedimento</h5>
                        <p class="mb-0 text-muted">Informe prazos, laboratório parceiro e observações clínicas.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label" for="data_prevista_coleta">Data prevista de coleta</label>
                        <input
                            type="date"
                            id="data_prevista_coleta"
                            name="data_prevista_coleta"
                            class="form-control"
                            value="{{ old('data_prevista_coleta') }}"
                            required
                        >
                        @error('data_prevista_coleta')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="laboratorio_parceiro">Laboratório parceiro</label>
                        <input
                            type="text"
                            id="laboratorio_parceiro"
                            name="laboratorio_parceiro"
                            class="form-control"
                            placeholder="Ex.: LabVet Diagnósticos"
                            value="{{ old('laboratorio_parceiro') }}"
                            required
                        >
                        @error('laboratorio_parceiro')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="prioridade">Prioridade</label>
                        <select id="prioridade" name="prioridade" class="form-select" required>
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('prioridade', 'normal') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('prioridade')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="observacoes_clinicas">Observações clínicas</label>
                        <textarea
                            id="observacoes_clinicas"
                            name="observacoes_clinicas"
                            class="form-control"
                            rows="4"
                            placeholder="Descreva sintomas, solicitações ao laboratório ou orientações adicionais"
                        >{{ old('observacoes_clinicas') }}</textarea>
                        @error('observacoes_clinicas')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="vet-exams__section-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="vet-exams__step-badge">3</span>
                    <div>
                        <h5 class="mb-0">Anexos e documentação da solicitação</h5>
                        <p class="mb-0 text-muted">Inclua guias médicas, termos assinados e outros arquivos gerados nesta etapa.</p>
                    </div>
                </div>
                <div class="vet-exams__upload-area p-4 text-center" id="vetExamRequestUploadArea" role="button" tabindex="0">
                    <input
                        type="file"
                        id="vetExamRequestAttachments"
                        name="attachments[]"
                        class="d-none"
                        multiple
                        accept="{{ $attachmentAcceptAttribute }}"
                    >
                    <i class="mdi mdi-cloud-upload-outline text-primary" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-1">
                        Arraste e solte arquivos aqui ou
                        <a href="#" id="vetExamRequestAttachmentSelect">clique para selecionar</a>
                    </p>
                    <p class="text-muted small mb-0">Formatos aceitos: {{ $attachmentFormatsSummary }} · Até 25 MB por arquivo</p>
                    <p class="text-muted small mt-3 mb-0">
                        Documentos específicos da coleta poderão ser anexados posteriormente no formulário de coleta.
                    </p>
                </div>
                <div id="vetExamRequestAttachmentsPreview" class="vet-exams__upload-files mt-3 d-none"></div>
                @if ($errors->has('attachments') || $errors->has('attachments.*'))
                    <small class="text-danger d-block mt-2">
                        {{ $errors->first('attachments') ?? $errors->first('attachments.*') }}
                    </small>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" name="action" value="confirm_and_schedule_vaccination" class="btn btn-warning">
                    Salvar
                </button>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script>
        (function ($) {
            'use strict';

            $(function () {
                var hasSelect2 = $.fn && typeof $.fn.select2 === 'function';

                if (hasSelect2) {
                    $('.select2').each(function () {
                        var $select = $(this);
                        var allowClear = $select.data('allow-clear');
                        var normalizedAllowClear = false;

                        if (typeof allowClear === 'string') {
                            normalizedAllowClear = allowClear === 'true';
                        } else if (typeof allowClear === 'boolean') {
                            normalizedAllowClear = allowClear;
                        }

                        $select.select2({
                            width: '100%',
                            placeholder: $select.data('placeholder') || '',
                            allowClear: normalizedAllowClear,
                        });
                    });
                }

                var $attendanceSelect = $('#atendimento_id');
                var $animalSelect = $('#animal_id');
                var $medicSelect = $('#medico_id');
                var $attendanceSummary = $('#vet-exam-attendance-summary');
                var defaultSummaryHtml = $attendanceSummary.length ? $attendanceSummary.html() : '';

                var $uploadArea = $('#vetExamRequestUploadArea');
                var $attachmentInput = $('#vetExamRequestAttachments');
                var $attachmentSelectLink = $('#vetExamRequestAttachmentSelect');
                var $attachmentPreview = $('#vetExamRequestAttachmentsPreview');
                var isDataTransferSupported = typeof DataTransfer !== 'undefined';
                var selectedAttachments = [];

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
                    var name = file && typeof file.name === 'string' ? file.name : '';
                    var size = file && typeof file.size === 'number' ? file.size : 0;
                    var lastModified = file && typeof file.lastModified === 'number'
                        ? file.lastModified
                        : 0;

                    return [name, size, lastModified].join('::');
                }

                function refreshAttachmentInput() {
                    if (!$attachmentInput.length) {
                        return;
                    }

                    if (!isDataTransferSupported) {
                        renderAttachmentList(selectedAttachments);
                        return;
                    }

                    var dt = new DataTransfer();

                    selectedAttachments.forEach(function (file) {
                        dt.items.add(file);
                    });

                    $attachmentInput[0].files = dt.files;
                    renderAttachmentList(selectedAttachments);
                }

                function addAttachments(files) {
                    if (!files || !files.length) {
                        return;
                    }

                    if (!isDataTransferSupported) {
                        selectedAttachments = Array.from(files);
                        renderAttachmentList(selectedAttachments);
                        return;
                    }

                    var existingKeys = {};

                    selectedAttachments.forEach(function (file) {
                        existingKeys[getAttachmentKey(file)] = true;
                    });

                    Array.from(files).forEach(function (file) {
                        var key = getAttachmentKey(file);

                        if (!existingKeys[key]) {
                            selectedAttachments.push(file);
                            existingKeys[key] = true;
                        }
                    });

                    refreshAttachmentInput();
                }

                if ($attachmentInput.length) {
                    selectedAttachments = isDataTransferSupported
                        ? Array.from($attachmentInput[0].files || [])
                        : [];

                    $attachmentInput.on('change', function () {
                        var files = Array.from(this.files || []);

                        if (!files.length) {
                            selectedAttachments = [];
                            refreshAttachmentInput();
                            return;
                        }

                        addAttachments(files);
                    });

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

                            addAttachments(Array.from(dataTransfer.files));
                        });
                    }
                }

                function updateAttendanceSummary() {
                    if (!$attendanceSummary.length) {
                        return;
                    }

                    var $selected = $attendanceSelect.find(':selected');
                    var selectedValue = $selected.val();

                    if (!selectedValue) {
                        $attendanceSummary.html(defaultSummaryHtml);
                        return;
                    }

                    var code = $selected.data('code') || '';
                    var status = $selected.data('status') || '';
                    var statusColor = $selected.data('statusColor') || 'primary';
                    var scheduled = $selected.data('scheduled') || '';
                    var patientName = $selected.data('patientName') || '';
                    var veterinarianName = $selected.data('veterinarianName') || '';
                    var historyUrl = $selected.data('historyUrl') || '#';

                    var details = [];

                    if (scheduled) {
                        details.push('Agendado para ' + scheduled);
                    }

                    if (patientName) {
                        details.push('Paciente: ' + patientName);
                    }

                    if (veterinarianName) {
                        details.push('Veterinário: ' + veterinarianName);
                    }

                    var detailsHtml = details.length
                        ? '<div class="small text-muted">' + details.join(' • ') + '</div>'
                        : '';

                    $attendanceSummary.html(
                        '<div class="d-flex align-items-start gap-3">' +
                            '<span class="badge rounded-pill bg-' + statusColor + '-subtle text-' + statusColor + ' fw-semibold mt-1">' +
                                (status || 'Atendimento vinculado') +
                            '</span>' +
                            '<div>' +
                                '<div class="fw-semibold text-color">' +
                                    '<a href="' + historyUrl + '" class="text-decoration-none">' +
                                        (code || 'Atendimento selecionado') +
                                    '</a>' +
                                '</div>' +
                                detailsHtml +
                            '</div>' +
                        '</div>'
                    );

                    var patientId = $selected.data('patientId');
                    if (patientId && $animalSelect.length) {
                        $animalSelect.val(String(patientId)).trigger('change');
                    }

                    var medicId = $selected.data('veterinarianId');
                    if (medicId && $medicSelect.length) {
                        $medicSelect.val(String(medicId)).trigger('change');
                    }
                }

                if ($attendanceSelect.length) {
                    $attendanceSelect.on('change', updateAttendanceSummary);

                    if (hasSelect2) {
                        $attendanceSelect.on('select2:clear', updateAttendanceSummary);
                    }

                    updateAttendanceSummary();
                }
            });
        })(jQuery);
    </script>
@endsection