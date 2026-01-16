(function () {
  'use strict';

  function getRecords() {
    if (!Array.isArray(window.vetRecordsData)) {
      return [];
    }

    return window.vetRecordsData;
  }

  function updateActiveElement(target) {
    document.querySelectorAll('[data-record-index]').forEach(function (element) {
      element.classList.remove('active');
    });

    if (target) {
      target.classList.add('active');
    }
  }

  function renderTags(tags) {
    var container = document.getElementById('vet-record-tags');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(tags) || tags.length === 0) {
      var placeholder = document.createElement('span');
      placeholder.className = 'text-muted small';
      placeholder.textContent = 'Sem etiquetas para exibir.';
      container.appendChild(placeholder);
      return;
    }

    tags.forEach(function (tag) {
      var badge = document.createElement('span');
      badge.className = 'vet-prontuarios__tag';
      badge.textContent = tag;
      container.appendChild(badge);
    });
  }

  function renderMetrics(metrics) {
    var container = document.getElementById('vet-record-metrics');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(metrics) || metrics.length === 0) {
      var alert = document.createElement('div');
      alert.className = 'col-12';
      alert.innerHTML = '<div class="alert alert-light mb-0">Nenhum indicador disponível.</div>';
      container.appendChild(alert);
      return;
    }

    metrics.forEach(function (metric) {
      var col = document.createElement('div');
      col.className = 'col-12 col-sm-6';

      var card = document.createElement('div');
      card.className = 'vet-prontuarios__metric-card';

      var icon = document.createElement('span');
      icon.className = 'vet-prontuarios__metric-icon';

      var iconElement = document.createElement('i');
      iconElement.className = metric.icon || '';
      icon.appendChild(iconElement);

      var labelRow = document.createElement('div');
      labelRow.className = 'd-flex justify-content-between align-items-center';

      var label = document.createElement('span');
      label.className = 'text-muted small';
      label.textContent = metric.label || '';

      var value = document.createElement('span');
      value.className = 'fw-semibold text-color';
      value.textContent = metric.value != null ? metric.value : '';

      labelRow.appendChild(label);
      labelRow.appendChild(value);

      card.appendChild(icon);
      card.appendChild(labelRow);
      col.appendChild(card);
      container.appendChild(col);
    });
  }

  function renderVitals(vitals) {
    var container = document.getElementById('vet-record-vitals');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(vitals) || vitals.length === 0) {
      var alert = document.createElement('div');
      alert.className = 'col-12';
      alert.innerHTML = '<div class="alert alert-light mb-0">Sem sinais vitais registrados.</div>';
      container.appendChild(alert);
      return;
    }

    vitals.forEach(function (vital) {
      var col = document.createElement('div');
      col.className = 'col-12 col-sm-6';

      var card = document.createElement('div');
      card.className = 'vet-prontuarios__vitals-card h-100';

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

  function renderNextSteps(steps) {
    var container = document.getElementById('vet-record-next-steps');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(steps) || steps.length === 0) {
      var item = document.createElement('li');
      item.className = 'list-group-item px-0 text-muted';
      item.textContent = 'Sem pendências registradas.';
      container.appendChild(item);
      return;
    }

    steps.forEach(function (step) {
      var item = document.createElement('li');
      item.className = 'list-group-item px-0';
      item.innerHTML = '<i class="ri-checkbox-circle-line text-primary me-2"></i>' + step;
      container.appendChild(item);
    });
  }

  function renderTimeline(events) {
    var container = document.getElementById('vet-record-timeline');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    var wrapper = document.createElement('div');
    wrapper.className = 'vet-prontuarios__timeline position-relative';

    if (!Array.isArray(events) || events.length === 0) {
      var placeholder = document.createElement('p');
      placeholder.className = 'text-muted small mb-0';
      placeholder.textContent = 'Nenhum evento registrado.';
      wrapper.appendChild(placeholder);
      container.appendChild(wrapper);
      return;
    }

    var list = document.createElement('div');
    list.className = 'd-flex flex-column gap-3';

    events.forEach(function (event) {
      var item = document.createElement('div');
      item.className = 'vet-prontuarios__timeline-item';

      var time = document.createElement('span');
      time.className = 'vet-prontuarios__timeline-time';
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

  function applyStatusBadge(statusColor, statusText) {
    var badge = document.getElementById('vet-record-status');

    if (!badge) {
      return;
    }

    badge.className = 'badge vet-prontuarios__badge-soft-' + (statusColor || 'primary');
    badge.textContent = statusText || 'Sem status';
  }

  function renderRecord(record) {
    if (!record) {
      return;
    }

    applyStatusBadge(record.status_color, record.status);

    var updated = document.getElementById('vet-record-updated');
    if (updated) {
      updated.textContent = record.updated_at || '--/--/---- --:--';
    }

    var patient = document.getElementById('vet-record-patient');
    if (patient) {
      patient.textContent = record.patient || 'Paciente não identificado';
    }

    var overview = document.getElementById('vet-record-overview');
    if (overview) {
      var parts = [];
      if (record.species) {
        parts.push(record.species);
      }
      if (record.breed) {
        parts.push(record.breed);
      }
      if (record.age) {
        parts.push(record.age);
      }
      overview.textContent = parts.join(' • ');
    }

    var summary = document.getElementById('vet-record-summary');
    if (summary) {
      summary.textContent = record.summary || '';
    }

    var code = document.getElementById('vet-record-code');
    if (code) {
      code.textContent = record.code || '—';
    }

    var veterinarian = document.getElementById('vet-record-veterinarian');
    if (veterinarian) {
      veterinarian.textContent = record.veterinarian || '—';
    }

    var type = document.getElementById('vet-record-type');
    if (type) {
      var details = [];
      if (record.type) {
        details.push(record.type);
      }
      if (record.clinic_room) {
        details.push(record.clinic_room);
      }
      type.textContent = details.join(' • ');
    }

    renderTags(record.tags);
    renderMetrics(record.metrics);
    renderVitals(record.vital_signs);
    renderNextSteps(record.next_steps);
    renderTimeline(record.timeline);
  }

  function onRecordClick(event) {
    var target = event.currentTarget;
    var index = parseInt(target.getAttribute('data-record-index'), 10);
    var record = getRecords()[index];

    if (!record) {
      return;
    }

    updateActiveElement(target);
    renderRecord(record);
  }

  function bindRecordSelection() {
    document.querySelectorAll('[data-record-index]').forEach(function (element) {
      element.addEventListener('click', onRecordClick);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var records = getRecords();

    if (records.length > 0) {
      renderRecord(records[0]);
    }

    bindRecordSelection();
  });
})();