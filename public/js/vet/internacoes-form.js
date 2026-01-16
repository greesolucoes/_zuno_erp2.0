(function () {
    const form = document.getElementById('vet-hospitalization-form');

    if (!form) {
        return;
    }

    const patientSelect = document.getElementById('hospitalizationPatient');
    const tutorNameInput = document.getElementById('hospitalizationTutorName');
    const tutorPhonesList = document.getElementById('hospitalizationTutorPhones');
    const tutorNameDisplay = document.getElementById('hospitalizationTutorNameDisplay');
    const tutorDocumentDisplay = document.getElementById('hospitalizationTutorDocument');
    const tutorContactDisplay = document.getElementById('hospitalizationTutorContactDisplay');
    const tutorEmailDisplay = document.getElementById('hospitalizationTutorEmailDisplay');
    const patientPhoto = document.getElementById('hospitalizationPatientPhoto');
    const patientName = document.getElementById('hospitalizationPatientName');
    const patientMeta = document.getElementById('hospitalizationPatientMeta');
    const patientNotes = document.getElementById('hospitalizationPatientNotes');
    const patientMetrics = document.getElementById('hospitalizationPatientMetrics');
    const patientOverview = document.getElementById('hospitalizationPatientOverview');
    const statusInput = document.getElementById('hospitalizationStatus');
    const saveDraftButton = document.getElementById('hospitalizationSaveDraft');
    const submitButton = form ? form.querySelector('[type="submit"]') : null;
    const defaultAvatar = form.dataset.defaultAvatar || '';
    const preserveStatus = form ? form.dataset.preserveStatus === 'true' : false;

    const parsePatient = function (option) {
        if (!option || !option.dataset.patient) {
            return null;
        }

        try {
            return JSON.parse(option.dataset.patient);
        } catch (error) {
            console.warn('Não foi possível interpretar os dados do paciente selecionado.', error);
            return null;
        }
    };

    const renderMetrics = function (metrics) {
        if (!patientMetrics) {
            return;
        }

        patientMetrics.innerHTML = '';

        if (!Array.isArray(metrics) || metrics.length === 0) {
            const fallback = document.createElement('div');
            fallback.className = 'col-12';
            fallback.innerHTML = '<div class="text-muted small">Os dados clínicos serão exibidos após a seleção do paciente.</div>';
            patientMetrics.appendChild(fallback);
            return;
        }

        metrics.forEach(function (metric) {
            const col = document.createElement('div');
            col.className = 'col-6 col-lg-3';

            const card = document.createElement('div');
            card.className = 'vet-hospitalization-metric';

            const label = document.createElement('span');
            label.className = 'text-muted d-block mb-1';
            label.textContent = metric && metric.label ? metric.label : '';

            const value = document.createElement('strong');
            value.textContent = metric && metric.value ? metric.value : '—';

            card.appendChild(label);
            card.appendChild(value);
            col.appendChild(card);
            patientMetrics.appendChild(col);
        });
    };

    const renderPhones = function (phones) {
        if (!tutorPhonesList) {
            return;
        }

        tutorPhonesList.innerHTML = '';

        const normalized = Array.isArray(phones) ? phones.filter(Boolean) : [];

        if (normalized.length === 0) {
            const placeholder = document.createElement('li');
            placeholder.className = 'text-muted';
            placeholder.textContent = tutorPhonesList.dataset.emptyPlaceholder || 'Nenhum telefone adicional cadastrado.';
            tutorPhonesList.appendChild(placeholder);
            return;
        }

        normalized.forEach(function (phone) {
            const item = document.createElement('li');
            item.textContent = phone;
            tutorPhonesList.appendChild(item);
        });
    };

    const updatePatientSummary = function (patient) {
        const tutor = patient && patient.tutor ? patient.tutor : null;

        if (tutorNameInput) {
            tutorNameInput.value = tutor && tutor.name ? tutor.name : '';
        }

        if (tutorNameDisplay) {
            tutorNameDisplay.innerHTML = '<strong>' + (tutor && tutor.name ? tutor.name : 'Tutor não informado') + '</strong>';
        }

        if (tutorDocumentDisplay) {
            tutorDocumentDisplay.textContent = tutor && tutor.document ? tutor.document : '';
        }

        if (tutorContactDisplay) {
            if (tutor && tutor.contact) {
                tutorContactDisplay.innerHTML = '<i class="ri-phone-line me-1"></i>' + tutor.contact;
            } else {
                tutorContactDisplay.textContent = '';
            }
        }

        if (tutorEmailDisplay) {
            tutorEmailDisplay.textContent = '';

            if (tutor && tutor.email) {
                tutorEmailDisplay.innerHTML = '<i class="ri-mail-line me-1"></i>' + tutor.email;
            }
        }

        renderPhones(tutor ? tutor.phones : null);

        if (patientPhoto) {
            patientPhoto.src = patient && patient.photo ? patient.photo : defaultAvatar;
        }

        if (patientName) {
            patientName.textContent = patient && patient.name ? patient.name : 'Selecione um paciente';
        }

        if (patientMeta) {
            patientMeta.textContent = patient && patient.meta
                ? patient.meta
                : 'As informações do paciente aparecerão aqui após a seleção.';
        }

        if (patientNotes) {
            const notes = patient && patient.notes ? patient.notes : null;
            patientNotes.textContent = notes || 'Adicione observações iniciais na seção de observações abaixo.';
        }

        if (patientOverview) {
            patientOverview.textContent = patient
                ? 'Confirme as informações antes de concluir a admissão do paciente.'
                : 'Selecione um paciente para visualizar os dados clínicos e contatos do tutor.';
        }

        renderMetrics(patient && Array.isArray(patient.metrics) ? patient.metrics : null);
    };

    if (patientSelect) {
        patientSelect.addEventListener('change', function () {
            const option = this.selectedOptions[0];
            const patient = parsePatient(option);
            updatePatientSummary(patient);
        });
    }

    if (form) {
        form.addEventListener('submit', function () {
            if (statusInput && !preserveStatus && statusInput.value !== 'rascunho') {
                statusInput.value = 'ativo';
            }
        });
    }

    if (submitButton) {
        submitButton.addEventListener('click', function () {
            if (statusInput && !preserveStatus) {
                statusInput.value = 'ativo';
            }
        });
    }

    if (saveDraftButton) {
        saveDraftButton.addEventListener('click', function () {
            if (statusInput) {
                statusInput.value = 'rascunho';
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    }
})();