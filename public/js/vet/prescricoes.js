(function () {
  'use strict';

  function getPrescriptions() {
    if (!Array.isArray(window.vetPrescriptionsData)) {
      return [];
    }

    return window.vetPrescriptionsData;
  }

  function toNumber(value) {
    var parsed = Number(value);

    return Number.isNaN(parsed) ? 0 : parsed;
  }

  function updateActiveRow(target) {
    document.querySelectorAll('[data-prescription-index]').forEach(function (row) {
      row.classList.remove('active');
    });

    if (target) {
      target.classList.add('active');
    }
  }

  function setText(id, value, fallback) {
    var element = document.getElementById(id);

    if (!element) {
      return;
    }

    element.textContent = value != null && value !== '' ? value : fallback || '—';
  }

  function updateBadge(id, baseClassPrefix, color, text) {
    var element = document.getElementById(id);

    if (!element) {
      return;
    }

    var normalizedColor = typeof color === 'string' && color.trim() !== '' ? color.trim() : 'primary';

    element.className = 'badge ' + baseClassPrefix + normalizedColor;
    element.textContent = text || '—';
  }

  function renderTags(tags) {
    var container = document.getElementById('vet-prescription-tags');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(tags) || tags.length === 0) {
      var placeholder = document.createElement('span');
      placeholder.className = 'text-muted small';
      placeholder.textContent = 'Nenhuma etiqueta registrada.';
      container.appendChild(placeholder);
      return;
    }

    tags.forEach(function (tag) {
      var badge = document.createElement('span');
      badge.className = 'vet-prescricoes__tag';

      var icon = document.createElement('i');
      icon.className = 'ri-price-tag-3-line';
      badge.appendChild(icon);

      var text = document.createTextNode(' ' + tag);
      badge.appendChild(text);

      container.appendChild(badge);
    });
  }

  function renderMetrics(metrics) {
    var container = document.getElementById('vet-prescription-metrics');

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
      card.className = 'vet-prescricoes__metric-card h-100';

      var wrapper = document.createElement('div');
      wrapper.className = 'd-flex align-items-center gap-3';

      var iconWrapper = document.createElement('span');
      iconWrapper.className = 'vet-prescricoes__metric-icon';

      var icon = document.createElement('i');
      icon.className = metric.icon || '';
      iconWrapper.appendChild(icon);

      var info = document.createElement('div');

      var label = document.createElement('p');
      label.className = 'text-muted small mb-1';
      label.textContent = metric.label || '';

      var value = document.createElement('h6');
      value.className = 'text-color fw-semibold mb-0';
      value.textContent = metric.value != null ? metric.value : '';

      info.appendChild(label);
      info.appendChild(value);

      wrapper.appendChild(iconWrapper);
      wrapper.appendChild(info);
      card.appendChild(wrapper);
      col.appendChild(card);
      container.appendChild(col);
    });
  }

  function renderMedications(medications) {
    var container = document.getElementById('vet-prescription-medications');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(medications) || medications.length === 0) {
      var alert = document.createElement('div');
      alert.className = 'alert alert-light mb-0';
      alert.textContent = 'Nenhum medicamento cadastrado.';
      container.appendChild(alert);
      return;
    }

    medications.forEach(function (medication) {
      var card = document.createElement('div');
      card.className = 'vet-prescricoes__medication-card';

      var header = document.createElement('div');
      header.className = 'd-flex flex-wrap align-items-center justify-content-between gap-2';

      var titleWrapper = document.createElement('div');

      var title = document.createElement('h6');
      title.className = 'text-color fw-semibold mb-1';
      title.textContent = medication.name || '';

      var subtitle = document.createElement('p');
      subtitle.className = 'text-muted small mb-0';
      subtitle.textContent = [(medication.dosage || ''), (medication.frequency || '')].filter(Boolean).join(' • ');

      titleWrapper.appendChild(title);
      titleWrapper.appendChild(subtitle);

      var badge = document.createElement('span');
      badge.className = 'badge bg-primary-subtle text-primary';
      badge.textContent = medication.duration || '';

      header.appendChild(titleWrapper);
      header.appendChild(badge);

      var schedule = document.createElement('div');
      schedule.className = 'd-flex flex-wrap gap-3 mt-3';

      var start = document.createElement('span');
      start.className = 'text-muted small';
      start.innerHTML = '<i class="ri-play-circle-line me-1"></i>' + (medication.start_at || '');

      var end = document.createElement('span');
      end.className = 'text-muted small';
      end.innerHTML = '<i class="ri-stop-circle-line me-1"></i>' + (medication.end_at || '');

      schedule.appendChild(start);
      schedule.appendChild(end);

      var notes = document.createElement('p');
      notes.className = 'text-muted small mb-0 mt-3';
      notes.textContent = medication.notes || '';

      card.appendChild(header);
      card.appendChild(schedule);
      card.appendChild(notes);
      container.appendChild(card);
    });
  }

  function renderList(containerId, items, options) {
    var container = document.getElementById(containerId);

    if (!container) {
      return;
    }

    container.innerHTML = '';

    var hasItems = Array.isArray(items) && items.length > 0;

    if (!hasItems) {
      var empty = document.createElement(container.tagName === 'UL' ? 'li' : 'span');
      empty.className = container.tagName === 'UL' ? 'list-group-item px-0 text-muted' : 'text-muted small';
      empty.textContent = options && options.emptyText ? options.emptyText : 'Nenhum registro disponível.';
      container.appendChild(empty);
      return;
    }

    items.forEach(function (item) {
      var element;

      if (container.tagName === 'UL') {
        element = document.createElement('li');
        element.className = options && options.itemClass ? options.itemClass : 'list-group-item px-0';
      } else {
        element = document.createElement('div');
        element.className = options && options.itemClass ? options.itemClass : '';
      }

      if (options && options.prefixIcon) {
        element.innerHTML = '<i class="' + options.prefixIcon + '"></i>' + (options.iconSpacing || ' ') + item;
      } else {
        element.textContent = item;
      }

      container.appendChild(element);
    });
  }

  function renderChecklist(items) {
    var container = document.getElementById('vet-prescription-checklist');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(items) || items.length === 0) {
      var empty = document.createElement('span');
      empty.className = 'text-muted small';
      empty.textContent = 'Checklist ainda não iniciado.';
      container.appendChild(empty);
      return;
    }

    items.forEach(function (item) {
      var wrapper = document.createElement('div');
      wrapper.className = 'd-flex align-items-center gap-3';

      var icon = document.createElement('span');
      icon.className = 'vet-prescricoes__check-icon ' + (item.checked ? 'bg-success-subtle text-success' : 'bg-light text-muted');

      var iconInner = document.createElement('i');
      iconInner.className = item.checked ? 'ri-check-line' : 'ri-checkbox-blank-line';
      icon.appendChild(iconInner);

      var label = document.createElement('span');
      label.className = 'text-color';
      label.textContent = item.label || '';

      wrapper.appendChild(icon);
      wrapper.appendChild(label);
      container.appendChild(wrapper);
    });
  }

  function renderTimeline(events) {
    var container = document.getElementById('vet-prescription-timeline');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(events) || events.length === 0) {
      var empty = document.createElement('p');
      empty.className = 'text-muted small mb-0';
      empty.textContent = 'Nenhum evento registrado.';
      container.appendChild(empty);
      return;
    }

    var wrapper = document.createElement('div');
    wrapper.className = 'vet-prescricoes__timeline position-relative d-flex flex-column gap-3';

    events.forEach(function (event) {
      var item = document.createElement('div');
      item.className = 'vet-prescricoes__timeline-item';

      var time = document.createElement('span');
      time.className = 'vet-prescricoes__timeline-time';
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
      wrapper.appendChild(item);
    });

    container.appendChild(wrapper);
  }

  function renderServices(services) {
    var container = document.getElementById('vet-prescription-services');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(services) || services.length === 0) {
      var empty = document.createElement('span');
      empty.className = 'text-muted small';
      empty.textContent = 'Sem serviços vinculados.';
      container.appendChild(empty);
      return;
    }

    services.forEach(function (service) {
      var item = document.createElement('div');
      item.className = 'd-flex justify-content-between align-items-center p-2 border rounded-3';

      var label = document.createElement('span');
      label.className = 'text-color';
      label.textContent = service.label || '';

      var date = document.createElement('span');
      date.className = 'text-muted small';
      date.textContent = service.date || '';

      item.appendChild(label);
      item.appendChild(date);
      container.appendChild(item);
    });
  }

  function renderAlerts(alerts) {
    var container = document.getElementById('vet-prescription-alerts');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(alerts) || alerts.length === 0) {
      var empty = document.createElement('span');
      empty.className = 'text-muted small';
      empty.textContent = 'Nenhum alerta específico.';
      container.appendChild(empty);
      return;
    }

    alerts.forEach(function (alert) {
      var wrapper = document.createElement('div');
      wrapper.className = 'p-3 border rounded-3 d-flex align-items-start gap-2';

      var icon = document.createElement('i');
      var type = typeof alert.type === 'string' ? alert.type : 'primary';
      var normalized = ['warning', 'danger', 'success', 'primary', 'info'].indexOf(type) !== -1 ? type : 'primary';
      icon.className = 'ri-alert-line text-' + normalized + ' mt-1';

      var content = document.createElement('div');

      var title = document.createElement('h6');
      title.className = 'text-color fw-semibold mb-1';
      title.textContent = alert.title || '';

      var description = document.createElement('p');
      description.className = 'text-muted small mb-0';
      description.textContent = alert.description || '';

      content.appendChild(title);
      content.appendChild(description);

      wrapper.appendChild(icon);
      wrapper.appendChild(content);
      container.appendChild(wrapper);
    });
  }

  function selectPrescription(index) {
    var prescriptions = getPrescriptions();
    var current = prescriptions[toNumber(index)];

    if (!current) {
      return;
    }

    var targetRow = document.querySelector('[data-prescription-index="' + index + '"]');
    updateActiveRow(targetRow);

    updateBadge('vet-prescription-status', 'vet-prescricoes__badge-soft-', current.status_color, current.status);
    updateBadge('vet-prescription-priority', 'vet-prescricoes__badge-outline-', current.priority_color, current.priority);

    setText('vet-prescription-updated', current.updated_at, '--/--/---- --:--');
    setText('vet-prescription-patient', current.patient, 'Paciente não selecionado');
    setText('vet-prescription-overview', current.summary, 'Selecione uma prescrição para visualizar os detalhes clínicos.');
    setText('vet-prescription-code', current.code);
    setText('vet-prescription-veterinarian', current.veterinarian);
    setText('vet-prescription-created-at', current.created_at);
    setText('vet-prescription-valid-until', current.valid_until);
    setText('vet-prescription-next-revalidation', current.next_revalidation);
    setText('vet-prescription-tutor', current.tutor);

    renderTags(current.tags);
    renderMetrics(current.metrics);
    renderMedications(current.medications);
    renderList('vet-prescription-instructions', current.instructions, {
      emptyText: 'Nenhuma orientação registrada.',
      prefixIcon: 'ri-check-double-line text-primary me-2',
      iconSpacing: ' ',
      itemClass: 'list-group-item px-0'
    });
    renderList('vet-prescription-safety', current.safety_notes, {
      emptyText: 'Nenhum alerta de segurança.',
      prefixIcon: 'ri-alert-line text-warning me-2',
      iconSpacing: ' ',
      itemClass: 'list-group-item px-0 text-muted'
    });
    renderList('vet-prescription-pending', current.pending_actions, {
      emptyText: 'Sem pendências cadastradas.',
      prefixIcon: 'ri-arrow-right-line text-primary me-2',
      iconSpacing: ' ',
      itemClass: 'list-group-item px-0'
    });
    renderChecklist(current.checklist);
    renderTimeline(current.timeline);
    renderServices(current.related_services);
    renderAlerts(current.alerts);
  }

  document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('[data-prescription-index]');

    rows.forEach(function (row) {
      row.addEventListener('click', function () {
        var index = this.getAttribute('data-prescription-index');
        selectPrescription(index);
      });
    });

    if (rows.length > 0) {
      selectPrescription(rows[0].getAttribute('data-prescription-index'));
    }
  });
})();