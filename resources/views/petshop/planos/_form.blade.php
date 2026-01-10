<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs nav-primary" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3 active" data-bs-toggle="tab" href="#dados" role="tab" aria-selected="true">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-file-text-line"></i>
                            Plano
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3" data-bs-toggle="tab" href="#versoes" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-calendar-2-line"></i>
                            Versões
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3" data-bs-toggle="tab" href="#beneficios" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-gift-line"></i>
                            Benefícios
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3" data-bs-toggle="tab" href="#valores" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-money-dollar-box-line"></i>
                            Valores
                        </div>
                    </div>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="dados" role="tabpanel" data-label="Plano">
                <div class="row g-3 mt-3">
                    <input type="hidden" name="empresa_id" value="{{ data_get($planoData ?? [], 'empresa_id', request()->empresa_id) }}">
                    <input type="hidden" name="local_id" value="{{ data_get($planoData ?? [], 'local_id', optional(__getLocalAtivo())->id) }}">
                    <div class="col-md-4">
                        {!! Form::text('slug', 'Slug')->attrs(['class' => 'text-uppercase'])->required() !!}
                    </div>
                    <div class="col-md-8">
                        {!! Form::text('nome', 'Nome')->attrs(['class' => 'text-uppercase'])->required() !!}
                    </div>
                    <div class="col-md-12">
                        {!! Form::textarea('descricao', 'Descrição')->attrs(['class' => 'text-uppercase']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select('ativo', 'Ativo', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select'])->value(1) !!}
                    </div>
                    

                </div>
            </div>

            <div class="tab-pane fade" id="versoes" role="tabpanel" data-label="Versões">
                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        {!! Form::date('versoes[0][vigente_desde]', 'Vigente desde')
                            ->attrs(['id' => 'vigente_desde'])
                            ->value(data_get($planoData ?? [], 'versoes.0.vigente_desde'))
                            ->required() !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('versoes[0][vigente_ate]', 'Vigente até')
                            ->attrs(['id' => 'vigente_ate'])
                            ->value(data_get($planoData ?? [], 'versoes.0.vigente_ate')) !!}
                    </div>
                    <hr>
                   

                   
                   
                </div>
            </div>

            <div class="tab-pane fade" id="beneficios" role="tabpanel" data-label="Benefícios">
                <div class="mt-3">
                     <div class="row mb-3">

                        <div class="col-md-3">
                        {!! Form::select('periodo', 'Período', ['' => 'Selecione', 'dia' => 'Dia', 'semana' => 'Semana', 'mes' => 'Mês', 'ano' => 'Ano'])->attrs(['class' => 'form-select', 'id' => 'periodo'])->required() !!}
                    </div>
                    <div class="col-md-3">
{!! Form::select('frequencia_tipo', 'Frequência', ['ilimitado' => 'Ilimitado', 'limitado' => 'Limitado'])
                            ->attrs(['class' => 'form-select', 'id' => 'frequencia_tipo'])
                            ->value(data_get($planoData ?? [], 'frequencia_tipo', 'ilimitado'))
                            ->required() !!}                    </div>
                    <div class="col-md-2" id="frequencia_qtd_wrapper" style="display: none;">
                        {!! Form::tel('frequencia_qtd', 'Qtd. por período')
                            ->attrs([
                                'id' => 'frequencia_qtd',
                                'min' => 1,
                                'placeholder' => 'Ex.: 2',
                                'inputmode' => 'numeric',
                                'pattern' => '[0-9]*',
                                'disabled' => true,
                            ]) !!}
                    </div>
                    </div>
                    <p id="beneficio-preview" class="text-muted small"></p>
                    <ul class="nav nav-tabs nav-primary" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#beneficio-servicos" role="tab">Serviços</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#beneficio-produtos" role="tab">Produtos</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#beneficio-frete-servicos" role="tab">Frete</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="beneficio-servicos" role="tabpanel">
                            @include('components.petshop.planos.servicos_input_line', ['show_actions' => 1])
                        </div>
                        <div class="tab-pane fade" id="beneficio-produtos" role="tabpanel">
                            @include('components.petshop.planos.produtos_input_line', ['show_actions' => 1])
                        </div>
                        <div class="tab-pane fade" id="beneficio-frete-servicos" role="tabpanel">
                            @include('components.petshop.planos._frete_servico_input_line', ['frete' => isset($frete) ? $frete : null])
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="valores" role="tabpanel" data-label="Valores">
                <div class="row g-3 mt-3">
                    
                    <div class="col-md-3">
                        {!! Form::tel('preco_plano', 'Preço base do plano (R$)')->attrs(['class' => 'moeda', 'id' => 'preco_plano'])->required() !!}
                    </div>
                   

                    <hr>

                    <div class="col-md-3">
                        {!! Form::select('multa_noshow_tipo', 'No-show / cancelamento', ['percentual' => 'Percentual', 'valor_fixo' => 'Valor fixo'])->attrs(['class' => 'form-select', 'id' => 'multa_noshow_tipo'])->value('percentual')->required() !!}
                    </div>
                    <div class="col-md-3 multa-percent d-none">
                        {!! Form::tel('multa_noshow_valor', 'Multa (%)')->attrs(['class' => 'percentual', 'id' => 'multa_percent']) !!}
                    </div>
                    <div class="col-md-3 multa-valor d-none">
                        {!! Form::tel('multa_noshow_valor', 'Multa (R$)')->attrs(['class' => 'moeda', 'id' => 'multa_valor']) !!}
                    </div>

                    <hr>
                    <div class="col-md-3">
                        {!! Form::select('bloquear_por_inadimplencia', 'Bloquear por inadimplência?', ['sim' => 'Sim', 'nao' => 'Não'])->attrs(['class' => 'form-select', 'id' => 'bloquear_por_inadimplencia'])->required() !!}
                    </div>
                    <div class="col-md-3 dias-tolerancia d-none">
                        {!! Form::tel('dias_tolerancia_atraso', 'Dias de tolerância')->attrs(['class' => 'dias', 'id' => 'dias_tolerancia_atraso']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 col-12" style="text-align: right;">
    <button id="submit-btn" type="submit" class="btn btn-success px-5">Salvar</button>
</div>