<div class="nfse-servico-item">
    {!! Form::hidden('servico_id[]', isset($servico) ? ($servico->servico_id ?? $servico->id) : null)->attrs(['class' => 'servico_id']) !!}

    <h5 class="mt-4 mb-3">Dados do serviço</h5>
    <div class="row g-xl-3 g-lg-2">
        <div class="col-md-12">
            {!! 
                Form::text('discriminacao[]', 'Discriminação (descrição do serviço)')
                ->attrs(['class' => 'discriminacao'])
                ->value(isset($servico) ? $servico->discriminacao : '')
                ->disabled(!isset($servico))
                ->required() 
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::tel('valor_servico[]', 'Valor do serviço')
                ->attrs(['class' => 'moeda valor-servico'])
                ->value(isset($servico) ? __moedaInput($servico->valor_servico) : '')
                ->disabled(!isset($servico))
                ->required() 
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('codigo_cnae[]', 'Cód. CNAE')
                ->attrs(['class' => 'codigo_cnae'])
                ->value(isset($servico) ? $servico->codigo_cnae : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('codigo_servico[]', 'Código do serviço')
                ->attrs(['class' => 'codigo_servico'])
                ->value(isset($servico) ? $servico->codigo_servico : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-3">
            {!! 
                Form::text('codigo_tributacao_municipio[]', 'Cód. de tributação do município')
                ->attrs(['class' => 'codigo_tributacao_municipio'])
                ->value(isset($servico) ? $servico->codigo_tributacao_municipio : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::select('exigibilidade_iss[]', 'Exigibilidade ISS', \App\Models\NotaServico::exigibilidades())
                ->attrs(['class' => 'form-select exigibilidade_iss'])
                ->value(isset($servico) ? $servico->exigibilidade_iss : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::select('iss_retido[]', 'ISS retido', [2 => 'Não', 1 => 'Sim'])
                ->attrs(['class' => 'form-select iss_retido'])
                ->value(isset($servico) ? $servico->iss_retido : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::select('responsavel_retencao_iss[]', 'Resp. pela retenção', [1 => 'Tomador', 2 => 'Sim'])
                ->attrs(['class' => 'form-select responsavel_retencao_iss'])
                ->value(isset($servico) ? $servico->responsavel_retencao_iss : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::date('data_competencia[]', 'Data da competência')
                ->attrs(['class' => 'data_competencia'])
                ->value(isset($servico) ? $servico->data_competencia : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::select('estado_local_prestacao_servico[]', 'UF do local de prestação', \App\Models\Cidade::estados())
                ->attrs(['class' => 'form-select estado_local_prestacao_servico'])
                ->value(isset($servico) ? $servico->estado_local_prestacao_servico : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-3">
            {!! 
                Form::select('cidade_local_prestacao_servico[]', 'Cidade do local de prestação')
                ->attrs(['class' => 'select2 cidade_local_prestacao_servico'])
                ->options(
                    isset($servico) && $servico->cidade_local_prestacao_servico ?
                    [$servico->cidade_local_prestacao_servico => $servico->cidade_local_prestacao_servico] :
                    []
                ) 
                ->disabled(!isset($servico))
            !!}
        </div>
    </div>

    <h5 class="mt-4 mb-3">Tributação</h5>
    <div class="row g-xl-3 g-lg-2">
        <div class="col-md-2">
            {!! 
                Form::text('valor_deducoes[]', 'Valor deduções')
                ->attrs(['class' => 'moeda'])
                ->value(isset($servico) ? __moedaInput($servico->valor_deducoes ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('desconto_incondicional[]', 'Desconto incondicional')
                ->attrs(['class' => 'moeda'])
                ->value(isset($servico) ? __moedaInput($servico->desconto_incondicional ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('desconto_condicional[]', 'Desconto condicional')
                ->attrs(['class' => 'moeda'])
                ->value(isset($servico) ? __moedaInput($servico->desconto_condicional ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('outras_retencoes[]', 'Outras retencoes')
                ->attrs(['class' => 'moeda'])
                ->value(isset($servico) ? __moedaInput($servico->outras_retencoes ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('aliquota_iss[]', 'Aliquota ISS')
                ->attrs(['class' => 'percentual'])
                ->value(isset($servico) ? __moedaInput($servico->aliquota_iss ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('aliquota_pis[]', 'Aliquota PIS')
                ->attrs(['class' => 'percentual'])
                ->value(isset($servico) ? __moedaInput($servico->aliquota_pis ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('aliquota_cofins[]', 'Aliquota COFINS')
                ->attrs(['class' => 'percentual'])
                ->value(isset($servico) ? __moedaInput($servico->aliquota_cofins ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('aliquota_inss[]', 'Aliquota INSS')
                ->attrs(['class' => 'percentual'])
                ->value(isset($servico) ? __moedaInput($servico->aliquota_inss ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('aliquota_ir[]', 'Aliquota IR')
                ->attrs(['class' => 'percentual'])
                ->value(isset($servico) ? __moedaInput($servico->aliquota_ir ?? "0.00") : '')
                ->disabled(!isset($servico)) 
            !!}
        </div>
        <div class="col-md-2">
            {!! 
                Form::text('aliquota_csll[]', 'Aliquota CSLL')
                ->attrs(['class' => 'percentual'])
                ->value(isset($servico) ? __moedaInput($servico->aliquota_csll ?? "0.00") : '') 
                ->disabled(!isset($servico))
            !!}
        </div>
    </div>
</div>