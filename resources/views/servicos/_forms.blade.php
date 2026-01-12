<div class="row g-3 m-auto">

    <div class="row mt-3">
        <div class="col-md-6 row">
            <button type="button" class="btn btn-identificacao-servico btn-outline-primary link-active px-6" onclick="selectDivServico('identificacao')">Dados do serviço</button>
        </div>
        <div class="col-md-6 row m-auto">
            <button type="button" class="btn btn-tempo-servico btn-outline-primary" onclick="selectDivServico('tempo')">Tempo de execução</button>
        </div>
    </div>

    <div class="div-identificacao-servico row mt-4">

        <div class="col-md-5">
            {!!Form::text('nome', 'Nome')->required()
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::tel('valor', 'Valor')->required()
            ->attrs(['class' => 'moeda'])
            !!}
        </div>

        {!! Form::hidden('tempo_servico')
            ->required()
            ->attrs(['id' => 'inp-tempo_servico'])
            ->value(old('tempo_servico', isset($item) ? $item->tempo_servico : null))
        !!}

        <div class="col-md-2">
            {!!Form::tel('comissao', 'Comissão (opcional)')
            ->attrs(['class' => 'moeda'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::select('categoria_id', 'Categoria', $categorias->pluck('nome', 'id')->all())->required()
            ->attrs(['class' => 'form-select'])
            !!}
        </div>

        <hr class="mt-4 mb-4">
        <div class="col-md-2">
            {!!Form::select('unidade_cobranca', 'Unidade de cobrança', ['UND' => 'UND', 'HORAS' => 'HORAS', 'MIN' => 'MIN'])
            ->attrs(['class' => 'select2'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Inativo'])
            ->attrs(['class' => 'form-select'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::text('tempo_adicional', 'Tempo adicional')
            ->attrs(['data-mask' => '00:00'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::tel('valor_adicional', 'Valor adicional')->attrs(['class' => 'moeda'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::text('tempo_tolerancia', 'Tempo de tolerância')
            ->attrs(['data-mask' => '00:00'])
            !!}
        </div>

        <div class="col-md-2">
            {!!Form::tel('codigo_servico', 'Código do serviço')->attrs(['class' => ''])
            !!}
        </div>

        <div class="card mt-4">
            <div class="row m-5">
                <h6>Tributação</h6>
                <div class="col-md-3">
                    {!!Form::tel('aliquota_iss', '% ISS')->attrs(['data-mask' => '000,00'])
                    !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('aliquota_pis', '% PIS')->attrs(['data-mask' => '000,00'])
                    !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('aliquota_cofins', '% COFINS')->attrs(['data-mask' => '000,00'])
                    !!}
                </div>
                <div class="col-md-3">
                    {!!Form::tel('aliquota_inss', '% INSS')->attrs(['data-mask' => '000,00'])
                    !!}
                </div>
            </div>
        </div>

    </div>

    <div class="div-tempo-servico row mt-4 d-none">
        @php
            $tempoServicoTotalMin = old('tempo_servico', isset($item) ? $item->tempo_servico : null);

            $diasValue = old('dias');
            $horasValue = old('horas');
            $minutosValue = old('minutos');

            if ($diasValue === null && $horasValue === null && $minutosValue === null && $tempoServicoTotalMin !== null && $tempoServicoTotalMin !== '') {
                $tempoServicoTotalMinInt = (int) $tempoServicoTotalMin;
                $diasValue = intdiv($tempoServicoTotalMinInt, 1440);
                $restoMin = $tempoServicoTotalMinInt % 1440;
                $horasValue = intdiv($restoMin, 60);
                $minutosValue = $restoMin % 60;
            }
        @endphp

        <h6 class="text-color required mb-2">Tempo de execução</h6>

        <div class="table-responsive">
            <table class="table table-dynamic table-duracao-servico mb-0">
                <thead>
                    <tr>
                        <th>Dias</th>
                        <th>Horas</th>
                        <th>Minutos</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {!!
                                Form::tel('dias', '')
                                ->attrs([
                                    'id' => 'inp-dias',
                                    'inputmode' => 'numeric',
                                    'maxlength' => 4,
                                ])
                                ->value(isset($diasValue) && (int)$diasValue > 0 ? $diasValue : null)
                                ->placeholder('0 dias')
                            !!}
                        </td>
                        <td>
                            {!!
                                Form::tel('horas', '')
                                ->attrs([
                                    'id' => 'inp-horas',
                                    'inputmode' => 'numeric',
                                    'maxlength' => 2,
                                ])
                                ->value(isset($horasValue) && (int)$horasValue > 0 ? $horasValue : null)
                                ->placeholder('0 horas')
                            !!}
                        </td>
                        <td>
                            {!!
                                Form::tel('minutos', '')
                                ->attrs([
                                    'id' => 'inp-minutos',
                                    'inputmode' => 'numeric',
                                    'maxlength' => 2,
                                ])
                                ->value(isset($minutosValue) && (int)$minutosValue > 0 ? $minutosValue : null)
                                ->placeholder('0 minutos')
                            !!}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <small class="text-muted d-block mt-1">O sistema salva este tempo em minutos (campo oculto).</small>
    </div>

<hr>

<div class="col-12">
    <button type="submit" class="btn btn-primary px-5">Salvar</button>
</div>
