(function () {
  'use strict';

  var data = window.vetAgendaScheduleData || {};
  var patients = Array.isArray(data.patients) ? data.patients : [];
  var veterinarians = Array.isArray(data.veterinarians) ? data.veterinarians : [];
  var services = Array.isArray(data.services) ? data.services : [];
  var locations = Array.isArray(data.locations) ? data.locations : [];
  var availability = Array.isArray(data.availability) ? data.availability : [];
  var communicationTemplates = data.communicationTemplates || {};

  var patientSelect = document.getElementById('vet-agenda-patient-select');
  var dateSelect = document.getElementById('vet-agenda-date-select');
  var timeSelect = document.getElementById('vet-agenda-time-select');
  var vetSelect = document.getElementById('vet-agenda-vet-select');
  var serviceSelect = document.getElementById('vet-agenda-service-select');
  var locationSelect = document.getElementById('vet-agenda-location-select');
  var channelSelect = document.getElementById('vet-agenda-channel-select');
  var modeRadios = document.querySelectorAll('input[name="vet-agenda-mode"]');

  function findById(collection, id) {
    if (!Array.isArray(collection)) {
      return null;
    }

    for (var index = 0; index < collection.length; index += 1) {
      if (collection[index] && String(collection[index].id) === String(id)) {
        return collection[index];
      }
    }

    return null;
  }

  function setText(id, value, fallback) {
    var element = document.getElementById(id);

    if (!element) {
      return;
    }

    element.textContent = value != null && value !== '' ? value : (fallback || '—');
  }

  function setHTML(id, html) {
    var element = document.getElementById(id);

    if (!element) {
      return;
    }

    element.innerHTML = html || '';
  }

  function renderAlerts(alerts) {
    var container = document.getElementById('vet-agenda-patient-alerts');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(alerts) || alerts.length === 0) {
      var empty = document.createElement('span');
      empty.className = 'text-muted small';
      empty.textContent = 'Nenhum alerta cadastrado.';
      container.appendChild(empty);
      return;
    }

    var variants = {
      danger: { className: 'bg-danger-subtle text-danger', icon: 'ri-alert-line' },
      warning: { className: 'bg-warning-subtle text-warning', icon: 'ri-error-warning-line' },
      info: { className: 'bg-primary-subtle text-primary', icon: 'ri-information-line' }
    };

    alerts.forEach(function (alert) {
      var wrapper = document.createElement('div');
      wrapper.className = 'vet-agenda-schedule__alert-item bg-light';

      var badge = document.createElement('span');
      var variant = variants[alert.type] || variants.info;
      badge.className = 'vet-agenda-schedule__alert-icon ' + variant.className;

      var icon = document.createElement('i');
      icon.className = variant.icon;
      badge.appendChild(icon);

      var content = document.createElement('div');
      var title = document.createElement('strong');
      title.className = 'd-block text-color';
      title.textContent = alert.title || 'Alerta clínico';

      var description = document.createElement('span');
      description.className = 'text-muted small';
      description.textContent = alert.description || 'Sem detalhes disponíveis.';

      content.appendChild(title);
      content.appendChild(description);

      wrapper.appendChild(badge);
      wrapper.appendChild(content);

      container.appendChild(wrapper);
    });
  }

  function renderTimeline(history) {
    var container = document.getElementById('vet-agenda-patient-history');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(history) || history.length === 0) {
      var empty = document.createElement('span');
      empty.className = 'text-muted small';
      empty.textContent = 'Sem histórico disponível.';
      container.appendChild(empty);
      return;
    }

    history.forEach(function (item) {
      var wrapper = document.createElement('div');
      wrapper.className = 'vet-agenda-schedule__timeline-item mb-3';

      var date = document.createElement('span');
      date.className = 'vet-agenda-schedule__timeline-date';
      date.textContent = item.date || '';

      var description = document.createElement('p');
      description.className = 'mb-0 text-color small';
      description.textContent = item.event || '';

      wrapper.appendChild(date);
      wrapper.appendChild(description);

      container.appendChild(wrapper);
    });
  }

  function renderRequirements(requirements) {
    var container = document.getElementById('vet-agenda-service-requirements');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(requirements) || requirements.length === 0) {
      var empty = document.createElement('li');
      empty.className = 'text-muted small';
      empty.textContent = 'Nenhum requisito cadastrado para este serviço.';
      container.appendChild(empty);
      return;
    }

    requirements.forEach(function (requirement) {
      var item = document.createElement('li');
      item.className = 'text-muted small';

      var dot = document.createElement('span');
      dot.className = 'vet-agenda-schedule__list-dot';

      item.appendChild(dot);
      item.appendChild(document.createTextNode(requirement || 'Requisito'));
      container.appendChild(item);
    });
  }

  function renderGuidelines(guideline) {
    var container = document.getElementById('vet-agenda-service-guidelines');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!guideline) {
      var empty = document.createElement('li');
      empty.className = 'text-muted small';
      empty.textContent = 'Selecione um serviço para exibir orientações.';
      container.appendChild(empty);
      return;
    }

    var item = document.createElement('li');
    item.className = 'text-muted small';

    var dot = document.createElement('span');
    dot.className = 'vet-agenda-schedule__list-dot';

    item.appendChild(dot);
    item.appendChild(document.createTextNode(guideline));
    container.appendChild(item);
  }

  function renderTimeOptions(date) {
    if (!timeSelect) {
      return;
    }

    timeSelect.innerHTML = '';

    if (!date || !Array.isArray(date.slots)) {
      var emptyOption = document.createElement('option');
      emptyOption.value = '';
      emptyOption.textContent = 'Sem horários disponíveis';
      timeSelect.appendChild(emptyOption);
      return;
    }

    date.slots.forEach(function (slot, index) {
      var option = document.createElement('option');
      option.value = slot.time;
      option.textContent = slot.label || slot.time;

      if (index === 0) {
        option.selected = true;
      }

      timeSelect.appendChild(option);
    });
  }

  function updatePatient(patient) {
    if (!patient) {
      return;
    }

    setText('vet-agenda-patient-name', patient.name, 'Paciente');
    setText('vet-agenda-patient-species', patient.species, 'Espécie');
    setText('vet-agenda-patient-breed', patient.breed, 'Raça');
    setText('vet-agenda-patient-age', patient.age, '—');
    setText('vet-agenda-patient-weight', patient.weight, '—');

    if (patient.guardian) {
      setText('vet-agenda-patient-guardian-name', patient.guardian.name, 'Tutor não informado');
      setText('vet-agenda-patient-guardian-phone', patient.guardian.phone, '--');
      setText('vet-agenda-patient-guardian-email', patient.guardian.email, '--');
    } else {
      setText('vet-agenda-patient-guardian-name', 'Tutor não informado');
      setText('vet-agenda-patient-guardian-phone', '--');
      setText('vet-agenda-patient-guardian-email', '--');
    }

    if (patient.plan) {
      setText('vet-agenda-patient-plan', patient.plan.label, 'Sem plano');
      setText('vet-agenda-patient-plan-status', patient.plan.status, '—');
      setText('vet-agenda-patient-plan-valid', patient.plan.valid_until, '--/--/----');

      var statusElement = document.getElementById('vet-agenda-patient-plan-status');
      if (statusElement) {
        statusElement.className = 'badge ';
        if (patient.plan.status === 'Ativo') {
          statusElement.className += 'vet-agenda-schedule__badge-soft-success';
        } else {
          statusElement.className += 'vet-agenda-schedule__badge-soft-warning';
        }
      }
    }

    if (patient.metrics) {
      setText('vet-agenda-patient-metric-temperature', patient.metrics.temperature, '--');
      setText('vet-agenda-patient-metric-heart', patient.metrics.heart_rate, '--');
      setText('vet-agenda-patient-metric-respiratory', patient.metrics.respiratory_rate, '--');
    }

    setText('vet-agenda-patient-last-visit', patient.last_visit, '--');
    setText('vet-agenda-patient-diet', patient.diet, '--');

    var photo = document.getElementById('vet-agenda-patient-photo');
    if (photo) {
      photo.src = patient.photo || '/assets/images/pets/dog-02.svg';
      photo.alt = patient.name || 'Paciente';
    }

    renderAlerts(patient.alerts);
    renderTimeline(patient.history);
  }

  function updateVeterinarian(veterinarian) {
    if (!veterinarian) {
      return;
    }

    setText('vet-agenda-vet-specialty', veterinarian.specialty, 'Especialidade');
    setText('vet-agenda-vet-crm', veterinarian.crm, 'CRMV');
    setText('vet-agenda-vet-next', veterinarian.next_availability, '--');
    setText('vet-agenda-summary-vet', veterinarian.name, '--');
    setText('vet-agenda-summary-vet-contact', 'Contato: ' + (veterinarian.contact || '--'));
    setText('vet-agenda-vet-contact', veterinarian.contact, '--');
    setText('vet-agenda-vet-email', veterinarian.email, '--');
  }

  function updateService(service) {
    if (!service) {
      return;
    }

    setText('vet-agenda-service-duration', service.duration, '--');
    setText('vet-agenda-summary-service', service.name, '--');
    setText('vet-agenda-summary-duration', service.duration, '--');

    renderRequirements(service.requirements);
    renderGuidelines(service.guidelines);

    if (service.default_location && locationSelect) {
      var defaultLocation = findById(locations, service.default_location);
      if (defaultLocation) {
        locationSelect.value = String(defaultLocation.id);
        updateLocation(defaultLocation);
      }
    }
  }

  function updateLocation(location) {
    if (!location) {
      return;
    }

    setText('vet-agenda-location-resources', location.resources, 'Selecione a sala para visualizar recursos.');
    setText('vet-agenda-location-notes', location.notes, '');
    setText('vet-agenda-summary-location', location.name, '--');
  }

  function updateDate(date) {
    if (!date) {
      return;
    }

    renderTimeOptions(date);
    setText('vet-agenda-date-note', date.note, 'Selecione uma data para visualizar recomendações.');
    setText('vet-agenda-summary-date', date.label, '--');

    if (date.slots && date.slots.length > 0) {
      updateTime(date.slots[0], date);
    } else {
      setText('vet-agenda-summary-time', '--');
    }
  }

  function updateTime(slot, date) {
    if (!slot) {
      setText('vet-agenda-summary-time', '--');
      return;
    }

    setText('vet-agenda-summary-time', slot.label || slot.time, '--');

    var context = getCurrentContext();
    context.date = date ? date.label : context.date;
    context.time = slot.label || slot.time || context.time;
    updateCommunicationPreview(context);
  }

  function getCurrentContext() {
    var patient = findById(patients, patientSelect ? patientSelect.value : null) || {};
    var veterinarian = findById(veterinarians, vetSelect ? vetSelect.value : null) || {};
    var service = findById(services, serviceSelect ? serviceSelect.value : null) || {};
    var date = findById(availability, dateSelect ? dateSelect.value : null) || {};
    var slot = null;

    if (date && date.slots && timeSelect) {
      for (var index = 0; index < date.slots.length; index += 1) {
        if (String(date.slots[index].time) === String(timeSelect.value)) {
          slot = date.slots[index];
          break;
        }
      }
    }

    var mode = 'Presencial';
    modeRadios.forEach(function (radio) {
      if (radio.checked) {
        if (radio.value === 'online') {
          mode = 'Teleatendimento';
        } else if (radio.value === 'domicilio') {
          mode = 'Domiciliar';
        }
      }
    });

    return {
      patient: patient,
      veterinarian: veterinarian,
      service: service,
      date: date.label,
      time: slot ? (slot.label || slot.time) : '',
      slot: slot,
      mode: mode,
      channel: channelSelect ? channelSelect.value : 'whatsapp'
    };
  }

  function updateSummary(context) {
    if (!context) {
      return;
    }

    setText('vet-agenda-summary-patient', context.patient.name, '--');
    setText('vet-agenda-summary-tutor', 'Tutor: ' + (context.patient.guardian ? context.patient.guardian.name : '--'));
    setText('vet-agenda-summary-vet', context.veterinarian.name, '--');
    setText('vet-agenda-summary-vet-contact', 'Contato: ' + (context.veterinarian.contact || '--'));
    setText('vet-agenda-summary-service', context.service.name, '--');
    setText('vet-agenda-summary-duration', context.service.duration, '--');
    setText('vet-agenda-summary-date', context.date, '--');
    setText('vet-agenda-summary-time', context.time, '--');

    var summaryChannel = document.getElementById('vet-agenda-summary-channel');
    if (summaryChannel) {
      summaryChannel.textContent = channelLabel(context.channel);
    }

    var summaryMode = document.getElementById('vet-agenda-summary-mode');
    if (summaryMode) {
      summaryMode.textContent = 'Modalidade: ' + (context.mode || 'Presencial');
    }
  }

  function channelLabel(channel) {
    if (channel === 'email') {
      return 'E-mail';
    }

    if (channel === 'sms') {
      return 'SMS';
    }

    return 'WhatsApp';
  }

  function updateCommunicationPreview(context) {
    var template = communicationTemplates[context.channel] || communicationTemplates.whatsapp || '';
    var preview = template;

    if (preview && typeof preview === 'string') {
      preview = preview
        .replace(/\{\{tutor\}\}/g, context.patient.guardian ? context.patient.guardian.name : 'tutor')
        .replace(/\{\{paciente\}\}/g, context.patient.name || 'paciente')
        .replace(/\{\{veterinario\}\}/g, context.veterinarian.name || 'veterinário')
        .replace(/\{\{data\}\}/g, context.date || 'data a definir')
        .replace(/\{\{hora\}\}/g, context.time || 'horário a definir');
    }

    setText('vet-agenda-communication-preview', preview, 'Personalize a mensagem de confirmação.');
  }

  function handlePatientChange() {
    var patient = findById(patients, patientSelect.value);
    if (!patient) {
      return;
    }

    updatePatient(patient);

    var context = getCurrentContext();
    updateSummary(context);
    updateCommunicationPreview(context);
  }

  function handleVeterinarianChange() {
    var veterinarian = findById(veterinarians, vetSelect.value);
    if (!veterinarian) {
      return;
    }

    updateVeterinarian(veterinarian);

    var context = getCurrentContext();
    updateSummary(context);
    updateCommunicationPreview(context);
  }

  function handleServiceChange() {
    var service = findById(services, serviceSelect.value);
    if (!service) {
      return;
    }

    updateService(service);

    var context = getCurrentContext();
    updateSummary(context);
    updateCommunicationPreview(context);
  }

  function handleLocationChange() {
    var location = findById(locations, locationSelect.value);
    if (!location) {
      return;
    }

    updateLocation(location);

    var context = getCurrentContext();
    updateSummary(context);
  }

  function handleDateChange() {
    var date = findById(availability, dateSelect.value);
    if (!date) {
      return;
    }

    updateDate(date);

    var context = getCurrentContext();
    updateSummary(context);
    updateCommunicationPreview(context);
  }

  function handleTimeChange() {
    var date = findById(availability, dateSelect.value);
    var slot = null;

    if (date && Array.isArray(date.slots)) {
      for (var index = 0; index < date.slots.length; index += 1) {
        if (String(date.slots[index].time) === String(timeSelect.value)) {
          slot = date.slots[index];
          break;
        }
      }
    }

    updateTime(slot, date);

    var context = getCurrentContext();
    updateSummary(context);
  }

  function handleChannelChange() {
    var context = getCurrentContext();
    updateSummary(context);
    updateCommunicationPreview(context);
  }

  function handleModeChange() {
    var context = getCurrentContext();
    updateSummary(context);
  }

  function init() {
    var context = getCurrentContext();

    updatePatient(context.patient);
    updateVeterinarian(context.veterinarian);
    updateService(context.service);
    updateLocation(findById(locations, locationSelect ? locationSelect.value : null));
    updateDate(findById(availability, dateSelect ? dateSelect.value : null));
    updateSummary(getCurrentContext());
    updateCommunicationPreview(getCurrentContext());

    if (patientSelect) {
      patientSelect.addEventListener('change', handlePatientChange);
    }

    if (vetSelect) {
      vetSelect.addEventListener('change', handleVeterinarianChange);
    }

    if (serviceSelect) {
      serviceSelect.addEventListener('change', handleServiceChange);
    }

    if (locationSelect) {
      locationSelect.addEventListener('change', handleLocationChange);
    }

    if (dateSelect) {
      dateSelect.addEventListener('change', handleDateChange);
    }

    if (timeSelect) {
      timeSelect.addEventListener('change', handleTimeChange);
    }

    if (channelSelect) {
      channelSelect.addEventListener('change', handleChannelChange);
    }

    modeRadios.forEach(function (radio) {
      radio.addEventListener('change', handleModeChange);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();