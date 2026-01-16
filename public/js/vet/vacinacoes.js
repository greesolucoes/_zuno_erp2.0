(function () {
  'use strict';

  function getVaccinations() {
    if (!Array.isArray(window.vetVaccinationsData)) {
      return [];
    }

    return window.vetVaccinationsData;
  }

  function setText(id, value, fallback) {
    var element = document.getElementById(id);

    if (!element) {
      return;
    }

    element.textContent = value != null && value !== '' ? value : (fallback || '—');
  }

  function renderTutorInfo(vaccination) {
    var element = document.getElementById('vet-vaccination-tutor');

    if (!element) {
      return;
    }

    if (!vaccination) {
      element.textContent = 'Escolha uma vacinação na lista ao lado.';
      return;
    }

    var parts = [];

    if (vaccination.species) {
      parts.push(vaccination.species);
    }

    if (vaccination.breed) {
      parts.push(vaccination.breed);
    }

    if (vaccination.tutor) {
      parts.push('Tutor: ' + vaccination.tutor);
    }

    element.textContent = parts.length > 0 ? parts.join(' • ') : 'Sem informações complementares.';
  }

  function renderTags(tags) {
    var container = document.getElementById('vet-vaccination-tags');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(tags) || tags.length === 0) {
      var placeholder = document.createElement('span');
      placeholder.className = 'text-muted small';
      placeholder.textContent = 'Sem etiquetas cadastradas.';
      container.appendChild(placeholder);
      return;
    }

    tags.forEach(function (tag) {
      var badge = document.createElement('span');
      badge.className = 'vet-vacinacoes__tag';

      var icon = document.createElement('i');
      icon.className = 'ri-price-tag-3-line';

      badge.appendChild(icon);
      badge.appendChild(document.createTextNode(tag));
      container.appendChild(badge);
    });
  }

  function renderList(containerId, items, emptyMessage) {
    var container = document.getElementById(containerId);

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(items) || items.length === 0) {
      var placeholder = document.createElement(container.tagName === 'UL' ? 'li' : 'div');
      placeholder.className = container.tagName === 'UL' ? 'list-group-item px-0 text-muted' : 'text-muted small';
      placeholder.textContent = emptyMessage || 'Nenhum registro disponível.';
      container.appendChild(placeholder);
      return;
    }

    items.forEach(function (item) {
      var element = document.createElement(container.tagName === 'UL' ? 'li' : 'div');

      if (container.tagName === 'UL') {
        element.className = 'list-group-item px-0';
        element.textContent = item;
      } else {
        element.className = 'text-color small';
        element.textContent = item;
      }

      container.appendChild(element);
    });
  }

  function renderReminders(reminders) {
    renderList('vet-vaccination-reminders', reminders, 'Nenhum lembrete configurado.');
  }

  function renderFollowUp(steps) {
    renderList('vet-vaccination-follow-up', steps, 'Nenhum próximo passo definido.');
  }

  function renderChecklist(checklist) {
    var container = document.getElementById('vet-vaccination-checklist');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(checklist) || checklist.length === 0) {
      var placeholder = document.createElement('span');
      placeholder.className = 'text-muted small';
      placeholder.textContent = 'Checklist ainda não iniciado.';
      container.appendChild(placeholder);
      return;
    }

    checklist.forEach(function (item) {
      var row = document.createElement('div');
      row.className = 'd-flex align-items-center gap-3';

      var iconWrapper = document.createElement('span');
      iconWrapper.className = 'vet-vacinacoes__check-icon ' + (item.checked ? 'bg-success-subtle text-success' : 'bg-light text-muted');

      var icon = document.createElement('i');
      icon.className = item.checked ? 'ri-check-line' : 'ri-checkbox-blank-line';
      iconWrapper.appendChild(icon);

      var label = document.createElement('span');
      label.className = 'text-color small';
      label.textContent = item.label || '';

      row.appendChild(iconWrapper);
      row.appendChild(label);

      container.appendChild(row);
    });
  }

  function renderDocuments(documents) {
    var container = document.getElementById('vet-vaccination-documents');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(documents) || documents.length === 0) {
      var placeholder = document.createElement('span');
      placeholder.className = 'text-muted small';
      placeholder.textContent = 'Nenhum documento anexado.';
      container.appendChild(placeholder);
      return;
    }

    documents.forEach(function (documentItem) {
      var wrapper = document.createElement('div');
      wrapper.className = 'd-flex justify-content-between align-items-center border rounded-3 px-3 py-2';

      var info = document.createElement('div');

      var title = document.createElement('h6');
      title.className = 'text-color fw-semibold mb-0';
      title.textContent = documentItem.label || '';

      var subtitle = document.createElement('p');
      subtitle.className = 'text-muted small mb-0';
      subtitle.textContent = (documentItem.type || '') + (documentItem.date ? ' • ' + documentItem.date : '');

      info.appendChild(title);
      info.appendChild(subtitle);

      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'btn btn-light btn-sm';
      button.innerHTML = '<i class="ri-download-2-line"></i>';

      wrapper.appendChild(info);
      wrapper.appendChild(button);

      container.appendChild(wrapper);
    });
  }

  function renderTimeline(timeline) {
    var container = document.getElementById('vet-vaccination-timeline');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(timeline) || timeline.length === 0) {
      var placeholder = document.createElement('p');
      placeholder.className = 'text-muted small mb-0';
      placeholder.textContent = 'Nenhum evento registrado.';
      container.appendChild(placeholder);
      return;
    }

    var wrapper = document.createElement('div');
    wrapper.className = 'vet-vacinacoes__timeline position-relative d-flex flex-column gap-3';

    timeline.forEach(function (event) {
      var item = document.createElement('div');
      item.className = 'vet-vacinacoes__timeline-item';

      var date = document.createElement('span');
      date.className = 'vet-vacinacoes__timeline-date';
      date.textContent = event.date || '';

      var title = document.createElement('h6');
      title.className = 'text-color fw-semibold mb-1';
      title.textContent = event.title || '';

      var description = document.createElement('p');
      description.className = 'text-muted small mb-0';
      description.textContent = event.description || '';

      item.appendChild(date);
      item.appendChild(title);
      item.appendChild(description);

      wrapper.appendChild(item);
    });

    container.appendChild(wrapper);
  }

  function renderAlerts(alerts) {
    var container = document.getElementById('vet-vaccination-alerts');

    if (!container) {
      return;
    }

    container.innerHTML = '';

    if (!Array.isArray(alerts) || alerts.length === 0) {
      var placeholder = document.createElement('span');
      placeholder.className = 'text-muted small';
      placeholder.textContent = 'Nenhum alerta específico.';
      container.appendChild(placeholder);
      return;
    }

    alerts.forEach(function (alert) {
      var wrapper = document.createElement('div');
      wrapper.className = 'border rounded-3 p-3 d-flex align-items-start gap-2';

      var icon = document.createElement('i');
      var type = alert.type === 'danger' ? 'danger' : (alert.type === 'warning' ? 'warning' : 'info');
      icon.className = 'ri-alert-line text-' + type + ' mt-1';

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

  function renderInventory(inventory) {
    setText('vet-vaccination-stock', inventory && inventory.stock_available != null ? inventory.stock_available : '—');
    setText('vet-vaccination-reserved', inventory && inventory.reserved_doses != null ? inventory.reserved_doses : '—');
    setText('vet-vaccination-wastage', inventory && inventory.wastage != null ? inventory.wastage : '—');
    setText('vet-vaccination-cold-chain', inventory && inventory.temperature_monitoring ? inventory.temperature_monitoring : '—');
  }

  function updateStatusBadge(vaccination) {
    var badge = document.getElementById('vet-vaccination-status');

    if (!badge) {
      return;
    }

    var color = vaccination && vaccination.status_color ? vaccination.status_color : 'muted';
    var text = vaccination && vaccination.status ? vaccination.status : 'Aguardando seleção';

    badge.textContent = text;
    if (color === 'muted') {
      badge.className = 'vet-vacinacoes__status-badge bg-light text-muted';
    } else {
      badge.className = 'vet-vacinacoes__status-badge bg-' + color + '-subtle text-' + color;
    }
  }

  function updateActiveRow(target) {
    document.querySelectorAll('[data-vaccination-index]').forEach(function (row) {
      row.classList.remove('active');
    });

    if (target) {
      target.classList.add('active');
    }
  }

  function applyVaccination(index) {
    var vaccinations = getVaccinations();
    var vaccination = vaccinations[index];

    updateActiveRow(document.querySelector('[data-vaccination-index="' + index + '"]'));

    if (!vaccination) {
      setText('vet-vaccination-code', 'Selecione um registro');
      setText('vet-vaccination-patient', 'Nenhum paciente selecionado');
      renderTutorInfo(null);
      renderTags([]);
      setText('vet-vaccination-next', '—');
      setText('vet-vaccination-name');
      setText('vet-vaccination-manufacturer');
      setText('vet-vaccination-lot');
      setText('vet-vaccination-valid');
      setText('vet-vaccination-dose');
      setText('vet-vaccination-route');
      setText('vet-vaccination-site');
      setText('vet-vaccination-scheduled');
      setText('vet-vaccination-last');
      setText('vet-vaccination-veterinarian');
      setText('vet-vaccination-room');
      setText('vet-vaccination-observations', 'Sem observações adicionais.');
      renderReminders([]);
      renderChecklist([]);
      renderDocuments([]);
      renderTimeline([]);
      renderAlerts([]);
      renderFollowUp([]);
      renderInventory(null);
      updateStatusBadge(null);
      return;
    }

    setText('vet-vaccination-code', vaccination.code, 'Selecione um registro');
    setText('vet-vaccination-patient', vaccination.patient, 'Nenhum paciente selecionado');
    renderTutorInfo(vaccination);
    renderTags(vaccination.tags);
    setText('vet-vaccination-next', vaccination.next_due, '—');

    if (vaccination.vaccine) {
      setText('vet-vaccination-name', vaccination.vaccine.name);
      setText('vet-vaccination-manufacturer', vaccination.vaccine.manufacturer);
      setText('vet-vaccination-lot', vaccination.vaccine.lot);
      setText('vet-vaccination-valid', vaccination.vaccine.valid_until);
      setText('vet-vaccination-dose', vaccination.vaccine.dose);
      setText('vet-vaccination-route', vaccination.vaccine.route);
      setText('vet-vaccination-site', vaccination.vaccine.site);
    } else {
      setText('vet-vaccination-name');
      setText('vet-vaccination-manufacturer');
      setText('vet-vaccination-lot');
      setText('vet-vaccination-valid');
      setText('vet-vaccination-dose');
      setText('vet-vaccination-route');
      setText('vet-vaccination-site');
    }

    setText('vet-vaccination-scheduled', vaccination.scheduled_at);
    setText('vet-vaccination-last', vaccination.last_application);
    setText('vet-vaccination-veterinarian', vaccination.veterinarian);
    setText('vet-vaccination-room', vaccination.clinic_room);

    setText('vet-vaccination-observations', vaccination.observations || 'Sem observações adicionais.');

    renderReminders(vaccination.reminders);
    renderChecklist(vaccination.checklist);
    renderDocuments(vaccination.documents);
    renderTimeline(vaccination.timeline);
    renderAlerts(vaccination.alerts);
    renderFollowUp(vaccination.follow_up);
    renderInventory(vaccination.inventory);
    updateStatusBadge(vaccination);
  }

  document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('[data-vaccination-index]');

    rows.forEach(function (row) {
      row.addEventListener('click', function () {
        var index = Number(row.getAttribute('data-vaccination-index'));
        applyVaccination(index);
      });
    });

    if (rows.length > 0) {
      applyVaccination(Number(rows[0].getAttribute('data-vaccination-index')));
    }
  });
})();