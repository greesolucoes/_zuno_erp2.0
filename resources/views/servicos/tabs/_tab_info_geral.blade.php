<div class="row g-xl-3 g-lg-2">
    <div class="col-md-4">
        {!! Form::select(
                'natureza_operacao',
                'Natureza de Operação',
                [
                    '' => 'Selecione','Tributação no Municipio'=>'Tributação no Municipio',
                    'Tributação fora do Municipio'=>'Tributação fora do Municipio',
                    'Isenção'=>'Isenção',
                    'Imune'=>'Imune',
                    'Exigibilidade Susp. decisão judicial'=>'Exigibilidade Susp. decisão judicial',
                    'Exigibilidade Susp. Procedimento Administrativo'=>'Exigibilidade Susp. Procedimento Administrativo'
                ]
            )->attrs(['class' => 'form-select'])->value(isset($item) ? $item->natureza_operacao : '')->required()
        !!}
    </div>
        <div class="col-md-2">
        {!! Form::select(
            'tipo_servico',
            'Tipo de Serviço',
            [
            '1' => 'Interno',
            '2' => 'Terceirizado'
            ],
        )->attrs(['class' => 'form-select'])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('status', 'Ativo', ['1' => 'Sim', '0' => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>

    <hr class="mt-4">

    <div class="col-md-4 align-items-end gap-2 form__cliente" id="funcionario-container">
        {!! Form::select(
            'funcionario_id',
            'Colaborador',
        )->options(
            isset($item->funcionario->id ) ? [$item->funcionario->id => $item->funcionario->nome] : [],
        )
        ->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-4 align-items-end gap-2 form__cliente d-none" id="fornecedor-container">
        {!! Form::select(
            'fornecedor_id',
            'Fornecedor',
        )->options(
            isset($item->fornecedor->id ) ? [$item->fornecedor->id => $item->fornecedor->razao_social] : [],
        )
        ->attrs(['class' => 'select2']) !!}
    </div>

    <div class="col-md-2">
        {!!
            Form::tel('comissao', 'Comissão')
            ->attrs(['class' => 'percentual'])
            ->placeholder('0.00%')
        !!}
    </div>

    <hr class="mt-4">

    <div class="col-md-6 col-12">
        {!!
            Form::textarea('descricao', 'Descrição')
            ->attrs([
                'rows' => '6',
                'style' => 'resize: none;',
            ])
            ->placeholder('Digite uma descrição para o serviço')
        !!}
    </div>
</div>
