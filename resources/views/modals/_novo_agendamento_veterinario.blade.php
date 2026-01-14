<div class="modal fade" id="modal_novo_agendamento_veterinario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Novo Atendimento Veterinário
                <i class="ri-stethoscope-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">
                {!!
                    Form::open()
                    ->id('form-novo-agendamento-veterinario')
                    ->post()
                !!}
                    <ul class="nav nav-tabs nav-primary" role="tablist">
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link active" data-bs-toggle="tab" href="#vet_info_geral" role="tab"
                                aria-selected="true">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-file-user-fill"></i>
                                        Informações gerais
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link" data-bs-toggle="tab" href="#vet_agendamento" role="tab">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-calendar-2-line"></i>
                                        Agendamento
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" style="flex: 1 !important" role="presentation">
                            <a class="px-3 nav-link" data-bs-toggle="tab" href="#vet_observacoes" role="tab">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="tab-title">
                                        <i class="ri-clipboard-line"></i>
                                        Observações
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="vet_info_geral" role="tabpanel">
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="required">Paciente</label>
                                    <select
                                        name="paciente_id"
                                        class="form-select select2"
                                        data-placeholder="Digite para buscar o animal (pet)"
                                        required
                                    >
                                        <option value=""></option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    {!! Form::select('veterinario_id', 'Profissional responsável')
                                        ->options(['' => 'Selecione um profissional'] + ($vet_veterinarians ?? []))
                                        ->attrs([
                                            'class' => 'form-select select2',
                                            'data-placeholder' => 'Selecione um profissional',
                                        ])
                                        ->required()
                                    !!}
                                </div>

                                {!! Form::hidden('tutor_id') !!}
                                {!! Form::hidden('tutor_nome') !!}

                                <div class="col-md-6">
                                    {!! Form::text('contato_tutor', 'Contato do tutor')
                                        ->attrs(['readonly' => true])
                                        ->disabled()
                                    !!}
                                </div>

                                <div class="col-md-6">
                                    {!! Form::text('email_tutor', 'E-mail do tutor')
                                        ->attrs(['readonly' => true])
                                        ->disabled()
                                    !!}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="vet_agendamento" role="tabpanel">
                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    {!! Form::date('data_atendimento', 'Data do atendimento')
                                        ->value(now()->format('Y-m-d'))
                                        ->required()
                                    !!}
                                </div>
                                <div class="col-md-4">
                                    {!! Form::select('horario', 'Horário')
                                        ->options(['' => 'Selecione um horário'] + ($vet_schedule_times ?? []))
                                        ->attrs([
                                            'class' => 'form-select select2',
                                            'data-placeholder' => 'Selecione um horário',
                                        ])
                                        ->required()
                                    !!}
                                </div>
                                <div class="col-md-4">
                                    {!! Form::select('sala_id', 'Sala')
                                        ->options(['' => 'Selecione uma sala'] + ($vet_rooms ?? []))
                                        ->attrs([
                                            'class' => 'form-select select2',
                                            'data-placeholder' => 'Selecione uma sala',
                                        ])
                                    !!}
                                </div>

                                <div class="col-12">
                                    {!! Form::text('tipo_atendimento', 'Tipo de atendimento')
                                        ->value('Atendimento veterinário')
                                    !!}
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="vet_observacoes" role="tabpanel">
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    {!! Form::textarea('motivo_visita', 'Motivo da visita / Observações')
                                        ->attrs(['rows' => 8])
                                    !!}
                                </div>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 align-items-center justify-content-end gap-2 new-colors">
                    <button
                        type="button"
                        class="btn btn-success btn-close-modal"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        id="submit_novo_agendamento_veterinario"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

