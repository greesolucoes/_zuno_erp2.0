<div class="row g-xl-3 g-lg-2">
    @isset($codigo)
        <div class="col-md-1">
            {!! Form::text('codigo', 'Código')->attrs([
                    'title' => '(Código)',
                    'maxLength' => '5',
                ])->value($codigo)
            !!}
        </div>
    @endisset

    <div class="col-md-4">
        {!!
            Form::text('nome', 'Nome do serviço')
            ->attrs(['class' => 'text-uppercase'])
            ->placeholder('Digite o nome do serviço')
            ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!
            Form::tel('valor', 'Valor')
            ->attrs(['class' => 'moeda'])
            ->required()
            ->value(isset($item) ? __moeda($item->valor) : '')
            ->placeholder('R$ 0,00')
        !!}
    </div>

 <div class="col-md-3 d-flex align-items-end gap-2">
        {!! Form::select(
            'categoria_id',
            'Categoria',
            ['' => 'Selecione uma Categoria'] + $categorias->pluck('nome', 'id')->all(),
        )->attrs(['class' => 'form-select'])->required() !!}
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modal_categoria_servico"
            type="button">
            <i class="ri-add-circle-fill"></i>
        </button>
    </div>

    <hr>

    {{-- <div class="col-md-2">
        {!!
            Form::tel('valor_adicional', 'Valor adicional')
            ->attrs(['class' => 'moeda'])
            ->value(isset($item) ? __moeda($item->valor_adicional) : '')
            ->placeholder('R$ 0,00')
        !!}
    </div> --}}

    {{-- <div class="col-md-2">
        {!!Form::text('tempo_tolerancia', 'Quantidade de dia(s)')
        ->attrs(['data-mask' => '00'])
        !!}
    </div> --}}

    <div class="col-md-2">
        {!!
            Form::tel('codigo_servico', 'Código do serviço')
            ->attrs([
                'class' => 'codigo_servico',
            ])
            ->placeholder('Digite o código do serviço')
        !!}
    </div>

    <div class="col-md-3">
        {!!
            Form::tel('codigo_tributacao_municipio', 'Código do tributação municipal')
            ->attrs(['data-mask' => '0000000000000000000'])
            ->placeholder('Digite o código de tributação municipal')
        !!}
    </div>

    <div class="col-md-2">
        {!!
            Form::tel('codigo_cnae', 'Código CNAE')
            ->attrs([
                'class' => 'cnae',
            ])
            ->placeholder('00.00-0/00')
        !!}
    </div>

    <hr>

    <div class="col-md-2">
        {!!
            Form::tel('valor_deducoes', 'Deduções')
            ->attrs(['class' => 'moeda'])
            ->value(isset($item) ? __moeda($item->valor_deducoes) : '')
            ->placeholder('R$ 0,00')
        !!}
    </div>

    <div class="col-md-2">
        {!!
            Form::tel('desconto_incondicional', 'Desconto incondicional')
            ->attrs(['class' => 'moeda'])
            ->value(isset($item) ? __moeda($item->desconto_incondicional) : '')
            ->placeholder('R$ 0,00')
        !!}
    </div>

    <div class="col-md-2">
        {!!
            Form::tel('desconto_condicional', 'Desconto condicional')
            ->attrs(['class' => 'moeda'])
            ->value(isset($item) ? __moeda($item->desconto_condicional) : '')
            ->placeholder('R$ 0,00')
        !!}
    </div>

    <div class="col-md-2">
        {!!
            Form::tel('outras_retencoes', 'Outras retenções')
            ->attrs(['class' => 'moeda'])
            ->value(isset($item) ? __moeda($item->outras_retencoes) : '')
            ->placeholder('R$ 0,00')
        !!}
    </div>

    <div class="col-md-2">
        {!!
            Form::select(
                'estado_local_prestacao_servico',
                'UF do local de prestação',
                \App\Models\Cidade::estados(),
            )->attrs(['class' => 'select2'])
        !!}
    </div>
</div>
