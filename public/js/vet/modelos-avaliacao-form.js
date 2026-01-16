(function ($) {
    'use strict';

    const $form = $('#form-modelos-avaliacao');
    const $tableBody = $('.table-model-fields tbody');
    const rowTemplate = document.getElementById('assessment-field-row-template');
    const $previewModal = $('#assessment-preview-modal');
    const $previewContent = $previewModal.find('.assessment-preview-content');
    let rowIndex = 0;
    const oldFieldsRaw = typeof window !== 'undefined' ? window.assessmentModelOldFields || null : null;
    const existingFieldsRaw = typeof window !== 'undefined' ? window.assessmentModelExistingFields || null : null;

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
        anamnese: {
            name: 'Anamnese completa',
            title: 'Modelo de Anamnese Completa',
            category: 'anamnese',
            previewCategoryLabel: 'Anamnese',
            notes: 'Coleta estruturada de informações fornecidas pelo tutor, incluindo histórico clínico, ambiente e sinais observados.',
            fields: [
                {
                    label: 'Data da consulta',
                    type: 'date',
                    config: {
                        date_hint: 'Registre quando a anamnese está sendo realizada.'
                    }
                },
                {
                    label: 'Profissional responsável',
                    type: 'text',
                    config: {
                        placeholder: 'Ex.: Dra. Ana Paula Vieira'
                    }
                },
                {
                    label: 'Nome do tutor',
                    type: 'text',
                    config: {
                        placeholder: 'Quem acompanha o paciente?'
                    }
                },
                {
                    label: 'Contato do tutor',
                    type: 'phone',
                    config: {
                        phone_placeholder: '(00) 00000-0000'
                    }
                },
                {
                    label: 'Paciente',
                    type: 'text',
                    config: {
                        placeholder: 'Nome do animal'
                    }
                },
                {
                    label: 'Espécie',
                    type: 'select',
                    config: {
                        select_options: 'Canina\nFelina\nSilvestre\nOutra'
                    }
                },
                {
                    label: 'Sexo',
                    type: 'radio_group',
                    config: {
                        radio_group_options: 'Macho\nFêmea\nNão informado',
                        radio_group_default: 'Não informado'
                    }
                },
                {
                    label: 'Peso corporal (kg)',
                    type: 'number',
                    config: {
                        number_min: '0.5',
                        number_max: '120',
                        placeholder: 'Ex.: 8,3'
                    }
                },
                {
                    label: 'Queixa principal',
                    type: 'textarea',
                    config: {
                        textarea_placeholder: 'Descreva o motivo da consulta conforme relato do tutor.'
                    }
                },
                {
                    label: 'Histórico clínico',
                    type: 'textarea',
                    config: {
                        textarea_placeholder: 'Doenças prévias, tratamentos realizados, alergias, vacinas e vermifugação.'
                    }
                },
                {
                    label: 'Ambiente e alimentação',
                    type: 'checkbox_group',
                    config: {
                        checkbox_group_options: 'Vive em ambiente interno\nTem acesso à rua\nAlimentação natural\nRação comercial\nSuplementação nutricional'
                    }
                },
                {
                    label: 'Sinais clínicos observados',
                    type: 'multi_select',
                    config: {
                        multi_select_options: 'Apatia\nVômito\nDiarreia\nTosse\nPrurido\nClaudicação'
                    }
                },
                {
                    label: 'Observações adicionais',
                    type: 'rich_text',
                    config: {
                        rich_text_default: '<p>Detalhe alterações comportamentais, respostas a estímulos e demais observações relevantes.</p>'
                    }
                }
            ]
        },
        pre_consulta: {
            name: 'Pré-anestésico cirúrgico',
            title: 'Checklist Pré-Consulta Cirúrgica',
            category: 'triagem',
            previewCategoryLabel: 'Pré-consulta',
            notes: 'Checklist aplicado antes de procedimentos cirúrgicos para confirmar preparo do paciente e validar exames indispensáveis.',
            fields: [
                {
                    label: 'Data e hora da avaliação',
                    type: 'datetime',
                    config: {
                        datetime_hint: 'Informe quando o pré-anestésico foi realizado.'
                    }
                },
                {
                    label: 'Veterinário responsável',
                    type: 'text',
                    config: {
                        placeholder: 'Ex.: Dr. João Martins'
                    }
                },
                {
                    label: 'Peso atualizado (kg)',
                    type: 'number',
                    config: {
                        number_min: '0.5',
                        number_max: '150',
                        placeholder: 'Ex.: 12,4'
                    }
                },
                {
                    label: 'Jejum alimentar e hídrico',
                    type: 'checkbox',
                    config: {
                        checkbox_label_checked: 'Paciente em jejum adequado',
                        checkbox_label_unchecked: 'Paciente ainda não cumpriu jejum',
                        checkbox_default: 'unchecked'
                    }
                },
                {
                    label: 'Exames anexados',
                    type: 'checkbox_group',
                    config: {
                        checkbox_group_options: 'Hemograma completo\nPerfil renal\nPerfil hepático\nRadiografia torácica\nEcocardiograma'
                    }
                },
                {
                    label: 'Exames pendentes',
                    type: 'multi_select',
                    config: {
                        multi_select_options: 'Hemograma\nUltrassom abdominal\nCoagulograma\nGlicemia\nOutros'
                    }
                },
                {
                    label: 'Classificação ASA',
                    type: 'radio_group',
                    config: {
                        radio_group_options: 'ASA I - Paciente saudável\nASA II - Doença sistêmica leve\nASA III - Doença sistêmica grave\nASA IV - Doença sistêmica ameaçadora à vida',
                        radio_group_default: 'ASA II - Doença sistêmica leve'
                    }
                },
                {
                    label: 'Medicações em uso',
                    type: 'textarea',
                    config: {
                        textarea_placeholder: 'Informe medicamentos contínuos, datas e horários da última dose.'
                    }
                },
                {
                    label: 'Tempo previsto de procedimento',
                    type: 'time',
                    config: {
                        time_hint: 'Ex.: 01:30 para cirurgias de média duração.'
                    }
                },
                {
                    label: 'Documentos anexos',
                    type: 'file',
                    config: {
                        file_types: 'pdf, jpg, png',
                        file_max_size: '5'
                    }
                },
                {
                    label: 'Conduta recomendada',
                    type: 'rich_text',
                    config: {
                        rich_text_default: '<p>Liste protocolos anestésicos sugeridos, cuidados pré e pós-operatórios e observações ao tutor.</p>'
                    }
                }
            ]
        },
        pos_operatorio: {
            name: 'Revisão pós-operatória',
            title: 'Acompanhamento Pós-operatório',
            category: 'pos-operatorio',
            previewCategoryLabel: 'Retorno',
            notes: 'Modelo para reavaliações pós-operatórias com foco em cicatrização, controle de dor e orientações ao tutor.',
            fields: [
                {
                    label: 'Data do retorno',
                    type: 'date',
                    config: {
                        date_hint: 'Informe o dia da reavaliação.'
                    }
                },
                {
                    label: 'Hora da reavaliação',
                    type: 'time',
                    config: {
                        time_hint: 'Horário em que o paciente foi atendido.'
                    }
                },
                {
                    label: 'Temperatura corporal (°C)',
                    type: 'number',
                    config: {
                        number_min: '34',
                        number_max: '43',
                        placeholder: 'Ex.: 38,1'
                    }
                },
                {
                    label: 'Escala de dor (0 a 10)',
                    type: 'integer',
                    config: {
                        integer_min: '0',
                        integer_max: '10',
                        placeholder: 'Avalie a dor percebida pelo paciente.'
                    }
                },
                {
                    label: 'Condição da incisão',
                    type: 'select',
                    config: {
                        select_options: 'Seca, sem alterações\nLeve edema\nSecreção serosa\nSecreção purulenta\nDeiscência'
                    }
                },
                {
                    label: 'Alertas observados',
                    type: 'checkbox_group',
                    config: {
                        checkbox_group_options: 'Apatia\nInapetência\nFebre\nSangramento\nDor à palpação'
                    }
                },
                {
                    label: 'Medicações administradas no retorno',
                    type: 'textarea',
                    config: {
                        textarea_placeholder: 'Registre medicamentos aplicados durante o retorno e respectivas doses.'
                    }
                },
                {
                    label: 'Próximo acompanhamento',
                    type: 'datetime',
                    config: {
                        datetime_hint: 'Agende data e horário sugeridos para a próxima avaliação.'
                    }
                },
                {
                    label: 'Liberado para atividades',
                    type: 'checkbox',
                    config: {
                        checkbox_label_checked: 'Paciente liberado com restrições',
                        checkbox_label_unchecked: 'Manter repouso e monitoramento',
                        checkbox_default: 'unchecked'
                    }
                },
                {
                    label: 'Orientações ao tutor',
                    type: 'rich_text',
                    config: {
                        rich_text_default: '<p>Descreva recomendações para curativos, medicações domiciliares e sinais de alerta que exigem retorno imediato.</p>'
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
            textarea.id = `assessment-field-rich-text-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
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

        return value
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
        const hasOwn = Object.prototype.hasOwnProperty;

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

            const configKey = match[1];
            if (!configKey) {
                return;
            }

            config[configKey] = value;
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
        const fieldId = `assessment-preview-field-${index}`;
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
                helpMessages.push('Segure CTRL (Windows) ou CMD (Mac) para selecionar múltiplos itens.');
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