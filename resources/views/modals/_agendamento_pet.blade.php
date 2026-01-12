<div class="modal fade" id="event-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form 
                class="needs-validation" 
                id="form-event" 
                method="post" 
                action="{{ route('agendamentos.store') }}" {{-- Rota default para os agendamentos, porém cada segmento do petshop tem sua action definida pelo js --}}
            >
                <input type="hidden" id="segmento" value="{{$segmento ?? null}}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                        Agendamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-3">
                    <div class="row g-2">
                        
                        <div class="col-6 d-flex align-items-end">
                            <div class="col">
                                <div class="w-100">
                                    {!! Form::select('animal_id', 'Pet')->attrs(['class' => 'form-select w-100'])->required() !!}
                                </div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-dark ms-2" type="button" id="btn-add-pet">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-6 col-12">
                            <div class="row align-items-end">
                                <div class="col">
                                    <div class="w-100">
                                        <label for="funcionario">Colaborador</label>
                                        <select class="select2 form-control" name="funcionario_id" id="funcionario">
                                            <option value="" selected disabled>Selecione o colaborador</option>
                                            @foreach ($funcionarios as $funcionario)
                                            <option value="{{ $funcionario->id }}" @selected(old('funcionario_id', isset($plano) ? $plano->funcionario_id : '') == $funcionario->id)>
                                                {{ $funcionario->nome }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-dark" type="button" id="btn-add-funcionario" data-bs-dismiss="modal">
                                        <i class="ri-add-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label for="">Serviços</label>
                                    <select class="form-control select2" name="servico" id="servicos_id" data-toggle="select2">
                                        <option value="" disabled selected>SELECIONE</option>
                                        @foreach ($servicos as $item)
                                        <option
                                            value="{{ $item->id }}"
                                            data-id="{{ $item->id }}"
                                            data-categoria="{{ $item->categoria->nome }}"
                                            data-valor="{{ $item->valor }}"
                                            data-tempo="{{ $item->tempo_execucao }}">
                                            {{ $item->nome }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-dark" type="button" id="btn-add-servico" data-bs-dismiss="modal">
                                        <i class="ri-add-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-4">
                                        {!! Form::tel('descontos', 'Desconto')->attrs(['class' => 'moeda'])
                                        ->placeholder('Desconto') !!}
                                    </div>
                                    <div class="col-4">
                                        {!! Form::tel('acrescimos', 'Acréscimo')->attrs(['class' => 'moeda'])
                                        ->placeholder('Acréscimo') !!}
                                    </div>
                                    <div class="col-4">
                                        {!! Form::tel('total', 'Total')->attrs(['class' => 'moeda'])->required()
                                        ->placeholder('Total')!!}
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-4">
                                        {!! Form::date('dataatual', 'Data atual')->attrs(['class' => 'form-select','readonly' => true
                                        ]) !!}
                                    </div>
                                    <div class="col-4">
                                        {!!Form::time('horario_inicio', 'Hora inicio')->required()!!}

                                    </div>
                                    <div class="col-4">
                                        {!!Form::time('horario_final', 'Hora final')->required()!!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-6 d-flex flex-column justify-content-between">
                                <div class="d-flex flex-column justify-content-between mb-2">
                                    {!! Form::select('Sala_id', 'Reserva cômodo?')
                                    ->options(
                                    ['' => 'Não']
                                    )
                                    ->options(
                                    ['' => 'Selecione'] + $salas->pluck('nomec', 'id')->all()
                                    )
                                    ->value($item->sala_id ?? null)
                                    ->attrs(['class' => 'form-select'])
                                    ->disabled()
                                    !!}
                                </div>

                                {!! Form::textarea('observacao', 'Observação')->attrs([
                                'rows' => 3,
                                'style' => 'height: 100%; min-height: 100px; resize: vertical;',
                                'placeholder' => 'Digite...',
                                ]) !!}
                            </div>
                        </div>

                        <input type="hidden" name="funcionario" id="funcionario">
                        <input type="hidden" name="data" id="data">
                        <input type="hidden" id="subtotal-servicos" value="{{ 'R$ ' . __moeda(isset($ordem) ? $ordem->getTotalValueServicosAttribute() : 0) }}" />
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-end">
                        <button type="button" class="btn btn-light me-1" data-bs-dismiss="modal">Sair</button>
                        <button type="button" class="btn btn-success" id="btn-save-event">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>