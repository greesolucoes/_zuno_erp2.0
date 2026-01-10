@php
    use Carbon\Carbon;
@endphp

<div class="d-flex gap-3">
    <div class="card" style="flex: 1; height: min-content">
        <div class="card-header">
            <h4 class="text-color">
                Data e horário do agendamento
            </h4>
        </div>
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-4">
                    {!! 
                        Form::date('data_agendamento', 'Data do agendamento')
                        ->value(isset($data) ? Carbon::parse($data->data_agendamento)->format('Y-m-d') : null)
                        ->required()
                    !!}
                </div>

                <hr class="mt-3">

                <div class="col-4">
                    {!! 
                        Form::time('horario_agendamento', 'Horário de início')->required()
                    !!}
                </div>

                <div class="col-4">
                    {!! 
                        Form::time('horario_saida', 'Horário de saída')->required()
                    !!}
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="flex: 1">
        <div class="card-header">
            <h4 class="text-color">
                Informações de funcionamento no dia
            </h4>
        </div>
        <div class="card-body p-3">
            <div class="text-color fs-3 text-center d-none" id="empty-msg-content">
                {{-- Conteúdo definido pelo JS --}}
            </div>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-5 d-none" id="empresa-schedule-content">
                    <div class="d-flex flex-column gap-1 new-colors">
                        <div class="text-color fs-5 fw-semibold">
                            Horário de funcionamento da empresa no dia
                        </div>
                        <div class="fw-bold text-orange" id="empresa-horario-funcionamento-content">

                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-5 d-none" id="funcionario-schedule-content">
                    <div class="d-flex flex-column gap-1 new-colors">
                        <div class="text-color fs-5 fw-semibold">
                            Horário do colaborador no dia
                        </div>
                        <div class="fw-bold text-orange" id="funcionario-horario-funcionamento-content">

                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 new-colors" id="funcionario-horario-intervalo-container">
                        <div class="text-color fs-5 fw-semibold">
                            Horário de intervalo no dia
                        </div>
                        <div class="fw-bold text-orange" id="funcionario-horario-intervalo-content">

                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex flex-column gap-3 new-colors d-none" id="current-agendamentos-estetica-content">
                    <h5 class="text-color">
                        Agendamentos para o dia
                    </h5>
                    <div class="current-agendamento-estetica-item agendamento-estetica-item-template">
                        <div class="d-flex flex-column">
                            <div class="text-color fs-5 fw-bold mb-4">
                                Horário
                            </div>
                            <div class="text-orange fw-bold agendamento-estetica-horario">
                                {{-- Conteúdo definido pelo JS --}}
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <div class="text-color fs-5 fw-bold mb-4">
                                Serviços
                            </div>
                            <ul class="fw-semibold agendamento-estetica-servicos p-0" style="color: #6495ed">
                                {{-- Conteúdo definido pelo JS --}}
                            </ul>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div class="text-color fs-5 fw-bold mb-4">
                                Informações do agendamento
                            </div>
                            <div class="d-flex align-items-center gap-1 text-purple fw-semibold">
                                <div class="fw-bold">Cliente:</div>
                                <div class="agendamento-estetica-cliente"></div>
                            </div>
                            <div class="d-flex align-items-center gap-1 text-purple fw-semibold">
                                <div class="fw-bold">Pet:</div>
                                <div class="agendamento-estetica-pet"></div>
                            </div>
                            <div class="d-flex align-items-center gap-1 text-purple fw-semibold">
                                <div class="fw-bold">Responsável:</div>
                                <div class="agendamento-estetica-funcionario"></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-color fs-3 text-center d-none" id="current-agendamentos-estetica-empty-msg">
                        {{-- Conteúdo definido pelo JS --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('modals._estetica_agendamento')