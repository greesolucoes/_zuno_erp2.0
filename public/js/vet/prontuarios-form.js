(function () {
    const DEBUG_NAMESPACE = '[vet/prontuarios]';
    const DEBUG_STATE = { enabled: true };

    function debugLog(message, payload) {
        if (!DEBUG_STATE.enabled) {
            return;
        }

        const details = payload !== undefined ? payload : '';
        try {
            console.log(DEBUG_NAMESPACE, message, details);
        } catch (error) {
            console.log(DEBUG_NAMESPACE, 'Falha ao registrar log', { message: message, payload: details, error: error });
        }
    }

    const WRAPPED_FLAG = '__vetDebugWrapped__';

    function wrapWithDebug(name, fn) {
        if (typeof fn !== 'function') {
            debugLog('wrapWithDebug ignorado para item não funcional', { name: name, tipo: typeof fn });
            return fn;
        }

        if (fn && fn[WRAPPED_FLAG]) {
            debugLog('wrapWithDebug ignorado: função já instrumentada', { name: name });
            return fn;
        }

        const wrappedFunction = function wrappedFunction() {
            const args = Array.prototype.slice.call(arguments);
            debugLog('→ ' + name, { argumentos: args, contexto: this });

            try {
                const result = fn.apply(this, arguments);
                debugLog('← ' + name, { retorno: result });
                return result;
            } catch (error) {
                debugLog('× ' + name, { erro: error });
                throw error;
            }
        };
        wrappedFunction.__original = fn;
        wrappedFunction.__debugName = name;

        return markWrapped(wrappedFunction);
    }

    function markWrapped(fn) {
        if (typeof fn === 'function') {
            try {
                Object.defineProperty(fn, WRAPPED_FLAG, {
                    value: true,
                    enumerable: false,
                    writable: false,
                });
            } catch (error) {
                fn[WRAPPED_FLAG] = true;
            }
        }
        return fn;
    }

    debugLog('Arquivo prontuarios-form.js avaliado', { timestamp: new Date().toISOString() });
    debugLog('Registrando listener DOMContentLoaded');

    function parseDatasetJSON(dataset, key, fallback) {
        try {
            if (!Object.prototype.hasOwnProperty.call(dataset, key)) {
                return fallback;
            }

            const value = dataset[key];
            return value ? JSON.parse(value) : fallback;
        } catch (error) {
            console.warn('[vet/prontuarios] Falha ao interpretar dataset', key, error);
            return fallback;
        }
    }

    function createElementFromHTML(html) {
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        return template.content.firstChild;
    }

    function destroyAssessmentSelectEnhancements(container) {
        if (
            !container ||
            !window.jQuery ||
            !window.jQuery.fn ||
            typeof window.jQuery.fn.select2 !== 'function'
        ) {
            return;
        }

        window.jQuery(container)
            .find('select[data-role="assessment-select-enhanced"]')
            .each(function () {
                const $select = window.jQuery(this);

                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
            });
    }

    function enhanceAssessmentSelectFields(container) {
        if (
            !container ||
            !window.jQuery ||
            !window.jQuery.fn ||
            typeof window.jQuery.fn.select2 !== 'function'
        ) {
            return;
        }

        window.jQuery(container)
            .find('select[data-role="assessment-select-enhanced"]')
            .each(function () {
                const $select = window.jQuery(this);

                if ($select.data('select2')) {
                    $select.select2('destroy');
                }

                const placeholder =
                    $select.data('placeholder') ||
                    $select.attr('placeholder') ||
                    ($select.prop('multiple') ? '' : 'Selecione uma opção');

                const dropdownParent = $select.closest('.vet-record-form__assessment-card');
                const dropdownTarget = dropdownParent.length ? dropdownParent : $select.parent();

                $select.select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: !$select.prop('multiple'),
                    dropdownParent: dropdownTarget,
                });

                $select.trigger('change');
            });
    }

    function formatChecklistTitle(key) {
        const titles = {
            before_consultation: 'Pré-atendimento',
            documentation: 'Documentação',
            orientations: 'Orientações ao tutor',
        };

        return titles[key] || key;
    }

    function formatCommunicationMessage(template, patient, slotLabel) {
        if (!template) {
            return '';
        }

        const replacements = {
            '{{ tutor }}': patient ? patient.tutor : 'tutor',
            '{{ paciente }}': patient ? patient.name : 'paciente',
            '{{ data_retorno }}': slotLabel || 'data do retorno',
        };

        let message = template.message || '';

        Object.entries(replacements).forEach(([key, value]) => {
            message = message.replace(new RegExp(key, 'g'), value);
        });

        return message;
    }

    function copyToClipboard(text) {
        if (!text) {
            return Promise.reject(new Error('Texto vazio'));
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (successful) {
                    resolve();
                } else {
                    reject(new Error('Não foi possível copiar o texto.'));
                }
            } catch (error) {
                document.body.removeChild(textarea);
                reject(error);
            }
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

    function resolveAttachmentBadge(attachment) {
        let extension = '';

        if (attachment && attachment.extension) {
            extension = String(attachment.extension).replace(/\./g, '').trim();
        }

        if (!extension && attachment && attachment.mime_type) {
            const mime = String(attachment.mime_type).split('/');
            extension = mime[mime.length - 1] || '';
        }

        if (!extension && attachment && attachment.name) {
            const parts = String(attachment.name).split('.');
            if (parts.length > 1) {
                extension = parts.pop();
            }
        }

        extension = extension ? extension.toUpperCase() : '';

        if (!extension) {
            return 'ARQUIVO';
        }

        if (extension.length > 8) {
            return extension.slice(0, 8);
        }

        return extension;
    }

    function generateAttachmentId(index) {
        const base = Date.now().toString(36);
        const suffix = Math.random().toString(16).slice(2, 8);

        return `record-attachment-${base}-${index}-${suffix}`;
    }

    function normalizeAttachmentItem(raw, fallbackIndex) {
        if (!raw || typeof raw !== 'object') {
            return null;
        }

        const idSource = raw.id || raw.path || generateAttachmentId(fallbackIndex || 0);
        const attachment = {
            id: String(idSource),
            name: raw.name || raw.original_name || raw.filename || 'Documento',
            extension: typeof raw.extension === 'string' ? raw.extension.replace(/\./g, '').toLowerCase() : '',
            mime_type: raw.mime_type || raw.type || '',
            type: raw.type || '',
            size: raw.size || '',
            size_in_bytes: raw.size_in_bytes || raw.bytes || raw.sizeBytes || null,
            uploaded_at: raw.uploaded_at || raw.created_at || '',
            uploaded_at_iso: raw.uploaded_at_iso || raw.created_at_iso || '',
            uploaded_by: raw.uploaded_by || raw.author || raw.user || '',
            url: raw.url || raw.link || '',
            path: raw.path || raw.storage_path || '',
        };

        if (!attachment.extension && attachment.name) {
            const nameParts = String(attachment.name).split('.');
            if (nameParts.length > 1) {
                attachment.extension = nameParts.pop().toLowerCase();
            }
        }

        if (typeof attachment.size_in_bytes === 'string') {
            const parsed = parseInt(attachment.size_in_bytes, 10);
            if (!Number.isNaN(parsed)) {
                attachment.size_in_bytes = parsed;
            }
        }

        if (!attachment.size && typeof attachment.size_in_bytes === 'number' && attachment.size_in_bytes >= 0) {
            attachment.size = formatFileSizeValue(attachment.size_in_bytes);
        }

        if (!attachment.uploaded_at && attachment.uploaded_at_iso) {
            attachment.uploaded_at = formatIsoDate(attachment.uploaded_at_iso);
        }

        if (!attachment.path && attachment.url) {
            try {
                const parsedUrl = new URL(attachment.url, window.location.origin);
                attachment.path = parsedUrl.pathname ? parsedUrl.pathname.replace(/^\//, '') : '';
            } catch (error) {
                debugLog('normalizeAttachmentItem: falha ao interpretar caminho do anexo', error);
            }
        }

        if (!attachment.type && attachment.extension) {
            attachment.type = attachment.extension.toUpperCase();
        }

        attachment.badge = resolveAttachmentBadge(attachment);

        return attachment;
    }

    document.addEventListener('DOMContentLoaded', function () {
        debugLog('DOMContentLoaded handler iniciado', { timestamp: new Date().toISOString() });
        console.log('[vet/prontuarios] Inicializando script do prontuário');
        const root = document.getElementById('vet-record-form');
        debugLog('Elemento raiz do prontuário localizado', { encontrado: Boolean(root) });
        if (!root) {
            console.warn('[vet/prontuarios] Elemento raiz #vet-record-form não encontrado. Abortando inicialização.');
            debugLog('Abortando inicialização por ausência do elemento raiz #vet-record-form');
            return;
        }

        const dataset = root.dataset;
        console.log('[vet/prontuarios] Dataset encontrado na raiz do prontuário', dataset);
        debugLog('Dataset bruto coletado da raiz', dataset);

        parseDatasetJSON = wrapWithDebug('parseDatasetJSON', parseDatasetJSON);
        debugLog('parseDatasetJSON instrumentado antes da leitura do dataset');
        const patients = parseDatasetJSON(dataset, 'patients', []) || [];
        const veterinarians = parseDatasetJSON(dataset, 'veterinarians', []) || [];
        const slots = parseDatasetJSON(dataset, 'slots', []) || [];
        const assessmentModels = parseDatasetJSON(dataset, 'assessmentModels', []) || [];
        const assessmentModelFetchUrl = dataset.assessmentModelFetchUrl || '';
        const checklists = parseDatasetJSON(dataset, 'checklists', {}) || {};
        const reminders = parseDatasetJSON(dataset, 'reminders', []) || [];
        const initialAttachments = parseDatasetJSON(dataset, 'attachments', []) || [];
        const communications = parseDatasetJSON(dataset, 'communications', []) || [];
        const serviceCatalog = parseDatasetJSON(dataset, 'serviceCatalog', []) || [];
        const serviceCreateUrl = dataset.serviceCreateUrl || '';
        const quickNotes = parseDatasetJSON(dataset, 'quickNotes', {}) || {};
        const evolutionTimeline = parseDatasetJSON(dataset, 'evolution', []) || [];
        const prefill = parseDatasetJSON(dataset, 'prefill', null);
        const assessmentPrefill =
            prefill && typeof prefill === 'object' && prefill !== null ? prefill.assessment || null : null;
        const assessmentPrefillModel =
            assessmentPrefill && typeof assessmentPrefill.model === 'object' && assessmentPrefill.model !== null
                ? assessmentPrefill.model
                : null;
        const initialAssessmentModelId =
            assessmentPrefillModel && assessmentPrefillModel.id !== undefined && assessmentPrefillModel.id !== null
                ? normalizeId(assessmentPrefillModel.id)
                : assessmentPrefill &&
                    assessmentPrefill.meta &&
                    assessmentPrefill.meta.model_id !== undefined &&
                    assessmentPrefill.meta.model_id !== null
                ? normalizeId(assessmentPrefill.meta.model_id)
                : '';
        const initialAssessmentValues =
            assessmentPrefill &&
            assessmentPrefill.values &&
            typeof assessmentPrefill.values === 'object' &&
            assessmentPrefill.values !== null
                ? deepClone(assessmentPrefill.values)
                : null;
        const initialAssessmentMeta =
            assessmentPrefill &&
            assessmentPrefill.meta &&
            typeof assessmentPrefill.meta === 'object' &&
            assessmentPrefill.meta !== null
                ? deepClone(assessmentPrefill.meta)
                : null;
        const initialAssessmentOptionLabel =
            (assessmentPrefillModel && assessmentPrefillModel.title) ||
            (initialAssessmentMeta && initialAssessmentMeta.model_title) ||
            initialAssessmentModelId;
        const attachmentsConfig = {
            uploadUrl: typeof dataset.attachmentsUploadUrl === 'string' ? dataset.attachmentsUploadUrl : '',
            removeUrl: typeof dataset.attachmentsRemoveUrl === 'string' ? dataset.attachmentsRemoveUrl : '',
            maxItems: Math.max(1, parseInt(dataset.attachmentsMaxItems || '8', 10) || 8),
            maxSize: parseInt(dataset.attachmentsMaxSizeBytes || '', 10) || 10 * 1024 * 1024,
        };

        const attachmentsState = {
            items: Array.isArray(initialAttachments)
                ? initialAttachments
                      .map(function (item, index) {
                          return normalizeAttachmentItem(item, index);
                      })
                      .filter(function (item) {
                          return Boolean(item);
                      })
                : [],
            isUploading: false,
        };

        debugLog('Dados interpretados a partir do dataset', {
            pacientes: patients,
            veterinarios: veterinarians,
            horarios: slots,
            modelosAvaliacao: assessmentModels,
            urlBuscaModelo: assessmentModelFetchUrl,
            checklists: checklists,
            lembretes: reminders,
            anexos: attachmentsState.items,
            comunicacoes: communications,
            servicos: serviceCatalog,
            servicoCadastroUrl: serviceCreateUrl,
            notasRapidas: quickNotes,
            evolucao: evolutionTimeline,
            prefill: prefill,
        });

        let currentAssessmentModel = null;
        let currentAssessmentMeta = {};
        let assessmentModelRequestToken = 0;

        const tabContainer = document.getElementById('recordFormTabs');
        const contextualSidebarCards = root.querySelectorAll('[data-tab-context]');
        const mainColumn = root.querySelector('#recordMainColumn');
        const sidebarColumn = root.querySelector('#recordSidebarColumn');

        debugLog('Configuração inicial dos cards contextuais do prontuário', {
            temContainer: Boolean(tabContainer),
            quantidadeCards: contextualSidebarCards ? contextualSidebarCards.length : 0,
        });

        function toggleSidebarCardsForTab(targetId) {
            if (!contextualSidebarCards || contextualSidebarCards.length === 0) {
                debugLog('Nenhum card contextual encontrado para alternar', { alvo: targetId });
                return;
            }

            const normalizedTarget = typeof targetId === 'string' && targetId !== '' ? targetId : '#tab-overview';
            debugLog('Atualizando cards contextuais para a aba ativa', { alvo: normalizedTarget });

            contextualSidebarCards.forEach(function (card) {
                if (!card || !card.dataset) {
                    return;
                }

                const contexts = (card.dataset.tabContext || '')
                    .split(',')
                    .map(function (context) {
                        return context.trim();
                    })
                    .filter(Boolean);

                if (contexts.length === 0) {
                    return;
                }

                if (contexts.indexOf(normalizedTarget) !== -1) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });

            const hasVisibleSidebarCard = Array.prototype.some.call(
                contextualSidebarCards,
                function (card) {
                    return card && !card.classList.contains('d-none');
                }
            );

            if (sidebarColumn) {
                if (hasVisibleSidebarCard) {
                    sidebarColumn.classList.remove('d-none');
                } else {
                    sidebarColumn.classList.add('d-none');
                }
            }

            if (mainColumn) {
                if (hasVisibleSidebarCard) {
                    mainColumn.classList.add('col-xl-8');
                    mainColumn.classList.remove('col-xl-12');
                } else {
                    mainColumn.classList.add('col-xl-12');
                    mainColumn.classList.remove('col-xl-8');
                }
            }
        }

        function setupTabSidebarSync() {
            const defaultTarget = '#tab-overview';
            let initialTarget = defaultTarget;

            if (tabContainer) {
                const activeTrigger = tabContainer.querySelector('.nav-link.active[data-bs-toggle]');
                if (activeTrigger) {
                    const candidate = activeTrigger.getAttribute('data-bs-target');
                    if (typeof candidate === 'string' && candidate !== '') {
                        initialTarget = candidate;
                    }
                }
            }

            debugLog('Inicializando visibilidade dos cards contextuais', { abaInicial: initialTarget });
            toggleSidebarCardsForTab(initialTarget);

            if (!tabContainer) {
                debugLog('Container de tabs não encontrado; permanecerá apenas a visibilidade inicial.');
                return;
            }

            const tabTriggers = tabContainer.querySelectorAll('[data-bs-toggle="pill"],[data-bs-toggle="tab"]');

            tabTriggers.forEach(function (trigger) {
                trigger.addEventListener('shown.bs.tab', function (event) {
                    const target = event && event.target ? event.target.getAttribute('data-bs-target') : null;
                    toggleSidebarCardsForTab(target || defaultTarget);
                });
            });
        }

        setupTabSidebarSync();

        const storeUrl = dataset.storeUrl || '';
        const updateUrl = dataset.updateUrl || '';
        const editUrlTemplate = dataset.editUrlTemplate || '';
        const formMode = dataset.mode || (prefill && prefill.id ? 'edit' : 'create');
        const attendanceIdFromDataset = dataset.attendanceId || '';
        const currentStatusFromDataset = dataset.currentStatus || '';

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        if (csrfToken && window.axios && window.axios.defaults) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }

        function sendRequest(config) {
            const requestConfig = Object.assign({}, config || {});
            requestConfig.method = (requestConfig.method || 'get').toLowerCase();
            requestConfig.headers = Object.assign(
                { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                requestConfig.headers || {}
            );

            if (!requestConfig.url) {
                return Promise.reject(new Error('Request URL não informado.'));
            }

            if (csrfToken && !requestConfig.headers['X-CSRF-TOKEN']) {
                requestConfig.headers['X-CSRF-TOKEN'] = csrfToken;
            }

            if (window.axios) {
                if (typeof window.axios === 'function') {
                    return window.axios(requestConfig);
                }

                if (typeof window.axios.request === 'function') {
                    return window.axios.request(requestConfig);
                }
            }

            const fetchHeaders = new Headers();
            Object.keys(requestConfig.headers).forEach(function (headerKey) {
                fetchHeaders.append(headerKey, requestConfig.headers[headerKey]);
            });

            const fetchOptions = {
                method: requestConfig.method.toUpperCase(),
                headers: fetchHeaders,
                credentials: 'same-origin',
            };

            if (requestConfig.data !== undefined) {
                if (requestConfig.data instanceof FormData) {
                    fetchOptions.body = requestConfig.data;
                } else {
                    if (!fetchHeaders.has('Content-Type')) {
                        fetchHeaders.set('Content-Type', 'application/json');
                    }
                    fetchOptions.body = JSON.stringify(requestConfig.data);
                }
            }

            return fetch(requestConfig.url, fetchOptions).then(function (response) {
                const contentType = response.headers.get('content-type') || '';
                const parseBody = contentType.indexOf('application/json') !== -1 ? response.json.bind(response) : response.text.bind(response);

                return parseBody().catch(function () {
                    return null;
                }).then(function (body) {
                    const axiosLikeResponse = {
                        data: body,
                        status: response.status,
                        statusText: response.statusText,
                        headers: response.headers,
                        config: requestConfig,
                        request: null,
                    };

                    if (!response.ok) {
                        const error = new Error('Request failed with status code ' + response.status);
                        error.response = axiosLikeResponse;
                        throw error;
                    }

                    return axiosLikeResponse;
                });
            });
        }

        function normalizeId(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value);
        }

        function toNullableInteger(value) {
            if (value === null || value === undefined || value === '') {
                return null;
            }

            const numeric = Number(value);

            return Number.isFinite(numeric) ? Math.trunc(numeric) : null;
        }

        function isSameId(a, b) {
            return normalizeId(a) === normalizeId(b);
        }

        function ensureSelectOption(selectElement, value, label) {
            if (!selectElement || value === null || value === undefined || value === '') {
                return;
            }

            const normalizedValue = normalizeId(value);
            const options = Array.from(selectElement.options || []);
            const hasOption = options.some(function (option) {
                return isSameId(option.value, normalizedValue);
            });

            if (hasOption) {
                return;
            }

            const option = document.createElement('option');
            option.value = normalizedValue;
            option.textContent = label || normalizedValue;
            selectElement.appendChild(option);
        }

        const serviceCatalogMap = new Map(
            (serviceCatalog || []).map(function (service) {
                return [normalizeId(service.id), service];
            })
        );

        function slugify(text) {
            if (!text) {
                return '';
            }

            return text
                .normalize('NFD')
                .replace(/[^\w\s-]/g, '')
                .replace(/[\u0300-\u036f]/g, '')
                .trim()
                .toLowerCase()
                .replace(/[\s_]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        const completedChecklistItems = new Set();

        if (prefill && typeof prefill === 'object' && prefill !== null) {
            if (prefill.triage && Array.isArray(prefill.triage.checklists_completed)) {
                prefill.triage.checklists_completed.forEach(function (item) {
                    completedChecklistItems.add(normalizeId(item));
                });
            }

            if (prefill.patient_snapshot && prefill.patient_snapshot.id) {
                const patientId = normalizeId(prefill.patient_snapshot.id);
                let foundPatient = false;

                for (let index = 0; index < patients.length; index += 1) {
                    if (isSameId(patients[index].id, patientId)) {
                        patients[index] = Object.assign({}, patients[index], prefill.patient_snapshot);
                        foundPatient = true;
                        break;
                    }
                }

                if (!foundPatient) {
                    patients.push(prefill.patient_snapshot);
                }
            }

            if (prefill.veterinarian && prefill.veterinarian.id) {
                const veterinarianId = normalizeId(prefill.veterinarian.id);
                let foundVeterinarian = false;

                for (let index = 0; index < veterinarians.length; index += 1) {
                    if (isSameId(veterinarians[index].id, veterinarianId)) {
                        veterinarians[index] = Object.assign({}, veterinarians[index], {
                            name: prefill.veterinarian.name || veterinarians[index].name,
                            specialty: prefill.veterinarian.specialty || veterinarians[index].specialty,
                        });
                        foundVeterinarian = true;
                        break;
                    }
                }

                if (!foundVeterinarian) {
                    veterinarians.push({
                        id: prefill.veterinarian.id,
                        name: prefill.veterinarian.name || 'Profissional não identificado',
                        specialty: prefill.veterinarian.specialty || 'Especialidade não informada',
                    });
                }
            }

            if (prefill.attendance && prefill.attendance.slot_option && prefill.attendance.slot_option.value && Array.isArray(slots)) {
                const slotValue = normalizeId(prefill.attendance.slot_option.value);
                const hasSlot = slots.some(function (slot) {
                    return isSameId(slot.value, slotValue);
                });

                if (!hasSlot) {
                    slots.unshift(prefill.attendance.slot_option);
                }
            }

            if (prefill.triage) {
                if (Array.isArray(prefill.triage.vital_signs) && prefill.triage.vital_signs.length) {
                    quickNotes.vital_signs = prefill.triage.vital_signs;
                }

                if (Array.isArray(prefill.triage.monitoring) && prefill.triage.monitoring.length) {
                    quickNotes.monitoring = prefill.triage.monitoring;
                }

                if (Array.isArray(prefill.triage.timeline) && prefill.triage.timeline.length) {
                    evolutionTimeline.splice(0, evolutionTimeline.length);
                    Array.prototype.push.apply(evolutionTimeline, prefill.triage.timeline);
                }
            }
        }

        function parseOptions(value) {
            if (Array.isArray(value)) {
                return value
                    .map(function (item) {
                        return String(item).trim();
                    })
                    .filter(Boolean);
            }

            if (typeof value === 'string') {
                return value
                    .split(/[\r\n,]+/)
                    .map(function (item) {
                        return item.trim();
                    })
                    .filter(Boolean);
            }

            return [];
        }

        function stripHtml(value) {
            if (typeof value !== 'string') {
                return '';
            }

            return value.replace(/<[^>]*>/g, '').trim();
        }

        function isTinymceAvailable() {
            return typeof tinymce !== 'undefined' && typeof tinymce.init === 'function';
        }

        function generateRichTextEditorId() {
            return 'vet-record-rich-text-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8);
        }

        function hideTinymceBranding() {
            var elements = document.querySelectorAll('.tox-statusbar__branding, .tox-promotion');
            elements.forEach(function (element) {
                element.classList.add('d-none');
            });
        }

        function getSwalInstance() {
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                return Swal;
            }

            if (
                typeof window !== 'undefined' &&
                window.Swal &&
                typeof window.Swal.fire === 'function'
            ) {
                return window.Swal;
            }

            return null;
        }

        function confirmHighlightsTemplateReplacement(label) {
            var swalInstance = getSwalInstance();
            var summaryLabel = (label || '').trim();
            var message = summaryLabel
                ? 'Carregar o modelo de "' + summaryLabel + '" irá substituir o resumo já preenchido neste campo.'
                : 'Carregar o modelo selecionado irá substituir o resumo já preenchido neste campo.';

            if (!swalInstance) {
                var promptLabel = summaryLabel || 'atendimento';
                return Promise.resolve(
                    window.confirm('Deseja substituir o resumo atual pelo modelo de "' + promptLabel + '"?')
                );
            }

            return swalInstance
                .fire({
                    title: 'Substituir resumo atual?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, carregar modelo',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                })
                .then(function (result) {
                    return Boolean(result && result.isConfirmed);
                });
        }

        function destroyRichTextEditors(container) {
            if (!isTinymceAvailable()) {
                return;
            }

            var editors = Array.isArray(tinymce.editors) ? tinymce.editors.slice() : [];

            editors.forEach(function (editor) {
                if (!editor || !editor.targetElm) {
                    return;
                }

                if (!container || container.contains(editor.targetElm) || editor.targetElm === container) {
                    editor.remove();
                }
            });
        }

        function initRichTextEditor(textarea) {
            if (!isTinymceAvailable() || !textarea) {
                debugLog('initRichTextEditor ignorado', { disponivel: isTinymceAvailable(), textarea: Boolean(textarea) });
                return;
            }

            if (!textarea.id) {
                textarea.id = generateRichTextEditorId();
            }

            if (tinymce.get(textarea.id)) {
                return;
            }

            tinymce.init({
                target: textarea,
                language: 'pt_BR',
                menubar: false,
                statusbar: false,
                height: 380,
                plugins: 'lists advlist table link',
                toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link removeformat',
                setup: function (editor) {
                    editor.on('change keyup', function () {
                        editor.save();

                        if (textarea === highlightsField) {
                            updateHighlightsDirtyState();
                        }
                    });

                    editor.on('init', function () {
                        hideTinymceBranding();
                        if (textarea && textarea.dataset && Object.prototype.hasOwnProperty.call(textarea.dataset, 'pendingRichTextValue')) {
                            const pendingValue = textarea.dataset.pendingRichTextValue || '';
                            editor.setContent(pendingValue);
                            editor.save();
                            delete textarea.dataset.pendingRichTextValue;
                        }

                        if (textarea === highlightsField) {
                            updateHighlightsDirtyState();

                            if (!highlightsTemplateState.isDirty) {
                                maybeApplyHighlightsTemplate({ silent: true });
                            }
                        }
                    });
                }
            });

            setTimeout(hideTinymceBranding, 400);
        }

        function setRichTextValue(textarea, value) {
            if (!textarea) {
                return;
            }

            const normalisedValue = value !== undefined && value !== null ? String(value) : '';
            textarea.value = normalisedValue;

            if (textarea.dataset) {
                textarea.dataset.pendingRichTextValue = normalisedValue;
            }

            if (typeof window === 'undefined' || typeof window.tinymce === 'undefined') {
                return;
            }

            if (!textarea.id) {
                return;
            }

            const editor = window.tinymce.get(textarea.id);

            if (editor) {
                editor.setContent(normalisedValue);
                editor.save();
                if (textarea.dataset) {
                    delete textarea.dataset.pendingRichTextValue;
                }
                return;
            }

            setTimeout(function () {
                const editorInstance = window.tinymce.get(textarea.id);
                if (editorInstance) {
                    editorInstance.setContent(normalisedValue);
                    editorInstance.save();
                    if (textarea.dataset) {
                        delete textarea.dataset.pendingRichTextValue;
                    }
                }
            }, 200);
        }

        function escapeHtml(value) {
            if (typeof value !== 'string') {
                return '';
            }

            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            };

            return value.replace(/[&<>"']/g, function (char) {
                return map[char] || char;
            });
        }

        const currencyFormatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
        const currencyInputFormatter = new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        function parseCurrency(value) {
            if (typeof value === 'number' && Number.isFinite(value)) {
                return value;
            }

            if (typeof value !== 'string') {
                return 0;
            }

            const normalized = value
                .replace(/[^0-9,.-]/g, '')
                .replace(/\./g, '')
                .replace(',', '.');

            const parsed = parseFloat(normalized);

            return Number.isFinite(parsed) ? parsed : 0;
        }

        function formatCurrencyDisplay(value) {
            const numeric = typeof value === 'number' && Number.isFinite(value) ? value : parseCurrency(value);
            return currencyFormatter.format(numeric);
        }

        function formatCurrencyInput(value) {
            const numeric = typeof value === 'number' && Number.isFinite(value) ? value : parseCurrency(value);
            return currencyInputFormatter.format(numeric);
        }

        const patientSelect = document.getElementById('recordPatientSelect');
        const veterinarianSelect = document.getElementById('recordVeterinarianSelect');
        const typeSelect = document.getElementById('recordTypeSelect');
        const slotSelect = document.getElementById('recordSlotSelect');
        const templateSelect = document.getElementById('recordTemplateSelect');
        const templateSummary = document.getElementById('recordTemplateSummary');
        const assessmentFieldsContainer = document.getElementById('assessmentModelFields');
        const assessmentCard = document.querySelector('.vet-record-form__assessment-card');
        const assessmentFullscreenToggle = document.getElementById('recordAssessmentFullscreenToggle');
        const initialAssessmentFieldsContent = assessmentFieldsContainer ? assessmentFieldsContainer.innerHTML : '';
        const initialTemplateSummaryHtml = templateSummary ? templateSummary.innerHTML : '';
        const initialTemplateSummaryClassName = templateSummary ? templateSummary.className : '';
        const highlightsField = document.getElementById('recordHighlightsInput');
        const attendanceHighlightsTemplates = {
            consulta:
                '<p><strong>Motivo da consulta:</strong></p>' +
                '<ul>' +
                '<li>Queixa principal e tempo de evolução.</li>' +
                '<li>Sintomas associados relatados pelo tutor.</li>' +
                '</ul>' +
                '<p><strong>Exame físico:</strong></p>' +
                '<ul>' +
                '<li>Principais achados clínicos e parâmetros avaliados.</li>' +
                '<li>Diagnósticos diferenciais considerados.</li>' +
                '</ul>' +
                '<p><strong>Plano terapêutico:</strong></p>' +
                '<ul>' +
                '<li>Exames solicitados ou realizados durante a consulta.</li>' +
                '<li>Medicações, orientações ao tutor e retorno sugerido.</li>' +
                '</ul>',
            retorno:
                '<p><strong>Motivo do retorno:</strong></p>' +
                '<ul>' +
                '<li>Evolução desde o último atendimento.</li>' +
                '<li>Respostas observadas ao tratamento instituído.</li>' +
                '</ul>' +
                '<p><strong>Avaliação atual:</strong></p>' +
                '<ul>' +
                '<li>Principais sinais clínicos no momento da reavaliação.</li>' +
                '<li>Resultados de exames de acompanhamento.</li>' +
                '</ul>' +
                '<p><strong>Ajustes e orientações:</strong></p>' +
                '<ul>' +
                '<li>Ajustes terapêuticos necessários.</li>' +
                '<li>Orientações adicionais e novo agendamento de retorno.</li>' +
                '</ul>',
            'pos-operatorio':
                '<p><strong>Procedimento realizado:</strong></p>' +
                '<ul>' +
                '<li>Cirurgia executada e data do procedimento.</li>' +
                '<li>Complicações ou intercorrências imediatas.</li>' +
                '</ul>' +
                '<p><strong>Evolução pós-operatória:</strong></p>' +
                '<ul>' +
                '<li>Sinais vitais e dor controlada.</li>' +
                '<li>Condições da ferida operatória e apetite.</li>' +
                '</ul>' +
                '<p><strong>Cuidados e acompanhamento:</strong></p>' +
                '<ul>' +
                '<li>Medicações prescritas e orientações domiciliares.</li>' +
                '<li>Data sugerida para reavaliação e sinais de alerta.</li>' +
                '</ul>',
            emergencia:
                '<p><strong>Motivo da emergência:</strong></p>' +
                '<ul>' +
                '<li>Descrição do evento e tempo de início.</li>' +
                '<li>Condição ao chegar na clínica (escala de dor, nível de consciência).</li>' +
                '</ul>' +
                '<p><strong>Intervenções imediatas:</strong></p>' +
                '<ul>' +
                '<li>Suporte realizado (acesso venoso, fluidoterapia, analgesia).</li>' +
                '<li>Exames rápidos executados e resultados principais.</li>' +
                '</ul>' +
                '<p><strong>Estado atual e próximos passos:</strong></p>' +
                '<ul>' +
                '<li>Resposta ao atendimento emergencial.</li>' +
                '<li>Monitoramento indicado, encaminhamentos e orientações ao tutor.</li>' +
                '</ul>',
        };
        const highlightsTemplateState = {
            appliedType: null,
            isApplying: false,
            isDirty: highlightsField ? Boolean(stripHtml(highlightsField.value || '')) : false,
            lastTemplateHtml: null,
        };

        function getAttendanceHighlightsTemplate(type) {
            if (!type) {
                return '';
            }

            const normalizedType = String(type).toLowerCase();
            return attendanceHighlightsTemplates[normalizedType] || '';
        }

        function getAttendanceTypeLabel(type) {
            if (!typeSelect) {
                return type || '';
            }

            const options = Array.from(typeSelect.options || []);
            const option = options.find(function (item) {
                return isSameId(item.value, type);
            });

            return option ? option.textContent.trim() : type || '';
        }

        function updateHighlightsDirtyState() {
            if (!highlightsField || highlightsTemplateState.isApplying) {
                return;
            }

            const rawValue = highlightsField.value || '';
            const hasContent = Boolean(stripHtml(rawValue));

            if (highlightsTemplateState.lastTemplateHtml) {
                const expected = stripHtml(highlightsTemplateState.lastTemplateHtml).replace(/\s+/g, ' ');
                const current = stripHtml(rawValue).replace(/\s+/g, ' ');

                if (current === expected) {
                    highlightsTemplateState.isDirty = false;
                    return;
                }
            }

            highlightsTemplateState.isDirty = hasContent;

            if (!hasContent) {
                highlightsTemplateState.appliedType = null;
                highlightsTemplateState.lastTemplateHtml = null;
            }
        }

        function applyHighlightsTemplateForType(type, options) {
            if (!highlightsField) {
                return false;
            }

            const template = getAttendanceHighlightsTemplate(type);
            if (!template) {
                return false;
            }

            const settings = Object.assign(
                {
                    confirmWhenDirty: false,
                    force: false,
                    silent: false,
                },
                options || {}
            );

            if (!settings.force && highlightsTemplateState.isDirty) {
                if (settings.confirmWhenDirty) {
                    const label = getAttendanceTypeLabel(type);
                    confirmHighlightsTemplateReplacement(label)
                        .then(function (shouldReplace) {
                            if (!shouldReplace) {
                                return;
                            }

                            const nextOptions = Object.assign({}, settings, {
                                force: true,
                                confirmWhenDirty: false,
                            });

                            applyHighlightsTemplateForType(type, nextOptions);
                        })
                        .catch(function (error) {
                            if (typeof console !== 'undefined' && console && typeof console.error === 'function') {
                                console.error('[vet/prontuarios] Falha ao confirmar substituição do resumo', error);
                            }
                        });
                }

                return false;
            }

            highlightsTemplateState.isApplying = true;
            setRichTextValue(highlightsField, template);
            highlightsTemplateState.appliedType = type || null;
            highlightsTemplateState.isDirty = false;
            highlightsTemplateState.lastTemplateHtml = template;

            window.setTimeout(function () {
                highlightsTemplateState.isApplying = false;
            }, 0);

            if (!settings.silent) {
                debugLog('Resumo rápido atualizado a partir do modelo de atendimento', { tipo: type });
            }

            return true;
        }

        function maybeApplyHighlightsTemplate(options) {
            if (!typeSelect || !typeSelect.value) {
                return false;
            }

            return applyHighlightsTemplateForType(typeSelect.value, options);
        }

        if (highlightsField) {
            highlightsField.classList.add('rich-text');
            highlightsField.addEventListener('input', updateHighlightsDirtyState);
            initRichTextEditor(highlightsField);
        }
        debugLog('Elementos principais capturados na tela', {
            patientSelect: Boolean(patientSelect),
            veterinarianSelect: Boolean(veterinarianSelect),
            typeSelect: Boolean(typeSelect),
            slotSelect: Boolean(slotSelect),
            templateSelect: Boolean(templateSelect),
            templateSummary: Boolean(templateSummary),
            assessmentFieldsContainer: Boolean(assessmentFieldsContainer),
            assessmentCard: Boolean(assessmentCard),
            assessmentFullscreenToggle: Boolean(assessmentFullscreenToggle),
            highlightsField: Boolean(highlightsField),
        });

        const assessmentFullscreenState = {
            isActive: false,
            overlay: null,
            wrapper: null,
            cardContainer: null,
            closeButton: null,
            placeholder: null,
        };

        function normalizeAssessmentModel(model) {
            if (!model || typeof model !== 'object') {
                return null;
            }

            const fields = Array.isArray(model.fields) ? model.fields : [];

            return Object.assign({}, model, {
                id:
                    model.id !== undefined && model.id !== null && model.id !== ''
                        ? normalizeId(model.id)
                        : '',
                fields: fields,
                fields_count: typeof model.fields_count === 'number' ? model.fields_count : fields.length,
                category: model.category || null,
                category_label: model.category_label || model.category || null,
                notes: model.notes || null,
                title: model.title || '',
            });
        }

        function buildAssessmentMeta(model, baseMeta) {
            const meta = baseMeta && typeof baseMeta === 'object' && baseMeta !== null ? Object.assign({}, baseMeta) : {};

            if (!model) {
                return meta;
            }

            const modelId = model.id !== undefined && model.id !== null && model.id !== '' ? normalizeId(model.id) : '';

            if (modelId !== '') {
                meta.model_id = modelId;
            } else if (meta.model_id === '') {
                delete meta.model_id;
            }

            if (model.title) {
                meta.model_title = model.title;
            } else {
                delete meta.model_title;
            }

            if (model.category) {
                meta.model_category = model.category;
            } else {
                delete meta.model_category;
            }

            if (model.category_label) {
                meta.model_category_label = model.category_label;
            } else if (meta.model_category_label === '') {
                delete meta.model_category_label;
            }

            if (model.notes) {
                meta.model_notes = model.notes;
            } else if (meta.model_notes === '') {
                delete meta.model_notes;
            }

            if (model.status) {
                meta.model_status = model.status;
            }

            return meta;
        }

        function updateAssessmentFullscreenTrigger() {
            if (!assessmentFullscreenToggle) {
                return;
            }

            const icon = assessmentFullscreenToggle.querySelector('i');
            const label = assessmentFullscreenToggle.querySelector('.vet-record-form__fullscreen-label');
            const isActive = Boolean(assessmentFullscreenState.isActive);

            assessmentFullscreenToggle.classList.toggle('is-active', isActive);
            assessmentFullscreenToggle.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            assessmentFullscreenToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');

            if (icon) {
                icon.classList.remove('ri-fullscreen-line', 'ri-fullscreen-exit-line');
                icon.classList.add(isActive ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line');
            }

            if (label) {
                label.textContent = isActive ? 'Sair do modo tela grande' : 'Modo tela grande';
            }
        }

        function ensureAssessmentFullscreenElements() {
            if (assessmentFullscreenState.overlay) {
                return assessmentFullscreenState;
            }

            if (typeof document === 'undefined' || !document.body) {
                return assessmentFullscreenState;
            }

            const overlay = createElementFromHTML(
                '<div class="vet-record-form__fullscreen-overlay" id="recordAssessmentFullscreenOverlay" hidden aria-hidden="true">' +
                    '<div class="vet-record-form__fullscreen-wrapper" role="dialog" aria-modal="true" aria-labelledby="recordAssessmentTitle">' +
                        
                        '<div class="vet-record-form__fullscreen-card-container"></div>' +
                    '</div>' +
                '</div>'
            );

            if (!overlay) {
                return assessmentFullscreenState;
            }

            const wrapper = overlay.querySelector('.vet-record-form__fullscreen-wrapper');
            const cardContainer = overlay.querySelector('.vet-record-form__fullscreen-card-container') || wrapper;
            const closeButton = overlay.querySelector('[data-action="close"]');

            document.body.appendChild(overlay);

            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) {
                    exitAssessmentFullscreen();
                }
            });

            if (closeButton) {
                closeButton.addEventListener('click', function () {
                    exitAssessmentFullscreen();
                });
            }

            assessmentFullscreenState.overlay = overlay;
            assessmentFullscreenState.wrapper = wrapper;
            assessmentFullscreenState.cardContainer = cardContainer;
            assessmentFullscreenState.closeButton = closeButton;

            return assessmentFullscreenState;
        }

        function ensureAssessmentPlaceholder() {
            if (assessmentFullscreenState.placeholder || !assessmentCard || !assessmentCard.parentNode) {
                return;
            }

            const placeholder = document.createElement('div');
            placeholder.className = 'vet-record-form__assessment-placeholder';
            assessmentCard.parentNode.insertBefore(placeholder, assessmentCard);
            assessmentFullscreenState.placeholder = placeholder;
        }

        function enterAssessmentFullscreen() {
            if (!assessmentCard) {
                return;
            }

            ensureAssessmentPlaceholder();
            const state = ensureAssessmentFullscreenElements();
            const overlay = state.overlay;
            const cardContainer = state.cardContainer;

            if (!overlay || !cardContainer) {
                return;
            }

            cardContainer.appendChild(assessmentCard);
            overlay.classList.add('is-active');
            overlay.removeAttribute('hidden');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('vet-record-form__no-scroll');
            assessmentFullscreenState.isActive = true;
            updateAssessmentFullscreenTrigger();
            debugLog('Modo tela grande da avaliação clínica ativado');

            if (state.closeButton && typeof state.closeButton.focus === 'function') {
                try {
                    state.closeButton.focus({ preventScroll: true });
                } catch (error) {
                    state.closeButton.focus();
                }
            }
        }

        function exitAssessmentFullscreen() {
            if (!assessmentFullscreenState.isActive) {
                return;
            }

            const overlay = assessmentFullscreenState.overlay;
            if (overlay) {
                overlay.classList.remove('is-active');
                overlay.setAttribute('aria-hidden', 'true');
                overlay.setAttribute('hidden', 'hidden');
            }

            if (assessmentFullscreenState.placeholder && assessmentFullscreenState.placeholder.parentNode && assessmentCard) {
                const referenceNode = assessmentFullscreenState.placeholder.nextSibling;
                if (referenceNode) {
                    assessmentFullscreenState.placeholder.parentNode.insertBefore(assessmentCard, referenceNode);
                } else {
                    assessmentFullscreenState.placeholder.parentNode.appendChild(assessmentCard);
                }
            }

            document.body.classList.remove('vet-record-form__no-scroll');
            assessmentFullscreenState.isActive = false;
            updateAssessmentFullscreenTrigger();
            debugLog('Modo tela grande da avaliação clínica desativado');

            if (assessmentFullscreenToggle && typeof assessmentFullscreenToggle.focus === 'function') {
                try {
                    assessmentFullscreenToggle.focus({ preventScroll: true });
                } catch (error) {
                    assessmentFullscreenToggle.focus();
                }
            }
        }

        if (assessmentFullscreenToggle && assessmentCard) {
            ensureAssessmentPlaceholder();
            ensureAssessmentFullscreenElements();
            updateAssessmentFullscreenTrigger();

            assessmentFullscreenToggle.addEventListener('click', function () {
                if (assessmentFullscreenState.isActive) {
                    exitAssessmentFullscreen();
                } else {
                    enterAssessmentFullscreen();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && assessmentFullscreenState.isActive) {
                exitAssessmentFullscreen();
            }
        });

        if (templateSelect && Array.isArray(assessmentModels) && !assessmentModels.length) {
            templateSelect.disabled = true;
        }

        const patientPhoto = document.getElementById('recordPatientPhoto');
        const patientName = document.getElementById('recordPatientName');
        const patientMeta = document.getElementById('recordPatientMeta');
        const patientTags = document.getElementById('recordPatientTags');
        const patientAlerts = document.getElementById('recordPatientAlerts');
        const patientConditions = document.getElementById('recordPatientConditions');
        const patientMedications = document.getElementById('recordPatientMedications');
        const patientContacts = document.getElementById('recordPatientContacts');
        const patientDetails = document.getElementById('recordPatientDetails');
        const patientSummary = document.getElementById('recordPatientSummary');
        const patientSummaryWeight = document.getElementById('recordPatientSummaryWeight');
        const patientSummarySex = document.getElementById('recordPatientSummarySex');
        const patientSummaryBirthDate = document.getElementById('recordPatientSummaryBirthDate');
        const patientSummaryLastVisit = document.getElementById('recordPatientSummaryLastVisit');
        const patientSummarySize = document.getElementById('recordPatientSummarySize');
        const patientSummaryOrigin = document.getElementById('recordPatientSummaryOrigin');
        const patientSummaryMicrochip = document.getElementById('recordPatientSummaryMicrochip');
        const patientSummaryPedigree = document.getElementById('recordPatientSummaryPedigree');
        const tutorSummaryName = document.getElementById('recordTutorSummaryName');
        const tutorSummaryDocument = document.getElementById('recordTutorSummaryDocument');
        const tutorSummaryContacts = document.getElementById('recordTutorSummaryContacts');
        const tutorSummaryEmail = document.getElementById('recordTutorSummaryEmail');
        const tutorSummaryAddress = document.getElementById('recordTutorSummaryAddress');

        const defaultPatientPhoto =
            (patientPhoto && patientPhoto.dataset && patientPhoto.dataset.defaultPhoto)
                || root.dataset.defaultPatientPhoto
                || (patientPhoto ? patientPhoto.getAttribute('src') : '');

        const veterinarianInfo = document.getElementById('recordVeterinarianInfo');
        const vitalNotesContainer = document.getElementById('recordVitalNotes');
        const monitoringNotesContainer = document.getElementById('recordMonitoringNotes');
        const attachmentList = document.getElementById('recordAttachmentList');
        const attachmentAddButton = document.getElementById('recordAttachmentAdd');
        const attachmentInput = document.getElementById('recordAttachmentInput');
        const attachmentInputsContainer = document.getElementById('recordAttachmentInputs');
        const attachmentEmptyState = document.getElementById('recordAttachmentEmpty');
        const checklistContainer = document.getElementById('recordChecklist');
        const communicationContainer = document.getElementById('recordCommunicationCards');
        const reminderContainer = document.getElementById('recordReminderList');
        const quickNotesContainer = document.getElementById('recordQuickNotes');
        const timelineContainer = document.getElementById('recordTimeline');
        const communicationCopyButton = document.getElementById('recordCommunicationCopy');
        const servicesSection = document.getElementById('recordServicesSection');
        const servicesBody = document.getElementById('recordServicesBody');
        const servicesEmptyState = document.getElementById('recordServicesEmptyState');
        const servicesSubtotalDisplay = document.getElementById('recordServicesSubtotal');
        const servicesTotalDisplay = document.getElementById('recordServicesTotal');
        const servicesAddButton = document.getElementById('recordServicesAdd');
        const feedbackContainer = document.getElementById('recordFormFeedback');
        const submitButton = document.getElementById('recordSubmitButton');
        const draftButton = document.getElementById('recordDraftButton');
        debugLog('Elementos auxiliares capturados', {
            patientPhoto: Boolean(patientPhoto),
            patientName: Boolean(patientName),
            patientMeta: Boolean(patientMeta),
            patientTags: Boolean(patientTags),
            patientAlerts: Boolean(patientAlerts),
            patientConditions: Boolean(patientConditions),
            patientMedications: Boolean(patientMedications),
            patientContacts: Boolean(patientContacts),
            patientDetails: Boolean(patientDetails),
            patientSummary: Boolean(patientSummary),
            patientSummaryWeight: Boolean(patientSummaryWeight),
            patientSummarySex: Boolean(patientSummarySex),
            patientSummaryBirthDate: Boolean(patientSummaryBirthDate),
            patientSummaryLastVisit: Boolean(patientSummaryLastVisit),
            patientSummarySize: Boolean(patientSummarySize),
            patientSummaryOrigin: Boolean(patientSummaryOrigin),
            patientSummaryMicrochip: Boolean(patientSummaryMicrochip),
            patientSummaryPedigree: Boolean(patientSummaryPedigree),
            tutorSummaryName: Boolean(tutorSummaryName),
            tutorSummaryDocument: Boolean(tutorSummaryDocument),
            tutorSummaryContacts: Boolean(tutorSummaryContacts),
            tutorSummaryEmail: Boolean(tutorSummaryEmail),
            tutorSummaryAddress: Boolean(tutorSummaryAddress),
            veterinarianInfo: Boolean(veterinarianInfo),
            vitalNotesContainer: Boolean(vitalNotesContainer),
            monitoringNotesContainer: Boolean(monitoringNotesContainer),
            attachmentList: Boolean(attachmentList),
            attachmentAddButton: Boolean(attachmentAddButton),
            attachmentInput: Boolean(attachmentInput),
            attachmentInputsContainer: Boolean(attachmentInputsContainer),
            attachmentEmptyState: Boolean(attachmentEmptyState),
            checklistContainer: Boolean(checklistContainer),
            communicationContainer: Boolean(communicationContainer),
            reminderContainer: Boolean(reminderContainer),
            quickNotesContainer: Boolean(quickNotesContainer),
            timelineContainer: Boolean(timelineContainer),
            communicationCopyButton: Boolean(communicationCopyButton),
            servicesSection: Boolean(servicesSection),
            servicesBody: Boolean(servicesBody),
            servicesEmptyState: Boolean(servicesEmptyState),
            servicesSubtotalDisplay: Boolean(servicesSubtotalDisplay),
            servicesTotalDisplay: Boolean(servicesTotalDisplay),
            servicesAddButton: Boolean(servicesAddButton),
            feedbackContainer: Boolean(feedbackContainer),
            submitButton: Boolean(submitButton),
            draftButton: Boolean(draftButton),
        });

        let serviceRowCounter = 0;
        let servicesSectionInitialized = false;

        function findServiceById(id) {
            if (!id) {
                return null;
            }

            return serviceCatalogMap.get(normalizeId(id)) || null;
        }

        function populateServiceSelect(selectElement, selectedId) {
            if (!selectElement) {
                return;
            }

            selectElement.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Selecione o serviço';
            selectElement.appendChild(placeholder);

            serviceCatalog.forEach(function (service) {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;

                if (selectedId && isSameId(service.id, selectedId)) {
                    option.selected = true;
                }

                selectElement.appendChild(option);
            });
        }

        function setCurrencyInputValue(input, value) {
            if (!input) {
                return;
            }

            const numeric = parseCurrency(value);
            input.value = formatCurrencyInput(numeric);
            input.dataset.numericValue = String(numeric);
        }

        function updateServiceRowDetails(row, serviceId, options) {
            if (!row) {
                return;
            }

            const settings = options || {};
            const descriptionElement = row.querySelector('[data-role="service-description"]');
            const unitPriceInput = row.querySelector('[data-role="service-unit-price"]');
            const serviceNameInput = row.querySelector('[data-role="service-name-input"]');
            const service = findServiceById(serviceId);
            const manualPrice = row.dataset.manualPrice === '1';

            if (service) {
                if (serviceNameInput) {
                    serviceNameInput.value = service.name || '';
                }

                const metaParts = [];
                if (service.category) {
                    metaParts.push(service.category);
                }
                if (service.duration) {
                    metaParts.push(service.duration);
                }

                let descriptionText = typeof service.description === 'string' ? service.description : '';
                if (metaParts.length) {
                    descriptionText = descriptionText
                        ? descriptionText + ' • ' + metaParts.join(' • ')
                        : metaParts.join(' • ');
                }

                if (descriptionElement) {
                    descriptionElement.textContent = descriptionText || 'Serviço sem descrição adicional.';
                    descriptionElement.classList.add('text-secondary');
                    descriptionElement.classList.remove('text-muted');
                }

                const shouldApplyDefaultPrice =
                    service.price !== undefined &&
                    service.price !== null &&
                    (settings.forcePriceUpdate || (!manualPrice && !settings.preserveManualPrice) || parseCurrency(unitPriceInput ? unitPriceInput.value : 0) <= 0);

                if (unitPriceInput && shouldApplyDefaultPrice) {
                    setCurrencyInputValue(unitPriceInput, service.price);
                    row.dataset.manualPrice = '0';
                }
            } else {
                if (serviceNameInput) {
                    serviceNameInput.value = '';
                }

                if (descriptionElement) {
                    descriptionElement.textContent = 'Selecione o serviço para visualizar detalhes e valor sugerido.';
                    descriptionElement.classList.add('text-muted');
                    descriptionElement.classList.remove('text-secondary');
                }

                if (unitPriceInput && !settings.preserveManualPrice) {
                    setCurrencyInputValue(unitPriceInput, 0);
                    row.dataset.manualPrice = '0';
                }
            }
        }

        function updateServiceRowTotal(row) {
            if (!row) {
                return 0;
            }

            const quantityInput = row.querySelector('[data-role="service-quantity"]');
            const unitPriceInput = row.querySelector('[data-role="service-unit-price"]');
            const totalElement = row.querySelector('[data-role="service-total"]');
            const totalInput = row.querySelector('[data-role="service-total-input"]');

            const quantityValue = quantityInput ? parseInt(quantityInput.value, 10) : 0;
            const quantity = Number.isFinite(quantityValue) && quantityValue > 0 ? quantityValue : 1;

            if (quantityInput && (!quantityInput.value || parseInt(quantityInput.value, 10) !== quantity)) {
                quantityInput.value = String(quantity);
            }

            const unitPrice = unitPriceInput ? parseCurrency(unitPriceInput.value) : 0;
            const total = quantity * unitPrice;

            if (totalElement) {
                totalElement.textContent = formatCurrencyDisplay(total);
            }

            if (totalInput) {
                totalInput.value = Number.isFinite(total) ? total.toFixed(2) : '0';
            }

            row.dataset.total = String(Number.isFinite(total) ? total : 0);

            return Number.isFinite(total) ? total : 0;
        }

        function updateServicesTotals() {
            let subtotal = 0;

            if (servicesBody) {
                const rows = servicesBody.querySelectorAll('tr[data-service-row]');
                rows.forEach(function (row) {
                    subtotal += parseFloat(row.dataset.total || '0') || 0;
                });
            }

            if (servicesSubtotalDisplay) {
                servicesSubtotalDisplay.textContent = formatCurrencyDisplay(subtotal);
            }

            if (servicesTotalDisplay) {
                servicesTotalDisplay.textContent = formatCurrencyDisplay(subtotal);
            }

            return subtotal;
        }

        function updateServicesEmptyState() {
            if (!servicesBody || !servicesEmptyState) {
                return;
            }

            const hasRows = Boolean(servicesBody.querySelector('tr[data-service-row]'));
            servicesEmptyState.style.display = hasRows ? 'none' : '';
        }

        function resetServiceRows() {
            if (!servicesBody) {
                return;
            }

            const rows = servicesBody.querySelectorAll('tr[data-service-row]');
            rows.forEach(function (row) {
                const selectElement = row.querySelector('[data-role="service-select"]');
                if (selectElement && window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
                    const $select = window.jQuery(selectElement);
                    if ($select.data('select2')) {
                        $select.select2('destroy');
                    }
                }

                row.remove();
            });

            updateServicesEmptyState();
            updateServicesTotals();
        }

        function createServiceRow(prefillData) {
            if (!servicesBody) {
                return null;
            }

            serviceRowCounter += 1;
            const rowKey = 'service-' + Date.now() + '-' + serviceRowCounter;
            const serviceCreateAction = serviceCreateUrl
                ? `<a href="${serviceCreateUrl}" target="_blank" class="btn btn-dark btn-sm px-2" data-role="service-create-link" title="Cadastrar novo serviço">
                        <i class="ri-add-circle-fill"></i>
                    </a>`
                : '';

            const row = createElementFromHTML(`
                <tr data-service-row="true" data-row-key="${rowKey}" class="dynamic-form">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select" data-role="service-select" name="services[${rowKey}][service_id]" data-placeholder="Selecione o serviço"></select>
                            ${serviceCreateAction}
                        </div>
                        <input type="hidden" data-role="service-name-input" name="services[${rowKey}][service_name]" value="">
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control text-center" data-role="service-quantity" name="services[${rowKey}][quantity]" min="1" step="1" value="1">
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control text-end" data-role="service-unit-price" name="services[${rowKey}][unit_price]" inputmode="decimal" autocomplete="off" value="0,00">
                        </div>
                    </td>
                    <td class="text-end">
                        <span class="fw-semibold" data-role="service-total">R$ 0,00</span>
                        <input type="hidden" data-role="service-total-input" name="services[${rowKey}][total]" value="0">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-link text-danger p-0 vet-record-form__service-remove" data-role="service-remove" title="Remover serviço">
                            <i class="ri-delete-bin-line fs-5"></i>
                        </button>
                    </td>
                </tr>
            `);

            const selectElement = row.querySelector('[data-role="service-select"]');
            const quantityInput = row.querySelector('[data-role="service-quantity"]');
            const unitPriceInput = row.querySelector('[data-role="service-unit-price"]');
            const removeButton = row.querySelector('[data-role="service-remove"]');

            const serviceId = prefillData && (prefillData.service_id || prefillData.serviceId || prefillData.id || prefillData.service)
                ? prefillData.service_id || prefillData.serviceId || prefillData.id || prefillData.service
                : '';
            const quantityPrefill = prefillData && prefillData.quantity ? parseInt(prefillData.quantity, 10) : 1;
            const quantityValue = Number.isFinite(quantityPrefill) && quantityPrefill > 0 ? quantityPrefill : 1;

            const prefilledUnitPrice = prefillData && Object.prototype.hasOwnProperty.call(prefillData, 'unit_price')
                ? parseCurrency(prefillData.unit_price)
                : null;

            if (quantityInput) {
                quantityInput.value = String(quantityValue);
            }

            if (unitPriceInput) {
                if (prefilledUnitPrice !== null && Number.isFinite(prefilledUnitPrice)) {
                    setCurrencyInputValue(unitPriceInput, prefilledUnitPrice);
                    row.dataset.manualPrice = '1';
                } else {
                    setCurrencyInputValue(unitPriceInput, 0);
                    row.dataset.manualPrice = '0';
                }
            } else {
                row.dataset.manualPrice = prefilledUnitPrice !== null ? '1' : '0';
            }

            populateServiceSelect(selectElement, serviceId);

            servicesBody.appendChild(row);
            updateServicesEmptyState();

            updateServiceRowDetails(row, serviceId, {
                preserveManualPrice: row.dataset.manualPrice === '1',
                forcePriceUpdate: prefilledUnitPrice === null,
            });

            updateServiceRowTotal(row);
            updateServicesTotals();

            if (selectElement) {
                selectElement.addEventListener('change', function () {
                    row.dataset.manualPrice = '0';
                    updateServiceRowDetails(row, selectElement.value, { forcePriceUpdate: true });
                    updateServiceRowTotal(row);
                    updateServicesTotals();
                });

                if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
                    const $select = window.jQuery(selectElement);
                    const dropdownParent = $select.closest('.vet-record-form__card');

                    $select.select2({
                        width: '100%',
                        placeholder: selectElement.getAttribute('data-placeholder') || 'Selecione o serviço',
                        allowClear: true,
                        dropdownParent: dropdownParent.length ? dropdownParent : $select.parent(),
                    });
                }
            }

            if (quantityInput) {
                quantityInput.addEventListener('change', function () {
                    updateServiceRowTotal(row);
                    updateServicesTotals();
                });

                quantityInput.addEventListener('input', function () {
                    const value = parseInt(quantityInput.value, 10);
                    if (!Number.isFinite(value) || value <= 0) {
                        return;
                    }

                    updateServiceRowTotal(row);
                    updateServicesTotals();
                });
            }

            if (unitPriceInput) {
                const commitUnitPrice = function () {
                    const numeric = parseCurrency(unitPriceInput.value);
                    setCurrencyInputValue(unitPriceInput, numeric);
                    row.dataset.manualPrice = '1';
                    updateServiceRowTotal(row);
                    updateServicesTotals();
                };

                unitPriceInput.addEventListener('focus', function () {
                    unitPriceInput.select();
                });

                unitPriceInput.addEventListener('input', function () {
                    row.dataset.manualPrice = '1';
                });

                unitPriceInput.addEventListener('change', commitUnitPrice);
                unitPriceInput.addEventListener('blur', commitUnitPrice);
            }

            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    if (selectElement && window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
                        const $select = window.jQuery(selectElement);
                        if ($select.data('select2')) {
                            $select.select2('destroy');
                        }
                    }

                    row.remove();
                    updateServicesEmptyState();
                    updateServicesTotals();
                });
            }

            if (!prefillData) {
                setTimeout(function () {
                    if (selectElement) {
                        if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
                            const $select = window.jQuery(selectElement);
                            if ($select.data('select2')) {
                                $select.select2('open');
                                return;
                            }
                        }

                        selectElement.focus();
                    }
                }, 0);
            }

            return row;
        }

        function loadPrefilledServices(servicesData) {
            if (!servicesBody) {
                return;
            }

            resetServiceRows();

            if (!Array.isArray(servicesData) || !servicesData.length) {
                updateServicesEmptyState();
                updateServicesTotals();
                return;
            }

            servicesData.forEach(function (item) {
                createServiceRow(item);
            });
        }

        function initializeServiceSection() {
            if (!servicesBody || !servicesSection) {
                return;
            }

            if (servicesSectionInitialized) {
                updateServicesEmptyState();
                updateServicesTotals();
                return;
            }

            servicesSectionInitialized = true;
            updateServicesEmptyState();
            updateServicesTotals();

            if (servicesAddButton) {
                if (!Array.isArray(serviceCatalog) || !serviceCatalog.length) {
                    servicesAddButton.disabled = true;
                    servicesAddButton.classList.add('disabled');
                    servicesAddButton.setAttribute('title', 'Cadastre serviços no catálogo para habilitar esta área.');
                } else {
                    servicesAddButton.addEventListener('click', function () {
                        createServiceRow(null);
                    });
                }
            }
        }

        let activeCommunicationIndex = 0;
        let selectedPatientId = '';

        if (Array.isArray(communications)) {
            const initialActiveIndex = communications.findIndex(function (template) {
                return template && (template.active || template.is_active);
            });

            if (initialActiveIndex >= 0) {
                activeCommunicationIndex = initialActiveIndex;
            }
        }

        function deepClone(value) {
            try {
                return JSON.parse(JSON.stringify(value));
            } catch (error) {
                return value;
            }
        }

        let currentRecordId = dataset.recordId ? normalizeId(dataset.recordId) : '';
        if (!currentRecordId && prefill && prefill.id !== undefined && prefill.id !== null) {
            currentRecordId = normalizeId(prefill.id);
        }

        let currentAttendanceId = attendanceIdFromDataset ? normalizeId(attendanceIdFromDataset) : '';
        if (!currentAttendanceId && prefill && prefill.attendance && prefill.attendance.id) {
            currentAttendanceId = normalizeId(prefill.attendance.id);
        }

        let currentStatus = currentStatusFromDataset || (prefill && prefill.status) || '';

        let currentPatientSnapshot = prefill && prefill.patient_snapshot ? deepClone(prefill.patient_snapshot) : null;
        let defaultTutorSnapshot = prefill && prefill.tutor ? deepClone(prefill.tutor) : null;
        let currentTutorSnapshot = defaultTutorSnapshot ? deepClone(defaultTutorSnapshot) : null;
        let currentTriageSnapshot = prefill && prefill.triage ? deepClone(prefill.triage) : null;
        let currentVitalSigns = currentTriageSnapshot && Array.isArray(currentTriageSnapshot.vital_signs)
            ? deepClone(currentTriageSnapshot.vital_signs)
            : [];

        if (!selectedPatientId) {
            if (currentPatientSnapshot && currentPatientSnapshot.id) {
                selectedPatientId = normalizeId(currentPatientSnapshot.id);
            } else if (prefill && prefill.patient && prefill.patient.id) {
                selectedPatientId = normalizeId(prefill.patient.id);
            }
        }

        const assessmentModelCache = new Map();
        if (Array.isArray(assessmentModels)) {
            assessmentModels.forEach(function (model) {
                const cachedModel = normalizeAssessmentModel(model);

                if (cachedModel && cachedModel.id) {
                    assessmentModelCache.set(cachedModel.id, cachedModel);
                }
            });
        }
        const assessmentFieldAliases = {
            'anamnese-e-historico': 'recordAnamnesis',
            'exame-fisico-e-sinais-vitais': 'recordPhysicalExam',
            'plano-terapeutico-e-condutas': 'recordPlan',
        };
        const assessmentFieldIdUsage = new Set();
        const assessmentFieldNameCount = {};

        function formatPatientOption(option) {
            if (!option.id || !window.jQuery) {
                return option.text;
            }

            const patient = findPatientById(option.id);
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

            const patient = findPatientById(option.id);
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

            const veterinarian = findVeterinarianById(option.id);
            if (!veterinarian) {
                return option.text;
            }

            const $container = window.jQuery('<div class="d-flex flex-column"></div>');
            window.jQuery('<span class="fw-semibold text-secondary"></span>').text(veterinarian.name).appendTo($container);

            if (veterinarian.specialty) {
                window.jQuery('<small class="text-muted"></small>').text(veterinarian.specialty).appendTo($container);
            }

            return $container;
        }

        function formatVeterinarianSelection(option) {
            if (!option.id) {
                return option.text;
            }

            const veterinarian = findVeterinarianById(option.id);
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

            if (patientSelect) {
                const $patientSelect = $(patientSelect);
                const placeholder = patientSelect.getAttribute('data-placeholder') || 'Selecione o paciente';
                const allowClear = patientSelect.getAttribute('data-allow-clear') === 'true';
                const dropdownParent = $patientSelect.closest('.vet-record-form__card');

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
                const dropdownParent = $veterinarianSelect.closest('.vet-record-form__card');

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

        function findPatientById(id) {
            return patients.find(function (patient) {
                return isSameId(patient.id, id);
            }) || null;
        }

        function findVeterinarianById(id) {
            return veterinarians.find(function (veterinarian) {
                return isSameId(veterinarian.id, id);
            }) || null;
        }

        function findSlotLabelByValue(value) {
            const slot = slots.find(function (item) {
                return isSameId(item.value, value);
            });

            return slot ? slot.label : '';
        }

        function renderTags(container, items) {
            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (!items || !items.length) {
                return;
            }

            items.forEach(function (tag) {
                const span = document.createElement('span');
                span.className = 'badge bg-light text-secondary';
                span.textContent = tag;
                container.appendChild(span);
            });
        }

        function renderList(container, items, emptyMessage) {
            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (!items || !items.length) {
                const li = document.createElement('li');
                li.className = 'text-muted';
                li.textContent = emptyMessage;
                container.appendChild(li);
                return;
            }

            items.forEach(function (item) {
                const li = document.createElement('li');
                li.className = 'd-flex align-items-start gap-2 mb-1';
                li.innerHTML = '<i class="ri-checkbox-circle-line text-success"></i><span>' + item + '</span>';
                container.appendChild(li);
            });
        }

        function renderMedications(container, medications) {
            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (!medications || !medications.length) {
                const li = document.createElement('li');
                li.className = 'text-muted';
                li.textContent = 'Preencha após revisar o prontuário.';
                container.appendChild(li);
                return;
            }

            medications.forEach(function (medication) {
                const li = document.createElement('li');
                li.innerHTML = '<strong>' + medication.name + '</strong><br><small class="text-muted">' + medication.schedule + '</small>';
                container.appendChild(li);
            });
        }

        function renderContacts(container, contacts) {
            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (!contacts || !contacts.length) {
                const li = document.createElement('li');
                li.className = 'text-muted';
                li.textContent = 'Disponível após selecionar o paciente.';
                container.appendChild(li);
                return;
            }

            contacts.forEach(function (contact) {
                const normalized = typeof contact === 'object' && contact !== null ? contact : { value: contact };
                const type = normalized.type || 'Contato';
                const value = normalized.value || '';

                if (!value) {
                    return;
                }

                const li = document.createElement('li');
                li.innerHTML = '<strong>' + type + ':</strong> <span class="text-muted">' + value + '</span>';
                container.appendChild(li);
            });

            if (!container.children.length) {
                const li = document.createElement('li');
                li.className = 'text-muted';
                li.textContent = 'Disponível após selecionar o paciente.';
                container.appendChild(li);
            }
        }

        function renderPatientDetails(container, patient) {
            if (!container) {
                console.warn('[vet/prontuarios] Container de detalhes do paciente não encontrado.');
                return;
            }
            container.innerHTML = '';

            if (!patient) {
                const li = document.createElement('li');
                li.className = 'text-muted';
                li.textContent = 'Selecione um paciente para visualizar os detalhes cadastrais.';
                container.appendChild(li);
                return;
            }

            const primaryContact = patient.primary_contact
                ? [patient.primary_contact.type, patient.primary_contact.value].filter(Boolean).join(': ')
                : '';

            const details = [
                { label: 'Espécie', value: patient.species },
                { label: 'Raça', value: patient.breed },
                { label: 'Sexo', value: patient.gender },
                { label: 'Idade', value: patient.age },
                { label: 'Peso', value: patient.weight },
                { label: 'Última visita', value: patient.last_visit },
                { label: 'Próximo retorno', value: patient.next_follow_up },
                { label: 'Tutor(a)', value: patient.tutor },
                { label: 'Contato principal', value: primaryContact },
            ].filter(function (detail) {
                return detail.value && detail.value !== '';
            });

            if (!details.length) {
                const li = document.createElement('li');
                li.className = 'text-muted';
                li.textContent = 'Sem detalhes adicionais cadastrados.';
                container.appendChild(li);
                return;
            }

            details.forEach(function (detail) {
                const li = document.createElement('li');
                const term = document.createElement('span');
                term.className = 'vet-record-form__details-term';
                term.textContent = detail.label;

                const value = document.createElement('span');
                value.className = 'text-secondary';
                value.textContent = detail.value;

                li.appendChild(term);
                li.appendChild(value);
                container.appendChild(li);
            });
        }

        function renderPatientSummary(element, patient) {
            if (!element) {
                return;
            }

            const defaultMessage = 'Sem observações clínicas registradas.';

            if (!patient) {
                element.textContent = defaultMessage;
                element.classList.add('text-muted');
                element.classList.remove('text-secondary');
                return;
            }

            if (patient.summary) {
                element.textContent = patient.summary;
                element.classList.add('text-secondary');
                element.classList.remove('text-muted');
                return;
            }

            if (patient.recent_notes && patient.recent_notes.length) {
                element.textContent = patient.recent_notes[0].content;
                element.classList.add('text-secondary');
                element.classList.remove('text-muted');
                return;
            }

            element.textContent = defaultMessage;
            element.classList.add('text-muted');
            element.classList.remove('text-secondary');
        }

        function renderAlerts(container, alerts) {
            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (!alerts || !alerts.length) {
                const empty = document.createElement('div');
                empty.className = 'vet-record-form__alert-item';
                empty.innerHTML = '<i class="ri-shield-check-line text-success"></i><div class="flex-fill"><div class="text-muted small">Paciente sem alertas relevantes.</div></div>';
                container.appendChild(empty);
                return;
            }

            alerts.forEach(function (alert) {
                const item = document.createElement('div');
                item.className = 'vet-record-form__alert-item';
                item.innerHTML = '<i class="' + alert.icon + ' text-' + (alert.type || 'primary') + '"></i><div class="flex-fill"><div class="fw-semibold text-secondary">' + alert.text + '</div></div>';
                container.appendChild(item);
            });
        }

        function renderCards(container, notes, iconClass) {
            if (!container) {
                return;
            }

            container.innerHTML = '';

            if (!notes || !notes.length) {
                return;
            }

            notes.forEach(function (note) {
                const col = document.createElement('div');
                col.className = 'col';
                col.innerHTML = `
                    <div class="border rounded-4 p-3 h-100 bg-white">
                        <span class="text-muted small d-block">${note.label}</span>
                        <div class="fw-semibold text-primary fs-5">${note.value}</div>
                    </div>
                `;
                container.appendChild(col);
            });
        }

        function clearAssessmentFieldRegistries() {
            assessmentFieldIdUsage.clear();
            Object.keys(assessmentFieldNameCount).forEach(function (key) {
                delete assessmentFieldNameCount[key];
            });
        }

        function resetAssessmentSummary() {
            if (!templateSummary) {
                return;
            }

            if (initialTemplateSummaryClassName) {
                templateSummary.className = initialTemplateSummaryClassName;
            } else {
                templateSummary.className = 'alert alert-soft-primary d-flex gap-3 align-items-start';
            }

            templateSummary.innerHTML = initialTemplateSummaryHtml || '';
        }

        function showAssessmentLoading() {
            if (!templateSummary) {
                return;
            }

            templateSummary.className = 'alert alert-soft-primary d-flex gap-3 align-items-start';
            templateSummary.innerHTML = `
                <span class="spinner-border spinner-border-sm text-primary mt-1" role="status" aria-hidden="true"></span>
                <div>
                    <h6 class="fw-semibold text-primary mb-1">Carregando modelo selecionado...</h6>
                    <p class="mb-0 text-muted">Aguarde enquanto buscamos os campos configurados.</p>
                </div>
            `;
        }

        function renderAssessmentSummary(model) {
            if (!templateSummary) {
                return;
            }

            const fieldsCount = typeof model.fields_count === 'number'
                ? model.fields_count
                : Array.isArray(model.fields)
                    ? model.fields.length
                    : 0;

            const notesHtml = model.notes
                ? `<small class="text-muted d-block mt-1">${escapeHtml(model.notes)}</small>`
                : '';

            templateSummary.className = 'alert alert-success d-flex gap-3 align-items-start';
            templateSummary.innerHTML = `
                <i class="ri-checkbox-circle-line fs-3 text-success"></i>
                <div>
                    <h6 class="fw-semibold text-success mb-1">${escapeHtml(model.title || 'Modelo selecionado')}</h6>
                    <p class="mb-0 text-muted">${escapeHtml(model.category_label || 'Personalizado')} • ${fieldsCount} campo(s)</p>
                    ${notesHtml}
                </div>
            `;
        }

        function showAssessmentError(message) {
            if (!templateSummary) {
                return;
            }

            templateSummary.className = 'alert alert-danger d-flex gap-3 align-items-start';
            templateSummary.innerHTML = `
                <i class="ri-error-warning-line fs-3 text-danger"></i>
                <div>
                    <h6 class="fw-semibold text-danger mb-1">Não foi possível carregar o modelo.</h6>
                    <p class="mb-0 text-muted">${escapeHtml(message)}</p>
                </div>
            `;
        }

        function resetAssessmentFields() {
            if (!assessmentFieldsContainer) {
                return;
            }

            clearAssessmentFieldRegistries();
            destroyRichTextEditors(assessmentFieldsContainer);
            destroyAssessmentSelectEnhancements(assessmentFieldsContainer);
            assessmentFieldsContainer.innerHTML = initialAssessmentFieldsContent || '';
        }

        function showAssessmentFieldsLoading() {
            if (!assessmentFieldsContainer) {
                return;
            }

            clearAssessmentFieldRegistries();
            destroyRichTextEditors(assessmentFieldsContainer);
            destroyAssessmentSelectEnhancements(assessmentFieldsContainer);
            assessmentFieldsContainer.innerHTML = `
                <div class="d-flex align-items-center justify-content-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Carregando campos do modelo...
                </div>
            `;
        }

        function showAssessmentFieldsError(message) {
            if (!assessmentFieldsContainer) {
                return;
            }

            clearAssessmentFieldRegistries();
            destroyRichTextEditors(assessmentFieldsContainer);
            destroyAssessmentSelectEnhancements(assessmentFieldsContainer);
            assessmentFieldsContainer.innerHTML = `
                <div class="alert alert-soft-danger mb-0" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <i class="ri-error-warning-line text-danger fs-4 mt-1"></i>
                        <div>
                            <h6 class="fw-semibold text-danger mb-1">Falha ao carregar os campos.</h6>
                            <p class="mb-0 text-muted">${escapeHtml(message)}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function generateFieldId(field, index) {
            const slug = slugify(field.label || '');
            const alias = assessmentFieldAliases[slug];

            if (alias && !assessmentFieldIdUsage.has(alias)) {
                assessmentFieldIdUsage.add(alias);
                return alias;
            }

            let base = slug || 'assessment-field-' + (index + 1);

            if (assessmentFieldIdUsage.has(base)) {
                let counter = 2;

                while (assessmentFieldIdUsage.has(base + '-' + counter)) {
                    counter += 1;
                }

                base = base + '-' + counter;
            }

            assessmentFieldIdUsage.add(base);
            return base;
        }

        function generateFieldName(field, index, multiple) {
            let base = slugify(field.label || '');

            if (!base) {
                base = 'campo-' + (index + 1);
            }

            const count = assessmentFieldNameCount[base] || 0;
            assessmentFieldNameCount[base] = count + 1;

            const uniqueBase = count === 0 ? base : base + '-' + (count + 1);
            return 'assessment_model_fields[' + uniqueBase + ']' + (multiple ? '[]' : '');
        }

        function buildAssessmentFieldElement(field, index) {
            if (!field || typeof field !== 'object') {
                return null;
            }

            const type = (field.type || 'text').toLowerCase();
            const label = field.label || 'Campo ' + (index + 1);
            const config = typeof field.config === 'object' && field.config !== null ? field.config : {};
            const isMultipleValue = type === 'multi_select' || type === 'checkbox_group';
            const fieldId = generateFieldId(field, index);
            const fieldName = generateFieldName(field, index, isMultipleValue);

            const container = document.createElement('div');
            container.className = 'mb-4';
            container.setAttribute('data-assessment-field-type', type);

            const labelElement = document.createElement('label');
            labelElement.className = 'form-label fw-semibold text-secondary';
            if (type !== 'checkbox_group' && type !== 'radio_group') {
                labelElement.setAttribute('for', fieldId);
            }
            labelElement.textContent = label;
            container.appendChild(labelElement);

            const helpMessages = [];
            const placeholder = config.placeholder
                || config.textarea_placeholder
                || config.date_hint
                || config.time_hint
                || config.datetime_hint
                || config.email_placeholder
                || config.phone_placeholder
                || '';

            let control = null;

            switch (type) {
                case 'text':
                case 'email':
                case 'phone': {
                    const input = document.createElement('input');
                    input.type = type === 'phone' ? 'tel' : type;
                    input.className = 'form-control';
                    input.id = fieldId;
                    input.name = fieldName;
                    input.placeholder = placeholder;

                    if (type === 'phone') {
                        input.inputMode = 'tel';
                    }

                    control = input;
                    break;
                }
                case 'textarea': {
                    const textarea = document.createElement('textarea');
                    textarea.className = 'form-control';
                    textarea.id = fieldId;
                    textarea.name = fieldName;
                    textarea.rows = 4;
                    textarea.placeholder = placeholder;
                    control = textarea;
                    break;
                }
                case 'number':
                case 'integer': {
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.className = 'form-control';
                    input.id = fieldId;
                    input.name = fieldName;
                    input.placeholder = placeholder;

                    if (type === 'integer') {
                        input.step = '1';
                    }

                    const min = config.number_min || config.integer_min;
                    const max = config.number_max || config.integer_max;

                    if (min !== undefined && min !== null && min !== '') {
                        input.min = min;
                        helpMessages.push('Mínimo: ' + min);
                    }

                    if (max !== undefined && max !== null && max !== '') {
                        input.max = max;
                        helpMessages.push('Máximo: ' + max);
                    }

                    control = input;
                    break;
                }
                case 'date':
                case 'time':
                case 'datetime': {
                    const input = document.createElement('input');
                    input.type = type === 'datetime' ? 'datetime-local' : type;
                    input.className = 'form-control';
                    input.id = fieldId;
                    input.name = fieldName;

                    if (type === 'date' && config.date_hint) {
                        helpMessages.push(config.date_hint);
                    }

                    if (type === 'time' && config.time_hint) {
                        helpMessages.push(config.time_hint);
                    }

                    if (type === 'datetime' && config.datetime_hint) {
                        helpMessages.push(config.datetime_hint);
                    }

                    control = input;
                    break;
                }
                case 'select': {
                    const select = document.createElement('select');
                    select.className = 'form-select';
                    select.id = fieldId;
                    select.name = fieldName;

                    const options = parseOptions(config.select_options);

                    if (!options.length) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Nenhuma opção configurada';
                        select.appendChild(option);
                        select.disabled = true;
                    } else {
                        const placeholderOption = document.createElement('option');
                        placeholderOption.value = '';
                        placeholderOption.textContent = 'Selecione uma opção';
                        select.appendChild(placeholderOption);

                        options.forEach(function (optionValue) {
                            const option = document.createElement('option');
                            option.value = optionValue;
                            option.textContent = optionValue;
                            select.appendChild(option);
                        });
                    }

                    control = select;
                    break;
                }
                case 'multi_select': {
                    const select = document.createElement('select');
                    select.className = 'form-select select2';
                    select.id = fieldId;
                    select.name = fieldName;
                    select.multiple = true;
                    select.dataset.role = 'assessment-select-enhanced';

                    select.setAttribute('data-width', '100%');

                    const placeholderOption = document.createElement('option');
                    placeholderOption.value = '';
                    placeholderOption.disabled = true;
                    placeholderOption.hidden = true;
                    select.appendChild(placeholderOption);

                    const options = parseOptions(config.multi_select_options);

                    if (!options.length) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Nenhuma opção configurada';
                        select.appendChild(option);
                        select.disabled = true;
                    } else {
                        options.forEach(function (optionValue) {
                            const option = document.createElement('option');
                            option.value = optionValue;
                            option.textContent = optionValue;
                            select.appendChild(option);
                        });
                    }

                    helpMessages.push('Selecione uma ou mais opções. Digite para filtrar rapidamente.');
                    control = select;
                    break;
                }
                case 'checkbox': {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'form-check mt-2';

                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.className = 'form-check-input';
                    input.id = fieldId;
                    input.name = fieldName;
                    input.value = '1';

                    if (config.checkbox_default === 'checked') {
                        input.checked = true;
                    }

                    const checkboxLabel = document.createElement('label');
                    checkboxLabel.className = 'form-check-label';
                    checkboxLabel.setAttribute('for', fieldId);
                    checkboxLabel.textContent = config.checkbox_label_checked || 'Marcar';

                    wrapper.appendChild(input);
                    wrapper.appendChild(checkboxLabel);

                    if (config.checkbox_label_unchecked) {
                        helpMessages.push(config.checkbox_label_unchecked);
                    }

                    control = wrapper;
                    break;
                }
                case 'checkbox_group': {
                    const options = parseOptions(config.checkbox_group_options);

                    if (!options.length) {
                        const empty = document.createElement('p');
                        empty.className = 'text-muted mb-0';
                        empty.textContent = 'Nenhuma opção configurada.';
                        control = empty;
                        break;
                    }

                    const group = document.createElement('div');
                    group.className = 'd-flex flex-column gap-2 mt-2';
                    group.id = fieldId;

                    options.forEach(function (optionValue, optionIndex) {
                        const optionId = fieldId + '-option-' + optionIndex;
                        const item = document.createElement('div');
                        item.className = 'form-check';

                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.className = 'form-check-input';
                        input.id = optionId;
                        input.name = fieldName;
                        input.value = optionValue;

                        const optionLabel = document.createElement('label');
                        optionLabel.className = 'form-check-label';
                        optionLabel.setAttribute('for', optionId);
                        optionLabel.textContent = optionValue;

                        item.appendChild(input);
                        item.appendChild(optionLabel);
                        group.appendChild(item);
                    });

                    control = group;
                    break;
                }
                case 'radio_group': {
                    const options = parseOptions(config.radio_group_options);

                    if (!options.length) {
                        const empty = document.createElement('p');
                        empty.className = 'text-muted mb-0';
                        empty.textContent = 'Nenhuma opção configurada.';
                        control = empty;
                        break;
                    }

                    const defaultValue = (config.radio_group_default || '').trim();
                    const group = document.createElement('div');
                    group.className = 'd-flex flex-column gap-2 mt-2';
                    group.id = fieldId;

                    options.forEach(function (optionValue, optionIndex) {
                        const optionId = fieldId + '-option-' + optionIndex;
                        const item = document.createElement('div');
                        item.className = 'form-check';

                        const input = document.createElement('input');
                        input.type = 'radio';
                        input.className = 'form-check-input';
                        input.id = optionId;
                        input.name = fieldName;
                        input.value = optionValue;

                        if (defaultValue && optionValue === defaultValue) {
                            input.checked = true;
                        }

                        const optionLabel = document.createElement('label');
                        optionLabel.className = 'form-check-label';
                        optionLabel.setAttribute('for', optionId);
                        optionLabel.textContent = optionValue;

                        item.appendChild(input);
                        item.appendChild(optionLabel);
                        group.appendChild(item);
                    });

                    control = group;
                    break;
                }
                case 'file': {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.className = 'form-control';
                    input.id = fieldId;
                    input.name = fieldName;

                    const fileTypes = parseOptions(config.file_types);
                    if (fileTypes.length) {
                        const accept = fileTypes
                            .map(function (item) {
                                const normalised = item.toLowerCase();
                                if (normalised.startsWith('.')) {
                                    return normalised;
                                }
                                if (/^[a-z0-9]+\/[a-z0-9.+-]+$/.test(normalised)) {
                                    return normalised;
                                }
                                return '.' + normalised.replace(/^\./, '');
                            })
                            .join(',');

                        input.accept = accept;
                        helpMessages.push('Tipos aceitos: ' + fileTypes.join(', '));
                    }

                    if (config.file_max_size) {
                        helpMessages.push('Tamanho máximo: ' + config.file_max_size + ' MB');
                    }

                    control = input;
                    break;
                }
                case 'rich_text': {
                    const textarea = document.createElement('textarea');
                    textarea.className = 'form-control rich-text';
                    textarea.id = fieldId;
                    textarea.name = fieldName;
                    textarea.rows = 6;
                    textarea.placeholder = placeholder || 'Utilize este espaço para registrar observações detalhadas.';

                    const defaultValue = (config.rich_text_default || '').trim();
                    if (defaultValue) {
                        textarea.value = defaultValue;
                    }

                    control = textarea;
                    break;
                }
                default: {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control';
                    input.id = fieldId;
                    input.name = fieldName;
                    input.placeholder = placeholder;
                    control = input;
                    break;
                }
            }

            if (!control) {
                return null;
            }

            if (type === 'checkbox_group' || type === 'radio_group') {
                labelElement.removeAttribute('for');
            }

            container.appendChild(control);

            if (type === 'rich_text') {
                initRichTextEditor(control);
            }

            if (helpMessages.length) {
                const help = document.createElement('small');
                help.className = 'text-muted d-block mt-2';
                help.textContent = helpMessages.join(' • ');
                container.appendChild(help);
            }

            return container;
        }

        function renderAssessmentFields(model, values) {
            debugLog('renderAssessmentFields iniciado', { modelo: model });
            if (!assessmentFieldsContainer) {
                debugLog('renderAssessmentFields abortado: contêiner ausente');
                return;
            }

            destroyRichTextEditors(assessmentFieldsContainer);
            destroyAssessmentSelectEnhancements(assessmentFieldsContainer);
            clearAssessmentFieldRegistries();

            if (!model || !Array.isArray(model.fields) || !model.fields.length) {
                debugLog('renderAssessmentFields sem campos para renderizar', { modeloValido: Boolean(model) });
                assessmentFieldsContainer.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="ri-checkbox-blank-line text-primary fs-1 mb-3 d-block"></i>
                        <p class="fw-semibold text-secondary mb-1">Modelo sem campos configurados.</p>
                        <p class="mb-0">Edite o modelo para adicionar campos personalizados.</p>
                    </div>
                `;
                return;
            }

            const fragment = document.createDocumentFragment();

            model.fields.forEach(function (field, index) {
                debugLog('renderAssessmentFields construindo campo', { indice: index, campo: field });
                const element = buildAssessmentFieldElement(field, index);
                if (element) {
                    fragment.appendChild(element);
                    debugLog('renderAssessmentFields campo criado', { indice: index, id: element.id });
                }
            });

            assessmentFieldsContainer.innerHTML = '';
            assessmentFieldsContainer.appendChild(fragment);
            assessmentFieldsContainer
                .querySelectorAll('textarea.rich-text')
                .forEach(function (textarea) {
                    initRichTextEditor(textarea);
                });
            enhanceAssessmentSelectFields(assessmentFieldsContainer);
            applyAssessmentValues(values);
            debugLog('renderAssessmentFields finalizado', { totalCampos: fragment.childNodes.length });
        }

        function fetchAssessmentModel(modelId) {
            debugLog('fetchAssessmentModel chamado', { modelId: modelId, url: assessmentModelFetchUrl });
            console.log('[vet/prontuarios] fetchAssessmentModel chamado', { modelId });
            if (!modelId) {
                debugLog('fetchAssessmentModel encerrado: modelo não informado');
                return Promise.reject(new Error('Modelo não informado.'));
            }

            if (assessmentModelCache.has(modelId)) {
                console.log('[vet/prontuarios] Modelo encontrado em cache', { modelId });
                debugLog('fetchAssessmentModel retornando cache', { modelId: modelId });
                return Promise.resolve(assessmentModelCache.get(modelId));
            }

            if (!assessmentModelFetchUrl) {
                console.warn('[vet/prontuarios] URL de busca de modelo não configurada');
                debugLog('fetchAssessmentModel encerrado: URL de busca não configurada');
                return Promise.reject(new Error('Endpoint de modelos não configurado.'));
            }

            const url = assessmentModelFetchUrl.replace('__MODEL__', encodeURIComponent(modelId));
            debugLog('fetchAssessmentModel requisitando servidor', { url: url });
            console.log('[vet/prontuarios] Buscando modelo de avaliação', { url });

            return fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            })
                .then(function (response) {
                    if (!response.ok) {
                        debugLog('fetchAssessmentModel resposta inválida', { status: response.status, statusText: response.statusText });
                        throw new Error('Resposta inválida do servidor.');
                    }

                    debugLog('fetchAssessmentModel resposta recebida com sucesso');
                    return response.json();
                })
                .then(function (data) {
                    assessmentModelCache.set(modelId, data);
                    debugLog('fetchAssessmentModel dados armazenados em cache', { modelId: modelId, campos: data ? data.fields : null });
                    return data;
                });
        }

        function loadAssessmentModel(modelId, options) {
            console.log('[vet/prontuarios] loadAssessmentModel acionado', { modelId });
            if (!assessmentFieldsContainer) {
                console.warn('[vet/prontuarios] Contêiner de campos de avaliação não encontrado');
                return;
            }

            const settings = options || {};
            const prefillValues = settings.values || null;
            const prefillMeta = settings.meta || null;
            const normalisedId = normalizeId(modelId);

            if (!normalisedId) {
                console.log('[vet/prontuarios] Nenhum modelo selecionado, resetando campos e resumo');
                currentAssessmentModel = null;
                currentAssessmentMeta = {};
                resetAssessmentSummary();
                resetAssessmentFields();
                return;
            }

            assessmentModelRequestToken += 1;
            const requestToken = assessmentModelRequestToken;

            currentAssessmentModel = null;
            currentAssessmentMeta = {};

            showAssessmentLoading();
            showAssessmentFieldsLoading();

            fetchAssessmentModel(normalisedId)
                .then(function (model) {
                    if (requestToken !== assessmentModelRequestToken) {
                        return;
                    }

                    const normalisedModel = normalizeAssessmentModel(model) || {
                        id: normalisedId,
                        title: '',
                        category: null,
                        category_label: null,
                        notes: null,
                        fields: [],
                        fields_count: 0,
                    };

                    console.log('[vet/prontuarios] Modelo carregado com sucesso', { model: normalisedModel });
                    currentAssessmentModel = normalisedModel;
                    currentAssessmentMeta = buildAssessmentMeta(normalisedModel, prefillMeta);
                    renderAssessmentSummary(normalisedModel);
                    renderAssessmentFields(normalisedModel, prefillValues);
                })
                .catch(function (error) {
                    if (requestToken !== assessmentModelRequestToken) {
                        return;
                    }

                    currentAssessmentModel = null;
                    currentAssessmentMeta = {};
                    console.error('[vet/prontuarios] Falha ao carregar modelo de avaliação', error);
                    showAssessmentError('Não foi possível carregar o modelo selecionado. Tente novamente.');
                    showAssessmentFieldsError('Verifique sua conexão ou atualize a página para tentar novamente.');
                });
        }

        function updateAttachmentEmptyState() {
            if (!attachmentEmptyState) {
                return;
            }

            if (!attachmentsState.items.length) {
                attachmentEmptyState.classList.remove('d-none');
            } else {
                attachmentEmptyState.classList.add('d-none');
            }
        }

        function syncAttachmentInputs(items) {
            if (!attachmentInputsContainer) {
                return;
            }

            const records = Array.isArray(items) ? items : attachmentsState.items;
            attachmentInputsContainer.innerHTML = '';

            records.forEach(function (attachment) {
                if (!attachment) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'anexos[]';
                input.value = JSON.stringify(attachment);
                attachmentInputsContainer.appendChild(input);
            });
        }

        function renderAttachmentCards(items) {
            if (!attachmentList) {
                return;
            }

            const records = Array.isArray(items) ? items : attachmentsState.items;
            attachmentList.innerHTML = '';

            if (!records.length) {
                updateAttachmentEmptyState();
                syncAttachmentInputs(records);
                return;
            }

            records.forEach(function (attachment) {
                if (!attachment) {
                    return;
                }

                const metaSegments = [];
                if (attachment.uploaded_by) {
                    metaSegments.push(`Enviado por ${attachment.uploaded_by}`);
                }
                if (attachment.uploaded_at) {
                    metaSegments.push(attachment.uploaded_by ? attachment.uploaded_at : `Enviado em ${attachment.uploaded_at}`);
                }

                const infoSegments = [];
                if (attachment.size) {
                    infoSegments.push(`Tamanho ${attachment.size}`);
                }

                const col = document.createElement('div');
                col.className = 'col-12 col-lg-6';

                const metaText = metaSegments.join(' • ');
                const infoText = infoSegments.join(' • ');
                const badge = resolveAttachmentBadge(attachment);
                const safeId = escapeHtml(String(attachment.id || ''));
                const safeName = escapeHtml(attachment.name || 'Documento');
                const safeMeta = escapeHtml(metaText);
                const safeInfo = escapeHtml(infoText);
                const safeUrl = attachment.url ? escapeHtml(attachment.url) : '';
                const hasUrl = Boolean(attachment.url);
                const canRemove = Boolean(attachment.path);

                col.innerHTML = `
                    <div class="vet-record-form__attachment-card">
                        <div class="d-flex flex-column flex-grow-1 gap-2">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="flex-grow-1">
                                    <h6 class="fw-semibold mb-1 text-color">${safeName}</h6>
                                    ${safeMeta ? `<div class="text-muted small">${safeMeta}</div>` : ''}
                                    ${safeInfo ? `<div class="text-muted small">${safeInfo}</div>` : ''}
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                        ${
                                            hasUrl
                                                ? `<a href="${safeUrl}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                                        <i class="ri-external-link-line me-1"></i>Visualizar
                                                    </a>`
                                                : ''
                                        }
                                        <button type="button" class="btn btn-sm btn-outline-danger${
                                            canRemove ? '' : ' disabled'
                                        }" data-record-attachment-remove="${safeId}"${canRemove ? '' : ' disabled'}>
                                            <i class="ri-delete-bin-6-line me-1"></i>Remover
                                        </button>
                                    </div>
                                </div>
                                <span class="vet-record-form__badge-soft-info">${escapeHtml(badge)}</span>
                            </div>
                        </div>
                    </div>
                `;

                attachmentList.appendChild(col);
            });

            updateAttachmentEmptyState();
            syncAttachmentInputs(records);
        }

        function findAttachmentIndexById(id) {
            if (!id) {
                return -1;
            }

            const normalisedId = String(id);

            return attachmentsState.items.findIndex(function (attachment) {
                if (!attachment) {
                    return false;
                }

                return String(attachment.id) === normalisedId;
            });
        }

        function findAttachmentById(id) {
            const index = findAttachmentIndexById(id);

            if (index === -1) {
                return null;
            }

            return attachmentsState.items[index] || null;
        }

        function addAttachment(attachment) {
            if (!attachment) {
                return;
            }

            if (attachmentsState.items.length >= attachmentsConfig.maxItems) {
                notifyAttachmentWarning('Você atingiu o limite de documentos permitidos.');
                return;
            }

            const exists = attachmentsState.items.some(function (item) {
                if (!item) {
                    return false;
                }

                if (item.path && attachment.path) {
                    return item.path === attachment.path;
                }

                return String(item.id) === String(attachment.id);
            });

            if (exists) {
                debugLog('addAttachment ignorado: documento já presente na lista', attachment);
                return;
            }

            attachmentsState.items.push(attachment);
            renderAttachmentCards();
        }

        function removeAttachment(attachment, options) {
            if (!attachment) {
                return;
            }

            const index = findAttachmentIndexById(attachment.id);

            if (index === -1) {
                return;
            }

            const removed = attachmentsState.items.splice(index, 1)[0];
            renderAttachmentCards();

            const shouldRemoveRemote = !options || options.skipRemote !== true;

            if (shouldRemoveRemote && removed && removed.path) {
                removeAttachmentRemote(removed.path);
            }
        }

        function removeAttachmentById(id, options) {
            const attachment = findAttachmentById(id);

            if (attachment) {
                removeAttachment(attachment, options);
            }
        }

        function removeAttachmentRemote(path) {
            if (!attachmentsConfig.removeUrl) {
                debugLog('removeAttachmentRemote ignorado: endpoint não configurado');
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

            return fetch(attachmentsConfig.removeUrl, {
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
                    debugLog('removeAttachmentRemote falhou', error);
                    notifyAttachmentError(error && error.message ? error.message : 'Não foi possível remover o documento.');
                });
        }

        function toggleAttachmentUploadState(isUploading) {
            attachmentsState.isUploading = Boolean(isUploading);

            if (attachmentAddButton) {
                attachmentAddButton.disabled = attachmentsState.isUploading;
                attachmentAddButton.classList.toggle('disabled', attachmentsState.isUploading);

                const icon = attachmentAddButton.querySelector('.ri');
                if (icon) {
                    icon.classList.toggle('ri-upload-2-line', !attachmentsState.isUploading);
                    icon.classList.toggle('ri-loader-4-line', attachmentsState.isUploading);
                    icon.classList.toggle('ri-spin', attachmentsState.isUploading);
                }
            }

            if (attachmentInput) {
                attachmentInput.disabled = attachmentsState.isUploading;
            }
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

        function validateAttachmentBeforeUpload(file) {
            if (!file) {
                return { valid: false, message: 'Arquivo inválido.' };
            }

            if (attachmentsState.items.length >= attachmentsConfig.maxItems) {
                return {
                    valid: false,
                    message: 'Você já anexou o limite de documentos permitidos.',
                };
            }

            if (file.size > attachmentsConfig.maxSize) {
                return {
                    valid: false,
                    message: `O arquivo "${file.name}" excede o tamanho máximo permitido de ${formatFileSizeValue(
                        attachmentsConfig.maxSize
                    )}.`,
                };
            }

            return { valid: true };
        }

        function uploadAttachment(file) {
            if (!attachmentsConfig.uploadUrl) {
                notifyAttachmentError('O envio de documentos não está disponível no momento.');
                return Promise.reject(new Error('Upload endpoint missing.'));
            }

            const validation = validateAttachmentBeforeUpload(file);
            if (!validation.valid) {
                notifyAttachmentWarning(validation.message);
                return Promise.reject(new Error(validation.message || 'Arquivo inválido.'));
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

            toggleAttachmentUploadState(true);

            return fetch(attachmentsConfig.uploadUrl, {
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
                    const attachment = normalizeAttachmentItem(data, attachmentsState.items.length);

                    if (!attachment) {
                        throw new Error('Resposta inválida ao anexar documento.');
                    }

                    addAttachment(attachment);
                })
                .catch(function (error) {
                    debugLog('uploadAttachment falhou', error);
                    notifyAttachmentError(error && error.message ? error.message : 'Não foi possível anexar o documento.');
                })
                .finally(function () {
                    toggleAttachmentUploadState(false);
                });
        }

        function handleAttachmentFiles(event) {
            const input = event.target;

            if (!input || !input.files) {
                return;
            }

            const files = Array.prototype.slice.call(input.files);

            if (!files.length) {
                return;
            }

            const availableSlots = attachmentsConfig.maxItems - attachmentsState.items.length;

            if (availableSlots <= 0) {
                notifyAttachmentWarning('Você já anexou o limite de documentos permitidos.');
                input.value = '';
                return;
            }

            const selectedFiles = files.slice(0, availableSlots);

            if (files.length > availableSlots) {
                notifyAttachmentWarning(`Apenas ${availableSlots} arquivo(s) adicionais podem ser anexados.`);
            }

            selectedFiles
                .reduce(function (promise, file) {
                    return promise.then(function () {
                        return uploadAttachment(file);
                    });
                }, Promise.resolve())
                .finally(function () {
                    input.value = '';
                });
        }

        function bindAttachmentEvents() {
            if (attachmentAddButton && attachmentInput) {
                attachmentAddButton.addEventListener('click', function (event) {
                    event.preventDefault();

                    if (attachmentsState.isUploading) {
                        return;
                    }

                    attachmentInput.value = '';
                    attachmentInput.click();
                });
            }

            if (attachmentInput) {
                attachmentInput.addEventListener('change', handleAttachmentFiles);
            }

            if (attachmentList) {
                attachmentList.addEventListener('click', function (event) {
                    const trigger = event.target.closest('[data-record-attachment-remove]');

                    if (!trigger) {
                        return;
                    }

                    event.preventDefault();

                    const attachmentId = trigger.getAttribute('data-record-attachment-remove');
                    const attachment = findAttachmentById(attachmentId);

                    if (!attachment) {
                        return;
                    }

                    const confirmRemoval = function () {
                        removeAttachment(attachment);
                    };

                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Remover documento',
                            text: `Deseja remover o documento "${attachment.name}"?`,
                            showCancelButton: true,
                            confirmButtonText: 'Remover',
                            cancelButtonText: 'Cancelar',
                            focusCancel: true,
                        }).then(function (result) {
                            if (result && result.isConfirmed) {
                                confirmRemoval();
                            }
                        });
                    } else {
                        const confirmed = window.confirm(`Remover o documento "${attachment.name}"?`);
                        if (confirmed) {
                            confirmRemoval();
                        }
                    }
                });
            }
        }

        function createChecklistItemElement(itemId, labelText) {
            const normalizedId = normalizeId(itemId);
            const trimmedLabel = typeof labelText === 'string' ? labelText.trim() : '';

            if (normalizedId === '' || trimmedLabel === '') {
                return null;
            }

            const wrapper = document.createElement('label');
            wrapper.className = 'vet-record-form__checklist-item';

            const checkbox = document.createElement('input');
            checkbox.className = 'form-check-input mt-2';
            checkbox.type = 'checkbox';
            checkbox.value = normalizedId;

            if (completedChecklistItems.has(normalizedId)) {
                checkbox.checked = true;
                wrapper.classList.add('is-completed');
            }

            checkbox.addEventListener('change', function () {
                if (checkbox.checked) {
                    wrapper.classList.add('is-completed');
                    completedChecklistItems.add(normalizedId);
                } else {
                    wrapper.classList.remove('is-completed');
                    completedChecklistItems.delete(normalizedId);
                }
            });

            const content = document.createElement('div');
            content.className = 'd-flex flex-column gap-1';

            const title = document.createElement('div');
            title.className = 'fw-semibold text-secondary';
            title.textContent = trimmedLabel;

            const helper = document.createElement('small');
            helper.className = 'text-muted';
            helper.textContent = 'Marque quando concluir a etapa.';

            content.appendChild(title);
            content.appendChild(helper);

            wrapper.appendChild(checkbox);
            wrapper.appendChild(content);

            return wrapper;
        }

        function appendChecklistEmptyState(message) {
            const col = document.createElement('div');
            col.className = 'col-12';

            const empty = document.createElement('div');
            empty.className = 'text-muted text-center py-3';
            empty.textContent = message;

            col.appendChild(empty);
            checklistContainer.appendChild(col);
        }

        function renderChecklistItems(itemsByCategory) {
            if (!checklistContainer) {
                return;
            }

            checklistContainer.innerHTML = '';

            if (!itemsByCategory) {
                appendChecklistEmptyState('Nenhum checklist clínico disponível.');
                return;
            }

            if (Array.isArray(itemsByCategory)) {
                if (itemsByCategory.length === 0) {
                    appendChecklistEmptyState('Nenhum checklist clínico cadastrado.');
                    return;
                }

                itemsByCategory.forEach(function (checklist, checklistIndex) {
                    const col = document.createElement('div');
                    col.className = 'col-12 col-lg-6 col-xxl-4';

                    const header = document.createElement('div');
                    header.className = 'mb-3';

                    const titleElement = document.createElement('h6');
                    titleElement.className = 'fw-semibold text-secondary mb-1';

                    let checklistTitle = '';
                    if (checklist && typeof checklist.title === 'string') {
                        checklistTitle = checklist.title.trim();
                    }

                    titleElement.textContent = checklistTitle !== '' ? checklistTitle : 'Checklist clínico';
                    header.appendChild(titleElement);

                    if (checklist && typeof checklist.description === 'string') {
                        const descriptionText = checklist.description.trim();

                        if (descriptionText !== '') {
                            const descriptionElement = document.createElement('p');
                            descriptionElement.className = 'text-muted small mb-0';
                            descriptionElement.textContent = descriptionText;
                            header.appendChild(descriptionElement);
                        }
                    }

                    const list = document.createElement('div');
                    list.className = 'd-flex flex-column gap-2';

                    const items = checklist && Array.isArray(checklist.items) ? checklist.items : [];
                    let hasItems = false;

                    const fallbackChecklistId = checklist && checklist.id !== undefined && checklist.id !== null
                        ? checklist.id
                        : checklistIndex;

                    items.forEach(function (rawItem, itemIndex) {
                        let labelText = '';
                        let itemId = null;

                        if (typeof rawItem === 'string') {
                            labelText = rawItem;
                        } else if (rawItem && typeof rawItem === 'object') {
                            if (typeof rawItem.label === 'string') {
                                labelText = rawItem.label;
                            } else if (typeof rawItem.titulo === 'string') {
                                labelText = rawItem.titulo;
                            }

                            if (rawItem.id !== undefined && rawItem.id !== null) {
                                itemId = rawItem.id;
                            }
                        }

                        const trimmedLabel = typeof labelText === 'string' ? labelText.trim() : '';

                        if (trimmedLabel === '') {
                            return;
                        }

                        const fallbackId = 'checklist-' + normalizeId(fallbackChecklistId) + '-item-' + (itemIndex + 1);
                        const element = createChecklistItemElement(itemId || fallbackId, trimmedLabel);

                        if (element) {
                            hasItems = true;
                            list.appendChild(element);
                        }
                    });

                    if (!hasItems) {
                        const emptyMessage = document.createElement('div');
                        emptyMessage.className = 'text-muted small';
                        emptyMessage.textContent = 'Nenhum item cadastrado para este checklist.';
                        list.appendChild(emptyMessage);
                    }

                    col.appendChild(header);
                    col.appendChild(list);
                    checklistContainer.appendChild(col);
                });

                return;
            }

            const categoryKeys = Object.keys(itemsByCategory);

            if (categoryKeys.length === 0) {
                appendChecklistEmptyState('Nenhum checklist clínico disponível.');
                return;
            }

            categoryKeys.forEach(function (category) {
                const items = Array.isArray(itemsByCategory[category]) ? itemsByCategory[category] : [];
                const col = document.createElement('div');
                col.className = 'col-12 col-lg-4';

                const titleElement = document.createElement('h6');
                titleElement.className = 'fw-semibold text-secondary mb-3';
                titleElement.textContent = formatChecklistTitle(category);
                col.appendChild(titleElement);

                const list = document.createElement('div');
                list.className = 'd-flex flex-column gap-2';

                let hasItems = false;

                items.forEach(function (item, itemIndex) {
                    if (!item || typeof item !== 'object') {
                        return;
                    }

                    const labelText = typeof item.label === 'string' ? item.label.trim() : '';

                    if (labelText === '') {
                        return;
                    }

                    const fallbackId = category + '-item-' + (itemIndex + 1);
                    const element = createChecklistItemElement(item.id || fallbackId, labelText);

                    if (element) {
                        hasItems = true;
                        list.appendChild(element);
                    }
                });

                if (!hasItems) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.className = 'text-muted small';
                    emptyMessage.textContent = 'Nenhum item cadastrado.';
                    list.appendChild(emptyMessage);
                }

                col.appendChild(list);
                checklistContainer.appendChild(col);
            });
        }

        function renderCommunicationCards(templatesData, patient) {
            if (!communicationContainer) {
                return;
            }

            communicationContainer.innerHTML = '';

            if (!Array.isArray(templatesData) || !templatesData.length) {
                return;
            }

            templatesData.forEach(function (template, index) {
                const slotLabel = findSlotLabelByValue(slotSelect.value);
                const formattedMessage = formatCommunicationMessage(template, patient, slotLabel || (patient ? patient.next_follow_up : ''));
                const formattedMessageHtml = formattedMessage.replace(/\n/g, '<br>');

                const col = document.createElement('div');
                col.className = 'col-12';

                col.innerHTML = `
                    <div class="vet-record-form__communication-card h-100 ${index === activeCommunicationIndex ? 'border border-primary' : ''}">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <span class="badge bg-soft-primary text-primary mb-2">${template.channel}</span>
                                <h6 class="fw-semibold mb-1">${template.subject}</h6>
                                <p class="text-muted mb-0" data-communication-message="${index}">${formattedMessageHtml}</p>
                            </div>
                            <button class="btn btn-sm ${index === activeCommunicationIndex ? 'btn-primary' : 'btn-outline-primary'}" data-select-communication="${index}">
                                <i class="ri-mail-send-line me-1"></i>Usar modelo
                            </button>
                        </div>
                    </div>
                `;

                communicationContainer.appendChild(col);
            });
        }

        function renderReminders(items) {
            if (!reminderContainer) {
                return;
            }

            reminderContainer.innerHTML = '';

            if (!items || !items.length) {
                const empty = document.createElement('div');
                empty.className = 'text-muted';
                empty.textContent = 'Nenhum lembrete configurado.';
                reminderContainer.appendChild(empty);
                return;
            }

            items.forEach(function (reminder) {
                const col = document.createElement('div');
                col.className = 'col-12';
                col.innerHTML = `
                    <div class="border rounded-4 p-3 d-flex align-items-start gap-3 bg-white">
                        <i class="ri-time-line text-primary fs-4"></i>
                        <div>
                            <div class="fw-semibold text-secondary">${reminder.message}</div>
                            <div class="text-muted small">${reminder.time}</div>
                        </div>
                        <button class="btn btn-sm btn-link ms-auto text-decoration-none text-muted">Editar</button>
                    </div>
                `;
                reminderContainer.appendChild(col);
            });
        }

        function renderQuickNotesCards(notes) {
            if (!quickNotesContainer) {
                return;
            }

            quickNotesContainer.innerHTML = '';

            const createCard = function (note, target) {
                debugLog('renderQuickNotesCards#createCard', { nota: note, alvo: target });
                const col = document.createElement('div');
                col.className = 'col-12 col-sm-6';
                col.innerHTML = `
                    <div class="vet-record-form__note-card h-100" data-note-target="${target}" data-note-value="${note.label}: ${note.value}">
                        <div class="fw-semibold text-secondary mb-1">${note.label}</div>
                        <div class="text-primary">${note.value}</div>
                        <small class="text-muted d-block mt-2">Clique para adicionar na evolução.</small>
                    </div>
                `;
                quickNotesContainer.appendChild(col);
            };

            (notes.vital_signs || []).forEach(function (note) {
                createCard(note, 'recordPhysicalExam');
            });

            (notes.monitoring || []).forEach(function (note) {
                createCard(note, 'recordPlan');
            });
        }

        function renderTimeline(items) {
            timelineContainer.innerHTML = '';

            if (!items || !items.length) {
                const empty = document.createElement('div');
                empty.className = 'text-muted';
                empty.textContent = 'A linha do tempo será gerada conforme as atualizações forem registradas.';
                timelineContainer.appendChild(empty);
                return;
            }

            items.forEach(function (entry) {
                const item = document.createElement('div');
                item.className = 'vet-record-form__timeline-item mb-4';
                item.innerHTML = `
                    <div class="vet-record-form__timeline-time">${entry.time}</div>
                    <div class="fw-semibold text-secondary">${entry.title}</div>
                    <p class="text-muted mb-0">${entry.description}</p>
                `;
                timelineContainer.appendChild(item);
            });
        }

        function updatePatientInformation(patient) {
            console.log('[vet/prontuarios] Atualizando painel do paciente', patient);

            const fallbackPhoto = defaultPatientPhoto || 'https://ui-avatars.com/api/?background=cfd1ff&color=383c9e&name=Pet';
            const defaultMetaMessage = 'Informações serão exibidas após a seleção.';
            const defaultTutorValue = '—';

            const setMetricValue = function (element, value) {
                if (element) {
                    const hasValue = value !== undefined && value !== null && value !== '';
                    element.textContent = hasValue ? value : '—';
                }
            };

            const formatContactLabel = function (contact) {
                if (!contact) {
                    return '';
                }

                if (typeof contact === 'string') {
                    return contact;
                }

                const type = contact.type || '';
                const value = contact.value || '';

                if (type && value) {
                    return type + ': ' + value;
                }

                return value || type;
            };

            if (!patient) {
                if (patientPhoto) {
                    patientPhoto.src = fallbackPhoto;
                    patientPhoto.alt = 'Foto do paciente';
                }

                if (patientName) {
                    patientName.textContent = 'Selecione um paciente';
                }

                if (patientMeta) {
                    patientMeta.textContent = defaultMetaMessage;
                }

                renderTags(patientTags, []);
                renderAlerts(patientAlerts, []);
                renderList(patientConditions, [], 'Nenhuma condição informada');
                renderMedications(patientMedications, []);
                renderContacts(patientContacts, []);
                renderPatientDetails(patientDetails, null);
                renderPatientSummary(patientSummary, null);

                setMetricValue(patientSummaryWeight, '—');
                setMetricValue(patientSummarySex, '—');
                setMetricValue(patientSummaryBirthDate, '—');
                setMetricValue(patientSummaryLastVisit, '—');
                setMetricValue(patientSummarySize, '—');
                setMetricValue(patientSummaryOrigin, '—');
                setMetricValue(patientSummaryMicrochip, '—');
                setMetricValue(patientSummaryPedigree, '—');

                if (tutorSummaryName) tutorSummaryName.textContent = defaultTutorValue;
                if (tutorSummaryDocument) tutorSummaryDocument.textContent = defaultTutorValue;
                if (tutorSummaryContacts) tutorSummaryContacts.textContent = defaultTutorValue;
                if (tutorSummaryEmail) tutorSummaryEmail.textContent = defaultTutorValue;
                if (tutorSummaryAddress) tutorSummaryAddress.textContent = defaultTutorValue;

                renderTimeline(evolutionTimeline);

                currentPatientSnapshot = null;
                currentTutorSnapshot = defaultTutorSnapshot ? deepClone(defaultTutorSnapshot) : null;

                return;
            }

            const resolvedPhoto = patient.photo || patient.photo_url || fallbackPhoto;
            if (patientPhoto) {
                patientPhoto.src = resolvedPhoto;
                patientPhoto.alt = patient.name ? 'Foto de ' + patient.name : 'Foto do paciente';
            }

            if (patientName) {
                patientName.textContent = patient.name || '—';
            }

            if (patientMeta) {
                const metaParts = [patient.species, patient.breed, patient.age].filter(Boolean);
                patientMeta.textContent = metaParts.length ? metaParts.join(' • ') : '—';
            }

            renderTags(patientTags, patient.tags || []);
            renderAlerts(patientAlerts, patient.alerts || []);

            const combinedConditions = [];
            (patient.allergies || []).forEach(function (allergy) {
                combinedConditions.push('Alergia: ' + allergy);
            });
            (patient.chronic_conditions || []).forEach(function (condition) {
                combinedConditions.push(condition);
            });

            renderList(patientConditions, combinedConditions, 'Nenhuma condição informada');
            renderMedications(patientMedications, patient.medications || []);

            const contactsList = Array.isArray(patient.tutor_contacts) && patient.tutor_contacts.length
                ? patient.tutor_contacts
                : (patient.contact ? [{ type: 'Contato', value: patient.contact }] : []);

            renderContacts(patientContacts, contactsList);
            renderPatientDetails(patientDetails, patient);
            renderPatientSummary(patientSummary, patient);

            setMetricValue(patientSummaryWeight, patient.weight);
            setMetricValue(patientSummarySex, patient.sex || patient.gender);
            setMetricValue(patientSummaryBirthDate, patient.birth_date);
            setMetricValue(patientSummaryLastVisit, patient.last_visit);
            setMetricValue(patientSummarySize, patient.size);
            setMetricValue(patientSummaryOrigin, patient.origin);
            setMetricValue(patientSummaryMicrochip, patient.microchip);
            setMetricValue(patientSummaryPedigree, patient.pedigree);

            const primaryContact = patient.primary_contact || (contactsList.length ? contactsList[0] : null);
            if (!patient.primary_contact && primaryContact) {
                patient.primary_contact = primaryContact;
            }

            const summaryContact = formatContactLabel(patient.contact || primaryContact) || defaultTutorValue;

            if (tutorSummaryName) {
                tutorSummaryName.textContent = patient.tutor || defaultTutorValue;
            }

            if (tutorSummaryDocument) {
                tutorSummaryDocument.textContent = patient.tutor_document || defaultTutorValue;
            }

            if (tutorSummaryContacts) {
                tutorSummaryContacts.textContent = summaryContact;
            }

            if (tutorSummaryEmail) {
                tutorSummaryEmail.textContent = patient.email || defaultTutorValue;
            }

            if (tutorSummaryAddress) {
                tutorSummaryAddress.textContent = patient.tutor_address || defaultTutorValue;
            }

            const timelineEntries = (patient.recent_notes || []).map(function (note) {
                return {
                    time: note.date,
                    title: note.author,
                    description: note.content,
                };
            });

            if (!timelineEntries.length) {
                const fallbackDescription = patient.summary || 'Registro ainda não preenchido.';
                timelineEntries.push({
                    time: patient.last_visit || 'Última visita',
                    title: 'Histórico clínico',
                    description: fallbackDescription,
                });
            }

            renderTimeline(timelineEntries);

            currentPatientSnapshot = deepClone(patient);

            const tutorSnapshot = {
                name: patient.tutor || (defaultTutorSnapshot ? defaultTutorSnapshot.name : null),
                contacts: deepClone(patient.tutor_contacts || []),
            };

            if (patient.tutor_id !== undefined && patient.tutor_id !== null && patient.tutor_id !== '') {
                tutorSnapshot.id = patient.tutor_id;
            }

            if (
                defaultTutorSnapshot &&
                defaultTutorSnapshot.id &&
                prefill &&
                prefill.patient &&
                prefill.patient.id &&
                patient.id &&
                isSameId(prefill.patient.id, patient.id)
            ) {
                tutorSnapshot.id = defaultTutorSnapshot.id;
            }

            currentTutorSnapshot = tutorSnapshot;
        }

        function updateVeterinarianInformation(veterinarianId) {
            const veterinarian = findVeterinarianById(veterinarianId);

            if (!veterinarian) {
                veterinarianInfo.innerHTML = `
                    <i class="ri-user-heart-line fs-4 text-primary"></i>
                    <div>
                        <h6 class="fw-semibold mb-1 text-primary">Nenhum profissional selecionado</h6>
                        <p class="mb-0 text-muted">Escolha o veterinário responsável para sugerirmos protocolos e lembretes associados.</p>
                    </div>
                `;
                return;
            }

            veterinarianInfo.innerHTML = `
                <i class="ri-user-smile-line fs-4 text-primary"></i>
                <div>
                    <h6 class="fw-semibold mb-1 text-primary">${veterinarian.name}</h6>
                    <p class="mb-0 text-muted">Especialidade: ${veterinarian.specialty}</p>
                </div>
            `;
        }

        function applyPrefill(prefillData) {
            if (!prefillData || typeof prefillData !== 'object') {
                return false;
            }

            let applied = false;

            if (prefillData.id !== undefined && prefillData.id !== null) {
                currentRecordId = normalizeId(prefillData.id);
            }

            if (prefillData.status) {
                currentStatus = prefillData.status;
            }

            if (prefillData.attendance && prefillData.attendance.id) {
                currentAttendanceId = normalizeId(prefillData.attendance.id);
            }

            if (prefillData.triage) {
                currentTriageSnapshot = deepClone(prefillData.triage);
                currentVitalSigns = Array.isArray(prefillData.triage.vital_signs)
                    ? deepClone(prefillData.triage.vital_signs)
                    : [];
            }

            if (prefillData.patient_snapshot) {
                currentPatientSnapshot = deepClone(prefillData.patient_snapshot);
            }

            if (prefillData.tutor) {
                defaultTutorSnapshot = deepClone(prefillData.tutor);
                currentTutorSnapshot = deepClone(prefillData.tutor);
            }

            if (prefillData.patient && prefillData.patient.id && patientSelect) {
                const patientId = normalizeId(prefillData.patient.id);
                const patientLabel = prefillData.patient.label || prefillData.patient.name || patientId;

                ensureSelectOption(patientSelect, patientId, patientLabel);

                if (window.jQuery && typeof window.jQuery === 'function') {
                    window.jQuery(patientSelect).val(patientId).trigger('change');
                } else {
                    patientSelect.value = patientId;
                    patientSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }

                applied = true;
            }

            if (prefillData.veterinarian && prefillData.veterinarian.id && veterinarianSelect) {
                const veterinarianId = normalizeId(prefillData.veterinarian.id);
                const veterinarianLabel = prefillData.veterinarian.label || prefillData.veterinarian.name || veterinarianId;

                ensureSelectOption(veterinarianSelect, veterinarianId, veterinarianLabel);

                if (window.jQuery && typeof window.jQuery === 'function') {
                    window.jQuery(veterinarianSelect).val(veterinarianId).trigger('change');
                } else {
                    veterinarianSelect.value = veterinarianId;
                    veterinarianSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }

                applied = true;
            }

            if (prefillData.attendance) {
                if (prefillData.attendance.type && typeSelect) {
                    const typeValue = normalizeId(prefillData.attendance.type);
                    const typeLabel = (prefillData.attendance.type_option && prefillData.attendance.type_option.label)
                        || prefillData.attendance.type_label
                        || typeValue;

                    const hasType = Array.from(typeSelect.options || []).some(function (option) {
                        return isSameId(option.value, typeValue);
                    });

                    if (!hasType) {
                        const option = document.createElement('option');
                        option.value = typeValue;
                        option.textContent = typeLabel || typeValue;
                        typeSelect.appendChild(option);
                    }

                    typeSelect.value = typeValue;
                    applied = true;
                }

                if (prefillData.attendance.slot && slotSelect) {
                    const slotValue = normalizeId(prefillData.attendance.slot);
                    const slotLabel = prefillData.attendance.slot_label || slotValue;

                    ensureSelectOption(slotSelect, slotValue, slotLabel);

                    slotSelect.value = slotValue;
                    slotSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    applied = true;
                }

                if (prefillData.attendance.summary && highlightsField && !highlightsField.value) {
                    setRichTextValue(highlightsField, prefillData.attendance.summary);
                    highlightsTemplateState.appliedType = null;
                    highlightsTemplateState.isDirty = Boolean(stripHtml(prefillData.attendance.summary || ''));
                    highlightsTemplateState.lastTemplateHtml = null;
                }
            }

            if (
                prefillData.billing &&
                Array.isArray(prefillData.billing.services) &&
                prefillData.billing.services.length
            ) {
                loadPrefilledServices(prefillData.billing.services);
                applied = true;
            } else if (Array.isArray(prefillData.services) && prefillData.services.length) {
                loadPrefilledServices(prefillData.services);
                applied = true;
            }

            if (Array.isArray(prefillData.anexos)) {
                attachmentsState.items = prefillData.anexos
                    .map(function (item, index) {
                        return normalizeAttachmentItem(item, index);
                    })
                    .filter(function (item) {
                        return Boolean(item);
                    });

                renderAttachmentCards();
            }

            if (prefillData.patient_snapshot) {
                updatePatientInformation(prefillData.patient_snapshot);
            }

            return applied;
        }

        function setActiveCommunication(index) {
            activeCommunicationIndex = index;
            const patient = findPatientById(selectedPatientId);
            renderCommunicationCards(communications, patient);
        }

        function handleCommunicationSelection(event) {
            const button = event.target.closest('[data-select-communication]');
            if (!button) {
                return;
            }

            const index = Number(button.getAttribute('data-select-communication'));
            setActiveCommunication(index);
        }

        function handleQuickNoteInsertion(event) {
            const card = event.target.closest('[data-note-target]');
            if (!card) {
                return;
            }

            const targetId = card.getAttribute('data-note-target');
            const value = card.getAttribute('data-note-value');
            const field = document.getElementById(targetId);

            if (!field) {
                return;
            }

            const prefix = field.value ? '\n' : '';
            field.value = field.value + prefix + value;
            field.dispatchEvent(new Event('input'));
        }

        function handleCopyCommunication() {
            if (!communicationCopyButton || !Array.isArray(communications) || !communications.length) {
                return;
            }

            const patient = findPatientById(selectedPatientId);
            const template = communications[activeCommunicationIndex];
            const slotLabel = findSlotLabelByValue(slotSelect.value) || (patient ? patient.next_follow_up : '');
            const message = formatCommunicationMessage(template, patient, slotLabel);

            copyToClipboard(message)
                .then(function () {
                    debugLog('handleCopyCommunication: mensagem copiada com sucesso', { mensagem: message });
                    communicationCopyButton.innerHTML = '<i class="ri-check-line me-1"></i>Copiado!';
                    communicationCopyButton.classList.remove('btn-outline-secondary');
                    communicationCopyButton.classList.add('btn-success');

                    setTimeout(function () {
                        communicationCopyButton.innerHTML = '<i class="ri-file-copy-line me-1"></i>Copiar mensagem';
                        communicationCopyButton.classList.add('btn-outline-secondary');
                        communicationCopyButton.classList.remove('btn-success');
                    }, 2000);
                })
                .catch(function () {
                    debugLog('handleCopyCommunication: falha ao copiar mensagem', { mensagem: message });
                    communicationCopyButton.innerHTML = '<i class="ri-error-warning-line me-1"></i>Erro ao copiar';
                    communicationCopyButton.classList.remove('btn-outline-secondary');
                    communicationCopyButton.classList.add('btn-danger');

                    setTimeout(function () {
                        communicationCopyButton.innerHTML = '<i class="ri-file-copy-line me-1"></i>Copiar mensagem';
                        communicationCopyButton.classList.add('btn-outline-secondary');
                        communicationCopyButton.classList.remove('btn-danger');
                    }, 2000);
                });
        }

        function initializeStaticBlocks() {
            debugLog('initializeStaticBlocks disparado', {
                notasVitais: quickNotes ? quickNotes.vital_signs : null,
                monitoramento: quickNotes ? quickNotes.monitoring : null,
                anexos: attachmentsState.items,
                checklists: checklists,
                comunicacoes: communications,
                lembretes: reminders,
                notasRapidas: quickNotes,
                evolucao: evolutionTimeline,
            });
            renderCards(vitalNotesContainer, quickNotes.vital_signs || []);
            renderCards(monitoringNotesContainer, quickNotes.monitoring || []);
            renderAttachmentCards(attachmentsState.items);
            renderChecklistItems(checklists);
            if (communicationContainer) {
                renderCommunicationCards(communications, null);
            }
            if (reminderContainer) {
                renderReminders(reminders);
            }
            renderQuickNotesCards(quickNotes);
            renderTimeline(evolutionTimeline);
            debugLog('initializeStaticBlocks concluído');
        }

        function applyAssessmentValues(values) {
            if (!assessmentFieldsContainer || !values || typeof values !== 'object') {
                return;
            }

            const normalizedValues = {};

            Object.keys(values).forEach(function (key) {
                if (key === '__meta') {
                    return;
                }

                normalizedValues[key] = values[key];
            });

            assessmentFieldsContainer
                .querySelectorAll('[name^="assessment_model_fields"]')
                .forEach(function (element) {
                    if (!element || !element.name) {
                        return;
                    }

                    const match = element.name.match(/^assessment_model_fields\[(.+?)\](\[\])?$/);

                    if (!match) {
                        return;
                    }

                    const key = match[1];
                    const rawValue = Object.prototype.hasOwnProperty.call(normalizedValues, key)
                        ? normalizedValues[key]
                        : undefined;

                    if (element.type === 'checkbox') {
                        const checkboxValues = Array.isArray(rawValue)
                            ? rawValue
                            : rawValue === undefined || rawValue === null
                                ? []
                                : [rawValue];
                        const normalisedCheckboxValues = checkboxValues
                            .map(function (item) {
                                return item !== undefined && item !== null ? String(item) : '';
                            })
                            .filter(Boolean);
                        const checkboxValue = element.value !== undefined ? String(element.value) : '1';
                        element.checked = normalisedCheckboxValues.includes(checkboxValue);
                        return;
                    }

                    if (element.type === 'radio') {
                        if (rawValue === undefined || rawValue === null) {
                            element.checked = false;
                            return;
                        }

                        element.checked = String(element.value) === String(rawValue);
                        return;
                    }

                    if (element.tagName === 'SELECT' && element.multiple) {
                        const selectedValues = Array.isArray(rawValue)
                            ? rawValue
                            : rawValue === undefined || rawValue === null
                                ? []
                                : [rawValue];
                        const normalizedSelected = selectedValues
                            .map(function (item) {
                                return item !== undefined && item !== null ? String(item) : '';
                            })
                            .filter(Boolean);

                        Array.from(element.options || []).forEach(function (option) {
                            option.selected = normalizedSelected.includes(String(option.value));
                        });

                        if (
                            window.jQuery &&
                            window.jQuery.fn &&
                            typeof window.jQuery.fn.select2 === 'function'
                        ) {
                            const $element = window.jQuery(element);
                            if ($element.data('select2')) {
                                $element.trigger('change');
                                return;
                            }
                        }

                        element.dispatchEvent(new Event('change', { bubbles: true }));
                        return;
                    }

                    if (element.tagName === 'SELECT') {
                        element.value = rawValue !== undefined && rawValue !== null ? String(rawValue) : '';

                        if (
                            window.jQuery &&
                            window.jQuery.fn &&
                            typeof window.jQuery.fn.select2 === 'function'
                        ) {
                            const $element = window.jQuery(element);
                            if ($element.data('select2')) {
                                $element.trigger('change');
                                return;
                            }
                        }

                        element.dispatchEvent(new Event('change', { bubbles: true }));
                        return;
                    }

                    if (element.type === 'number' || element.type === 'range') {
                        element.value = rawValue !== undefined && rawValue !== null ? String(rawValue) : '';
                        return;
                    }

                    if (element.type === 'file') {
                        element.value = '';
                        return;
                    }

                    if (element.tagName === 'TEXTAREA' && element.classList.contains('rich-text')) {
                        setRichTextValue(element, rawValue !== undefined && rawValue !== null ? rawValue : '');
                        return;
                    }

                    if (element.tagName === 'TEXTAREA') {
                        element.value = rawValue !== undefined && rawValue !== null ? String(rawValue) : '';
                        return;
                    }

                    element.value = rawValue !== undefined && rawValue !== null ? String(rawValue) : '';
                });
        }

        function collectAssessmentValues() {
            if (!assessmentFieldsContainer) {
                return {};
            }

            const data = {};
            const fields = assessmentFieldsContainer.querySelectorAll('[name^="assessment_model_fields"]');

            fields.forEach(function (element) {
                if (!element || !element.name) {
                    return;
                }

                const match = element.name.match(/^assessment_model_fields\[(.+?)\](\[\])?$/);
                if (!match) {
                    return;
                }

                const key = match[1];
                const isArray = Boolean(match[2]);

                if (element.type === 'checkbox') {
                    if (!Array.isArray(data[key])) {
                        data[key] = [];
                    }

                    if (element.checked) {
                        data[key].push(element.value !== undefined ? element.value : '1');
                    }

                    return;
                }

                if (element.type === 'radio') {
                    if (element.checked) {
                        data[key] = element.value;
                    } else if (!Object.prototype.hasOwnProperty.call(data, key)) {
                        data[key] = null;
                    }

                    return;
                }

                if (element.tagName === 'SELECT' && element.multiple) {
                    const values = Array.from(element.selectedOptions || [])
                        .map(function (option) {
                            return option.value;
                        })
                        .filter(function (value) {
                            return value !== '';
                        });

                    data[key] = values;
                    return;
                }

                if (element.type === 'file') {
                    return;
                }

                let value = element.value;

                if (element.tagName === 'TEXTAREA' && typeof window.tinymce !== 'undefined' && element.id) {
                    const editor = window.tinymce.get(element.id);
                    if (editor) {
                        value = editor.getContent({ format: 'html' });
                    }
                }

                if (element.type === 'number' || element.type === 'range') {
                    value = value !== '' ? Number(value) : null;
                }

                if (isArray) {
                    if (!Array.isArray(data[key])) {
                        data[key] = [];
                    }

                    if (value !== undefined && value !== null && value !== '') {
                        data[key].push(value);
                    }
                } else {
                    data[key] = value !== '' ? value : null;
                }
            });

            Object.keys(data).forEach(function (key) {
                const value = data[key];
                if (
                    value === null ||
                    value === undefined ||
                    (Array.isArray(value) && value.length === 0) ||
                    (typeof value === 'object' && !Array.isArray(value) && value !== null && Object.keys(value).length === 0 && key !== '__meta')
                ) {
                    delete data[key];
                }
            });

            if (currentAssessmentMeta && typeof currentAssessmentMeta === 'object' && Object.keys(currentAssessmentMeta).length) {
                data.__meta = deepClone(currentAssessmentMeta);
            }

            return data;
        }

        function collectServicesData() {
            if (!servicesBody) {
                return [];
            }

            const rows = servicesBody.querySelectorAll('tr[data-service-row]');
            const servicesData = [];

            rows.forEach(function (row) {
                const serviceSelect = row.querySelector('[data-role="service-select"]');
                const serviceNameInput = row.querySelector('[data-role="service-name-input"]');
                const quantityInput = row.querySelector('[data-role="service-quantity"]');
                const unitPriceInput = row.querySelector('[data-role="service-unit-price"]');
                const totalInput = row.querySelector('[data-role="service-total-input"]');
                const descriptionElement = row.querySelector('[data-role="service-description"]');

                const rawServiceId = serviceSelect ? serviceSelect.value : '';
                const serviceId = toNullableInteger(rawServiceId);
                const serviceName = serviceNameInput ? serviceNameInput.value : '';
                const description = descriptionElement ? descriptionElement.textContent || '' : '';

                const quantityValue = quantityInput ? parseFloat(quantityInput.value) : NaN;
                const quantity = Number.isFinite(quantityValue) && quantityValue > 0 ? quantityValue : 1;
                const unitPrice = unitPriceInput ? parseCurrency(unitPriceInput.value) : 0;
                const totalValue = totalInput ? parseFloat(totalInput.value) : NaN;
                const computedUnitPrice = Number.isFinite(unitPrice) ? unitPrice : 0;
                const total = Number.isFinite(totalValue) ? totalValue : quantity * computedUnitPrice;
                const manualPrice = row.dataset.manualPrice === '1';

                if (serviceId === null && serviceName === '' && !Number.isFinite(total) && !Number.isFinite(unitPrice)) {
                    return;
                }

                const formattedUnitPrice = parseFloat(computedUnitPrice.toFixed(2));
                const normalizedTotal = Number.isFinite(total) ? total : quantity * computedUnitPrice;
                const formattedTotal = parseFloat(normalizedTotal.toFixed(2));

                if (serviceId === null && serviceName === '' && formattedTotal === 0) {
                    return;
                }

                servicesData.push({
                    service_id: serviceId,
                    service_name: serviceName || null,
                    description: description ? description.trim() : null,
                    quantity: parseFloat(quantity.toFixed(2)),
                    unit_price: formattedUnitPrice,
                    total: formattedTotal,
                    manual_price: manualPrice,
                });
            });

            return servicesData;
        }

        function collectChecklistSelections() {
            return Array.from(completedChecklistItems)
                .map(function (item) {
                    return normalizeId(item);
                })
                .filter(function (item) {
                    return item !== '';
                });
        }

        function collectCommunications() {
            if (!Array.isArray(communications)) {
                return [];
            }

            const patient = findPatientById(selectedPatientId) || currentPatientSnapshot;
            const slotLabel = findSlotLabelByValue(slotSelect.value) || (patient ? patient.next_follow_up : '');

            return communications.map(function (template, index) {
                const clone = deepClone(template || {});
                clone.active = index === activeCommunicationIndex;
                clone.preview = formatCommunicationMessage(clone, patient, slotLabel);
                return clone;
            });
        }

        function parseSlotSelection(value) {
            if (!value) {
                return null;
            }

            if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(value)) {
                return value;
            }

            if (/^\d{2}:\d{2}$/.test(value)) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                return year + '-' + month + '-' + day + 'T' + value;
            }

            return value;
        }

        function buildMetadata(status, slotValue) {
            const metadata = {
                form_mode: formMode,
                record_id: currentRecordId || null,
                active_communication_index: activeCommunicationIndex,
            };

            if (selectedPatientId) {
                metadata.selected_patient_id = selectedPatientId;
            }

            if (slotValue) {
                metadata.slot_value = slotValue;
                const label = findSlotLabelByValue(slotValue) || slotValue;
                metadata.slot_label = label;
            }

            if (status) {
                metadata.status_after_submit = status;
            }

            if (currentStatus) {
                metadata.previous_status = currentStatus;
            }

            if (currentAssessmentModel && currentAssessmentModel.id !== undefined && currentAssessmentModel.id !== null) {
                const numericModelId = toNullableInteger(currentAssessmentModel.id);

                if (numericModelId !== null) {
                    metadata.assessment_model_id = numericModelId;
                }
            }

            if (currentAssessmentMeta && typeof currentAssessmentMeta === 'object' && Object.keys(currentAssessmentMeta).length) {
                metadata.assessment_model_meta = deepClone(currentAssessmentMeta);
            }

            return metadata;
        }

        function showFeedback(type, message, details) {
            if (!feedbackContainer) {
                window.alert(message);
                return;
            }

            const alertType = type || 'info';
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + alertType + ' alert-dismissible fade show';
            alert.setAttribute('role', 'alert');

            const content = document.createElement('div');
            content.textContent = message || '';
            alert.appendChild(content);

            if (Array.isArray(details) && details.length) {
                const list = document.createElement('ul');
                list.className = 'mt-2 mb-0 ps-3';
                details.forEach(function (detail) {
                    const item = document.createElement('li');
                    item.textContent = detail;
                    list.appendChild(item);
                });
                alert.appendChild(list);
            }

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn-close';
            closeButton.setAttribute('data-bs-dismiss', 'alert');
            closeButton.setAttribute('aria-label', 'Fechar');
            alert.appendChild(closeButton);

            feedbackContainer.innerHTML = '';
            feedbackContainer.appendChild(alert);
        }

        function setButtonLoading(button, isLoading, label) {
            if (!button) {
                return;
            }

            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }

            if (isLoading) {
                const text = label || 'Salvando...';
                button.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                    escapeHtml(text);
                button.disabled = true;
            } else {
                button.innerHTML = button.dataset.originalHtml;
                button.disabled = false;
            }
        }

        function resolveTutorId() {
            if (currentTutorSnapshot && currentTutorSnapshot.id !== undefined && currentTutorSnapshot.id !== null) {
                const tutorFromSnapshot = toNullableInteger(currentTutorSnapshot.id);

                if (tutorFromSnapshot !== null) {
                    return tutorFromSnapshot;
                }
            }

            if (currentPatientSnapshot && currentPatientSnapshot.tutor_id !== undefined) {
                const tutorFromPatient = toNullableInteger(currentPatientSnapshot.tutor_id);

                if (tutorFromPatient !== null) {
                    return tutorFromPatient;
                }
            }

            if (defaultTutorSnapshot && defaultTutorSnapshot.id !== undefined && defaultTutorSnapshot.id !== null) {
                const tutorFromDefault = toNullableInteger(defaultTutorSnapshot.id);

                if (tutorFromDefault !== null) {
                    return tutorFromDefault;
                }
            }

            if (prefill && prefill.tutor && prefill.tutor.id !== undefined && prefill.tutor.id !== null) {
                const tutorFromPrefill = toNullableInteger(prefill.tutor.id);

                if (tutorFromPrefill !== null) {
                    return tutorFromPrefill;
                }
            }

            return null;
        }

        function collectFormData(options) {
            const settings = options || {};
            const status = settings.status || null;

            const patientId = toNullableInteger(
                patientSelect && patientSelect.value ? patientSelect.value : selectedPatientId || ''
            );
            const veterinarianId = toNullableInteger(veterinarianSelect && veterinarianSelect.value ? veterinarianSelect.value : '');
            const typeValue = typeSelect && typeSelect.value ? typeSelect.value : '';
            const slotValue = slotSelect && slotSelect.value ? slotSelect.value : '';
            const summary = highlightsField ? highlightsField.value.trim() : '';
            const attendanceId = toNullableInteger(currentAttendanceId || null);
            const tutorId = resolveTutorId();

            const payload = {
                paciente_id: patientId,
                atendimento_id: attendanceId,
                veterinario_id: veterinarianId,
                tipo: typeValue || null,
                data_registro: parseSlotSelection(slotValue),
                resumo_rapido: summary !== '' ? summary : null,
                checklists: collectChecklistSelections(),
                comunicacoes: collectCommunications(),
                lembretes: Array.isArray(reminders) ? deepClone(reminders) : [],
                anexos: attachmentsState.items.map(function (attachment) {
                    return attachment ? deepClone(attachment) : attachment;
                }),
                metadata: buildMetadata(status, slotValue),
                snapshot_paciente: currentPatientSnapshot ? deepClone(currentPatientSnapshot) : null,
                snapshot_tutor: currentTutorSnapshot ? deepClone(currentTutorSnapshot) : null,
                dados_triagem: currentTriageSnapshot ? deepClone(currentTriageSnapshot) : null,
                sinais_vitais: Array.isArray(currentVitalSigns) ? deepClone(currentVitalSigns) : [],
                avaliacao_personalizada: collectAssessmentValues(),
            };

            if (currentAssessmentModel && currentAssessmentModel.id !== undefined && currentAssessmentModel.id !== null) {
                const assessmentModelId = toNullableInteger(currentAssessmentModel.id);

                if (assessmentModelId !== null) {
                    payload.assessment_model_id = assessmentModelId;
                }
            }

            if (tutorId !== null) {
                payload.tutor_id = tutorId;
            }

            if (status) {
                payload.status = status;
            }

            Object.keys(payload).forEach(function (key) {
                const value = payload[key];
                if (
                    value === null ||
                    value === undefined ||
                    (typeof value === 'string' && value.trim() === '') ||
                    (Array.isArray(value) && value.length === 0) ||
                    (typeof value === 'object' &&
                        !Array.isArray(value) &&
                        value !== null &&
                        Object.keys(value).length === 0 &&
                        key !== 'metadata')
                ) {
                    if (key !== 'metadata') {
                        delete payload[key];
                    }
                }
            });

            return payload;
        }

        let isSubmitting = false;

        function submitRecord(options) {
            if (isSubmitting) {
                return;
            }

            const settings = options || {};
            const status = settings.status || null;
            const triggerButton = settings.triggerButton || submitButton;
            const loadingLabel = settings.loadingLabel || (status === 'finished' ? 'Finalizando...' : 'Salvando...');

            const payload = collectFormData({ status: status });

            if (!payload.paciente_id) {
                showFeedback('danger', 'Selecione o paciente para salvar o prontuário.');
                return;
            }

            const shouldUpdate = Boolean(currentRecordId && updateUrl);
            const requestUrl = shouldUpdate ? updateUrl : storeUrl;

            if (!requestUrl) {
                showFeedback('danger', 'Configuração de envio não disponível. Atualize a página e tente novamente.');
                return;
            }

            isSubmitting = true;
            setButtonLoading(triggerButton, true, loadingLabel);

            const alternateButton =
                triggerButton === submitButton ? draftButton : triggerButton === draftButton ? submitButton : null;
            if (alternateButton) {
                alternateButton.disabled = true;
            }

            sendRequest({
                method: shouldUpdate ? 'put' : 'post',
                url: requestUrl,
                data: payload,
                headers: {
                    Accept: 'application/json',
                },
            })
                .then(function (response) {
                    const data = response && response.data ? response.data : null;

                    if (!shouldUpdate) {
                        const newId = (data && (data.id || (data.record && data.record.id))) || currentRecordId || null;

                        if (newId) {
                            const redirectId = normalizeId(newId);
                            const redirectUrl =
                                editUrlTemplate && editUrlTemplate.indexOf('__RECORD__') !== -1
                                    ? editUrlTemplate.replace('__RECORD__', redirectId)
                                    : editUrlTemplate;

                            if (redirectUrl) {
                                showFeedback('success', 'Prontuário cadastrado com sucesso. Redirecionando...');
                                setTimeout(function () {
                                    window.location.href = redirectUrl;
                                }, 800);
                                return;
                            }
                        }
                    }

                    if (data) {
                        if (data.id !== undefined && data.id !== null) {
                            currentRecordId = normalizeId(data.id);
                        }

                        if (data.status) {
                            currentStatus = data.status;
                        } else if (status) {
                            currentStatus = status;
                        }

                        if (data.attendance && data.attendance.id) {
                            currentAttendanceId = normalizeId(data.attendance.id);
                        }

                        if (data.patient_snapshot) {
                            currentPatientSnapshot = deepClone(data.patient_snapshot);
                        }

                        if (data.tutor) {
                            defaultTutorSnapshot = deepClone(data.tutor);
                            currentTutorSnapshot = deepClone(data.tutor);
                        }

                        if (data.triage) {
                            currentTriageSnapshot = deepClone(data.triage);
                            currentVitalSigns = Array.isArray(data.triage.vital_signs)
                                ? deepClone(data.triage.vital_signs)
                                : [];
                        }
                    } else if (status) {
                        currentStatus = status;
                    }

                    const successMessage = shouldUpdate
                        ? 'Prontuário atualizado com sucesso.'
                        : 'Prontuário cadastrado com sucesso.';
                    showFeedback('success', successMessage);
                })
                .catch(function (error) {
                    const details = [];
                    let message = 'Não foi possível salvar o prontuário. Tente novamente.';

                    if (error && error.response) {
                        if (error.response.status === 422 && error.response.data) {
                            message = error.response.data.message || message;

                            if (error.response.data.errors) {
                                Object.values(error.response.data.errors).forEach(function (fieldErrors) {
                                    if (Array.isArray(fieldErrors)) {
                                        fieldErrors.forEach(function (fieldMessage) {
                                            details.push(fieldMessage);
                                        });
                                    }
                                });
                            }
                        } else if (error.response.data && error.response.data.message) {
                            message = error.response.data.message;
                        }
                    }

                    showFeedback('danger', message, details);
                })
                .finally(function () {
                    isSubmitting = false;
                    setButtonLoading(triggerButton, false);

                    if (alternateButton) {
                        alternateButton.disabled = false;
                    }
                });
        }

        const debugFunctionBindings = {
            parseDatasetJSON: parseDatasetJSON,
            createElementFromHTML: createElementFromHTML,
            formatChecklistTitle: formatChecklistTitle,
            formatCommunicationMessage: formatCommunicationMessage,
            copyToClipboard: copyToClipboard,
            normalizeId: normalizeId,
            isSameId: isSameId,
            slugify: slugify,
            parseOptions: parseOptions,
            stripHtml: stripHtml,
            escapeHtml: escapeHtml,
            formatCurrencyDisplay: formatCurrencyDisplay,
            formatCurrencyInput: formatCurrencyInput,
            parseCurrency: parseCurrency,
            setCurrencyInputValue: setCurrencyInputValue,
            formatPatientOption: formatPatientOption,
            formatPatientSelection: formatPatientSelection,
            formatVeterinarianOption: formatVeterinarianOption,
            formatVeterinarianSelection: formatVeterinarianSelection,
            initializeSelect2: initializeSelect2,
            findPatientById: findPatientById,
            findVeterinarianById: findVeterinarianById,
            findSlotLabelByValue: findSlotLabelByValue,
            populateServiceSelect: populateServiceSelect,
            renderTags: renderTags,
            renderList: renderList,
            renderMedications: renderMedications,
            renderContacts: renderContacts,
            renderPatientDetails: renderPatientDetails,
            renderPatientSummary: renderPatientSummary,
            renderAlerts: renderAlerts,
            renderCards: renderCards,
            updateServiceRowDetails: updateServiceRowDetails,
            updateServiceRowTotal: updateServiceRowTotal,
            updateServicesTotals: updateServicesTotals,
            updateServicesEmptyState: updateServicesEmptyState,
            resetServiceRows: resetServiceRows,
            createServiceRow: createServiceRow,
            loadPrefilledServices: loadPrefilledServices,
            initializeServiceSection: initializeServiceSection,
            clearAssessmentFieldRegistries: clearAssessmentFieldRegistries,
            resetAssessmentSummary: resetAssessmentSummary,
            showAssessmentLoading: showAssessmentLoading,
            renderAssessmentSummary: renderAssessmentSummary,
            showAssessmentError: showAssessmentError,
            resetAssessmentFields: resetAssessmentFields,
            showAssessmentFieldsLoading: showAssessmentFieldsLoading,
            showAssessmentFieldsError: showAssessmentFieldsError,
            generateFieldId: generateFieldId,
            generateFieldName: generateFieldName,
            buildAssessmentFieldElement: buildAssessmentFieldElement,
            renderAssessmentFields: renderAssessmentFields,
            fetchAssessmentModel: fetchAssessmentModel,
            loadAssessmentModel: loadAssessmentModel,
            renderAttachmentCards: renderAttachmentCards,
            renderChecklistItems: renderChecklistItems,
            renderCommunicationCards: renderCommunicationCards,
            renderReminders: renderReminders,
            renderQuickNotesCards: renderQuickNotesCards,
            renderTimeline: renderTimeline,
            updatePatientInformation: updatePatientInformation,
            updateVeterinarianInformation: updateVeterinarianInformation,
            setActiveCommunication: setActiveCommunication,
            handleCommunicationSelection: handleCommunicationSelection,
            handleQuickNoteInsertion: handleQuickNoteInsertion,
            handleCopyCommunication: handleCopyCommunication,
            initializeStaticBlocks: initializeStaticBlocks,
            collectAssessmentValues: collectAssessmentValues,
            collectServicesData: collectServicesData,
            collectChecklistSelections: collectChecklistSelections,
            collectCommunications: collectCommunications,
            parseSlotSelection: parseSlotSelection,
            buildMetadata: buildMetadata,
            showFeedback: showFeedback,
            setButtonLoading: setButtonLoading,
            sendRequest: sendRequest,
            collectFormData: collectFormData,
            submitRecord: submitRecord,
            deepClone: deepClone,
        };

        Object.keys(debugFunctionBindings).forEach(function (functionName) {
            const original = debugFunctionBindings[functionName];
            debugLog('Preparando instrumentação da função', { nome: functionName, disponivel: typeof original === 'function' });
            if (typeof original === 'function') {
                debugFunctionBindings[functionName] = wrapWithDebug(functionName, original);
            }
        });

        ;({
            parseDatasetJSON,
            createElementFromHTML,
            formatChecklistTitle,
            formatCommunicationMessage,
            copyToClipboard,
            normalizeId,
            isSameId,
            slugify,
            parseOptions,
            stripHtml,
            escapeHtml,
            formatCurrencyDisplay,
            formatCurrencyInput,
            parseCurrency,
            setCurrencyInputValue,
            formatPatientOption,
            formatPatientSelection,
            formatVeterinarianOption,
            formatVeterinarianSelection,
            initializeSelect2,
            findPatientById,
            findVeterinarianById,
            findSlotLabelByValue,
            populateServiceSelect,
            renderTags,
            renderList,
            renderMedications,
            renderContacts,
            renderPatientDetails,
            renderPatientSummary,
            renderAlerts,
            renderCards,
            updateServiceRowDetails,
            updateServiceRowTotal,
            updateServicesTotals,
            updateServicesEmptyState,
            resetServiceRows,
            createServiceRow,
            loadPrefilledServices,
            initializeServiceSection,
            clearAssessmentFieldRegistries,
            resetAssessmentSummary,
            showAssessmentLoading,
            renderAssessmentSummary,
            showAssessmentError,
            resetAssessmentFields,
            showAssessmentFieldsLoading,
            showAssessmentFieldsError,
            generateFieldId,
            generateFieldName,
            buildAssessmentFieldElement,
            renderAssessmentFields,
            fetchAssessmentModel,
            loadAssessmentModel,
            renderAttachmentCards,
            renderChecklistItems,
            renderCommunicationCards,
            renderReminders,
            renderQuickNotesCards,
            renderTimeline,
            updatePatientInformation,
            updateVeterinarianInformation,
            setActiveCommunication,
            handleCommunicationSelection,
            handleQuickNoteInsertion,
            handleCopyCommunication,
            initializeStaticBlocks,
            collectAssessmentValues,
            collectServicesData,
            collectChecklistSelections,
            collectCommunications,
            parseSlotSelection,
            buildMetadata,
            showFeedback,
            setButtonLoading,
            sendRequest,
            collectFormData,
            submitRecord,
            deepClone,
        } = debugFunctionBindings);

        debugLog('Funções instrumentadas para debug detalhado');

        bindAttachmentEvents();

        debugLog('Invocando rotinas iniciais da tela de prontuário');
        initializeServiceSection();
        initializeStaticBlocks();
        initializeSelect2();

        debugLog('Registrando listeners principais dos campos do formulário');

        patientSelect.addEventListener('change', function () {
            selectedPatientId = patientSelect.value;
            const patient = findPatientById(selectedPatientId);
            console.log('[vet/prontuarios] Paciente selecionado', {
                id: selectedPatientId,
                registro: patient,
            });
            updatePatientInformation(patient);
            renderCommunicationCards(communications, patient);

            if (patient && patient.tags && patient.tags.length) {
                highlightsField.placeholder = 'Resumo sugerido: ' + patient.tags.join(', ');
            } else {
                highlightsField.placeholder = 'Ex.: Pós-operatório de retirada de nódulo - evolução estável com leve edema.';
            }
        });
        debugLog('Listener de mudança registrado para o seletor de paciente', { elemento: 'recordPatientSelect', existente: Boolean(patientSelect) });

        veterinarianSelect.addEventListener('change', function () {
            updateVeterinarianInformation(veterinarianSelect.value);
        });
        debugLog('Listener de mudança registrado para o seletor de veterinário', { elemento: 'recordVeterinarianSelect', existente: Boolean(veterinarianSelect) });

        if (typeSelect) {
            typeSelect.addEventListener('change', function (event) {
                const selectedType = event && event.target ? event.target.value : typeSelect.value;

                if (!selectedType) {
                    highlightsTemplateState.appliedType = null;
                    highlightsTemplateState.lastTemplateHtml = null;
                    updateHighlightsDirtyState();
                    return;
                }

                applyHighlightsTemplateForType(selectedType, { confirmWhenDirty: true });
            });
            debugLog('Listener de mudança registrado para o seletor de tipo de atendimento', {
                elemento: 'recordTypeSelect',
                existente: true,
            });
        } else {
            debugLog('Elemento recordTypeSelect não encontrado. Nenhum listener registrado para tipo de atendimento.');
        }

        if (templateSelect) {
            debugLog('Listener de mudança registrado para o seletor de modelos de avaliação', { elemento: 'recordTemplateSelect' });
            templateSelect.addEventListener('change', function () {
                console.log('[vet/prontuarios] Evento de alteração no select de modelos capturado', {
                    value: templateSelect.value,
                });
                loadAssessmentModel(templateSelect.value);
            });
        } else {
            debugLog('Elemento recordTemplateSelect não encontrado. Nenhum listener registrado.');
        }

        slotSelect.addEventListener('change', function () {
            const patient = findPatientById(selectedPatientId);
            renderCommunicationCards(communications, patient);
        });
        debugLog('Listener de mudança registrado para o seletor de horários', { elemento: 'recordSlotSelect', existente: Boolean(slotSelect) });

        if (communicationContainer) {
            communicationContainer.addEventListener('click', handleCommunicationSelection);
            debugLog('Listener de clique registrado para comunicação', { elemento: 'recordCommunicationCards', existente: Boolean(communicationContainer) });
        } else {
            debugLog('Elemento recordCommunicationCards ausente. Nenhum listener de comunicação registrado.');
        }

        if (quickNotesContainer) {
            debugLog('Listener de clique registrado para notas rápidas', { elemento: 'recordQuickNotes' });
            quickNotesContainer.addEventListener('click', handleQuickNoteInsertion);
        } else {
            debugLog('Elemento recordQuickNotes ausente. Listener de notas rápidas não configurado.');
        }

        if (communicationCopyButton) {
            communicationCopyButton.addEventListener('click', handleCopyCommunication);
            debugLog('Listener de clique registrado para o botão de copiar comunicação', { elemento: 'recordCommunicationCopy', existente: Boolean(communicationCopyButton) });
        } else {
            debugLog('Elemento recordCommunicationCopy ausente. Nenhum listener de cópia configurado.');
        }

        if (submitButton) {
            debugLog('Listener de clique registrado para o botão de envio do prontuário', { elemento: 'recordSubmitButton' });
            submitButton.addEventListener('click', function () {
                submitRecord({
                    status: 'finished',
                    triggerButton: submitButton,
                    loadingLabel: 'Finalizando...',
                });
            });
        }

        if (draftButton) {
            debugLog('Listener de clique registrado para o botão de rascunho do prontuário', { elemento: 'recordDraftButton' });
            draftButton.addEventListener('click', function () {
                submitRecord({
                    status: 'draft',
                    triggerButton: draftButton,
                    loadingLabel: 'Salvando rascunho...',
                });
            });
        }

        const prefillApplied = applyPrefill(prefill);
        debugLog('Resultado da aplicação de pré-preenchimento', { aplicado: prefillApplied });

        if (highlightsField) {
            updateHighlightsDirtyState();

            if (!highlightsTemplateState.isDirty) {
                maybeApplyHighlightsTemplate({ silent: true });
            }
        }

        if (initialAssessmentModelId) {
            if (templateSelect) {
                const optionLabel = initialAssessmentOptionLabel || initialAssessmentModelId;
                ensureSelectOption(templateSelect, initialAssessmentModelId, optionLabel);
                templateSelect.value = initialAssessmentModelId;
            }

            loadAssessmentModel(initialAssessmentModelId, {
                values: initialAssessmentValues,
                meta: initialAssessmentMeta,
            });
        } else if (
            initialAssessmentMeta &&
            typeof initialAssessmentMeta === 'object' &&
            Object.keys(initialAssessmentMeta).length &&
            (!initialAssessmentModelId || initialAssessmentModelId === '')
        ) {
            currentAssessmentMeta = Object.assign({}, initialAssessmentMeta);
        }

        if (!prefillApplied || !veterinarianSelect || !veterinarianSelect.value) {
            updateVeterinarianInformation('');
        }

        debugLog('Finalizando inicialização do prontuário veterinário');
    });
})();