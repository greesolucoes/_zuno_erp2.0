@extends('layouts.app', ['title' => 'Agendamentos'])

@section('css')
  <link href="/css/agenda.css" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
  <input type="hidden" id="agendamentos" value="{{ json_encode($agendamentos) }}">
  <div class="row">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start mb-4">
          <div class="d-flex flex-column" style="flex: 1">
	            <h3 class="text-gold mb-4">
	              Agendamentos
	            </h3>
	            <div class="d-flex align-items-end gap-2">
	              <div class="col-3">
	                {!! 
	                  Form::select('filter_funcionario_id', 'Colaborador')
	                !!}
	              </div>
	              <div class="col-3">
	                {!! 
	                  Form::select('filter_cliente_id', 'Cliente')
	                !!}
	              </div>
	              <div class="col-2 new-colors">
	                <button class="btn btn-danger btn-clear-filters">
	                  <i class="ri-eraser-fill"></i>
	                  Limpar
	                </button>
	              </div>
	            </div>
	          </div>
	          <div class="d-flex flex-column justify-content-center align-items-end">
	            <div class="d-flex align-items-center gap-2">

                <button 
                  type="button" 
                  class="btn btn-primary service-btn selected-service-button" 
                  id="show-all-agendamentos"
                  data-selected=true
                  data-categoria=""
                >
                  <i class="ri-grid-fill"></i>
                  Todos os agendamentos
                </button>
                <button 
                  type="button" 
                  class="btn btn-primary service-btn" 
                  id="show-hotel-agendamentos"
                  data-selected=false
                  data-categoria="HOTEL"
                >
                  <i class="ri-hotel-line"></i>
                  Hotel
                </button>
                <button 
                  type="button" 
                  class="btn btn-primary service-btn" 
                  id="show-creche-agendamentos"
                  data-selected=false
                  data-categoria="CRECHE"
                >
                  <i class="ri-graduation-cap-line"></i>
                  Creche
                </button>
                <button
                  type="button"
                  class="btn btn-primary service-btn"
                  id="show-estetica-agendamentos"
                  data-selected=false
                  data-categoria="ESTETICA"
                >
                  <i class="ri-sparkling-line"></i>
                  Estética
                </button>
                <button
                  type="button"
                  class="btn btn-primary service-btn"
                  id="show-veterinario-agendamentos"
                  data-selected=false
                  data-categoria="VETERINARIO"
                >
                  <i class="ri-stethoscope-line"></i>
                  Atendimento Veterinário
                </button>
	            </div>
	            <div class="d-flex flex-column mt-3 g-2" id="agenda-status-select">
                <div class="d-flex gap-2 align-items-center">
                  <button 
                    class="btn-status btn-agendado" 
                    data-value="agendado" 
                    data-icon="ri-calendar-event-line"
                  >
                    <i class="ri-calendar-event-line mr-3"></i> Agendado (AG)
                  </button>
                  <button 
                    class="btn-status btn-em-andamento" 
                    data-value="em_andamento" 
                    data-icon="ri-hourglass-fill"
                  >
                    <i class="ri-hourglass-fill mr-3"></i> Em Andamento (EA)
                  </button>
                  <button 
                    class="btn-status btn-concluido" 
                    data-value="concluido" 
                    data-icon="ri-check-double-line"
                  >
                    <i class="ri-check-double-line mr-3"></i> Concluído (CL)
                  </button>
                  <button 
                    class="btn-status btn-cancelado" 
                    data-value="cancelado" 
                    data-icon="ri-close-circle-line"
                  >
                    <i class="ri-close-circle-line mr-3"></i> Cancelado (CC)
                  </button>
                  <button 
                    class="btn-status btn-rejeitado" 
                    data-value="rejeitado" 
                    data-icon="ri-calendar-close-line"
                  >
                    <i class="ri-calendar-close-line mr-3"></i> Rejeitado (RJ)
                  </button>
                  <button 
                    class="btn-status btn-pendente-aprovacao" 
                    href="#"
                    data-value="pendente_aprovacao" 
                    data-icon="ri-timer-fill"
                  >
                    <i class="ri-timer-fill mr-3"></i> Aprovação Pendente (AP)
                  </button>
                </div>
	            </div>
	            <div class="d-flex justify-content-end mt-3 g-2" >
	              <button type="button" class="btn btn-primary" id="btn-novo-agendamento">
	                <i class="ri-add-circle-line"></i>
	                Novo Agendamento
	              </button>
	            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="row">
              <div id="external-events" class="mt-3">
              </div>
              <div class="col-lg-12">
                  <div id="calendar" class="calendario"></div>
              </div> <!-- end col -->
            </div> <!-- end row -->

          </div>

	          <div id="row" class="mt-5">
	            <div class="p-2 bg-primary rounded-2 mb-2" style="width: max-content; color: #fff" data-class="bg-primary">
	                <i class="ri-focus-fill me-2 vertical-middle"></i>
	                Atendimentos de Planos
	            </div>
	            <div class="p-2 bg-warning rounded-2 text-warning" style="width: max-content; color: #fff !important"  data-class="bg-warning">
	              <i class="ri-focus-fill me-2 vertical-middle"></i>
	              Atendimentos Avulso
	            </div>
	          </div>
          <!-- end col-12 -->
        </div> <!-- end row -->
      </div>
    </div>
  </div>

  <input type="hidden" id="create_permission" value="@can('agendamento_create') 1 @else 0 @endcan">

	  @include('modals._handle_agendamento_petshop')
	  @include('modals._vet_atendimento')

    @include('modals._edit_reserva_hotel')
    @include('modals._edit_reserva_creche')
    @include('modals._edit_reserva_estetica')

    @include('modals._servicos_extras_petshop')
    @include('modals._servico_frete_petshop')

    @include('modals._produtos_petshop')

    @include('modals._editar_agendamento_pet')
    @include('modals._editar_agendamento_cliente')

    @include('modals._checklist_pethsop')
    @include('modals._editar_pet_checklist')

    @include('modals._plano_petshop_info')
    
    @include('modals._estetica_modal_agenda')
    @include('modals._estetica_agendamento')
    @include('modals._agendamento_pet')
    @include('modals._novo_pet', ['clientes' => $clientes ?? null, 'back_modal' => '#event-modal'])

    @include('modals._select_servico_agenda_petshop')

    @include('modals._endereco_cliente')

    @include('modals._novo_agendamento_hotel')
    @include('modals._novo_agendamento_creche')
	    @include('modals._novo_agendamento_estetica')

  @include('modals._veiculos_cliente')
  @include('modals._novo_cliente')
  @include('modals._novo_funcionario', ['item' => $item ?? null, 'back_modal' => '#event-modal'])
  @include('modals._novo_servico', ['back_modal' => '#event-modal'])

  @section('js')
    <script src="/assets/vendor/fullcalendar/main.min.js"></script>
    <script src="/tinymce/tinymce.min.js"></script>

    <script src="/js/novo_cliente.js"></script>
    <script src="/js/servicos_produtos_input_line.js"></script>
    <script src="/js/novo_colaborador.js"></script>
    <script src="/js/novo_servico.js"></script>

	    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
	    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/isoWeek.min.js"></script>
	    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/pt-br.js"></script>
	    <script src="/js/estetica_form.js"></script>
	    <script src="/js/calendar-pet.js"></script>
	    <script src="/js/hotel.js"></script>
	    <script src="/js/creche.js"></script>
	    <script src="/js/novo_pet.js"></script>
	    <script src="/js/checklist_templates.js"></script>
	  @endsection

@endsection
