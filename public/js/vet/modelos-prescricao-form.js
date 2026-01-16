(function ($) {
    'use strict';

    const $form = $('#form-modelos-prescricao');
    const $tableBody = $('.table-model-fields tbody');
    const rowTemplate = document.getElementById('prescription-field-row-template');
    const $previewModal = $('#prescription-preview-modal');
    const $previewContent = $previewModal.find('.prescription-preview-content');
    let rowIndex = 0;
    const oldFieldsRaw = typeof window !== 'undefined' ? window.prescriptionModelOldFields || null : null;
    const existingFieldsRaw = typeof window !== 'undefined' ? window.prescriptionModelExistingFields || null : null;
    const hasOwn = Object.prototype.hasOwnProperty;

    function initCustomCategoryField() {
        if (!$form.length) {
            return;
        }

        const $categorySelect = $form.find('select[name="category"]');
        const $customWrapper = $form.find('.custom-category-wrapper');
        const $customInput = $form.find('input[name="custom_category"]');

        if (!$categorySelect.length || !$customWrapper.length || !$customInput.length) {
            return;
        }

        const toggleCustomCategory = (shouldFocus = false) => {
            const selectedValue = ($categorySelect.val() || '').toString();
            const isCustom = selectedValue === 'personalizado';

            $customWrapper.toggleClass('d-none', !isCustom);
            $customInput.prop('disabled', !isCustom);

            if (isCustom && shouldFocus) {
                setTimeout(() => {
                    $customInput.trigger('focus');
                }, 15);
            }
        };

        $categorySelect.on('change', () => {
            const shouldFocus = ($categorySelect.val() || '').toString() === 'personalizado';
            toggleCustomCategory(shouldFocus);
        });

        toggleCustomCategory();
    }

    initCustomCategoryField();

    function isSelect2Available() {
        return !!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function');
    }

    function initFieldTypeSelect2($row) {
        if (!isSelect2Available()) {
            return;
        }

        const $select = $row.find('.field-type');
        if (!$select.length) {
            return;
        }

        if ($select.data('select2')) {
            $select.select2('destroy');
        }

        const $dropdownParent = $select.closest('.table-responsive');
        const config = {
            width: '100%'
        };

        if ($dropdownParent.length) {
            config.dropdownParent = $dropdownParent;
        }

        $select.select2(config);
    }

    function destroyFieldTypeSelect2($row) {
        if (!isSelect2Available()) {
            return;
        }

        const $select = $row.find('.field-type');
        if (!$select.length) {
            return;
        }

        if ($select.data('select2')) {
            $select.select2('destroy');
        }

        $select
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id')
            .removeAttr('aria-hidden')
            .removeAttr('tabindex');

        const $container = $select.next('.select2-container');
        if ($container.length) {
            $container.remove();
        }
    }

    const templateLibrary = {
        pos_alta: {
            name: 'Prescrição de alta cirúrgica',
            title: 'Modelo de Prescrição Pós-operatória',
            category: 'pos-operatorio',
            previewCategoryLabel: 'Alta clínica',
            notes: 'Plano focado em alta segura após procedimentos cirúrgicos, com orientações claras ao tutor e monitoramento inicial.',
            fields: [
                {
                    label: 'Data da alta',
                    type: 'date',
                    config: {
                        date_hint: 'Informe quando o paciente foi liberado.'
                    }
                },
                {
                    label: 'Responsável pela prescrição',
                    type: 'text',
                    config: {
                        placeholder: 'Nome do médico veterinário.'
                    }
                },
                {
                    label: 'Peso atualizado (kg)',
                    type: 'number',
                    config: {
                        number_min: '0.5',
                        number_max: '120',
                        placeholder: 'Ex.: 6,4'
                    }
                },
                {
                    label: 'Procedimento realizado',
                    type: 'textarea',
                    config: {
                        textarea_placeholder: 'Descreva o procedimento realizado ou motivo da internação.'
                    }
                },
                {
                    label: 'Medicações principais',
                    type: 'rich_text',
                    config: {
                        rich_text_default: '<ul><li>Analgésico: Dipirona sódica 25 mg/kg VO a cada 8h por 5 dias.</li><li>Anti-inflamatório: Meloxicam 0,1 mg/kg VO a cada 24h por 3 dias.</li><li>Antibiótico: Amoxicilina + Clavulanato 12,5 mg/kg VO a cada 12h por 7 dias.</li></ul>'
                    }
                },
                {
                    label: 'Orientações ao tutor',
                    type: 'checkbox_group',
                    config: {
                        checkbox_group_options: [
                            'Manter curativo limpo e seco',
                            'Utilizar colar elizabetano',
                            'Restringir exercícios intensos',
                            'Administrar medicações nos horários indicados'
                        ]
                    }
                },
                {
                    label: 'Próxima reavaliação',
                    type: 'datetime',
                    config: {
                        datetime_hint: 'Sugira data e horário para revisão pós-operatória.'
                    }
                },
                {
                    label: 'Contato de suporte',
                    type: 'phone',
                    config: {
                        phone_placeholder: '(00) 00000-0000'
                    }
                }
            ]
        },
        controle_dor: {
            name: 'Plano multimodal de analgesia',
            title: 'Modelo de Controle de Dor Crônica',
            category: 'controle-dor',
            previewCategoryLabel: 'Controle da dor',
            notes: 'Estrutura para acompanhamento contínuo de pacientes com dor crônica ou aguda prolongada, combinando diferentes fármacos e escalas de avaliação.',
            fields: [
                {
                    label: 'Data de início do protocolo',
                    type: 'date',
                    config: {
                        date_hint: 'Registre quando o plano analgésico foi iniciado.'
                    }
                },
                {
                    label: 'Veterinário responsável',
                    type: 'text',
                    config: {
                        placeholder: 'Profissional responsável pelo acompanhamento.'
                    }
                },
                {
                    label: 'Escala de dor inicial',
                    type: 'select',
                    config: {
                        select_options: [
                            'Glasgow modificada',
                            'Colorado State University',
                            'Escala de dor felina',
                            'Outra'
                        ]
                    }
                },
                {
                    label: 'Fármaco base',
                    type: 'text',
                    config: {
                        placeholder: 'Ex.: Tramadol 3 mg/kg VO'
                    }
                },
                {
                    label: 'Dose recomendada (mg/kg)',
                    type: 'number',
                    config: {
                        number_min: '0',
                        number_max: '50',
                        placeholder: 'Ex.: 2,5'
                    }
                },
                {
                    label: 'Intervalo de administração',
                    type: 'select',
                    config: {
                        select_options: [
                            'A cada 6 horas',
                            'A cada 8 horas',
                            'A cada 12 horas',
                            'A cada 24 horas'
                        ]
                    }
                },
                {
                    label: 'Plano multimodal',
                    type: 'rich_text',
                    config: {
                        rich_text_default: '<p><strong>Objetivo:</strong> Reduzir a percepção de dor em até 48h.</p><ul><li>Analgesia: Metadona 0,3 mg/kg IM q6h.</li><li>Apoio: Dipirona 25 mg/kg VO q8h.</li><li>Adjuvante: Gabapentina 10 mg/kg VO q12h.</li></ul>'
                    }
                },
                {
                    label: 'Alertas e monitoramento',
                    type: 'textarea',
                    config: {
                        textarea_placeholder: 'Descreva sinais de alerta e parâmetros que devem ser monitorados pelo tutor.'
                    }
                },
                {
                    label: 'Retorno programado',
                    type: 'date',
                    config: {
                        date_hint: 'Data prevista para reavaliação da dor.'
                    }
                }
            ]
        },
        antibiotico: {
            name: 'Plano antimicrobiano orientado',
            title: 'Modelo de Antibioticoterapia',
            category: 'antibioticoterapia',
            previewCategoryLabel: 'Tratamento antibiótico',
            notes: 'Modelo para prescrição segura de antibióticos com registro de cultura, sensibilidade e checkpoints de revisão.',
            fields: [
                {
                    label: 'Data de início do tratamento',
                    type: 'date',
                    config: {
                        date_hint: 'Informe quando o antibiótico deve começar a ser administrado.'
                    }
                },
                {
                    label: 'Diagnóstico/Agente suspeito',
                    type: 'textarea',
                    config: {
                        textarea_placeholder: 'Registre o diagnóstico clínico ou agente infeccioso identificado.'
                    }
                },
                {
                    label: 'Resultado de cultura e sensibilidade',
                    type: 'file',
                    config: {
                        file_types: 'pdf, jpg, png',
                        file_max_size: '5'
                    }
                },
                {
                    label: 'Antibiótico prescrito',
                    type: 'text',
                    config: {
                        placeholder: 'Ex.: Enrofloxacino 10 mg/kg'
                    }
                },
                {
                    label: 'Frequência de administração',
                    type: 'select',
                    config: {
                        select_options: [
                            'A cada 12 horas',
                            'A cada 24 horas',
                            'A cada 48 horas'
                        ]
                    }
                },
                {
                    label: 'Duração prevista (dias)',
                    type: 'integer',
                    config: {
                        integer_min: '1',
                        integer_max: '60',
                        placeholder: 'Ex.: 21'
                    }
                },
                {
                    label: 'Checkpoints de reavaliação',
                    type: 'multi_select',
                    config: {
                        multi_select_options: [
                            'Reavaliar em 3 dias',
                            'Reavaliar em 7 dias',
                            'Reavaliar ao término do tratamento',
                            'Solicitar novos exames'
                        ]
                    }
                },
                {
                    label: 'Observações de segurança',
                    type: 'rich_text',
                    config: {
                        rich_text_default: '<p><strong>Atenção:</strong> informar tutor sobre sinais de efeitos adversos gastrointestinais e necessidade de oferta de água fresca.</p>'
                    }
                }
            ]
        }
    };

    if (!$tableBody.length || !rowTemplate) {
        return;
    }

    function renderRowTemplate(index) {
        return rowTemplate.innerHTML.replace(/__INDEX__/g, index);
    }

    function updateEmptyState() {
        const hasRows = $tableBody.find('tr.dynamic-form').length > 0;
        const $emptyState = $tableBody.find('.empty-state');

        if (!$emptyState.length) {
            return;
        }

        if (hasRows) {
            $emptyState.addClass('d-none');
        } else {
            $emptyState.removeClass('d-none');
        }
    }

    function destroyEditors($row) {
        if (typeof tinymce === 'undefined') {
            return;
        }

        $row.find('textarea.rich-text').each(function () {
            const editorId = $(this).attr('id');
            if (editorId && tinymce.get(editorId)) {
                tinymce.get(editorId).remove();
            }
        });
    }

    function initEditor($textarea) {
        if (typeof tinymce === 'undefined' || !$textarea.length) {
            return;
        }

        const textarea = $textarea.get(0);
        if (!textarea.id) {
            textarea.id = `prescription-field-rich-text-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
        }

        if (tinymce.get(textarea.id)) {
            return;
        }

        tinymce.init({
            target: textarea,
            language: 'pt_BR',
            menubar: false,
            statusbar: false,
            height: 260,
            plugins: 'lists advlist table link',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link removeformat',
            setup(editor) {
                editor.on('change keyup', function () {
                    editor.save();
                });
            }
        });

        setTimeout(() => {
            $('.tox-statusbar__branding, .tox-promotion').addClass('d-none');
        }, 400);
    }

    function handleConfigVisibility($row, type) {
        const $configs = $row.find('.field-config');
        $configs.addClass('d-none');
        $configs.find('input, textarea, select').prop('disabled', true);

        destroyEditors($row);

        const $target = $configs.filter(`[data-config-for="${type}"]`);
        if ($target.length) {
            $target.removeClass('d-none');
            $target.find('input, textarea, select').prop('disabled', false);

            if (type === 'rich_text') {
                initEditor($target.find('textarea.rich-text'));
            }
        }
    }

    function normaliseConfigValueForInput(key, value) {
        if (Array.isArray(value)) {
            if ([
                'select_options',
                'multi_select_options',
                'checkbox_group_options',
                'radio_group_options'
            ].includes(key)) {
                return value.join('\n');
            }

            return value.join(', ');
        }

        if (typeof value === 'undefined' || value === null) {
            return '';
        }

        return value;
    }

    function populateRowWithData($row, fieldData) {
        if (!fieldData) {
            return;
        }

        if (fieldData.label) {
            $row.find('.field-label').val(fieldData.label);
        }

        if (fieldData.type) {
            const $type = $row.find('.field-type');
            $type.val(fieldData.type);
            handleConfigVisibility($row, fieldData.type);
        }

        const config = fieldData.config || {};
        const rowIndex = $row.data('row-index');

        Object.entries(config).forEach(([key, value]) => {
            let $input = $();

            if (typeof rowIndex !== 'undefined' && rowIndex !== null) {
                $input = $row.find(`[name="fields[${key}][${rowIndex}]"]`).first();
            }

            if (!$input.length) {
                $input = $row.find(`[name="fields[${key}][]"]`).first();
            }

            if (!$input.length) {
                return;
            }

            const normalisedValue = normaliseConfigValueForInput(key, value);

            if ($input.hasClass('rich-text') && typeof tinymce !== 'undefined') {
                const editorId = $input.attr('id');
                $input.val(normalisedValue || '');

                setTimeout(() => {
                    if (editorId && tinymce.get(editorId)) {
                        tinymce.get(editorId).setContent(normalisedValue || '');
                    }
                }, 250);
                return;
            }

            $input.val(normalisedValue || '');
        });
    }

    function bindRowEvents($row) {
        $row.find('.field-type').on('change', function () {
            handleConfigVisibility($row, $(this).val());
        });

        $row.find('.btn-remove-field').on('click', function () {
            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Deseja remover este campo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                const $rows = $tableBody.find('tr.dynamic-form');
                if ($rows.length === 1) {
                    destroyEditors($row);
                    $row.find('input[type="text"], input[type="number"], textarea').val('');

                    const $typeSelect = $row.find('.field-type');
                    if (isSelect2Available() && $typeSelect.data('select2')) {
                        const firstValue = ($typeSelect.find('option').first().val() || '').toString();
                        $typeSelect.val(firstValue).trigger('change.select2');
                    } else {
                        $typeSelect.prop('selectedIndex', 0).trigger('change');
                    }

                    updateEmptyState();
                    return;
                }

                destroyEditors($row);
                destroyFieldTypeSelect2($row);
                $row.remove();
                updateEmptyState();
            });
        });

        handleConfigVisibility($row, $row.find('.field-type').val());
    }

    function addRow(fieldData) {
        const index = rowIndex++;
        const html = renderRowTemplate(index);
        const $row = $(html);
        $row.attr('data-row-index', index);
        $tableBody.append($row);
        bindRowEvents($row);

        if (fieldData) {
            populateRowWithData($row, fieldData);
        }

        initFieldTypeSelect2($row);
        updateEmptyState();
    }

    function hasIncompleteRow() {
        let incomplete = false;

        $tableBody.find('tr.dynamic-form').each(function () {
            const $row = $(this);
            const type = $row.find('.field-type').val();

            if (!$row.find('.field-label').val()) {
                incomplete = true;
                return false;
            }

            const $visibleConfig = $row.find(`.field-config[data-config-for="${type}"]`);
            $visibleConfig.find('input, textarea').each(function () {
                const $input = $(this);
                if ($input.prop('disabled')) {
                    return;
                }

                if ($input.hasClass('rich-text')) {
                    const editorId = $input.attr('id');
                    if (typeof tinymce !== 'undefined' && editorId && tinymce.get(editorId)) {
                        const content = tinymce.get(editorId).getContent({ format: 'text' }).trim();
                        if (!content && !$input.data('optional')) {
                            incomplete = true;
                            return false;
                        }
                    }
                    return;
                }

                if ($input.data('optional')) {
                    return;
                }

                if (!$input.val()) {
                    incomplete = true;
                    return false;
                }
            });

            if (incomplete) {
                return false;
            }
        });

        return incomplete;
    }

    $('.btn-add-field').on('click', function () {
        if (hasIncompleteRow()) {
            Swal.fire('Atenção', 'Preencha as informações do campo atual antes de adicionar um novo.', 'warning');
            return;
        }

        addRow();
    });

    function normaliseOptions(value) {
        if (!value) {
            return [];
        }

        if (Array.isArray(value)) {
            return value
                .map((option) => (option || '').toString().trim())
                .filter((option) => option.length);
        }

        return value
            .toString()
            .split('\n')
            .map((option) => option.trim())
            .filter((option) => option.length);
    }

    function normaliseValueMap(value) {
        if (!value || typeof value !== 'object') {
            return {};
        }

        if (Array.isArray(value)) {
            return value.reduce((accumulator, current, index) => {
                accumulator[index] = current;
                return accumulator;
            }, {});
        }

        return { ...value };
    }

    function normaliseOldFields(raw) {
        if (!raw || typeof raw !== 'object') {
            return [];
        }

        const labelsMap = normaliseValueMap(raw.label);
        const typesMap = normaliseValueMap(raw.type);
        const configKeys = Object.keys(raw).filter((key) => key !== 'label' && key !== 'type');
        const configMaps = configKeys.reduce((accumulator, key) => {
            accumulator[key] = normaliseValueMap(raw[key]);
            return accumulator;
        }, {});

        const indices = Array.from(new Set([
            ...Object.keys(labelsMap),
            ...Object.keys(typesMap)
        ])).sort((a, b) => Number(a) - Number(b));

        const fields = [];

        indices.forEach((indexKey) => {
            const fieldLabel = (labelsMap[indexKey] || '').toString().trim();
            const fieldType = (typesMap[indexKey] || '').toString().trim();

            if (!fieldLabel || !fieldType) {
                return;
            }

            const config = {};

            configKeys.forEach((key) => {
                const map = configMaps[key] || {};
                if (!hasOwn.call(map, indexKey)) {
                    return;
                }

                const value = map[indexKey];

                if (typeof value === 'undefined' || value === null || value === '') {
                    return;
                }

                config[key] = value;
            });

            fields.push({
                label: fieldLabel,
                type: fieldType,
                config,
            });
        });

        return fields;
    }

    function extractRowData($row) {
        const label = ($row.find('.field-label').val() || '').trim();
        if (!label) {
            return null;
        }

        const type = $row.find('.field-type').val();
        const $config = $row.find(`.field-config[data-config-for="${type}"]`);
        const config = {};

        $config.find('input, textarea, select').each(function () {
            const $input = $(this);
            if ($input.prop('disabled')) {
                return;
            }

            let value = '';

            if ($input.hasClass('rich-text') && typeof tinymce !== 'undefined') {
                const editorId = $input.attr('id');
                if (editorId && tinymce.get(editorId)) {
                    value = tinymce.get(editorId).getContent();
                } else {
                    value = $input.val();
                }
            } else {
                value = $input.val();
            }

            const name = $input.attr('name');
            if (!name) {
                return;
            }

            const match = name.match(/^fields\[(.+?)]\[[^\]]*]$/) || name.match(/^fields\[(.+)]\[\]$/);
            if (!match) {
                return;
            }

            config[match[1]] = value;
        });

        return { label, type, config };
    }

    function clearRows() {
        $tableBody.find('tr.dynamic-form').each(function () {
            const $row = $(this);
            destroyEditors($row);
            destroyFieldTypeSelect2($row);
        }).remove();

        rowIndex = 0;
        updateEmptyState();
    }

    function applyTemplate(templateKey) {
        const template = templateLibrary[templateKey];
        if (!template) {
            return;
        }

        clearRows();

        template.fields.forEach((field) => addRow(field));

        if (template.title) {
            $form.find('input[name="title"]').val(template.title);
        }

        if (template.category) {
            $form.find('select[name="category"]').val(template.category).trigger('change');
        }

        if (typeof template.notes !== 'undefined') {
            $form.find('textarea[name="notes"]').val(template.notes);
        }

        Swal.fire('Layout aplicado', `O layout "${template.name}" foi carregado. Ajuste os campos conforme a necessidade.`, 'success');
    }

    function hasExistingConfiguration() {
        if ($tableBody.find('tr.dynamic-form').length > 0) {
            return true;
        }

        const title = ($form.find('input[name="title"]').val() || '').trim();
        const notes = ($form.find('textarea[name="notes"]').val() || '').trim();
        const category = ($form.find('select[name="category"]').val() || '').trim();

        return !!(title || notes || category);
    }

    function collectCurrentFields() {
        const rows = [];
        $tableBody.find('tr.dynamic-form').each(function () {
            const data = extractRowData($(this));
            if (data) {
                rows.push(data);
            }
        });

        return rows;
    }

    function renderPreviewField(field, index) {
        const fieldId = `prescription-preview-field-${index}`;
        const placeholder = field.config.placeholder ||
            field.config.textarea_placeholder ||
            field.config.date_hint ||
            field.config.time_hint ||
            field.config.datetime_hint ||
            field.config.email_placeholder ||
            field.config.phone_placeholder || '';

        const helpMessages = [];
        if (field.type === 'number' || field.type === 'integer') {
            if (field.config.number_min || field.config.integer_min) {
                helpMessages.push(`Mínimo: ${field.config.number_min || field.config.integer_min}`);
            }
            if (field.config.number_max || field.config.integer_max) {
                helpMessages.push(`Máximo: ${field.config.number_max || field.config.integer_max}`);
            }
        }

        let inputHtml = '';

        switch (field.type) {
            case 'text':
            case 'email':
            case 'phone':
                inputHtml = `<input type="${field.type === 'phone' ? 'tel' : field.type === 'email' ? 'email' : 'text'}" class="form-control" id="${fieldId}" placeholder="${placeholder}">`;
                break;
            case 'textarea':
                inputHtml = `<textarea class="form-control" id="${fieldId}" rows="4" placeholder="${placeholder}"></textarea>`;
                break;
            case 'number':
            case 'integer': {
                const stepAttr = field.type === 'integer' ? 'step="1"' : 'step="any"';
                inputHtml = `<input type="number" class="form-control" id="${fieldId}" placeholder="${placeholder}" ${stepAttr}>`;
                break;
            }
            case 'date':
                inputHtml = `<input type="date" class="form-control" id="${fieldId}">`;
                break;
            case 'time':
                inputHtml = `<input type="time" class="form-control" id="${fieldId}">`;
                break;
            case 'datetime':
                inputHtml = `<input type="datetime-local" class="form-control" id="${fieldId}">`;
                break;
            case 'select': {
                const options = normaliseOptions(field.config.select_options || field.config.multi_select_options);
                const optionsHtml = options.length
                    ? options.map((option) => `<option>${option}</option>`).join('')
                    : '<option disabled selected>Nenhuma opção configurada</option>';
                inputHtml = `<select class="form-select" id="${fieldId}">${optionsHtml}</select>`;
                break;
            }
            case 'multi_select': {
                const options = normaliseOptions(field.config.multi_select_options || field.config.select_options);
                const optionsHtml = options.length
                    ? options.map((option) => `<option>${option}</option>`).join('')
                    : '<option disabled>Nenhuma opção configurada</option>';
                inputHtml = `<select class="form-select" id="${fieldId}" multiple>${optionsHtml}</select>`;
                helpMessages.push('Selecione uma ou mais opções. Digite para filtrar rapidamente.');
                break;
            }
            case 'checkbox': {
                const defaultValue = field.config.checkbox_default === 'checked';
                const checkedAttr = defaultValue ? 'checked' : '';
                const checkedLabel = field.config.checkbox_label_checked || 'Sim';
                inputHtml = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="${fieldId}" ${checkedAttr}>
                        <label class="form-check-label" for="${fieldId}">${checkedLabel}</label>
                    </div>
                `;
                break;
            }
            case 'checkbox_group': {
                const options = normaliseOptions(field.config.checkbox_group_options);
                if (!options.length) {
                    inputHtml = '<p class="text-muted mb-0">Nenhuma opção configurada.</p>';
                    break;
                }

                inputHtml = options.map((option, optionIndex) => {
                    const optionId = `${fieldId}-checkbox-${optionIndex}`;
                    return `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="${optionId}">
                            <label class="form-check-label" for="${optionId}">${option}</label>
                        </div>
                    `;
                }).join('');
                break;
            }
            case 'radio_group': {
                const options = normaliseOptions(field.config.radio_group_options);
                if (!options.length) {
                    inputHtml = '<p class="text-muted mb-0">Nenhuma opção configurada.</p>';
                    break;
                }

                const defaultValue = (field.config.radio_group_default || '').trim();
                inputHtml = options.map((option, optionIndex) => {
                    const optionId = `${fieldId}-radio-${optionIndex}`;
                    const checkedAttr = option === defaultValue ? 'checked' : '';
                    return `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="${fieldId}" id="${optionId}" ${checkedAttr}>
                            <label class="form-check-label" for="${optionId}">${option}</label>
                        </div>
                    `;
                }).join('');
                break;
            }
            case 'file':
                inputHtml = `<input type="file" class="form-control" id="${fieldId}">`;
                if (field.config.file_types) {
                    helpMessages.push(`Tipos aceitos: ${field.config.file_types}`);
                }
                if (field.config.file_max_size) {
                    helpMessages.push(`Tamanho máximo: ${field.config.file_max_size} MB`);
                }
                break;
            case 'rich_text': {
                const content = (field.config.rich_text_default || '').trim();
                inputHtml = `
                    <div class="border rounded p-3 bg-white shadow-sm">
                        ${content || '<p class="text-muted mb-0">Área rica para anotações detalhadas.</p>'}
                    </div>
                `;
                break;
            }
            default:
                inputHtml = `<input type="text" class="form-control" id="${fieldId}" placeholder="${placeholder}">`;
        }

        const helpHtml = helpMessages.length
            ? `<small class="text-muted d-block mt-2">${helpMessages.join(' • ')}</small>`
            : '';

        return `
            <div class="mb-4">
                <label class="form-label fw-semibold" for="${fieldId}">${field.label}</label>
                ${inputHtml}
                ${helpHtml}
            </div>
        `;
    }

    function buildPreviewHtml(options) {
        const fields = Array.isArray(options.fields) ? options.fields : [];
        const title = (options.title || 'Modelo sem título').trim();
        const category = (options.categoryLabel || 'Categoria não informada').trim();
        const notes = (options.notes || '').trim();
        const showEmptyWarning = options.showEmptyWarning !== false;

        let headerHtml = `
            <div class="border-bottom pb-3 mb-4">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="mb-1">${title}</h4>
                        <span class="badge bg-primary">${category}</span>
                    </div>
                    <div class="text-end text-muted small">
                        <span class="d-block">Pré-visualização dinâmica</span>
                        <span>${fields.length} campo(s) configurado(s)</span>
                    </div>
                </div>
                ${notes ? `<p class="mt-3 mb-0 text-muted">${notes}</p>` : ''}
            </div>
        `;

        if (!fields.length && showEmptyWarning) {
            headerHtml += '<div class="alert alert-warning" role="alert">Adicione ao menos um campo para visualizar a simulação do formulário.</div>';
        }

        const bodyHtml = fields.map((field, index) => renderPreviewField(field, index)).join('');

        return headerHtml + bodyHtml;
    }

    function openPreview(options) {
        const html = buildPreviewHtml(options);
        $previewContent.html(html);
        $previewModal.modal('show');
    }

    $('.btn-preview-form').on('click', function () {
        const fields = collectCurrentFields();
        openPreview({
            title: ($form.find('input[name="title"]').val() || 'Modelo sem título').trim(),
            categoryLabel: $form.find('select[name="category"] option:selected').text() || 'Categoria não informada',
            notes: ($form.find('textarea[name="notes"]').val() || '').trim(),
            fields,
            showEmptyWarning: true
        });
    });

    $('.btn-preview-template').on('click', function () {
        const templateKey = $(this).data('template');
        const template = templateLibrary[templateKey];
        if (!template) {
            return;
        }

        openPreview({
            title: template.title || template.name,
            categoryLabel: template.previewCategoryLabel || 'Modelo de referência',
            notes: template.notes,
            fields: template.fields,
            showEmptyWarning: false
        });
    });

    $('.btn-apply-template').on('click', function () {
        const templateKey = $(this).data('template');
        const template = templateLibrary[templateKey];
        if (!template) {
            return;
        }

        const loadTemplate = () => {
            applyTemplate(templateKey);
        };

        if (hasExistingConfiguration()) {
            Swal.fire({
                title: 'Substituir configuração atual?',
                text: 'Carregar o layout pronto irá substituir os campos já configurados neste modelo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, carregar layout',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    loadTemplate();
                }
            });
            return;
        }

        loadTemplate();
    });

    const oldFields = normaliseOldFields(oldFieldsRaw);
    if (oldFields.length) {
        clearRows();
        oldFields.forEach((field) => addRow(field));
    } else {
        const existingFields = Array.isArray(existingFieldsRaw) ? existingFieldsRaw : [];

        if (existingFields.length) {
            clearRows();
            existingFields.forEach((field) => addRow(field));
        }
    }

    updateEmptyState();
})(jQuery);