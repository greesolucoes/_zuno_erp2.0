@once
    @push('styles')
        <style>
            .custom-option-group {
                position: relative;
            }

            .custom-option-group .custom-option-input {
                display: none;
                margin-top: 0.5rem;
            }

            .custom-option-group.is-custom .custom-option-input {
                display: block;
            }

            .custom-option-group.is-custom select.form-select,
            .custom-option-group.is-custom .select2-container {
                display: none !important;
            }

            .custom-option-group .custom-option-input .form-control {
                margin-top: 0;
            }
        </style>
    @endpush
@endonce

@php
    $formMedicine = $medicine ?? [];
    $statusOptions = [
        'Ativo' => 'Ativo',
        'Inativo' => 'Inativo',
    ];

    $controlOptions = [
        'Não controlado' => 'Não controlado',
        'Tarja vermelha (controle especial)' => 'Tarja vermelha (controle especial)',
        'Tarja preta (psicotrópico)' => 'Tarja preta (psicotrópico)',
        '__custom__' => 'Personalizado',
    ];

    $stockOptions = [
        'Disponível' => 'Disponível',
        'Estoque baixo' => 'Estoque baixo',
        'Sem estoque' => 'Sem estoque',
    ];

    $selectedEspecies = old('especies', $formMedicine['especies'] ?? []);

    $customOptionValue = '__custom__';

    $selectedProductId = old('produto_id', $formMedicine['produto_id'] ?? ($formMedicine['product']['id'] ?? ''));
    $selectedProductLabel = $formMedicine['product_label']
        ?? ($formMedicine['product']['label'] ?? ($formMedicine['product']['name'] ?? null));

    if ($selectedProductId && ! $selectedProductLabel) {
        $productModel = \App\Models\Produto::find($selectedProductId);

        if ($productModel) {
            $productPrice = $productModel->valor_unitario ?? null;
            $selectedProductLabel = trim(
                $productModel->nome .
                ($productPrice ? ' - R$ ' . __moeda($productPrice) : '')
            );
        }
    }

    $productSelectOptions = [];
    if ($selectedProductId && $selectedProductLabel) {
        $productSelectOptions[$selectedProductId] = $selectedProductLabel;
    }

    $resolveCustomField = function (string $field, array $options, ?string $default = null) use ($formMedicine, $customOptionValue, $errors) {
        $baseValue = $formMedicine[$field] ?? $default ?? '';
        $rawValue = old($field, $baseValue ?? '');
        $isCustom = $rawValue === $customOptionValue
            || (filled($rawValue) && ! array_key_exists($rawValue, $options));
        $inputValue = $isCustom && $rawValue !== $customOptionValue ? $rawValue : '';

        return [
            'value' => $inputValue,
            'isCustom' => $isCustom,
            'selected' => $isCustom ? $customOptionValue : $rawValue,
            'hasError' => $errors->has($field),
        ];
    };

    $therapeuticClassField = $resolveCustomField('classe_terapeutica', $therapeuticClasses);
    $controlField = $resolveCustomField('classificacao_controle', $controlOptions, 'Não controlado');
    $routeField = $resolveCustomField('via_administracao', $routes);
    $presentationField = $resolveCustomField('apresentacao', $presentations);
    $dispensingField = $resolveCustomField('forma_dispensacao', $dispensingOptions);
    $ageRestrictionField = $resolveCustomField('restricao_idade', $ageRestrictions);
    $storageConditionField = $resolveCustomField('condicao_armazenamento', $storageConditions);
@endphp

<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs nav-primary" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3 active" data-bs-toggle="tab" href="#medicine-tab-general" role="tab" aria-selected="true">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-file-list-line"></i>
                            Informações gerais
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3" data-bs-toggle="tab" href="#medicine-tab-administration" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-injection-line"></i>
                            Administração
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3" data-bs-toggle="tab" href="#medicine-tab-stock" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-stack-line"></i>
                            Estoque
                        </div>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link px-3" data-bs-toggle="tab" href="#medicine-tab-guidance" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                        <div class="tab-title">
                            <i class="ri-chat-3-line"></i>
                            Orientações
                        </div>
                    </div>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="medicine-tab-general" role="tabpanel">
                <div class="row g-3 mt-3">
                      <div class="col-md-6 col-12">
                        {!! Form::select('produto_id', 'Produto', $productSelectOptions)
                            ->id('inp-produto_id')
                            ->attrs([
                                'class' => 'form-select select2',
                                'data-toggle' => 'select2',
                                'data-placeholder' => 'Digite para buscar o produto',
                                'data-width' => '100%',
                            ])
                            ->placeholder('Digite para buscar o produto')
                            ->value($selectedProductId) !!}
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('nome_comercial', 'Nome comercial')
                            ->placeholder('Ex.: Tramadol 50 mg')
                            ->attrs(['maxlength' => 255])
                            ->value(old('nome_comercial', $formMedicine['nome_comercial'] ?? ''))
                            ->required() !!}
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('nome_generico', 'Princípio ativo / Nome genérico')
                            ->placeholder('Ex.: Cloridrato de tramadol')
                            ->attrs(['maxlength' => 255])
                            ->value(old('nome_generico', $formMedicine['nome_generico'] ?? ''))
                            ->required() !!}
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="custom-option-group {{ $therapeuticClassField['isCustom'] ? 'is-custom' : '' }}"
                            data-custom-wrapper="classe_terapeutica">
                            {!! Form::select('classe_terapeutica', 'Categoria terapêutica', $therapeuticClasses)
                                ->attrs([
                                    'class' => 'form-select select2',
                                    'data-toggle' => 'select2',
                                    'data-width' => '100%',
                                    'data-allow-custom' => 'true',
                                    'data-custom-field' => 'classe_terapeutica',
                                    'data-custom-option' => $customOptionValue,
                                ])
                                ->value($therapeuticClassField['selected'])
                                ->required() !!}
                            <div class="custom-option-input">
                                <input type="text"
                                    class="form-control {{ $therapeuticClassField['hasError'] ? 'is-invalid' : '' }}"
                                    data-custom-input="classe_terapeutica"
                                    placeholder="Digite o valor personalizado"
                                    value="{{ $therapeuticClassField['isCustom'] ? $therapeuticClassField['value'] : '' }}"
                                    autocomplete="off"
                                    @if ($therapeuticClassField['isCustom'])
                                        name="classe_terapeutica" required
                                    @else
                                        disabled
                                    @endif>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        {!! Form::text('classe_farmacologica', 'Classe farmacológica')
                            ->placeholder('Ex.: Opioide sintético, AINE, antibiótico...')
                            ->attrs(['maxlength' => 255])
                            ->value(old('classe_farmacologica', $formMedicine['classe_farmacologica'] ?? ''))
                            ->required() !!}
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="custom-option-group {{ $controlField['isCustom'] ? 'is-custom' : '' }}"
                            data-custom-wrapper="classificacao_controle">
                            {!! Form::select('classificacao_controle', 'Classificação de controle', $controlOptions)
                                ->attrs([
                                    'class' => 'form-select',
                                    'data-allow-custom' => 'true',
                                    'data-custom-field' => 'classificacao_controle',
                                    'data-custom-option' => $customOptionValue,
                                ])
                                ->value($controlField['selected']) !!}
                            <div class="custom-option-input">
                                <input type="text"
                                    class="form-control {{ $controlField['hasError'] ? 'is-invalid' : '' }}"
                                    data-custom-input="classificacao_controle"
                                    placeholder="Digite o valor personalizado"
                                    value="{{ $controlField['isCustom'] ? $controlField['value'] : '' }}"
                                    autocomplete="off"
                                    @if ($controlField['isCustom'])
                                        name="classificacao_controle" required
                                    @else
                                        disabled
                                    @endif>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="custom-option-group {{ $routeField['isCustom'] ? 'is-custom' : '' }}"
                            data-custom-wrapper="via_administracao">
                            {!! Form::select('via_administracao', 'Via de administração', $routes)
                                ->attrs([
                                    'class' => 'form-select select2',
                                    'data-toggle' => 'select2',
                                    'data-width' => '100%',
                                    'data-allow-custom' => 'true',
                                    'data-custom-field' => 'via_administracao',
                                    'data-custom-option' => $customOptionValue,
                                ])
                                ->value($routeField['selected'])
                                ->required() !!}
                            <div class="custom-option-input">
                                <input type="text"
                                    class="form-control {{ $routeField['hasError'] ? 'is-invalid' : '' }}"
                                    data-custom-input="via_administracao"
                                    placeholder="Digite o valor personalizado"
                                    value="{{ $routeField['isCustom'] ? $routeField['value'] : '' }}"
                                    autocomplete="off"
                                    @if ($routeField['isCustom'])
                                        name="via_administracao" required
                                    @else
                                        disabled
                                    @endif>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="custom-option-group {{ $presentationField['isCustom'] ? 'is-custom' : '' }}"
                            data-custom-wrapper="apresentacao">
                            {!! Form::select('apresentacao', 'Apresentação', $presentations)
                                ->attrs([
                                    'class' => 'form-select select2',
                                    'data-toggle' => 'select2',
                                    'data-width' => '100%',
                                    'data-allow-custom' => 'true',
                                    'data-custom-field' => 'apresentacao',
                                    'data-custom-option' => $customOptionValue,
                                ])
                                ->value($presentationField['selected'])
                                ->required() !!}
                            <div class="custom-option-input">
                                <input type="text"
                                    class="form-control {{ $presentationField['hasError'] ? 'is-invalid' : '' }}"
                                    data-custom-input="apresentacao"
                                    placeholder="Digite o valor personalizado"
                                    value="{{ $presentationField['isCustom'] ? $presentationField['value'] : '' }}"
                                    autocomplete="off"
                                    @if ($presentationField['isCustom'])
                                        name="apresentacao" required
                                    @else
                                        disabled
                                    @endif>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="medicine-tab-administration" role="tabpanel">
                <div class="row g-3 mt-3">
                    <div class="col-md-6 col-12">
                        {!! Form::text('concentracao', 'Concentração / Potência')
                            ->placeholder('Ex.: 50 mg/comprimido, 1,5 mg/ml')
                            ->attrs(['maxlength' => 255])
                            ->value(old('concentracao', $formMedicine['concentracao'] ?? ''))
                            ->required() !!}
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="custom-option-group {{ $dispensingField['isCustom'] ? 'is-custom' : '' }}"
                            data-custom-wrapper="forma_dispensacao">
                            {!! Form::select('forma_dispensacao', 'Forma de dispensação', $dispensingOptions)
                                ->attrs([
                                    'class' => 'form-select select2',
                                    'data-toggle' => 'select2',
                                    'data-width' => '100%',
                                    'data-allow-custom' => 'true',
                                    'data-custom-field' => 'forma_dispensacao',
                                    'data-custom-option' => $customOptionValue,
                                ])
                                ->value($dispensingField['selected'])
                                ->required() !!}
                            <div class="custom-option-input">
                                <input type="text"
                                    class="form-control {{ $dispensingField['hasError'] ? 'is-invalid' : '' }}"
                                    data-custom-input="forma_dispensacao"
                                    placeholder="Digite o valor personalizado"
                                    value="{{ $dispensingField['isCustom'] ? $dispensingField['value'] : '' }}"
                                    autocomplete="off"
                                    @if ($dispensingField['isCustom'])
                                        name="forma_dispensacao" required
                                    @else
                                        disabled
                                    @endif>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('dosagem', 'Dosagem recomendada')
                            ->placeholder('Ex.: 0,1 mg/kg')
                            ->attrs(['maxlength' => 120])
                            ->value(old('dosagem', $formMedicine['dosagem'] ?? ''))
                            ->required() !!}
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('frequencia', 'Frequência de administraão')
                            ->placeholder('Ex.: A cada 8 horas')
                            ->attrs(['maxlength' => 120])
                            ->value(old('frequencia', $formMedicine['frequencia'] ?? ''))
                            ->required() !!}
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('duracao', 'Duração recomendada / protocolo')
                            ->placeholder('Ex.: 7 dias ou conforme avaliação')
                            ->attrs(['maxlength' => 120])
                            ->value(old('duracao', $formMedicine['duracao'] ?? '')) !!}
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="custom-option-group {{ $ageRestrictionField['isCustom'] ? 'is-custom' : '' }}"
                            data-custom-wrapper="restricao_idade">
                            {!! Form::select('restricao_idade', 'Restrição etária', $ageRestrictions)
                                ->attrs([
                                    'class' => 'form-select select2',
                                    'data-toggle' => 'select2',
                                    'data-width' => '100%',
                                    'data-allow-custom' => 'true',
                                    'data-custom-field' => 'restricao_idade',
                                    'data-custom-option' => $customOptionValue,
                                ])
                                ->value($ageRestrictionField['selected']) !!}
                            <div class="custom-option-input">
                                <input type="text"
                                    class="form-control {{ $ageRestrictionField['hasError'] ? 'is-invalid' : '' }}"
                                    data-custom-input="restricao_idade"
                                    placeholder="Digite o valor personalizado"
                                    value="{{ $ageRestrictionField['isCustom'] ? $ageRestrictionField['value'] : '' }}"
                                    autocomplete="off"
                                    @if ($ageRestrictionField['isCustom'])
                                        name="restricao_idade" required
                                    @else
                                        disabled
                                    @endif>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="custom-option-group {{ $storageConditionField['isCustom'] ? 'is-custom' : '' }}"
                            data-custom-wrapper="condicao_armazenamento">
                            {!! Form::select('condicao_armazenamento', 'Condições de armazenamento', $storageConditions)
                                ->attrs([
                                    'class' => 'form-select select2',
                                    'data-toggle' => 'select2',
                                    'data-width' => '100%',
                                    'data-allow-custom' => 'true',
                                    'data-custom-field' => 'condicao_armazenamento',
                                    'data-custom-option' => $customOptionValue,
                                ])
                                ->value($storageConditionField['selected'])
                                ->required() !!}
                            <div class="custom-option-input">
                                <input type="text"
                                    class="form-control {{ $storageConditionField['hasError'] ? 'is-invalid' : '' }}"
                                    data-custom-input="condicao_armazenamento"
                                    placeholder="Digite o valor personalizado"
                                    value="{{ $storageConditionField['isCustom'] ? $storageConditionField['value'] : '' }}"
                                    autocomplete="off"
                                    @if ($storageConditionField['isCustom'])
                                        name="condicao_armazenamento" required
                                    @else
                                        disabled
                                    @endif>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('validade', 'Validade / tempo de prateleira')
                            ->placeholder('Ex.: 24 meses, vencer em 11/2025')
                            ->attrs(['maxlength' => 120])
                            ->value(old('validade', $formMedicine['validade'] ?? '')) !!}
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('fornecedor', 'Fornecedor principal')
                            ->placeholder('Ex.: PharmaVet Distribuidora')
                            ->attrs(['maxlength' => 255])
                            ->value(old('fornecedor', $formMedicine['fornecedor'] ?? '')) !!}
                    </div>

                    <div class="col-md-6 col-12">
                        {!! Form::text('sku', 'Código interno / SKU')
                            ->placeholder('Ex.: MED-TRAM-50')
                            ->attrs(['maxlength' => 60])
                            ->value(old('sku', $formMedicine['sku'] ?? '')) !!}
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="medicine-tab-stock" role="tabpanel">
                <div class="row g-3 mt-3">
                    <div class="col-md-3 col-12">
                        {!! Form::text('current_stock', 'Estoque atual (unidades)')
                            ->attrs([
                                'min' => 0,
                                'step' => 1,
                                'readonly' => true,
                                'class' => 'form-control text-end',
                                'id' => 'inp-medicine_current_stock',
                            ])
                            ->value(old('current_stock', $formMedicine['current_stock'] ?? 0)) ->disabled()!!}
                        <small class="text-muted">Valor preenchido de acordo com o estoque do produto selecionado.</small>
                    </div>

                    <div class="col-md-3 col-12">
                        {!! Form::text('minimum_stock', 'Estoque mínimo desejado')
                            ->attrs([
                                'min' => 0,
                                'step' => 1,
                                'readonly' => true,
                                'class' => 'form-control text-end',
                                'id' => 'inp-medicine_minimum_stock',
                            ])
                            ->value(old('minimum_stock', $formMedicine['minimum_stock'] ?? 0)) ->disabled()!!}
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="medicine-tab-guidance" role="tabpanel">
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        {!! Form::select('especies[]', 'Espécies atendidas', $especiesOptions)
                            ->attrs([
                                'class' => 'form-select select2',
                                'multiple' => true,
                                'data-toggle' => 'select2',
                                'data-width' => '100%',
                            ])
                            ->multiple()
                            ->value($selectedEspecies)
                            ->required() !!}
                    </div>

                    <div class="col-12">
                        {!! Form::textarea('indicacoes', 'Indicações clínicas')
                            ->placeholder('Condições e objetivos terapêuticos para uso do medicamento.')
                            ->attrs(['rows' => 3, 'style' => 'resize:none;'])
                            ->value(old('indicacoes', $formMedicine['indicacoes'] ?? ''))
                            ->required() !!}
                    </div>

                    <div class="col-12">
                        {!! Form::textarea('contraindicacoes', 'Contraindicações e precauções')
                            ->placeholder('Situações em que o uso é desaconselhado, ajustes necessários e cuidados especiais.')
                            ->attrs(['rows' => 3, 'style' => 'resize:none;'])
                            ->value(old('contraindicacoes', $formMedicine['contraindicacoes'] ?? '')) !!}
                    </div>

                    <div class="col-12">
                        {!! Form::textarea('efeitos_adversos', 'Efeitos adversos potenciais')
                            ->placeholder('Reações esperadas, intensidade e conduta em caso de ocorrência.')
                            ->attrs(['rows' => 3, 'style' => 'resize:none;'])
                            ->value(old('efeitos_adversos', $formMedicine['efeitos_adversos'] ?? '')) !!}
                    </div>

                    <div class="col-12">
                        {!! Form::textarea('interacoes', 'Interações medicamentosas')
                            ->placeholder('Medicamentos ou nutrientes que podem interagir. Informe ajustes de intervalo ou dose.')
                            ->attrs(['rows' => 3, 'style' => 'resize:none;'])
                            ->value(old('interacoes', $formMedicine['interacoes'] ?? '')) !!}
                    </div>

                    <div class="col-12">
                        {!! Form::textarea('monitoramento', 'Monitoramento recomendado')
                            ->placeholder('Exames, sinais vitais e indicadores clínicos que precisam ser acompanhados.')
                            ->attrs(['rows' => 3, 'style' => 'resize:none;'])
                            ->value(old('monitoramento', $formMedicine['monitoramento'] ?? '')) !!}
                    </div>

                    <div class="col-12">
                        {!! Form::textarea('orientacoes_tutor', 'Orientações ao tutor')
                            ->placeholder('Instruções para administração domiciliar, armazenamento e sinais de alerta para o tutor.')
                            ->attrs(['rows' => 3, 'style' => 'resize:none;'])
                            ->value(old('orientacoes_tutor', $formMedicine['orientacoes_tutor'] ?? '')) !!}
                    </div>

                    <div class="col-12">
                        {!! Form::textarea('observacoes', 'Observações internas / alertas adicionais')
                            ->placeholder('Informações complementares para a equipe clínica ou de estoque.')
                            ->attrs(['rows' => 3, 'style' => 'resize:none;'])
                            ->value(old('observacoes', $formMedicine['observacoes'] ?? '')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <button type="submit" class="btn btn-success px-5">
        <i class="ri-save-3-fill"></i>
        Salvar
    </button>
</div>