(function () {
    'use strict';

    var globalConfig = window.vetAtendimentoRegistrarConfig || {};
    var elements = {
        form: null,
        fullscreenToggle: null,
        visitReasonCard: null,
        visitReasonTextarea: null,
        patientSelect: null,
        patientPhoto: null,
        patientName: null,
        patientMeta: null,
        patientWeight: null,
        patientSex: null,
        patientBirthDate: null,
        patientLastVisit: null,
        patientSize: null,
        patientOrigin: null,
        patientMicrochip: null,
        patientPedigree: null,
        patientNotes: null,
        tutorSummaryName: null,
        tutorSummaryDocument: null,
        tutorSummaryContacts: null,
        tutorSummaryEmail: null,
        tutorSummaryAddress: null,
        tutorContactInput: null,
        tutorEmailInput: null,
        tutorIdInput: null,
        tutorNameInput: null,
        quickAttachmentsList: null,
        quickAttachmentsEmpty: null,
        quickAttachmentsAddButton: null,
        quickAttachmentsInput: null,
        quickAttachmentsInputsContainer: null,
        tabContainer: null,
        contextualSidebarCards: null,
        mainColumn: null,
        sidebarColumn: null,
        checklistClearButtons: null,
        schedulingDateInput: null,
        schedulingTimeSelect: null,
        schedulingHint: null,
    };
    var state = {
        patientOptionsCache: {},
        patientDetailsCache: {},
        isFetchingPatient: false,
        quickAttachments: [],
        isUploadingAttachment: false,
    };
    var schedulingState = {
        todayIso: '',
    };
    var defaultPhoto = typeof globalConfig.defaultPhoto === 'string' ? globalConfig.defaultPhoto : '';
    var listeners = {
        fullscreen: null,
    };

    function logDebug(message, payload) {
        if (!globalConfig || !globalConfig.debug) {
            return;
        }

        try {
            console.log('[vet/atendimento]', message, payload || '');
        } catch (error) {
            // silencia erros de console antigos sem interromper execução
        }
    }

    function escapeHtml(value) {
        if (value === undefined || value === null) {
            return '';
        }

        var div = document.createElement('div');
        div.textContent = String(value);
        return div.innerHTML;
    }

    function safeArray(value) {
        return Array.isArray(value) ? value : [];
    }

    function setText(element, value, fallback) {
        if (!element) {
            return;
        }

        var hasValue = value !== undefined && value !== null && String(value).trim() !== '';
        var text = hasValue ? value : fallback;

        if (text === undefined || text === null) {
            text = '—';
        }

        element.textContent = text;
    }

    function setImageSource(image, src) {
        if (!image) {
            return;
        }

        if (src && String(src).trim() !== '') {
            image.src = src;
            return;
        }

        if (defaultPhoto && String(defaultPhoto).trim() !== '') {
            image.src = defaultPhoto;
            return;
        }

        image.removeAttribute('src');
    }

    function toggleSidebarCardsForTab(targetId) {
        var cards = elements.contextualSidebarCards;

        if (!cards || cards.length === 0) {
            return;
        }

        var normalizedTarget = typeof targetId === 'string' && targetId.trim() !== ''
            ? targetId.trim()
            : '#vetEncounterTabPre';

        Array.prototype.forEach.call(cards, function (card) {
            if (!card || !card.dataset) {
                return;
            }

            var contexts = String(card.getAttribute('data-tab-context') || '')
                .split(',')
                .map(function (value) { return value.trim(); })
                .filter(function (value) { return value !== ''; });

            if (contexts.length === 0) {
                return;
            }

            if (contexts.indexOf(normalizedTarget) !== -1) {
                card.classList.remove('d-none');
            } else {
                card.classList.add('d-none');
            }
        });
    }

    function updateColumnsForTab(targetId) {
        var mainColumn = elements.mainColumn;
        var sidebarColumn = elements.sidebarColumn;

        if (!mainColumn) {
            return;
        }

        var normalizedTarget = typeof targetId === 'string' && targetId.trim() !== ''
            ? targetId.trim()
            : '#vetEncounterTabPre';

        var isStatusTab = normalizedTarget === '#vetEncounterTabStatus';

        if (isStatusTab) {
            mainColumn.classList.add('col-xl-12');
            mainColumn.classList.remove('col-xl-8');

            if (sidebarColumn) {
                sidebarColumn.classList.add('d-none');
            }
        } else {
            mainColumn.classList.add('col-xl-8');
            mainColumn.classList.remove('col-xl-12');

            if (sidebarColumn) {
                sidebarColumn.classList.remove('d-none');
            }
        }
    }

    function initializeTabs() {
        var root = elements.form || document;

        elements.tabContainer = document.getElementById('vetEncounterTabs');
        elements.contextualSidebarCards = root.querySelectorAll('[data-tab-context]');
        elements.mainColumn = root.querySelector('[data-vet-encounter-main-column]');
        elements.sidebarColumn = root.querySelector('[data-vet-encounter-sidebar]');

        var hasContextualCards = elements.contextualSidebarCards && elements.contextualSidebarCards.length > 0;

        if (!elements.tabContainer) {
            if (hasContextualCards) {
                toggleSidebarCardsForTab('#vetEncounterTabPre');
            }
            updateColumnsForTab('#vetEncounterTabPre');
            return;
        }

        var activeTrigger = elements.tabContainer.querySelector('.nav-link.active[data-bs-toggle]');
        var initialTarget = activeTrigger ? activeTrigger.getAttribute('data-bs-target') : null;

        if (hasContextualCards) {
            toggleSidebarCardsForTab(initialTarget);
        }
        updateColumnsForTab(initialTarget);

        var tabTriggers = elements.tabContainer.querySelectorAll('[data-bs-toggle]');

        Array.prototype.forEach.call(tabTriggers, function (trigger) {
            trigger.addEventListener('shown.bs.tab', function (event) {
                var target = event && event.target ? event.target.getAttribute('data-bs-target') : null;
                if (hasContextualCards) {
                    toggleSidebarCardsForTab(target);
                }
                updateColumnsForTab(target);
            });
        });
    }

    function dispatchChangeEvent(element) {
        if (!element) {
            return;
        }

        try {
            element.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (error) {
            var fallbackEvent = document.createEvent('Event');
            fallbackEvent.initEvent('change', true, false);
            element.dispatchEvent(fallbackEvent);
        }
    }

    function clearChecklistSelections(button) {
        if (!button) {
            return;
        }

        var targetKey = button.getAttribute('data-target');
        var root = elements.form || document;
        var container = button.closest('.border');
        var checkboxes = null;

        if (container) {
            checkboxes = container.querySelectorAll('input[type="checkbox"][name^="checklists["]');
        }

        if ((!checkboxes || checkboxes.length === 0) && targetKey) {
            var name = 'checklists[' + targetKey + '][]';
            checkboxes = root.querySelectorAll('input[type="checkbox"][name="' + name + '"]');
        }

        if (!checkboxes || checkboxes.length === 0) {
            return;
        }

        var hasChanged = false;

        Array.prototype.forEach.call(checkboxes, function (checkbox) {
            if (!checkbox) {
                return;
            }

            if (checkbox.checked) {
                checkbox.checked = false;
                dispatchChangeEvent(checkbox);
                hasChanged = true;
            }
        });

        if (hasChanged) {
            button.blur();
        }
    }

    function initializeChecklistClearButtons() {
        var root = elements.form || document;

        elements.checklistClearButtons = root.querySelectorAll('[data-checklist-clear]');

        if (!elements.checklistClearButtons || elements.checklistClearButtons.length === 0) {
            return;
        }

        Array.prototype.forEach.call(elements.checklistClearButtons, function (button) {
            if (!button) {
                return;
            }

            button.addEventListener('click', function (event) {
                event.preventDefault();
                clearChecklistSelections(button);
            });
        });
    }

    function parseTimeValue(value) {
        if (!value || typeof value !== 'string') {
            return null;
        }

        var parts = value.split(':');

        if (parts.length < 2) {
            return null;
        }

        var hours = parseInt(parts[0], 10);
        var minutes = parseInt(parts[1], 10);

        if (Number.isNaN(hours) || Number.isNaN(minutes)) {
            return null;
        }

        return hours * 60 + minutes;
    }

    function formatMinutesAsTime(minutes) {
        var normalized = Number(minutes);

        if (!Number.isFinite(normalized) || normalized < 0) {
            normalized = 0;
        }

        var hours = Math.floor(normalized / 60);
        var mins = Math.floor(normalized % 60);

        var hoursLabel = hours < 10 ? '0' + hours : String(hours);
        var minutesLabel = mins < 10 ? '0' + mins : String(mins);

        return hoursLabel + ':' + minutesLabel;
    }

    function ensureSchedulingMinDate() {
        if (!elements.schedulingDateInput) {
            return;
        }

        var todayIso = new Date().toISOString().split('T')[0];
        schedulingState.todayIso = todayIso;
        var currentValue = elements.schedulingDateInput.value || '';

        if (currentValue === '' || currentValue >= todayIso) {
            elements.schedulingDateInput.setAttribute('min', todayIso);
        } else {
            elements.schedulingDateInput.removeAttribute('min');
        }
    }

    function updateSchedulingAvailability() {
        var dateInput = elements.schedulingDateInput;
        var timeSelect = elements.schedulingTimeSelect;
        var hint = elements.schedulingHint;

        if (!dateInput || !timeSelect) {
            return;
        }

        ensureSchedulingMinDate();

        var selectedDate = dateInput.value || '';
        var todayIso = schedulingState.todayIso || new Date().toISOString().split('T')[0];
        var now = new Date();
        var currentMinutes = now.getHours() * 60 + now.getMinutes();
        var isToday = selectedDate === todayIso;
        var selectedValue = timeSelect.value;

        Array.prototype.forEach.call(timeSelect.options, function (option) {
            if (!option || !option.value) {
                return;
            }

            var optionMinutes = parseTimeValue(option.value);

            if (optionMinutes === null) {
                option.removeAttribute('data-vet-time-status');
                option.disabled = false;
                return;
            }

            var disable = isToday && optionMinutes < currentMinutes;
            if (option.value === selectedValue) {
                disable = false;
            }

            option.disabled = disable;
            option.setAttribute('data-vet-time-status', disable ? 'past' : 'future');
        });

        if (hint) {
            if (isToday) {
                hint.textContent =
                    'Horários anteriores a ' + formatMinutesAsTime(currentMinutes) + ' de hoje já passaram.';
                hint.classList.remove('text-muted');
                hint.classList.add('text-danger');
            } else {
                hint.textContent = 'Selecione data e horário futuros para o atendimento.';
                hint.classList.remove('text-danger');
                hint.classList.add('text-muted');
            }
        }
    }

    function initializeSchedulingSelect2() {
        if (!elements.schedulingTimeSelect || !window.jQuery || typeof window.jQuery.fn.select2 !== 'function') {
            return;
        }

        var $select = window.jQuery(elements.schedulingTimeSelect);

        if ($select.data('select2')) {
            $select.select2('destroy');
        }

        var dropdownParent = $select.closest('.card-body');
        if (!dropdownParent.length) {
            dropdownParent = $select.parent();
        }

        var placeholder = elements.schedulingTimeSelect.getAttribute('data-placeholder') || 'Selecione um horário';

        $select.select2({
            width: '100%',
            placeholder: placeholder,
            dropdownParent: dropdownParent.length ? dropdownParent : $select.parent(),
            minimumResultsForSearch: 6,
        });

        $select.on('select2:select', function () {
            updateSchedulingAvailability();
        });
    }

    function showSchedulingWarning(title, message) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: 'warning',
                title: title || 'Atenção',
                text: message || '',
            });
            return;
        }

        if (typeof window.swal === 'function') {
            window.swal(title || message || 'Atenção', message || title || '', 'warning');
            return;
        }

        if (message) {
            window.alert(message);
        }
    }

    function storeSchedulingOldValue(element) {
        if (!element) {
            return;
        }

        element.dataset.vetOldValue = element.value ?? '';
    }

    function revertSchedulingValue(element) {
        if (!element) {
            return;
        }

        var previous = element.dataset ? element.dataset.vetOldValue : undefined;

        if (previous === undefined) {
            return;
        }

        if (String(element.value) === String(previous)) {
            return;
        }

        element.value = previous;
        dispatchChangeEvent(element);

        if (window.jQuery && window.jQuery(element).data('select2')) {
            window.jQuery(element).trigger('change');
        }
    }

    function validateSchedulingDateTime(trigger) {
        var dateInput = elements.schedulingDateInput;
        var timeSelect = elements.schedulingTimeSelect;

        if (!dateInput || !dateInput.value) {
            return true;
        }

        if (dateInput.getAttribute('data-original-value') !== '') {
            if (
                (dateInput.value === dateInput.getAttribute('data-original-value')) &&
                (timeSelect.value === timeSelect.getAttribute('data-original-value'))
            ) {
                return true;
            }
        }

        var dateParts = dateInput.value.split('-');

        if (dateParts.length < 3) {
            return true;
        }

        var day = parseInt(dateParts[2], 10);
        var month = parseInt(dateParts[1], 10) - 1;
        var year = parseInt(dateParts[0], 10);

        if (Number.isNaN(day) || Number.isNaN(month) || Number.isNaN(year)) {
            return true;
        }

        var todayIso = schedulingState.todayIso || new Date().toISOString().split('T')[0];
        var todayParts = todayIso.split('-');
        var today = new Date(
            parseInt(todayParts[0], 10),
            parseInt(todayParts[1], 10) - 1,
            parseInt(todayParts[2], 10),
        );

        var chosenDate = new Date(year, month, day);

        if (chosenDate < today && dateInput.value !== dateInput.getAttribute('data-original-value')) {
            showSchedulingWarning('Data inválida', 'A data do atendimento deve ser igual ou posterior a data atual.');
            revertSchedulingValue(dateInput);
            return false;
        }

        if (!timeSelect || !timeSelect.value) {
            return true;
        }

        var scheduled = new Date(dateInput.value + 'T' + timeSelect.value);

        if (scheduled < new Date() && timeSelect.value !== timeSelect.getAttribute('data-original-value')) {
            showSchedulingWarning('Horário inválido', 'Selecione uma data e horário posteriores ao atual.');
            revertSchedulingValue(timeSelect);
            if (chosenDate.getTime() === today.getTime()) {
                revertSchedulingValue(dateInput);
            }
            return false;
        }

        return true;
    }

    function handleSchedulingFieldBlur(event) {
        validateSchedulingDateTime(event.target);
        updateSchedulingAvailability();
    }

    function initializeSchedulingGuidance() {
        elements.schedulingDateInput = document.querySelector('[name="data_atendimento"]');
        elements.schedulingTimeSelect = document.querySelector('[name="horario"]');
        elements.schedulingHint = document.getElementById('vetEncounterSchedulingHint');

        if (!elements.schedulingDateInput || !elements.schedulingTimeSelect) {
            return;
        }

        initializeSchedulingSelect2();
        updateSchedulingAvailability();

        ['focus'].forEach(function () {
            elements.schedulingDateInput.addEventListener('focus', function () {
                storeSchedulingOldValue(elements.schedulingDateInput);
            });

            $(elements.schedulingTimeSelect).on('select2:open', function () {
                storeSchedulingOldValue(elements.schedulingTimeSelect);
            });
        });

        elements.schedulingDateInput.addEventListener('blur', handleSchedulingFieldBlur);

        $(elements.schedulingTimeSelect).on('select2:select', function (e) {
            handleSchedulingFieldBlur(e);
        });
        $(elements.schedulingTimeSelect).on('select2:close', function (e) {
            handleSchedulingFieldBlur(e);
        });
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : null;
    }

    function formatFileSizeValue(bytes) {
        var value = Number(bytes);

        if (!value || value <= 0) {
            return '0 B';
        }

        if (value < 1024) {
            return value + ' B';
        }

        var units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        var size = value / 1024;

        for (var i = 0; i < units.length; i += 1) {
            var unit = units[i];

            if (size < 1024 || i === units.length - 1) {
                var precision = size >= 10 ? 0 : 2;

                return Number(size).toLocaleString('pt-BR', {
                    minimumFractionDigits: precision,
                    maximumFractionDigits: precision,
                }) + ' ' + unit;
            }

            size /= 1024;
        }

        return Number(size).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) + ' PB';
    }

    function getAttachmentMaxItems() {
        var maxItems = Number(globalConfig.attachmentsMaxItems);

        if (!Number.isFinite(maxItems) || maxItems <= 0) {
            return 8;
        }

        return Math.max(1, Math.floor(maxItems));
    }

    function getAttachmentMaxSize() {
        var maxSize = Number(globalConfig.attachmentsMaxSizeBytes);

        if (!Number.isFinite(maxSize) || maxSize <= 0) {
            return 10 * 1024 * 1024;
        }

        return Math.floor(maxSize);
    }

    function generateAttachmentId() {
        return 'att-' + Date.now().toString(36) + '-' + Math.random().toString(16).slice(2, 8);
    }

    function resolveAttachmentBadge(attachment) {
        var extension = '';

        if (attachment && attachment.extension) {
            extension = String(attachment.extension).replace(/\./g, '').trim();
        }

        if (!extension && attachment && attachment.mime_type) {
            var mime = String(attachment.mime_type).split('/');
            extension = mime[mime.length - 1] || '';
        }

        if (!extension && attachment && attachment.name) {
            var parts = String(attachment.name).split('.');
            if (parts.length > 1) {
                extension = parts.pop();
            }
        }

        extension = extension ? extension.toUpperCase() : '';

        if (!extension) {
            return 'ARQUIVO';
        }

        if (extension.length > 8) {
            extension = extension.slice(0, 8);
        }

        return extension;
    }

    function resolveAttachmentIcon(attachment) {
        var extension = '';
        var mimeType = '';

        if (attachment && attachment.extension) {
            extension = String(attachment.extension).toLowerCase();
        }

        if (attachment && attachment.mime_type) {
            mimeType = String(attachment.mime_type).toLowerCase();
        }

        if (!extension && attachment && attachment.name) {
            var parts = String(attachment.name).split('.');
            if (parts.length > 1) {
                extension = parts.pop().toLowerCase();
            }
        }

        if (!extension && mimeType) {
            var mimeParts = mimeType.split('/');
            if (mimeParts.length > 1) {
                extension = mimeParts[1];
            }
        }

        var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        if (imageExtensions.indexOf(extension) !== -1 || mimeType.indexOf('image/') === 0) {
            return 'ri-image-line';
        }

        if (extension === 'pdf' || mimeType === 'application/pdf') {
            return 'ri-file-pdf-line';
        }

        if (['doc', 'docx', 'odt'].indexOf(extension) !== -1) {
            return 'ri-file-word-line';
        }

        if (['xls', 'xlsx', 'ods', 'csv'].indexOf(extension) !== -1) {
            return 'ri-file-excel-line';
        }

        if (['ppt', 'pptx', 'odp'].indexOf(extension) !== -1) {
            return 'ri-slideshow-line';
        }

        if (['zip', 'rar', '7z', 'gz', 'tar'].indexOf(extension) !== -1) {
            return 'ri-folder-zip-line';
        }

        if (mimeType.indexOf('video/') === 0 || ['mp4', 'mov', 'avi', 'mkv', 'webm'].indexOf(extension) !== -1) {
            return 'ri-video-line';
        }

        if (mimeType.indexOf('audio/') === 0 || ['mp3', 'wav', 'ogg', 'flac'].indexOf(extension) !== -1) {
            return 'ri-volume-up-line';
        }

        if (['txt', 'rtf', 'md'].indexOf(extension) !== -1) {
            return 'ri-file-text-line';
        }

        return 'ri-attachment-2';
    }

    function normalizeAttachment(raw) {
        if (!raw || typeof raw !== 'object') {
            return null;
        }

        var attachment = {
            id: raw.id ? String(raw.id) : generateAttachmentId(),
            name: raw.name || raw.original_name || raw.filename || 'Documento',
            extension: raw.extension || raw.ext || '',
            mime_type: raw.mime_type || raw.type || '',
            size: raw.size || '',
            size_in_bytes: raw.size_in_bytes || raw.bytes || raw.sizeBytes || null,
            uploaded_at: raw.uploaded_at || raw.created_at || '',
            uploaded_at_iso: raw.uploaded_at_iso || raw.created_at_iso || '',
            uploaded_by: raw.uploaded_by || raw.author || raw.user || '',
            url: raw.url || raw.link || '',
            path: raw.path || raw.storage_path || '',
        };

        if (!attachment.extension && attachment.name) {
            var parts = String(attachment.name).split('.');
            if (parts.length > 1) {
                attachment.extension = parts.pop();
            }
        }

        if (attachment.extension) {
            attachment.extension = String(attachment.extension).replace(/\./g, '').toLowerCase();
        }

        if (typeof attachment.size_in_bytes === 'string') {
            var parsedSize = parseInt(attachment.size_in_bytes, 10);
            if (!Number.isNaN(parsedSize)) {
                attachment.size_in_bytes = parsedSize;
            }
        }

        if (!attachment.size && typeof attachment.size_in_bytes === 'number' && attachment.size_in_bytes >= 0) {
            attachment.size = formatFileSizeValue(attachment.size_in_bytes);
        }

        if (!attachment.path && attachment.url) {
            try {
                var url = new URL(attachment.url, window.location.origin);
                attachment.path = url.pathname ? url.pathname.replace(/^\//, '') : '';
            } catch (error) {
                logDebug('Falha ao interpretar caminho do anexo', error);
            }
        }

        attachment.badge = raw.badge || resolveAttachmentBadge(attachment);
        attachment.icon = resolveAttachmentIcon(attachment);

        return attachment;
    }

    function parseInitialAttachments() {
        if (!globalConfig) {
            return [];
        }

        var initial = globalConfig.quickAttachments;

        if (!initial) {
            return [];
        }

        if (!Array.isArray(initial)) {
            try {
                initial = JSON.parse(initial);
            } catch (error) {
                logDebug('Falha ao interpretar anexos iniciais', error);
                return [];
            }
        }

        return initial
            .map(function (item) {
                return normalizeAttachment(item);
            })
            .filter(function (attachment) {
                return Boolean(attachment);
            });
    }

    function findAttachmentById(id) {
        if (!id) {
            return null;
        }

        var targetId = String(id);

        for (var index = 0; index < state.quickAttachments.length; index += 1) {
            var attachment = state.quickAttachments[index];
            if (attachment && String(attachment.id) === targetId) {
                return attachment;
            }
        }

        return null;
    }

    function syncAttachmentInputs() {
        if (!elements.quickAttachmentsInputsContainer) {
            return;
        }

        elements.quickAttachmentsInputsContainer.innerHTML = '';

        state.quickAttachments.forEach(function (attachment) {
            if (!attachment) {
                return;
            }

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'quick_attachments[]';
            input.value = JSON.stringify({
                id: attachment.id,
                name: attachment.name,
                extension: attachment.extension,
                mime_type: attachment.mime_type,
                size: attachment.size,
                size_in_bytes: attachment.size_in_bytes,
                uploaded_at: attachment.uploaded_at,
                uploaded_at_iso: attachment.uploaded_at_iso,
                uploaded_by: attachment.uploaded_by,
                url: attachment.url,
                path: attachment.path,
            });

            elements.quickAttachmentsInputsContainer.appendChild(input);
        });
    }

    function removeRenderedAttachments() {
        if (!elements.quickAttachmentsList) {
            return;
        }

        var rendered = elements.quickAttachmentsList.querySelectorAll('[data-attachment-id]');

        Array.prototype.slice.call(rendered).forEach(function (node) {
            if (node && node.parentNode) {
                node.parentNode.removeChild(node);
            }
        });
    }

    function buildQuickAttachmentCard(attachment) {
        var card = document.createElement('div');
        card.className = 'quick-attachment-item';
        card.setAttribute('data-attachment-id', attachment.id);

        var iconWrapper = document.createElement('div');
        iconWrapper.className = 'quick-attachment-icon';
        var icon = document.createElement('i');
        icon.className = attachment.icon || resolveAttachmentIcon(attachment);
        iconWrapper.appendChild(icon);

        var content = document.createElement('div');
        content.className = 'flex-grow-1 text-truncate';

        var title = document.createElement('h6');
        title.className = 'fw-semibold mb-1 text-color text-truncate quick-attachment-title';
        title.textContent = attachment.name || 'Documento'; 

        var metaLine = document.createElement('div');
        metaLine.className = 'quick-attachment-meta mb-1';
        var uploadedBy = attachment.uploaded_by ? 'Enviado por ' + attachment.uploaded_by : 'Enviado';
        if (attachment.uploaded_at) {
            uploadedBy += ' • ' + attachment.uploaded_at;
        }
        metaLine.textContent = uploadedBy;

        var sizeLine = document.createElement('div');
        sizeLine.className = 'quick-attachment-meta';
        var sizeLabel = attachment.size || (attachment.size_in_bytes ? formatFileSizeValue(attachment.size_in_bytes) : '—');
        sizeLine.textContent = 'Tamanho ' + sizeLabel;

        var actions = document.createElement('div');
        actions.className = 'quick-attachment-actions mt-2 d-flex flex-wrap align-items-center gap-3';

        if (attachment.url) {
            var link = document.createElement('a');
            link.href = attachment.url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.className = 'small fw-semibold text-purple';
            link.textContent = 'Abrir documento';
            actions.appendChild(link);
        }

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-link btn-sm px-2 py-1 quick-attachment-remove';
        removeButton.setAttribute('data-attachment-id', attachment.id);
        removeButton.innerHTML = '<i class="ri-delete-bin-6-line me-1"></i>Remover';
        actions.appendChild(removeButton);

        content.appendChild(title);
        content.appendChild(metaLine);
        content.appendChild(sizeLine);
        content.appendChild(actions);

        var badge = document.createElement('span');
        badge.className = 'quick-attachment-badge';
        badge.textContent = attachment.badge || resolveAttachmentBadge(attachment);

        card.appendChild(iconWrapper);
        card.appendChild(content);
        card.appendChild(badge);

        return card;
    }

    function renderQuickAttachments() {
        if (!elements.quickAttachmentsList) {
            return;
        }

        removeRenderedAttachments();

        if (!state.quickAttachments.length) {
            if (elements.quickAttachmentsEmpty) {
                elements.quickAttachmentsEmpty.classList.remove('d-none');
                if (!elements.quickAttachmentsEmpty.parentNode) {
                    elements.quickAttachmentsList.appendChild(elements.quickAttachmentsEmpty);
                }
            }
            return;
        }

        if (elements.quickAttachmentsEmpty) {
            elements.quickAttachmentsEmpty.classList.add('d-none');
        }

        state.quickAttachments.forEach(function (attachment) {
            if (!attachment) {
                return;
            }

            var col = document.createElement('div');
            col.className = 'col-12 col-lg-6';
            col.setAttribute('data-attachment-id', attachment.id);
            col.appendChild(buildQuickAttachmentCard(attachment));
            elements.quickAttachmentsList.appendChild(col);
        });
    }

    function toggleAttachmentButton(disabled) {
        if (!elements.quickAttachmentsAddButton) {
            return;
        }

        elements.quickAttachmentsAddButton.disabled = Boolean(disabled);
        elements.quickAttachmentsAddButton.classList.toggle('disabled', Boolean(disabled));

        var icon = elements.quickAttachmentsAddButton.querySelector('.ri');
        if (icon) {
            icon.classList.toggle('ri-loader-4-line', Boolean(disabled));
            icon.classList.toggle('ri-upload-2-line', !disabled);
            icon.classList.toggle('ri-spin', Boolean(disabled));
        }
    }

    function notifyAttachment(options) {
        if (!options) {
            return;
        }

        var type = options.type || 'info';
        var title = options.title || '';
        var message = options.message || '';

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: type,
                title: title || undefined,
                text: message || undefined,
                confirmButtonText: 'Ok',
            });
            return;
        }

        if (typeof window.swal === 'function') {
            window.swal(title || message || 'Atenção', message || title || '', type);
            return;
        }

        var composed = title;
        if (message) {
            composed = title ? title + '\n' + message : message;
        }

        if (composed) {
            window.alert(composed);
        }
    }

    function notifyAttachmentError(message) {
        notifyAttachment({
            type: 'error',
            title: 'Não foi possível anexar o documento',
            message: message || 'Tente novamente em instantes.',
        });
    }

    function notifyAttachmentWarning(message) {
        notifyAttachment({
            type: 'warning',
            title: 'Atenção',
            message: message || 'Verifique as informações do arquivo selecionado.',
        });
    }

    function addQuickAttachment(attachment) {
        if (!attachment) {
            return;
        }

        var maxItems = getAttachmentMaxItems();

        if (state.quickAttachments.length >= maxItems) {
            notifyAttachmentWarning('Você já anexou o limite de ' + maxItems + ' documentos.');
            return;
        }

        var exists = state.quickAttachments.some(function (item) {
            if (!item) {
                return false;
            }

            if (item.path && attachment.path) {
                return item.path === attachment.path;
            }

            return String(item.id) === String(attachment.id);
        });

        if (exists) {
            logDebug('Documento já presente na lista, ignorando duplicado.', attachment);
            return;
        }

        state.quickAttachments.push(attachment);
        renderQuickAttachments();
        syncAttachmentInputs();
    }

    function removeQuickAttachment(attachment, options) {
        if (!attachment) {
            return;
        }

        var id = attachment.id;
        var index = -1;

        state.quickAttachments.some(function (item, itemIndex) {
            if (item && String(item.id) === String(id)) {
                index = itemIndex;
                return true;
            }

            return false;
        });

        if (index === -1) {
            return;
        }

        var removed = state.quickAttachments.splice(index, 1)[0];
        renderQuickAttachments();
        syncAttachmentInputs();

        var shouldRemoveRemote = !options || options.skipRemote !== true;

        if (shouldRemoveRemote && removed && removed.path) {
            deleteQuickAttachmentRemote(removed.path);
        }
    }

    function removeQuickAttachmentById(id, options) {
        var attachment = findAttachmentById(id);

        if (attachment) {
            removeQuickAttachment(attachment, options);
        }
    }

    function deleteQuickAttachmentRemote(path) {
        var removeUrl = globalConfig.attachmentsRemoveUrl;

        if (!removeUrl) {
            logDebug('Endpoint para remover anexos não configurado.');
            return Promise.resolve();
        }

        var formData = new FormData();
        formData.append('path', path);

        var headers = {
            'X-Requested-With': 'XMLHttpRequest',
        };

        var csrfToken = getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return fetch(removeUrl, {
            method: 'POST',
            headers: headers,
            body: formData,
        })
            .then(function (response) {
                if (!response.ok) {
                    return response
                        .json()
                        .catch(function () {
                            return { message: 'Falha ao remover o documento.' };
                        })
                        .then(function (data) {
                            throw new Error(data && data.message ? data.message : 'Falha ao remover o documento.');
                        });
                }

                return response.json().catch(function () {
                    return { deleted: true };
                });
            })
            .catch(function (error) {
                logDebug('Erro ao remover documento remoto', error);
                notifyAttachmentError(error && error.message ? error.message : 'Não foi possível remover o documento.');
            });
    }

    function validateAttachmentBeforeUpload(file) {
        if (!file) {
            return { valid: false, message: 'Arquivo inválido.' };
        }

        var maxItems = getAttachmentMaxItems();
        if (state.quickAttachments.length >= maxItems) {
            return {
                valid: false,
                message: 'Você já anexou o limite de ' + maxItems + ' documentos.',
            };
        }

        var maxSize = getAttachmentMaxSize();
        if (file.size > maxSize) {
            return {
                valid: false,
                message:
                    'O arquivo "' +
                    file.name +
                    '" excede o tamanho máximo permitido de ' +
                    formatFileSizeValue(maxSize) +
                    '.',
            };
        }

        return { valid: true };
    }

    function uploadQuickAttachment(file) {
        var uploadUrl = globalConfig.attachmentsUploadUrl;

        if (!uploadUrl) {
            notifyAttachmentError('O envio de documentos não está disponível no momento.');
            return Promise.reject(new Error('Upload endpoint missing.'));
        }

        var validation = validateAttachmentBeforeUpload(file);
        if (!validation.valid) {
            notifyAttachmentWarning(validation.message);
            return Promise.reject(new Error(validation.message || 'Arquivo inválido.'));
        }

        var formData = new FormData();
        formData.append('file', file);

        var headers = {
            'X-Requested-With': 'XMLHttpRequest',
        };

        var csrfToken = getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        toggleAttachmentButton(true);

        return fetch(uploadUrl, {
            method: 'POST',
            headers: headers,
            body: formData,
        })
            .then(function (response) {
                if (!response.ok) {
                    return response
                        .json()
                        .catch(function () {
                            return { message: 'Não foi possível enviar o documento.' };
                        })
                        .then(function (data) {
                            throw new Error(data && data.message ? data.message : 'Não foi possível enviar o documento.');
                        });
                }

                return response.json();
            })
            .then(function (data) {
                var attachment = normalizeAttachment(data);

                if (!attachment) {
                    throw new Error('Resposta inválida ao anexar documento.');
                }

                addQuickAttachment(attachment);
            })
            .catch(function (error) {
                logDebug('Erro ao anexar documento', error);
                notifyAttachmentError(error && error.message ? error.message : 'Não foi possível anexar o documento.');
            })
            .finally(function () {
                toggleAttachmentButton(false);
            });
    }

    function handleQuickAttachmentFiles(event) {
        var input = event.target;
        if (!input || !input.files) {
            return;
        }

        var files = Array.prototype.slice.call(input.files);
        if (!files.length) {
            return;
        }

        var availableSlots = getAttachmentMaxItems() - state.quickAttachments.length;
        if (availableSlots <= 0) {
            notifyAttachmentWarning('Você já anexou o limite de documentos permitidos.');
            input.value = '';
            return;
        }

        if (files.length > availableSlots) {
            files = files.slice(0, availableSlots);
            notifyAttachmentWarning('Apenas ' + availableSlots + ' arquivo(s) adicionais podem ser anexados.');
        }

        files
            .reduce(function (promise, file) {
                return promise.then(function () {
                    return uploadQuickAttachment(file);
                });
            }, Promise.resolve())
            .finally(function () {
                input.value = '';
            });
    }

    function bindQuickAttachmentEvents() {
        if (elements.quickAttachmentsAddButton && elements.quickAttachmentsInput) {
            elements.quickAttachmentsAddButton.addEventListener('click', function (event) {
                event.preventDefault();

                if (elements.quickAttachmentsAddButton.disabled) {
                    return;
                }

                elements.quickAttachmentsInput.value = '';
                elements.quickAttachmentsInput.click();
            });
        }

        if (elements.quickAttachmentsInput) {
            elements.quickAttachmentsInput.addEventListener('change', handleQuickAttachmentFiles);
        }

        if (elements.quickAttachmentsList) {
            elements.quickAttachmentsList.addEventListener('click', function (event) {
                var trigger = event.target.closest('.quick-attachment-remove');

                if (!trigger) {
                    return;
                }

                event.preventDefault();

                var attachmentId = trigger.getAttribute('data-attachment-id');
                var attachment = findAttachmentById(attachmentId);

                if (!attachment) {
                    return;
                }

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Remover documento',
                        text: 'Deseja remover o documento "' + attachment.name + '"?',
                        showCancelButton: true,
                        confirmButtonText: 'Remover',
                        cancelButtonText: 'Cancelar',
                        focusCancel: true,
                    }).then(function (result) {
                        if (result && result.isConfirmed) {
                            removeQuickAttachment(attachment);
                        }
                    });
                } else {
                    var confirmed = window.confirm('Remover o documento "' + attachment.name + '"?');
                    if (confirmed) {
                        removeQuickAttachment(attachment);
                    }
                }
            });
        }
    }

    function initializeQuickAttachments() {
        elements.quickAttachmentsList = document.getElementById('vetQuickAttachmentList');
        elements.quickAttachmentsEmpty = document.getElementById('vetQuickAttachmentEmpty');
        elements.quickAttachmentsAddButton = document.getElementById('vetQuickAttachmentAdd');
        elements.quickAttachmentsInput = document.getElementById('vetQuickAttachmentInput');
        elements.quickAttachmentsInputsContainer = document.getElementById('vetQuickAttachmentInputs');

        if (!elements.quickAttachmentsList) {
            logDebug('Seção de anexos rápidos não encontrada.');
            return;
        }

        var initialAttachments = parseInitialAttachments();
        if (initialAttachments.length) {
            state.quickAttachments = initialAttachments;
        }

        renderQuickAttachments();
        syncAttachmentInputs();
        bindQuickAttachmentEvents();
    }

    function getPatientOptionFromCache(id) {
        if (!id) {
            return null;
        }

        return state.patientOptionsCache[id] || null;
    }

    function storePatientOption(option) {
        if (!option || !option.id) {
            return;
        }

        state.patientOptionsCache[option.id] = {
            id: option.id,
            text: option.text || '',
            meta: safeArray(option.meta),
            tutor: option.tutor || null,
        };
    }

    function storePatientDetails(patient) {
        if (!patient || !patient.id) {
            return;
        }

        state.patientDetailsCache[patient.id] = patient;
    }

    function getPatientDetailsFromCache(id) {
        if (!id) {
            return null;
        }

        return state.patientDetailsCache[id] || null;
    }

    function getOptionMeta(option) {
        if (!option) {
            return [];
        }

        if (Array.isArray(option.meta)) {
            return option.meta;
        }

        if (option.element && option.element.dataset && option.element.dataset.meta) {
            try {
                var parsed = JSON.parse(option.element.dataset.meta);
                if (Array.isArray(parsed)) {
                    return parsed;
                }
            } catch (error) {
                logDebug('Falha ao interpretar meta do option', error);
            }
        }

        if (option.id) {
            var cached = getPatientOptionFromCache(option.id);
            if (cached && Array.isArray(cached.meta)) {
                return cached.meta;
            }
        }

        return [];
    }

    function buildPatientDetailsUrl(patientId) {
        if (!patientId) {
            return null;
        }

        var template = globalConfig && typeof globalConfig.patientDetailsUrl === 'string'
            ? globalConfig.patientDetailsUrl
            : '';

        if (!template) {
            return null;
        }

        return template.replace(/0$/, encodeURIComponent(patientId));
    }

    function fetchPatientDetails(patientId) {
        if (!patientId) {
            return Promise.resolve(null);
        }

        var cached = getPatientDetailsFromCache(patientId);
        if (cached) {
            return Promise.resolve(cached);
        }

        var url = buildPatientDetailsUrl(patientId);
        if (!url) {
            return Promise.reject(new Error('Endpoint de detalhes do paciente não configurado.'));
        }

        state.isFetchingPatient = true;

        return fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Não foi possível carregar os dados do paciente.');
                }

                return response.json();
            })
            .then(function (data) {
                if (data && data.id) {
                    storePatientDetails(data);
                }

                return data;
            })
            .finally(function () {
                state.isFetchingPatient = false;
            });
    }

    function formatPatientOption(option) {
        if (!option || !option.id) {
            return escapeHtml(option && option.text ? option.text : '');
        }

        var meta = getOptionMeta(option);
        var markup = '<span class="fw-semibold text-color">' + escapeHtml(option.text || '') + '</span>';

        if (meta.length) {
            markup += '<small class="text-muted d-block">' + escapeHtml(meta.join(' • ')) + '</small>';
        }

        return '<div class="d-flex flex-column">' + markup + '</div>';
    }

    function formatPatientSelection(option) {
        if (!option || !option.id) {
            return escapeHtml(option && option.text ? option.text : '');
        }

        var cached = getPatientOptionFromCache(option.id);
        var label = option.text || (cached ? cached.text : '') || '';

        return escapeHtml(label);
    }

    function updateTutorSummary(tutor) {
        if (!tutor) {
            setText(elements.tutorSummaryName, 'Tutor não selecionado', 'Tutor não selecionado');
            setText(elements.tutorSummaryDocument, '', '—');
            setText(elements.tutorSummaryContacts, '', '—');
            setText(elements.tutorSummaryEmail, '', '—');
            setText(elements.tutorSummaryAddress, '', '—');
            return;
        }

        setText(elements.tutorSummaryName, tutor.name, 'Tutor não selecionado');
        setText(elements.tutorSummaryDocument, tutor.document, '—');

        var phones = safeArray(tutor.phones);
        setText(elements.tutorSummaryContacts, phones.length ? phones.join(' • ') : '', '—');

        setText(elements.tutorSummaryEmail, tutor.email, '—');
        setText(elements.tutorSummaryAddress, tutor.address, '—');
    }

    function updateTutorFormFields(formData) {
        if (!elements.tutorContactInput || !elements.tutorEmailInput || !elements.tutorIdInput || !elements.tutorNameInput) {
            return;
        }

        if (!formData) {
            elements.tutorContactInput.value = '';
            elements.tutorEmailInput.value = '';
            elements.tutorIdInput.value = '';
            elements.tutorNameInput.value = '';
            return;
        }

        if ('tutor_contact' in formData) {
            elements.tutorContactInput.value = formData.tutor_contact || '';
        }

        if ('tutor_email' in formData) {
            elements.tutorEmailInput.value = formData.tutor_email || '';
        }

        if ('tutor_id' in formData) {
            elements.tutorIdInput.value = formData.tutor_id !== undefined && formData.tutor_id !== null ? formData.tutor_id : '';
        }

        if ('tutor_name' in formData) {
            elements.tutorNameInput.value = formData.tutor_name || '';
        }
    }

    function updatePatientSummary(patient) {
        if (!patient) {
            clearPatientInfo();
            return;
        }

        storePatientDetails(patient);
        storePatientOption({
            id: patient.id,
            text: patient.name,
            meta: safeArray(patient.meta),
        });

        setImageSource(elements.patientPhoto, patient.photo_url);
        setText(elements.patientName, patient.name, 'Selecione um paciente');

        var meta = safeArray(patient.meta);
        setText(
            elements.patientMeta,
            meta.length ? meta.join(' • ') : '',
            'As informações do paciente aparecerão após a seleção.'
        );

        var summary = patient.summary || {};
        setText(elements.patientWeight, summary.weight, '—');
        setText(elements.patientSex, summary.sex, '—');
        setText(elements.patientBirthDate, summary.birth_date, '—');
        setText(elements.patientLastVisit, summary.last_visit, '—');
        setText(elements.patientSize, summary.size, '—');
        setText(elements.patientOrigin, summary.origin, '—');
        setText(elements.patientMicrochip, summary.microchip, '—');
        setText(elements.patientPedigree, summary.pedigree, '—');

        if (elements.patientNotes) {
            var notes = summary.notes;
            if (typeof notes === 'string') {
                notes = notes.trim();
            }

            setText(elements.patientNotes, notes, 'Sem observações clínicas registradas.');
        }

        updateTutorSummary(patient.tutor || null);
        updateTutorFormFields(patient.form || null);
    }

    function clearPatientInfo() {
        setImageSource(elements.patientPhoto, defaultPhoto);
        setText(elements.patientName, 'Selecione um paciente', 'Selecione um paciente');
        setText(elements.patientMeta, '', 'As informações do paciente aparecerão após a seleção.');
        setText(elements.patientWeight, '', '—');
        setText(elements.patientSex, '', '—');
        setText(elements.patientBirthDate, '', '—');
        setText(elements.patientLastVisit, '', '—');
        setText(elements.patientSize, '', '—');
        setText(elements.patientOrigin, '', '—');
        setText(elements.patientMicrochip, '', '—');
        setText(elements.patientPedigree, '', '—');

        if (elements.patientNotes) {
            elements.patientNotes.textContent = 'Sem observações clínicas registradas.';
        }

        updateTutorSummary(null);
        updateTutorFormFields(null);
    }

    function handlePatientSelection(patientId, optionData) {
        if (!patientId) {
            clearPatientInfo();
            return;
        }

        if (optionData) {
            storePatientOption(optionData);
        }

        fetchPatientDetails(patientId)
            .then(function (data) {
                if (data) {
                    updatePatientSummary(data);
                } else {
                    clearPatientInfo();
                }
            })
            .catch(function (error) {
                logDebug('Erro ao buscar detalhes do paciente', error);
                clearPatientInfo();
            });
    }

    function initializePatientSelect() {
        if (!elements.patientSelect || !window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
            return;
        }

        var $ = window.jQuery;
        var $select = $(elements.patientSelect);
        var placeholder = elements.patientSelect.getAttribute('data-placeholder') || 'Selecione um paciente';
        var dropdownParent = $select.closest('.card-body');
        var searchUrl = globalConfig && typeof globalConfig.patientsSearchUrl === 'string'
            ? globalConfig.patientsSearchUrl
            : '';

        if (!searchUrl) {
            console.warn('[vet/atendimento] URL de busca de pacientes não configurada.');
            return;
        }

        $select.select2({
            width: '100%',
            placeholder: placeholder,
            dropdownParent: dropdownParent.length ? dropdownParent : $select.parent(),
            ajax: {
                url: searchUrl,
                delay: 300,
                dataType: 'json',
                cache: true,
                data: function (params) {
                    return {
                        search: params.term || '',
                        page: params.page || 1,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    var results = Array.isArray(data && data.results) ? data.results : [];

                    results.forEach(function (item) {
                        if (item && item.id) {
                            storePatientOption(item);
                        }
                    });

                    return {
                        results: results,
                        pagination: {
                            more: data && data.pagination ? Boolean(data.pagination.more) : false,
                        },
                    };
                },
            },
            templateResult: formatPatientOption,
            templateSelection: formatPatientSelection,
            escapeMarkup: function (markup) {
                return markup;
            },
        });

        var select2Instance = $select.data('select2');
        if (select2Instance && select2Instance.$container) {
            select2Instance.$container.addClass('select2-lg');
        }

        $select.on('select2:select', function (event) {
            var optionData = event && event.params ? event.params.data : null;
            var id = optionData && optionData.id ? optionData.id : elements.patientSelect.value;
            handlePatientSelection(id, optionData);
        });

        $select.on('select2:clear', function () {
            handlePatientSelection('');
        });

        elements.patientSelect.addEventListener('change', function () {
            if (!elements.patientSelect.value) {
                clearPatientInfo();
            }
        });
    }

    function applyInitialPatientSelection() {
        if (!elements.patientSelect) {
            return;
        }

        var initialId = elements.patientSelect.getAttribute('data-initial-value') || (globalConfig && globalConfig.initialPatientId) || '';

        if (!initialId) {
            return;
        }

        fetchPatientDetails(initialId)
            .then(function (data) {
                if (!data || !data.id) {
                    return;
                }

                if (window.jQuery) {
                    var $ = window.jQuery;
                    var $select = $(elements.patientSelect);
                    var option = new Option(data.name || '', data.id, true, true);
                    option.dataset.meta = JSON.stringify(safeArray(data.meta));
                    $select.append(option).trigger('change');
                } else {
                    elements.patientSelect.value = data.id;
                    elements.patientSelect.dispatchEvent(new Event('change'));
                }

                updatePatientSummary(data);
            })
            .catch(function (error) {
                logDebug('Erro ao aplicar paciente inicial', error);
            });
    }

    function initializePatientSection() {
        elements.patientSelect = document.getElementById('vetEncounterPatient');
        elements.patientPhoto = document.getElementById('vetEncounterPatientPhoto');
        elements.patientName = document.getElementById('vetEncounterPatientName');
        elements.patientMeta = document.getElementById('vetEncounterPatientMeta');
        elements.patientWeight = document.getElementById('vetEncounterPatientWeight');
        elements.patientSex = document.getElementById('vetEncounterPatientSex');
        elements.patientBirthDate = document.getElementById('vetEncounterPatientBirthDate');
        elements.patientLastVisit = document.getElementById('vetEncounterPatientLastVisit');
        elements.patientSize = document.getElementById('vetEncounterPatientSize');
        elements.patientOrigin = document.getElementById('vetEncounterPatientOrigin');
        elements.patientMicrochip = document.getElementById('vetEncounterPatientMicrochip');
        elements.patientPedigree = document.getElementById('vetEncounterPatientPedigree');
        elements.patientNotes = document.getElementById('vetEncounterPatientNotes');
        elements.tutorSummaryName = document.getElementById('vetEncounterTutorSummaryName');
        elements.tutorSummaryDocument = document.getElementById('vetEncounterTutorSummaryDocument');
        elements.tutorSummaryContacts = document.getElementById('vetEncounterTutorSummaryContacts');
        elements.tutorSummaryEmail = document.getElementById('vetEncounterTutorSummaryEmail');
        elements.tutorSummaryAddress = document.getElementById('vetEncounterTutorSummaryAddress');
        elements.tutorContactInput = document.getElementById('vetEncounterTutorContact');
        elements.tutorEmailInput = document.getElementById('vetEncounterTutorEmail');
        elements.tutorIdInput = document.getElementById('vetEncounterTutorId');
        elements.tutorNameInput = document.getElementById('vetEncounterTutorNameInput');

        clearPatientInfo();
        initializePatientSelect();
        applyInitialPatientSelection();
    }

    function getTinymceSelector() {
        if (globalConfig && typeof globalConfig.tinymceSelector === 'string' && globalConfig.tinymceSelector.trim() !== '') {
            return globalConfig.tinymceSelector;
        }

        return '#vetEncounterVisitReason';
    }

    function ensureTextareaFallback() {
        if (!elements.visitReasonTextarea) {
            return;
        }

        elements.visitReasonTextarea.removeAttribute('hidden');
        elements.visitReasonTextarea.style.visibility = 'visible';
        elements.visitReasonTextarea.style.opacity = '1';
        elements.visitReasonTextarea.style.minHeight = '420px';
    }

    function updateFullscreenButton(isFullscreen) {
        var toggle = elements.fullscreenToggle;

        if (!toggle) {
            return;
        }

        toggle.setAttribute('aria-pressed', isFullscreen ? 'true' : 'false');
        toggle.classList.toggle('btn-primary', isFullscreen);
        toggle.classList.toggle('btn-outline-primary', !isFullscreen);

        var icon = toggle.querySelector('.ri');
        if (icon) {
            icon.classList.toggle('ri-fullscreen-line', !isFullscreen);
            icon.classList.toggle('ri-fullscreen-exit-line', isFullscreen);
        }

        var label = toggle.querySelector('.btn-label');
        if (label) {
            label.textContent = isFullscreen ? 'Sair da tela cheia' : 'Tela cheia';
        }
    }

    function unbindFullscreenListener() {
        if (!elements.fullscreenToggle || !listeners.fullscreen) {
            return;
        }

        elements.fullscreenToggle.removeEventListener('click', listeners.fullscreen);
        listeners.fullscreen = null;
    }

    function initializeTinyMCE() {
        logDebug('Inicializando TinyMCE');
        var selector = getTinymceSelector();
        var textarea = document.querySelector(selector);

        elements.visitReasonTextarea = textarea;

        if (!textarea) {
            console.warn('[vet/atendimento] Textarea para TinyMCE não encontrada.', { selector: selector });
            return;
        }

        if (typeof tinymce === 'undefined') {
            console.warn('[vet/atendimento] TinyMCE não está disponível na página.');
            ensureTextareaFallback();
            updateFullscreenButton(false);
            if (elements.fullscreenToggle) {
                elements.fullscreenToggle.disabled = true;
            }
            return;
        }

        var editorId = textarea.id || selector.replace(/^#/, '');

        if (tinymce.get(editorId)) {
            tinymce.get(editorId).remove();
        }

        if (elements.fullscreenToggle) {
            elements.fullscreenToggle.disabled = true;
        }

        tinymce.init({
            selector: selector,
            language: 'pt_BR',
            height: 640,
            menubar: 'file edit view insert format',
            menu: {
                file: { title: 'Arquivo', items: 'newdocument preview' },
                edit: { title: 'Editar', items: 'undo redo | cut copy paste pastetext | selectall | searchreplace' },
                view: { title: 'Visualizar', items: 'code fullscreen' },
                insert: { title: 'Inserir', items: 'link table' },
                format: {
                    title: 'Formatar',
                    items: 'bold italic underline strikethrough superscript subscript | removeformat',
                },
            },
            plugins: 'lists advlist table link searchreplace code preview fullscreen',
            toolbar:
                'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | removeformat | link table | code | fullscreen',
            toolbar_sticky: true,
            statusbar: false,
            branding: false,
            promotion: false,
            content_style:
                "body {\n" +
                "    font-family: 'Inter', sans-serif;\n" +
                '    font-size: 15px;\n' +
                '    line-height: 1.7;\n' +
                '    color: #1f2933;\n' +
                '    max-width: 860px;\n' +
                '    margin: 0 auto;\n' +
                '    padding: 48px 60px 80px;\n' +
                '    background-color: #ffffff;\n' +
                '}\n' +
                '\n' +
                'h1, h2, h3, h4 {\n' +
                '    color: #0f172a;\n' +
                '    font-weight: 600;\n' +
                '    margin-top: 2.5rem;\n' +
                '    margin-bottom: 1.25rem;\n' +
                '}\n' +
                '\n' +
                'h1:first-child, h2:first-child, h3:first-child {\n' +
                '    margin-top: 0;\n' +
                '}\n' +
                '\n' +
                'ul, ol {\n' +
                '    padding-left: 1.4rem;\n' +
                '    margin-left: 0;\n' +
                '    margin-bottom: 1.5rem;\n' +
                '}\n' +
                '\n' +
                'li {\n' +
                '    margin-bottom: 0.75rem;\n' +
                '}\n' +
                '\n' +
                'p {\n' +
                '    margin-bottom: 1.25rem;\n' +
                '}\n' +
                '\n' +
                'strong {\n' +
                '    font-weight: 600;\n' +
                '}\n',
            setup: function (editor) {
                logDebug('TinyMCE setup iniciado');

                editor.on('init', function () {
                    logDebug('TinyMCE inicializado');
                    updateFullscreenButton(false);
                    ensureTextareaFallback();

                    if (elements.fullscreenToggle) {
                        elements.fullscreenToggle.disabled = false;
                        unbindFullscreenListener();

                        listeners.fullscreen = function (event) {
                            event.preventDefault();
                            editor.execCommand('mceFullScreen');
                        };

                        elements.fullscreenToggle.addEventListener('click', listeners.fullscreen);
                    }
                });

                editor.on('FullscreenStateChanged', function (event) {
                    logDebug('Estado de tela cheia alterado', { fullscreen: event.state });
                    updateFullscreenButton(Boolean(event.state));
                });

                editor.on('remove', function () {
                    logDebug('Editor TinyMCE removido');
                    updateFullscreenButton(false);
                    if (elements.fullscreenToggle) {
                        elements.fullscreenToggle.disabled = true;
                    }
                    unbindFullscreenListener();
                });
            },
        });
    }

    function initialize() {
        logDebug('Inicialização da tela de atendimento iniciada');
        elements.form = document.getElementById('form-vet-encounter');
        elements.fullscreenToggle = document.getElementById('vetVisitReasonFullscreenToggle');
        elements.visitReasonCard = document.getElementById('vetVisitReasonCard');
        elements.visitReasonTextarea = document.querySelector(getTinymceSelector());

        if (!elements.visitReasonTextarea) {
            console.warn('[vet/atendimento] Textarea principal não encontrada.');
        }

        initializeSchedulingGuidance();

        if (elements.fullscreenToggle) {
            elements.fullscreenToggle.setAttribute('aria-pressed', 'false');
            elements.fullscreenToggle.classList.add('btn-outline-primary');
            elements.fullscreenToggle.classList.remove('btn-primary');
        }

        initializePatientSection();
        initializeQuickAttachments();
        initializeTabs();
        initializeChecklistClearButtons();
        initializeTinyMCE();
    }

    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    onReady(initialize);

    window.VetAtendimentoRegistrar = {
        config: globalConfig,
        elements: elements,
        initialize: initialize,
        initializeTinyMCE: initializeTinyMCE,
        initializeTabs: initializeTabs,
        updateFullscreenButton: updateFullscreenButton,
        toggleSidebarCardsForTab: toggleSidebarCardsForTab,
        fetchPatientDetails: fetchPatientDetails,
        updatePatientSummary: updatePatientSummary,
        handlePatientSelection: handlePatientSelection,
        initializeQuickAttachments: initializeQuickAttachments,
        addQuickAttachment: addQuickAttachment,
        removeQuickAttachmentById: removeQuickAttachmentById,
        uploadQuickAttachment: uploadQuickAttachment,
        deleteQuickAttachmentRemote: deleteQuickAttachmentRemote,
        clearChecklistSelections: clearChecklistSelections,
        initializeChecklistClearButtons: initializeChecklistClearButtons,
        initializeSchedulingGuidance: initializeSchedulingGuidance,
        updateSchedulingAvailability: updateSchedulingAvailability,
    };
})();