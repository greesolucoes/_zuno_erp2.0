var DATACHANGE = null;
var SERVICOS = [];
!(function (l) {
  "use strict";

  let agendamentos = JSON.parse($("#agendamentos").val());

  function e() {
    (this.$body = l("body")),
      (this.$modal = new bootstrap.Modal(
        document.getElementById("event-modal"),
        {
          backdrop: "static",
        }
      )),
      (this.$calendar = l("#calendar")),
      (this.$formEvent = l("#form-event")),
      (this.$btnNewEvent = l("#btn-new-event")),
      (this.$btnDeleteEvent = l("#btn-delete-event")),
      (this.$btnSaveEvent = l("#btn-save-event")),
      (this.$modalTitle = l("#modal-title")),
      (this.$calendarObj = null),
      (this.$selectedEvent = null),
      (this.$newEventData = null);
  }
  (e.prototype.onEventClick = function (e) {
    this.$formEvent[0].reset(),
      this.$formEvent.removeClass("was-validated"),
      (this.$newEventData = null),
      this.$btnDeleteEvent.show(),
      this.$modalTitle.text("Edit Event"),
      (this.$selectedEvent = e.event),
      l("#event-title").val(this.$selectedEvent.title),
      l("#event-category").val(this.$selectedEvent.classNames[0]);
  }),
    (e.prototype.init = function () {
      var e = new Date(l.now()),
        e =
          (new FullCalendar.Draggable(
            document.getElementById("external-events"),
            {
              itemSelector: ".external-event",
              eventData: function (e) {
                return {
                };
              },
            }
          ),
            agendamentos),
        a = this;
      (a.$calendarObj = new FullCalendar.Calendar(a.$calendar[0], {
        slotDuration: "00:15:00",
        slotMinTime: "08:00:00",
        slotMaxTime: "19:00:00",
        themeSystem: "bootstrap",
        bootstrapFontAwesome: !1,
        locale: "pt-br",
        initialView: "listDay",
        handleWindowResize: false,
        height: 'auto',
        contentHeight: 'auto',
        expandRows: true,
        headerToolbar: {
          left: "prev_btn,next_btn today_btn",
          center: "title",
          right: ''
        },
        customButtons: {
          prev_btn: {
          text: ' ',
            click: function() {
              a.$calendarObj.prev();
            }
          },
          next_btn: {
            text: ' ',
            click: function() {
              a.$calendarObj.next();
            }
          },
          today_btn: {
            text: ' ',
            click: function() {
              a.$calendarObj.today();
            }
          }, 
        },
        initialEvents: e,
        editable: false,
        eventDisplay: 'block',
        displayEventTime: false,
        allDaySlot: false,
        droppable: false,
        selectable: false,
        fixedWeekCount: false,
        slotMinTime: '00:00:00', 
        slotMaxTime: '23:59:00',
        slotLabelFormat: {
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        },
        dateClick: function (e) {
          const selected_service_btn = document.querySelector('.service-btn.selected-service-button');

          const categoria_servico = selected_service_btn ? selected_service_btn.getAttribute('data-categoria') : null;

          if (selected_service_btn && categoria_servico) {
            switch (categoria_servico) {
              case 'HOTEL':
                handleModalNovoHotel(e.dateStr);
                break;
              case 'CRECHE':
                handleModalNovaCreche(e.dateStr);
                break;
              case 'ESTETICA':
                handleModalNovaEstetica();
                break;
            } 
          } else {
            handleModalSelectService(e.dateStr);
          }

          const dataAtual = new Date(e.dateStr); // agora sim é um objeto Date
          const soData = dataAtual.toISOString().slice(0, 10); // "YYYY-MM-DD"
          $('#inp-dataatual').val(soData);
        },
        eventDidMount: function (info) {
          const current_view = a.$calendarObj.view.type;
          
          const root = info.el;        
          root.style.position = root.style.position || 'relative';
          root.style.zIndex = '999';
          root.style.pointerEvents = 'auto';

          const eventEl = root.querySelector('.fc-event-title') ?? root.querySelector('.fc-list-event-title');

          if (eventEl) {
            root.style.borderLeft = info.event.extendedProps.has_plano ? '5px solid #56327A' : '5px solid #f68e38';

            if (info.event.extendedProps.pet) {
              let label_status = '';
              switch (info.event.extendedProps.estado) {
                case 'agendado':
                  label_status = 'AG';
                  break;
                case 'em_andamento':
                  label_status = 'EA';
                  break;
                case 'concluido':
                  label_status = 'CL';
                  break;
                case 'cancelado':
                  label_status = 'CC';
                  break;
                case 'rejeitado':
                  label_status = 'RJ';
                  break;
                case 'pendente_aprovacao':
                  label_status = 'AP';
                  break;
                default:
                  break;
              }

              let status_class = '';
              switch(info.event.extendedProps.estado) {
                case 'agendado':
                  status_class = 'estado-agendado-area';
                  break;
                case 'em_andamento':
                  status_class = 'estado-em-andamento-area';
                  break;
                case 'concluido':
                  status_class = 'estado-concluido-area';
                  break;
                case 'cancelado':
                  status_class = 'estado-cancelado-area';
                  break;
                case 'rejeitado':
                  status_class = 'estado-rejeitado-area';
                  break;
                case 'pendente_aprovacao':
                  status_class = 'estado-pendente-aprovacao-area';
                  break;
              }

              let status_icon = '';
              switch(info.event.extendedProps.estado) {
                case 'agendado':
                  status_icon = '<i style="font-size: 20px" class="ri-calendar-event-line"></i>';
                  break;
                case 'em_andamento':
                  status_icon = '<i style="font-size: 20px" class="ri-hourglass-fill"></i>';
                  break;
                case 'concluido':
                  status_icon = '<i style="font-size: 20px" class="ri-calendar-check-fill"></i>';
                  break;
                case 'cancelado':
                  status_icon = '<i style="font-size: 20px" class="ri-close-circle-line"></i>';
                  break;
                case 'rejeitado':
                  status_icon = '<i style="font-size: 20px" class="ri-calendar-close-line"></i>';
                  break;
                case 'pendente_aprovacao':
                  status_icon = '<i style="font-size: 20px" class="ri-timer-fill"></i>';
                  break;
              }

              switch (current_view) {
                case 'listDay':

                  eventEl.innerHTML = 
                  `
                    <div
                      class="text-start text-black row agendamento-container-list agendamento-container"
                      style="border-left: 5px solid ${info.event.extendedProps.has_plano ? '#56327A' : '#f68e38'}"
                      data-id="${info.event.id}"
                      data-tipo-agendamento="${info.event.extendedProps.modulo}"
                    >
                      <div 
                        class="gap-1 day-status
                          ${
                            status_class
                          }
                        "
                      >
                          ${
                            status_icon
                          }                   
                          <span class="text-uppercase fw-semibold">${label_status}</span> 
                      </div>
                      <div class="d-flex align-items-center">
                        <div class="horario-container">
                          <div class="d-flex flex-column align-items-center justify-content-center">
                            ${
                              info.event.extendedProps.data_entrada != info.event.extendedProps.data_saida ?
                              info.event.extendedProps.data_entrada :
                              ''
                            }
                            <span class="fw-semibold fs-4">${info.event.extendedProps.horario_entrada}</span>
                          </div>
                          <div class="d-flex flex-column align-items-center justify-content-center">
                            <div 
                              class="connect-circle" 
                              style="background-color: ${info.event.extendedProps.has_plano ? '#56327A' : '#f68e38'}"
                            ></div>
                            <div 
                              class="connect-line" 
                              style="background-color: ${info.event.extendedProps.has_plano ? '#56327A' : '#f68e38'}"
                            ></div>
                            <div 
                              class="connect-circle" 
                              style="background-color: ${info.event.extendedProps.has_plano ? '#56327A' : '#f68e38'}"
                            ></div>
                          </div>
                          <div class="d-flex flex-column align-items-center justify-content-center">
                            ${
                              info.event.extendedProps.data_saida != info.event.extendedProps.data_entrada ?
                              info.event.extendedProps.data_saida :
                              ''
                            }
                            <span class="fw-semibold fs-4">${info.event.extendedProps.horario_saida}</span>
                          </div>
                        </div>
                        <div class="d-flex flex-column gap-1 py-2" style="padding-right: 10px">
                          <div class="pet-info">
                            ${
                              info.event.extendedProps.quarto ?
                              `
                                <div class="mb-2"><b>Quarto:</b> ${info.event.extendedProps.quarto}</div>
                              ` : 
                              ''
                            }
                            ${
                              info.event.extendedProps.turma ?
                              `
                                <div class="mb-2"><b>Turma:</b> ${info.event.extendedProps.turma}</div>
                              ` : 
                              ''
                            }
                            <div>
                              <b>Pet:</b>
                              ${info.event.extendedProps.pet.nome}
                            </div>
                            <div>
                              <b>Raça:</b>
                              ${info.event.extendedProps.pet.raca ?? '--'}
                            </div>
                            <div>
                              <b>Pelagem:</b>
                              ${info.event.extendedProps.pet.pelagem ?? '--'}
                            </div>
                            <div>
                              <b>Porte:</b>
                              ${info.event.extendedProps.pet.porte ?? '--'} 
                            </div>
                          </div>
                          <div class="d-flex align-items-center justify-content-between gap-1">
                            <div class="d-flex gap-1 align-items-end" style="max-width: 150px">
                              <b>Cliente:</b>
                              <small class="text-truncate d-block">${info.event.extendedProps.cliente.razao_social ?? '--'}</small>
                            </div>
                            <div class="d-flex gap-1 align-items-end" style="max-width: 150px">
                              <b>Colaborador:</b>
                              <small class="text-truncate d-block">${info.event.extendedProps.colaborador ?? '--'}</small>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  `;
                  
                  break;
                case 'dayGridMonth':
                  eventEl.closest('.fc-daygrid-event')
                    .setAttribute('data-id', info.event.id);

                  eventEl.closest('.fc-daygrid-event')
                    .setAttribute('data-tipo-agendamento', info.event.extendedProps.modulo);

                  status_class ? root.classList.add(status_class) : '';

                  // const day_element = root.closest('.fc-daygrid-day');

                  // if (day_element) {
                  //   handlePaginationForAgendamentos(day_element);
                  // }


                  eventEl.innerHTML =
                  `
                    <div
                      class="agendamento-container d-flex align-items-center"
                      style="width: 100%; position: relative;"
                      data-id="${info.event.id}"
                      data-tipo-agendamento="${info.event.extendedProps.modulo}"
                    >
                      <div class="gap-1 month-status ${status_class}" style="flex-shrink: 0;">
                        ${status_icon}
                        <span class="text-uppercase fw-semibold">${label_status}</span>
                      </div>

                      <div class="pet-label-center">${info.event.extendedProps.pet.nome}</div>
                    </div>
                  `

                  root.addEventListener('mouseenter', (e) => {
                    const tooltip_data = {
                      turma: info.event.extendedProps.turma ?? null,
                      quarto: info.event.extendedProps.quarto ?? null,
                      pet: info.event.extendedProps.pet,
                      cliente: info.event.extendedProps.cliente,
                      colaborador: info.event.extendedProps.colaborador,
                      data_entrada: info.event.extendedProps.data_entrada,
                      horario_entrada: info.event.extendedProps.horario_entrada,
                      data_saida: info.event.extendedProps.data_saida,
                      horario_saida: info.event.extendedProps.horario_saida,
                      has_plano: info.event.extendedProps.has_plano,
                      status_class,
                      label_status,
                      status_icon,
                    }

                    showTooltipForAgendamento(e, tooltip_data);
                  });

                  root.addEventListener('mouseleave', () => {
                    const tooltip = document.getElementById('custom-tooltip');
                    if (!tooltip) return;

                    tooltip.classList.remove('show');

                    tooltip.addEventListener('transitionend', () => {
                      if (!tooltip.classList.contains('show') && !tooltip.matches(':hover')) {
                        tooltip.remove();
                      }
                    }, { once: true });
                  });

                  const agendamentos_containers =  document.querySelectorAll(
                    `.fc-daygrid-event[data-id="${info.event.id}"][data-tipo-agendamento="${info.event.extendedProps.modulo}"]`
                  );

                  agendamentos_containers.forEach(container => {
                    container.addEventListener('mouseenter', () => {
                      document
                        .querySelectorAll(
                          `[data-id="${info.event.id}"][data-tipo-agendamento="${info.event.extendedProps.modulo}"]`
                        )
                        .forEach(el => el.classList.add('agendamento-hover'));
                    });

                    container.addEventListener('mouseleave', () => {
                      document
                        .querySelectorAll(
                          `[data-id="${info.event.id}"][data-tipo-agendamento="${info.event.extendedProps.modulo}"]`
                        )
                        .forEach(el => el.classList.remove('agendamento-hover'));
                    });
                  });

                  break;
                default:
                  break;
              }
              
            };
          }

          eventEl.querySelectorAll('.agendamento-container').forEach((el) => {
            
          })

          eventEl.addEventListener('mouseout', (e) => {
            e.target.style.scale = '1';
          })



          let delete_btn = root.querySelector('.delete-agenda-btn');
          if (!delete_btn) {
            delete_btn = document.createElement('div');
            delete_btn.className = 'delete-agenda-btn';
            delete_btn.innerHTML = '<i class="ri-close-line"></i>';

            root.appendChild(delete_btn);
          }
        },
        noEventsContent: function() {
          let container = document.createElement('div');
          container.classList.add('without-agendamentos');

          const active_servico = $('.service-btn.selected-service-button').attr('data-categoria');

          container.innerHTML = `
            <img src="/assets/images/svg/sem agendamento pet.svg"/>
            <p class="fw-semibold">
              Sem agendamentos ${active_servico ? 'para ' + active_servico : 'por'} hoje...
            </p>
          `;

          return { domNodes: [container] };
        },
        eventClick: function (e) {
          if ($(e.jsEvent.target).closest('.delete-agenda-btn').length) {
            return;
          }

          const moduleType = e.event.extendedProps.modulo || e.event.modulo;

          if (moduleType === 'VETERINARIO') {
            setHandleModalAtendimentoVeterinario(e.event);
            return;
          }

          setHandleModalAgendamento(e.event);

        },
      })),
        a.$calendarObj.render(),
        a.$btnNewEvent.on("click", function (e) {
          a.onSelect({
            date: new Date(),
            allDay: !0,
          });
        }),
        a.$formEvent.on("submit", function (e) { }),
        l(
          a.$btnDeleteEvent.on("click", function (e) {
            a.$selectedEvent &&
              (a.$selectedEvent.remove(),
                (a.$selectedEvent = null),
                a.$modal.hide());
          })
        );
      $('#event-modal .modal-footer button:contains("Sair")').remove();
      $('#event-modal').on('shown.bs.modal', function () {
        $('#event-modal .modal-header').addClass('bg-dark text-white');
        $('#event-modal .btn-close').addClass('btn-close-white');
      });
    }),
    (l.CalendarApp = new e()),
    (l.CalendarApp.Constructor = e);
})(window.jQuery),
  (function () {
    "use strict";
    window.jQuery.CalendarApp.init();

    setDateHandlersButtons();
    setCalendarViewSelect(window.jQuery.CalendarApp.$calendarObj);
    getAgendamentos();
})();

/**
 * Altera o texto e o ícone dos botões de navegação do calendário
*/
function setDateHandlersButtons () {
  $('.fc-prev_btn-button').empty();
  $('.fc-prev_btn-button').html(`
      <i class="ri-arrow-left-double-line"></i> Anterior
    `).attr('title', 'Anterior');

  $('.fc-next_btn-button').empty();
  $('.fc-next_btn-button').html(`
    Próximo <i class="ri-arrow-right-double-line"></i> 
  `).attr('title', 'Próximo');

  $('.fc-today_btn-button').empty();
  $('.fc-today_btn-button').html(`
    <i class="ri-calendar-todo-fill"></i> Atual
  `).attr('title', 'Atual');
}

/**
 * Adiciona o select de troca de modelo de visualização do calendário
 * 
 * @param {FullCalendar.Calendar} calendar - Instância do calendário para fazer a manipulação da view
*/
function setCalendarViewSelect(calendar) {
  const header_right_block = $('.fc-header-toolbar .fc-toolbar-chunk').last();

  header_right_block.empty();
  header_right_block.addClass('d-flex align-items-center justify-content-end');
  header_right_block.html(`
    <div class="form-group col-md-5 gap-1">
      <label for="fc-view-select" class="me-2 mb-0">Visualização da agenda</label>
      <select id="fc-view-select" class="form-select form-select-sm">
        <option value="listDay">Agendamentos do dia</option>
        <option value="dayGridMonth">Mês</option>
      </select>
    </div>
  `);

  $("#fc-view-select").on("change", function() {
    calendar.changeView(this.value);
  });
}

/**
 *  Mostra um tooltip  com mais informações sobre o agendamento 
 *  para os agendamentos do esquema de visualização mensal
 * 
 * @param {*} mouse_event Evento de mouse que disparou o tooltip
 * @param {*} data Informações do agendamento
 */
function showTooltipForAgendamento(mouse_event, data) {
  let tooltip = document.getElementById('custom-tooltip'); 

  if (!tooltip) {
    tooltip = document.createElement('div');
    tooltip.id = 'custom-tooltip';

    document.body.appendChild(tooltip);
  }

  const offset = 12;
  const tooltip_rect = tooltip.getBoundingClientRect();
  const viewport_width = window.innerWidth;
  const viewport_height = window.innerHeight;

  let top = mouse_event.clientY - tooltip_rect.height - offset;
  let left = mouse_event.clientX + offset;

  if (mouse_event.clientX + tooltip_rect.width + offset > viewport_width) {
    left = mouse_event.clientX - tooltip_rect.width - offset;
  }

  if (mouse_event.clientY - tooltip_rect.height - offset < 0) {
    top = mouse_event.clientY + offset;
  }

  if (mouse_event.clientY + tooltip_rect.height > viewport_height) {
    top = viewport_height - tooltip_rect.height - offset;
  }

  tooltip.style.top = `${top + window.scrollY}px`;
  tooltip.style.left = `${left + window.scrollX}px`;
  tooltip.innerHTML = `
    <div
      class="text-start text-black row agendamento-container-list"
    >
      <div 
        class="gap-1 day-status
          ${
            data.status_class
          }
        "
      >
          ${
            data.status_icon
          }                   
          <span class="text-uppercase fw-semibold">${data.label_status}</span> 
      </div>
      <div class="d-flex align-items-center">
        <div class="horario-container">
          <div class="d-flex flex-column align-items-center justify-content-center">
            ${
              data.data_entrada != data.data_saida ?
              data.data_entrada :
              ''
            }
            <span class="fw-semibold fs-4">${data.horario_entrada}</span>
          </div>
          <div class="d-flex flex-column align-items-center justify-content-center">
            <div 
              class="connect-circle" 
              style="background-color: ${data.has_plano ? '#56327A' : '#f68e38'}"
            ></div>
            <div 
              class="connect-line" 
              style="background-color: ${data.has_plano ? '#56327A' : '#f68e38'}"
            ></div>
            <div 
              class="connect-circle" 
              style="background-color: ${data.has_plano ? '#56327A' : '#f68e38'}"
            ></div>
          </div>
          <div class="d-flex flex-column align-items-center justify-content-center">
            ${
              data.data_saida != data.data_entrada ?
              data.data_saida :
              ''
            }
            <span class="fw-semibold fs-4">${data.horario_saida}</span>
          </div>
        </div>
        <div class="d-flex flex-column gap-1 py-2" style="padding-right: 10px">
          <div class="pet-info">
            ${
              data.quarto ?
              `
                <div class="mb-2"><b>Quarto:</b> ${data.quarto}</div>
              ` : 
              ''
            }
            ${
              data.turma ?
              `
                <div class="mb-2"><b>Turma:</b> ${data.turma}</div>
              ` : 
              ''
            }
            <div>
              <b>Pet:</b>
              ${data.pet.nome}
            </div>
            <div>
              <b>Raça:</b>
              ${data.pet.raca ?? '--'}
            </div>
            <div>
              <b>Pelagem:</b>
              ${data.pet.pelagem ?? '--'}
            </div>
            <div>
              <b>Porte:</b>
              ${data.pet.porte ?? '--'} 
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between gap-1">
            <div class="d-flex gap-1 align-items-center" style="max-width: 150px">
              <b>Cliente:</b>
              <small class="text-truncate d-block">${data.cliente.razao_social ?? '--'}</small>
            </div>
            <div class="d-flex gap-1 align-items-center" style="max-width: 150px">
              <b>Colaborador:</b>
              <small class="text-truncate d-block">${data.colaborador ?? '--'}</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  void tooltip.offsetWidth;

  tooltip.classList.add('show');
}

/**
 * Método reponsável por configurar o modal de detalhamento
 * dos agendamentos conforme o agendamento selecionado
 * 
 * @param {*} data Informações do agendamento
 * 
 * @returns 
 */
function setHandleModalAgendamento(data) {
  const modal_agendamento = document.getElementById('handle_modal_agendamento');

  if (!modal_agendamento) return;

  // Título do modal

  let modulo_servico_icon = '';
  let modulo_servico_title = '';
  switch (data.extendedProps.modulo) {
    case 'HOTEL':
      modulo_servico_icon = '<i class="ri-hotel-line"></i>';
      modulo_servico_title = 'Reserva de hotel do pet: ';
    break;
    case 'CRECHE':
      modulo_servico_icon = '<i class="ri-graduation-cap-line"></i>';
      modulo_servico_title = 'Reserva de creche do pet: ';
    break;
    case 'ESTETICA':
      modulo_servico_icon = '<i class="ri-sparkling-line"></i>';
      modulo_servico_title = 'Reserva de estética do pet: ';
  };

  const modal_agendamento_title = modal_agendamento.querySelector('.modal-title');
  modal_agendamento_title.innerHTML = `
    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
    ${modulo_servico_title}
    <b>${data.extendedProps.pet.nome}</b>
    ${modulo_servico_icon}
  `;

  // Status do agendamento

  const modal_status_container = modal_agendamento.querySelector('#status-container');
  let status_btn_class = '';
  switch(data.extendedProps.estado) {
    case 'agendado':
      status_btn_class = 'btn-agendado';
      break;
    case 'em_andamento':
      status_btn_class = 'btn-em-andamento';
      break;
    case 'concluido':
      status_btn_class = 'btn-concluido';
      break;
    case 'rejeitado':
      status_btn_class = 'btn-rejeitado';
      break;
    case 'cancelado':
      status_btn_class = 'btn-cancelado';
      break;
    case 'pendente_aprovacao':
      status_btn_class = 'btn-pendente-aprovacao';
      break;
  }

  if (data.extendedProps.modulo != "ESTETICA" || data.extendedProps.estado != "pendente_aprovacao") {
    modal_status_container.querySelector('.btn-pendente-aprovacao').classList.add('d-none');
  } else {
    modal_status_container.querySelector('.btn-pendente-aprovacao').classList.remove('d-none');
  }

  modal_status_container.querySelectorAll('.btn-modal-status').forEach((btn) => {
    btn.classList.remove('selected');
  });
  modal_status_container.querySelector(`.${status_btn_class}`).classList.add('selected');
  
  modal_status_container.querySelectorAll('.btn-modal-status').forEach((btn) => {
    btn.addEventListener('click', () => {
      handleStatusAgendamentos(btn, {
        id: data.id,
        modulo: data.extendedProps.modulo,
        status: btn.getAttribute('data-value'),
        has_conta: data.extendedProps.conta_receber_id ? true : false
      })
    })
  });

  // Serviços do agendamento
  
  const modal_reserva_container = modal_agendamento.querySelector('#reserva-container');
  if (data.extendedProps.reserva || data.extendedProps.servicos.length > 0) {
    const modal_reserva_content = modal_reserva_container.querySelector('#reserva-content');
    const reserva_title = modal_reserva_container.querySelector('#reserva-container-title');
    const reserva_btn = modal_reserva_container.querySelector('#btn-edit-reserva');

    switch(data.extendedProps.modulo) {
      case 'HOTEL':
        modal_reserva_container.classList.remove('d-none');
        reserva_title.innerHTML = '<i class="ri-hotel-line"></i> Reserva';
        reserva_btn.innerHTML = '<i class="ri-hotel-line"></i> Editar reserva';

        reserva_btn.onclick = () => handleEditReservaHotelModal(data.extendedProps, data.id);
        break;
      case 'CRECHE':
        modal_reserva_container.classList.remove('d-none');
        reserva_title.innerHTML = '<i class="ri-graduation-cap-line"></i> Reserva';
        reserva_btn.innerHTML = '<i class="ri-graduation-cap-line"></i> Editar reserva';

        reserva_btn.onclick = () => handleEditReservaCrecheModal(data.extendedProps, data.id);
        break;
      case 'ESTETICA':
        modal_reserva_container.classList.remove('d-none');
        reserva_title.innerHTML = '<i class="ri-sparkling-line"></i> Reserva';
        reserva_btn.innerHTML = '<i class="ri-sparkling-line"></i> Editar reserva';

        reserva_btn.onclick = () => handleEditReservaEsteticaModal(data.extendedProps, data.id);
        break;
      default:
        modal_reserva_container.classList.add('d-none');
    }

    const data_saida_time = new Date(`${convertPtDateToInternational(data.extendedProps.data_saida)} ${data.extendedProps.horario_saida}`);
    const now = new Date();

    let servicos_container = '';
    if (data.extendedProps.modulo != 'ESTETICA') {
      servicos_container += `
        <div class="servico-item new-colors" style="border: none !important;">
          <div class="d-flex align-items-center gap-2">
            <div class="text-black fw-semibold">${data.extendedProps.reserva.nome}</div>
          </div>
          <b class="text-green">R$ ${convertFloatToMoeda(data.extendedProps.reserva.pivot.valor_servico ?? data.extendedProps.reserva.valor)}</b>
        </div>
      `;
    } else {
      data.extendedProps.servicos.forEach(servico => {
        servicos_container += `
          <div class="new-colors">
            <div class="d-flex align-items-center gap-2">
              <div class="text-black fw-semibold">${servico.nome}</div>
            </div>
            <b class="text-green">R$ ${convertFloatToMoeda(servico.subtotal)}</b>
          </div>
        `;
      });
    }

    let reserva_content = '';
    reserva_content += `
      <div 
        class="d-flex align-items-center justify-content-between gap-2 mt-3"
        style="padding-bottom: 7px; border-bottom: 1px solid #48185b;"
      >
        <div 
          class="horario-container text-black"
          style="border: none !important; padding: 0 !important; margin: 0 !important;"
        >
          <div class="d-flex flex-column align-items-center justify-content-center">
            ${
              data.extendedProps.data_entrada != data.extendedProps.data_saida ?
              data.extendedProps.data_entrada :
              ''
            }
            <span class="fw-semibold fs-5">${data.extendedProps.horario_entrada}</span>
          </div>
          <div class="d-flex flex-column align-items-center justify-content-center">
            <div 
              class="connect-circle" 
              style="background-color: ${data.extendedProps.has_plano ? '#56327A' : '#f68e38'}"
            ></div>
            <div 
              class="connect-line" 
              style="background-color: ${data.extendedProps.has_plano ? '#56327A' : '#f68e38'}"
            ></div>
            <div 
              class="connect-circle" 
              style="background-color: ${data.extendedProps.has_plano ? '#56327A' : '#f68e38'}"
            ></div>
          </div>
          <div class="d-flex flex-column align-items-center justify-content-center">
            ${
              data.extendedProps.data_saida != data.extendedProps.data_entrada ?
              data.extendedProps.data_saida :
              ''
            }
            <span class="fw-semibold fs-5">${data.extendedProps.horario_saida}</span>
          </div>
        </div>
        <div class="d-flex flex-column gap-2">
          ${
            data.extendedProps.quarto ?
            `
              <div class="mb-2 text-black"><b>Quarto:</b> ${data.extendedProps.quarto}</div>
            ` : 
            ''
          }
          ${
            data.extendedProps.turma ?
            `
              <div class="mb-2 text-black"><b>Turma:</b> ${data.extendedProps.turma}</div>
            ` : 
            ''
          }
          ${servicos_container}
        </div>
      </div>
    `
    
    modal_reserva_content.innerHTML = reserva_content;
  } else {
    modal_reserva_container.classList.add('d-none');
  }


  const modal_servicos_container = modal_agendamento.querySelector('#servicos-container');
  const btn_handle_servicos = modal_servicos_container.closest('.card-body').querySelector('#btn-edit-servicos')

  let servicos_content = '';

  if (data.extendedProps.servicos.length > 0 && data.extendedProps.modulo != 'ESTETICA') {
    modal_servicos_container.closest('.card-body').closest('.card').classList.remove('d-none');

    data.extendedProps.servicos.forEach(servico => {
      servicos_content += `
        <div class="servico-item new-colors">
          <div class="d-flex align-items-center gap-2">
            <div class="text-black fw-semibold">${servico.nome}</div>
            ${
              servico.pivot ? `
                - 
                <div class="text-center">
                  ${convertInternationalDateToPt(servico.pivot.data_servico)} 
                  <small>
                    ${servico.pivot.hora_servico.split(':')[0]}:${servico.pivot.hora_servico.split(':')[1]}
                  </small>
                </div>
              ` : 
              ''
            }
          </div>
          <b class="text-green">R$ ${convertFloatToMoeda(servico.pivot.valor_servico ?? servico.subtotal)}</b>
        </div>
      `;
    });
    btn_handle_servicos.innerHTML = `
      <i class="ri-edit-box-line"></i>
      Editar serviços
    `
  } else {
    modal_servicos_container.closest('.card-body').closest('.card').classList.remove('d-none');

    servicos_content = `
      <div class="servico-item new-colors">
        <div class="text-black text-center">Sem serviços...</div>
      </div>
    `
    btn_handle_servicos.innerHTML = `
      <i class="ri-add-circle-line"></i> Adicionar serviços
    `

    if (data.extendedProps.modulo == 'ESTETICA') {
      modal_servicos_container.closest('.card-body').closest('.card').classList.add('d-none');
    }
  }
  btn_handle_servicos.onclick = () => handleServicosExtrasModal(data.extendedProps.modulo, data.extendedProps, data._def.publicId);

  modal_servicos_container.innerHTML = servicos_content;

  const modal_servico_frete = modal_agendamento.querySelector('#servico-frete-container');
  if (data.extendedProps.frete) {
    let frete_variable = data.extendedProps.modulo == 'ESTETICA' ? 
    data.extendedProps.frete.subtotal :
    data.extendedProps.frete.pivot.valor_servico;

    modal_servico_frete.innerHTML = `
      <div class="servico-item new-colors">
        <div class="text-black fw-semibold">${data.extendedProps.frete.nome ?? data.extendedProps.frete.servico.nome}</div>
        <b class="text-green">R$ ${convertFloatToMoeda(frete_variable ?? data.extendedProps.frete.valor)}</b>

        ${
          data.extendedProps.endereco_frete && data.extendedProps.endereco_frete.endereco_url ? ` 
            <div class="text-right mt-1">
              <a 
                class="address_link"
                href=${data.extendedProps.endereco_frete.endereco_url}
                target="_blank"
              >
                <i class="ri-road-map-line"></i>
                Abrir endereço no mapa
              </a>
            </div>
          ` : ''
        }
      </div>
    `;
    modal_servico_frete.closest('.card-body').querySelector('#btn-edit-frete').innerHTML = `
      <i class="ri-edit-box-line"></i>
      Editar frete
    `

    if (data.extendedProps.endereco_frete) {
      modal_servico_frete.closest('.card-body').querySelector('#btn-cupom-frete').classList.remove('d-none');
    } else {
      modal_servico_frete.closest('.card-body').querySelector('#btn-cupom-frete').classList.add('d-none');
    }
  } else {
    modal_servico_frete.innerHTML = `
      <div class="servico-item new-colors">
        <div class="text-black text-center">Sem serviço de frete...</div>
      </div>
    `;
    modal_servico_frete.closest('.card-body').querySelector('#btn-edit-frete').innerHTML = `
      <i class="ri-add-circle-line"></i>
      Adicionar frete
    `

    modal_servico_frete.closest('.card-body').querySelector('#btn-cupom-frete').classList.add('d-none');
  };

  const btn_handle_frete = modal_servico_frete.closest('.card-body').querySelector('#btn-edit-frete');
  const btn_cupom_frete = modal_servico_frete.closest('.card-body').querySelector('#btn-cupom-frete');

  btn_handle_frete.onclick = () => handleServicoFreteModal(data.extendedProps.modulo, data.extendedProps, data._def.publicId);
  btn_cupom_frete.onclick = () => handleCupomEndPoint(data.extendedProps.modulo, data._def.publicId);

  // Produtos

  const modal_produtos_container = modal_agendamento.querySelector('#produtos-container');
  const btn_handle_produtos = modal_produtos_container.closest('.card-body').querySelector('#btn-edit-produtos');

  let produtos_content = '';

  if (data.extendedProps.produtos.length > 0) {
    data.extendedProps.produtos.forEach(produto => {
      produtos_content += `
        <div class="produto-item new-colors">
          <div class="d-flex align-items-center gap-1">
            <div class="text-black fw-semibold">${parseFloat(produto.quantidade).toFixed(0)}x ${produto.nome}</div>
          </div>
          <b class="text-green">R$ ${convertFloatToMoeda(produto.subtotal)}</b>
        </div>
      `;
    });

    btn_handle_produtos.innerHTML = `
      <i class="ri-edit-box-line"></i>
      Editar produtos
    `
  } else {
    produtos_content = `
      <div class="produto-item new-colors">
        <div class="text-black text-center">Sem produtos...</div>
      </div>
    `

    btn_handle_produtos.innerHTML = `
      <i class="ri-add-circle-line"></i>
      Adicionar produtos
    `
  }

  modal_produtos_container.innerHTML = produtos_content;

  btn_handle_produtos.onclick = () => handleProdutosModal(data.extendedProps.modulo, data.extendedProps, data._def.publicId);

  // Cliente e Pet

  const modal_cliente_pet_content = modal_agendamento.querySelector('#cliente-pet-content');
  if (data.extendedProps.cliente && data.extendedProps.pet) {
    modal_cliente_pet_content.innerHTML = `
      <div 
        class="d-flex justify-content-between gap-2"
        style="padding-bottom: 7px; border-bottom: 1px solid #48185b;"
      > 
        <div class="d-flex flex-column gap-1 text-black">
          <div class="fs-5 d-flex gap-1">
            <div class="fw-semibold">Cliente: </div>${data.extendedProps.cliente.razao_social ?? '--'}
          </div>
          <div class="fs-6 d-flex gap-1">
            <div class="fw-semibold">Contato: </div>${data.extendedProps.cliente_contato ?? '--'}
          </div>
        </div>
        <div class="d-flex flex-column gap-1 text-black">
          <div class="d-flex gap-1">
            <div class="fw-semibold">Pet: </div>${data.extendedProps.pet.nome}
          </div>
          <div class="fs-6 d-flex gap-1">
            <div class="fw-semibold">Raça: </div>${data.extendedProps.pet.raca ?? '--'}
          </div>
          <div class="fs-6 d-flex gap-1">
            <div class="fw-semibold">Pelagem: </div>${data.extendedProps.pet.pelagem ?? '--'}
          </div>
          <div class="fs-6 d-flex gap-1">
            <div class="fw-semibold">Porte: </div>${data.extendedProps.pet.porte ?? '--'}
          </div>
        </div>
      </div>
    `;
  }

  modal_cliente_pet_content.closest('.card-body').querySelector('#btn-edit-cliente').onclick = () => handleEditClienteModal(data.extendedProps);
  modal_cliente_pet_content.closest('.card-body').querySelector('#btn-edit-pet').onclick = () => handleEditPetModal(data.extendedProps);


  // Checklist

  const modal_checklist_container = modal_agendamento.querySelector('#checklist-container');
  if (data.extendedProps.has_checklist) {
    modal_checklist_container.classList.remove('d-none');

    const modal_checklist_content = modal_agendamento.querySelector('#checklist-content');
    let checklist_content = '';

    const add_checklist_entrada_btn = modal_checklist_container.querySelector('#checklist-btns').querySelector('#btn-add-checklist-entrada');
    const add_checklist_saida_btn = modal_checklist_container.querySelector('#checklist-btns').querySelector('#btn-add-checklist-saida');

    add_checklist_entrada_btn.classList.remove('d-none');
    add_checklist_saida_btn.classList.remove('d-none');

    if (data.extendedProps.checklists.length > 0) {
      data.extendedProps.checklists.forEach(checklist => {
        let checklist_label = '';

        switch (checklist.tipo) {
          case 'entrada':
            checklist_label = 'Checklist de entrada';

            add_checklist_entrada_btn.classList.add('d-none');
            break;
          case 'saida':
            checklist_label = 'Checklist de saída';

            add_checklist_saida_btn.classList.add('d-none');
            break;   
        }

        checklist_content += `
          <div class="servico-item">
            <div class="d-flex align-items-center gap-2">
              <div class="text-black fw-semibold">${checklist_label}</div>
              <button
                data-tipo-checklist="${checklist.tipo}"
                class="btn btn-sm btn-agendamento-checklist"
              >
                <i class="ri-eye-line"></i>
              </button> 
            </div>
          </div>
        `;
      });
    } else {
      checklist_content = `
        <div class="servico-item new-colors">
          <div class="text-black text-center">Sem checklist definido...</div>
        </div>
      `
    }

    add_checklist_entrada_btn.onclick = () => handleChecklistModal(data.extendedProps.modulo, data.extendedProps, data._def.publicId, 'entrada');
    add_checklist_saida_btn.onclick = () => handleChecklistModal(data.extendedProps.modulo, data.extendedProps, data._def.publicId, 'saida');
 
    modal_checklist_content.innerHTML = checklist_content;

    const btns_checklist = modal_checklist_content.querySelectorAll('.btn-agendamento-checklist');
    if (btns_checklist) {
      btns_checklist.forEach(btn => {
        btn.onclick = (e) => handleChecklistModal(
          data.extendedProps.modulo,
          data.extendedProps,
          data._def.publicId,
          e.target.closest('.btn-agendamento-checklist').getAttribute('data-tipo-checklist'),
          true
        );
      });
    }

  } else {
    modal_checklist_container.classList.add('d-none');
  }

  // Plano

  const modal_plano_container = modal_agendamento.querySelector('#plano-container');
  if (data.extendedProps.has_plano) {
    modal_plano_container.classList.remove('d-none');
    modal_plano_container.querySelector('#btn-view-plano').classList.remove('d-none');

    const modal_plano_content = modal_agendamento.querySelector('#plano-content');
    let plano_content = '';

    const frequencia_qtd_plural = data.extendedProps.frequencia_qtd_plano > 1 ? 's' : '';

    plano_content += `
      <div style="padding-bottom: 7px; border-bottom: 1px solid #48185b;">
        <div class="text-black text-uppercase fw-semibold fs-5">
          ${data.extendedProps.nome_plano ?? '--'}
        </div>
        <div class="d-flex gap-1 justify-content-between">
          <div class="fw-semibold fs-6">
            ${
              data.extendedProps.frequencia_tipo_plano == 'ilimitado' ?
                `
                  Agendamentos ilimitados por ${data.extendedProps.periodo_plano}
                ` 
              : 
                `
                  ${data.extendedProps.frequencia_qtd_plano} agendamento${frequencia_qtd_plural} por ${data.extendedProps.periodo_plano}
                `
            }
          </div>
        </div>
      </div>
    `

    modal_plano_content.innerHTML = plano_content;
    modal_plano_container.querySelector('#btn-view-plano').onclick = () => handlePlanoModal(data.extendedProps, data.extendedProps.id);
  } else {
    modal_plano_container.classList.add('d-none');
    modal_plano_container.querySelector('#btn-view-plano').classList.add('d-none');
  }

  // Fatura 

  const modal_fatura_container = modal_agendamento.querySelector('#fatura-container');  

  const modal_fatura_content = modal_agendamento.querySelector('#fatura-content');

  let fatura_content = '';
  let total_fatura = 0;

  if (data.extendedProps.reserva) {
    let valor_reserva_info = `
      <div>
        <span class="fw-semibold">Valor da Reserva:</span> 
        <span class="text-green">R$ ${convertFloatToMoeda(data.extendedProps.reserva.pivot.valor_servico)}</span>
      </div>
    `;

    fatura_content += valor_reserva_info;
    total_fatura += parseFloat(data.extendedProps.reserva.pivot.valor_servico);
  }

  if (data.extendedProps.servicos && data.extendedProps.servicos.length > 0 && data.extendedProps.modulo == 'ESTETICA') {
    let total_servicos = 0;

    data.extendedProps.servicos.forEach(servico => {
      total_servicos += parseFloat(servico.subtotal);
    });

    let valor_servicos_info = `
      <div>
        <span class="fw-semibold">Valor da Reserva:</span> 
        <span class="text-green">R$ ${convertFloatToMoeda(total_servicos)}</span>
      </div>
    `;

    fatura_content += valor_servicos_info;
    total_fatura += total_servicos;
  }

  if (data.extendedProps.servicos && data.extendedProps.servicos.length > 0 && data.extendedProps.modulo != 'ESTETICA') {
    let total_servicos = 0;

    data.extendedProps.servicos.forEach(servico => {
      total_servicos += parseFloat(servico.pivot.valor_servico);
    });

    let valor_servicos_info = `
      <div>
        <span class="fw-semibold">Valor dos Serviços Extras:</span> 
        <span class="text-green">R$ ${convertFloatToMoeda(total_servicos)}</span>
      </div>
    `;

    fatura_content += valor_servicos_info;
    total_fatura += total_servicos;
  }

  if (data.extendedProps.frete) {
    let frete_servico_var = data.extendedProps.frete?.pivot?.valor_servico ?
    data.extendedProps.frete.pivot.valor_servico : data.extendedProps.frete.subtotal;

    let valor_frete_info = `
      <div>
        <span class="fw-semibold">Valor do Frete:</span> 
        <span class="text-green">R$ ${convertFloatToMoeda(frete_servico_var)}</span>
      </div>
    `;

    fatura_content += valor_frete_info;
    total_fatura += parseFloat(frete_servico_var);
  }

  if (data.extendedProps.produtos && data.extendedProps.produtos.length > 0 && data.extendedProps.modulo != 'ESTETICA') {
    let total_produtos = 0;

    data.extendedProps.produtos.forEach(produto => {
      total_produtos += parseFloat(produto.valor_unitario * produto.pivot.quantidade);
    });

    let valor_produtos_info = `
      <div>
        <span class="fw-semibold">Valor dos Produtos:</span> 
        <span class="text-green">R$ ${convertFloatToMoeda(total_produtos)}</span>
      </div>
    `;

    fatura_content += valor_produtos_info;
    total_fatura += total_produtos;
  }

  if (data.extendedProps.produtos && data.extendedProps.produtos.length > 0 && data.extendedProps.modulo == 'ESTETICA') {
    let total_produtos = 0;

    data.extendedProps.produtos.forEach(produto => {
      console.log(produto)

      total_produtos += parseFloat(produto.valor) * produto.quantidade;
    });

    let valor_produtos_info = `
      <div>
        <span class="fw-semibold">Valor dos Produtos:</span> 
        <span class="text-green">R$ ${convertFloatToMoeda(total_produtos)}</span>
      </div>
    `;

    fatura_content += valor_produtos_info;
    total_fatura += total_produtos;
  }

  fatura_content += `
    <div class="mt-2 fw-semibold fs-4 text-right">
      <span>Total:</span> 
      <span class="text-green">R$ ${convertFloatToMoeda(total_fatura)}</span>
    </div>
  `

  modal_fatura_content.innerHTML = fatura_content; 

  if (data.extendedProps.conta_receber_id) {
    modal_fatura_container.querySelector('#btn-gerar-conta').classList.add('d-none');
    modal_fatura_container.querySelector('#btn-view-conta').classList.remove('d-none');

    modal_fatura_container.querySelector('#btn-view-conta').onclick = () => {
      const url = `/conta-receber/${data.extendedProps.conta_receber_id}/edit`;
      window.open(url, '_blank');
    }

  } else {
    modal_fatura_container.querySelector('#btn-view-conta').classList.add('d-none');
    modal_fatura_container.querySelector('#btn-gerar-conta').classList.remove('d-none');

    modal_fatura_container.querySelector('#btn-gerar-conta').onclick = () => {
      const url = `
        /conta-receber/create?agendamento_id=${data.id}
        &modulo=${data.extendedProps.modulo}
        &valor_total=${total_fatura}
        &data_entrada=${convertPtDateToInternational(data.extendedProps.data_entrada)}
        &data_saida=${convertPtDateToInternational(data.extendedProps.data_saida)}
        &cliente=${data.extendedProps.cliente.razao_social}
        &cliente_id=${data.extendedProps.cliente_id}
        &empresa_id=${$('#empresa_id').val()}
      `;

      window.open(url, '_blank');
    }
  }

  // Botão de O.S

  if (data.extendedProps.ordem_servico_id) {
    const modal_agendamento_os_btn = modal_agendamento.querySelector('#os-btn');
    modal_agendamento_os_btn.href = `${window.location.origin}/ordem-servico/${data.extendedProps.ordem_servico_id}`;
    modal_agendamento_os_btn.target = '_blank';
    modal_agendamento_os_btn.innerHTML = `
      <i class="ri-profile-line"></i>
      O.S <b>${data.extendedProps.ordem_servico_id}</b>
    `;
    modal_agendamento_os_btn.classList.remove('d-none'); 
  } else {
    modal_agendamento.querySelector('#os-btn').classList.add('d-none');
  }

  const modal = new bootstrap.Modal(modal_agendamento);
  modal.show();
}

function escapeHtml(value) {
  if (value === null || value === undefined) {
    return '';
  }

  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatMultiline(value) {
  if (!value) {
    return '';
  }

  return escapeHtml(value).replace(/\n/g, '<br>');
}

function buildInfoList(items) {
  if (!items || !items.length) {
    return '<span class="text-muted">Informações não disponíveis.</span>';
  }

  const content = items
    .filter((item) => item && item.value !== null && item.value !== undefined && item.value !== '')
    .map((item) => {
      return `
        <div>
          <span class="fw-semibold">${escapeHtml(item.label)}:</span> ${escapeHtml(item.value)}
        </div>
      `;
    })
    .join('');

  return content || '<span class="text-muted">Informações não disponíveis.</span>';
}

function setHandleModalAtendimentoVeterinario(event) {
  const modalElement = document.getElementById('handle_modal_atendimento_veterinario');

  if (!modalElement) return;

  const petName = event.extendedProps.pet.nome || '--';
  const titlePet = modalElement.querySelector('#vet-modal-title-pet');

  if (titlePet) {
    titlePet.textContent = petName;
  }

  const badge = modalElement.querySelector('#vet-status-badge');

  if (badge) {
    const statusColor = event.extendedProps.status_color || 'primary';
    badge.className = `badge rounded-pill text-bg-${statusColor} mt-3`;
    badge.textContent = event.extendedProps.status_label || '';
  }

  const statusButtons = modalElement.querySelectorAll('#vet-status-container .btn-modal-status');

  statusButtons.forEach((button) => {
    const statusValue = button.getAttribute('data-value');

    button.classList.toggle('selected', statusValue === event.extendedProps.estado);

    button.onclick = () => {
      handleStatusAgendamentos(button, {
        id: event.id,
        modulo: event.extendedProps.modulo || event.modulo || 'VETERINARIO',
        status: statusValue,
        has_conta: false,
      });
    };
  });

  const scheduleContent = modalElement.querySelector('#vet-schedule-content');
  const scheduleItems = [
    { label: 'Código', value: event.extendedProps.codigo },
    { label: 'Data', value: event.extendedProps.data_entrada },
    { label: 'Horário', value: event.extendedProps.horario },
    { label: 'Serviço', value: event.extendedProps.servico },
    { label: 'Tipo de atendimento', value: event.extendedProps.tipo_atendimento },
    { label: 'Sala', value: event.extendedProps.sala },
    { label: 'Veterinário', value: event.extendedProps.veterinario || event.extendedProps.colaborador },
  ];

  if (scheduleContent) {
    scheduleContent.innerHTML = buildInfoList(scheduleItems);
  }

  const tutorContent = modalElement.querySelector('#vet-tutor-content');
  const tutorItems = [
    { label: 'Nome', value: event.extendedProps.cliente.razao_social },
    { label: 'Contato', value: event.extendedProps.cliente_contato },
    { label: 'E-mail', value: event.extendedProps.cliente.email },
  ];

  if (tutorContent) {
    tutorContent.innerHTML = buildInfoList(tutorItems);
  }

  const patientContent = modalElement.querySelector('#vet-patient-content');
  const patientItems = [
    { label: 'Pet', value: event.extendedProps.pet.nome },
    { label: 'Espécie', value: event.extendedProps.pet.especie },
    { label: 'Raça', value: event.extendedProps.pet.raca },
    { label: 'Pelagem', value: event.extendedProps.pet.pelagem },
    { label: 'Porte', value: event.extendedProps.pet.porte },
    { label: 'Peso', value: event.extendedProps.pet.peso },
    { label: 'Idade', value: event.extendedProps.pet.idade },
  ];

  if (patientContent) {
    patientContent.innerHTML = buildInfoList(patientItems);
  }

  const notesContent = modalElement.querySelector('#vet-notes-content');
  const notes = event.extendedProps.notes || event.extendedProps.descricao;

  if (notesContent) {
    notesContent.innerHTML = notes && notes !== '--'
      ? `<p class="mb-0">${formatMultiline(notes)}</p>`
      : '<span class="text-muted">Sem observações registradas.</span>';
  }

  const billingContent = modalElement.querySelector('#vet-billing-content');
  const billingLink = modalElement.querySelector('#vet-billing-link');
  const billing = event.extendedProps.billing;

  if (billingContent) {
    if (billing) {
      const servicesTotal = billing.services_total_formatted ? `R$ ${billing.services_total_formatted}` : null;
      const productsTotal = billing.products_total_formatted ? `R$ ${billing.products_total_formatted}` : null;
      const grandTotal = billing.total_formatted ? `R$ ${billing.total_formatted}` : null;

      let billingHtml = '';

      if (servicesTotal) {
        billingHtml += `<div><span class="fw-semibold">Serviços:</span> ${servicesTotal}</div>`;
      }

      if (productsTotal) {
        billingHtml += `<div><span class="fw-semibold">Produtos:</span> ${productsTotal}</div>`;
      }

      if (grandTotal) {
        billingHtml += `<div class="mt-2 fw-semibold fs-5">Total: ${grandTotal}</div>`;
      }

      billingContent.innerHTML = billingHtml || '<span class="text-muted">Resumo indisponível.</span>';

      if (billingLink) {
        if (event.extendedProps.links && event.extendedProps.links.billing) {
          billingLink.href = event.extendedProps.links.billing;
          billingLink.classList.remove('d-none');
          billingLink.classList.remove('disabled');
          billingLink.setAttribute('aria-disabled', 'false');
        } else {
          billingLink.classList.add('d-none');
        }
      }
    } else {
      billingContent.innerHTML = '<span class="text-muted">Nenhum faturamento gerado.</span>';

      if (billingLink) {
        billingLink.classList.add('d-none');
      }
    }
  }

  const historyLink = modalElement.querySelector('#vet-history-link');

  if (historyLink) {
    if (event.extendedProps.links && event.extendedProps.links.history) {
      historyLink.href = event.extendedProps.links.history;
      historyLink.classList.remove('d-none');
      historyLink.classList.remove('disabled');
      historyLink.setAttribute('aria-disabled', 'false');
    } else {
      historyLink.classList.add('d-none');
    }
  }

  const editLink = modalElement.querySelector('#vet-edit-link');

  if (editLink) {
    if (event.extendedProps.links && event.extendedProps.links.edit) {
      editLink.href = event.extendedProps.links.edit;
      editLink.classList.remove('disabled');
      editLink.setAttribute('aria-disabled', 'false');
    } else {
      editLink.href = '#';
      editLink.classList.add('disabled');
      editLink.setAttribute('aria-disabled', 'true');
    }
  }

  const listLink = modalElement.querySelector('#vet-list-link');

  if (listLink) {
    if (event.extendedProps.links && event.extendedProps.links.index) {
      listLink.href = event.extendedProps.links.index;
      listLink.classList.remove('disabled');
      listLink.setAttribute('aria-disabled', 'false');
    } else {
      listLink.href = '#';
      listLink.classList.add('disabled');
      listLink.setAttribute('aria-disabled', 'true');
    }
  }

  const modal = new bootstrap.Modal(modalElement);
  modal.show();
}

/**
 * Atualiza o status do agendamento conforme o agendamento
 * e a situação selcionados
 *
 * @param {Element} element Elemento que disparou a requisição
 * @param {object} data Objeto com as informações do agendamento
 */
function handleStatusAgendamentos(element, data) {
  if (!data) return;

  Swal.fire({
    title: 'Deseja atualizar o status do agendamento?',
    icon: 'warning',
    showCancelButton: true,
    html: `
      ${data.status == 'cancelado' &&  data.has_conta ? `Ao cancelar o agendamento, a conta a receber vinculada ao mesmo será excluida.` : ''}
    `,
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Sim, atualizar!',
  }).then((result) => {
    if (!result.isConfirmed) {
      element.classList.remove('selected');
      return;
    }; 

    $.ajax({
      url: `${path_url}api/agendamentos/update-status-agendamento`,
      method: 'POST',
      data: data,
      success: function (response) {
        if (response.success == true) {
          Swal.fire({
            icon: 'success',
            title: 'Agendamento atualizado com sucesso!',
          }).then(() => {
            location.reload();
            
          })
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Erro ao atualizar agendamento!',
            text: response.message ??
            response.xhr.responseJSON.message ??
            'Ocorreu um erro desconhecido ao atualizar o agendamento.'
          });
        }
      }
    })
  })

}


/**
 * Configura a exibição e o formulário do modal de edição 
 * de reserva de hotel
 * 
 * @param {object} data Informações do agendamento
 * @param {number} reserva_id Id da reserva
 */
function handleEditReservaHotelModal(data, reserva_id) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');
  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 

  const edit_hotel_modal = document.querySelector('#edit_reserva_hotel');

  edit_hotel_modal.setAttribute('data-reserva-id', reserva_id);

  // Preechimento dos campos com as informações do agendamento

  edit_hotel_modal.querySelector('input[name="reserva_id_quarto"]').value = data.quarto_id;
  edit_hotel_modal.querySelector('input[name="reserva_nome_quarto"]').value = data.quarto;

  edit_hotel_modal.querySelector('#inp-reserva_checkin').value = convertPtDateToInternational(data.data_entrada)
  edit_hotel_modal.querySelector('#inp-reserva_timecheckin').value = data.horario_entrada;
  edit_hotel_modal.querySelector('#inp-reserva_checkout').value = convertPtDateToInternational(data.data_saida);
  edit_hotel_modal.querySelector('#inp-reserva_timecheckout').value = data.horario_saida;
  edit_hotel_modal.querySelector('#inp-reserva_tempo_execucao').value = data.reserva.tempo_execucao;

  // Preparação dos inputs

  const data_entrada_field = edit_hotel_modal.querySelector('input[name="reserva_checkin"]');
  const horario_entrada_field = edit_hotel_modal.querySelector('input[name="reserva_timecheckin"]');
  const data_saida_field = edit_hotel_modal.querySelector('input[name="reserva_checkout"]');
  const horario_saida_field = edit_hotel_modal.querySelector('input[name="reserva_timecheckout"]');

  $(data_entrada_field).tooltip('dispose');
  data_entrada_field.classList.remove('is-invalid');
  data_entrada_field.classList.remove('is-valid');

  $(horario_entrada_field).tooltip('dispose');
  horario_entrada_field.classList.remove('is-invalid');
  horario_entrada_field.classList.remove('is-valid');

  $(data_saida_field).tooltip('dispose');
  data_saida_field.classList.remove('is-invalid');
  data_saida_field.classList.remove('is-valid');

  $(horario_saida_field).tooltip('dispose');
  horario_saida_field.classList.remove('is-invalid');
  horario_saida_field.classList.remove('is-valid');


  // Configuração do select2

  setQuartoSelect2();

  const selected_quarto = $('input[name="reserva_id_quarto"]').val();
  const label_quarto = $('input[name="reserva_nome_quarto"]').val();

  if (selected_quarto && label_quarto) {
      const option = new Option(label_quarto, selected_quarto, true, true);
      $('select[name="reserva_quarto_id"]').append(option).trigger('change');

      $('select[name="reserva_quarto_id"]').tooltip('dispose');
      $('select[name="reserva_quarto_id"]').next('.select2').removeClass('select2-invalid');
      $('select[name="reserva_quarto_id"]').next('.select2').removeClass('select2-valid');
  }

  // Validação e interação dos campos

  const checkin_date = new Date(convertPtDateToInternational(data.data_entrada));
  checkin_date.setHours(0, 0, 0, 0);
  checkin_date.setDate(checkin_date.getDate() + 1);


  const modal = new bootstrap.Modal(edit_hotel_modal);
  
  // Ações de blur dos campos 

  if (data_entrada_field.attributes['disabled'] == undefined) { 
    data_entrada_field.addEventListener('blur', function (e) {
      const input_values = {
        data_entrada: {
          element: data_entrada_field,
          value: data_entrada_field.value
        },
        horario_entrada: {
          element: horario_entrada_field,
          value: horario_entrada_field.value
        },
        data_saida: {
          element: data_saida_field,
          value: data_saida_field.value
        },
        horario_saida: {
          element: horario_saida_field,
          value: horario_saida_field.value
        },
        tempo_execucao_reserva: data.reserva.tempo_execucao
      }

      validateDatesForModal(input_values, 'HOTEL', e.target);
      validateQuartoIsFree(reserva_id);
      setQuartoSelect2();
    });
  }
  if (horario_entrada_field.attributes['disabled'] == undefined) {
    horario_entrada_field.addEventListener('blur', function (e) {
      const input_values = {
        data_entrada: {
          element: data_entrada_field,
          value: data_entrada_field.value
        },
        horario_entrada: {
          element: horario_entrada_field,
          value: horario_entrada_field.value
        },
        data_saida: {
          element: data_saida_field,
          value: data_saida_field.value
        },
        horario_saida: {
          element: horario_saida_field,
          value: horario_saida_field.value
        },
        tempo_execucao_reserva: data.reserva.tempo_execucao
      }

      validateDatesForModal(input_values, 'HOTEL', e.target);
      validateQuartoIsFree(reserva_id);
      setQuartoSelect2();
    });
  }

  data_saida_field.addEventListener('blur', function (e) {
    const input_values = {
      data_entrada: {
        element: data_entrada_field,
        value: data_entrada_field.value
      },
      horario_entrada: {
        element: horario_entrada_field,
        value: horario_entrada_field.value
      },
      data_saida: {
        element: data_saida_field,
        value: data_saida_field.value
      },
      horario_saida: {
        element: horario_saida_field,
        value: horario_saida_field.value
      },
      tempo_execucao_reserva: data.reserva.tempo_execucao
    }

    validateDatesForModal(input_values, 'HOTEL', e.target);
    validateQuartoIsFree(reserva_id);
    setQuartoSelect2();
  });
  horario_saida_field.addEventListener('blur', function (e) {
    const input_values = {
      data_entrada: {
        element: data_entrada_field,
        value: data_entrada_field.value
      },
      horario_entrada: {
        element: horario_entrada_field,
        value: horario_entrada_field.value
      },
      data_saida: {
        element: data_saida_field,
        value: data_saida_field.value
      },
      horario_saida: {
        element: horario_saida_field,
        value: horario_saida_field.value
      },
      tempo_execucao_reserva: data.reserva.tempo_execucao
    }

    validateDatesForModal(input_values, 'HOTEL', e.target);
    validateQuartoIsFree(reserva_id);
    setQuartoSelect2();
  });

  setTimeout(() => {
    modal.show();
  }, 100);
}

/**
 * Configura a exibição e o formulário do modal de edição 
 * de reserva de creche
 * 
 * @param {object} data Informações do agendamento
 * @param {number} reserva_id Id da reserva
 */
function handleEditReservaCrecheModal(data, reserva_id) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');
  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 

  const edit_creche_modal = document.querySelector('#edit_reserva_creche');

  edit_creche_modal.setAttribute('data-reserva-id', reserva_id);

  // Preechimento dos campos com as informações do agendamento

  edit_creche_modal.querySelector('input[name="reserva_id_turma"]').value = data.turma_id;
  edit_creche_modal.querySelector('input[name="reserva_nome_turma"]').value = data.turma;

  edit_creche_modal.querySelector('#inp-reserva_data_entrada').value = convertPtDateToInternational(data.data_entrada)
  edit_creche_modal.querySelector('#inp-reserva_horario_entrada').value = data.horario_entrada;
  edit_creche_modal.querySelector('#inp-reserva_data_saida').value = convertPtDateToInternational(data.data_saida);
  edit_creche_modal.querySelector('#inp-reserva_horario_saida').value = data.horario_saida;
  edit_creche_modal.querySelector('#inp-reserva_tempo_execucao').value = data.tempo_execucao;

  // Preparação dos inputs

  const data_entrada_field = edit_creche_modal.querySelector('input[name="reserva_data_entrada"]');
  const horario_entrada_field = edit_creche_modal.querySelector('input[name="reserva_horario_entrada"]');
  const data_saida_field = edit_creche_modal.querySelector('input[name="reserva_data_saida"]');
  const horario_saida_field = edit_creche_modal.querySelector('input[name="reserva_horario_saida"]');

  $(data_entrada_field).tooltip('dispose');
  data_entrada_field.classList.remove('is-invalid');
  data_entrada_field.classList.remove('is-valid');

  $(horario_entrada_field).tooltip('dispose');
  horario_entrada_field.classList.remove('is-invalid');
  horario_entrada_field.classList.remove('is-valid');

  $(data_saida_field).tooltip('dispose');
  data_saida_field.classList.remove('is-invalid');
  data_saida_field.classList.remove('is-valid');

  $(horario_saida_field).tooltip('dispose');
  horario_saida_field.classList.remove('is-invalid');
  horario_saida_field.classList.remove('is-valid');


  // Configuração do select2

  setTurmaSelect2();

  const selected_turma = $('input[name="reserva_id_turma"]').val();
  const label_turma = $('input[name="reserva_nome_turma"]').val();

  if (selected_turma && label_turma) {
      const option = new Option(label_turma, selected_turma, true, true);
      $('select[name="reserva_turma_id"]').append(option).trigger('change');

      $('select[name="reserva_turma_id"]').tooltip('dispose');
      $('select[name="reserva_turma_id"]').next('.select2').removeClass('select2-invalid');
      $('select[name="reserva_turma_id"]').next('.select2').removeClass('select2-valid');
  }

  // Validação e interação dos campos

  const checkin_date = new Date(convertPtDateToInternational(data.data_entrada));
  checkin_date.setHours(0, 0, 0, 0);
  checkin_date.setDate(checkin_date.getDate() + 1);

  const modal = new bootstrap.Modal(edit_creche_modal);

   // Evento de focus dos campos de data

  document.querySelectorAll(`
    input[name="reserva_data_entrada"],
    input[name="reserva_horario_entrada"],
    input[name="reserva_data_saida"],
    input[name="reserva_horario_saida"]
  `).forEach(input => {
    input.addEventListener('focus', function () {
      this.setAttribute('data-old', this.value);
    });
  });
  
  // Ações de blur dos campos 

  if (data_entrada_field.attributes['disabled'] == undefined) { 
    data_entrada_field.addEventListener('blur', function (e) {
      const input_values = {
        data_entrada: {
          element: data_entrada_field,
          value: data_entrada_field.value
        },
        horario_entrada: {
          element: horario_entrada_field,
          value: horario_entrada_field.value
        },
        data_saida: {
          element: data_saida_field,
          value: data_saida_field.value
        },
        horario_saida: {
          element: horario_saida_field,
          value: horario_saida_field.value
        }, 
        tempo_execucao_reserva: data.reserva.tempo_execucao
      }

      validateDatesForModal(input_values, 'CRECHE', e.target);
      validateTurmaIsFree(reserva_id);
      setTurmaSelect2();
    });
  }
  if (horario_entrada_field.attributes['disabled'] == undefined) {
    horario_entrada_field.addEventListener('blur', function (e) {
      const input_values = {
        data_entrada: {
          element: data_entrada_field,
          value: data_entrada_field.value
        },
        horario_entrada: {
          element: horario_entrada_field,
          value: horario_entrada_field.value
        },
        data_saida: {
          element: data_saida_field,
          value: data_saida_field.value
        },
        horario_saida: {
          element: horario_saida_field,
          value: horario_saida_field.value
        }, 
        tempo_execucao_reserva: data.reserva.tempo_execucao
      }

      validateDatesForModal(input_values, 'CRECHE', e.target);
      validateQuartoIsFree(reserva_id);
      setQuartoSelect2();
    });
  }

  data_saida_field.addEventListener('blur', function (e) {
    const input_values = {
      data_entrada: {
        element: data_entrada_field,
        value: data_entrada_field.value
      },
      horario_entrada: {
        element: horario_entrada_field,
        value: horario_entrada_field.value
      },
      data_saida: {
        element: data_saida_field,
        value: data_saida_field.value
      },
      horario_saida: {
        element: horario_saida_field,
        value: horario_saida_field.value
      }, 
      tempo_execucao_reserva: data.reserva.tempo_execucao
    }

    validateDatesForModal(input_values, 'CRECHE', e.target);
    validateQuartoIsFree(reserva_id);
    setQuartoSelect2();
  });
  horario_saida_field.addEventListener('blur', function (e) {
    const input_values = {
      data_entrada: {
        element: data_entrada_field,
        value: data_entrada_field.value
      },
      horario_entrada: {
        element: horario_entrada_field,
        value: horario_entrada_field.value
      },
      data_saida: {
        element: data_saida_field,
        value: data_saida_field.value
      },
      horario_saida: {
        element: horario_saida_field,
        value: horario_saida_field.value
      }
    }

    validateDatesForModal(input_values, 'CRECHE', e.target);
    validateQuartoIsFree(reserva_id);
    setQuartoSelect2();
  });

  setTimeout(() => {
    modal.show();
  }, 100);
}

/**
 * Configura a exibição e o formulário do modal de edição 
 * de reserva de estética
 * 
 * @param {*} data Informações do agendamento
 * @param {*} reserva_id Id da reserva
 * @returns 
 */
function handleEditReservaEsteticaModal(data, reserva_id) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');
  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 

  const edit_estetica_modal = document.querySelector('#edit_reserva_estetica');

  edit_estetica_modal.setAttribute('data-reserva-id', reserva_id);

  $(edit_estetica_modal).find('input[name="id_estetica"]').val(reserva_id)

  const modal = new bootstrap.Modal(edit_estetica_modal);

  // Preechimento e configuração dos select de serviços

  const servicos_table = edit_estetica_modal.querySelector('.table-modal-servicos-estetica tbody');
  const template_servico_line = edit_estetica_modal.querySelector('.table-modal-servicos-estetica .dynamic-form');

  $(edit_estetica_modal).find('#select[name="servico_id[]"]').each(function() {
      if ($(this).hasClass('select2-hidden-accessible')) {
          $(this).select2('destroy');
      }
  });

  cleanSelect2Artifacts($(edit_estetica_modal), 'select[name="servico_id[]"]');

  servicos_table.innerHTML = '';

  data.servicos.forEach(servico => {
    const clone = template_servico_line.cloneNode(true);
    clone.classList.remove('d-none');

    const select = clone.querySelector('select[name="servico_id[]"]');

    const subtotal = clone.querySelector('.subtotal-servico');

    const id_servico = clone.querySelector('input[name="id_servico[]"]');
    const label_servico = clone.querySelector('input[name="label_servico[]"]');
    const tempo_execucao = clone.querySelector('input[name="tempo_execucao"]');

    select.value = servico.id; 
    subtotal.value = servico.subtotal; 

    id_servico.value = servico.id;
    label_servico.value = servico.nome;
    tempo_execucao.value = servico.tempo_execucao;

    servicos_table.appendChild(clone);
  });

  setEsteticaServicosSelect2()

  $(edit_estetica_modal).find('select[name="servico_id[]"]').each(function() {
    const id_servico = $(this).closest('tr').find('input[name="id_servico[]"]').val();
    const label_servico = $(this).closest('tr').find('input[name="label_servico[]"]').val();

    if (id_servico && label_servico) {
      const option = new Option(label_servico, id_servico, true, true);
      $(this).append(option).trigger('change.select2');
    }
  });

  // Variáveis de controle do modal

  const clean_funcionario_btn = edit_estetica_modal.querySelector('#clear-funcionario');

  // Preechimento e configuração do select de colaborador

  $(edit_estetica_modal).find('select[name="colaborador_id"]').select2({
    dropdownParent: $('#edit_reserva_estetica'),
    placeholder: 'Digite para buscar o colaborador',
    language: 'pt-BR',
    minimumInputLength: 2,
    ajax: {
      cache: true,
      url: path_url + 'api/funcionarios/pesquisa',
      dataType: 'json',
      data: function (params) {
        return {
          pesquisa: params.term,
          empresa_id: $('#empresa_id').val(),
        };
      },
      processResults: function (response) {
        return {
          results: response.map((v) => ({
            id: v.id,
            text: v.nome,
            has_jornada: v.has_jornada,
          })),
        };
      },
    },
  }).on('select2:select', async function (e) {
    await getSelectedDayJornada();
    validateDataAndHorarioFromEsteticaAgendamento();
  })

  if (data.colaborador_id && data.colaborador) {
    const option = new Option(data.colaborador, data.colaborador_id, true, true);
    $(edit_estetica_modal).find('select[name="colaborador_id"]').append(option).trigger('change');

    $(edit_estetica_modal).find('select[name="colaborador_id"]').tooltip('dispose');
    $(edit_estetica_modal).find('select[name="colaborador_id"]').next('.select2').removeClass('select2-invalid');
    $(edit_estetica_modal).find('select[name="colaborador_id"]').next('.select2').removeClass('select2-valid');

    clean_funcionario_btn.removeAttribute('disabled');
  } else {
    $(edit_estetica_modal).find('select[name="colaborador_id"]').val(null).trigger('change');
    $(edit_estetica_modal).find('select[name="colaborador_id"]').tooltip('dispose');
    $(edit_estetica_modal).find('select[name="colaborador_id"]').next('.select2').removeClass('select2-invalid');
    $(edit_estetica_modal).find('select[name="colaborador_id"]').next('.select2').removeClass('select2-valid');
  }

  clean_funcionario_btn.addEventListener('click', function () {
    clearFuncionarioSelect2();
  })

  if (!$(edit_estetica_modal).find('select[name="colaborador_id"]').val()) {
    clean_funcionario_btn.setAttribute('disabled', true);
  } else {
    clean_funcionario_btn.removeAttribute('disabled');
  }

  // Preenchimento dos campos de data e horário no modal

  $(edit_estetica_modal).find('input[name="data_agendamento"]').val(convertPtDateToInternational(data.data_entrada));
  $(edit_estetica_modal).find('input[name="horario_agendamento"]').val(data.horario_entrada);
  $(edit_estetica_modal).find('input[name="horario_saida"]').val(data.horario_saida);

  handleDataAgendamentoEsteticaFields();

  // Evento de exibição do modal

  setTimeout(() => {
    modal.show();
  }, 100);
}

/**
 * Limpa o valor do select de colaborador
 */
async function clearFuncionarioSelect2() {
  const colaborador_id = $('#edit_reserva_estetica').find('select[name="colaborador_id"]').val();

  if (colaborador_id) {
    $('#edit_reserva_estetica').find('select[name="colaborador_id"]').val(null).trigger('change');
    $('#edit_reserva_estetica').find('select[name="colaborador_id"]').next('.select2').find('.select2-selection--single').removeClass('select2-valid');
  }

  await getSelectedDayJornada();
  validateDataAndHorarioFromEsteticaAgendamento();
}

/**
 * Valida as datas selecionadas pelo usuário para identificar se elas estão 
 * batendo com os horários atuais e se estão se respeitando
 * 
 * @param {object} data Objeto com o valor atual dos campos de data e horário
 * @param {string} modulo Tipo de módulo que está sendo utilizado no momento (Influencia na mensagem de erro)
 * @param {element} trigger Elemento que disparou o evento (opcional)
 * 
 * @returns {boolean} Se as datas e horários estiverem corretos
 */
function validateDatesForModal(data, modulo, trigger = null) {
  const data_entrada_date = new Date(data.data_entrada.value);
  data_entrada_date.setHours(0, 0, 0, 0);
  data_entrada_date.setDate(data_entrada_date.getDate() + 1);

  const data_entrada_date_time = new Date(`${data.data_entrada.value} ${data.horario_entrada.value}`);

  const data_saida_date = new Date(data.data_saida.value);
  data_saida_date.setDate(data_saida_date.getDate() + 1);
  data_saida_date.setHours(0, 0, 0, 0);

  const data_saida_date_time = new Date(`${data.data_saida.value} ${data.horario_saida.value}`);

  let diff_ms = data_saida_date_time - data_entrada_date_time;
  const diff_time = Math.floor(diff_ms / 1000 / 60);

  const data_entrada_field = data.data_entrada.element;
  const horario_entrada_field = data.horario_entrada.element;
  const data_saida_field = data.data_saida.element;
  const horario_saida_field = data.horario_saida.element;

 
  if (diff_time < data.tempo_execucao_reserva) {
    Swal.fire({
      icon: 'error',
      title: 'Erro ao atualizar agendamento!',
      text: `
        O periodo selecionado deve ser maior ou igual ao tempo de execução do serviço da reserva.
      `
    }).then(() => {
      if (trigger) {
        trigger.classList.add('is-invalid');
        initializeTooltip($(trigger), 'O periodo selecionado deve ser maior ou igual ao tempo de execução do serviço da reserva.');
        trigger.value = trigger.getAttribute('data-old');
      }
    })
    
    return false;
  }

  if (
    data_saida_date < data_entrada_date &&
    data_entrada_field.value && data_saida_field.value
  ) 
  {
    Swal.fire({
      icon: 'error',
      title: 'Erro ao atualizar agendamento!',
      text: `
        A data de 
        ${modulo == 'HOTEL' ? 'check out ' : 'saída '} 
        deve ser maior do que a data de 
        ${modulo == 'HOTEL' ? 'check in' : 'entrada'}.
      `
    })

    let msg = '';
    switch(modulo) {
      case 'HOTEL':
        msg = 'A data de check out deve ser maior do que a data de check in.';
      break;
      case 'CRECHE':
        msg = 'A data de saída deve ser maior do que a data de entrada.';
      break;
    }

    data_saida_field.classList.add('is-invalid');
    initializeTooltip(data_saida_field, msg);

    return false;
  }

  if (
    data_saida_date_time < data_entrada_date_time &&
    data_entrada_field.value && data_saida_field.value && horario_entrada_field.value && horario_saida_field.value 
  ) 
  {
    Swal.fire({
      icon: 'error',
      title: 'Erro ao atualizar agendamento!',
      text: ` 
        O horário de 
        ${modulo == 'HOTEL' ? 'check out ' : 'saída '}
        deve ser maior do que o horário de 
        ${modulo == 'HOTEL' ? 'check in' : 'entrada'}.
      `
    })

    let msg = '';
    switch(modulo) {
      case 'HOTEL':
        msg = 'O horário de check out deve ser maior do que o horário de check in.';
      break;
      case 'CRECHE':
        msg = 'O horário de saída deve ser maior do que o horário de entrada.';
      break;
    }

    horario_saida_field.classList.add('is-invalid');
    initializeTooltip(horario_saida_field, msg);

    return false;
  }

  return true;
}

/**
 * Configura o select2 de quartos na modal de edição de agendamento
 */
function setQuartoSelect2 () {
  const checkin_date_field = $('input[name="reserva_checkin"]').val();
  const checkout_date_field = $('input[name="reserva_checkout"]').val();
  const checkin_time_field = $('input[name="reserva_timecheckin"]').val();
  const checkout_time_field = $('input[name="reserva_timecheckout"]').val();

  const checkin_time = checkin_date_field + ' ' + checkin_time_field;
  const checkout_time = checkout_date_field + ' ' + checkout_time_field;

  $('#inp-reserva_quarto_id').select2({
    placeholder: 'Selecione um quarto',
    dropdownParent: $('#edit_reserva_hotel'),
    width: '100%',
    ajax: {
      url: path_url + 'api/quartos/',
      dataType: 'json',
      data: function (params) {
        return {
          pesquisa: params.term,
          empresa_id: $('#empresa_id').val(),
          checkin: checkin_time,
          checkout: checkout_time,
        };
      },
      processResults: function (response) {
        return {
          results: response.data.map(function (quarto) {
            return {
              id: quarto.id,
              text: quarto.nome,
            };
          })
        };
      }
    }
  })
}

/**
 * Verifica se o quarto selecionado para a reserva está livre
 * no periódo selecionado
 * 
 * @param {number} reserva_id Id da reserva
 * 
 * @returns 
 */
function validateQuartoIsFree (reserva_id) {
    const quarto_id_input = $('select[name="reserva_quarto_id"]');

    const checkin_input = $('input[name="reserva_checkin"]');
    const checkin_hour_input = $('input[name="reserva_timecheckin"]');
    const checkout_input = $('input[name="reserva_checkout"]');
    const checkout_hour_input = $('input[name="reserva_timecheckout"]');

    if (
        !quarto_id_input.val() ||
        !checkin_input.val() ||
        !checkin_hour_input.val() ||
        !checkout_input.val() ||
        !checkout_hour_input.val()
    ) return false;

    const checkin_time = `${checkin_input.val()} ${checkin_hour_input.val()}`;
    const checkout_time = `${checkout_input.val()} ${checkout_hour_input.val()}`;

    let is_free = true;

    $.ajax({
        url: path_url + 'api/quartos/check-quarto-free',
        method: 'GET',
        data: {
            quarto_id: quarto_id_input.val(),
            empresa_id: $('#empresa_id').val(),
            checkin: checkin_time,
            checkout: checkout_time,
            reserva_id
        },
        async: false,
        success: function (response) {
            if (!response.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Quarto indisponível',
                    html: `
                        O quarto
                        <b>${quarto_id_input.find('option:selected').text()}</b>
                        está ocupado no período escolhido. <br>

                        <small>Selecione outro quarto ou altere o período da reserva.</small>
                    `
                });

                initializeTooltip(quarto_id_input.next('.select2'), 'Quarto indisponível no período escolhido.');
                quarto_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-valid');
                quarto_id_input.next('.select2').find('.select2-selection--single').addClass('select2-invalid');

                is_free = false;
            } else {
                quarto_id_input.tooltip('dispose');
                quarto_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-invalid');
                quarto_id_input.next('.select2').find('.select2-selection--single').addClass('select2-valid');
            }
        }
    })

    return is_free;
}

/**
 * Configura o select2 das turmas na modal de edição de agendamento
 */
function setTurmaSelect2 () {
  const data_entrada_date_field = $('input[name="reserva_data_entrada"]').val();
  const data_saida_date_field = $('input[name="reserva_data_saida"]').val();
  const horario_entrada_field = $('input[name="reserva_horario_entrada"]').val();
  const horario_saida_field = $('input[name="reserva_horario_saida"]').val();

  const data_entrada_time = data_entrada_date_field + ' ' + horario_entrada_field;
  const data_saida_time = data_saida_date_field + ' ' + horario_saida_field;

  $('#inp-reserva_turma_id').select2({
    placeholder: 'Selecione um quarto',
    dropdownParent: $('#edit_reserva_creche'),
    width: '100%',
    ajax: {
      url: path_url + 'api/turmas/',
      dataType: 'json',
      data: function (params) {
        return {
          pesquisa: params.term,
          empresa_id: $('#empresa_id').val(),
          data_entrada: data_entrada_time,
          data_saida: data_saida_time,
        };
      },
      processResults: function (response) {
        return {
          results: response.data.map(function (turma) {
            return {
              id: turma.id,
              text: turma.nome,
            };
          })
        };
      }
    }
  })
}

/**
 * Verifica se a turma selecionado para a reserva está livre
 * no periódo selecionado
 * 
 * @param {number} reserva_id Id da reserva
 * 
 * @returns 
 */
function validateTurmaIsFree (reserva_id) {
    const turma_id_input = $('select[name="reserva_turma_id"]');

    const data_entrada_input = $('input[name="reserva_data_entrada"]');
    const horario_entrada_input = $('input[name="reserva_horario_entrada"]');
    const data_saida_input = $('input[name="reserva_data_saida"]');
    const horario_saida_input = $('input[name="reserva_horario_saida"]');

    if (
        !turma_id_input.val() ||
        !data_entrada_input.val() ||
        !horario_entrada_input.val() ||
        !data_saida_input.val() ||
        !horario_saida_input.val()
    ) return false;

    const data_entrada_time = `${data_entrada_input.val()} ${horario_entrada_input.val()}`;
    const data_saida_time = `${data_saida_input.val()} ${horario_saida_input.val()}`;

    let is_free = true;

    $.ajax({
        url: path_url + 'api/turmas/check-turma-free',
        method: 'GET',
        data: {
            turma_id: turma_id_input.val(),
            empresa_id: $('#empresa_id').val(),
            data_entrada: data_entrada_time,
            data_saida: data_saida_time,
            reserva_id
        },
        async: false,
        success: function (response) {
            if (!response.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Turma indisponível',
                    html: `
                        A turma
                        <b>${turma_id_input.find('option:selected').text()}</b>
                        está lotada no período escolhido. <br>

                        <small>Selecione outra turma ou altere o período da reserva.</small>
                    `
                });

                initializeTooltip(turma_id_input.next('.select2'), 'Turma indisponível no período escolhido.');
                turma_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-valid');
                turma_id_input.next('.select2').find('.select2-selection--single').addClass('select2-invalid');

                is_free = false;
            } else {
                turma_id_input.tooltip('dispose');
                turma_id_input.next('.select2').find('.select2-selection--single').removeClass('select2-invalid');
                turma_id_input.next('.select2').find('.select2-selection--single').addClass('select2-valid');
            }
        }
    })

    return is_free;
}

function setEsteticaServicosSelect2() {
   $('select[name="servico_id[]"]').each(function(id, element) {
        const dropdownParent = $(this).closest('.modal');

        $(this).select2({
            dropdownParent: dropdownParent.length ? dropdownParent : $(document.body),
            minimumInputLength: 2,
            language: 'pt-BR',
            placeholder: 'Digite para buscar o serviço',
            width: '100%',
            ajax: {
                cache: true,
                url: path_url + 'api/petshop/servicos',
                dataType: 'json',
                data: function(params) {
                    const payload = {
                        pesquisa: params.term,
                        empresa_id: $('#empresa_id').val(),
                    };

                    if ($(element).closest('table').hasClass('table-servico-frete')) {
                        payload.is_frete = true;
                        payload.categoria = 'FRETE';
                    } else {
                        payload.categoria = 'ESTETICA';
                    }

                    return payload;
                },
                processResults: function(response) {
                  return {
                    results: response.map(function(v) {
                      return { 
                        id: v.id,
                        text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor),
                        tempo_execucao: v.tempo_execucao,
                        valor: v.valor
                      };
                    })
                  };
                }
            }
        }).on('select2:select', function(e) {
            var $row = $(this).closest('tr');
            var servico_id = $(this).val();

            if (!servico_id) return;
            $.get(path_url + 'api/servicos/find/' + servico_id)
                .done(function(res) {
                    var valor = res.valor || 0;
                    $row.find('.subtotal-servico').val(convertFloatToMoeda(valor));
                });
            $('#servico_id').val(servico_id);
            servicoSelect = document.querySelector('select.servico_id');

            if (!$(this).data('is-frete')) {
              $(this).siblings('input[name="tempo_execucao"]').first().val(e.params.data.tempo_execucao);

              handleDataAgendamentoEsteticaFields();
            }

            if ($(this).data('is-frete')) {
              handleAddressModalForEstetica();
            }
        });
    });
}

/**
 * Orquestra os campos e os outros recursos do modal de serviços extras e
 * implementa a navegação entre os modais
 * 
 * @param {*} modulo Módulo de serviço extra a ser referenciado
 * @param {*} data Informações do agendamento
 * @param {*} reserva_id ID da reserva
 * 
 * @returns 
 */
function handleServicosExtrasModal (modulo, data, reserva_id) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');

  if (!modal_agendamento_el) return;
  
  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 
  
  let modal_servicos_extras = document.getElementById('servicos_extras_petshop');

  modal_servicos_extras.setAttribute('data-modulo', modulo);

  modal_servicos_extras.setAttribute('data-reserva-id', reserva_id);

  // Título do modal

  let modulo_servico_icon = '';
  let modulo_servico_title = '';
  switch (modulo) {
    case 'HOTEL':
      modulo_servico_icon = '<i class="ri-hotel-line"></i>';
      modulo_servico_title = 'Serviços Extras da Reserva de Hotel';
    break;
    case 'CRECHE':
      modulo_servico_icon = '<i class="ri-graduation-cap-line"></i>';
      modulo_servico_title = 'Serviços Extras da Reserva de Creche';
    break;
  };
  
  modal_servicos_extras.querySelector('.modal-title').innerHTML = `
    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
    ${modulo_servico_title}
    ${modulo_servico_icon}
  `;

  // Preechimento das datas do agendamento

  const data_entrada_area = modal_servicos_extras.querySelector('#data-entrada-area');
  const horario_entrada_area = modal_servicos_extras.querySelector('#horario-entrada-area');
  const data_saida_area = modal_servicos_extras.querySelector('#data-saida-area');
  const horario_saida_area = modal_servicos_extras.querySelector('#horario-saida-area');

  const connect_circles = modal_servicos_extras.querySelectorAll('.connect-circle');
  const connect_row = modal_servicos_extras.querySelector('.connect-row');

  data_entrada_area.innerHTML = data.data_entrada;
  horario_entrada_area.innerHTML = data.horario_entrada;
  data_saida_area.innerHTML = data.data_saida;
  horario_saida_area.innerHTML = data.horario_saida;

  if (data.has_plano) {
    connect_circles.forEach(el => {
      el.style.backgroundColor = '#56327A';
    });
    connect_row.style.backgroundColor = '#56327A';
  } else {
    connect_circles.forEach(el => {
      el.style.backgroundColor = '#f68e38';
    });
    connect_row.style.backgroundColor = '#f68e38';
  }

  // Preenchimento e configuração dos campos de serviço extra

  const servicos_table = modal_servicos_extras.querySelector('.table-modal-servicos-extras tbody');
  const template_servico_line = modal_servicos_extras.querySelector('.table-modal-servicos-extras .dynamic-form');

  cleanSelect2Artifacts($(modal_servicos_extras), 'select[name="extra_servico_ids[]"]');

  $(modal_servicos_extras).find('input, select').removeClass('is-invalid');
  $(modal_servicos_extras).find('input, select').removeClass('is-valid');
  $(modal_servicos_extras).find('input, select').tooltip('dispose');

  if (data.servicos.length > 0) {
    servicos_table.innerHTML = '';

    data.servicos.forEach(servico => {
      const clone = template_servico_line.cloneNode(true);
      clone.classList.remove('d-none');

      const select = clone.querySelector('select[name="extra_servico_ids[]"]');
      const valor_servico = clone.querySelector('.valor-servico');
      const data_servico = clone.querySelector('input[name="extra_servico_datas[]"]');
      const hora_servico = clone.querySelector('input[name="extra_servico_horas[]"]');
      const tempo_execucao_servico = clone.querySelector('input[name="extra_tempo_execucao"]');

      const id_servico = clone.querySelector('input[name="id_servico"]');
      const nome_servico = clone.querySelector('input[name="nome_servico"]');

      select.value = servico.id; 
      valor_servico.value = convertFloatToMoeda(servico.pivot.valor_servico); 
      data_servico.value = servico.pivot.data_servico;
      hora_servico.value = servico.pivot.hora_servico;
      tempo_execucao_servico.value = servico.tempo_execucao;

      id_servico.value = servico.id;
      nome_servico.value = servico.nome;

      valor_servico.addEventListener('blur', function () {
        calcTotalExtraServicos();
      });

      servicos_table.appendChild(clone);
    });

    setExtraServicosSelect2();

    $('select[name="extra_servico_ids[]"]').each(function() {
      const id_servico = $(this).closest('tr').find('input[name="id_servico"]').val();
      const label_servico = $(this).closest('tr').find('input[name="nome_servico"]').val();

      if (id_servico && label_servico) {
        const option = new Option(label_servico, id_servico, true, true);
        $(this).append(option).trigger('change.select2');
      }
    });

    calcTotalExtraServicos();
  } else {
    servicos_table.innerHTML = '';

    const clone = template_servico_line.cloneNode(true);
    clone.classList.remove('d-none');

    $(clone).find('input').val('');
    $(clone).find('select[name="extra_servico_ids[]"]').val(null).trigger('change');

    servicos_table.appendChild(clone);
    
    setExtraServicosSelect2();
  }

  // Eventos dos campos de horário

  const formatted_data_entrada = convertPtDateToInternational(data.data_entrada) + ' ' + data.horario_entrada;
  const formatted_data_saida = convertPtDateToInternational(data.data_saida) + ' ' + data.horario_saida;

  modal_servicos_extras.querySelectorAll('input[name="extra_servico_datas[]"]').forEach(el => {
    el.addEventListener('focus', function () {
      $(this).data('old', $(this).val());
    })

    el.addEventListener('blur', function () {
      const row = $(this).closest('tr');

      validateServicoExtraDate(row, formatted_data_entrada, formatted_data_saida);
    });
  });

  modal_servicos_extras.querySelectorAll('input[name="extra_servico_horas[]"]').forEach(el => {
    el.addEventListener('focus', function () {
      $(this).data('old', $(this).val());
    })

    el.addEventListener('blur', function () {
      const row = $(this).closest('tr');

      validateServicoExtraDate(row, formatted_data_entrada, formatted_data_saida);
    });
  });


  modal_servicos_extras = bootstrap.Modal.getOrCreateInstance(modal_servicos_extras);

  setTimeout(() => {
    modal_servicos_extras.show();
  }, 100);
}

/**
 * Coofigura os select2 dos serviços extras já implementando as ações que 
 * ocorrem ao selecionar um serviço
 */
function setExtraServicosSelect2() {
   $('select[name="extra_servico_ids[]"]').each(function(id, element) {
        const dropdownParent = $(this).closest('.modal');

        $(this).select2({
            dropdownParent: dropdownParent.length ? dropdownParent : $(document.body),
            minimumInputLength: 2,
            language: 'pt-BR',
            placeholder: 'Digite para buscar o serviço',
            width: '100%',
            ajax: {
                cache: true,
                url: path_url + 'api/petshop/servicos',
                dataType: 'json',
                data: function(params) {
                    const payload = {
                      pesquisa: params.term,
                      empresa_id: $('#empresa_id').val(),
                      without_petshop: true
                    };
                    

                    return payload;
                },
                processResults: function(response) {
                    return {
                        results: response.map(function(v) {
                            return { 
                              id: v.id,
                              text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor),
                              valor: v.valor,
                              tempo_execucao: v.tempo_execucao 
                            };
                        })
                    };
                }
            }
        }).on('select2:select', function(e) {
          var $row = $(this).closest('tr');
          var servico_id = $(this).val();

          if (!servico_id) return;

          $row.find('.valor-servico').val(convertFloatToMoeda(e.params.data.valor)).trigger('blur');

          $row.find('.valor-servico').on('blur', function() {
            calcTotalExtraServicos();
          });

          $row.find('input[name="extra_tempo_execucao"]').val(e.params.data.tempo_execucao);

          const data_entrada_reserva = 
            convertPtDateToInternational($('#data-entrada-area').text()) + ' ' + $('#horario-entrada-area').text();

          const data_saida = 
            convertPtDateToInternational($('#data-saida-area').text()) + ' ' + $('#horario-saida-area').text();

          validateServicoExtraDate($row, data_entrada_reserva, data_saida);

          $row.find('input[name="extra_servico_datas[]"]').on('blur', function () {
            validateServicoExtraDate($row, data_entrada_reserva, data_saida);
          });
          $row.find('input[name="extra_servico_horas[]"]').on('blur', function () {
            validateServicoExtraDate($row, data_entrada_reserva, data_saida);
          });
          
          calcTotalExtraServicos()
        });
    });
}

/**
 * Impõem uma validação nos campos de data de em cada linha de serviço extra
 * para cada linha passada 
 * 
 * @param {JQuery} row Linha de serviço extra que contém os campos (Tem que ser um elemento JQuery)
 * @param {*} data_entrada_reserva Data e horário de entrada da reserva
 * @param {*} data_saida_reserva Data e horário de saída da reserva
 * 
 * @returns {boolean}
 */
function validateServicoExtraDate (row, data_entrada_reserva, data_saida_reserva) {
  const data_entrada = new Date(data_entrada_reserva);
  const data_saida = new Date(data_saida_reserva);

  const servico_field = row.find('select[name="extra_servico_ids[]"]').first();
  const data_servico_field = row.find('input[name="extra_servico_datas[]"]').first();
  const hora_servico_field = row.find('input[name="extra_servico_horas[]"]').first();
  const tempo_execucao_fields = $('input[name="extra_tempo_execucao"]');

  const data_servico = new Date(data_servico_field.val() + ' ' + hora_servico_field.val());

  let diff_ms = data_saida - data_servico;
  const diff_time = Math.floor(diff_ms / 1000 / 60);

  const now = new Date();
  now.setHours(0, 0, 0, 0);

  const right_now = new Date();
  right_now.setHours(0, 0, 0, 0);

  let total_time_execucao = 0;
  tempo_execucao_fields.each(function() {
    total_time_execucao += parseInt($(this).val());
  })

  if (!data_servico_field.val()) {
    initializeTooltip(data_servico_field, 'Determine a data de início primeiro.');
    data_servico_field.addClass('is-invalid');

    return false;
  };

  if (!hora_servico_field.val()) {
    initializeTooltip(hora_servico_field, 'Determine o horário de início primeiro.');
    hora_servico_field.addClass('is-invalid');

    return false;
  };

  if (data_servico < data_entrada) {
    Swal.fire({
        title: 'Data de início inválida.',
        html: `
            A data de início do serviço extra:
                <b>
                    ${servico_field.find('option:selected').text().split(' R$')[0]}
                </b>  
            deve ser maior ou igual a data de entrada da reserva.
        `,
        icon: 'warning'
    })

    initializeTooltip(data_servico_field, 'Data de início menor do que a data de entrada.');
    data_servico_field.addClass('is-invalid');
    data_servico_field.val(data_servico_field.data('old'));

    return false;
  }

  if (data_servico.getDate() == data_entrada.getDate() && hora_servico_field.val()) {
    if (data_servico.getTime() < data_entrada.getTime()) {
      Swal.fire({
          title: 'Horário de início inválido.',
          html: `
              O horário de início do serviço extra:
                  <b>
                      ${servico_field.find('option:selected').text().split(' R$')[0]}
                  </b>  
              deve ser maior ou igual ao horário de entrada da reserva.
          `,
          icon: 'warning'
      })

      initializeTooltip(data_servico_field, 'Horário de início menor do que o horário de entrada.');
      hora_servico_field.addClass('is-invalid');
      hora_servico_field.val(hora_servico_field.data('old'));

      return false;
    }
  }

  if (total_time_execucao > diff_time) {
    Swal.fire({
      title: 'Tempo de execução inválido.',
      html: `
          O tempo de execução do serviço extra:
              <b>
                  ${servico_field.find('option:selected').text().split(' R$')[0]}
              </b>  
          excede o tempo de permanência da reserva.
      `,
      icon: 'warning'
    })

    servico_field.next('.select2').addClass('select2-invalid');
    initializeTooltip(servico_field.next('.select2'), 'Tempo de execução maior do que o tempo de permanência da reserva.');

    return false;
  } 

  servico_field.next('.select2').removeClass('select2-invalid');
  servico_field.next('.select2').tooltip('dispose');

  if (data_servico > data_saida) {
    Swal.fire({
        title: 'Data de início inválida.',
        html: `
            A data de início do serviço extra:
              <b>
                  ${servico_field.find('option:selected').text().split(' R$')[0]}
              </b>  
            deve ser menor ou igual a data de saída da reserva.
        `,
        icon: 'warning'
    })

    initializeTooltip(data_servico_field, 'Data de início maior do que a data de saída.');
    data_servico_field.addClass('is-invalid');
    data_servico_field.val(data_servico_field.data('old'));

    return false;
  }

  return true;
}

/**
 * Calcula o valor total do serviços com base no valor de cada serviço 
 * e exibe no rodapé da tabela de serviços extras
 */
function calcTotalExtraServicos() {
  const total_servico_area = $('.total-servicos-extra');

  let total_servicos = 0;

  $('.table-modal-servicos-extras .valor-servico').each(function () {
    const clean_value = $(this).val().includes(',') ? convertMoedaToFloat($(this).val()) : parseFloat($(this).val());
    
    total_servicos += clean_value;
  });

  total_servico_area.html(isNaN(total_servicos) ? 'R$ 0,00' : convertFloatToMoeda(total_servicos));
}

/**
 * Manipula os campos do modal de serviço de frete e implementa a navegação entre os modais
 * 
 * @param {*} modulo módulo de serviço a ser referenciado
 * @param {*} data informações do agendamento
 * @param {*} reserva_id Id da reserva
 * 
 * @returns 
 */
function handleServicoFreteModal(modulo, data, reserva_id) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');

  if (!modal_agendamento_el) return;
  
  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 

  let modal_frete = document.getElementById('servico_frete_petshop');

  // Preenchimento e configuração do select de frete
  
  cleanSelect2Artifacts($(modal_frete), 'select[name="servico_frete"]');

  const servico_frete_field = $(modal_frete).find('select[name="servico_frete"]');
  const valor_frete_field = $(modal_frete).find('input[name="servico_frete_valor"]');

  const servico_frete_id_field = $(modal_frete).find('input[name="servico_frete_id"]');
  const servico_frete_nome_field = $(modal_frete).find('input[name="servico_frete_nome"]');

  const handle_endereco_btn = $('#handle-address-btn');

  if (data.frete) {
    const valor_frete_variable = modulo == 'ESTETICA' ? 
    data.frete.subtotal : data.frete.pivot.valor_servico;
    
    const nome_servico_variable = modulo == 'ESTETICA' ? 
    data.frete.servico.nome : data.frete.nome;

    const servico_id_variable = modulo == 'ESTETICA' ? 
    data.frete.servico.id : data.frete.id;

    servico_frete_field.val(servico_id_variable).trigger('change');
    valor_frete_field.val(convertFloatToMoeda(valor_frete_variable));

    servico_frete_id_field.val(servico_id_variable);
    servico_frete_nome_field.val(nome_servico_variable);

    if (data.endereco_frete) {
      handle_endereco_btn.html(`
        <i class="ri-map-pin-user-line"></i>
        Alterar endereço do frete
      `);

      modal_frete.querySelector('#endereco_cliente').value = JSON.stringify(data.endereco_frete);
    } else {
      handle_endereco_btn.html(`
        <i class="ri-map-pin-user-line"></i>
        Adicionar endereço do frete
      `);
    }

  } else {
    handle_endereco_btn.html(`
      <i class="ri-map-pin-user-line"></i>
      Adicionar endereço do frete
    `);

    servico_frete_id_field.val(null);
    servico_frete_nome_field.val(null);
    valor_frete_field.val(null);
  }

  setFreteServicoSelect2();

  if (servico_frete_id_field.val() && servico_frete_nome_field.val()) {
    const option = new Option(servico_frete_nome_field.val(), servico_frete_id_field.val(), true, true);
    servico_frete_field.append(option).trigger('change.select2');
  } else {
    servico_frete_field.val(null).trigger('change');
  }

  modal_frete.setAttribute('data-modulo', modulo);
  modal_frete.setAttribute('data-reserva-id', reserva_id);
  modal_frete.setAttribute('data-cliente-id', data.cliente.id);
  modal_frete.setAttribute('data-has-address', data.endereco_frete ? 'true' : 'false');

  modal_frete = bootstrap.Modal.getOrCreateInstance(modal_frete);

  handleEnderecoClienteModal();

  setTimeout(() => {
    modal_frete.show();
  }, 100);
}

/**
 * Configura os select2 dos serviços extras já implementando as ações que 
 * ocorrem ao selecionar um serviço
 */
function setFreteServicoSelect2() {
  const dropdownParent = $('select[name="servico_frete"]').closest('.modal');

  $('select[name="servico_frete"]').select2({
      dropdownParent: dropdownParent.length ? dropdownParent : $(document.body),
      minimumInputLength: 2,
      language: 'pt-BR',
      placeholder: 'Digite para buscar o serviço',
      width: '100%',
      ajax: {
          cache: true,
          url: path_url + 'api/petshop/servicos',
          dataType: 'json',
          data: function(params) {
              const payload = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
                is_frete: true
              };
              
              return payload;
          },
          processResults: function(response) {
              return {
                results: response.map(function(v) {
                  return { 
                    id: v.id,
                    text: v.nome + ' R$ ' + convertFloatToMoeda(v.valor),
                    valor: v.valor,
                    tempo_execucao: v.tempo_execucao 
                  };
                })
              };
          }
      }
  }).on('select2:select', function(e) {
    var $row = $(this).closest('tr');
    var servico_id = $(this).val();

    if (!servico_id) return;

    $row.find('.valor-servico').val(convertFloatToMoeda(e.params.data.valor)).trigger('blur');

    handleEnderecoClienteModal();
  });
}

/**
 * Manipula o botão de disparo que abre o modal de endereço e limpa os campos
 * caso não haja um frete selecionado
 */
function handleEnderecoClienteModal() {
  const modal_frete = bootstrap.Modal.getOrCreateInstance($('#servico_frete_petshop'))

  const frete_input = $('#servico_frete_petshop').find('select[name="servico_frete"]').first();
  const handle_modal_btn = $('#handle-address-btn');

  const endereco_data = $('#endereco_cliente').val() && JSON.parse($('#endereco_cliente').val());

  if (frete_input.length && handle_modal_btn.length && frete_input.val()) {
    handle_modal_btn.prop('disabled', false);


    handle_modal_btn.off('click').on('click', function() {
      modal_frete.hide();

      const modal_endereco_cliente = $('#modal_endereco_cliente');  
      
      if (endereco_data) {
        modal_endereco_cliente.find('input[name="cep"]').val(endereco_data.cep);
        modal_endereco_cliente.find('input[name="rua"]').val(endereco_data.rua);
        modal_endereco_cliente.find('input[name="bairro"]').val(endereco_data.bairro);
        modal_endereco_cliente.find('input[name="numero"]').val(endereco_data.numero);
        modal_endereco_cliente.find('input[name="complemento"]').val(endereco_data.complemento);

        const new_option = new Option(endereco_data.cidade.nome, endereco_data.cidade_id, false, false);
        modal_endereco_cliente.find('select[name="modal_cidade_id"]').append(new_option).trigger('change');
      }

      getClienteAddressForModalFields(modal_frete._element.getAttribute('data-cliente-id'));

      modal_endereco_cliente.on('hidden.bs.modal', function (e) {
        $('#endereco_cliente').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="cep"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="cep"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="rua"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="rua"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="bairro"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="bairro"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="numero"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="numero"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('input[name="complemento"]').val(null).trigger('change');
        $('#modal_endereco_cliente').find('input[name="complemento"]').removeClass('is-valid is-invalid').tooltip('dispose');
        $('#modal_endereco_cliente').find('select[name="modal_cidade_id"]').val(null).trigger('change');

        modal_frete.show();
      });

      modal_endereco_cliente.modal('show');
    });

    return;
  }

  $('#endereco_cliente').val(null).trigger('change');
  $('#modal_endereco_cliente').find('input[name="cep"]').val(null).trigger('change');
  $('#modal_endereco_cliente').find('input[name="rua"]').val(null).trigger('change');
  $('#modal_endereco_cliente').find('input[name="bairro"]').val(null).trigger('change');
  $('#modal_endereco_cliente').find('input[name="numero"]').val(null).trigger('change');
  $('#modal_endereco_cliente').find('input[name="complemento"]').val(null).trigger('change');
  $('#modal_endereco_cliente').find('select[name="modal_cidade_id"]').val(null).trigger('change');

  handle_modal_btn.prop('disabled', true);
}

/**
 * Preenche os campos de endereço do modal 
 * com base no endereço original do cliente caso o usuário deseje
 * 
 * @param {number} cliente_id Id do cliente do agendamento
 */
function getClienteAddressForModalFields(cliente_id) {
  if (!cliente_id) return;

  const address_fields = $('#modal_endereco_cliente').find('input, select');

  let is_filled = false;

  address_fields.each(function () {
    if ($(this).val()) {
      is_filled = true;
      return false;
    }
  });

  if (is_filled) return;

  let address = null;

  $.ajax({
    url: path_url + 'api/clientes/find/' + cliente_id,
    dataType: 'json',
    method: 'GET',
    async: false,
    success: function (response) {
        address = response;
    }
  });

  if (address) {
    setTimeout(() => {
        Swal.fire({
          title: 'Deseja utilizar o endereço do cliente?',
          showCancelButton: true,
          confirmButtonText: 'Sim',
          cancelButtonText: `Não`,
          icon: 'question',
          revertButtons: true,
        }).then((result) => {
          if (result.isConfirmed) {
            $('input[name="cep"]').val(address.cep).trigger('blur');
            $('input[name="rua"]').val(address.rua).trigger('blur');
            $('input[name="numero"]').val(address.numero).trigger('blur');
            $('input[name="bairro"]').val(address.bairro).trigger('blur');
            $('input[name="complemento"]').val(address.complemento).trigger('blur');

            // const cidade_option = new Option(address.cidade.nome, address.cidade_id, true, true);
            // $('select[name="modal_cidade_id"]').append(cidade_option).trigger('change');
          }    
        })
    }, 600);
  }
}

function handleCupomEndPoint(modulo, reserva_id) {
  switch (modulo) {
    case 'HOTEL':
      window.open(path_url + 'hotel/endereco-entrega/' + reserva_id, "_blank");
      break;
    case 'CRECHE':
      window.open(path_url + 'creche/endereco-entrega/' + reserva_id, "_blank");
      break;
    case 'ESTETICA':
      window.open(path_url + 'estetica/endereco-entrega/' + reserva_id, "_blank");
      break;
  }
}

/**
 * Manipula e configura os campos de produtos do modal e organiza os eventos 
 * 
 * @param {*} modulo Serviço a ser referenciado
 * @param {*} data Informações do agendamento e dos produtos
 * @param {*} reserva_id Id da reserva
 * @returns 
 */
function handleProdutosModal (modulo, data, reserva_id) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');

  if (!modal_agendamento_el) return;
  
  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 
  
  let modal_produtos = document.getElementById('produtos_petshop');

  modal_produtos.setAttribute('data-modulo', modulo);

  modal_produtos.setAttribute('data-reserva-id', reserva_id);

  // Título do modal

  let modulo_produto_icon = '';
  let modulo_produto_title = '';
  switch (modulo) {
    case 'HOTEL':
      modulo_produto_icon = '<i class="ri-hotel-line"></i>';
      modulo_produto_title = 'Produtos da Reserva de Hotel';
    break;
    case 'CRECHE':
      modulo_produto_icon = '<i class="ri-graduation-cap-line"></i>';
      modulo_produto_title = 'Produtos da Reserva de Creche';
    break;
    case 'ESTETICA':
      modulo_produto_icon = '<i class="ri-sparkling-line"></i>';
      modulo_produto_title = 'Produtos da Reserva de Estética';
    break;
  };
  
  modal_produtos.querySelector('.modal-title').innerHTML = `
    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
    ${modulo_produto_title}
    ${modulo_produto_icon}
  `;

  // Preenchimento e configuração dos campos de serviço extra

  const produtos_table = modal_produtos.querySelector('.table-produtos-agendamento tbody');
  const template_produto_line = modal_produtos.querySelector('.table-produtos-agendamento .dynamic-form');

  cleanSelect2Artifacts($(modal_produtos), 'select[name="agendamento_produto_id[]"]');

  $(modal_produtos).find('input, select').removeClass('is-invalid');
  $(modal_produtos).find('input, select').removeClass('is-valid');
  $(modal_produtos).find('input, select').tooltip('dispose');

  if (data.produtos.length > 0) {
    produtos_table.innerHTML = '';

    data.produtos.forEach(produto => {
      const clone = template_produto_line.cloneNode(true);

      clone.classList.remove('d-none');

      const select = clone.querySelector('select[name="agendamento_produto_id[]"]');
      const quantidade = clone.querySelector('input[name="agendamento_qtd_produto[]"]');
      const valor_unitario = clone.querySelector('input[name="agendamento_valor_unitario_produto[]"]');
      const subtotal = clone.querySelector('input[name="agendamento_subtotal_produto[]"]');

      const id_produto = clone.querySelector('input[name="id_produto"]');
      const nome_produto = clone.querySelector('input[name="nome_produto"]');

      select.value = produto.id; 
      quantidade.value = produto.quantidade;
      valor_unitario.value = convertFloatToMoeda(produto.valor_unitario);
      subtotal.value = convertFloatToMoeda(produto.subtotal);

      id_produto.value = produto.id;
      nome_produto.value = produto.nome;

      valor_unitario.addEventListener('blur', function () {
        calcTotalProdutos();
      });

      produtos_table.appendChild(clone);
    });

    setProdutosSelect2();

    $('select[name="agendamento_produto_id[]"]').each(function() {
      const id_produto = $(this).closest('tr').find('input[name="id_produto"]').val();
      const label_produto = $(this).closest('tr').find('input[name="nome_produto"]').val();

      if (id_produto && label_produto) {
        const option = new Option(label_produto, id_produto, true, true);
        $(this).append(option).trigger('change.select2');
      }
    });

    calcTotalProdutos();
  } else {
    produtos_table.innerHTML = '';

    const clone = template_produto_line.cloneNode(true);
    clone.classList.remove('d-none');

    $(clone).find('input').val('');
    $(clone).find('select[name="agendamento_produto_id[]"]').val(null).trigger('change');

    produtos_table.appendChild(clone);
    
    setProdutosSelect2();

    calcTotalProdutos();
  }

  // Eventos de conta dos produtos

  modal_produtos.querySelectorAll('input[name="agendamento_qtd_produto[]"]').forEach(input => {
    input.addEventListener('blur', function () {
      const row = this.closest('tr');

      calcSubtotalProduto(row);
      calcTotalProdutos();
    });
  })

  modal_produtos = bootstrap.Modal.getOrCreateInstance(modal_produtos);

  setTimeout(() => {
    modal_produtos.show();
  }, 100);
}

/**
 * Configura os select2 dos produtos já implementando as ações que 
 * ocorrem ao selecionar um produto
 */
function setProdutosSelect2() {
   $('select[name="agendamento_produto_id[]"]').each(function() {
        const dropdownParent = $(this).closest('.modal');

        $(this).select2({
          language: 'pt-BR',
          placeholder: 'Digite para buscar o produto',
          dropdownParent: dropdownParent.length ? dropdownParent : $(document.body),
          width: '100%',
          ajax: {
            cache: true,
            url: path_url + 'api/produtos',
            dataType: 'json',
            data: function (params) {
                let empresa_id = $('#empresa_id').val();
                console.clear();

                var query = {
                    pesquisa: params.term,
                    empresa_id: empresa_id,
                    usuario_id: $('#usuario_id').val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];

                $.each(response, function (i, v) {
                  results.push({
                      id: v.id,
                      text: `${v.nome} - R$ ${convertFloatToMoeda(v.valor_unitario)}`,
                      nome: v.nome,
                      valor_unitario: v.valor_unitario,
                  });
                });

                return {
                    results,
                };
            },
          },
        }).on('select2:select', function(e) {
          var $row = $(this).closest('tr');
          var produto_id = $(this).val();

          if (!produto_id) return;

          $row.find('.valor_unitario-produto').val(convertFloatToMoeda(e.params.data.valor_unitario)).trigger('blur');
          $row.find('.qtd-produto').val(1).trigger('blur');

          $row.find('input[name="id_produto"]').val(produto_id);
          $row.find('input[name="nome_produto"]').val(e.params.data.nome);

          $row.find('.qtd-produto').on('blur', function() {
            const dom_row = this.closest('tr');

            calcSubtotalProduto(dom_row);
            calcTotalProdutos();
          });
          
          calcTotalProdutos()
        });
    });
}

/**
 * Calcula o valor total do serviços com base no valor de cada serviço 
 * e exibe no rodapé da tabela de serviços extras
 */
function calcSubtotalProduto(row) {
  const quantidade = row.querySelector('input[name="agendamento_qtd_produto[]"]').value || 0;
  const valor_unitario = row.querySelector('input[name="agendamento_valor_unitario_produto[]"]').value;

  const subtotal_produto = convertMoedaToFloat(valor_unitario ?? 0) * parseFloat(quantidade ?? 0);

  row.querySelector('input[name="agendamento_subtotal_produto[]"]').value = convertFloatToMoeda(subtotal_produto);
}

/**
 * Calcula o valor total do serviços com base no valor de cada serviço 
 * e exibe no rodapé da tabela de serviços extras
 */
function calcTotalProdutos() {
  const total_produto_area = $('.total-produtos');

  let total_servicos = 0;

  $('.table-produtos-agendamento .subtotal-produto').each(function () {
    const clean_value = $(this).val().includes(',') ? convertMoedaToFloat($(this).val()) : parseFloat($(this).val());
    
    total_servicos += clean_value;
  });

  total_produto_area.html(isNaN(total_servicos) ? 'R$ 0,00' : convertFloatToMoeda(total_servicos));
}

function validateProduto(row) {
  const produto_id = row.querySelector('select[name="agendamento_produto_id[]"]').value;
  const quantidade = row.querySelector('input[name="agendamento_qtd_produto[]"]').value || 0;
  
  if (produto_id) {
    if (quantidade <= 0 || !quantidade) {
      Swal.fire({
        icon: 'error',
        title: 'Quantidade inválida!',
        html: `
          Informe a quantidade do produto 
          <b>${row.querySelector('input[name="nome_produto"]').value}</b>.
        `,
      })

      row.querySelector('input[name="agendamento_qtd_produto[]"]').classList.add('is-invalid');
      initializeTooltip(row.querySelector('input[name="agendamento_qtd_produto[]"]'), 'Determine a quantidade do produto.');

      return false;
    }
  }

  return true;
}

/**
 * Manipula os campos do modal de edição de cliente e os
 * preenche com seus respectivos valores
 * 
 * @param {object} data Informações do agendamento e do cliente 
 * @returns 
 */
function handleEditClienteModal(data) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');
  if (!modal_agendamento_el) return;

  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 

  let modal_editar_cliente = document.getElementById('editar_agendamento_cliente');

  modal_editar_cliente.setAttribute('data-cliente-id', data.cliente.id);

  // Nome do cliente no título do modal
  
  modal_editar_cliente.querySelector('.modal-title b').textContent = data.cliente.razao_social;

  // Preenchimento dos campos do modal

  $(modal_editar_cliente).find('input, select, textarea').removeClass('is-valid');
  $(modal_editar_cliente).find('input, select, textarea').removeClass('is-invalid');
  $(modal_editar_cliente).find('input, select, textarea').tooltip('dispose');

  let clean_cpf_cnpj = data.cliente.cpf_cnpj?.replace(/[^0-9]/g, '') ?? null;

  modal_editar_cliente.querySelector('input[name="ie"]').value = data.cliente.ie;
  modal_editar_cliente.querySelector('select[name="status"]').value = data.cliente.status;
  modal_editar_cliente.querySelector('select[name="contribuinte"]').value = data.cliente.contribuinte;
  modal_editar_cliente.querySelector('select[name="consumidor_final"]').value = data.cliente.consumidor_final;
  modal_editar_cliente.querySelector('input[name="limite_credito"]').value = data.cliente.limite_credito;
  modal_editar_cliente.querySelector('input[name="telefone"]').value = data.cliente.telefone;
  modal_editar_cliente.querySelector('input[name="telefone_secundario"]').value = data.cliente.telefone_secundario;
  modal_editar_cliente.querySelector('input[name="telefone_terciario"]').value = data.cliente.telefone_terciario;
  modal_editar_cliente.querySelector('input[name="email"]').value = data.cliente.email;
  modal_editar_cliente.querySelector('input[name="cep"]').value = data.cliente.cep;
  modal_editar_cliente.querySelector('input[name="rua"]').value = data.cliente.rua;
  modal_editar_cliente.querySelector('input[name="numero"]').value = data.cliente.numero;
  modal_editar_cliente.querySelector('input[name="bairro"]').value = data.cliente.bairro;
  modal_editar_cliente.querySelector('input[name="complemento"]').value = data.cliente.complemento;

  if ((clean_cpf_cnpj && clean_cpf_cnpj.length <= 11) || !clean_cpf_cnpj) {
    // Quando é CPF

    modal_editar_cliente.querySelectorAll('div[data-person="juridica"]').forEach((div) => {
      div.classList.add('d-none');

      div.querySelector('input') ? div.querySelector('input').classList?.add('ignore') : '';
      div.querySelector('select') ? div.querySelector('select').classList?.add('ignore') : '';
      div.querySelector('textarea') ? div.querySelector('textarea').classList?.add('ignore') : '';

      div.querySelector('input') ? div.querySelector('input').value = '' : '';
      div.querySelector('select') ? div.querySelector('select').value = '' : '';
      div.querySelector('textarea') ? div.querySelector('textarea').value = '' : '';
    })

    modal_editar_cliente.querySelectorAll('div[data-person="fisica"]').forEach((div) => {
      div.classList.remove('d-none');

      div.querySelector('input') ? div.querySelector('input').classList.remove('ignore') : '';
      div.querySelector('select') ? div.querySelector('select').classList.remove('ignore') : '';
      div.querySelector('textarea') ? div.querySelector('textarea').classList.remove('ignore') : '';
    })

    modal_editar_cliente.querySelector('input[name="cpf"]').value = data.cliente.cpf_cnpj;
    modal_editar_cliente.querySelector('input[name="nome"]').value = data.cliente.razao_social;
    modal_editar_cliente.querySelector('input[name="data_nascimento"]').value = data.cliente.data_nascimento;
  } else {
    // Quando é CNPJ

    modal_editar_cliente.querySelectorAll('div[data-person="fisica"]').forEach((div) => {
      div.classList.add('d-none');

      div.querySelector('input') ? div.querySelector('input').classList?.add('ignore') : '';
      div.querySelector('select') ? div.querySelector('select').classList?.add('ignore') : '';
      div.querySelector('textarea') ? div.querySelector('textarea').classList?.add('ignore') : '';

      div.querySelector('input') ? div.querySelector('input').value = '' : '';
      div.querySelector('select') ? div.querySelector('select').value = '' : '';
      div.querySelector('textarea') ? div.querySelector('textarea').value = '' : '';
    })

    modal_editar_cliente.querySelectorAll('div[data-person="juridica"]').forEach((div) => {
      div.classList.remove('d-none');

      div.querySelector('input') ? div.querySelector('input').classList.remove('ignore') : '';
      div.querySelector('select') ? div.querySelector('select').classList.remove('ignore') : '';
      div.querySelector('textarea') ? div.querySelector('textarea').classList.remove('ignore') : '';
    })

    modal_editar_cliente.querySelector('input[name="cnpj"]').value = data.cliente.cpf_cnpj;
    modal_editar_cliente.querySelector('input[name="razao_social"]').value = data.cliente.razao_social;
    modal_editar_cliente.querySelector('input[name="nome_fantasia"]').value = data.cliente.nome_fantasia;
    modal_editar_cliente.querySelector('input[name="contato"]').value = data.cliente.contato;
  }

  setCidadeSelect2(modal_editar_cliente);

  if (data.cliente.cidade_id && data.cliente.nome_cidade) {
    const option = new Option(data.cliente.nome_cidade, data.cliente.cidade_id, true, true);

    $(modal_editar_cliente).find('select[name="cidade_id"]').append(option);
  }

  modal_editar_cliente = bootstrap.Modal.getOrCreateInstance(modal_editar_cliente); 

  setTimeout(() => {
    modal_editar_cliente.show();
  }, 100);
}

/**
 * Configura o select2 do campo de cidade
 * 
 * @param {*} modal modal pai caso tenha
 */
function setCidadeSelect2(modal) {
  $('#inp-cidade_id').select2({
    minimumInputLength: 2,
    language: 'pt-BR',
    placeholder: 'Digite para buscar a cidade',
    dropdownParent: $(modal) ? $(modal) : $(document.body),
    width: '100%',
    ajax: {
        cache: true,
        url: path_url + 'api/buscaCidades',
        dataType: 'json',
        data: function (params) {
            console.clear();
            var query = {
                pesquisa: params.term,
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text = v.info;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
  });
}

/**
 * Manipula e configura o modal de edição de pet preenchendo os campos e
 * os configurando
 * 
 * @param {object} data Informações do agendamento e do pet 
 * @returns 
 */
function handleEditPetModal(data) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');
  if (!modal_agendamento_el) return;

  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide(); 

  let modal_editar_pet = document.getElementById('editar_agendamento_pet');

  modal_editar_pet.setAttribute('data-pet-id', data.pet.id);

  // Nome do pet no título do modal
  
  modal_editar_pet.querySelector('.modal-title b').textContent = data.pet.nome;

  // Preenchimento dos campos do modal

  $(modal_editar_pet).find('input, select, textarea').removeClass('is-valid');
  $(modal_editar_pet).find('input, select, textarea').removeClass('is-invalid');
  $(modal_editar_pet).find('input, select, textarea').tooltip('dispose');

  modal_editar_pet.querySelector('input[name="nome"]').value = data.pet.nome;
  modal_editar_pet.querySelector('input[name="cliente"]').value = data.cliente.razao_social;
  modal_editar_pet.querySelector('input[name="cor"]').value = data.pet.cor;
  modal_editar_pet.querySelector('select[name="sexo"]').value = data.pet.sexo;
  modal_editar_pet.querySelector('input[name="peso"]').value = data.pet.peso;
  modal_editar_pet.querySelector('input[name="porte"]').value = data.pet.porte;
  modal_editar_pet.querySelector('input[name="origem"]').value = data.pet.origem;
  modal_editar_pet.querySelector('input[name="data_nascimento"]').value = data.pet.data_nascimento;
  modal_editar_pet.querySelector('input[name="chip"]').value = data.pet.chip;

  modal_editar_pet.querySelector('select[name="tem_pedigree"]').value = data.pet.tem_pedigree ? data.pet.tem_pedigree : "N";
  modal_editar_pet.querySelector('input[name="pedigree"]').value = data.pet.pedigree;
  modal_editar_pet.querySelector('textarea[name="observacao"]').value = data.pet.observacao;

  cleanSelect2Artifacts($(modal_editar_pet), 'select[name="especie_id"]');
  cleanSelect2Artifacts($(modal_editar_pet), 'select[name="raca_id"]');

  setAgendamentoEspecieSelect2($(modal_editar_pet), 'select[name="agendamento_especie_id"]');
  setAgendamentoRacaSelect2($(modal_editar_pet), 'select[name="agendamento_raca_id"]');
  setAgendamentoPelagemSelect2();

 if (data.pet.especie_id) {
  const option = new Option(data.pet.especie, data.pet.especie_id, true, true);
  $('select[name="agendamento_especie_id"]').append(option).trigger('change.select2');
 }
 if (data.pet.raca_id) {
  const option = new Option(data.pet.raca, data.pet.raca_id, true, true)
  $('select[name="agendamento_raca_id"]').append(option).trigger('change.select2');
 }
 if (data.pet.pelagem_id) {
  const option = new Option(data.pet.pelagem, data.pet.pelagem_id, true, true)
  $('select[name="agendamento_pelagem_id"]').append(option).trigger('change.select2');
 }

  // Eventos dos botões de adicionar uma nova entidade

  modal_editar_pet.querySelector('#btn-agendamento-nova-especie').onclick = () => handleModalNovaEspecie();
  modal_editar_pet.querySelector('#btn-agendamento-nova-raca').onclick = () => handleModalNovaRaca();
  modal_editar_pet.querySelector('#btn-agendamento-nova-pelagem').onclick = () => handleModalNovaEspecie();

  modal_editar_pet = bootstrap.Modal.getOrCreateInstance(modal_editar_pet); 

  setTimeout(() => {
    modal_editar_pet.show();
  }, 100);
}

/**
 * Configura o select2 de pelagem do modal de edição de pet
*/
function setAgendamentoPelagemSelect2() {
  $('select[name="agendamento_pelagem_id"]').select2({
      language: 'pt-BR',
      placeholder: 'Digite para buscar a pelagem',

      ajax: {
          cache: true,
          url: path_url + 'api/animais/pelagens',
          dataType: 'json',
          data: function (params) {
              var query = {
                  pesquisa: params.term,
                  empresa_id: $('#empresa_id').val(),
              };
              return query;
          },
          processResults: function (response) {
              var results = [];
              $.each(response.data, function (i, v) {
                  var o = {};
                  o.id = v.id;
                  o.text =
                      v.nome;
                  o.value = v.id;
                  
                  results.push(o);
              });
              return {
                  results: results,
              };
          },
      },
  });
}

/**
 * Configura os recursos do modal de nova espécie
 * como os seus campos, envio e navegação
*/
function handleModalNovaEspecie() {
  let modal_editar_pet = document.getElementById('editar_agendamento_pet');
  modal_editar_pet = bootstrap.Modal.getOrCreateInstance(modal_editar_pet);

  modal_editar_pet.hide();  

  let modal_nova_especie_el = document.getElementById('modal_especie');
  let modal_nova_especie = bootstrap.Modal.getOrCreateInstance(modal_nova_especie_el);

  // Configuração do select de espécie

  $(modal_nova_especie_el).find('input').val('');
  $(modal_nova_especie_el).find('input').removeClass('is-valid');
  $(modal_nova_especie_el).find('input').removeClass('is-invalid');
  $(modal_nova_especie_el).find('input').tooltip('dispose');

  // Navegação entre os modais caso o modal de nova espécie seja fechado

  $(modal_nova_especie_el).off('hidden.bs.modal').on('hidden.bs.modal', () => {
    modal_editar_pet.show();
  });

  // Evento de envio do formulário

  $('.btn-store-especie').off('click').on('click', function() {
    let item = {
        nome: $('#inp-nome_especie').val(),
        empresa_id: $('#empresa_id').val(),
    };

    $.post(path_url + 'api/animais/store-especie', item)
    .done((result) => {
        let select = $('#inp-agendamento_especie_id');
        var newOption = new Option(result.nome, result.id, true, true);

        select.append(newOption).trigger('change');
        $('#inp-agendamento_especie_id').val(result.id).trigger('change');

        Swal.fire({
            icon: 'success',
            title: 'Espécie cadastrada com sucesso',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
          modal_nova_especie.hide();
        });
    })
    .fail((err) => {
        new swal('Ops...', 'Já existe uma espécie com este nome', 'alert');
    });
  })  

  modal_nova_especie.show();
}

/**
 * Configura os recursos do modal de nova raça
 * como os seus campos, envio e navegação
 * levando em conta também a espécie selecionada
*/
function handleModalNovaRaca() {
  let modal_editar_pet = document.getElementById('editar_agendamento_pet');
  modal_editar_pet = bootstrap.Modal.getOrCreateInstance(modal_editar_pet);

  // Valores do campo de espécie caso ele esteja preenchido

  const especie_id = $('select[name="agendamento_especie_id"]').val();
  const especie_label = $('select[name="agendamento_especie_id"]').find('option:selected').text();

  modal_editar_pet.hide();  

  let modal_nova_raca_el = document.getElementById('modal_raca');
  let modal_nova_raca = bootstrap.Modal.getOrCreateInstance(modal_nova_raca_el);

  // Configuração do select de espécie

  cleanSelect2Artifacts($(modal_nova_raca_el), 'select[name="especie_id"]');

  setAgendamentoEspecieSelect2($(modal_nova_raca_el));

  if (especie_id && especie_label) {
    const option = new Option(especie_label, especie_id, true, true);
    $(modal_nova_raca_el).find('select[name="especie_id"]').append(option).trigger('change.select2');
  }

  $(modal_nova_raca_el).find('input').val('');
  $(modal_nova_raca_el).find('input').removeClass('is-valid');
  $(modal_nova_raca_el).find('input').removeClass('is-invalid');
  $(modal_nova_raca_el).find('input').tooltip('dispose');

  // Navegação entre os modais caso o modal de nova raça seja fechado

  modal_nova_raca_el.addEventListener('hidden.bs.modal', () => {
    modal_editar_pet.show();
  });

  // Evento de envio do formulário

  $('.btn-store-raca').off('click').on('click', function() {
    let item = {
        nome: $('#inp-nome_raca').val(),
        especie_id: $('#inp-especie_id').val(),
        empresa_id: $('#empresa_id').val(),
    };

    $.post(path_url + 'api/animais/store-raca', item)
      .done((result) => {
          let select = $('#inp-agendamento_raca_id');
          var newOption = new Option(result.nome, result.id, true, true);

          select.append(newOption).trigger('change');
          $('#inp-agendamento_raca_id').val(result.id).trigger('change');

          Swal.fire({
              icon: 'success',
              title: 'Raça cadastrada com sucesso',
              showConfirmButton: false,
              timer: 1500
          }).then(() => {
            modal_nova_raca.hide();
          });
      })
      .fail((err) => {
          new swal('Ops...', 'Já existe uma raça com este nome', 'error');
      });
  })  

  modal_nova_raca.show();
}

/**
 * Configura os recursos do modal de nova pelagem
 * como os seus campos, envio e navegação
*/
function handleModalNovaEspecie() {
  let modal_editar_pet = document.getElementById('editar_agendamento_pet');
  modal_editar_pet = bootstrap.Modal.getOrCreateInstance(modal_editar_pet);

  modal_editar_pet.hide();  

  let modal_nova_pelagem_el = document.getElementById('modal_pelagem');
  let modal_nova_pelagem = bootstrap.Modal.getOrCreateInstance(modal_nova_pelagem_el);

  // Configuração do select de espécie

  cleanSelect2Artifacts($(modal_nova_pelagem_el), 'select[name="especie_id"]');

  setAgendamentoEspecieSelect2($(modal_nova_pelagem_el));

  $(modal_nova_pelagem_el).find('input').val('');
  $(modal_nova_pelagem_el).find('input').removeClass('is-valid');
  $(modal_nova_pelagem_el).find('input').removeClass('is-invalid');
  $(modal_nova_pelagem_el).find('input').tooltip('dispose');

  // Navegação entre os modais caso o modal de nova pelagem seja fechado

  modal_nova_pelagem_el.addEventListener('hidden.bs.modal', () => {
    modal_editar_pet.show();
  });

  // Evento de envio do formulário

  $('.btn-store-pelagem').off('click').on('click', function() {
    let item = {
        nome: $('#inp-nome_pelagem').val(),
        empresa_id: $('#empresa_id').val(),
    };

    $.post(path_url + 'api/animais/store-pelagem', item)
    .done((result) => {
      let select = $('#inp-agendamento_pelagem_id');
      var newOption = new Option(result.nome, result.id, true, true);

      select.append(newOption).trigger('change');
      $('#inp-agendamento_pelagem_id').val(result.id).trigger('change');

      Swal.fire({
          icon: 'success',
          title: 'Pelagem cadastrada com sucesso',
          showConfirmButton: false,
          timer: 1500
      }).then(() => {
        modal_nova_pelagem.hide();
      });
    })
    .fail((err) => {
      new swal('Ops...', 'Já existe uma pelagem com este nome', 'alert');
    });
  })  

  modal_nova_pelagem.show();
}

/**
 * Manipula o modal de checklist do petshop conforme a opção selecionada
 * e as outras informações do agendamento
 *
 * @param {string} modulo Módulo de serviço a ser referenciado
 * @param {object} data Informações do agendamento
 * @param {number} reserva_id ID da reserva
 * @param {string} tipo_checklist Tipo do checklist (entrada ou saída)
 * @param {boolean} only_view Define se o modal será apenas para visualização
 * 
 */
function handleChecklistModal(modulo, data, reserva_id, tipo_checklist, only_view = false) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');
  if (!modal_agendamento_el) return;

  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide();

  let modal_checklist = document.getElementById('checklist_petshop');

  // Título do modal

  let tipo_checklist_label = '';

  if (modulo === 'HOTEL') {
    switch (tipo_checklist) {
      case 'entrada':
        tipo_checklist_label = 'Checklist de Check in';
      break;
      case 'saida':
        tipo_checklist_label = 'Checklist de Check out';
      break;
    }
  } else if (modulo === 'CRECHE') {
    switch (tipo_checklist) {
      case 'entrada':
        tipo_checklist_label = 'Checklist de Entrada';
      break;
      case 'saida':
        tipo_checklist_label = 'Checklist de Saída';
      break;
    }
  }

  modal_checklist.querySelector('.modal-title').innerHTML = `
    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />  
    ${tipo_checklist_label}
    <i class="ri-check-double-line"></i>
  `;

  // Preenchimento das informações do pet

  let pet_sexo_label = '';
  if (data.pet.sexo) {
    switch (data.pet.sexo) {
      case 'M':
        pet_sexo_label = 'Macho';
      break;
      case 'F':
        pet_sexo_label = 'Fêmea';
      break;
    }
  }

  modal_checklist.querySelector('#pet_nome').innerText = data.pet.nome ?? '--';
  modal_checklist.querySelector('#pet_especie').innerText = data.pet.especie ?? '--';
  modal_checklist.querySelector('#pet_raca').innerText = data.pet.raca ?? '--';
  modal_checklist.querySelector('#pet_peso').innerText = data.pet.peso ?? '--';
  modal_checklist.querySelector('#animal_idade').innerText = data.pet.idade ?? '--';
  modal_checklist.querySelector('#pet_sexo').innerText = pet_sexo_label ?? '--';
  modal_checklist.querySelector('#pet_porte').innerText = data.pet.porte ?? '--';
  modal_checklist.querySelector('#pet_chip').innerText = data.pet.chip ?? '--';

  // Manipulação do modal de edição de pet

  const edit_pet_btn = modal_checklist.querySelector('#edit-pet-checklist-btn');

  edit_pet_btn.onclick = () => handleEditPetFromChecklistModal(modulo, data.pet.id, data);


  // Configuração do modo apenas visualização

  const checklist = data.checklists.find(el => el.tipo === tipo_checklist);

  setAnexosForChecklist(checklist);

  if (only_view) {
    modal_checklist.querySelector('#edit-pet-checklist-btn').classList.add('d-none');
    modal_checklist.querySelector('textarea').setAttribute('disabled', true);

    const textarea = modal_checklist.querySelector('textarea');

    textarea.value = checklist && checklist.checklist.texto_checklist ? checklist.checklist.texto_checklist : '';

    modal_checklist.querySelector('.add-image-btn').classList.add('d-none');

    modal_checklist.querySelectorAll('.remove-image-btn').forEach(btn => {
      btn.classList.add('d-none');
    });

    modal_checklist.querySelectorAll('.btn-file').forEach(btn => {
      btn.classList.add('d-none');
    });

    modal_checklist.querySelector('#btn-print').classList.add('d-none');
    modal_checklist.querySelector('#btn-save').classList.add('d-none');

    modal_checklist.querySelector('#go-edit-btn').classList.remove('d-none');
  } else {
    modal_checklist.querySelector('#edit-pet-checklist-btn').classList.remove('d-none');

    const textarea = modal_checklist.querySelector('textarea');

    const tiny_instance = tinymce.get(textarea.id);
    if (tiny_instance) {
      tiny_instance.mode.set('design');
    } else {
      textarea.removeAttribute('disabled');
    }

    textarea.value = checklist && checklist.checklist.texto_checklist ? checklist.checklist.texto_checklist : '';

    textarea.removeAttribute('disabled');

    if ((checklist && checklist.anexos && checklist.anexos < 12) || !checklist || !checklist.anexos) {
      modal_checklist.querySelector('.add-image-btn').classList.remove('d-none');
    }

    modal_checklist.querySelectorAll('.remove-image-btn').forEach(btn => {
      btn.classList.remove('d-none');
    });

    modal_checklist.querySelectorAll('.btn-file').forEach(btn => {
      btn.classList.remove('d-none');
    });

    modal_checklist.querySelector('#btn-print').classList.remove('d-none');
    modal_checklist.querySelector('#btn-save').classList.remove('d-none');

    modal_checklist.querySelector('#go-edit-btn').classList.add('d-none');
  }

  modal_checklist.querySelector('#go-edit-btn').onclick = () => handleChecklistModal(modulo, data, reserva_id, tipo_checklist, false);

  modal_checklist.setAttribute('data-modulo', modulo);
  modal_checklist.setAttribute('data-reserva-id', reserva_id);
  modal_checklist.setAttribute('data-tipo-checklist', tipo_checklist);
  modal_checklist.setAttribute('data-checklist-id', checklist ? checklist.id : null);

  modal_checklist = bootstrap.Modal.getOrCreateInstance(modal_checklist);
  
  setTimeout(() => {
    modal_checklist.show();
  }, 100);
}

/**
 * Manipula e preenche as informações do modal de edição de pet
 * a partir do modal de checklist
 *
 * @param {string} modulo Módulo de serviço a ser referenciado
 * @param {number} pet_id ID do pet
 * @param {object} data Informações do pet
 * 
 */
function handleEditPetFromChecklistModal(modulo, pet_id, data) {
  const modal_checklist_el = document.getElementById('checklist_petshop');
  if (!modal_checklist_el) return;

  const modal_checklist = bootstrap.Modal.getOrCreateInstance(modal_checklist_el);
  modal_checklist.hide();

  let modal_edit_pet_el = document.getElementById('modal_editar_pet_checklist');  
  let modal_edit_pet = bootstrap.Modal.getOrCreateInstance(modal_edit_pet_el);

  modal_edit_pet_el.setAttribute('data-modulo', modulo);
  modal_edit_pet_el.setAttribute('data-pet-id', pet_id);

  // Preechimento e configuração dos campos do modal de edição de pet

  setAgendamentoEspecieSelect2($(modal_edit_pet_el));
  setAgendamentoRacaSelect2($(modal_edit_pet_el));


  $(modal_edit_pet_el).find('#inp-edit_animal_id').val(data.pet.id).trigger('change');
  $(modal_edit_pet_el).find('input[name="nome"]').val(data.pet.nome).trigger('change');
  $(modal_edit_pet_el).find('input[name="idade"]').val(data.pet.idade).trigger('change');
  $(modal_edit_pet_el).find('input[name="peso"]').val(data.pet.peso).trigger('change');
  $(modal_edit_pet_el).find('select[name="sexo"]').val(data.pet.sexo).trigger('change');
  $(modal_edit_pet_el).find('input[name="porte"]').val(data.pet.porte).trigger('change');
  $(modal_edit_pet_el).find('input[name="chip"]').val(data.pet.chip).trigger('change');

  if (data.pet.especie_id) {
    const option = new Option(data.pet.especie, data.pet.especie_id, true, true);
    $(modal_edit_pet_el).find('select[name="especie_id"]').append(option).trigger('change.select2');
  }

  if (data.pet.raca_id) {
    const option = new Option(data.pet.raca, data.pet.raca_id, true, true);
    $(modal_edit_pet_el).find('select[name="raca_id"]').append(option).trigger('change.select2');
  }

  setTimeout(() => {
    modal_edit_pet.show();
  }, 100);

  modal_edit_pet_el.removeEventListener('hidden.bs.modal', () => {
    modal_checklist.show();
  });

  modal_edit_pet_el.addEventListener('hidden.bs.modal', () => {
    $(modal_edit_pet_el).find('input, select').val('').trigger('change');
    $(modal_edit_pet_el).find('input, select').removeClass('is-invalid').removeClass('is-valid').tooltip('dispose');

    $(modal_edit_pet_el).find('select.select2').each(function () {
      $(this).val(null).trigger('change');
      $(this).find('.select2-selection--single').removeClass('select2-valid');
      $(this).find('.select2-selection--single').removeClass('select2-invalid');
    });

    modal_checklist.show();
  });
}

/**
 * Configura o select2 de espécie dentro do modal de edição de pet
 * 
 * @param {Jquery} modal Modal onde o select2 será inicializado
 */
function setAgendamentoEspecieSelect2(modal, query_element = null) {
  $(query_element ? query_element : 'select[name="especie_id"]').select2({
      language: 'pt-BR',
      placeholder: 'Digite para buscar a espécie',
      dropdownParent: modal ? modal : $(document.body),
      ajax: {
          cache: true,
          url: path_url + 'api/animais/especies',
          dataType: 'json',
          data: function (params) {
              var query = {
                  pesquisa: params.term,
                  empresa_id: $('#empresa_id').val(),
              };
              return query;
          },
          processResults: function (response) {
              var results = [];
              $.each(response.data, function (i, v) {
                  var o = {};
                  o.id = v.id;
                  o.text =
                      v.nome;
                  o.value = v.id;
                  
                  results.push(o);
              });
              return {
                  results: results,
              };
          },
      },
  }).on('select2:select', function (e) {
      $('select[name="raca_id"]').val(null).trigger('change');
      $('select[name="agendamento_raca_id"]').val(null).trigger('change');
      setAgendamentoRacaSelect2(modal);
  }); 
}

/**
 * Configura o select2 de raça dentro do modal de edição de pet
 * 
 * @param {Jquery} modal Modal onde o select2 será inicializado
 */
function setAgendamentoRacaSelect2(modal, element_query = null) {
  $(element_query ? element_query : 'select[name="raca_id"]').select2({
        language: 'pt-BR',
        placeholder: 'Digite para buscar a raça',
        dropdownParent: modal ? modal : $(document.body),
        ajax: {
            cache: true,
            url: path_url + 'api/animais/racas',
            dataType: 'json',
            data: function (params) {
                var query = {
                    pesquisa: params.term,
                    especie_id: $('select[name="especie_id"]').val() || $('select[name="agendamento_especie_id"]').val(),
                    empresa_id: $('#empresa_id').val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];
                $.each(response.data, function (i, v) {
                    var o = {};
                    o.id = v.id;
                    o.text =
                        v.nome;
                    o.value = v.id;
                    
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    });
}

function setAnexosForChecklist(data) {
  const checklist_modal = document.getElementById('checklist_petshop');
  if (!checklist_modal) return;

  const anexos_container = checklist_modal.querySelector('div[name="anexo-container"]');
  const anexo_template = checklist_modal.querySelector('div[name="anexo-block"]').cloneNode(true);
  const add_anexo_btn = checklist_modal.querySelector('.add-image-btn').cloneNode(true);

  anexos_container.innerHTML = '';

  if (data && data.checklist.anexos && data.checklist.anexos.length > 0) {
    data.checklist.anexos.forEach(anexo => {
      const anexo_clone = anexo_template.cloneNode(true);

      anexo_clone.querySelector('input[name="anexos_url[]"]').value = anexo;
      anexo_clone.querySelector('img').setAttribute('src', anexo);
      anexo_clone.querySelector('.remove-image-btn').style.display = 'inline-block';
      
      anexos_container.appendChild(anexo_clone);
    });

    anexos_container.appendChild(add_anexo_btn);
  } else {
    anexo_template.querySelector('input[name="anexos_url[]"]').value = '';
    anexo_template.querySelector('img').setAttribute('src', '/imgs/no-image.png');
    anexo_template.querySelector('.remove-image-btn').style.display = 'none';

    anexos_container.appendChild(anexo_template);
    anexos_container.appendChild(add_anexo_btn);
  }

  setActionsForAnexos();
}

function setActionsForAnexos() {
  $('div[name="anexo-container"]').ready(function () {
    /**
     * Adiciona o evento de clique aos botões de adicionar anexo
     * e manipula a imagem de pré-visualização. Além disso,
     * controla a exibição do botão de remover anexo e limita
     * o número máximo de anexos a 12.
    */
    function addAnexoHandler() {
        $('.btn-file').each(function () {
            let anx_block = $(this).closest('div[name="anexo-block"]');
            let container = anx_block.closest('div[name="anexo-container"]');

            $(this).off('click').on('click', function () {
                let input = anx_block.find('input[type="file"]');

                let img = input.siblings('div').find('img.mc__anexo');

                input.trigger('click');

                input.off('change').on('change', function () {
                    let file = this.files[0];

                    if (file) {
                        img.attr('src', URL.createObjectURL(file));

                        anx_block.find('.remove-image-btn').show();
                    }
                });
            })

            if (anx_block.find('img.mc__anexo').attr('src').includes('/imgs/no-image.png') && container.find('.btn-file').length <= 1) {
                anx_block.find('.remove-image-btn').hide();
            } else {
                anx_block.find('.remove-image-btn').show();
            }
        })  

        if ($('.mc__anexo').length >= 12) {
            $('.add-image-btn').hide();
        }
    }
    addAnexoHandler();

    /**
     * Faz a manipulação do evento de remover um anexo. 
     * Ele também prepara o campo para o backend do anexo removido.
    */
    function removeAnexoHandler() {
        $('.remove-image-btn').each(function () {

            $(this).off('click').on('click', function () {
                addAnexoHandler();

                let anx_block = $(this).closest('div[name="anexo-block"]');
                let container = anx_block.closest('div[name="anexo-container"]');

                let anx_to_remove_url = anx_block.find('img.mc__anexo').attr('src');
                if (!anx_to_remove_url.includes('/imgs/no-image.png') && !anx_to_remove_url.includes('blob:http')) {
                    let anx_to_remove_field = $('<input>', {
                        type: 'hidden',
                        name: 'anexos_to_remove[]',
                        value: anx_to_remove_url
                    });
                    $('div[name="anexo-container"]').append(anx_to_remove_field);
                }

                if (container.find('.mc__anexo').length <= 1) {
                    anx_block.find('input[type="file"]').val('');
                    anx_block.find('img.mc__anexo').attr('src', '/imgs/no-image.png');
                    $('.remove-image-btn').hide();
                    return;
                }

                $(this).closest('div[name="anexo-block"]').remove();

                if ($('.mc__anexo').length < 12) {
                    $('.add-image-btn').show();
                }
            })
        })
    }
    removeAnexoHandler();

    $('.add-image-btn').on('click', function () {
        let container = $(this).closest('div[name="anexo-container"]');
        if (container.find('.mc__anexo').last().attr('src').includes('/imgs/no-image.png')) {
            new swal(
                'Atenção',
                'Selecione uma imagem antes para adicionar mais um anexo',
                'warning'
            )
            return;
        }

        if (container.find('.mc__anexo').length >= 12) {
            new swal(
                'Atenção',
                'Você só pode inserir até 12 anexos',
                'error'
            )
            return;
        }

        let old_anx_block = container.find('div[name="anexo-block"]').last();
        let clone = $(old_anx_block).clone();

        clone.find('input[type="file"]').val('');
        clone.find('img.mc__anexo').attr('src', '/imgs/no-image.png');

        old_anx_block.after(clone);

        addAnexoHandler();
        removeAnexoHandler();
    })
  })
}

/**
 * Manipula o modal de plano, suas informações e sua navegação 
 * para outras informações
 * 
 * @param {*} data 
 * @param {*} reserva_id 
 */
async function handlePlanoModal (data, reserva_id) {
  const modal_agendamento_el = document.getElementById('handle_modal_agendamento');
  if (!modal_agendamento_el) return;

  const modal_agendamento = bootstrap.Modal.getInstance(modal_agendamento_el);
  modal_agendamento.hide();

  const modal_plano_el = document.getElementById('plano_petshop_info');
  if (!modal_plano_el) return;

  // Título do modal

  let tipo_periodo_label = '';

  switch (data.periodo_plano) {
    case 'dia':
      tipo_periodo_label = 'do Dia';
    break;
    case 'semana':
      tipo_periodo_label = 'da Semana';
    break;
    case 'mes':
      tipo_periodo_label = 'do Mês';
    break;
    case 'ano':
      tipo_periodo_label = 'do Ano';
    break;
  }

  modal_plano_el.querySelector('.modal-title').innerHTML = `
    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />  
      Agendamentos ${tipo_periodo_label} do Cliente <b>${data.cliente.razao_social}</b>
      <i class="ri-check-double-line"></i>
  `;

  // Informação do periodo abrangido pelo plano

  const data_entrada = new Date(convertPtDateToInternational(data.data_entrada) + ' ' + data.horario_entrada);
  
  let periodo_abrangido_label = '';
  switch (data.periodo_plano) {
    case 'dia':
      const formatted_data_entrada = `${data_entrada.getDate().toFixed(0).padStart(2, '0')}/${(data_entrada.getMonth() + 1).toFixed(0).padStart(2, '0')}/${data_entrada.getFullYear()}`;

      periodo_abrangido_label = `Agendamentos do dia ${formatted_data_entrada}`;
    break;
    case 'semana':
      const first_day_week = data_entrada.getDate() - data_entrada.getDay();
      const last_day_week = first_day_week + 6;

      const first_day_week_date = new Date(data_entrada.setDate(first_day_week));
      const last_day_week_date = new Date(data_entrada.setDate(last_day_week));

      const formatted_first_day_week_date = `${first_day_week_date.getDate().toFixed(0).padStart(2, '0')}/${(first_day_week_date.getMonth() + 1).toFixed(0).padStart(2, '0')}/${first_day_week_date.getFullYear()}`;
      const formatted_last_day_week_date = `${last_day_week_date.getDate().toFixed(0).padStart(2, '0')}/${(last_day_week_date.getMonth() + 1).toFixed(0).padStart(2, '0')}/${last_day_week_date.getFullYear()}`;

      periodo_abrangido_label = `Agendamentos da semana de ${formatted_first_day_week_date} a ${formatted_last_day_week_date}`;
    break;
    case 'mes':
      const first_day_month = new Date(data_entrada.getFullYear(), data_entrada.getMonth(), 1);
      const last_day_month = new Date(data_entrada.getFullYear(), data_entrada.getMonth() + 1, 0);

      const first_day_month_date = new Date(first_day_month.setDate(first_day_month.getDate()));
      const last_day_month_date = new Date(last_day_month.setDate(last_day_month.getDate()));

      const formatted_first_day_month_date = `${first_day_month_date.getDate().toFixed(0).padStart(2, '0')}/${(first_day_month_date.getMonth() + 1).toFixed(0).padStart(2, '0')}/${first_day_month_date.getFullYear()}`;
      const formatted_last_day_month_date = `${last_day_month_date.getDate().toFixed(0).padStart(2, '0')}/${(last_day_month_date.getMonth() + 1).toFixed(0).padStart(2, '0')}/${last_day_month_date.getFullYear()}`;

      periodo_abrangido_label = `Agendamentos do mês de ${formatted_first_day_month_date} a ${formatted_last_day_month_date}`;
    break;
    case 'ano':
      const first_day_year = new Date(data_entrada.getFullYear(), 0, 1);
      const last_day_year = new Date(data_entrada.getFullYear(), 11, 31);

      const first_day_year_date = new Date(first_day_year.setDate(first_day_year.getDate()));
      const last_day_year_date = new Date(last_day_year.setDate(last_day_year.getDate()));

      const formatted_first_day_year_date = `${first_day_year_date.getDate().toFixed(0).padStart(2, '0')}/${(first_day_year_date.getMonth() + 1).toFixed(0).padStart(2, '0')}/${first_day_year_date.getFullYear()}`;
      const formatted_last_day_year_date = `${last_day_year_date.getDate().toFixed(0).padStart(2, '0')}/${(last_day_year_date.getMonth() + 1).toFixed(0).padStart(2, '0')}/${last_day_year_date.getFullYear()}`;

      periodo_abrangido_label = `Agendamentos do ano de ${formatted_first_day_year_date} a ${formatted_last_day_year_date}`;
    break;
  }

  modal_plano_el.querySelector('#periodo-abrangido-label').innerHTML = periodo_abrangido_label;

  // Preenchimento dos agendamentos no conteúdo do modal

  const agendamentos = await getPlanoAgendamentos(data.plano, data.cliente.id, data.periodo_plano, data_entrada);

  const agendamentos_container = modal_plano_el.querySelector('#agendamento-plano-container');

  agendamentos_container.innerHTML = '';

  agendamentos.forEach((agendamento) => {
    let label_status = '';
    switch (agendamento.extendedProps.estado) {
      case 'agendado':
        label_status = 'AG';
        break;
      case 'em_andamento':
        label_status = 'EA';
        break;
      case 'concluido':
        label_status = 'CL';
        break;
      case 'cancelado':
        label_status = 'CC';
        break;
      case 'rejeitado':
        label_status = 'RJ';
        break;
      case 'pendente_aprovacao':
        label_status = 'AP';
        break;
      default:
        break;
    }

    let status_class = '';
    switch(agendamento.extendedProps.estado) {
      case 'agendado':
        status_class = 'estado-agendado-area';
        break;
      case 'em_andamento':
        status_class = 'estado-em-andamento-area';
        break;
      case 'concluido':
        status_class = 'estado-concluido-area';
        break;
      case 'cancelado':
        status_class = 'estado-cancelado-area';
        break;
      case 'rejeitado':
        status_class = 'estado-rejeitado-area';
        break;
      case 'pendente_aprovacao':
        status_class = 'estado-pendente-aprovacao-area';
        break;
    }

    let status_icon = '';
    switch(agendamento.extendedProps.estado) {
      case 'agendado':
        status_icon = '<i style="font-size: 20px" class="ri-calendar-event-line"></i>';
        break;
      case 'em_andamento':
        status_icon = '<i style="font-size: 20px" class="ri-hourglass-fill"></i>';
        break;
      case 'concluido':
        status_icon = '<i style="font-size: 20px" class="ri-calendar-check-fill"></i>';
        break;
      case 'cancelado':
        status_icon = '<i style="font-size: 20px" class="ri-close-circle-line"></i>';
        break;
      case 'rejeitado':
        status_icon = '<i style="font-size: 20px" class="ri-calendar-close-line"></i>';
        break;
      case 'pendente_aprovacao':
        status_icon = '<i style="font-size: 20px" class="ri-timer-fill"></i>';
        break;
    }

    const agendamento_card = 
    `
      <div
        class="card plano-agendamento-card text-start text-black row"
        style="border-left: 5px solid #56327A"
        data-id="${agendamento.id}"
        data-tipo-agendamento="${agendamento.extendedProps.modulo}"
      >
        <div 
          class="gap-1 day-status
            ${
              status_class
            }
          "
        >
            ${
              status_icon
            }                   
            <span class="text-uppercase fw-semibold">${label_status}</span> 
        </div>
        <div class="d-flex align-items-center" style="border-bottom: 2px solid #ccc">
          <div class="horario-container">
            <div class="d-flex flex-column align-items-center justify-content-center">
              ${agendamento.extendedProps.data_entrada}
              <span class="fw-semibold fs-4">${agendamento.extendedProps.horario_entrada}</span>
            </div>
            <div class="d-flex flex-column align-items-center justify-content-center">
              <div 
                class="connect-circle" 
                style="background-color: #56327A"
              ></div>
              <div 
                class="connect-line" 
                style="background-color: #56327A"
              ></div>
              <div 
                class="connect-circle" 
                style="background-color: #56327A"
              ></div>
            </div>
            <div class="d-flex flex-column align-items-center justify-content-center">
              ${agendamento.extendedProps.data_saida}
              <span class="fw-semibold fs-4">${agendamento.extendedProps.horario_saida}</span>
            </div>
          </div>
          <div class="d-flex flex-column gap-1 py-2" style="padding-right: 10px">
            <div class="pet-info">
              ${
                agendamento.extendedProps.quarto ?
                `
                  <div class="mb-2"><b>Quarto:</b> ${agendamento.extendedProps.quarto}</div>
                ` : 
                ''
              }
              ${
                agendamento.extendedProps.turma ?
                `
                  <div class="mb-2"><b>Turma:</b> ${agendamento.extendedProps.turma}</div>
                ` : 
                ''
              }
              <div>
                <b>Pet:</b>
                ${agendamento.extendedProps.pet.nome}
              </div>
              <div>
                <b>Raça:</b>
                ${agendamento.extendedProps.pet.raca ?? '--'}
              </div>
              <div>
                <b>Pelagem:</b>
                ${agendamento.extendedProps.pet.pelagem ?? '--'}
              </div>
              <div>
                <b>Porte:</b>
                ${agendamento.extendedProps.pet.porte ?? '--'} 
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-1">
              <div class="d-flex gap-1 align-items-end" style="max-width: 200px">
                <b>Cliente:</b>
                <small class="text-truncate d-block">${agendamento.extendedProps.cliente.razao_social ?? '--'}</small>
              </div>
              <div class="d-flex gap-1 align-items-end" style="max-width: 200px">
                <b>Colaborador:</b>
                <small class="text-truncate d-block">${agendamento.extendedProps.colaborador ?? '--'}</small>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-end align-items-center p-2">
          <button 
            class="btn btn-sm btn-agendamento-secondary"
            id="go-agendamento-${agendamento.id}"
          >
            <i class="ri-calendar-event-line"></i>
            Abrir agendamento
          </button>
        </div>
      </div>
    `;

    agendamentos_container.insertAdjacentHTML('beforeend', agendamento_card);

    agendamentos_container.querySelector(`#go-agendamento-${agendamento.id}`).onclick = () => {
      bootstrap.Modal.getOrCreateInstance(modal_plano_el).hide(); 

      setHandleModalAgendamento(agendamento);
    }
  });

  modal_plano_el.setAttribute('data-reserva-id', reserva_id);

  const modal_plano = bootstrap.Modal.getOrCreateInstance(modal_plano_el);
  modal_plano.show();
}

/**
 * Pega os agendamentos do plano de acordo com o período dele
 * 
 * @param {number} plano_id ID do plano
 * @param {number} cliente_id ID do cliente que está usando o plano
 * @param {string} periodo_plano Período do plano
 * @param {Date} data_entrada Data de entrada do agendamento aberto
 * 
 * @returns {Promise<object>} Agendamentos do plano
 */
async function getPlanoAgendamentos(plano_id, cliente_id, periodo_plano, data_entrada) {
  let data = [];

  let start_date = null;
  let end_date = null;

  switch(periodo_plano) {
    case 'dia':
      start_date = new Date(data_entrada.getFullYear(), data_entrada.getMonth(), data_entrada.getDate());
      end_date = new Date(data_entrada.getFullYear(), data_entrada.getMonth(), data_entrada.getDate(), 23, 59, 59);
    break;
    case 'semana':
      const first_day_week = new Date(data_entrada);
      first_day_week.setDate(data_entrada.getDate() - data_entrada.getDay());
      
      const last_day_week = new Date(first_day_week);
      last_day_week.setDate(first_day_week.getDate() + 6);

      start_date = new Date(first_day_week.getFullYear(), first_day_week.getMonth(), first_day_week.getDate());
      end_date = new Date(last_day_week.getFullYear(), last_day_week.getMonth(), last_day_week.getDate(), 23, 59, 59);
    break;  
    case 'mes':
      start_date = new Date(data_entrada.getFullYear(), data_entrada.getMonth(), 1);
      end_date = new Date(data_entrada.getFullYear(), data_entrada.getMonth() + 1, 0, 23, 59, 59);
    break;
    case 'ano':
      start_date = new Date(data_entrada.getFullYear(), 0, 1);
      end_date = new Date(data_entrada.getFullYear(), 11, 31, 23, 59, 59);
    break;
  }

  await $.ajax({
    url: path_url + 'api/agendamentos/plano-agendamentos',
    method: 'GET',
    data: {
      plano_id: plano_id,
      cliente_id: cliente_id,
      start_date: start_date.toISOString().split('T')[0],
      end_date: end_date.toISOString().split('T')[0]
    },
    dataType: 'json',
    success: function (response) {
      response.data.forEach(agendamento => {
        data.push(agendamento);
      })
    }
  })

  return data;
}

$(document).on('click', '.btn-add-estetica-modal-tr', function () {
    let $table = $('.table-modal-servicos-estetica');

    let isEmpty = false;
    $table.find('input, select').each(function () {
        if ((($(this).val() === '' || $(this).val() === null) &&
            $(this).attr('type') !== 'hidden' &&
            $(this).attr('type') !== 'file' &&
            !$(this).hasClass('ignore') &&
            !$(this).prop('disabled'))) {
            isEmpty = true;
        }
    });

    if (isEmpty) {
        new swal('Atenção', 'Preencha todos os campos antes de adicionar novos.', 'warning');
        return;
    }

    const $tr = $table.find('.dynamic-form').first();
    if ($tr.length === 0) return;
    $tr.find('select[name="reserva_servico_id[]"]').select2('destroy');
    const $clone = $tr.clone();
    $clone.find('input, select').val('');
    $table.append($clone);

    setTimeout(function () {
      setEsteticaServicosSelect2();
    }, 100);
});

$(document).on('click', '.btn-add-servico-extra-tr', function () {
    let $table = $('.table-modal-servicos-extras').first();

    let isEmpty = false;
    $table.find('input, select').each(function () {
        if ((($(this).val() === '' || $(this).val() === null) &&
            $(this).attr('type') !== 'hidden' &&
            $(this).attr('type') !== 'file' &&
            !$(this).hasClass('ignore') &&
            !$(this).prop('disabled'))) {
            isEmpty = true;
        }
    });

    if (isEmpty) {
        new swal('Atenção', 'Preencha todos os campos antes de adicionar novos.', 'warning');
        return;
    }

    const $tr = $table.find('.dynamic-form').first();
    if ($tr.length === 0) return;
    $tr.find('select[name="extra_servico_ids[]"]').select2('destroy');
    const $clone = $tr.clone();
    $clone.find('input, select').val('');
    $table.append($clone);

    setTimeout(function () {
      setExtraServicosSelect2();
    }, 100);
});

$(document).on('click', '.btn-add-produto', function () {
    let $table = $('.table-produtos-agendamento').first();

    let isEmpty = false;
    $table.find('input, select').each(function () {
        if ((($(this).val() === '' || $(this).val() === null) &&
            $(this).attr('type') !== 'hidden' &&
            $(this).attr('type') !== 'file' &&
            !$(this).hasClass('ignore') &&
            !$(this).prop('disabled'))) {
            isEmpty = true;
        }
    });

    if (isEmpty) {
        new swal('Atenção', 'Preencha todos os campos antes de adicionar novos.', 'warning');
        return;
    }

    const $tr = $table.find('.dynamic-form').first();
    if ($tr.length === 0) return;
    $tr.find('select[name="agendamento_produto_id[]"]').select2('destroy');

    const $clone = $tr.clone();
    $clone.find('input, select').removeClass('is-invalid').removeClass('is-valid').tooltip('dispose');
    $clone.find('input, select').val('');
    
    $table.append($clone);

    setTimeout(function () {
      setProdutosSelect2();
    }, 100);
});

$(document).on('click', '.pethsop-modal-btn-remove-tr', function (e) {
    e.preventDefault();
    const $btn = $(this);
    Swal.fire({
      title: 'Você tem certeza?',
      text: 'Deseja remover esse item mesmo?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sim, remover',
      cancelButtonText: 'Cancelar',
    }).then((willDelete) => {
        if (willDelete.isConfirmed) {
            const $tbody = $btn.closest('tbody');
            if ($tbody.find('tr.dynamic-form').length > 1) {
                $btn.closest('tr').remove();
            } else {
                $btn.closest('tr').find('input, select').val(null).trigger('change');
                $btn.closest('tr').find('input, select').removeClass('is-invalid').removeClass('is-valid').tooltip('dispose');
                $btn.closest('tr').find('.select2').find('.select2-selection--single').removeClass('select2-invalid').removeClass('select2-valid');
                $btn.closest('tr').find('.select2').tooltip('dispose');
            }

            handleEnderecoClienteModal();
            calcTotalExtraServicos();
            calcTotalProdutos();
        } 
    });
});

// Eventos de envio dos formulários de edição de reservas

document.querySelector('#submit_update_reserva_hotel').addEventListener('click', function (e) {
  e.preventDefault();

  const edit_hotel_modal = document.querySelector('#edit_reserva_hotel');

  const data_entrada_field = edit_hotel_modal.querySelector('input[name="reserva_checkin"]');
  const horario_entrada_field = edit_hotel_modal.querySelector('input[name="reserva_timecheckin"]');
  const data_saida_field = edit_hotel_modal.querySelector('input[name="reserva_checkout"]');
  const horario_saida_field = edit_hotel_modal.querySelector('input[name="reserva_timecheckout"]');
  const reserva_tempo_execucao = edit_hotel_modal.querySelector('input[name="reserva_tempo_execucao"]');

  const input_values = {
    data_entrada: {
      element: data_entrada_field,
      value: data_entrada_field.value
    },
    horario_entrada: {
      element: horario_entrada_field,
      value: horario_entrada_field.value
    },
    data_saida: {
      element: data_saida_field,
      value: data_saida_field.value
    },
    horario_saida: {
      element: horario_saida_field,
      value: horario_saida_field.value
    },
    tempo_execucao_reserva: reserva_tempo_execucao.value
  }

  if (
    !addClassRequired('#form-edit-reserva-hotel', true) ||
    !validateDatesForModal(input_values, 'HOTEL') ||
    !validateQuartoIsFree(edit_hotel_modal.getAttribute('data-reserva-id'))
  ) return;
 
  let formDataArray = $('#form-edit-reserva-hotel').serializeArray();

  let filteredFormData = formDataArray.filter(function(field) {
      return field.value.trim() !== '';
  });

  let form_data = $.param(filteredFormData);


  $.ajax({
    url: path_url + 'api/hoteis/update-reserva/' + edit_hotel_modal.getAttribute('data-reserva-id'),
    method: 'PUT',
    data: form_data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Reserva atualizada com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });

        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar reserva',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar a reserva.'
      });
    }
  })
});

document.querySelector('#submit_update_reserva_creche').addEventListener('click', function (e) {
  e.preventDefault();

  const edit_creche_modal = document.querySelector('#edit_reserva_creche');

  const data_entrada_field = edit_creche_modal.querySelector('input[name="reserva_data_entrada"]');
  const horario_entrada_field = edit_creche_modal.querySelector('input[name="reserva_horario_entrada"]');
  const data_saida_field = edit_creche_modal.querySelector('input[name="reserva_data_saida"]');
  const horario_saida_field = edit_creche_modal.querySelector('input[name="reserva_horario_saida"]');
  const reserva_tempo_execucao = edit_creche_modal.querySelector('input[name="reserva_tempo_execucao"]');

  const input_values = {
    data_entrada: {
      element: data_entrada_field,
      value: data_entrada_field.value
    },
    horario_entrada: {
      element: horario_entrada_field,
      value: horario_entrada_field.value
    },
    data_saida: {
      element: data_saida_field,
      value: data_saida_field.value
    },
    horario_saida: {
      element: horario_saida_field,
      value: horario_saida_field.value
    },
    tempo_execucao_reserva: reserva_tempo_execucao.value
  }

  if (
    !addClassRequired('#form-edit-reserva-creche', true) ||
    !validateDatesForModal(input_values, 'CRECHE') ||
    !validateTurmaIsFree(edit_creche_modal.getAttribute('data-reserva-id'))
  ) return;

  let formDataArray = $('#form-edit-reserva-creche').serializeArray();

  let filteredFormData = formDataArray.filter(function(field) {
      return field.value.trim() !== '';
  });

  let form_data = $.param(filteredFormData);

  $.ajax({
    url: path_url + 'api/creches/update-reserva/' + edit_creche_modal.getAttribute('data-reserva-id'),
    method: 'PUT',
    data: form_data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Reserva atualizada com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });

        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar reserva',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar a reserva.'
      });
    }
  })
});

document.querySelector('#submit_update_reserva_estetica').addEventListener('click', function (e) {
  e.preventDefault();

  const edit_estetica_modal = document.querySelector('#edit_reserva_estetica');

  const servicos = edit_estetica_modal.querySelectorAll('select[name="servico_id[]"]');
  let servicos_val = [];

  servicos.forEach(servico => {

    if (servico.value) {
      servicos_val.push(servico.value);
    }
  });

  if (servicos_val.length <= 0) {
    Swal.fire({
      icon: 'error',
      title: 'Erro ao atualizar reserva',
      text: 'Selecione pelo menos um serviço'
    });

    return;
  }

  if (
    !addClassRequired('#form-edit-reserva-estetica', true) ||
    !validateDataAndHorarioFromEsteticaAgendamento()
  ) return;

  let formDataArray = $('#form-edit-reserva-estetica').serializeArray();
  let filteredFormData = formDataArray.filter(function(field) {
      return field.value.trim() !== '';
  });

  let form_data = $.param(filteredFormData);

  $.ajax({
    url: path_url + 'api/esteticas/update-reserva/' + edit_estetica_modal.getAttribute('data-reserva-id'),
    method: 'PUT',
    data: form_data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Reserva atualizada com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });

        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar reserva',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar a reserva.'
      });
    }
  })  
});

document.querySelector('#submit_update_servicos_extras').addEventListener('click', function (e) {
  e.preventDefault();

  const handle_servicos_modal = document.querySelector('#servicos_extras_petshop');

  const servicos = handle_servicos_modal.querySelectorAll('select[name="extra_servico_ids[]"]');
  const data_entrada_reserva = 
    convertPtDateToInternational($('#data-entrada-area').text()) + ' ' + $('#horario-entrada-area').text();
  const data_saida_reserva = 
    convertPtDateToInternational($('#data-saida-area').text()) + ' ' + $('#horario-saida-area').text();

  if (servicos.length > 0) {
    for (const servico of servicos) {
      const row = $(servico.closest('tr'));
      if (!validateServicoExtraDate(row, data_entrada_reserva, data_saida_reserva)) {
        return;
      }
    }
  }

  const modulo = handle_servicos_modal.getAttribute('data-modulo');
  let modulo_end_point = '';
  switch (modulo) {
    case 'HOTEL': 
      modulo_end_point = 'hoteis';
      break;

    case 'CRECHE': 
      modulo_end_point = 'creches';
      break;
  }

  const reserva_id = handle_servicos_modal.getAttribute('data-reserva-id');

  let formDataArray = $('#form-handle-extra-servicos-petshop').serializeArray();
  let filteredFormData = formDataArray.filter(function(field) {
      return field.value.trim() !== '';
  });

  let form_data = $.param(filteredFormData);

  $.ajax({
    url: `${path_url}api/${modulo_end_point}/update-servicos-extras/${reserva_id}`,
    method: 'PUT',
    data: form_data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Serviços atualizados com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });

        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar serviços',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar os serviços.'
      });
    }
  })  
});

document.querySelector('#submit_update_produtos').addEventListener('click', function (e) {
  e.preventDefault();

  const handle_produtos_modal = document.querySelector('#produtos_petshop');

  const produtos = handle_produtos_modal.querySelectorAll('select[name="agendamento_produto_id[]"]');

  if (produtos.length > 0) {
    for (const produto of produtos) {
      const row = produto.closest('tr');

      if (!validateProduto(row)) {
        return;
      }
    }
  }

  const modulo = handle_produtos_modal.getAttribute('data-modulo');
  let modulo_end_point = '';
  switch (modulo) {
    case 'HOTEL': 
      modulo_end_point = 'hoteis';
      break;

    case 'CRECHE': 
      modulo_end_point = 'creches';
      break;

    case 'ESTETICA': 
      modulo_end_point = 'creches';
      break;
  }

  const reserva_id = handle_produtos_modal.getAttribute('data-reserva-id');

  let formDataArray = $('#form-handle-produtos-petshop').serializeArray();
  let filteredFormData = formDataArray.filter(function(field) {
      return field.value.trim() !== '';
  });

  let form_data = $.param(filteredFormData);

  $.ajax({
    url: `${path_url}api/${modulo_end_point}/update-produtos/${reserva_id}`,
    method: 'PUT',
    data: form_data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Produtos atualizados com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });

        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar produtos',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar os produtos.'
      });
    }
  })  
});
 
document.querySelector('#submit_update_frete').addEventListener('click', function (e) {
  e.preventDefault();

  const frete_modal = document.querySelector('#servico_frete_petshop');

  if (frete_modal.getAttribute('data-has-address') && frete_modal.getAttribute('data-has-address') === 'false') {
    Swal.fire({
      title: 'Determine o endereço do frete',
      text: 'Para aplicar o frete, é necessário determinar o endereço no qual o frete será feito.',
      icon: 'warning',
      confirmButtonText: 'Ok'
    });

    return;
  }

  const modulo = frete_modal.getAttribute('data-modulo');
  let modulo_end_point = '';
  switch (modulo) {
    case 'HOTEL': 
      modulo_end_point = 'hoteis';
      break;

    case 'CRECHE': 
      modulo_end_point = 'creches';
      break;

    case 'ESTETICA': 
      modulo_end_point = 'esteticas';
      break;
  }

  const reserva_id = frete_modal.getAttribute('data-reserva-id');

  let formDataArray = $('#form-handle-servicos-frete-petshop').serializeArray();
  let filteredFormData = formDataArray.filter(function(field) {
      return field.value.trim() !== '';
  });

  let form_data = $.param(filteredFormData);

  $.ajax({
    url: `${path_url}api/${modulo_end_point}/update-frete/${reserva_id}`,
    method: 'PUT',
    data: form_data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Frete atualizado com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });

        return;
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar frete',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar o frete.'
      });
    }
  })  
});

document.querySelector('.submit-editar-cliente-btn').addEventListener('click', function (e) {
  e.preventDefault();

  // Validação do campos antes do envio

  if (!addClassRequired('#modal-editar-cliente', true)) return;

  const edit_cliente_modal = document.querySelector('#editar_agendamento_cliente');

  if (
    edit_cliente_modal.querySelector('input[name="cpf"]') &&
    edit_cliente_modal.querySelector('input[name="cpf"]').value &&
    !edit_cliente_modal.querySelector('input[name="cpf"]').classList.contains('ignore')
  ) {
    const input = edit_cliente_modal.querySelector('input[name="cpf"]');
    const digits = input.value.replace(/\D/g, '');
    let res = false;

    if (digits.length !== 11) {
      Swal.fire({
        icon: 'error',
        title: 'CPF inválido',
        text: 'O CPF deve conter 11 dígitos.'
      })

      input.classList.add('is-invalid');
      initializeTooltip(input, 'CPF inválido.');

      return;
    }

    if (digits.length === 11) {
      // Função de validação da main.js
      
      res = validateCpf(digits);
    }

    if (!res) {
      input.classList.add('is-invalid');
      initializeTooltip(input, 'CPF inválido.');

      Swal.fire({
        icon: 'error',
        title: 'CPF inválido',
        text: 'Verifique se você digitou o CPF corretamente.'
      });

      return;
    }
  }

  if (
    edit_cliente_modal.querySelector('input[name="cnpj"]') &&
    edit_cliente_modal.querySelector('input[name="cnpj"]').value &&
    !edit_cliente_modal.querySelector('input[name="cnpj"]').classList.contains('ignore')
  ) {
    const input = edit_cliente_modal.querySelector('input[name="cnpj"]');
    const digits = input.value.replace(/\D/g, '');
    let res = false;

    if (digits.length !== 14) {
      Swal.fire({
        icon: 'error',
        title: 'CNPJ inválido',
        text: 'O CNPJ deve conter 14 dígitos.'
      })

      input.classList.add('is-invalid');
      initializeTooltip(input, 'CNPJ inválido.');

      return;
    }

    if (digits.length === 14) {
      // Função de validação da main.js

      res = validateCnpj(digits);
    }

    if (!res) {
      input.classList.add('is-invalid');
      initializeTooltip(input, 'CNPJ inválido.');

      Swal.fire({
        icon: 'error',
        title: 'CNPJ inválido',
        text: 'Verifique se você digitou o CNPJ corretamente.'
      });

      return;
    }
  }

  const cliente_id = edit_cliente_modal.getAttribute('data-cliente-id');

  let formDataArray = $('#modal-editar-cliente').serializeArray();

  let filteredFormData = formDataArray.filter(function(field) {
    let el = $('[name="' + field.name + '"]'); 
    let is_ignored = el.hasClass('ignore');
    
    return !is_ignored;
  });

  filteredFormData.push({name: 'empresa_id', value: $('#empresa_id').val()});

  let form_data = $.param(filteredFormData);

  $.ajax({
    url: `${path_url}api/clientes/update/${cliente_id}`,
    method: 'PUT',
    data: form_data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Cliente atualizado com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Erro ao atualizar cliente',
          text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar o cliente.'
        });
      }

      return;
    },
    error: function (response) {
      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar cliente',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar o cliente.'
      });
    }
  })  
});

document.querySelector('#submit_endereco_cliente').addEventListener('click', function (e) {
  e.preventDefault();

  const frete_modal = document.querySelector('#servico_frete_petshop');
  const endereco_cliente_modal = document.querySelector('#modal_endereco_cliente');

  let is_valid = true;

  endereco_cliente_modal.querySelectorAll('input, select').forEach((input) => {
    if (input.hasAttribute('required')) {
      if (!input.value) {
        input.classList.add('is-invalid');
        initializeTooltip(input, 'Campo obrigatório.');

        is_valid = false;
        return;
      }
    }
  });

  if (!is_valid) {
    Swal.fire({
      icon: 'error',
      title: 'Erro ao atualizar endereço',
      text: 'Preencha todos os campos obrigatórios antes de prosseguir.'
    });

    return;
  };

  const servico_id = frete_modal.querySelector('select[name="servico_frete"]').value;
  const valor_servico = frete_modal.querySelector('input[name="servico_frete_valor"]').value;
  const agendamento_id = frete_modal.getAttribute('data-reserva-id');
  const cliente_id = frete_modal.getAttribute('data-cliente-id');
  const modulo = frete_modal.getAttribute('data-modulo');

  const data = {
    servico_id,
    valor_servico,
    agendamento_id,
    modulo,
    cliente_id,
    empresa_id: $('#empresa_id').val(),
    cep: endereco_cliente_modal.querySelector('input[name="cep"]').value,
    rua: endereco_cliente_modal.querySelector('input[name="rua"]').value,
    bairro: endereco_cliente_modal.querySelector('input[name="bairro"]').value,
    numero: endereco_cliente_modal.querySelector('input[name="numero"]').value,
    modal_cidade_id: endereco_cliente_modal.querySelector('select[name="modal_cidade_id"]').value,
    complemento: endereco_cliente_modal.querySelector('textarea[name="complemento"]').value,
  }

  $.ajax({
    url: `${path_url}api/agendamentos/update-endereco-frete`,
    method: 'POST',
    data: data,
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Endereço atualizado com sucesso',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        })
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Erro ao atualizar endereço',
          text: response.message ?? 'Ocorreu um erro desconhecido ao atualizar o endereço de frete.'
        });
      }
    },
    error: function (response) {
      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar endereço',
        text: response.message ?? 'Ocorreu um erro desconhecido ao atualizar o endereço de frete.'
      });
    }
  })
});

document.querySelector('.submit-editar-pet-btn').addEventListener('click', function (e) {
  e.preventDefault();

  if (!addClassRequired('#modal-editar-pet', true)) return

  const edit_pet_modal = document.querySelector('#editar_agendamento_pet');

  const pet_id = edit_pet_modal.getAttribute('data-pet-id');

  let formDataArray = $('#modal-editar-pet').serializeArray();

  formDataArray.push({name: 'id', value: pet_id});
  formDataArray.push({name: 'empresa_id', value: $('#empresa_id').val()});

  formDataArray = formDataArray.map(field => {
    switch (field.name) {
      case 'agendamento_especie_id':
        field.name = 'especie_id';
        break;
      case 'agendamento_raca_id':
        field.name = 'raca_id';
        break;
      case 'agendamento_pelagem_id':
        field.name = 'pelagem_id';
        break;
    }

    return field;
  });

  let form_data = $.param(formDataArray);

  $.ajax({
    url: `${path_url}api/animais/update`,
    method: 'PUT',
    data: form_data,
    success: function (response) {
      Swal.fire({
        icon: 'success',
        title: 'Pet atualizado com sucesso',
        showConfirmButton: false,
        timer: 1500
      }).then(() => {
        location.reload();
      });

      return;
    },
    error: function (response) {
      Swal.fire({
        icon: 'error',
        title: 'Erro ao atualizar pet',
        text: response.message ?? response.xhr.responseJSON.message ?? 'Ocorreu um erro desconhecido ao atualizar o pet.'
      });
    }
  })  
});

document.querySelectorAll('.submit-checklist-btn').forEach(
  (btn) => btn.addEventListener('click', function (e) {
    e.preventDefault();

    const go_print = btn.getAttribute('data-print');

    const checklist_modal = document.getElementById('checklist_petshop');
    if (!checklist_modal) return;

    const modulo = checklist_modal.getAttribute('data-modulo');
    const tipo_checklist = checklist_modal.getAttribute('data-tipo-checklist');
    const checklist_id = checklist_modal.getAttribute('data-checklist-id');
    const reserva_id = checklist_modal.getAttribute('data-reserva-id');
    const empresa_id = document.getElementById('empresa_id').value;
    const texto_checklist = checklist_modal.querySelector('textarea')

    const texto_checklist_value = tinymce.get(texto_checklist.id) ? tinymce.get(texto_checklist.id).getContent() : texto_checklist.value;
      
    let form_data = new FormData();

    form_data.append('modulo', modulo);
    form_data.append('tipo', tipo_checklist);
    form_data.append('checklist_id', checklist_id);
    form_data.append('reserva_id', reserva_id);
    form_data.append('empresa_id', empresa_id);
    form_data.append('texto_checklist', texto_checklist_value);
    form_data.append('go_print', go_print);
    
    $('input[name="anexos[]"]').each(function () {
      if (this.files.length > 0) {
        for (let i = 0; i < this.files.length; i++) {
          form_data.append('anexos[]', this.files[i]);
        }
      }
    });

    $('input[name="anexos_url[]"]').each(function () {
        form_data.append('anexos_url[]', this.value);
    });
    
    $('input[name="anexos_to_remove[]"]').each(function () {
      form_data.append('anexos_to_remove[]', this.value);
    });

    if (go_print) {
      Swal.fire({
        title: 'Tem certeza de que deseja imprimir?',
        text: "Ao imprimir todas as alterações feitas serão salvas",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, imprimir',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: path_url + 'api/petshop/checklist/update-or-create',
            method: 'POST',
            data: form_data,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success == true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Checklist atualizado com sucesso!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (response.print_url) {
                          window.open(response.print_url, '_blank');
                        }
                    });
                } else {
                  Swal.fire({
                      icon: 'error',
                      title: 'Erro ao atualizar checklist',
                      text: response.message ?? ''
                  });
                }
            },
            error: function (xhr) {
              let msg = 'Erro ao atualizar checklist.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                  msg = xhr.responseJSON.message;
              }
              Swal.fire({
                  icon: 'error',
                  title: 'Erro ao atualizar checklist.',
                  text: msg
              });
            }
          });
        } else {
          return;
        }
      })
    } else {
      $.ajax({
        url: path_url + 'api/petshop/checklist/update-or-create',
        method: 'POST',
        data: form_data,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success == true) {
                Swal.fire({
                    icon: 'success',
                    title: 'Checklist atualizado com sucesso!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao atualizar checklist',
                    text: response.message ?? ''
                });
            }
        },
        error: function (xhr) {
            let msg = 'Erro ao atualizar checklist.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Erro ao atualizar checklist.',
                text: msg
            });
        }
      });
    }
  }
));

/**
 * -- EM DESENVOLVIMENTO --
 * 
 * Manipula a paginação dos agendamentos pelo dia
 * de modo que apenas os 5 primeiros sejam exibidos
 * 
 * @param {*} day_element Elemento do dia
 * @returns 
 */
function handlePaginationForAgendamentos(day_element) {
  const events = day_element.querySelectorAll('.fc-daygrid-event');
  const agendamentos_pagination = 5;
  
  if (events.length > agendamentos_pagination) {
    const has_btn = day_element.querySelector('.show-more-btn');
    if (has_btn) return;

    events.forEach((event, index) => {
      if (index >= agendamentos_pagination) event.style.display = 'none';
    });

    const hidden_agendamentos_count = events.length - agendamentos_pagination;
    const plural = hidden_agendamentos_count > 1 ? 's' : '';

    const show_more_btn = document.createElement('button');
    show_more_btn.innerHTML = `<i class="ri-add-fill"></i> ${events.length - agendamentos_pagination} agendamento${plural}`;
    show_more_btn.classList.add('show-more-btn');

    show_more_btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      const hidden_events = Array.from(events).slice(agendamentos_pagination);
      const expanded = hidden_events[0].style.display !== 'none';

      if (expanded) {
        hidden_events.forEach(e => (e.style.display = 'none'));
        show_more_btn.innerHTML = `<i class="ri-add-fill"></i> ${events.length - agendamentos_pagination} agendamento${plural}`;
      } else {
        hidden_events.forEach(e => (e.style.display = 'block'));
        show_more_btn.innerHTML = 'Exibir menos';
      }
    });

    day_element.querySelector('.fc-daygrid-day-events')?.appendChild(show_more_btn);
  }
}

/**
 * Busca os agendamentos para a agenda conforme a data que ela está abrangendo 
 * e outros filtros auxiliares que estão selecionados
 * 
 */
async function getAgendamentos () {
  const calendar = window.jQuery.CalendarApp.$calendarObj;
  calendar.removeAllEvents();

  if (!calendar) return;
  
  const view = calendar.view;
  const view_schema = view.type;
  const start_date = convertDateToDb(view.currentStart);
  const end_date = view_schema != 'listDay' ? convertDateToDb(view.currentEnd) : null;
  let estados = [];
  $('.btn-status.selected').each(function() {
    estados.push($(this).data('value'));
  })

  const categoria = $('.service-btn[data-selected=true]').data('categoria');
  const funcionario_id = $('#inp-filter_funcionario_id').val() ?? null
  const cliente_id = $('#inp-filter_cliente_id').val() ?? null

  const empresa_id = $('#empresa_id').val();

  const data = await $.ajax({
    url: path_url + 'api/agendamentos/search-agendamentos',
    data: {
      empresa_id,
      start_date,
      end_date,
      categoria,
      estados, 
      funcionario_id,
      cliente_id
    }
  });

  if (data.error) {
    Swal.fire({
      title: 'Problema ao carregar agendamentos...',
      text: 'Ocorreu um erro desconhecido ao tentar carregar os seus agendamentos, tente novamente.',
      icon: 'error'
    })

    return;
  }

  const eventos = Array.isArray(data[0]) ? data.flat() : data;

  calendar.removeAllEvents();
  calendar.addEventSource(eventos);
}

function cleanSelect2Artifacts(container, element_query) {
  container.find(element_query).each(function () {
    if ($(this).data('select2')) {
      try { $(this).select2('destroy'); } catch(e) { /* ignore */ }
    }
    $(this).removeAttr('data-select2-id aria-hidden tabindex style');
  });

  container.find('.select2, .select2-container, .select2-selection, .select2-dropdown').remove();
}

function setModalFuncionario() {
  $('#inp-funcionario_id').select2({
    theme: 'bootstrap4',
    dropdownParent: $('#event-modal'),
    placeholder: 'Digite para buscar o colaborador',
    language: 'pt-BR',
    minimumInputLength: 2,
    ajax: {
      cache: true,
      url: path_url + 'api/funcionarios/pesquisa',
      dataType: 'json',
      data: function (params) {
        return {
          pesquisa: params.term,
          empresa_id: $('#empresa_id').val(),
        };
      },
      processResults: function (response) {
        return {
          results: response.map((v) => ({
            id: v.id,
            text: v.nome,
          })),
        };
      },
    },
  });
}

function setModalCliente() {
  $('#inp-cliente_id').select2({
    minimumInputLength: 2,
    language: 'pt-BR',
    placeholder: 'Digite para buscar o cliente',
    theme: 'bootstrap4',
    dropdownParent: $('#event-modal'),
    ajax: {
      cache: true,
      url: path_url + 'api/clientes/pesquisa',
      dataType: 'json',
      data: function (params) {
        return {
          pesquisa: params.term,
          empresa_id: $('#empresa_id').val(),
        };
      },
      processResults: function (response) {
        return {
          results: response.map((v) => ({
            id: v.id,
            text: v.razao_social + ' - ' + v.cpf_cnpj,
          })),
        };
      }
    }
  });
}

function setModalAnimal() {
  $("#inp-animal_id").select2({
    dropdownParent: $("#modal-editar_estetica"),
    minimumInputLength: 2,
    language: "pt-BR",
    placeholder: "Digite para buscar o animal",
    theme: "bootstrap4",
    dropdownParent: $("#event-modal .modal-content"),
    ajax: {
      cache: true,
      url: path_url + "api/animais/pesquisa",
      dataType: "json",
      data: function (params) {
        console.clear();
        var query = {
          pesquisa: params.term,
          empresa_id: $("#empresa_id").val(),
          cliente_id: $('#inp-cliente_id').val(),
        };
        return query;
      },
      processResults: function (response) {
        var results = [];

        $.each(response, function (i, v) {
          var o = {};
          o.id = v.id;

          o.text = v.nome;
          o.value = v.id;
          results.push(o);
        });
        return {
          results: results,
        };
      },
    },
  });
}

$(document).on('click', '.delete-agenda-btn', function (e) {
  e.preventDefault();
  e.stopPropagation();

  deleteAgendamentoFromAgenda($(this));
});

function deleteAgendamentoFromAgenda (element) {
  let id = null;

  if (element.siblings('.fc-event-main').length) {
    id = element.siblings('.fc-event-main').find('.agendamento-container').attr('data-id');
  } else if (element.siblings('.fc-list-event-title').length) {
    id = element.siblings('.fc-list-event-title').find('.agendamento-container').attr('data-id');
  }

  let tipo_agendamento = null;

  if (element.siblings('.fc-event-main').length) {
    tipo_agendamento = element.siblings('.fc-event-main').find('.agendamento-container').attr('data-tipo-agendamento');
  } else if (element.siblings('.fc-list-event-title').length) {
    tipo_agendamento = element.siblings('.fc-list-event-title').find('.agendamento-container').attr('data-tipo-agendamento');
  }

  Swal.fire({
    title: 'Deseja excluir o agendamento?',
    icon: 'warning',
    showCancelButton: true,
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Sim, excluir!',
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: path_url + 'api/agendamentos/excluir-agendamento',
        method: 'POST',
        data: {
          tipo_agendamento,
          id
        },
        success: function (response) {
          if (response.success == true) {
            Swal.fire({
              icon: 'success',
              title: 'Agendamento excluido com sucesso!',
            }).then(() => {
              location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Erro ao excluir agendamento!',
              text: response.message
            });
          }
        },
        error: function (xhr) {
          let msg = 'Erro ao excluir.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          }
          Swal.fire({
            icon: 'error',
            title: 'Erro ao excluir agendamento!',
            text: msg
          });
        }
      })
    }
  })
}

// Eventos que fazem a filtragem dos serviços na agenda 
// conforme a categoria selecionada no momento

$('.service-btn').on('click', async function() {
  $('.service-btn').each(function() {
    $(this).attr('data-selected', false);
    $(this).removeClass('selected-service-button');
  });

  $(this).attr('data-selected', true);
  $(this).addClass('selected-service-button');

  getAgendamentos();
});

$('.btn-status').on('click', function () {
  if ($(this).hasClass('selected')) {
    $(this).removeClass('selected')
  } else {
    $(this).addClass('selected')
  }

  getAgendamentos();  
});

$('#fc-view-select').on('change', function() {
    getAgendamentos();
}); 

$('.fc-next_btn-button').on('click', function() {
  getAgendamentos();
});
$('.fc-prev_btn-button').on('click', function() {
  getAgendamentos();
});
$('.fc-today_btn-button').on('click', function() {
  getAgendamentos();
});

// Eventos que manipulam os filtros da agenda: 
$('#agenda-status-select .dropdown-menu .dropdown-item').each(function () {
  $(this).on('click', function(e) {
    e.preventDefault();

    $('#agenda-status-select .dropdown-menu .dropdown-item').each(function () {
      $(this).removeClass('active');
      $(this).attr('data-selected', false);
    })

    const dropdown = $('#statusDropdown')

    const icon = $(this).data('icon');
    const text = $(this).text().trim();
    const bg_color = $(this).css('background-color');

    $(this).addClass('active');
    $(this).attr('data-selected', true);

    dropdown.empty();
    dropdown.html(`
      <i class="${icon}" style="margin-right:6px;"></i> ${text}
    `);
    dropdown.css('background-color', bg_color)

    getAgendamentos();
  });
});

function setFilterFuncionarios () {
  $('#inp-filter_funcionario_id').select2({
    placeholder: 'Digite para buscar o colaborador',
    language: 'pt-BR',
    theme: 'bootstrap4',
    ajax: {
        cache: true,
        url: path_url + 'api/funcionarios/pesquisa',
        dataType: 'json',
        data: function (params) {
            console.log(params);
            var query = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;

                o.text =
                    v.nome +
                    ' - Cargo: ' +
                    v.cargo;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
  }).on('select2:select', function (e) {
    getAgendamentos();
  });
}

function setFilterClientes () {
  $('#inp-filter_cliente_id').select2({
    placeholder: 'Digite para buscar o colaborador',
    language: 'pt-BR',
    placeholder: 'Digite para buscar o cliente',
    theme: 'bootstrap4',
    ajax: {
        cache: true,
        url: path_url + 'api/clientes/pesquisa',
        dataType: 'json',
        data: function (params) {
            var query = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text = v.razao_social + ' - ' + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
  }).on('select2:select', function (e) {
    getAgendamentos();
  });
}

function clearFilters () {
  if (!$('#inp-filter_funcionario_id').val() && !$('#inp-filter_cliente_id').val()) {
    return;
  }

  Swal.fire({
    title: 'Deseja limpar os filtros?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, limpar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $('#inp-filter_funcionario_id').val(null).trigger('change');
      $('#inp-filter_cliente_id').val(null).trigger('change');
      getAgendamentos();
    }
  })
}

$(document).delegate('.btn-clear-filters', 'click', function (e) {
  e.preventDefault();
  clearFilters();
});

$(document).ready(function () {
  setFilterFuncionarios();
  setFilterClientes();
});

$('#btn-novo-agendamento').on('click', function (e) { 
  const selected_service_btn = document.querySelector('.service-btn.selected-service-button');

  const categoria_servico = selected_service_btn ? selected_service_btn.getAttribute('data-categoria') : null;

  let selected_date = null;
  const calendar = window.jQuery.CalendarApp.$calendarObj;
  const current_view = calendar.view;

  if (current_view.type == 'listDay') {
    selected_date = current_view.currentStart.toISOString().split('T')[0];
  }

  if (selected_service_btn && categoria_servico) {
    switch (categoria_servico) {
      case 'HOTEL':
        handleModalNovoHotel(selected_date);
        break;
      case 'CRECHE':
        handleModalNovaCreche(selected_date);
        break;
      case 'ESTETICA':
        handleModalNovaEstetica();
        break;
    } 
  } else {
    handleModalSelectService(selected_date);
  }
});

/**
 * Manipula o e implementa o modal de seleção de serviços 
 * para se fazer um novo agendamento
 * 
 * @param {string | null} selected_date Data selecionada no calendário
 */
function handleModalSelectService (selected_date = null) {
  let modal_select_service = document.querySelector('#select_servico_agenda_petshop');

  if (!selected_date) {
    const calendar = window.jQuery.CalendarApp.$calendarObj;
    const current_view = calendar.view;

    if (current_view.type == 'listDay') {
      selected_date = current_view.currentStart.toISOString().split('T')[0];
    }
  }

  modal_select_service.querySelector('#hotel-option').onclick = () => handleModalNovoHotel(selected_date);
  modal_select_service.querySelector('#creche-option').onclick = () => handleModalNovaCreche(selected_date);
  modal_select_service.querySelector('#estetica-option').onclick = () => handleModalNovaEstetica();

  modal_select_service = bootstrap.Modal.getOrCreateInstance(modal_select_service);

  modal_select_service.show();
}

/**
 * Manipula e configura os campos e o comportamento
 * dentro do modal de agendamento de hotel
 * 
 * @param {string | null} selected_date Data selecionada no calendário
 */
function handleModalNovoHotel(selected_date = null) {
  let modal_select_service = $('#select_servico_agenda_petshop');

  if (modal_select_service.hasClass('show')) {
    modal_select_service.modal('hide');
  }

  let modal_novo_hotel = document.querySelector('#modal_novo_agendamento_hotel');

  if (selected_date) {
    modal_novo_hotel.querySelector('input[name="checkin"]').value = selected_date;
  }

  modal_novo_hotel = bootstrap.Modal.getOrCreateInstance(modal_novo_hotel);

  modal_novo_hotel.show();
}

/**
 * Manipula e configura os campos e o comportamento
 * dentro do modal de agendamento de creche
 * 
 * @param {string | null} selected_date Data selecionada no calendário
 * 
 */
function handleModalNovaCreche(selected_date = null) {
  let modal_select_service = $('#select_servico_agenda_petshop');

  if (modal_select_service.hasClass('show')) {
    modal_select_service.modal('hide');
  }

  let modal_nova_creche = document.querySelector('#modal_novo_agendamento_creche');

  if (selected_date) {
    modal_nova_creche.querySelector('input[name="data_entrada"]').value = selected_date;
  }

  modal_nova_creche = bootstrap.Modal.getOrCreateInstance(modal_nova_creche);

  modal_nova_creche.show();
}

/**
 * Manipula e configura os campos e o comportamento
 * dentro do modal de agendamento de estética
 */
function handleModalNovaEstetica() {
  let modal_select_service = $('#select_servico_agenda_petshop');

  if (modal_select_service.hasClass('show')) {
    modal_select_service.modal('hide');
  }

  let modal_nova_estetica = $('#modal_novo_agendamento_estetica');

  modal_nova_estetica = bootstrap.Modal.getOrCreateInstance(modal_nova_estetica);

  modal_nova_estetica.show();
}