(function () {
  'use strict';

  var data = window.vetVaccinationScheduleData || {};
  var patients = Array.isArray(data.patients) ? data.patients : [];
  var vaccines = Array.isArray(data.vaccines) ? data.vaccines : [];
  var availability = Array.isArray(data.availability) ? data.availability : [];
  var rooms = Array.isArray(data.rooms) ? data.rooms : [];
  var veterinarians = Array.isArray(data.veterinarians) ? data.veterinarians : [];
  var defaultPatientPhoto = typeof data.defaultPatientPhoto === 'string' ? data.defaultPatientPhoto : '';

  var patientSelect = document.getElementById('vet-vaccination-patient-select');
  var vaccineContainer = document.getElementById('vet-vaccination-vaccine-container');
  var vaccineTemplate = document.getElementById('vet-vaccination-vaccine-template');
  var addVaccineButton = document.getElementById('vet-vaccination-add-vaccine');
  var dateSelect = document.getElementById('vet-vaccination-date-select');
  var timeSelect = document.getElementById('vet-vaccination-time-select');
  var vetSelect = document.getElementById('vet-vaccination-vet-select');
  var roomSelect = document.getElementById('vet-vaccination-room-select');
  var patientPhoto = document.getElementById('vetEncounterPatientPhoto');
  var tabsContainer = document.getElementById('vetVaccinationTabs');
  var contextualCards = document.querySelectorAll('[data-tab-context]');
  var ROOM_FEATURES_FALLBACK = 'Selecione uma sala para visualizar estrutura e equipamentos.';
  var ROOM_FEATURES_EMPTY = 'Cadastre salas clínicas para habilitar esta seleção.';
  var activeVaccineCard = null;
  var vaccineCardCounter = 0;

  function normalizeId(value) {
    if (value === null || value === undefined) {
      return '';
    }

    return String(value);
  }

  function isSameId(a, b) {
    return normalizeId(a) === normalizeId(b);
  }

  function findById(collection, id) {
    if (!Array.isArray(collection)) {
      return null;
    }

    var normalizedId = normalizeId(id);

    for (var index = 0; index < collection.length; index += 1) {
      var item = collection[index];
      if (item && isSameId(item.id, normalizedId)) {
        return item;
      }
    }

    return null;
  }

  function getSlot(date, time) {
    if (!date || !Array.isArray(date.slots)) {
      return null;
    }

    var normalizedTime = normalizeId(time);

    for (var index = 0; index < date.slots.length; index += 1) {
      var slot = date.slots[index];
      if (slot && isSameId(slot.time, normalizedTime)) {
        return slot;
      }
    }

    return null;
  }

  function setInputValue(id, value) {
    var element = document.getElementById(id);

    if (!element) {
      return;
    }

    element.value = value != null ? value : '';
  }

  function setText(id, value, fallback) {
    var element = document.getElementById(id);

    if (!element) {
      return;
    }

    element.textContent = value != null && value !== '' ? value : (fallback || '—');
  }

  function getCardFieldValue(card, field) {
    if (!card) {
      return '';
    }

    var element = card.querySelector('[data-field="' + field + '"]');

    if (!element) {
      return '';
    }

    if (
      element.tagName === 'INPUT' ||
      element.tagName === 'TEXTAREA' ||
      element.tagName === 'SELECT'
    ) {
      return element.value != null ? element.value : '';
    }

    return element.textContent != null ? element.textContent : '';
  }

  function setCardField(card, field, value, options) {
    if (!card) {
      return;
    }

    var element = card.querySelector('[data-field="' + field + '"]');

    if (!element) {
      return;
    }

    var normalizedValue = value != null ? value : '';
    var shouldPreserve = !!(options && options.preserveExisting);

    if (shouldPreserve) {
      var currentValue = element.value != null ? element.value : '';
      if (String(currentValue).trim() !== '') {
        return;
      }
    }

    if (
      element.tagName === 'INPUT' ||
      element.tagName === 'TEXTAREA' ||
      element.tagName === 'SELECT'
    ) {
      element.value = normalizedValue;
    }
  }

  function renderAlerts(alerts) {
    var container = document.getElementById('vet-vaccination-patient-alerts');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(alerts) || alerts.length === 0) {
      var empty = document.createElement('span');
      empty.className = 'text-muted small';
      empty.textContent = 'Nenhum alerta clínico registrado para este paciente.';
      container.appendChild(empty);
      return;
    }

    var variants = {
      danger: { className: 'bg-danger-subtle text-danger', icon: 'ri-error-warning-line' },
      warning: { className: 'bg-warning-subtle text-warning', icon: 'ri-alert-line' },
      info: { className: 'bg-primary-subtle text-primary', icon: 'ri-information-line' }
    };

    alerts.forEach(function (alert) {
      var wrapper = document.createElement('div');
      wrapper.className = 'vet-vaccination-schedule__alert-item bg-light';

      var iconWrapper = document.createElement('span');
      var variant = variants[alert.type] || variants.info;
      iconWrapper.className = 'vet-vaccination-schedule__alert-icon ' + variant.className;

      var icon = document.createElement('i');
      icon.className = variant.icon;
      iconWrapper.appendChild(icon);

      var content = document.createElement('div');
      var title = document.createElement('strong');
      title.className = 'd-block text-color';
      title.textContent = alert.title || 'Alerta clínico';

      var description = document.createElement('span');
      description.className = 'text-muted small';
      description.textContent = alert.description || 'Sem descrição disponível.';

      content.appendChild(title);
      content.appendChild(description);

      wrapper.appendChild(iconWrapper);
      wrapper.appendChild(content);

      container.appendChild(wrapper);
    });
  }

  function renderHistory(history) {
    var container = document.getElementById('vet-vaccination-history');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(history) || history.length === 0) {
      var empty = document.createElement('span');
      empty.className = 'text-muted small';
      empty.textContent = 'Sem histórico registrado.';
      container.appendChild(empty);
      return;
    }

    history.forEach(function (item) {
      var wrapper = document.createElement('div');
      wrapper.className = 'vet-vaccination-schedule__timeline-item mb-3';

      var date = document.createElement('span');
      date.className = 'vet-vaccination-schedule__timeline-date';
      date.textContent = item.date || '';

      var description = document.createElement('p');
      description.className = 'mb-0 text-color small';
      description.textContent = item.event || '';

      wrapper.appendChild(date);
      wrapper.appendChild(description);

      container.appendChild(wrapper);
    });
  }

  function toggleSidebarCardsForTab(targetId) {
    if (!contextualCards || contextualCards.length === 0) {
      return;
    }

    var normalizedTarget = typeof targetId === 'string' && targetId !== ''
      ? targetId
      : '#vetVaccinationTabPatient';

    contextualCards.forEach(function (card) {
      if (!card || !card.dataset) {
        return;
      }

      var contextValue = card.dataset.tabContext || '';
      var contexts = contextValue
        .split(',')
        .map(function (value) { return value.trim(); })
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
  }

  function initializeTabs() {
    if (!tabsContainer) {
      toggleSidebarCardsForTab('#vetVaccinationTabPatient');
      return;
    }

    var activeTrigger = tabsContainer.querySelector('.nav-link.active[data-bs-toggle="tab"]');
    var initialTarget = activeTrigger ? activeTrigger.getAttribute('data-bs-target') : null;
    toggleSidebarCardsForTab(initialTarget);

    var tabTriggers = tabsContainer.querySelectorAll('[data-bs-toggle="tab"]');

    tabTriggers.forEach(function (trigger) {
      trigger.addEventListener('shown.bs.tab', function (event) {
        var target = event && event.target ? event.target.getAttribute('data-bs-target') : null;
        toggleSidebarCardsForTab(target);
      });
    });
  }

  function parsePatientFromOption(option) {
    return parseOptionPayload(option, 'patient');
  }

  function parseOptionPayload(option, key) {
    if (!option || !option.dataset) {
      return null;
    }

    var raw = option.dataset[key];

    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw);
    } catch (error) {
      return null;
    }
  }

  function resolvePatient(id) {
    var normalizedId = normalizeId(id);

    if (!normalizedId) {
      return null;
    }

    var patient = findById(patients, normalizedId);
    if (patient) {
      return patient;
    }

    if (!patientSelect) {
      return null;
    }

    for (var index = 0; index < patientSelect.options.length; index += 1) {
      var option = patientSelect.options[index];
      if (option && isSameId(option.value, normalizedId)) {
        var parsed = parsePatientFromOption(option);
        if (parsed) {
          return parsed;
        }
      }
    }

    return null;
  }

  function applyPatientDetails(patient) {
    var info = patient || {};

    setText('vet-vaccination-patient-plan', info.plan);
    setInputValue('vet-vaccination-patient-tutor', info.tutor);
    setInputValue('vet-vaccination-patient-contact', info.contact);
    setInputValue('vet-vaccination-patient-species', info.species);
    setInputValue('vet-vaccination-patient-breed', info.breed);
    setInputValue('vet-vaccination-patient-age', info.age);
    setInputValue('vet-vaccination-patient-weight', info.weight);

    var notes = document.getElementById('vet-vaccination-patient-notes');
    if (notes) {
      notes.value = info.notes || '';
    }

    if (patientPhoto) {
      var photoSource = info.photo && info.photo !== '' ? info.photo : defaultPatientPhoto;
      patientPhoto.src = photoSource || '';
      patientPhoto.alt = info.name ? 'Foto de ' + info.name : 'Foto do paciente';
    }

    setText('vetEncounterPatientName', info.name, 'Selecione um paciente');

    var meta = info.meta;
    if (!meta) {
      var metaParts = [];
      if (info.species) {
        metaParts.push(info.species);
      }
      if (info.breed) {
        metaParts.push(info.breed);
      }
      if (info.age) {
        metaParts.push(info.age);
      }
      meta = metaParts.join(' • ');
    }

    setText('vetEncounterPatientMeta', meta);
    setText('vetEncounterPatientWeight', info.weight);
    setText('vetEncounterPatientSex', info.sex);
    setText('vetEncounterPatientBirthDate', info.birth_date);
    setText('vetEncounterPatientLastVisit', info.last_visit);
    setText('vetEncounterPatientSize', info.size);
    setText('vetEncounterPatientOrigin', info.origin);
    setText('vetEncounterPatientMicrochip', info.microchip);
    setText('vetEncounterPatientPedigree', info.pedigree);
    setText('vetEncounterPatientNotes', info.notes, 'Sem observações clínicas registradas.');
    setText('vetEncounterTutorSummaryName', info.tutor, 'Tutor não informado');
    setText('vetEncounterTutorSummaryDocument', info.tutor_document);
    setText('vetEncounterTutorSummaryContacts', info.contact);
    setText('vetEncounterTutorSummaryEmail', info.email);
    setText('vetEncounterTutorSummaryAddress', info.tutor_address);

    renderAlerts(info.alerts);
    renderHistory(info.history);
  }

  function renderSlots(date) {
    if (!timeSelect) {
      return;
    }

    timeSelect.innerHTML = '';

    if (!date || !Array.isArray(date.slots) || date.slots.length === 0) {
      var option = document.createElement('option');
      option.value = '';
      option.textContent = 'Nenhum horário disponível';
      timeSelect.appendChild(option);
      setText('vet-vaccination-summary-time', '—');
      return;
    }

    date.slots.forEach(function (slot, index) {
      var option = document.createElement('option');
      option.value = slot.time;
      option.textContent = slot.label || slot.time;
      option.dataset.label = slot.label || slot.time;
      if (index === 0) {
        option.selected = true;
      }
      timeSelect.appendChild(option);
    });
  }

  function updatePatient(value) {
    var targetId = value !== undefined ? value : (patientSelect ? patientSelect.value : '');
    var patient = resolvePatient(targetId);

    if (!patient && normalizeId(targetId) !== '') {
      applyPatientDetails(null);
      return;
    }

    applyPatientDetails(patient);
  }

  function updateVaccineSummaryFromCard(card) {
    var vaccine = null;
    var manufacturer = '';
    var lot = '';
    var validUntil = '';
    var route = '';
    var dose = '';
    var volume = '';

    if (card) {
      var select = card.querySelector('[data-role="vaccine-select"]');
      vaccine = findById(vaccines, select ? select.value : null);
      manufacturer = getCardFieldValue(card, 'manufacturer');
      lot = getCardFieldValue(card, 'lot');
      validUntil = getCardFieldValue(card, 'valid_until');
      route = getCardFieldValue(card, 'route');
      dose = getCardFieldValue(card, 'dose');
      volume = getCardFieldValue(card, 'volume');
    }

    if (!manufacturer && vaccine) {
      manufacturer = vaccine.manufacturer || '';
    }

    if (!lot && vaccine) {
      lot = vaccine.lot || '';
    }

    if (!validUntil && vaccine) {
      validUntil = vaccine.valid_until || '';
    }

    if (!route && vaccine) {
      route = vaccine.route || '';
    }

    if (!dose && vaccine) {
      dose = vaccine.dose || '';
    }

    if (!volume && vaccine) {
      volume = vaccine.volume || '';
    }

    var stock = vaccine && vaccine.stock ? vaccine.stock : null;

    setText('vet-vaccination-summary-manufacturer', manufacturer, '—');
    setText('vet-vaccination-summary-lot', lot, '—');
    setText('vet-vaccination-summary-valid', validUntil, '—');
    setText('vet-vaccination-summary-route', route, '—');
    setText('vet-vaccination-summary-dose', dose, '—');
    setText('vet-vaccination-summary-volume', volume, '—');
    setText('vet-vaccination-summary-stock', stock ? stock.available : null, '—');
    setText('vet-vaccination-summary-reserved', stock ? stock.reserved : null, '—');
    setText('vet-vaccination-summary-temperature', vaccine ? vaccine.temperature_range : null, '—');
  }

  function updateVaccineCard(card, options) {
    if (!card) {
      return;
    }

    var select = card.querySelector('[data-role="vaccine-select"]');

    if (!select) {
      return;
    }

    var preserveExisting = options && options.preserveExisting === true;
    var forceDefault = options && options.forceDefault === true;
    var vaccine = findById(vaccines, select.value);

    if (!vaccine && forceDefault && vaccines.length > 0) {
      vaccine = vaccines[0];
      select.value = normalizeId(vaccine.id);
    }

    if (!vaccine) {
      if (!preserveExisting) {
        setCardField(card, 'manufacturer', '', { preserveExisting: false });
        setCardField(card, 'volume', '', { preserveExisting: false });
        setCardField(card, 'lot', '', { preserveExisting: false });
        setCardField(card, 'valid_until', '', { preserveExisting: false });
        setCardField(card, 'route', '', { preserveExisting: false });
        setCardField(card, 'dose', '', { preserveExisting: false });
        setCardField(card, 'site', '', { preserveExisting: false });
        setCardField(card, 'observations', '', { preserveExisting: false });
      }

      card.dataset.vaccineId = normalizeId(select.value);

      if (card === activeVaccineCard) {
        updateVaccineSummaryFromCard(card);
      }

      return;
    }

    setCardField(card, 'manufacturer', vaccine.manufacturer, { preserveExisting: preserveExisting });
    setCardField(card, 'volume', vaccine.volume, { preserveExisting: preserveExisting });
    setCardField(card, 'lot', vaccine.lot, { preserveExisting: preserveExisting });
    setCardField(card, 'valid_until', vaccine.valid_until, { preserveExisting: preserveExisting });
    setCardField(card, 'route', vaccine.route, { preserveExisting: preserveExisting });
    setCardField(card, 'dose', vaccine.dose, { preserveExisting: preserveExisting });
    setCardField(card, 'site', vaccine.site, { preserveExisting: preserveExisting });
    setCardField(card, 'observations', vaccine.observations, { preserveExisting: preserveExisting });

    card.dataset.vaccineId = normalizeId(vaccine.id);

    if (card === activeVaccineCard) {
      updateVaccineSummaryFromCard(card);
    }
  }

  function refreshVaccineCardTitles() {
    if (!vaccineContainer) {
      return;
    }

    var cards = vaccineContainer.querySelectorAll('[data-vaccine-card]');

    cards.forEach(function (card, index) {
      var indicator = card.querySelector('[data-role="dose-index"]');
      if (indicator) {
        indicator.textContent = index + 1;
      }
    });
  }

  function updateRemoveButtons() {
    if (!vaccineContainer) {
      return;
    }

    var cards = vaccineContainer.querySelectorAll('[data-vaccine-card]');
    var total = cards.length;

    cards.forEach(function (card) {
      var removeButton = card.querySelector('[data-action="remove-card"]');
      if (!removeButton) {
        return;
      }

      if (total <= 1) {
        removeButton.classList.add('d-none');
        removeButton.setAttribute('tabindex', '-1');
        removeButton.setAttribute('aria-hidden', 'true');
      } else {
        removeButton.classList.remove('d-none');
        removeButton.removeAttribute('tabindex');
        removeButton.removeAttribute('aria-hidden');
      }
    });
  }

  function createVaccineCardFromTemplate(index) {
    if (!vaccineTemplate) {
      return null;
    }

    var html = vaccineTemplate.innerHTML.replace(/__INDEX__/g, String(index));
    var wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    var card = wrapper.firstElementChild;

    if (card) {
      card.setAttribute('data-index', String(index));
    }

    return card;
  }

  function setActiveVaccineCard(card) {
    if (card === activeVaccineCard) {
      updateVaccineSummaryFromCard(card);
      return;
    }

    if (activeVaccineCard) {
      activeVaccineCard.classList.remove('is-active');
    }

    activeVaccineCard = card || null;

    if (activeVaccineCard) {
      activeVaccineCard.classList.add('is-active');
    }

    updateVaccineSummaryFromCard(activeVaccineCard);
  }

  function enhanceVaccineSelect(select) {
    if (!select) {
      return;
    }

    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
      return;
    }

    var $ = window.jQuery;
    var $select = $(select);

    if ($select.data('select2')) {
      return;
    }

    var placeholder = select.getAttribute('data-placeholder') || 'Selecione a vacina';
    var allowClear = select.getAttribute('data-allow-clear') === 'true';
    var dropdownParent = $select.closest('[data-vaccine-card]');

    $select.select2({
      width: '100%',
      placeholder: placeholder,
      allowClear: allowClear,
      dropdownParent: dropdownParent.length ? dropdownParent : $select.parent()
    });

    $select.on('select2:open.vetVaccination', function () {
      var card = select.closest('[data-vaccine-card]');
      if (card) {
        setActiveVaccineCard(card);
      }
    });

    $select.on('select2:clear.vetVaccination', function () {
      select.value = '';
      $select.trigger('change');
    });
  }

  function destroyVaccineSelect(select) {
    if (!select) {
      return;
    }

    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
      return;
    }

    var $select = window.jQuery(select);

    if (!$select.data('select2')) {
      return;
    }

    $select.off('.vetVaccination');
    $select.select2('destroy');
  }

  function removeVaccineCard(card) {
    if (!card || !vaccineContainer) {
      return;
    }

    var wasActive = card === activeVaccineCard;
    var nextActive = null;

    if (wasActive) {
      var nextSibling = card.nextElementSibling;
      while (nextSibling) {
        if (nextSibling.matches && nextSibling.matches('[data-vaccine-card]')) {
          nextActive = nextSibling;
          break;
        }
        nextSibling = nextSibling.nextElementSibling;
      }

      if (!nextActive) {
        var previousSibling = card.previousElementSibling;
        while (previousSibling) {
          if (previousSibling.matches && previousSibling.matches('[data-vaccine-card]')) {
            nextActive = previousSibling;
            break;
          }
          previousSibling = previousSibling.previousElementSibling;
        }
      }
    }

    var select = card.querySelector('[data-role="vaccine-select"]');
    destroyVaccineSelect(select);

    card.remove();

    refreshVaccineCardTitles();
    updateRemoveButtons();

    if (wasActive) {
      if (nextActive) {
        setActiveVaccineCard(nextActive);
      } else {
        var fallback = vaccineContainer.querySelector('[data-vaccine-card]');
        setActiveVaccineCard(fallback);
      }
    }
  }

  function addVaccineCard() {
    if (!vaccineContainer || !vaccineTemplate) {
      return;
    }

    if (!Array.isArray(vaccines) || vaccines.length === 0) {
      return;
    }

    vaccineCardCounter += 1;
    var index = vaccineCardCounter;
    var card = createVaccineCardFromTemplate(index);

    if (!card) {
      return;
    }

    vaccineContainer.appendChild(card);
    updateVaccineCard(card, { forceDefault: true });

    var select = card.querySelector('[data-role="vaccine-select"]');
    enhanceVaccineSelect(select);

    if (
      select &&
      (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function')
    ) {
      select.focus();
    }

    refreshVaccineCardTitles();
    updateRemoveButtons();
    setActiveVaccineCard(card);
  }

  function initializeVaccineCards() {
    if (!vaccineContainer) {
      if (addVaccineButton) {
        addVaccineButton.disabled = true;
        addVaccineButton.classList.add('disabled');
      }
      updateVaccineSummaryFromCard(null);
      return;
    }

    var cards = vaccineContainer.querySelectorAll('[data-vaccine-card]');
    var maxIndex = -1;

    cards.forEach(function (card) {
      var index = parseInt(card.getAttribute('data-index'), 10);
      if (!isNaN(index) && index > maxIndex) {
        maxIndex = index;
      }

      updateVaccineCard(card, { preserveExisting: true });

      var select = card.querySelector('[data-role="vaccine-select"]');
      enhanceVaccineSelect(select);
    });

    vaccineCardCounter = Math.max(maxIndex, cards.length - 1, 0);

    refreshVaccineCardTitles();
    updateRemoveButtons();

    if (cards.length > 0) {
      setActiveVaccineCard(cards[0]);
    } else {
      setActiveVaccineCard(null);
    }

    if ((!Array.isArray(vaccines) || vaccines.length === 0) && addVaccineButton) {
      addVaccineButton.disabled = true;
      addVaccineButton.classList.add('disabled');
    }
  }

  function updateDate() {
    if (!dateSelect) {
      return;
    }

    var date = findById(availability, dateSelect.value);
    var selectedOption = dateSelect.options[dateSelect.selectedIndex];

    setText('vet-vaccination-summary-date', selectedOption ? selectedOption.textContent : '—');
    setText('vet-vaccination-summary-slot-note', date && date.note ? date.note : 'Selecione uma data para visualizar recomendações.');

    renderSlots(date);
    updateTime();
  }

  function updateTime() {
    if (!timeSelect) {
      return;
    }

    var date = findById(availability, dateSelect ? dateSelect.value : null);
    var selectedTime = timeSelect.value;
    var slot = getSlot(date, selectedTime);

    if (!slot && timeSelect.options.length > 0) {
      var option = timeSelect.options[timeSelect.selectedIndex];
      if (option) {
        setText('vet-vaccination-summary-time', option.dataset.label || option.textContent);
        return;
      }
    }

    setText('vet-vaccination-summary-time', slot && (slot.label || slot.time), '—');
  }

  function updateVeterinarian() {
    if (!vetSelect) {
      return;
    }

    if (vetSelect.disabled) {
      setInputValue('vet-vaccination-vet-crm', '');
      return;
    }

    var option = vetSelect.selectedIndex >= 0 ? vetSelect.options[vetSelect.selectedIndex] : null;
    var info = parseOptionPayload(option, 'veterinarian');

    if (!info && option) {
      info = findById(veterinarians, option.value);
    }

    if (!info && option) {
      info = {
        crm: option.getAttribute('data-crm') || ''
      };
    }

    setInputValue('vet-vaccination-vet-crm', info && info.crm ? info.crm : '');
  }

  function updateRoom() {
    if (!roomSelect) {
      return;
    }

    if (roomSelect.disabled) {
      setText('vet-vaccination-room-features', ROOM_FEATURES_EMPTY, ROOM_FEATURES_EMPTY);
      return;
    }

    var option = roomSelect.selectedIndex >= 0 ? roomSelect.options[roomSelect.selectedIndex] : null;
    var info = parseOptionPayload(option, 'room');

    if (!info && option) {
      info = findById(rooms, option.value);
    }

    var features = info && info.features ? info.features : null;
    setText('vet-vaccination-room-features', features, ROOM_FEATURES_FALLBACK);
  }

  function initializePatientSelect() {
    if (!patientSelect || !window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
      return;
    }

    var $ = window.jQuery;
    var $patientSelect = $(patientSelect);
    var placeholder = patientSelect.getAttribute('data-placeholder') || 'Selecione o paciente';
    var allowClear = patientSelect.getAttribute('data-allow-clear') === 'true';
    var dropdownParent = $patientSelect.closest('.vet-vaccination-schedule__card');

    $patientSelect.select2({
      width: '100%',
      placeholder: placeholder,
      allowClear: allowClear,
      dropdownParent: dropdownParent.length ? dropdownParent : $patientSelect.parent()
    });

    var instance = $patientSelect.data('select2');
    if (instance && instance.$container) {
      instance.$container.addClass('select2-lg');
    }

    $patientSelect.on('select2:select', function (event) {
      var selectedId = event && event.params && event.params.data ? event.params.data.id : undefined;
      updatePatient(selectedId);
    });

    $patientSelect.on('select2:clear', function () {
      if (patientSelect) {
        patientSelect.value = '';
      }
      updatePatient('');
    });
  }

  function initializeVetSelect() {
    if (!vetSelect || vetSelect.disabled) {
      updateVeterinarian();
      return;
    }

    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
      updateVeterinarian();
      return;
    }

    var $ = window.jQuery;
    var $vetSelect = $(vetSelect);
    var placeholder = vetSelect.getAttribute('data-placeholder') || 'Selecione o profissional';
    var allowClear = vetSelect.getAttribute('data-allow-clear') === 'true';
    var dropdownParent = $vetSelect.closest('.vet-vaccination-schedule__card');

    $vetSelect.select2({
      width: '100%',
      placeholder: placeholder,
      allowClear: allowClear,
      dropdownParent: dropdownParent.length ? dropdownParent : $vetSelect.parent()
    });

    var instance = $vetSelect.data('select2');
    if (instance && instance.$container) {
      instance.$container.addClass('select2-lg');
    }

    $vetSelect.on('select2:select', function () {
      updateVeterinarian();
    });

    $vetSelect.on('select2:clear', function () {
      vetSelect.value = '';
      updateVeterinarian();
    });
  }

  function initializeRoomSelect() {
    if (!roomSelect) {
      updateRoom();
      return;
    }

    if (roomSelect.disabled) {
      updateRoom();
      return;
    }

    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
      updateRoom();
      return;
    }

    var $ = window.jQuery;
    var $roomSelect = $(roomSelect);
    var placeholder = roomSelect.getAttribute('data-placeholder') || 'Selecione a sala clínica';
    var allowClear = roomSelect.getAttribute('data-allow-clear') === 'true';
    var dropdownParent = $roomSelect.closest('.vet-vaccination-schedule__card');

    $roomSelect.select2({
      width: '100%',
      placeholder: placeholder,
      allowClear: allowClear,
      dropdownParent: dropdownParent.length ? dropdownParent : $roomSelect.parent()
    });

    var instance = $roomSelect.data('select2');
    if (instance && instance.$container) {
      instance.$container.addClass('select2-lg');
    }

    $roomSelect.on('select2:select', function () {
      updateRoom();
    });

    $roomSelect.on('select2:clear', function () {
      roomSelect.value = '';
      updateRoom();
    });
  }

  function init() {
    initializeTabs();
    initializePatientSelect();
    initializeVetSelect();
    initializeRoomSelect();
    updatePatient();
    initializeVaccineCards();
    updateDate();
    updateRoom();
    updateVeterinarian();
  }

  if (patientSelect) {
    patientSelect.addEventListener('change', function () {
      updatePatient();
    });
  }

  if (addVaccineButton) {
    addVaccineButton.addEventListener('click', function (event) {
      event.preventDefault();
      addVaccineCard();
    });
  }

  if (vaccineContainer) {
    vaccineContainer.addEventListener('change', function (event) {
      var select = event.target.closest('[data-role="vaccine-select"]');
      if (!select || !vaccineContainer.contains(select)) {
        return;
      }

      var card = select.closest('[data-vaccine-card]');
      updateVaccineCard(card, { forceDefault: false });
      setActiveVaccineCard(card);
    });

    vaccineContainer.addEventListener('click', function (event) {
      var removeButton = event.target.closest('[data-action="remove-card"]');
      if (removeButton && vaccineContainer.contains(removeButton)) {
        event.preventDefault();
        var cardToRemove = removeButton.closest('[data-vaccine-card]');
        removeVaccineCard(cardToRemove);
        return;
      }

      var card = event.target.closest('[data-vaccine-card]');
      if (card && vaccineContainer.contains(card)) {
        setActiveVaccineCard(card);
      }
    });

    vaccineContainer.addEventListener('focusin', function (event) {
      var card = event.target.closest('[data-vaccine-card]');
      if (card && vaccineContainer.contains(card)) {
        setActiveVaccineCard(card);
      }
    });
  }

  if (dateSelect) {
    dateSelect.addEventListener('change', updateDate);
  }

  if (timeSelect) {
    timeSelect.addEventListener('change', updateTime);
  }

  if (vetSelect) {
    vetSelect.addEventListener('change', updateVeterinarian);
  }

  if (roomSelect) {
    roomSelect.addEventListener('change', updateRoom);
  }

  document.addEventListener('DOMContentLoaded', init);
})();