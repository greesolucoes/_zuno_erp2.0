<div class="modal fade" id="edit_reserva_hotel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
                <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                Editar Reserva de Hotel
                <i class="ri-hotel-line"></i>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-content">
            <div class="modal-body p-3">
                {!! 
                    Form::open() 
                    ->id('form-edit-reserva-hotel')
                    ->put()
                !!}
                    <div class="col-2">
                        {!! 
                            Form::select('reserva_quarto_id', 'Quarto')
                            ->attrs(['class' => 'form-select'])
                            ->required()
                        !!} 

                        <input type="hidden" name="reserva_nome_quarto" />
                        <input type="hidden" name="reserva_id_quarto" />
                    </div>
                    <div class="row mt-3">
                        <div class="col-3">
                            {!!
                            Form::date('reserva_checkin', 'Check in')
                            ->required()
                            !!}
                        </div>
                        <div class="col-3">
                            {!!
                            Form::time('reserva_timecheckin', 'Horário de Check in')
                            ->required()
                            !!}
                        </div>
                        <div class="col-3">
                            {!!
                            Form::date('reserva_checkout', 'Check out')
                                ->required()
                            !!}
                        </div>
                        
                        <div class="col-3">
                            {!!
                                Form::time('reserva_timecheckout', 'Horário de Check out')
                                    ->required()
                            !!}
                        </div>
                        {!!
                            Form::hidden('reserva_tempo_execucao')
                        !!}
                    </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 align-items-center justify-content-end gap-2 new-colors">
                    <button 
                        type="button" 
                        class="btn btn-success" 
                        data-bs-toggle="modal" 
                        data-bs-target="#handle_modal_agendamento"
                    >
                        Voltar
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        id="submit_update_reserva_hotel"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>