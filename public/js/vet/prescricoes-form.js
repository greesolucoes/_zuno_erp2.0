(function () {
    'use strict';

    function logDebug(message, payload) {
        if (typeof window === 'undefined' || !window.console || typeof window.console.log !== 'function') {
            return;
        }

        if (payload !== undefined) {
            window.console.log('[VetPrescription]', message, payload);
        } else {
            window.console.log('[VetPrescription]', message);
        }
    }

    function hideTinymceBranding() {
        if (typeof document === 'undefined') {
            return;
        }

        document
            .querySelectorAll('.tox-statusbar__branding, .tox-promotion')
            .forEach((element) => {
                element.classList.add('d-none');
            });
    }

    function isTinymceAvailable() {
        return typeof window !== 'undefined'
            && typeof window.tinymce !== 'undefined'
            && typeof window.tinymce.init === 'function';
    }

    function ensureRichTextEditorId(element) {
        if (element.id) {
            return element.id;
        }

        const id = `vetPrescriptionRichText-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
        element.id = id;
        return id;
    }

    function initRichTextEditor(textarea, initialContent) {
        if (!textarea) {
            return;
        }

        const content = typeof initialContent === 'string' ? initialContent : '';
        textarea.value = content;

        if (!isTinymceAvailable()) {
            return;
        }

        const editorId = ensureRichTextEditorId(textarea);
        const existingEditor = window.tinymce.get(editorId);

        if (existingEditor) {
            existingEditor.remove();
        }

        window.tinymce.init({
            target: textarea,
            language: 'pt_BR',
            menubar: false,
            statusbar: false,
            height: 260,
            plugins: 'lists advlist table link',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link removeformat',
            placeholder: textarea.getAttribute('placeholder') || '',
            setup(editor) {
                editor.on('init', () => {
                    hideTinymceBranding();

                    if (content) {
                        editor.setContent(content);
                        editor.save();
                    }
                });

                editor.on('change keyup', () => {
                    editor.save();
                });
            },
        });

        setTimeout(hideTinymceBranding, 400);
    }

    function destroyRichTextEditors(container) {
        if (!isTinymceAvailable()) {
            return;
        }

        const editors = Array.isArray(window.tinymce.editors) ? window.tinymce.editors.slice() : [];

        editors.forEach((editor) => {
            if (!editor || !editor.targetElm) {
                return;
            }

            if (!container || container.contains(editor.targetElm) || editor.targetElm === container) {
                editor.remove();
            }
        });
    }

    function destroyTemplateSelectEnhancements(container) {
        if (
            !container ||
            !window.jQuery ||
            !window.jQuery.fn ||
            typeof window.jQuery.fn.select2 !== 'function'
        ) {
            return;
        }

        window
            .jQuery(container)
            .find('select[data-role="template-select-enhanced"]')
            .each(function destroyTemplateSelect() {
                const $select = window.jQuery(this);

                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
            });
    }

    function enhanceTemplateSelectFields(container) {
        if (
            !container ||
            !window.jQuery ||
            !window.jQuery.fn ||
            typeof window.jQuery.fn.select2 !== 'function'
        ) {
            return;
        }

        window
            .jQuery(container)
            .find('select[data-role="template-select-enhanced"]')
            .each(function enhanceTemplateSelect() {
                const $select = window.jQuery(this);

                if ($select.data('select2')) {
                    $select.select2('destroy');
                }

                const placeholder =
                    $select.data('placeholder') ||
                    $select.attr('placeholder') ||
                    ($select.prop('multiple') ? '' : 'Selecione uma opção');

                const dropdownParent = $select.closest('.vet-prescricao-form__dynamic-field');
                const dropdownTarget = dropdownParent.length ? dropdownParent : $select.parent();

                $select.select2({
                    width: '100%',
                    placeholder,
                    allowClear: !$select.prop('multiple'),
                    dropdownParent: dropdownTarget,
                });

                $select.trigger('change');
            });
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : null;
    }

    function formatFileSizeValue(bytes) {
        const value = Number(bytes);

        if (!value || value <= 0) {
            return '0 B';
        }

        if (value < 1024) {
            return `${value} B`;
        }

        const units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        let size = value / 1024;

        for (let index = 0; index < units.length; index += 1) {
            const unit = units[index];

            if (size < 1024 || index === units.length - 1) {
                const precision = size >= 10 ? 0 : 2;

                return Number(size).toLocaleString('pt-BR', {
                    minimumFractionDigits: precision,
                    maximumFractionDigits: precision,
                }) + ` ${unit}`;
            }

            size /= 1024;
        }

        return `${value} B`;
    }

    function formatIsoDate(value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '';
        }

        const datePart = date.toLocaleDateString('pt-BR');
        const timePart = date.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
        });

        return `${datePart} ${timePart}`;
    }

    function normalizePositiveNumber(value, fallback) {
        const numeric = Number(value);

        return Number.isFinite(numeric) && numeric > 0 ? numeric : fallback;
    }

    function notifyAttachment(options) {
        if (!options) {
            return;
        }

        const type = options.type || 'info';
        const title = options.title || '';
        const message = options.message || '';

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

        const composed = title && message ? `${title}\n${message}` : title || message;

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

    const data = window.vetPrescriptionFormData || {};
    const isEditing = Boolean(data.isEditing);
    const existingPrescription = data.existingPrescription && typeof data.existingPrescription === 'object'
        ? data.existingPrescription
        : null;
    const patientsData = Array.isArray(data.patients) ? data.patients : [];
    const templatesData = Array.isArray(data.templates) ? data.templates : [];
    const veterinariansData = Array.isArray(data.veterinarians) ? data.veterinarians : [];
    const allergiesCatalog = Array.isArray(data.allergiesCatalog) ? data.allergiesCatalog : [];
    const chronicConditionsCatalog = Array.isArray(data.chronicConditionsCatalog) ? data.chronicConditionsCatalog : [];
    const medicationsCatalog = Array.isArray(data.medicationsCatalog) ? data.medicationsCatalog : [];
    const initialTemplateId = data.initialTemplateId !== undefined && data.initialTemplateId !== null && data.initialTemplateId !== ''
        ? String(data.initialTemplateId)
        : '';
    const oldTemplateId = data.oldTemplateId !== undefined && data.oldTemplateId !== null && data.oldTemplateId !== ''
        ? String(data.oldTemplateId)
        : '';
    const templateFieldOverrides = normalizeTemplateFieldOverrides(
        data.oldTemplateFields && typeof data.oldTemplateFields === 'object' ? data.oldTemplateFields : null,
    );
    const templateFieldOverridesTemplateId = templateFieldOverrides
        ? (oldTemplateId ? normalizeId(oldTemplateId) : normalizeId(initialTemplateId))
        : '';

    logDebug('Bootstrapped form data', {
        rawData: data,
        patientsCount: patientsData.length,
        templatesCount: templatesData.length,
        veterinariansCount: veterinariansData.length,
        allergiesCatalogCount: allergiesCatalog.length,
        chronicConditionsCatalogCount: chronicConditionsCatalog.length,
        medicationsCatalogCount: medicationsCatalog.length,
        initialTemplateId,
        oldTemplateId,
        isEditing,
        hasExistingPrescription: Boolean(existingPrescription),
        hasOldTemplateFields: Boolean(templateFieldOverrides),
    });

    const medicationsCatalogById = new Map();
    const medicationsCatalogByKey = new Map();
    medicationsCatalog.forEach(registerMedicationCatalogItem);

    if (existingPrescription && existingPrescription.patient_id) {
        const prescriptionPatientId = normalizeId(existingPrescription.patient_id);

        if (prescriptionPatientId) {
            const patientIndex = patientsData.findIndex((patient) => isSameId(patient.id, prescriptionPatientId));

            if (patientIndex !== -1) {
                const targetPatient = patientsData[patientIndex];

                if (Array.isArray(existingPrescription.allergies)) {
                    targetPatient.allergies = existingPrescription.allergies;
                }

                if (Array.isArray(existingPrescription.conditions)) {
                    targetPatient.conditions = existingPrescription.conditions;
                }

                if (Object.prototype.hasOwnProperty.call(existingPrescription, 'notes')) {
                    targetPatient.notes = existingPrescription.notes;
                }
            }
        }
    }

    const defaultPatientPhoto = typeof data.defaultPatientPhoto === 'string' ? data.defaultPatientPhoto : '';

    const patientSelect = document.getElementById('vetPrescriptionPatientSelect');
    const patientHiddenField = document.getElementById('vetPrescriptionPatientHidden');
    const templateSelect = document.getElementById('vetPrescriptionTemplateSelect');
    const veterinarianSelect = document.getElementById('vetPrescriptionVeterinarianSelect');
    const veterinarianAvailability = document.getElementById('vetPrescriptionVeterinarianAvailability');
    const templateHiddenField = document.getElementById('vetPrescriptionTemplateHidden');
    const templateFieldsWrapper = document.getElementById('vetPrescriptionTemplateFields');

    const tutorName = document.getElementById('vetPrescriptionTutorName');
    const tutorDocument = document.getElementById('vetPrescriptionTutorDocument');
    const tutorContact = document.getElementById('vetPrescriptionTutorContact');
    const tutorEmail = document.getElementById('vetPrescriptionTutorEmail');
    const tutorAddress = document.getElementById('vetPrescriptionTutorAddress');
    const microchip = document.getElementById('vetPrescriptionMicrochip');
    const lastVisit = document.getElementById('vetPrescriptionLastVisit');
    const lastExam = document.getElementById('vetPrescriptionLastExam');
    const weight = document.getElementById('vetPrescriptionWeight');
    const behavior = document.getElementById('vetPrescriptionBehavior');
    const allergiesListContainer = document.getElementById('vetPrescriptionAllergiesList');
    const allergiesSummary = document.getElementById('vetPrescriptionAllergiesSummary');
    const conditionsListContainer = document.getElementById('vetPrescriptionConditionsList');
    const conditionsSummary = document.getElementById('vetPrescriptionConditionsSummary');
    const vitalsWrapper = document.getElementById('vetPrescriptionVitals');
    const notesField = document.getElementById('vetPrescriptionNotes');

    const allergySelect = document.getElementById('vetPrescriptionAllergySelect');
    const addAllergyButton = document.getElementById('vetPrescriptionAllergyAddButton');
    const allergiesField = document.getElementById('vetPrescriptionAllergiesField');
    const conditionSelect = document.getElementById('vetPrescriptionConditionSelect');
    const addConditionButton = document.getElementById('vetPrescriptionConditionAddButton');
    const conditionsField = document.getElementById('vetPrescriptionConditionsField');

    const patientPhoto = document.getElementById('vetPrescriptionPatientPhoto');
    const patientName = document.getElementById('vetPrescriptionPatientName');
    const patientDetails = document.getElementById('vetPrescriptionPatientDetails');
    const patientSummaryWeight = document.getElementById('vetPrescriptionSummaryWeight');
    const patientSummarySex = document.getElementById('vetPrescriptionSummarySex');
    const patientSummaryBirthDate = document.getElementById('vetPrescriptionSummaryBirthDate');
    const patientSummaryLastVisit = document.getElementById('vetPrescriptionSummaryLastVisit');
    const patientSummarySize = document.getElementById('vetPrescriptionSummarySize');
    const patientSummaryOrigin = document.getElementById('vetPrescriptionSummaryOrigin');
    const patientSummaryMicrochip = document.getElementById('vetPrescriptionSummaryMicrochip');
    const patientSummaryPedigree = document.getElementById('vetPrescriptionSummaryPedigree');
    const patientNotesText = document.getElementById('vetPrescriptionPatientNotes');
    const tutorSummaryName = document.getElementById('vetPrescriptionTutorSummaryName');
    const tutorSummaryDocument = document.getElementById('vetPrescriptionTutorSummaryDocument');
    const tutorSummaryContacts = document.getElementById('vetPrescriptionTutorSummaryContacts');
    const tutorSummaryEmail = document.getElementById('vetPrescriptionTutorSummaryEmail');
    const tutorSummaryAddress = document.getElementById('vetPrescriptionTutorSummaryAddress');
    const patientWeightChip = document.getElementById('vetPrescriptionPatientWeightChip');
    const patientAgeChip = document.getElementById('vetPrescriptionPatientAgeChip');
    const patientBehavior = document.getElementById('vetPrescriptionPatientBehavior');
    const patientDiet = document.getElementById('vetPrescriptionPatientDiet');

    const tutorCardName = document.getElementById('vetPrescriptionTutorCardName');
    const tutorCardDocument = document.getElementById('vetPrescriptionTutorCardDocument');
    const tutorCardContact = document.getElementById('vetPrescriptionTutorCardContact');
    const tutorCardEmail = document.getElementById('vetPrescriptionTutorCardEmail');
    const tutorCardAddress = document.getElementById('vetPrescriptionTutorCardAddress');

    const diagnosisInput = document.getElementById('vetPrescriptionDiagnosisInput');
    const objectivesList = document.getElementById('vetPrescriptionObjectivesList');
    const summaryTextarea = document.getElementById('vetPrescriptionSummaryTextarea');
    const guidelinesTextarea = document.getElementById('vetPrescriptionGuidelines');
    const medicationsList = document.getElementById('vetPrescriptionMedicationsList');
    const monitoringList = document.getElementById('vetPrescriptionMonitoringList');
    const alertsWrapper = document.getElementById('vetPrescriptionAlerts');
    const timelineWrapper = document.getElementById('vetPrescriptionTimeline');

    const addMedicationButton = document.getElementById('vetPrescriptionAddMedication');
    const channelsWrapper = document.getElementById('vetPrescriptionChannels');
    const form = document.getElementById('vetPrescriptionForm');
    const medicationsField = document.getElementById('vetPrescriptionMedicationsField');
    const channelsField = document.getElementById('vetPrescriptionChannelsField');
    const submitButton = document.getElementById('vetPrescriptionSubmit');
    const attachmentsList = document.getElementById('vetPrescriptionAttachmentList');
    const attachmentsEmptyElement = document.getElementById('vetPrescriptionAttachmentEmpty');
    const attachmentsAddButton = document.getElementById('vetPrescriptionAttachmentAdd');
    const attachmentsInput = document.getElementById('vetPrescriptionAttachmentInput');
    const attachmentsInputsContainer = document.getElementById('vetPrescriptionAttachmentInputs');
    const attachmentsEmptyTemplate = attachmentsEmptyElement ? attachmentsEmptyElement.cloneNode(true) : null;

    const attachmentsConfig = {
        uploadUrl: typeof data.attachmentsUploadUrl === 'string' ? data.attachmentsUploadUrl : '',
        removeUrl: typeof data.attachmentsRemoveUrl === 'string' ? data.attachmentsRemoveUrl : '',
        maxItems: Math.max(1, Math.floor(normalizePositiveNumber(data.attachmentsMaxItems, 8))),
        maxSize: normalizePositiveNumber(data.attachmentsMaxSizeBytes, 10 * 1024 * 1024),
    };

    const attachmentsState = {
        items: [],
        isUploading: false,
    };

    const defaultPatientNotesMessage = 'Sem observações clínicas registradas.';

    let currentPatientId = null;
    let currentAllergies = [];
    let currentConditions = [];
    let editingDataApplied = false;

    function resolveAttachmentIcon(extension, mimeType) {
        const normalizedExtension = typeof extension === 'string' ? extension.trim().toLowerCase() : '';
        const normalizedMime = typeof mimeType === 'string' ? mimeType.trim().toLowerCase() : '';

        if (normalizedMime.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(normalizedExtension)) {
            return 'ri-image-line';
        }

        if (normalizedMime.startsWith('video/')) {
            return 'ri-video-line';
        }

        if (normalizedMime.startsWith('audio/')) {
            return 'ri-music-2-line';
        }

        if (normalizedMime === 'application/pdf' || normalizedExtension === 'pdf') {
            return 'ri-file-pdf-line';
        }

        if (normalizedMime.includes('word') || ['doc', 'docx'].includes(normalizedExtension)) {
            return 'ri-file-word-line';
        }

        if (normalizedMime.includes('excel') || normalizedMime === 'text/csv' || ['xls', 'xlsx', 'csv'].includes(normalizedExtension)) {
            return 'ri-file-excel-line';
        }

        if (['ppt', 'pptx'].includes(normalizedExtension)) {
            return 'ri-file-ppt-line';
        }

        if (normalizedMime === 'application/zip' || normalizedMime === 'application/x-zip-compressed' || ['zip', 'rar', '7z'].includes(normalizedExtension)) {
            return 'ri-folder-zip-line';
        }

        return 'ri-file-line';
    }

    function normalizeAttachmentItem(raw, fallbackIndex) {
        if (!raw || typeof raw !== 'object') {
            return null;
        }

        let path = '';
        if (typeof raw.path === 'string' && raw.path.trim() !== '') {
            path = raw.path.trim().replace(/^\/+/, '');
        }

        const idSource = raw.id || path || `attachment-${Date.now()}-${fallbackIndex || 0}`;
        const id = String(idSource);

        const parsedSize = Number(raw.size_in_bytes);
        const sizeBytes = Number.isFinite(parsedSize) && parsedSize > 0 ? parsedSize : null;
        const sizeLabel = typeof raw.size === 'string' && raw.size.trim() !== ''
            ? raw.size.trim()
            : sizeBytes ? formatFileSizeValue(sizeBytes) : null;

        const uploadedAtIso = typeof raw.uploaded_at_iso === 'string' && raw.uploaded_at_iso.trim() !== ''
            ? raw.uploaded_at_iso.trim()
            : null;

        let uploadedAt = typeof raw.uploaded_at === 'string' && raw.uploaded_at.trim() !== ''
            ? raw.uploaded_at.trim()
            : null;

        if (!uploadedAt && uploadedAtIso) {
            uploadedAt = formatIsoDate(uploadedAtIso) || null;
        }

        const url = typeof raw.url === 'string' && raw.url.trim() !== '' ? raw.url.trim() : '';

        return {
            id,
            name: typeof raw.name === 'string' && raw.name.trim() !== '' ? raw.name.trim() : 'Documento',
            path,
            url,
            extension: typeof raw.extension === 'string' && raw.extension.trim() !== '' ? raw.extension.trim().toLowerCase() : '',
            mimeType: typeof raw.mime_type === 'string' ? raw.mime_type : '',
            size: sizeLabel,
            sizeBytes,
            uploadedAt,
            uploadedAtIso,
            uploadedBy: typeof raw.uploaded_by === 'string' && raw.uploaded_by.trim() !== '' ? raw.uploaded_by.trim() : '',
        };
    }

    function getAttachmentMaxItems() {
        const value = Number(attachmentsConfig.maxItems);
        return Number.isFinite(value) && value > 0 ? value : 8;
    }

    function getAttachmentMaxSize() {
        const value = Number(attachmentsConfig.maxSize);
        return Number.isFinite(value) && value > 0 ? value : 10 * 1024 * 1024;
    }

    function refreshAttachmentButtonState() {
        if (!attachmentsAddButton) {
            return;
        }

        const atLimit = attachmentsState.items.length >= getAttachmentMaxItems();
        const disabled = attachmentsState.isUploading || atLimit;

        attachmentsAddButton.disabled = disabled;
        attachmentsAddButton.classList.toggle('disabled', disabled);

        const icon = attachmentsAddButton.querySelector('.ri');
        if (icon) {
            icon.classList.toggle('ri-loader-4-line', attachmentsState.isUploading);
            icon.classList.toggle('ri-upload-2-line', !attachmentsState.isUploading);
            icon.classList.toggle('ri-spin', attachmentsState.isUploading);
        }
    }

    function setAttachmentUploading(isUploading) {
        attachmentsState.isUploading = Boolean(isUploading);
        refreshAttachmentButtonState();
    }

    function buildAttachmentCard(attachment) {
        const card = document.createElement('div');
        card.className = 'vet-prescricao-form__attachment-card';

        const iconWrapper = document.createElement('span');
        iconWrapper.className = 'vet-prescricao-form__attachment-icon bg-primary-subtle text-primary';

        const icon = document.createElement('i');
        icon.className = resolveAttachmentIcon(attachment.extension, attachment.mimeType);
        iconWrapper.appendChild(icon);

        const body = document.createElement('div');
        body.className = 'flex-fill';

        const title = document.createElement('h6');
        title.className = 'mb-1';
        title.textContent = attachment.name || 'Documento';
        body.appendChild(title);

        const metaParts = [];
        if (attachment.uploadedBy) {
            metaParts.push(`por ${attachment.uploadedBy}`);
        }
        if (attachment.uploadedAt) {
            metaParts.push(`em ${attachment.uploadedAt}`);
        }

        const metaLine = document.createElement('span');
        metaLine.className = 'text-muted small d-block';
        metaLine.textContent = metaParts.length ? `Enviado ${metaParts.join(' ')}` : 'Documento anexado anteriormente.';
        body.appendChild(metaLine);

        const sizeParts = [];
        if (attachment.size) {
            sizeParts.push(`Tamanho ${attachment.size}`);
        }
        if (attachment.extension) {
            sizeParts.push(attachment.extension.toUpperCase());
        }

        if (sizeParts.length) {
            const sizeLine = document.createElement('span');
            sizeLine.className = 'text-muted small d-block';
            sizeLine.textContent = sizeParts.join(' • ');
            body.appendChild(sizeLine);
        }

        const actions = document.createElement('div');
        actions.className = 'd-flex flex-wrap align-items-center gap-2 mt-2';

        if (attachment.url) {
            const viewLink = document.createElement('a');
            viewLink.href = attachment.url;
            viewLink.target = '_blank';
            viewLink.rel = 'noopener';
            viewLink.className = 'btn btn-sm btn-outline-primary';
            viewLink.textContent = 'Visualizar';
            actions.appendChild(viewLink);
        }

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-sm btn-outline-danger';
        removeButton.setAttribute('data-attachment-remove', attachment.id);
        removeButton.textContent = 'Remover';

        if (!attachment.path) {
            removeButton.disabled = true;
            removeButton.classList.add('disabled');
        }

        actions.appendChild(removeButton);
        body.appendChild(actions);

        const pill = document.createElement('span');
        pill.className = 'vet-prescricao-form__pill';
        pill.textContent = (attachment.extension || 'ARQ').toUpperCase();

        card.appendChild(iconWrapper);
        card.appendChild(body);
        card.appendChild(pill);

        return card;
    }

    function renderAttachments() {
        if (!attachmentsList) {
            return;
        }

        attachmentsList.innerHTML = '';

        if (!attachmentsState.items.length) {
            let placeholder = null;

            if (attachmentsEmptyTemplate) {
                placeholder = attachmentsEmptyTemplate.cloneNode(true);
                placeholder.classList.remove('d-none');
                placeholder.id = 'vetPrescriptionAttachmentEmpty';
            } else {
                placeholder = document.createElement('div');
                placeholder.className = 'col-12';

                const placeholderContent = document.createElement('div');
                placeholderContent.className = 'vet-prescricao-form__list-placeholder';
                placeholderContent.textContent = 'Nenhum documento anexado.';
                placeholder.appendChild(placeholderContent);
            }

            attachmentsList.appendChild(placeholder);
            return;
        }

        attachmentsState.items.forEach((attachment) => {
            if (!attachment) {
                return;
            }

            const col = document.createElement('div');
            col.className = 'col-md-6';
            col.setAttribute('data-attachment-id', attachment.id);
            col.appendChild(buildAttachmentCard(attachment));
            attachmentsList.appendChild(col);
        });
    }

    function syncAttachmentInputs() {
        if (!attachmentsInputsContainer) {
            return;
        }

        attachmentsInputsContainer.innerHTML = '';

        attachmentsState.items.forEach((attachment) => {
            if (!attachment) {
                return;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'attachments[]';
            input.value = JSON.stringify({
                id: attachment.id,
                path: attachment.path,
                url: attachment.url,
                name: attachment.name,
                extension: attachment.extension,
                mime_type: attachment.mimeType,
                size: attachment.size,
                size_in_bytes: attachment.sizeBytes,
                uploaded_at: attachment.uploadedAt,
                uploaded_at_iso: attachment.uploadedAtIso,
                uploaded_by: attachment.uploadedBy,
            });

            attachmentsInputsContainer.appendChild(input);
        });
    }

    function addAttachment(attachment) {
        if (!attachment) {
            return;
        }

        const maxItems = getAttachmentMaxItems();
        if (attachmentsState.items.length >= maxItems) {
            notifyAttachmentWarning(`Você já anexou o limite de ${maxItems} documentos.`);
            return;
        }

        const exists = attachmentsState.items.some((item) => {
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

        attachmentsState.items.push(attachment);
        renderAttachments();
        syncAttachmentInputs();
        refreshAttachmentButtonState();
    }

    function deleteAttachmentRemote(path) {
        const removeUrl = attachmentsConfig.removeUrl;

        if (!removeUrl) {
            logDebug('Endpoint para remover anexos não configurado.');
            return Promise.resolve();
        }

        const formData = new FormData();
        formData.append('path', path);

        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
        };

        const csrfToken = getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return fetch(removeUrl, {
            method: 'POST',
            headers,
            body: formData,
        })
            .then((response) => {
                if (!response.ok) {
                    return response
                        .json()
                        .catch(() => ({ message: 'Falha ao remover o documento.' }))
                        .then((data) => {
                            throw new Error(data && data.message ? data.message : 'Falha ao remover o documento.');
                        });
                }

                return response.json().catch(() => ({ deleted: true }));
            })
            .catch((error) => {
                logDebug('Erro ao remover documento remoto', error);
                throw error;
            });
    }

    function removeAttachment(attachment, options) {
        if (!attachment) {
            return Promise.resolve();
        }

        const index = attachmentsState.items.findIndex((item) => item && String(item.id) === String(attachment.id));

        if (index === -1) {
            return Promise.resolve();
        }

        const [removed] = attachmentsState.items.splice(index, 1);
        renderAttachments();
        syncAttachmentInputs();
        refreshAttachmentButtonState();

        const shouldRemoveRemote = !options || options.skipRemote !== true;

        if (shouldRemoveRemote && removed && removed.path) {
            return deleteAttachmentRemote(removed.path).catch((error) => {
                attachmentsState.items.splice(index, 0, removed);
                renderAttachments();
                syncAttachmentInputs();
                refreshAttachmentButtonState();
                throw error;
            });
        }

        return Promise.resolve();
    }

    function removeAttachmentById(id, options) {
        const attachment = attachmentsState.items.find((item) => item && String(item.id) === String(id));

        if (!attachment) {
            return Promise.resolve();
        }

        return removeAttachment(attachment, options);
    }

    function validateAttachmentBeforeUpload(file) {
        if (!file) {
            return { valid: false, message: 'Arquivo inválido.' };
        }

        const maxItems = getAttachmentMaxItems();
        if (attachmentsState.items.length >= maxItems) {
            return {
                valid: false,
                message: `Você já anexou o limite de ${maxItems} documentos.`,
            };
        }

        const maxSize = getAttachmentMaxSize();
        if (file.size && maxSize && file.size > maxSize) {
            return {
                valid: false,
                message: `O arquivo "${file.name}" excede o tamanho máximo permitido de ${formatFileSizeValue(maxSize)}.`,
            };
        }

        return { valid: true };
    }

    function uploadAttachment(file) {
        if (!file) {
            return Promise.resolve();
        }

        const uploadUrl = attachmentsConfig.uploadUrl;

        if (!uploadUrl) {
            notifyAttachmentError('O envio de documentos não está disponível no momento.');
            return Promise.resolve();
        }

        const validation = validateAttachmentBeforeUpload(file);
        if (!validation.valid) {
            notifyAttachmentWarning(validation.message);
            return Promise.resolve();
        }

        const formData = new FormData();
        formData.append('file', file);

        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
        };

        const csrfToken = getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        setAttachmentUploading(true);

        return fetch(uploadUrl, {
            method: 'POST',
            headers,
            body: formData,
        })
            .then((response) => {
                if (!response.ok) {
                    return response
                        .json()
                        .catch(() => ({ message: 'Não foi possível enviar o documento.' }))
                        .then((data) => {
                            throw new Error(data && data.message ? data.message : 'Não foi possível enviar o documento.');
                        });
                }

                return response.json();
            })
            .then((data) => {
                const attachment = normalizeAttachmentItem(data, attachmentsState.items.length);

                if (!attachment) {
                    throw new Error('Resposta inválida ao anexar documento.');
                }

                addAttachment(attachment);
            })
            .catch((error) => {
                logDebug('Erro ao anexar documento', error);
                notifyAttachmentError(error && error.message ? error.message : 'Não foi possível anexar o documento.');
            })
            .finally(() => {
                setAttachmentUploading(false);
            });
    }

    function handleAttachmentFiles(event) {
        const input = event.target;
        if (!input || !input.files) {
            return;
        }

        let files = Array.prototype.slice.call(input.files);
        if (!files.length) {
            return;
        }

        const availableSlots = getAttachmentMaxItems() - attachmentsState.items.length;

        if (availableSlots <= 0) {
            notifyAttachmentWarning('Você já anexou o limite de documentos permitidos.');
            input.value = '';
            return;
        }

        if (files.length > availableSlots) {
            files = files.slice(0, availableSlots);
            notifyAttachmentWarning(`Apenas ${availableSlots} arquivo(s) adicionais podem ser anexados.`);
        }

        files
            .reduce((promise, file) => promise.then(() => uploadAttachment(file)), Promise.resolve())
            .finally(() => {
                input.value = '';
            });
    }

    function initializeAttachments() {
        if (!attachmentsList) {
            return;
        }

        if (Array.isArray(data.attachments)) {
            attachmentsState.items = data.attachments
                .map((item, index) => normalizeAttachmentItem(item, index))
                .filter((item) => item);
        }

        renderAttachments();
        syncAttachmentInputs();
        refreshAttachmentButtonState();

        attachmentsList.addEventListener('click', (event) => {
            const target = event.target.closest('[data-attachment-remove]');
            if (!target) {
                return;
            }

            event.preventDefault();

            const attachmentId = target.getAttribute('data-attachment-remove');
            if (!attachmentId) {
                return;
            }

            target.disabled = true;
            target.classList.add('disabled');

            removeAttachmentById(attachmentId)
                .catch((error) => {
                    logDebug('Erro ao remover anexo da prescrição', error);
                    notifyAttachmentError(error && error.message ? error.message : 'Não foi possível remover o documento.');
                });
        });

        if (attachmentsAddButton && attachmentsInput) {
            attachmentsAddButton.addEventListener('click', (event) => {
                event.preventDefault();

                if (attachmentsAddButton.disabled) {
                    return;
                }

                attachmentsInput.value = '';
                attachmentsInput.click();
            });

            attachmentsInput.addEventListener('change', handleAttachmentFiles);
        }
    }

    function normalizeId(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value);
    }

    function normalizeTextValue(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value);
    }

    function normalizeArrayValue(value) {
        if (Array.isArray(value)) {
            return value
                .map((item) => normalizeTextValue(item))
                .map((item) => item.trim())
                .filter((item) => item !== '');
        }

        const normalized = normalizeTextValue(value).trim();

        return normalized !== '' ? [normalized] : [];
    }

    function normalizeTemplateFieldOverrides(raw) {
        if (!raw || typeof raw !== 'object') {
            return null;
        }

        const normalized = {};

        Object.keys(raw).forEach((key) => {
            const value = raw[key];

            if (value === undefined || value === null) {
                return;
            }

            if (Array.isArray(value)) {
                normalized[key] = normalizeArrayValue(value);
                return;
            }

            normalized[key] = normalizeTextValue(value);
        });

        return Object.keys(normalized).length ? normalized : null;
    }

    function getTemplateFieldOverridesForTemplate(template) {
        if (!templateFieldOverrides) {
            return null;
        }

        if (!template || !template.id) {
            return templateFieldOverridesTemplateId ? null : templateFieldOverrides;
        }

        if (!templateFieldOverridesTemplateId) {
            return templateFieldOverrides;
        }

        return isSameId(normalizeId(template.id), templateFieldOverridesTemplateId)
            ? templateFieldOverrides
            : null;
    }

    function resolveTemplateFieldOverride(overrides, field, index) {
        if (!overrides) {
            return undefined;
        }

        const indexKey = String(index);

        if (Object.prototype.hasOwnProperty.call(overrides, indexKey)) {
            return overrides[indexKey];
        }

        const fieldId = field && typeof field === 'object' && field.id !== undefined && field.id !== null
            ? String(field.id)
            : null;

        if (fieldId && Object.prototype.hasOwnProperty.call(overrides, fieldId)) {
            return overrides[fieldId];
        }

        return undefined;
    }

    function isCheckedValue(value) {
        if (value === undefined || value === null) {
            return false;
        }

        if (typeof value === 'boolean') {
            return value;
        }

        const normalized = String(value).trim().toLowerCase();

        return ['1', 'true', 'yes', 'on', 'checked'].includes(normalized);
    }

    function normalizeChannelList(value) {
        if (!Array.isArray(value)) {
            return [];
        }

        return value
            .map((channel) => {
                if (typeof channel === 'string') {
                    return channel.trim();
                }

                if (channel && typeof channel === 'object') {
                    if (Object.prototype.hasOwnProperty.call(channel, 'value')) {
                        return normalizeTextValue(channel.value).trim();
                    }

                    if (Object.prototype.hasOwnProperty.call(channel, 'name')) {
                        return normalizeTextValue(channel.name).trim();
                    }
                }

                return normalizeTextValue(channel).trim();
            })
            .filter((channel) => channel !== '');
    }

    function parseJsonArray(value) {
        if (Array.isArray(value)) {
            return value;
        }

        if (typeof value !== 'string' || value.trim() === '') {
            return [];
        }

        try {
            const parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            logDebug('Failed to parse JSON array value', { value, error });
            return [];
        }
    }

    function isSameId(a, b) {
        return normalizeId(a) === normalizeId(b);
    }

    function normalizeMedicationKey(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value).trim().toLowerCase();
    }

    function registerMedicationCatalogItem(item) {
        if (!item || typeof item !== 'object') {
            return;
        }

        const itemId = normalizeId(item.id);
        if (itemId) {
            medicationsCatalogById.set(itemId, item);
        }

        [item.label, item.name, item.generic_name]
            .map(normalizeMedicationKey)
            .filter((key) => key !== '')
            .forEach((key) => {
                if (!medicationsCatalogByKey.has(key)) {
                    medicationsCatalogByKey.set(key, item);
                }
            });
    }

    function findMedicationByCatalogId(id) {
        if (!id) {
            return null;
        }

        const normalized = normalizeId(id);
        return normalized ? medicationsCatalogById.get(normalized) || null : null;
    }

    function findMedicationByCatalogKey(value) {
        const key = normalizeMedicationKey(value);
        return key ? medicationsCatalogByKey.get(key) || null : null;
    }

    function resolveMedicationCatalogItem(label, idHint) {
        const hinted = findMedicationByCatalogId(idHint);
        if (hinted) {
            return hinted;
        }

        if (!label) {
            return null;
        }

        return findMedicationByCatalogKey(label);
    }

    function populateMedicationSelect(select, selectedId, fallbackLabel = '') {
        if (!select) {
            return;
        }

        clearElement(select);

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '';
        select.appendChild(placeholder);

        const normalizedSelected = normalizeId(selectedId);
        let hasSelected = false;

        medicationsCatalog.forEach((item) => {
            if (!item || !item.id || !item.label) {
                return;
            }

            const option = document.createElement('option');
            const optionValue = normalizeId(item.id);
            option.value = optionValue;
            option.textContent = item.label;

            if (normalizedSelected && optionValue === normalizedSelected) {
                option.selected = true;
                hasSelected = true;
            }

            select.appendChild(option);
        });

        if (!hasSelected && fallbackLabel) {
            const customOption = document.createElement('option');
            customOption.value = '';
            customOption.textContent = fallbackLabel;
            customOption.selected = true;
            customOption.dataset.customOption = 'true';
            select.appendChild(customOption);
        }
    }

    function setMedicationSelectValue(select, value, options = {}) {
        if (!select) {
            return;
        }

        const normalized = normalizeId(value);
        select.value = normalized;

        if (window.jQuery && window.jQuery.fn) {
            const $select = window.jQuery(select);
            if ($select.data('select2')) {
                const triggerChange = options.triggerChange === true;
                if (triggerChange) {
                    $select.val(normalized || '').trigger('change').trigger('change.select2');
                } else {
                    $select.val(normalized || '').trigger('change.select2');
                }
            }
        }
    }

    function initializeMedicationSelects(scope) {
        if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
            return;
        }

        const root = scope instanceof HTMLElement ? scope : document;
        const selects = root.querySelectorAll('select[data-role="medication-select"]');

        selects.forEach((select) => {
            if (select.dataset.medicationSelectInitialized === 'true') {
                return;
            }

            const $select = window.jQuery(select);
            const placeholder = select.getAttribute('data-placeholder') || 'Selecione o medicamento';
            const dropdownParent = $select.closest('.vet-prescricao-form__medication-item');

            $select.select2({
                width: '100%',
                placeholder,
                allowClear: true,
                dropdownParent: dropdownParent.length ? dropdownParent : $select.parent(),
            });

            select.dataset.medicationSelectInitialized = 'true';
        });
    }

    function findPatient(id) {
        return patientsData.find((patient) => isSameId(patient.id, id)) || null;
    }

    function parsePatientFromOption(option) {
        if (!option || !option.dataset) {
            return null;
        }

        const raw = option.dataset.patient;
        if (!raw) {
            return null;
        }

        try {
            const parsed = JSON.parse(raw);
            logDebug('Parsed patient payload from option dataset', parsed);
            return parsed;
        } catch (error) {
            logDebug('Failed to parse patient dataset payload', {
                error,
                raw,
            });
            return null;
        }
    }

    function resolvePatient(id) {
        if (!id) {
            return null;
        }

        const patientFromData = findPatient(id);
        if (patientFromData) {
            return patientFromData;
        }

        if (!patientSelect) {
            return null;
        }

        const option = Array.from(patientSelect.options).find((item) => isSameId(item.value, id));
        if (!option) {
            return null;
        }

        const parsedPatient = parsePatientFromOption(option);
        if (parsedPatient) {
            return parsedPatient;
        }

        return null;
    }

    function findTemplate(id) {
        return templatesData.find((template) => isSameId(template.id, id)) || null;
    }

    function findVeterinarian(id) {
        return veterinariansData.find((vet) => isSameId(vet.id, id)) || null;
    }

    function clearElement(element) {
        if (!element) {
            return;
        }

        while (element.firstChild) {
            element.removeChild(element.firstChild);
        }
    }

    function createTag(text, icon, options) {
        const span = document.createElement('span');
        span.className = 'vet-prescricao-form__tag';
        if (icon) {
            const iconElement = document.createElement('i');
            iconElement.className = icon;
            span.appendChild(iconElement);
        }
        const label = document.createElement('span');
        label.textContent = text;
        span.appendChild(label);

        if (options && options.removable && typeof options.onRemove === 'function') {
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'vet-prescricao-form__tag-remove';
            removeButton.innerHTML = '<i class="ri-close-line"></i>';
            removeButton.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                options.onRemove();
            });
            span.appendChild(removeButton);
        }

        return span;
    }

    function renderListPlaceholder(container, message) {
        if (!container) {
            return;
        }
        const placeholder = document.createElement('div');
        placeholder.className = 'vet-prescricao-form__list-placeholder w-100';
        placeholder.textContent = message;
        container.appendChild(placeholder);
    }

    function buildAssignmentSummary(items, config) {
        const safeConfig = config || {};
        const emptyMessage = typeof safeConfig.emptyMessage === 'string' ? safeConfig.emptyMessage : '';
        const remainderSingular = typeof safeConfig.remainderSingular === 'string'
            ? safeConfig.remainderSingular
            : 'registro';
        const remainderPlural = typeof safeConfig.remainderPlural === 'string'
            ? safeConfig.remainderPlural
            : 'registros';

        if (!Array.isArray(items) || !items.length) {
            return emptyMessage;
        }

        const normalizedNames = items
            .map((item) => {
                if (item && typeof item === 'object' && Object.prototype.hasOwnProperty.call(item, 'name')) {
                    return normalizeTextValue(item.name).trim();
                }
                return normalizeTextValue(item).trim();
            })
            .filter((name) => name !== '');

        if (!normalizedNames.length) {
            const fallbackLabel = items.length === 1 ? remainderSingular : remainderPlural;
            return `${items.length} ${fallbackLabel}`;
        }

        if (normalizedNames.length === 1) {
            return normalizedNames[0];
        }

        if (normalizedNames.length === 2) {
            return `${normalizedNames[0]} e ${normalizedNames[1]}`;
        }

        const remaining = normalizedNames.length - 2;
        const remainderLabel = remaining === 1 ? remainderSingular : remainderPlural;
        return `${normalizedNames[0]}, ${normalizedNames[1]} e mais ${remaining} ${remainderLabel}`;
    }

    function resolveFieldPlaceholder(config) {
        if (!config || typeof config !== 'object') {
            return '';
        }

        return (
            config.placeholder
            || config.textarea_placeholder
            || config.email_placeholder
            || config.phone_placeholder
            || ''
        );
    }

    function normalizeTemplateOptions(raw) {
        if (!raw) {
            return [];
        }

        if (Array.isArray(raw)) {
            return raw
                .map((item) => (typeof item === 'string' ? item.trim() : ''))
                .filter((item) => item !== '');
        }

        return String(raw)
            .split(/,|\r\n|\r|\n/)
            .map((item) => item.trim())
            .filter((item) => item !== '');
    }

    function normalizeFileAccept(value) {
        if (!value) {
            return { accept: '', display: '' };
        }

        const items = Array.isArray(value)
            ? value
            : String(value).split(',');

        const normalized = [];
        const display = [];

        items.forEach((item) => {
            if (typeof item !== 'string') {
                return;
            }

            const trimmed = item.trim();

            if (!trimmed) {
                return;
            }

            display.push(trimmed);

            if (trimmed.includes('/')) {
                normalized.push(trimmed);
            } else {
                const normalizedItem = trimmed.startsWith('.') ? trimmed : `.${trimmed}`;
                normalized.push(normalizedItem.toLowerCase());
            }
        });

        return {
            accept: normalized.join(','),
            display: display.join(', '),
        };
    }

    function createTemplateFieldControl(type, fieldId, config, label, index, prefillValue = null) {
        const helpMessages = [];
        const safeConfig = config && typeof config === 'object' ? config : {};
        const placeholder = resolveFieldPlaceholder(safeConfig);
        let element = null;
        let initialValue = '';
        let onAttach = null;

        switch (type) {
            case 'text':
            case 'email':
            case 'phone': {
                const input = document.createElement('input');
                input.type = type === 'phone' ? 'tel' : type === 'email' ? 'email' : 'text';
                input.className = 'form-control vet-prescricao-form__subtle-input';
                input.id = fieldId;
                input.name = `template_fields[${index}]`;
                if (placeholder) {
                    input.placeholder = placeholder;
                }
                if (prefillValue !== null && prefillValue !== undefined) {
                    input.value = normalizeTextValue(prefillValue);
                }
                element = input;
                break;
            }
            case 'textarea': {
                const textarea = document.createElement('textarea');
                textarea.className = 'form-control vet-prescricao-form__subtle-input';
                textarea.id = fieldId;
                textarea.name = `template_fields[${index}]`;
                textarea.rows = 4;
                if (placeholder) {
                    textarea.placeholder = placeholder;
                }
                if (prefillValue !== null && prefillValue !== undefined) {
                    textarea.value = normalizeTextValue(prefillValue);
                }
                element = textarea;
                break;
            }
            case 'number':
            case 'integer': {
                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'form-control vet-prescricao-form__subtle-input';
                input.id = fieldId;
                input.name = `template_fields[${index}]`;
                if (placeholder) {
                    input.placeholder = placeholder;
                }
                if (type === 'integer') {
                    input.step = '1';
                } else {
                    const rawStep = safeConfig.number_step;
                    if (rawStep !== undefined && rawStep !== null && rawStep !== '') {
                        const stepNumber = Number(rawStep);
                        if (!Number.isNaN(stepNumber) && stepNumber > 0) {
                            input.step = String(stepNumber);
                        } else {
                            input.step = 'any';
                        }
                    } else {
                        input.step = 'any';
                    }
                }

                if (safeConfig.number_min !== undefined || safeConfig.integer_min !== undefined) {
                    const minValue = safeConfig.number_min ?? safeConfig.integer_min;
                    if (minValue !== undefined && minValue !== null && minValue !== '') {
                        const minNumber = Number(minValue);
                        if (!Number.isNaN(minNumber)) {
                            input.min = String(minNumber);
                            helpMessages.push(`Valor mínimo: ${minNumber}`);
                        }
                    }
                }

                if (safeConfig.number_max !== undefined || safeConfig.integer_max !== undefined) {
                    const maxValue = safeConfig.number_max ?? safeConfig.integer_max;
                    if (maxValue !== undefined && maxValue !== null && maxValue !== '') {
                        const maxNumber = Number(maxValue);
                        if (!Number.isNaN(maxNumber)) {
                            input.max = String(maxNumber);
                            helpMessages.push(`Valor máximo: ${maxNumber}`);
                        }
                    }
                }

                if (prefillValue !== null && prefillValue !== undefined) {
                    input.value = normalizeTextValue(prefillValue);
                }

                element = input;
                break;
            }
            case 'date': {
                const input = document.createElement('input');
                input.type = 'date';
                input.className = 'form-control vet-prescricao-form__subtle-input';
                input.id = fieldId;
                input.name = `template_fields[${index}]`;
                if (safeConfig.date_hint) {
                    helpMessages.push(String(safeConfig.date_hint));
                }
                if (prefillValue !== null && prefillValue !== undefined) {
                    input.value = normalizeTextValue(prefillValue);
                }
                element = input;
                break;
            }
            case 'time': {
                const input = document.createElement('input');
                input.type = 'time';
                input.className = 'form-control vet-prescricao-form__subtle-input';
                input.id = fieldId;
                input.name = `template_fields[${index}]`;
                if (safeConfig.time_hint) {
                    helpMessages.push(String(safeConfig.time_hint));
                }
                if (prefillValue !== null && prefillValue !== undefined) {
                    input.value = normalizeTextValue(prefillValue);
                }
                element = input;
                break;
            }
            case 'datetime': {
                const input = document.createElement('input');
                input.type = 'datetime-local';
                input.className = 'form-control vet-prescricao-form__subtle-input';
                input.id = fieldId;
                input.name = `template_fields[${index}]`;
                if (safeConfig.datetime_hint) {
                    helpMessages.push(String(safeConfig.datetime_hint));
                }
                if (prefillValue !== null && prefillValue !== undefined) {
                    input.value = normalizeTextValue(prefillValue);
                }
                element = input;
                break;
            }
            case 'select': {
                const options = normalizeTemplateOptions(safeConfig.select_options);
                if (!options.length) {
                    const message = document.createElement('p');
                    message.className = 'text-muted mb-0';
                    message.textContent = 'Nenhuma opção configurada para este campo.';
                    element = message;
                    break;
                }
                const select = document.createElement('select');
                select.className = 'form-select vet-prescricao-form__subtle-input';
                select.id = fieldId;
                select.name = `template_fields[${index}]`;
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = 'Selecione uma opção';
                select.appendChild(placeholderOption);
                const presetValue = normalizeTextValue(prefillValue).trim();
                let matchedPreset = false;
                options.forEach((option) => {
                    const opt = document.createElement('option');
                    opt.value = option;
                    opt.textContent = option;
                    if (presetValue && option === presetValue) {
                        opt.selected = true;
                        matchedPreset = true;
                    }
                    select.appendChild(opt);
                });
                if (presetValue && !matchedPreset) {
                    const customOption = document.createElement('option');
                    customOption.value = presetValue;
                    customOption.textContent = presetValue;
                    customOption.selected = true;
                    customOption.setAttribute('data-custom-option', 'true');
                    select.appendChild(customOption);
                }
                initialValue = presetValue;
                element = select;
                break;
            }
            case 'multi_select': {
                const options = normalizeTemplateOptions(safeConfig.multi_select_options ?? safeConfig.select_options);
                if (!options.length) {
                    const message = document.createElement('p');
                    message.className = 'text-muted mb-0';
                    message.textContent = 'Nenhuma opção configurada para este campo.';
                    element = message;
                    break;
                }
                const select = document.createElement('select');
                select.className = 'form-select select2 vet-prescricao-form__subtle-input';
                select.id = fieldId;
                select.name = `template_fields[${index}][]`;
                select.multiple = true;
                select.setAttribute('data-width', '100%');
                select.dataset.role = 'template-select-enhanced';
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.disabled = true;
                placeholderOption.hidden = true;
                const presetValues = normalizeArrayValue(prefillValue);
                const unmatched = new Set(presetValues);
                select.appendChild(placeholderOption);
                options.forEach((option) => {
                    const opt = document.createElement('option');
                    opt.value = option;
                    opt.textContent = option;
                    if (presetValues.includes(option)) {
                        opt.selected = true;
                        unmatched.delete(option);
                    }
                    select.appendChild(opt);
                });
                unmatched.forEach((value) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = value;
                    opt.selected = true;
                    opt.setAttribute('data-custom-option', 'true');
                    select.appendChild(opt);
                });
                helpMessages.push('Selecione uma ou mais opções. Digite para filtrar rapidamente.');
                initialValue = presetValues;
                element = select;
                break;
            }
            case 'checkbox': {
                const wrapper = document.createElement('div');
                wrapper.className = 'form-check';
                const input = document.createElement('input');
                input.className = 'form-check-input';
                input.type = 'checkbox';
                input.id = fieldId;
                input.name = `template_fields[${index}]`;
                input.value = '1';
                const defaultChecked = safeConfig.checkbox_default === 'checked';
                const presetChecked = isCheckedValue(prefillValue);
                input.checked = presetChecked || (!prefillValue && defaultChecked);

                if (defaultChecked) {
                    helpMessages.push('Marcado por padrão.');
                } else {
                    helpMessages.push('Desmarcado por padrão.');
                }
                const checkboxLabel = document.createElement('label');
                checkboxLabel.className = 'form-check-label';
                checkboxLabel.setAttribute('for', fieldId);
                checkboxLabel.textContent = safeConfig.checkbox_label_checked || label;
                wrapper.appendChild(input);
                wrapper.appendChild(checkboxLabel);
                if (safeConfig.checkbox_label_unchecked) {
                    helpMessages.push(`Quando desmarcado: ${safeConfig.checkbox_label_unchecked}`);
                }
                initialValue = input.checked ? '1' : '0';
                element = wrapper;
                break;
            }
            case 'checkbox_group': {
                const options = normalizeTemplateOptions(safeConfig.checkbox_group_options);
                if (!options.length) {
                    const message = document.createElement('p');
                    message.className = 'text-muted mb-0';
                    message.textContent = 'Nenhuma opção configurada para este campo.';
                    element = message;
                    break;
                }
                const list = document.createElement('div');
                list.className = 'd-flex flex-column gap-2';
                options.forEach((option, optionIndex) => {
                    const optionId = `${fieldId}-checkbox-${optionIndex}`;
                    const item = document.createElement('div');
                    item.className = 'form-check';
                    const input = document.createElement('input');
                    input.className = 'form-check-input';
                    input.type = 'checkbox';
                    input.id = optionId;
                    input.name = `template_fields[${index}][]`;
                    input.value = option;
                    const selectedValues = normalizeArrayValue(prefillValue);
                    if (selectedValues.includes(option)) {
                        input.checked = true;
                    }
                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'form-check-label';
                    optionLabel.setAttribute('for', optionId);
                    optionLabel.textContent = option;
                    item.appendChild(input);
                    item.appendChild(optionLabel);
                    list.appendChild(item);
                });
                initialValue = normalizeArrayValue(prefillValue);
                element = list;
                break;
            }
            case 'radio_group': {
                const options = normalizeTemplateOptions(safeConfig.radio_group_options);
                if (!options.length) {
                    const message = document.createElement('p');
                    message.className = 'text-muted mb-0';
                    message.textContent = 'Nenhuma opção configurada para este campo.';
                    element = message;
                    break;
                }
                const list = document.createElement('div');
                list.className = 'd-flex flex-column gap-2';
                const presetValue = normalizeTextValue(prefillValue).trim();
                const defaultValue = presetValue !== ''
                    ? presetValue
                    : (typeof safeConfig.radio_group_default === 'string'
                        ? safeConfig.radio_group_default.trim()
                        : '');
                options.forEach((option, optionIndex) => {
                    const optionId = `${fieldId}-radio-${optionIndex}`;
                    const item = document.createElement('div');
                    item.className = 'form-check';
                    const input = document.createElement('input');
                    input.className = 'form-check-input';
                    input.type = 'radio';
                    input.id = optionId;
                    input.name = `template_fields[${index}]`;
                    input.value = option;
                    if (defaultValue && option === defaultValue) {
                        input.checked = true;
                    }
                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'form-check-label';
                    optionLabel.setAttribute('for', optionId);
                    optionLabel.textContent = option;
                    item.appendChild(input);
                    item.appendChild(optionLabel);
                    list.appendChild(item);
                });
                initialValue = defaultValue;
                element = list;
                break;
            }
            case 'file': {
                const input = document.createElement('input');
                input.type = 'file';
                input.className = 'form-control';
                input.id = fieldId;
                input.name = `template_fields[${index}]`;
                const { accept, display } = normalizeFileAccept(safeConfig.file_types);
                if (accept) {
                    input.setAttribute('accept', accept);
                    if (display) {
                        helpMessages.push(`Tipos permitidos: ${display}`);
                    }
                }
                if (safeConfig.file_max_size) {
                    helpMessages.push(`Tamanho máximo: ${safeConfig.file_max_size} MB`);
                }
                element = input;
                break;
            }
            case 'rich_text': {
                const textarea = document.createElement('textarea');
                textarea.className = 'vet-prescricao-form__rich-text-editor';
                textarea.id = fieldId;
                textarea.name = `template_fields[${index}]`;
                textarea.rows = 8;
                textarea.placeholder = 'Escreva um texto padrão ou deixe em branco';

                const defaultContent = typeof safeConfig.rich_text_default === 'string'
                    ? safeConfig.rich_text_default.trim()
                    : '';
                const presetContent = normalizeTextValue(prefillValue).trim();

                if (presetContent !== '') {
                    initialValue = presetContent;
                } else if (defaultContent) {
                    initialValue = defaultContent;
                } else {
                    initialValue = '';
                }

                if (initialValue) {
                    textarea.value = initialValue;
                }

                element = textarea;
                onAttach = (parent) => {
                    setTimeout(() => {
                        if (parent && !parent.contains(textarea)) {
                            return;
                        }

                        if (!textarea.isConnected) {
                            return;
                        }

                        initRichTextEditor(textarea, initialValue);
                    }, 0);
                };
                helpMessages.push('Campo com suporte a formatação rica. Ajuste o texto conforme necessário.');
                break;
            }
            default: {
                const message = document.createElement('p');
                message.className = 'text-muted mb-0';
                message.textContent = 'Tipo de campo não suportado para visualização.';
                element = message;
            }
        }

        return {
            element,
            help: helpMessages,
            initialValue,
            type,
            onAttach,
        };
    }

    function createTemplateFieldElement(field, index, overrideValue) {
        const safeField = field && typeof field === 'object' ? field : {};
        const type = typeof safeField.type === 'string' ? safeField.type : 'text';
        const label = typeof safeField.label === 'string' && safeField.label.trim() !== ''
            ? safeField.label.trim()
            : `Campo ${index + 1}`;
        const typeLabel = typeof safeField.type_label === 'string' && safeField.type_label.trim() !== ''
            ? safeField.type_label.trim()
            : '';
        const config = safeField.config && typeof safeField.config === 'object' ? safeField.config : {};
        const fieldId = `vetPrescriptionDynamicField-${index}-${type}`;

        const container = document.createElement('div');
        container.className = 'vet-prescricao-form__dynamic-field';

        const header = document.createElement('div');
        header.className = 'd-flex justify-content-between align-items-start mb-2';

        const title = document.createElement('span');
        title.className = 'fw-semibold text-secondary';
        title.textContent = label;
        header.appendChild(title);

        if (typeLabel) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-light text-secondary';
            badge.textContent = typeLabel;
        header.appendChild(badge);
        }

        container.appendChild(header);

        const effectivePrefill = overrideValue !== undefined ? overrideValue : (safeField.prefill_value ?? null);
        const control = createTemplateFieldControl(type, fieldId, config, label, index, effectivePrefill);

        if (!control || !control.element) {
            const message = document.createElement('p');
            message.className = 'text-muted mb-0';
            message.textContent = 'Não foi possível exibir este campo.';
            container.appendChild(message);
            return container;
        }

        container.appendChild(control.element);

        if (typeof control.onAttach === 'function') {
            control.onAttach(container);
        }

        if (Array.isArray(control.help) && control.help.length) {
            control.help.forEach((message) => {
                if (!message) {
                    return;
                }
                const help = document.createElement('small');
                help.className = 'text-muted d-block mt-1';
                help.textContent = message;
                container.appendChild(help);
            });
        }

        return container;
    }

    function renderTemplateFields(template) {
        if (!templateFieldsWrapper) {
            return;
        }

        destroyRichTextEditors(templateFieldsWrapper);
        destroyTemplateSelectEnhancements(templateFieldsWrapper);
        clearElement(templateFieldsWrapper);

        if (!template) {
            renderListPlaceholder(templateFieldsWrapper, 'Selecione um modelo de prescrição para visualizar os campos configurados.');
            return;
        }

        const fields = Array.isArray(template.fields) ? template.fields : [];

        if (!fields.length) {
            renderListPlaceholder(templateFieldsWrapper, 'Nenhum campo configurado para este modelo.');
            return;
        }

        logDebug('Rendering dynamic fields for template', {
            templateId: template.id || null,
            fieldsCount: fields.length,
        });

        const overrides = getTemplateFieldOverridesForTemplate(template);

        fields.forEach((field, index) => {
            const overrideValue = resolveTemplateFieldOverride(overrides, field, index);
            const element = createTemplateFieldElement(field, index, overrideValue);
            if (element) {
                templateFieldsWrapper.appendChild(element);
            }
        });

        enhanceTemplateSelectFields(templateFieldsWrapper);
    }

    function normalizeCatalogItem(item) {
        if (!item) {
            return null;
        }

        if (typeof item === 'string') {
            const trimmed = item.trim();
            return trimmed ? { id: null, name: trimmed } : null;
        }

        if (typeof item === 'object') {
            const rawId = item.id ?? item.value ?? item.slug ?? null;
            const id = rawId !== undefined && rawId !== null && rawId !== '' ? String(rawId) : null;
            const rawName = item.name ?? item.label ?? item.title ?? item.descricao ?? '';
            const name = typeof rawName === 'string' ? rawName.trim() : String(rawName || '').trim();

            if (!name) {
                return null;
            }

            return { id, name };
        }

        return null;
    }

    function cloneTagItems(items) {
        return items.map((item) => ({
            id: item.id !== undefined && item.id !== null && item.id !== '' ? String(item.id) : null,
            name: item.name,
        }));
    }

    function hasListItem(list, candidate) {
        if (!candidate) {
            return false;
        }

        return list.some((item) => {
            if (candidate.id && item.id && isSameId(item.id, candidate.id)) {
                return true;
            }

            return item.name.toLowerCase() === candidate.name.toLowerCase();
        });
    }

    function normalizePatientItems(items) {
        if (!Array.isArray(items)) {
            return [];
        }

        const normalized = [];

        items.forEach((item) => {
            const normalizedItem = normalizeCatalogItem(item);
            if (normalizedItem && !hasListItem(normalized, normalizedItem)) {
                normalized.push(normalizedItem);
            }
        });

        return normalized;
    }

    function updatePatientOptionDataset(patientId, patientData) {
        if (!patientSelect || !patientId) {
            return;
        }

        const option = Array.from(patientSelect.options).find((item) => isSameId(item.value, patientId));
        const source = patientData || findPatient(patientId);

        if (!option || !source) {
            return;
        }

        try {
            option.dataset.patient = JSON.stringify(source);
        } catch (error) {
            logDebug('Failed to update patient dataset payload', { error, source });
        }
    }

    function persistCurrentPatientItems(key, values) {
        if (!currentPatientId) {
            return;
        }

        const patient = findPatient(currentPatientId);
        if (!patient) {
            return;
        }

        patient[key] = cloneTagItems(values);
        updatePatientOptionDataset(currentPatientId, patient);
    }

    function resetSelectValue(selectElement) {
        if (selectElement) {
            selectElement.value = '';
        }
    }

    function buildPayloadFromOption(option) {
        if (!option) {
            return null;
        }

        return normalizeCatalogItem({
            id: option.value,
            name: option.getAttribute('data-name') || option.textContent,
        });
    }

    function updateAllergiesSummary() {
        if (!allergiesSummary) {
            return;
        }

        allergiesSummary.textContent = buildAssignmentSummary(currentAllergies, {
            emptyMessage: 'Nenhuma alergia registrada.',
            remainderSingular: 'alergia',
            remainderPlural: 'alergias',
        });
    }

    function updateConditionsSummary() {
        if (!conditionsSummary) {
            return;
        }

        conditionsSummary.textContent = buildAssignmentSummary(currentConditions, {
            emptyMessage: 'Sem registros recentes.',
            remainderSingular: 'condição crônica',
            remainderPlural: 'condições crônicas',
        });
    }

    function renderAllergiesList() {
        updateAllergiesSummary();

        if (!allergiesListContainer) {
            return;
        }

        clearElement(allergiesListContainer);

        if (!currentAllergies.length) {
            renderListPlaceholder(allergiesListContainer, 'Nenhuma alergia registrada.');
            return;
        }

        currentAllergies.forEach((item, index) => {
            allergiesListContainer.appendChild(
                createTag(item.name, 'ri-alert-line', {
                    removable: true,
                    onRemove: () => removeAllergy(index),
                }),
            );
        });
    }

    function renderConditionsList() {
        updateConditionsSummary();

        if (!conditionsListContainer) {
            return;
        }

        clearElement(conditionsListContainer);

        if (!currentConditions.length) {
            renderListPlaceholder(conditionsListContainer, 'Sem registros recentes.');
            return;
        }

        currentConditions.forEach((item, index) => {
            conditionsListContainer.appendChild(
                createTag(item.name, 'ri-heart-pulse-line', {
                    removable: true,
                    onRemove: () => removeCondition(index),
                }),
            );
        });
    }

    function syncAssignmentFields() {
        if (allergiesField) {
            allergiesField.value = JSON.stringify(cloneTagItems(currentAllergies));
        }

        if (conditionsField) {
            conditionsField.value = JSON.stringify(cloneTagItems(currentConditions));
        }
    }

    function updateAssignmentControlsState() {
        const hasAllergyOptions = allergiesCatalog.length > 0;
        const hasConditionOptions = chronicConditionsCatalog.length > 0;
        const disableAllergies = !currentPatientId || !hasAllergyOptions;
        const disableConditions = !currentPatientId || !hasConditionOptions;

        if (allergySelect) {
            allergySelect.disabled = disableAllergies;
            if (disableAllergies) {
                resetSelectValue(allergySelect);
            }
        }

        if (addAllergyButton) {
            addAllergyButton.disabled = disableAllergies;
        }

        if (conditionSelect) {
            conditionSelect.disabled = disableConditions;
            if (disableConditions) {
                resetSelectValue(conditionSelect);
            }
        }

        if (addConditionButton) {
            addConditionButton.disabled = disableConditions;
        }
    }

    function removeAllergy(index) {
        currentAllergies = currentAllergies.filter((_, itemIndex) => itemIndex !== index);
        persistCurrentPatientItems('allergies', currentAllergies);
        renderAllergiesList();
        syncAssignmentFields();
        logDebug('Removed allergy from patient list', {
            patientId: currentPatientId,
            remaining: currentAllergies.length,
        });
    }

    function removeCondition(index) {
        currentConditions = currentConditions.filter((_, itemIndex) => itemIndex !== index);
        persistCurrentPatientItems('conditions', currentConditions);
        renderConditionsList();
        syncAssignmentFields();
        logDebug('Removed condition from patient list', {
            patientId: currentPatientId,
            remaining: currentConditions.length,
        });
    }

    function handleAddAllergy() {
        if (!allergySelect || !currentPatientId) {
            return;
        }

        const option = allergySelect.options[allergySelect.selectedIndex];
        if (!option || !option.value) {
            return;
        }
        const payload = buildPayloadFromOption(option);

        if (!payload) {
            return;
        }

        if (hasListItem(currentAllergies, payload)) {
            resetSelectValue(allergySelect);
            return;
        }

        currentAllergies = currentAllergies.concat([payload]);
        persistCurrentPatientItems('allergies', currentAllergies);
        renderAllergiesList();
        syncAssignmentFields();
        resetSelectValue(allergySelect);

        logDebug('Added allergy to patient list', {
            patientId: currentPatientId,
            allergy: payload,
        });
    }

    function handleAddCondition() {
        if (!conditionSelect || !currentPatientId) {
            return;
        }

        const option = conditionSelect.options[conditionSelect.selectedIndex];
        if (!option || !option.value) {
            return;
        }
        const payload = buildPayloadFromOption(option);

        if (!payload) {
            return;
        }

        if (hasListItem(currentConditions, payload)) {
            resetSelectValue(conditionSelect);
            return;
        }

        currentConditions = currentConditions.concat([payload]);
        persistCurrentPatientItems('conditions', currentConditions);
        renderConditionsList();
        syncAssignmentFields();
        resetSelectValue(conditionSelect);

        logDebug('Added chronic condition to patient list', {
            patientId: currentPatientId,
            condition: payload,
        });
    }

    function updateVeterinarianAvailabilityLabel() {
        if (!veterinarianSelect || !veterinarianAvailability) {
            return;
        }
        const selectedOption = veterinarianSelect.options[veterinarianSelect.selectedIndex];
        const availability = selectedOption ? selectedOption.getAttribute('data-availability') : '';
        veterinarianAvailability.textContent = availability || '—';
    }

    function updatePatientDetails(patient) {
        const safePatient = patient || {};
        const normalizedPatientId = safePatient.id ? normalizeId(safePatient.id) : null;
        const hasPatientSelection = Boolean(normalizedPatientId);
        const notesValue = normalizeTextValue(safePatient.notes);

        const photoFallback = (patientPhoto && patientPhoto.dataset && patientPhoto.dataset.defaultPhoto)
            ? patientPhoto.dataset.defaultPhoto
            : defaultPatientPhoto;

        const resolvedPhoto = safePatient.photo_url || safePatient.photo || photoFallback || '';

        logDebug('Updating patient details', {
            selectedId: safePatient.id || null,
            patient: safePatient,
        });

        if (tutorName) tutorName.textContent = safePatient.tutor || '—';
        if (tutorDocument) tutorDocument.textContent = safePatient.tutor_document || '—';
        if (tutorContact) tutorContact.textContent = safePatient.contact || '—';
        if (tutorEmail) tutorEmail.textContent = safePatient.email || '—';
        if (tutorAddress) tutorAddress.textContent = safePatient.tutor_address || '—';
        if (microchip) microchip.textContent = safePatient.microchip || '—';
        if (lastVisit) lastVisit.textContent = safePatient.last_visit || '—';
        if (lastExam) lastExam.textContent = safePatient.last_exam || '—';
        if (weight) weight.textContent = safePatient.weight || '—';
        if (behavior) behavior.textContent = safePatient.behavior || '—';
        if (notesField) notesField.value = notesValue;

        if (patientPhoto && resolvedPhoto) {
            patientPhoto.src = resolvedPhoto;
            if (hasPatientSelection && safePatient.name) {
                patientPhoto.alt = `Foto de ${safePatient.name}`;
            } else {
                patientPhoto.alt = 'Foto do paciente';
            }
        }

        if (patientName) {
            patientName.textContent = hasPatientSelection ? (safePatient.name || '—') : 'Selecione um paciente';
        }

        if (patientDetails) {
            const metaParts = [safePatient.species, safePatient.breed, safePatient.age].filter(Boolean);
            if (hasPatientSelection) {
                patientDetails.textContent = metaParts.length ? metaParts.join(' • ') : '—';
            } else {
                patientDetails.textContent = 'As informações do paciente aparecerão após a seleção.';
            }
        }

        if (patientSummaryWeight) patientSummaryWeight.textContent = safePatient.weight || '—';
        if (patientSummarySex) patientSummarySex.textContent = safePatient.sex || '—';
        if (patientSummaryBirthDate) patientSummaryBirthDate.textContent = safePatient.birth_date || '—';
        if (patientSummaryLastVisit) patientSummaryLastVisit.textContent = safePatient.last_visit || '—';
        if (patientSummarySize) patientSummarySize.textContent = safePatient.size || '—';
        if (patientSummaryOrigin) patientSummaryOrigin.textContent = safePatient.origin || '—';
        if (patientSummaryMicrochip) patientSummaryMicrochip.textContent = safePatient.microchip || '—';
        if (patientSummaryPedigree) patientSummaryPedigree.textContent = safePatient.pedigree || '—';

        if (patientNotesText) {
            patientNotesText.textContent = notesValue !== '' ? notesValue : defaultPatientNotesMessage;
        }

        if (patientWeightChip) patientWeightChip.textContent = safePatient.weight || '—';
        if (patientAgeChip) patientAgeChip.textContent = safePatient.age || '—';
        if (patientBehavior) patientBehavior.textContent = safePatient.behavior || '—';
        if (patientDiet) patientDiet.textContent = safePatient.diet || '—';

        if (tutorSummaryName) tutorSummaryName.textContent = safePatient.tutor || '—';
        if (tutorSummaryDocument) tutorSummaryDocument.textContent = safePatient.tutor_document || '—';
        if (tutorSummaryContacts) tutorSummaryContacts.textContent = safePatient.contact || '—';
        if (tutorSummaryEmail) tutorSummaryEmail.textContent = safePatient.email || '—';
        if (tutorSummaryAddress) tutorSummaryAddress.textContent = safePatient.tutor_address || '—';

        if (tutorCardName) tutorCardName.textContent = safePatient.tutor || '—';
        if (tutorCardDocument) tutorCardDocument.textContent = safePatient.tutor_document || '—';
        if (tutorCardContact) tutorCardContact.textContent = safePatient.contact || '—';
        if (tutorCardEmail) tutorCardEmail.textContent = safePatient.email || '—';
        if (tutorCardAddress) tutorCardAddress.textContent = safePatient.tutor_address || '—';

        const normalizedAllergies = normalizePatientItems(safePatient.allergies);
        const normalizedConditions = normalizePatientItems(safePatient.conditions);

        currentPatientId = normalizedPatientId || null;
        currentAllergies = normalizedAllergies;
        currentConditions = normalizedConditions;

        if (safePatient) {
            safePatient.allergies = cloneTagItems(currentAllergies);
            safePatient.conditions = cloneTagItems(currentConditions);
        }

        if (currentPatientId) {
            const patientFromData = findPatient(currentPatientId);
            if (patientFromData) {
                patientFromData.allergies = cloneTagItems(currentAllergies);
                patientFromData.conditions = cloneTagItems(currentConditions);
                updatePatientOptionDataset(currentPatientId, patientFromData);
            }
        }

        renderAllergiesList();
        renderConditionsList();
        updateAssignmentControlsState();
        syncAssignmentFields();

        if (vitalsWrapper) {
            clearElement(vitalsWrapper);
            const vitals = Array.isArray(safePatient.vitals) ? safePatient.vitals : [];
            if (vitals.length) {
                vitals.forEach((vital) => {
                    const column = document.createElement('div');
                    column.className = 'col-md-4';
                    const card = document.createElement('div');
                    card.className = 'vet-prescricao-form__indicator-card py-3 h-100';
                    const label = document.createElement('span');
                    label.className = 'text-muted small d-block';
                    label.textContent = vital.label;
                    const value = document.createElement('strong');
                    value.textContent = vital.value;
                    card.appendChild(label);
                    card.appendChild(value);
                    column.appendChild(card);
                    vitalsWrapper.appendChild(column);
                });
            }
        }
    }

    function renderObjectives(template) {
        if (!objectivesList) {
            return;
        }
        clearElement(objectivesList);
        const objectives = template && Array.isArray(template.objectives) ? template.objectives : [];
        if (objectives.length) {
            objectives.forEach((objective) => {
                const li = document.createElement('li');
                li.className = 'mb-1';
                li.textContent = objective;
                objectivesList.appendChild(li);
            });
        }
    }

    function renderMonitoring(template) {
        if (!monitoringList) {
            return;
        }
        clearElement(monitoringList);
        const monitoring = template && Array.isArray(template.monitoring) ? template.monitoring : [];
        if (monitoring.length) {
            monitoring.forEach((item) => {
                const li = document.createElement('li');
                li.className = 'mb-1';
                li.textContent = item;
                monitoringList.appendChild(li);
            });
        }
    }

    function normalizeTemplateMedication(raw) {
        const safe = raw && typeof raw === 'object' ? raw : {};
        const medicationId = normalizeId(
            safe.medication_id
            ?? safe.id
            ?? safe.catalog_id
            ?? safe.medicine_id
            ?? ''
        );

        const catalogItem = medicationId ? findMedicationByCatalogId(medicationId) : null;
        const label = normalizeTextValue(safe.label) || normalizeTextValue(catalogItem ? catalogItem.label : '');
        const name = normalizeTextValue(safe.name);

        return {
            id: medicationId,
            name: name || label || normalizeTextValue(catalogItem ? catalogItem.name : ''),
            label,
            dosage: normalizeTextValue(safe.dosage),
            frequency: normalizeTextValue(safe.frequency),
            duration: normalizeTextValue(safe.duration),
            route: normalizeTextValue(safe.route || safe.via),
            notes: normalizeTextValue(safe.notes || safe.observations),
        };
    }

    function hideMedicationsPlaceholder() {
        if (!medicationsList) {
            return;
        }

        const placeholder = medicationsList.querySelector('.vet-prescricao-form__list-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
    }

    function ensureMedicationsPlaceholder() {
        if (!medicationsList) {
            return;
        }

        const hasItems = medicationsList.querySelector('.vet-prescricao-form__medication-item');
        const placeholder = medicationsList.querySelector('.vet-prescricao-form__list-placeholder');

        if (hasItems && placeholder) {
            placeholder.remove();
            return;
        }

        if (!hasItems && !placeholder) {
            renderListPlaceholder(medicationsList, 'Nenhum medicamento configurado para este modelo.');
        }
    }

    function collectMedicationsData() {
        if (!medicationsList) {
            return [];
        }

        const items = medicationsList.querySelectorAll('.vet-prescricao-form__medication-item');

        return Array.from(items)
            .map((item) => {
                const select = item.querySelector('select[data-role="medication-select"]');
                const selectedId = select ? normalizeId(select.value) : '';
                const selectedOption = select && select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
                const optionLabel = selectedOption ? normalizeTextValue(selectedOption.textContent).trim() : '';
                const fallbackLabel = select ? normalizeTextValue(select.getAttribute('data-initial-label') || '') : '';

                const payload = {
                    medication_id: selectedId !== '' ? selectedId : null,
                    name: (optionLabel || fallbackLabel) ? normalizeTextValue(optionLabel || fallbackLabel).trim() : null,
                    dosage: null,
                    frequency: null,
                    duration: null,
                    route: null,
                    notes: null,
                };

                item.querySelectorAll('[data-medication-field]').forEach((field) => {
                    const fieldName = field.getAttribute('data-medication-field');
                    if (!fieldName) {
                        return;
                    }

                    const value = normalizeTextValue(field.value).trim();
                    payload[fieldName] = value !== '' ? value : null;
                });

                const hasData = payload.medication_id
                    || payload.name
                    || payload.dosage
                    || payload.frequency
                    || payload.duration
                    || payload.route
                    || payload.notes;

                return hasData ? payload : null;
            })
            .filter((item) => item !== null);
    }

    function syncMedicationsField() {
        if (!medicationsField) {
            return;
        }

        const medications = collectMedicationsData();
        medicationsField.value = JSON.stringify(medications);
    }

    function collectActiveChannels() {
        if (!channelsWrapper) {
            return [];
        }

        const active = channelsWrapper.querySelectorAll('.vet-prescricao-form__tag.vet-prescricao-form__tag--active');

        return Array.from(active)
            .map((tag) => {
                const channelId = normalizeTextValue(tag.getAttribute('data-channel')).trim();
                if (channelId !== '') {
                    return channelId;
                }

                return normalizeTextValue(tag.textContent).trim();
            })
            .filter((value) => value !== '');
    }

    function syncChannelsField() {
        if (!channelsField) {
            return;
        }

        const channels = collectActiveChannels();
        channelsField.value = JSON.stringify(channels);
    }

    function applyInitialChannelSelection(initialChannels) {
        if (!channelsWrapper || !Array.isArray(initialChannels) || !initialChannels.length) {
            return;
        }

        const tags = Array.from(channelsWrapper.querySelectorAll('.vet-prescricao-form__tag'));

        initialChannels.forEach((channel) => {
            const normalized = normalizeTextValue(channel).trim();
            if (normalized === '') {
                return;
            }

            const match = tags.find((tag) => {
                const datasetValue = normalizeTextValue(tag.getAttribute('data-channel')).trim();
                if (datasetValue !== '') {
                    return datasetValue === normalized;
                }

                return normalizeTextValue(tag.textContent).trim() === normalized;
            });

            if (match && !match.classList.contains('vet-prescricao-form__tag--active')) {
                match.classList.add('vet-prescricao-form__tag--active');
                const icon = match.querySelector('i');
                if (icon) {
                    icon.className = 'ri-checkbox-circle-line';
                }
            }
        });
    }

    function handleFormSubmit() {
        syncAssignmentFields();
        syncMedicationsField();
        syncChannelsField();
        syncAttachmentInputs();

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
            submitButton.setAttribute('aria-disabled', 'true');
        }
    }

    function createMedicationCard(rawMedication) {
        const medication = normalizeTemplateMedication(rawMedication);
        const wrapper = document.createElement('div');
        wrapper.className = 'vet-prescricao-form__medication-item p-3';

        const header = document.createElement('div');
        header.className = 'd-flex justify-content-between align-items-start mb-2';

        const titleWrapper = document.createElement('div');
        const infoLabel = document.createElement('span');
        infoLabel.className = 'vet-prescricao-form__info-label d-block';
        infoLabel.textContent = 'Medicamento';
        const medicationSelect = document.createElement('select');
        medicationSelect.className = 'form-select vet-prescricao-form__subtle-input vet-prescricao-form__medication-select';
        medicationSelect.dataset.role = 'medication-select';
        medicationSelect.dataset.placeholder = 'Selecione o medicamento';
        medicationSelect.dataset.allowClear = 'true';

        const fallbackLabel = normalizeTextValue(medication.label) || normalizeTextValue(medication.name);
        const initialCatalogItem = medication.id
            ? findMedicationByCatalogId(medication.id)
            : resolveMedicationCatalogItem(fallbackLabel, medication.id);
        const initialCatalogId = initialCatalogItem ? normalizeId(initialCatalogItem.id) : normalizeId(medication.id);

        populateMedicationSelect(medicationSelect, initialCatalogId, fallbackLabel);

        titleWrapper.appendChild(infoLabel);
        titleWrapper.appendChild(medicationSelect);

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-sm btn-link text-danger vetPrescriptionRemoveMedication';
        removeButton.innerHTML = '<i class="ri-delete-bin-line"></i>';
        removeButton.addEventListener('click', () => {
            wrapper.remove();
            ensureMedicationsPlaceholder();
            syncMedicationsField();
        });

        header.appendChild(titleWrapper);
        header.appendChild(removeButton);
        wrapper.appendChild(header);

        const grid = document.createElement('div');
        grid.className = 'row g-3';

        function createTextControl(labelText, value, fieldName, columnClass = 'col-md-3') {
            const column = document.createElement('div');
            column.className = columnClass;
            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = labelText;
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control vet-prescricao-form__subtle-input';
            input.value = value || '';
            input.dataset.autoFilled = 'false';
            if (fieldName) {
                input.setAttribute('data-medication-field', fieldName);
            }
            input.addEventListener('input', () => {
                input.dataset.autoFilled = 'false';
                syncMedicationsField();
            });
            input.addEventListener('change', () => {
                input.dataset.autoFilled = 'false';
                syncMedicationsField();
            });
            column.appendChild(label);
            column.appendChild(input);
            grid.appendChild(column);
            return input;
        }

        const dosageInput = createTextControl('Dosagem', medication.dosage, 'dosage');
        const frequencyInput = createTextControl('Frequência', medication.frequency, 'frequency');
        const durationInput = createTextControl('Duração', medication.duration, 'duration');
        const routeInput = createTextControl('Via', medication.route, 'route');

        const notesColumn = document.createElement('div');
        notesColumn.className = 'col-12';
        const notesLabel = document.createElement('label');
        notesLabel.className = 'form-label';
        notesLabel.textContent = 'Observações ao tutor';
        const notesTextarea = document.createElement('textarea');
        notesTextarea.className = 'form-control vet-prescricao-form__subtle-input';
        notesTextarea.rows = 2;
        notesTextarea.value = medication.notes || '';
        notesTextarea.dataset.autoFilled = 'false';
        notesTextarea.setAttribute('data-medication-field', 'notes');
        notesTextarea.addEventListener('input', () => {
            notesTextarea.dataset.autoFilled = 'false';
            syncMedicationsField();
        });
        notesTextarea.addEventListener('change', () => {
            notesTextarea.dataset.autoFilled = 'false';
            syncMedicationsField();
        });
        notesColumn.appendChild(notesLabel);
        notesColumn.appendChild(notesTextarea);
        grid.appendChild(notesColumn);

        wrapper.appendChild(grid);

        function applyAutoValue(control, value, override) {
            if (!control) {
                return;
            }

            const normalizedValue = normalizeTextValue(value);
            const existingValue = normalizeTextValue(control.value);
            const shouldOverride = override || existingValue === '' || control.dataset.autoFilled === 'true';

            if (!shouldOverride && normalizedValue !== '') {
                return;
            }

            if (normalizedValue !== '') {
                control.value = normalizedValue;
                control.dataset.autoFilled = 'true';
            } else if (control.dataset.autoFilled === 'true') {
                control.value = '';
            }
        }

        function removeCustomMedicationOptions() {
            const customOptions = medicationSelect.querySelectorAll('option[data-custom-option="true"]');
            customOptions.forEach((option) => option.remove());
        }

        let isProgrammaticMedicationChange = false;

        function applyCatalogData(catalogItem, options = {}) {
            if (!catalogItem) {
                return;
            }

            const overrideFields = options.overrideFields === true;
            const catalogId = normalizeId(catalogItem.id);

            if (catalogId) {
                removeCustomMedicationOptions();
                isProgrammaticMedicationChange = true;
                setMedicationSelectValue(medicationSelect, catalogId, { triggerChange: false });
                isProgrammaticMedicationChange = false;
            }

            applyAutoValue(dosageInput, catalogItem.dosage, overrideFields);
            applyAutoValue(frequencyInput, catalogItem.frequency, overrideFields);
            applyAutoValue(durationInput, catalogItem.duration, overrideFields);
            applyAutoValue(routeInput, catalogItem.route, overrideFields);
            applyAutoValue(notesTextarea, catalogItem.notes, overrideFields);
        }

        function handleMedicationSelection(options = {}) {
            const selectedId = normalizeId(medicationSelect.value);
            const catalogItem = selectedId ? findMedicationByCatalogId(selectedId) : null;
            if (catalogItem) {
                applyCatalogData(catalogItem, options);
            }
            syncMedicationsField();
        }

        medicationSelect.addEventListener('change', () => {
            if (isProgrammaticMedicationChange) {
                return;
            }
            handleMedicationSelection({ overrideFields: true });
        });
        medicationSelect.addEventListener('change', syncMedicationsField);

        if (window.jQuery && window.jQuery.fn) {
            const $medicationSelect = window.jQuery(medicationSelect);
            initializeMedicationSelects(medicationSelect.closest('.vet-prescricao-form__medication-item'));

            let select2Instance = $medicationSelect.data('select2');
            if (!select2Instance) {
                initializeMedicationSelects(medicationSelect.closest('.vet-prescricao-form__medication-item'));
                select2Instance = $medicationSelect.data('select2');
            }

            if (select2Instance) {
                $medicationSelect.on('select2:select', () => {
                    if (isProgrammaticMedicationChange) {
                        return;
                    }
                    handleMedicationSelection({ overrideFields: true });
                    syncMedicationsField();
                });

                $medicationSelect.on('select2:clear', () => {
                    if (isProgrammaticMedicationChange) {
                        return;
                    }
                    handleMedicationSelection({ overrideFields: true });
                    syncMedicationsField();
                });
            }
        }

        if (initialCatalogItem) {
            applyCatalogData(initialCatalogItem, { overrideFields: false });
        }

        return wrapper;
    }

    function appendMedicationCard(medication) {
        if (!medicationsList) {
            return;
        }

        const card = createMedicationCard(medication);
        medicationsList.appendChild(card);
        initializeMedicationSelects(card);
        syncMedicationsField();
    }

    function renderMedications(template) {
        if (!medicationsList) {
            return;
        }
        clearElement(medicationsList);
        const medications = template && Array.isArray(template.medications) ? template.medications : [];
        if (medications.length) {
            medications.forEach((medication) => {
                appendMedicationCard(medication);
            });
        } else {
            renderListPlaceholder(medicationsList, 'Nenhum medicamento configurado para este modelo.');
        }

        initializeMedicationSelects(medicationsList);
        ensureMedicationsPlaceholder();
        syncMedicationsField();
    }

    function renderAlerts(template) {
        if (!alertsWrapper) {
            return;
        }
        clearElement(alertsWrapper);
        const alerts = template && Array.isArray(template.alerts) ? template.alerts : [];
        if (alerts.length) {
            alerts.forEach((alert) => {
                const container = document.createElement('div');
                container.className = `vet-prescricao-form__alert bg-${alert.type}-subtle text-${alert.type}`;
                const iconWrapper = document.createElement('div');
                iconWrapper.className = 'vet-prescricao-form__alert-icon bg-white';
                iconWrapper.innerHTML = '<i class="ri-alert-line"></i>';
                const content = document.createElement('div');
                const title = document.createElement('strong');
                title.className = 'd-block';
                title.textContent = alert.title;
                const description = document.createElement('span');
                description.className = 'small';
                description.textContent = alert.description;
                content.appendChild(title);
                content.appendChild(description);
                container.appendChild(iconWrapper);
                container.appendChild(content);
                alertsWrapper.appendChild(container);
            });
        }
    }

    function renderTimeline(template) {
        if (!timelineWrapper) {
            return;
        }
        clearElement(timelineWrapper);
        const timeline = template && Array.isArray(template.timeline) ? template.timeline : [];
        if (timeline.length) {
            timeline.forEach((event) => {
                const item = document.createElement('div');
                item.className = 'vet-prescricao-form__timeline-item mb-3';
                const time = document.createElement('span');
                time.className = 'vet-prescricao-form__info-label d-block';
                time.textContent = event.time;
                const title = document.createElement('strong');
                title.className = 'd-block';
                title.textContent = event.title;
                const description = document.createElement('span');
                description.className = 'text-muted small';
                description.textContent = event.description;
                item.appendChild(time);
                item.appendChild(title);
                item.appendChild(description);
                timelineWrapper.appendChild(item);
            });
        }
    }

    function updateTemplateDetails(template) {
        if (diagnosisInput) {
            diagnosisInput.value = template ? template.diagnosis || '' : '';
        }
        if (summaryTextarea) {
            summaryTextarea.value = template ? template.summary || '' : '';
        }

        renderObjectives(template || null);
        renderMedications(template || null);
        renderMonitoring(template || null);
        renderAlerts(template || null);
        renderTimeline(template || null);
        renderTemplateFields(template || null);
    }

    function formatPatientOption(option) {
        if (!option.id || !window.jQuery) {
            return option.text;
        }

        const patient = findPatient(option.id);
        if (!patient) {
            return option.text;
        }

        const $container = window.jQuery('<div class="d-flex flex-column"></div>');
        window.jQuery('<span class="fw-semibold text-secondary"></span>').text(patient.name).appendTo($container);

        const metaParts = [patient.species, patient.breed].filter(Boolean);
        if (patient.tutor) {
            metaParts.push('Tutor(a): ' + patient.tutor);
        }

        if (metaParts.length) {
            window.jQuery('<small class="text-muted"></small>').text(metaParts.join(' • ')).appendTo($container);
        }

        return $container;
    }

    function formatPatientSelection(option) {
        if (!option.id) {
            return option.text;
        }

        const patient = findPatient(option.id);
        if (!patient) {
            return option.text;
        }

        const parts = [patient.name];
        if (patient.species) {
            parts.push(patient.species);
        }
        if (patient.tutor) {
            parts.push('Tutor(a): ' + patient.tutor);
        }

        return parts.join(' • ');
    }

    function formatVeterinarianOption(option) {
        if (!option.id || !window.jQuery) {
            return option.text;
        }

        const veterinarian = findVeterinarian(option.id);
        if (!veterinarian) {
            return option.text;
        }

        const $container = window.jQuery('<div class="d-flex flex-column"></div>');
        window.jQuery('<span class="fw-semibold text-secondary"></span>').text(veterinarian.name).appendTo($container);

        const metaParts = [];
        if (veterinarian.specialty) {
            metaParts.push(veterinarian.specialty);
        }
        if (veterinarian.next_available) {
            metaParts.push('Próximo horário: ' + veterinarian.next_available);
        }

        if (metaParts.length) {
            window.jQuery('<small class="text-muted"></small>').text(metaParts.join(' • ')).appendTo($container);
        }

        return $container;
    }

    function formatVeterinarianSelection(option) {
        if (!option.id) {
            return option.text;
        }

        const veterinarian = findVeterinarian(option.id);
        if (!veterinarian) {
            return option.text;
        }

        const parts = [veterinarian.name];
        if (veterinarian.specialty) {
            parts.push(veterinarian.specialty);
        }

        return parts.join(' • ');
    }

    function initializeSelect2() {
        if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
            return;
        }

        const $ = window.jQuery;

        initializeMedicationSelects(document);

        if (patientSelect) {
            const $patientSelect = $(patientSelect);
            const placeholder = patientSelect.getAttribute('data-placeholder') || 'Selecione o paciente';
            const allowClear = patientSelect.getAttribute('data-allow-clear') === 'true';
            const dropdownParent = $patientSelect.closest('.vet-prescricao-form__card');

            $patientSelect.select2({
                width: '100%',
                placeholder: placeholder,
                allowClear: allowClear,
                dropdownParent: dropdownParent.length ? dropdownParent : $patientSelect.parent(),
                templateResult: formatPatientOption,
                templateSelection: formatPatientSelection,
            });

            const patientSelect2 = $patientSelect.data('select2');
            if (patientSelect2 && patientSelect2.$container) {
                patientSelect2.$container.addClass('select2-lg');
            }
        }

        if (veterinarianSelect) {
            const $veterinarianSelect = $(veterinarianSelect);
            const placeholder = veterinarianSelect.getAttribute('data-placeholder') || 'Selecione o veterinário';
            const allowClear = veterinarianSelect.getAttribute('data-allow-clear') === 'true';
            const dropdownParent = $veterinarianSelect.closest('.vet-prescricao-form__card');

            $veterinarianSelect.select2({
                width: '100%',
                placeholder: placeholder,
                allowClear: allowClear,
                dropdownParent: dropdownParent.length ? dropdownParent : $veterinarianSelect.parent(),
                templateResult: formatVeterinarianOption,
                templateSelection: formatVeterinarianSelection,
            });
        }
    }

    function handleChannelToggle(event) {
        const target = event.target.closest('.vet-prescricao-form__tag');
        if (!target) {
            return;
        }
        const isActive = target.classList.toggle('vet-prescricao-form__tag--active');
        const icon = target.querySelector('i');
        if (icon) {
            icon.className = isActive ? 'ri-checkbox-circle-line' : 'ri-checkbox-blank-circle-line';
        }
        syncChannelsField();
    }

    function addBlankMedication() {
        hideMedicationsPlaceholder();
        appendMedicationCard({});
        ensureMedicationsPlaceholder();
    }

    function applyExistingPrescriptionData() {
        if (editingDataApplied || !existingPrescription) {
            return;
        }

        editingDataApplied = true;

        if (diagnosisInput && Object.prototype.hasOwnProperty.call(existingPrescription, 'diagnosis')) {
            diagnosisInput.value = normalizeTextValue(existingPrescription.diagnosis);
        }

        if (summaryTextarea && Object.prototype.hasOwnProperty.call(existingPrescription, 'summary')) {
            summaryTextarea.value = normalizeTextValue(existingPrescription.summary);
        }

        if (guidelinesTextarea && Object.prototype.hasOwnProperty.call(existingPrescription, 'guidelines')) {
            guidelinesTextarea.value = normalizeTextValue(existingPrescription.guidelines);
        }

        if (notesField && Object.prototype.hasOwnProperty.call(existingPrescription, 'notes')) {
            notesField.value = normalizeTextValue(existingPrescription.notes);
            notesField.dataset.autoFilled = 'false';
        }

        if (templateHiddenField && existingPrescription.template_id) {
            templateHiddenField.value = normalizeId(existingPrescription.template_id);
        }

        if (channelsField && Array.isArray(existingPrescription.channels)) {
            const normalizedChannels = normalizeChannelList(existingPrescription.channels);
            channelsField.value = JSON.stringify(normalizedChannels);
            applyInitialChannelSelection(normalizedChannels);
            syncChannelsField();
        }

        if (Array.isArray(existingPrescription.allergies)) {
            currentAllergies = normalizePatientItems(existingPrescription.allergies);
            renderAllergiesList();
        }

        if (Array.isArray(existingPrescription.conditions)) {
            currentConditions = normalizePatientItems(existingPrescription.conditions);
            renderConditionsList();
        }

        syncAssignmentFields();

        if (Array.isArray(existingPrescription.medications) && medicationsList) {
            clearElement(medicationsList);
            existingPrescription.medications.forEach((medication) => {
                appendMedicationCard(medication);
            });
            ensureMedicationsPlaceholder();
            syncMedicationsField();
        }
    }

    function init() {
        logDebug('Initializing prescription form');
        initializeSelect2();
        initializeAttachments();

        updateAssignmentControlsState();
        syncAssignmentFields();

        if (channelsField) {
            const initialChannels = parseJsonArray(channelsField.value);
            applyInitialChannelSelection(initialChannels);
            syncChannelsField();
        }

        if (medicationsList) {
            medicationsList.addEventListener('input', syncMedicationsField);
            medicationsList.addEventListener('change', syncMedicationsField);
            syncMedicationsField();
        }

        if (addAllergyButton) {
            addAllergyButton.addEventListener('click', handleAddAllergy);
        }

        if (addConditionButton) {
            addConditionButton.addEventListener('click', handleAddCondition);
        }

        if (patientSelect) {
            logDebug('Patient select initial value', {
                value: patientSelect.value,
            });
            const syncPatientFromSelect = (value) => {
                const targetValue = value !== undefined ? value : patientSelect.value;
                const normalizedValue = normalizeId(targetValue);
                const selectedPatient = resolvePatient(targetValue);
                logDebug('Syncing patient details from selection', {
                    value: targetValue,
                    found: selectedPatient,
                });
                if (patientHiddenField) {
                    patientHiddenField.value = normalizedValue;
                }
                updatePatientDetails(selectedPatient);
            };

            patientSelect.addEventListener('change', () => {
                syncPatientFromSelect();
            });

            if (window.jQuery) {
                const $patientSelect = window.jQuery(patientSelect);
                $patientSelect.on('select2:select', (event) => {
                    const selectedId = event && event.params && event.params.data ? event.params.data.id : undefined;
                    logDebug('Select2 patient select event fired', {
                        type: 'select',
                        selectedId,
                    });
                    syncPatientFromSelect(selectedId);
                });
                $patientSelect.on('select2:clear', () => {
                    logDebug('Select2 patient select event fired', {
                        type: 'clear',
                    });
                    syncPatientFromSelect('');
                });
            }

            if (patientSelect.value) {
                syncPatientFromSelect(patientSelect.value);
            }
        }

        if (templateSelect) {
            const applyTemplateSelection = (value) => {
                const selectedTemplate = findTemplate(value);
                logDebug('Applying template selection', {
                    value,
                    found: selectedTemplate,
                });

                if (templateHiddenField) {
                    templateHiddenField.value = selectedTemplate ? normalizeId(selectedTemplate.id) : '';
                }

                updateTemplateDetails(selectedTemplate);
            };

            if (!templateSelect.value && initialTemplateId) {
                templateSelect.value = initialTemplateId;
            }

            logDebug('Template select initial value', {
                value: templateSelect.value,
                initialTemplateId,
            });

            templateSelect.addEventListener('change', () => {
                applyTemplateSelection(templateSelect.value);
            });

            if (templateSelect.value) {
                applyTemplateSelection(templateSelect.value);
            } else if (initialTemplateId) {
                applyTemplateSelection(initialTemplateId);
            } else {
                renderTemplateFields(null);
            }
        } else {
            renderTemplateFields(null);
        }

        if (veterinarianSelect) {
            logDebug('Veterinarian select initial value', {
                value: veterinarianSelect.value,
            });
            veterinarianSelect.addEventListener('change', updateVeterinarianAvailabilityLabel);
            updateVeterinarianAvailabilityLabel();
        }

        if (addMedicationButton && medicationsList) {
            addMedicationButton.addEventListener('click', addBlankMedication);
        }

        if (channelsWrapper) {
            channelsWrapper.addEventListener('click', handleChannelToggle);
            syncChannelsField();
        }

        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }

        applyExistingPrescriptionData();
    }

    document.addEventListener('DOMContentLoaded', init);
})();