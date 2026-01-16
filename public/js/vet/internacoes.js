(function () {
  'use strict';

  function getHospitalizations() {
    if (!Array.isArray(window.vetHospitalizationsData)) {
      return [];
    }

    return window.vetHospitalizationsData;
  }

  function updateActiveElement(target) {
    document.querySelectorAll('[data-hospitalization-index]').forEach(function (element) {
      element.classList.remove('active');
    });

    if (target && target.classList.contains('active') === false) {
      target.classList.add('active');
    }
  }

  function setBadge(elementId, color, text) {
    var element = document.getElementById(elementId);

    if (!element) {
      return;
    }

    element.className = 'badge text-bg-' + (color || 'primary');
    element.textContent = text || '';
  }

  function setText(elementId, value) {
    var element = document.getElementById(elementId);

    if (element) {
      element.textContent = value || '';
    }
  }

  function setPill(elementId, iconClass, value, fallback) {
    var element = document.getElementById(elementId);

    if (!element) {
      return;
    }

    var text = value || fallback || '';
    element.innerHTML = '<i class="' + iconClass + '"></i>' + (text ? ' ' + text : '');
  }

  function renderTags(tags) {
    var container = document.getElementById('vet-hosp-tags');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(tags) || tags.length === 0) {
      var placeholder = document.createElement('span');
      placeholder.className = 'text-muted small';
      placeholder.textContent = 'Sem etiquetas registradas.';
      container.appendChild(placeholder);
      return;
    }

    tags.forEach(function (tag) {
      var badge = document.createElement('span');
      badge.className = 'badge text-bg-secondary me-2';
      badge.textContent = tag;
      container.appendChild(badge);
    });
  }

  function renderVitals(vitals) {
    var container = document.getElementById('vet-hosp-vitals');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(vitals) || vitals.length === 0) {
      var col = document.createElement('div');
      col.className = 'col-12';
      col.innerHTML = '<div class="alert alert-light mb-0">Sem sinais vitais registrados.</div>';
      container.appendChild(col);
      return;
    }

    vitals.forEach(function (vital) {
      var col = document.createElement('div');
      col.className = 'col-12 col-sm-6';

      var card = document.createElement('div');
      card.className = 'border rounded-4 p-3 h-100 bg-light';

      var label = document.createElement('span');
      label.className = 'text-muted small';
      label.textContent = vital.label || '';

      var value = document.createElement('div');
      value.className = 'fw-semibold text-color';
      value.textContent = vital.value || '';

      card.appendChild(label);
      card.appendChild(value);
      col.appendChild(card);
      container.appendChild(col);
    });
  }

  function renderMedications(medications) {
    var container = document.getElementById('vet-hosp-medications');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(medications) || medications.length === 0) {
      var alert = document.createElement('div');
      alert.className = 'alert alert-light mb-0';
      alert.textContent = 'Nenhuma medicação registrada.';
      container.appendChild(alert);
      return;
    }

    medications.forEach(function (medication) {
      var card = document.createElement('div');
      card.className = 'border rounded-4 p-3 bg-white';

      var name = document.createElement('div');
      name.className = 'fw-semibold text-color';
      name.textContent = medication.name || '';

      var schedule = document.createElement('span');
      schedule.className = 'text-muted small';
      schedule.textContent = medication.schedule || '';

      card.appendChild(name);
      card.appendChild(schedule);
      container.appendChild(card);
    });
  }

  function renderProcedures(procedures) {
    var container = document.getElementById('vet-hosp-procedures');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(procedures) || procedures.length === 0) {
      var item = document.createElement('li');
      item.className = 'list-group-item px-0 text-muted';
      item.textContent = 'Sem procedimentos planejados.';
      container.appendChild(item);
      return;
    }

    procedures.forEach(function (procedure) {
      var item = document.createElement('li');
      item.className = 'list-group-item px-0';
      item.innerHTML = '<i class="ri-checkbox-circle-line text-primary me-2"></i>' + procedure;
      container.appendChild(item);
    });
  }

  function renderTeam(team) {
    var container = document.getElementById('vet-hosp-team');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(team) || team.length === 0) {
      var alert = document.createElement('div');
      alert.className = 'alert alert-light mb-0';
      alert.textContent = 'Nenhum profissional associado.';
      container.appendChild(alert);
      return;
    }

    team.forEach(function (member) {
      var row = document.createElement('div');
      row.className = 'd-flex align-items-center justify-content-between border rounded-4 p-3';

      var info = document.createElement('div');
      var name = document.createElement('span');
      name.className = 'fw-semibold text-color';
      name.textContent = member.name || '';

      var role = document.createElement('div');
      role.className = 'text-muted small';
      role.textContent = member.role || '';

      info.appendChild(name);
      info.appendChild(role);

      var action = document.createElement('button');
      action.type = 'button';
      action.className = 'btn btn-light btn-sm';
      action.innerHTML = '<i class="ri-chat-1-line"></i>';

      row.appendChild(info);
      row.appendChild(action);
      container.appendChild(row);
    });
  }

  function renderTimeline(events) {
    var container = document.getElementById('vet-hosp-timeline');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    var wrapper = document.createElement('div');
    wrapper.className = 'list-group list-group-flush';

    if (!Array.isArray(events) || events.length === 0) {
      var placeholder = document.createElement('p');
      placeholder.className = 'text-muted small mb-0';
      placeholder.textContent = 'Nenhum evento registrado.';
      wrapper.appendChild(placeholder);
      container.appendChild(wrapper);
      return;
    }

    var list = document.createElement('div');
    list.className = 'd-flex flex-column';

    events.forEach(function (event) {
      var item = document.createElement('div');
      item.className = 'list-group-item border-0 ps-0';

      var time = document.createElement('span');
      time.className = 'fw-semibold text-primary';
      time.textContent = event.time || '';

      var title = document.createElement('h6');
      title.className = 'text-color fw-semibold mb-1';
      title.textContent = event.title || '';

      var description = document.createElement('p');
      description.className = 'text-muted small mb-0';
      description.textContent = event.description || '';

      item.appendChild(time);
      item.appendChild(title);
      item.appendChild(description);
      list.appendChild(item);
    });

    wrapper.appendChild(list);
    container.appendChild(wrapper);
  }

  function renderPatient(patient) {
    if (!patient) {
      return;
    }

    setBadge('vet-hosp-status', patient.status_color, patient.status);
    setBadge('vet-hosp-risk', patient.risk_color, patient.risk_level);

    setText('vet-hosp-name', patient.patient || 'Paciente não identificado');

    var overviewParts = [];
    if (patient.species) {
      overviewParts.push(patient.species);
    }
    if (patient.breed) {
      overviewParts.push(patient.breed);
    }
    if (patient.age) {
      overviewParts.push(patient.age);
    }
    if (patient.weight) {
      overviewParts.push(patient.weight);
    }
    setText('vet-hosp-overview', overviewParts.join(' • '));

    setPill('vet-hosp-bed', 'ri-hotel-bed-line', patient.bed, 'Leito indefinido');
    setPill('vet-hosp-sector', 'ri-building-4-line', patient.sector, 'Setor não informado');
    setText('vet-hosp-check-in', patient.check_in || '—');
    setText('vet-hosp-discharge', patient.expected_discharge || '—');
    setText('vet-hosp-tutor', patient.tutor || '—');
    setText('vet-hosp-contact', patient.contact || '');
    setText('vet-hosp-diagnosis', patient.diagnosis || 'Sem diagnóstico registrado.');
    setText('vet-hosp-plan', patient.care_plan || 'Aguardando definição do plano de cuidados.');
    setText('vet-hosp-notes', patient.notes || 'Sem observações registradas.');

    renderTags(patient.tags);
    renderVitals(patient.vital_signs);
    renderMedications(patient.medications);
    renderProcedures(patient.procedures);
    renderTeam(patient.team);
    renderTimeline(patient.timeline);
  }

  function onPatientClick(event) {
    var target = event.currentTarget;
    var index = parseInt(target.getAttribute('data-hospitalization-index'), 10);
    var patient = getHospitalizations()[index];

    if (!patient) {
      return;
    }

    updateActiveElement(target);
    renderPatient(patient);
  }

  function bindPatientSelection() {
    document.querySelectorAll('[data-hospitalization-index]').forEach(function (element) {
      element.addEventListener('click', onPatientClick);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var patients = getHospitalizations();

    if (patients.length > 0) {
      renderPatient(patients[0]);
    }

    bindPatientSelection();
  });
})();