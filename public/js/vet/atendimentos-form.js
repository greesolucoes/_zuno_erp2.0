(function () {
    const root = document.getElementById('vet-encounter-form');

    if (!root) {
        return;
    }

    const dataset = root.dataset;

    const patients = JSON.parse(dataset.patients || '[]');
    const veterinarians = JSON.parse(dataset.veterinarians || '[]');
    const services = JSON.parse(dataset.services || '[]');
    const rooms = JSON.parse(dataset.rooms || '[]');
    const checklists = JSON.parse(dataset.checklists || '[]');
    const attachments = JSON.parse(dataset.attachments || '[]');
    const communications = JSON.parse(dataset.communications || '[]');
    const guides = JSON.parse(dataset.guides || '[]');
    const timeline = JSON.parse(dataset.timeline || '[]');
    const nextSteps = JSON.parse(dataset.nextSteps || '[]');
    const quickNotes = JSON.parse(dataset.quickNotes || '[]');

    const patientSelect = document.getElementById('vetEncounterPatient');
    const tutorContactInput = document.getElementById('vetEncounterTutorContact');
    const patientPhoto = document.getElementById('vetEncounterPatientPhoto');
    const patientName = document.getElementById('vetEncounterPatientName');
    const patientPlan = document.getElementById('vetEncounterPatientPlan');
    const patientInfo = document.getElementById('vetEncounterPatientInfo');
    const patientSummary = document.getElementById('vetEncounterClinicalSummary');
    const alertsWrapper = document.getElementById('vetEncounterAlerts');
    const treatmentsWrapper = document.getElementById('vetEncounterTreatments');

    const veterinarianSelect = document.getElementById('vetEncounterVeterinarian');
    const veterinarianInfo = document.getElementById('vetEncounterVeterinarianInfo');

    const serviceSelect = document.getElementById('vetEncounterService');
    const serviceDescription = document.getElementById('vetEncounterServiceDescription');
    const serviceChips = document.getElementById('vetEncounterServiceChips');
    const servicePreparations = document.getElementById('vetEncounterServicePreparations');

    const roomSelect = document.getElementById('vetEncounterRoom');
    const roomResources = document.getElementById('vetEncounterRoomResources');

    const dateInput = document.getElementById('vetEncounterDate');
    const timeSelect = document.getElementById('vetEncounterTime');

    const checklistWrapper = document.getElementById('vetEncounterChecklist');
    const attachmentsWrapper = document.getElementById('vetEncounterAttachments');
    const communicationsWrapper = document.getElementById('vetEncounterCommunications');
    const summaryWrapper = document.getElementById('vetEncounterSummary');
    const timelineWrapper = document.getElementById('vetEncounterTimeline');
    const quickNotesWrapper = document.getElementById('vetEncounterQuickNotes');
    const guidesWrapper = document.getElementById('vetEncounterGuides');

    const patientsMap = Object.fromEntries(patients.map((patient) => [patient.id, patient]));
    const veterinariansMap = Object.fromEntries(veterinarians.map((vet) => [vet.id, vet]));
    const servicesMap = Object.fromEntries(services.map((service) => [service.id, service]));
    const roomsMap = Object.fromEntries(rooms.map((room) => [room.id, room]));

    function createAlert(alert) {
        const col = document.createElement('div');
        col.className = 'col-12 col-md-6';

        const wrapper = document.createElement('div');
        wrapper.className = 'vet-encounter-form__alert-item h-100';

        const icon = document.createElement('i');
        icon.className = `${alert.icon} text-${alert.color}`;

        const text = document.createElement('div');
        text.className = 'small fw-semibold text-muted';
        text.textContent = alert.message;

        wrapper.appendChild(icon);
        wrapper.appendChild(text);
        col.appendChild(wrapper);

        return col;
    }

    function createChecklistGroup(group) {
        const col = document.createElement('div');
        col.className = 'col-12';

        const card = document.createElement('div');
        card.className = 'vet-encounter-form__checklist-item';

        const checkboxColumn = document.createElement('div');
        checkboxColumn.className = 'form-check mt-1';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'form-check-input';
        checkbox.id = `checklist-${group.id}`;

        const checklistBody = document.createElement('div');
        checklistBody.className = 'flex-grow-1';

        const title = document.createElement('h6');
        title.className = 'fw-semibold mb-2';
        title.textContent = group.title;

        const list = document.createElement('ul');
        list.className = 'list-unstyled mb-0 small text-muted';

        group.items.forEach((item) => {
            const listItem = document.createElement('li');
            listItem.className = 'd-flex align-items-start gap-2 mb-1';

            const indicator = document.createElement('i');
            indicator.className = item.mandatory ? 'ri-checkbox-circle-line text-success mt-1' : 'ri-checkbox-blank-circle-line text-muted mt-1';

            const text = document.createElement('span');
            text.textContent = `${item.label}${item.mandatory ? ' · obrigatório' : ''}`;

            listItem.appendChild(indicator);
            listItem.appendChild(text);
            list.appendChild(listItem);
        });

        checkboxColumn.appendChild(checkbox);
        card.appendChild(checkboxColumn);
        checklistBody.appendChild(title);
        checklistBody.appendChild(list);
        card.appendChild(checklistBody);
        col.appendChild(card);

        return col;
    }

    function createAttachmentCard(attachment) {
        const col = document.createElement('div');
        col.className = 'col-12';

        const card = document.createElement('div');
        card.className = 'vet-encounter-form__communication-card';

        const header = document.createElement('div');
        header.className = 'd-flex align-items-center justify-content-between mb-2';

        const title = document.createElement('h6');
        title.className = 'fw-semibold mb-0';
        title.textContent = attachment.title;

        const badge = document.createElement('span');
        badge.className = 'badge bg-light text-muted';
        badge.textContent = `${attachment.type} · ${attachment.size}`;

        const meta = document.createElement('p');
        meta.className = 'text-muted small mb-0';
        meta.textContent = `Enviado por ${attachment.uploadedBy}`;

        header.appendChild(title);
        header.appendChild(badge);
        card.appendChild(header);
        card.appendChild(meta);
        col.appendChild(card);

        return col;
    }

    function createCommunicationCard(communication) {
        const col = document.createElement('div');
        col.className = 'col-12';

        const card = document.createElement('div');
        card.className = 'vet-encounter-form__communication-card h-100';

        const header = document.createElement('div');
        header.className = 'd-flex align-items-center justify-content-between mb-2';

        const title = document.createElement('h6');
        title.className = 'fw-semibold mb-0';
        title.textContent = communication.title;

        const badge = document.createElement('span');
        badge.className = 'badge bg-primary-subtle text-primary';
        badge.textContent = communication.channel;

        const preview = document.createElement('p');
        preview.className = 'text-muted small mb-3';
        preview.textContent = communication.preview;

        const actions = document.createElement('div');
        actions.className = 'd-flex gap-2';

        const sendButton = document.createElement('button');
        sendButton.type = 'button';
        sendButton.className = 'btn btn-sm btn-primary';
        sendButton.textContent = 'Enviar agora';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn btn-sm btn-outline-secondary';
        editButton.textContent = 'Personalizar';

        header.appendChild(title);
        header.appendChild(badge);
        actions.appendChild(sendButton);
        actions.appendChild(editButton);
        card.appendChild(header);
        card.appendChild(preview);
        card.appendChild(actions);
        col.appendChild(card);

        return col;
    }

    function renderTimeline() {
        timelineWrapper.innerHTML = '';

        if (!timeline.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'Linha do tempo será preenchida após registrar atividades.';
            timelineWrapper.appendChild(empty);
            return;
        }

        timeline.forEach((item) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'vet-encounter-form__timeline-item mb-4';

            const time = document.createElement('span');
            time.className = 'text-primary fw-semibold d-block mb-1';
            time.textContent = item.time;

            const title = document.createElement('h6');
            title.className = 'fw-semibold mb-1';
            title.textContent = item.event;

            const description = document.createElement('p');
            description.className = 'text-muted small mb-0';
            description.textContent = item.description;

            wrapper.appendChild(time);
            wrapper.appendChild(title);
            wrapper.appendChild(description);
            timelineWrapper.appendChild(wrapper);
        });
    }

    function renderQuickNotes() {
        quickNotesWrapper.innerHTML = '';

        if (!quickNotes.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'Sem notas rápidas cadastradas.';
            quickNotesWrapper.appendChild(empty);
            return;
        }

        quickNotes.forEach((note, index) => {
            const col = document.createElement('div');
            col.className = 'col-12';

            const card = document.createElement('div');
            card.className = 'vet-encounter-form__note-card';
            card.dataset.note = note;
            card.tabIndex = 0;

            const text = document.createElement('p');
            text.className = 'mb-0 small fw-semibold text-muted';
            text.textContent = note;

            card.appendChild(text);
            card.addEventListener('click', () => {
                navigator.clipboard?.writeText(note).catch(() => {
                    /* ignore clipboard errors */
                });
            });

            card.addEventListener('keypress', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    card.click();
                }
            });

            col.appendChild(card);
            quickNotesWrapper.appendChild(col);
        });
    }

    function renderGuides() {
        guidesWrapper.innerHTML = '';

        if (!guides.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'Nenhum guia clínico disponível.';
            guidesWrapper.appendChild(empty);
            return;
        }

        guides.forEach((guide) => {
            const card = document.createElement('div');
            card.className = 'border rounded-4 p-3';

            const title = document.createElement('h6');
            title.className = 'fw-semibold mb-2';
            title.textContent = guide.title;

            const list = document.createElement('ul');
            list.className = 'list-unstyled mb-0 small text-muted';

            guide.items.forEach((item) => {
                const listItem = document.createElement('li');
                listItem.className = 'd-flex align-items-start gap-2 mb-1';

                const icon = document.createElement('i');
                icon.className = 'ri-checkbox-circle-line text-success mt-1';

                const span = document.createElement('span');
                span.textContent = item;

                listItem.appendChild(icon);
                listItem.appendChild(span);
                list.appendChild(listItem);
            });

            card.appendChild(title);
            card.appendChild(list);
            guidesWrapper.appendChild(card);
        });
    }

    function renderChecklists() {
        checklistWrapper.innerHTML = '';
        checklists.forEach((group) => {
            checklistWrapper.appendChild(createChecklistGroup(group));
        });
    }

    function renderAttachments() {
        attachmentsWrapper.innerHTML = '';
        if (!attachments.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'Nenhum anexo disponível para este atendimento.';
            attachmentsWrapper.appendChild(empty);
            return;
        }

        attachments.forEach((attachment) => {
            attachmentsWrapper.appendChild(createAttachmentCard(attachment));
        });
    }

    function renderCommunications() {
        communicationsWrapper.innerHTML = '';
        if (!communications.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'Nenhuma comunicação sugerida para este atendimento.';
            communicationsWrapper.appendChild(empty);
            return;
        }

        communications.forEach((communication) => {
            communicationsWrapper.appendChild(createCommunicationCard(communication));
        });
    }

    function updatePatient(patientId) {
        const patient = patientsMap[patientId];
        if (!patient) {
            return;
        }

        patientPhoto.src = patient.photo;
        patientName.textContent = patient.name;
        patientPlan.textContent = patient.plan;
        patientInfo.textContent = `${patient.species} · ${patient.breed} · ${patient.age} · ${patient.weight}`;
        patientSummary.textContent = patient.clinicalSummary;
        tutorContactInput.value = `${patient.tutor.name} · ${patient.tutor.phone}`;

        alertsWrapper.innerHTML = '';
        if (patient.alerts?.length) {
            patient.alerts.forEach((alert) => {
                alertsWrapper.appendChild(createAlert(alert));
            });
        } else {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'Nenhum alerta registrado para o paciente.';
            alertsWrapper.appendChild(empty);
        }

        treatmentsWrapper.innerHTML = '';
        if (patient.ongoingTreatments?.length) {
            patient.ongoingTreatments.forEach((treatment) => {
                const item = document.createElement('li');
                item.className = 'list-inline-item badge bg-light text-muted';
                item.textContent = treatment;
                treatmentsWrapper.appendChild(item);
            });
        } else {
            const item = document.createElement('li');
            item.className = 'text-muted small';
            item.textContent = 'Sem tratamentos em andamento.';
            treatmentsWrapper.appendChild(item);
        }

        updateSummary();
    }

    function updateVeterinarian(veterinarianId) {
        const vet = veterinariansMap[veterinarianId];
        if (!vet) {
            return;
        }

        veterinarianInfo.textContent = `${vet.role} · Próxima disponibilidade: ${vet.nextAvailability}`;
        updateSummary();
    }

    function updateService(serviceId) {
        const service = servicesMap[serviceId];
        if (!service) {
            return;
        }

        serviceDescription.textContent = service.description;

        serviceChips.innerHTML = '';

        const durationChip = document.createElement('span');
        durationChip.className = 'vet-encounter-form__info-chip vet-encounter-form__chip-success';
        durationChip.dataset.type = 'duration';
        durationChip.textContent = service.duration;

        const priceChip = document.createElement('span');
        priceChip.className = 'vet-encounter-form__info-chip vet-encounter-form__chip-warning';
        priceChip.dataset.type = 'price';
        priceChip.textContent = service.price;

        serviceChips.appendChild(durationChip);
        serviceChips.appendChild(priceChip);

        servicePreparations.innerHTML = '';
        if (service.preparations?.length) {
            service.preparations.forEach((preparation) => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-light text-muted';
                badge.textContent = preparation;
                servicePreparations.appendChild(badge);
            });
        } else {
            const text = document.createElement('span');
            text.className = 'text-muted small';
            text.textContent = 'Sem preparos específicos.';
            servicePreparations.appendChild(text);
        }

        updateSummary();
    }

    function updateRoom(roomId) {
        const room = roomsMap[roomId];
        if (!room) {
            return;
        }

        roomResources.innerHTML = '';
        if (room.resources?.length) {
            room.resources.forEach((resource) => {
                const item = document.createElement('li');
                item.innerHTML = `<i class="ri-checkbox-circle-line text-success me-1"></i>${resource}`;
                roomResources.appendChild(item);
            });
        } else {
            const item = document.createElement('li');
            item.className = 'text-muted small';
            item.textContent = 'Nenhum recurso adicional cadastrado.';
            roomResources.appendChild(item);
        }

        updateSummary();
    }

    function updateSummary() {
        const patient = patientsMap[patientSelect.value];
        const vet = veterinariansMap[veterinarianSelect.value];
        const service = servicesMap[serviceSelect.value];
        const room = roomsMap[roomSelect.value];
        const date = dateInput.value;
        const time = timeSelect.value;

        summaryWrapper.innerHTML = '';

        const summaryItems = [
            {
                icon: 'ri-user-heart-line',
                label: 'Paciente',
                value: patient ? `${patient.name} · ${patient.species}` : 'Selecione um paciente',
            },
            {
                icon: 'ri-user-3-line',
                label: 'Profissional',
                value: vet ? `${vet.name}` : 'Selecione um profissional',
            },
            {
                icon: 'ri-briefcase-4-line',
                label: 'Serviço',
                value: service ? service.name : 'Defina o serviço',
            },
            {
                icon: 'ri-calendar-line',
                label: 'Agendamento',
                value: date ? `${new Date(date).toLocaleDateString()} · ${time}` : 'Informe data e horário',
            },
            {
                icon: 'ri-home-8-line',
                label: 'Sala',
                value: room ? room.name : 'Selecione a sala',
            },
        ];

        summaryItems.forEach((item) => {
            const li = document.createElement('li');
            li.className = 'd-flex align-items-start gap-2 mb-2';

            const icon = document.createElement('i');
            icon.className = `${item.icon} text-primary mt-1`;

            const content = document.createElement('div');

            const label = document.createElement('span');
            label.className = 'd-block text-muted small';
            label.textContent = item.label;

            const value = document.createElement('span');
            value.className = 'fw-semibold text-dark';
            value.textContent = item.value;

            content.appendChild(label);
            content.appendChild(value);
            li.appendChild(icon);
            li.appendChild(content);
            summaryWrapper.appendChild(li);
        });
    }

    function hydrateTimeline() {
        renderTimeline();
    }

    function hydrateQuickNotes() {
        renderQuickNotes();
    }

    function hydrateGuides() {
        renderGuides();
    }

    function hydrateNextSteps() {
        if (!nextSteps.length) {
            return;
        }

        const list = document.getElementById('vetEncounterNextSteps');
        list.innerHTML = '';

        nextSteps.forEach((step) => {
            const item = document.createElement('li');
            item.className = 'd-flex gap-2 align-items-start mb-2';

            const icon = document.createElement('i');
            icon.className = 'ri-calendar-check-line text-primary mt-1';

            const text = document.createElement('span');
            text.textContent = step;

            item.appendChild(icon);
            item.appendChild(text);
            list.appendChild(item);
        });
    }

    patientSelect?.addEventListener('change', (event) => {
        updatePatient(event.target.value);
    });

    veterinarianSelect?.addEventListener('change', (event) => {
        updateVeterinarian(event.target.value);
    });

    serviceSelect?.addEventListener('change', (event) => {
        updateService(event.target.value);
    });

    roomSelect?.addEventListener('change', (event) => {
        updateRoom(event.target.value);
    });

    dateInput?.addEventListener('change', updateSummary);
    timeSelect?.addEventListener('change', updateSummary);

    renderChecklists();
    renderAttachments();
    renderCommunications();
    hydrateTimeline();
    hydrateQuickNotes();
    hydrateGuides();
    hydrateNextSteps();

    if (patientSelect?.value) {
        updatePatient(patientSelect.value);
    }

    if (veterinarianSelect?.value) {
        updateVeterinarian(veterinarianSelect.value);
    }

    if (serviceSelect?.value) {
        updateService(serviceSelect.value);
    }

    if (roomSelect?.value) {
        updateRoom(roomSelect.value);
    }

    updateSummary();
})();