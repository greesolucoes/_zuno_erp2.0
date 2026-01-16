(function () {
  'use strict';

  function parseEvents(calendarEl) {
    if (!calendarEl) {
      return [];
    }

    var raw = calendarEl.getAttribute('data-events') || '[]';
    var parsed;

    try {
      parsed = JSON.parse(raw);
    } catch (error) {
      console.warn('Não foi possível interpretar os eventos da agenda veterinária.', error);
      return [];
    }

    if (!Array.isArray(parsed)) {
      return [];
    }

    return parsed.map(function (event, index) {
      var start = event.start || event.date || null;
      var end = event.end || null;
      var accentColor = event.color || '#556ee6';

      if (!start) {
        start = dayjs().startOf('day').toISOString();
      }

      if (!end && start) {
        try {
          end = dayjs(start).add(30, 'minute').toISOString();
        } catch (error) {
          end = null;
        }
      }

      var extendedProps = Object.assign({}, event, {
        accentColor: accentColor,
      });

      return {
        id: event.id || index + 1,
        title: event.patient || event.title || 'Consulta',
        start: start,
        end: end,
        backgroundColor: '#ffffff',
        borderColor: 'rgba(22, 22, 107, 0.08)',
        textColor: '#1f2633',
        classNames: ['vet-calendar__event'],
        extendedProps: extendedProps,
      };
    });
  }

  function toggleEmptyState(calendarEl, emptyEl, hasEvents) {
    if (!emptyEl) {
      return;
    }

    if (hasEvents) {
      emptyEl.classList.add('d-none');
    } else {
      emptyEl.classList.remove('d-none');
    }
  }

  function capitalize(str) {
    if (typeof str !== 'string' || !str.length) {
      return '';
    }

    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function formatEventTime(info) {
    if (!info.event) {
      return '';
    }

    var start = info.event.start ? dayjs(info.event.start) : null;
    var end = info.event.end ? dayjs(info.event.end) : null;

    if (!start) {
      return '';
    }

    var formatted = start.format('HH:mm');

    if (end) {
      formatted += ' - ' + end.format('HH:mm');
    }

    return formatted;
  }

  function buildEventHtml(info) {
    var details = info.event.extendedProps || {};
    var patient = details.patient || info.event.title || 'Consulta';
    var service = details.service || '';
    var veterinarian = details.veterinarian || '';
    var timeText = info.timeText || formatEventTime(info);
    var accentColor = details.accentColor || details.color || '#556ee6';

    var html = '';
    html += '<div class="vet-calendar-event__body">';
    html += '  <div class="vet-calendar-event__header">';
    html += '    <div class="d-flex align-items-center gap-2">';
    html += '      <span class="vet-calendar-event__dot" style="background:' + accentColor + '"></span>';
    html += '      <span class="vet-calendar-event__title">' + patient + '</span>';
    html += '    </div>';
    if (timeText) {
      html += '    <span class="vet-calendar-event__meta">' + timeText + '</span>';
    }
    html += '  </div>';
    if (service) {
      html += '  <div class="vet-calendar-event__meta">' + service + '</div>';
    }
    if (veterinarian) {
      html += '  <div class="vet-calendar-event__meta"><i class="ri-stethoscope-line me-1"></i>' + veterinarian + '</div>';
    }
    html += '</div>';

    return { html: html };
  }

  function createTooltip(info) {
    if (!window.bootstrap || !window.bootstrap.Tooltip) {
      return;
    }

    var details = info.event.extendedProps || {};
    var accentColor = details.accentColor || details.color || '#556ee6';
    var start = info.event.start ? dayjs(info.event.start).format('DD/MM [às] HH:mm') : '—';
    var end = info.event.end ? dayjs(info.event.end).format('HH:mm') : null;
    var timeRange = end ? start + ' - ' + end : start;

    var content = '';
    content += '<div class="text-start">';
    content += '  <div class="d-flex align-items-center gap-2 mb-2">';
    content += '    <span class="vet-agenda__timeline-dot" style="background:' + accentColor + '"></span>';
    content += '    <span class="fw-semibold">' + (details.patient || info.event.title || 'Consulta') + '</span>';
    content += '  </div>';
    if (timeRange) {
      content += '  <div class="text-muted small"><i class="ri-time-line me-1"></i>' + timeRange + '</div>';
    }
    if (details.service) {
      content += '  <div class="text-muted small"><i class="ri-hearts-line me-1"></i>' + details.service + '</div>';
    }
    if (details.veterinarian) {
      content += '  <div class="text-muted small"><i class="ri-stethoscope-line me-1"></i>' + details.veterinarian + '</div>';
    }
    if (details.room) {
      content += '  <div class="text-muted small"><i class="ri-map-pin-line me-1"></i>' + details.room + '</div>';
    }
    if (details.tutor) {
      content += '  <div class="text-muted small"><i class="ri-user-heart-line me-1"></i>' + details.tutor;
      if (details.tutor_contact) {
        content += ' • ' + details.tutor_contact;
      }
      content += '</div>';
    }
    content += '</div>';

    info.el.setAttribute('data-bs-toggle', 'tooltip');
    info.el.setAttribute('data-bs-html', 'true');
    info.el.setAttribute('data-bs-placement', 'auto');

    var tooltip = new bootstrap.Tooltip(info.el, {
      title: content,
      html: true,
      container: 'body',
      trigger: 'hover focus',
    });

    info.el.addEventListener('hidden.bs.tooltip', function () {
      tooltip.dispose();
    });
  }

  function updateSidebarDetails(info) {
    var highlight = document.getElementById('vet-appointments-highlight');
    if (!highlight || !info) {
      return;
    }

    var details = info.event.extendedProps || {};
    var accentColor = details.accentColor || details.color || '#556ee6';
    var range = details.start_human;

    if (!range && info.event.start) {
      var start = dayjs(info.event.start).format('DD/MM [às] HH:mm');
      var end = info.event.end ? dayjs(info.event.end).format('HH:mm') : null;
      range = end ? start + ' - ' + end : start;
    }

    var tutorRow = '';
    if (details.tutor) {
      tutorRow += '<p class="mb-0 text-muted small"><i class="ri-user-heart-line me-1"></i>' + details.tutor;
      if (details.tutor_contact) {
        tutorRow += ' • ' + details.tutor_contact;
      }
      tutorRow += '</p>';
    }

    var notesRow = details.notes ? '<p class="mb-0 text-muted small mt-2">' + details.notes + '</p>' : '';

    var roomRow = details.room ? '<p class="mb-0 text-muted small"><i class="ri-map-pin-line me-1"></i>' + details.room + '</p>' : '';

    var veterinarianRow = details.veterinarian
      ? '<p class="mb-0 text-muted small"><i class="ri-stethoscope-line me-1"></i>' + details.veterinarian + '</p>'
      : '';

    var serviceRow = details.service
      ? '<p class="mb-0 text-muted small"><i class="ri-hearts-line me-1"></i>' + details.service + '</p>'
      : '';

    var html = '';
    html += '<div class="d-flex align-items-center justify-content-between mb-2">';
    html += '  <div class="d-flex align-items-center gap-2">';
    html += '    <span class="vet-agenda__timeline-dot" style="background:' + accentColor + ';"></span>';
    if (range) {
      html += '    <span class="fw-semibold text-color">' + range + '</span>';
    }
    html += '  </div>';
    html += '  <span class="badge bg-primary-subtle text-primary">' + (details.status || 'Agendado') + '</span>';
    html += '</div>';
    html += '<h6 class="text-color fw-semibold mb-2">' + (details.patient || info.event.title || 'Consulta') + '</h6>';
    html += serviceRow;
    html += veterinarianRow;
    html += roomRow;
    html += tutorRow;
    html += notesRow;

    highlight.innerHTML = html;
    highlight.classList.remove('d-none');
  }

  function bindShortcutButtons(calendar) {
    document.querySelectorAll('[data-agenda-action]').forEach(function (button) {
      button.addEventListener('click', function () {
        var action = button.getAttribute('data-agenda-action');

        if (action === 'week') {
          calendar.incrementDate({ weeks: 1 });
        }

        if (action === 'month') {
          calendar.incrementDate({ months: 1 });
        }
      });
    });
  }

  function bindNavigation(calendar) {
    document.querySelectorAll('[data-calendar-nav]').forEach(function (button) {
      button.addEventListener('click', function () {
        var action = button.getAttribute('data-calendar-nav');

        if (action === 'prev') {
          calendar.prev();
        }

        if (action === 'next') {
          calendar.next();
        }
      });
    });
  }

  function setActiveViewButton(viewName) {
    document.querySelectorAll('[data-calendar-view]').forEach(function (button) {
      var view = button.getAttribute('data-calendar-view');
      if (view === viewName) {
        button.classList.add('active');
      } else {
        button.classList.remove('active');
      }
    });
  }

  function bindViewButtons(calendar) {
    document.querySelectorAll('[data-calendar-view]').forEach(function (button) {
      button.addEventListener('click', function () {
        var view = button.getAttribute('data-calendar-view');
        if (!view) {
          return;
        }

        calendar.changeView(view);
        setActiveViewButton(view);
      });
    });
  }

  function updateToolbar(calendar) {
    var rangeLabel = document.getElementById('vet-calendar-range');
    var summaryLabel = document.getElementById('vet-calendar-summary');

    if (!calendar) {
      return;
    }

    var view = calendar.view;
    var rangeStart = dayjs(view.activeStart);
    var rangeEnd = dayjs(view.activeEnd).subtract(1, 'minute');

    var rangeText;
    if (view.type === 'dayGridMonth') {
      rangeText = rangeStart.format('MMMM [de] YYYY');
    } else if (view.type === 'timeGridDay') {
      rangeText = rangeStart.format('dddd[, ]DD [de] MMMM');
    } else if (view.type === 'listWeek') {
      rangeText = rangeStart.format('DD MMM') + ' — ' + rangeEnd.format('DD MMM YYYY');
    } else {
      rangeText = rangeStart.format('DD MMM') + ' — ' + rangeEnd.format('DD MMM YYYY');
    }

    if (rangeLabel) {
      rangeLabel.textContent = capitalize(rangeText);
    }

    if (!summaryLabel) {
      return;
    }

    var events = calendar.getEvents();
    var eventsInRange = events.filter(function (event) {
      if (!event.start) {
        return false;
      }

      var time = dayjs(event.start).valueOf();
      return time >= rangeStart.valueOf() && time <= rangeEnd.valueOf();
    });

    if (!eventsInRange.length) {
      summaryLabel.textContent = 'Sem atendimentos neste período.';
      return;
    }

    var count = eventsInRange.length;
    summaryLabel.textContent = count === 1
      ? '1 atendimento agendado para este período.'
      : count + ' atendimentos agendados para este período.';
  }

  function initCalendar() {
    var calendarEl = document.getElementById('vet-calendar');

    if (!calendarEl || typeof FullCalendar === 'undefined') {
      return;
    }

    dayjs.locale('pt-br');

    var events = parseEvents(calendarEl);
    var emptyState = document.getElementById('vet-calendar-empty');

    toggleEmptyState(calendarEl, emptyState, events.length > 0);

    var calendar = new FullCalendar.Calendar(calendarEl, {
      locale: 'pt-br',
      initialView: 'timeGridWeek',
      height: 'auto',
      contentHeight: 'auto',
      expandRows: true,
      nowIndicator: true,
      headerToolbar: false,
      eventDisplay: 'block',
      slotDuration: '00:30:00',
      slotMinTime: '07:00:00',
      slotMaxTime: '21:00:00',
      slotLabelFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false,
      },
      eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false,
      },
      dayMaxEventRows: 4,
      moreLinkText: 'mais',
      events: events,
      eventContent: buildEventHtml,
      eventDidMount: function (info) {
        var details = info.event.extendedProps || {};
        var accentColor = details.accentColor || details.color || '#556ee6';
        info.el.style.setProperty('--vet-event-color', accentColor);
        info.el.style.borderLeft = '4px solid ' + accentColor;
        createTooltip(info);
      },
      eventClick: function (info) {
        updateSidebarDetails(info);
      },
    });

    calendar.render();

    updateToolbar(calendar);
    setActiveViewButton(calendar.view.type);

    var initialEvent = calendar.getEvents()[0];
    if (initialEvent) {
      updateSidebarDetails({ event: initialEvent });
    }

    calendar.on('datesSet', function () {
      updateToolbar(calendar);
      setActiveViewButton(calendar.view.type);
    });

    var todayButton = document.getElementById('vet-agenda-today');
    if (todayButton) {
      todayButton.addEventListener('click', function () {
        calendar.today();
      });
    }

    bindShortcutButtons(calendar);
    bindNavigation(calendar);
    bindViewButtons(calendar);
  }

  document.addEventListener('DOMContentLoaded', initCalendar);
})();